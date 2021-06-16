<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Updater;

use Inpsyde\AGBConnector\CustomExceptions\GeneralException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentFinderInterface;
use Inpsyde\AGBConnector\Document\Factory\WpPostBasedDocumentFactoryInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use Inpsyde\AGBConnector\Plugin;
use Inpsyde\AGBConnector\Settings;
use Inpsyde\AGBConnector\ShortCodes;
use RuntimeException;
use WP_Post;

class Updater implements UpdaterInterface
{

    /**
     * @var DocumentFinderInterface
     */
    protected $documentPageFinder;
    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;
    /**
     * @var array
     */
    protected $allocations;
    /**
     * @var WpPostBasedDocumentFactoryInterface
     */
    protected $documentFactory;
    /**
     * @var ShortCodes
     */
    protected $shortCodes;

    public function __construct(
        DocumentFinderInterface $documentPageFinder,
        DocumentRepositoryInterface $documentRepository,
        WpPostBasedDocumentFactoryInterface $documentFactory,
        ShortCodes $shortCodes,
        array $allocations
    ) {

        $this->documentPageFinder = $documentPageFinder;
        $this->documentRepository = $documentRepository;
        $this->allocations = $allocations;
        $this->documentFactory = $documentFactory;
        $this->shortCodes = $shortCodes;
    }

    /**
     * @inheritDoc
     */
    public function update(): void
    {
        $this->update210to300();
    }

    /**
     * Update plugin to the 3.0.0 version
     */
    protected function update210to300(): void
    {
        foreach ($this->allocations as $allocationsOfType) {
            foreach ($allocationsOfType as $allocation) {
                $documentPostId = (int)$allocation['pageId'] ?? 0;

                if ($documentPostId) {
                    $post = get_post($documentPostId);

                    try {
                        $this->moveOldDocumentPostToWpBlock($post);
                    } catch (XmlApiException | RuntimeException $exception) {
                        $this->log($exception->getMessage());
                        update_option(Settings::MIGRATION_FAILED_FLAG_OPTION_NAME, true);
                        return;
                    }
                }
            }
        }

        delete_option(Plugin::OPTION_TEXT_ALLOCATIONS);
        delete_option(Settings::MIGRATION_FAILED_FLAG_OPTION_NAME);
    }

    /**
     * Migrate old documents from plugin version 2 to plugin version 3.
     *
     * @param WP_Post $post
     *
     * @throws XmlApiException
     * @throws GeneralException
     */
    protected function moveOldDocumentPostToWpBlock(WP_Post $post): void
    {
        $document = $this->documentFactory->createDocument($post);
        $savedDocumentId = $this->documentRepository->saveDocument($document);
        $document->getSettings()->setDocumentId($savedDocumentId);
        $post->post_content = $this->getDocumentCodeForDisplayingPage($document, $post);
        $displayingPageUpdateResult = wp_update_post($post, true);

        if (is_wp_error($displayingPageUpdateResult)) {
            throw new RuntimeException(
                sprintf(
                    'Failed to update document page content, got an error: %1$s',
                    $displayingPageUpdateResult->get_error_message()
                )
            );
        }
    }

    /**
     * Return code for document block to be saved in the displaying page content.
     *
     * @param int $documentId
     *
     * @return string
     */
    protected function getBlockCodeForDocumentId(int $documentId): string
    {
        return sprintf(
            '<!-- wp:block {"ref":%1$d} /-->',
            $documentId
        );
    }

    /**
     * Add message to the log.
     *
     * @param string $message
     */
    protected function log(string $message): void
    {
        if (function_exists('wc_get_logger')) {
            $logger = wc_get_logger();

            if ($logger) {
                $logger->warning($message);
            }
        }

        error_log($message);
    }

    /**
     * Get shortcode or wp_block code to display document on the page.
     *
     * @param DocumentInterface $document
     * @param WP_Post $post
     *
     * @return string
     */
    protected function getDocumentCodeForDisplayingPage(
        DocumentInterface $document,
        WP_Post $post
    ): string {

        if ($this->isGutenbergEnabledForPost($post)) {
            return $this->getBlockCodeForDocumentId($document->getSettings()->getDocumentId());
        }

        return $this->getDocumentShortcode($document);
    }

    /**
     * Check if Gutenberg editor will be used for post.
     *
     * @param WP_Post $post
     *
     * @return bool
     */
    protected function isGutenbergEnabledForPost(WP_Post $post): bool
    {
        $editorReplacedForPost = apply_filters('replace_editor', false, $post);
        $editorCanBeUsedForPost = use_block_editor_for_post($post);

        return !$editorReplacedForPost && $editorCanBeUsedForPost;
    }

    /**
     * Return shortcode for given document.
     *
     * @param DocumentInterface $document
     *
     * @return string
     */
    protected function getDocumentShortcode(DocumentInterface $document): string
    {
        return $this->shortCodes->generateShortcodeForDocument($document);
    }
}
