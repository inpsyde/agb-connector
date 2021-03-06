<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use WP_Post;

/**
 * Service listening for the post saving hook and adding a mark (meta field) to the post if it
 * displays Document (contains shortcode or block with the Document).
 */
class PostSavingListener
{
    /**
     * @var ShortCodes
     */
    protected $shortcodes;
    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;

    /**
     * @param DocumentRepositoryInterface $documentRepository
     * @param ShortCodes $shortCodes
     */
    public function __construct(DocumentRepositoryInterface $documentRepository, ShortCodes $shortCodes)
    {
        $this->shortcodes = $shortCodes;
        $this->documentRepository = $documentRepository;
    }

    public function init(): void
    {
        add_action('save_post', [$this, 'handlePostSaving'], 10, 2);
    }

    /**
     * Add meta field to the saved post if it displays one of the Documents.
     *
     * @param int $postId
     * @param WP_Post$post
     */
    public function handlePostSaving($postId, $post): void
    {
        if ($this->checkIfPostIsDocument((int) $postId)) {
            //it's a document itself, so it cannot be displaying page
            return;
        }

        $documentIdsFromShortcodes = $this->findDocumentsIdsUsedInShortcodes($post->post_content);$documentIdsFromBlocks = $this->findDocumentsIdsUsedInPostBlocks($post);

        $postDisplaysAgbDocument = array_unique(
            array_merge($documentIdsFromShortcodes, $documentIdsFromBlocks)
        );
        $this->updateAgbMetaField($postId, $postDisplaysAgbDocument);
    }

    /**
     * Check if provided text contains at least one of the plugin's shortcodes.
     *
     * @param string $text
     *
     * @return int[]
     */
    protected function findDocumentsIdsUsedInShortcodes(string $text): array
    {
        $foundDocumentIds = [];

        $shortcodeRegex = get_shortcode_regex($this->shortcodes->getShortcodeTags());

        preg_match_all('/' . $shortcodeRegex . '/', $text, $matches);

        $foundShortcodes = $matches[0] ?? [];
        $foundShortcodeTags = $matches[2] ?? [];
        $foundShortcodeAtts = $matches[3] ?? [];
        $foundCount = count($foundShortcodes);

        for ($i = 0; $i < $foundCount; $i++) {
            $atts = shortcode_parse_atts($foundShortcodeAtts[$i]);
            $country = $atts['country'] ?? '';
            $language = $atts['language'] ?? '';
            $documentType = $this->shortcodes->getDocumentTypeByShortcodeTag($foundShortcodeTags[$i]);

            $foundDocumentIds[] = $this->documentRepository->getDocumentPostIdByTypeCountryAndLanguage($documentType, $country, $language);
        }

        return $foundDocumentIds;
    }

    /**
     * Check if provided post contain at least one Document in form of Gutenberg block.
     *
     * @param WP_Post $post
     *
     * @return int[]
     */
    protected function findDocumentsIdsUsedInPostBlocks(WP_Post $post): array
    {
        $blocks = parse_blocks($post->post_content);

        $refIds = $this->getRefsIdsFromBlocks($blocks);

        $refIds = array_unique($refIds);

        return array_filter($refIds, [$this, 'checkIfPostIsDocument']);
    }

    /**
     * @param array $blocks
     *
     * @return int[]
     */
    protected function getRefsIdsFromBlocks(array $blocks): array
    {
        $refIds = [];

        foreach ($blocks as $block) {
            $refIds = array_merge($refIds, $this->getRefsFromSingleBlock($block));
        }

        return $refIds;
    }

    /**
     * @param array $block
     *
     * @return int[]
     */
    protected function getRefsFromSingleBlock(array $block): array
    {
        $refIds[] = intval($block['attrs']['ref'] ?? 0);

        if ($block['innerBlocks']) {
            $refsFromInternalBlocks = $this->getRefsIdsFromBlocks($block['innerBlocks']);
            $refIds = array_merge($refIds, $refsFromInternalBlocks);
        }

        return $refIds;
    }

    /**
     * @param int $postId
     *
     * @return bool
     */
    protected function checkIfPostIsDocument(int $postId): bool
    {
        return metadata_exists('post', $postId, WpPostMetaFields::WP_POST_DOCUMENT_TYPE);
    }

    /**
     * Add meta field if post displays Document, remove this meta field otherwise.
     *
     * @param int $postId
     * @param int[] $documentIds
     *
     * @return void
     */
    protected function updateAgbMetaField(int $postId, array $documentIds): void
    {
        if ($documentIds) {
            update_post_meta($postId, 'agb_page_contain_documents', $documentIds);

            return;
        }

        delete_post_meta($postId, 'agb_page_contain_documents');
    }
}
