<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;

use Inpsyde\AGBConnector\Document\Document;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\DocumentSettings;
use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;
use Inpsyde\AGBConnector\Plugin;
use WP_Post;

class WpPostBasedDocumentFactory implements WpPostBasedDocumentFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function createDocument(WP_Post $post): DocumentInterface
    {
        if ($post->post_type === 'wp_block') {
            return $this->createFromWpBlock($post);
        }

        return $this->createFromPost($post);
    }

    /**
     * Create Document instance from 'wp_block' post type (used in new plugin versions).
     *
     * @param WP_Post $post Post to get content from.
     *
     * @return DocumentInterface
     */
    protected function createFromWpBlock(WP_Post $post): DocumentInterface
    {
        $documentType = $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_TYPE);
        $savePdf = $documentType !== 'impressum' &&
            $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_FLAG_SAVE_PDF);

        $documentSettings = new DocumentSettings();
        $documentSettings->setAttachToWcEmail(
            $savePdf &&
            $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_FLAG_ATTACH_TO_WC_EMAIL)
        );
        $documentSettings->setDocumentId($post->ID);
        $documentSettings->setSavePdf($savePdf);
        $documentSettings->setPdfAttachmentId(
            $this->getAttachedPdfId($post)
        );
        $documentSettings->setHideTitle(
            (bool) $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_FLAG_HIDE_TITLE)
        );

        return new Document(
            $documentSettings,
            $post->post_title,
            $post->post_content,
            $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_COUNTRY),
            $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_LANGUAGE),
            $documentType
        );
    }

    /**
     * Create Document instance from post added by old plugin versions.
     *
     * @param WP_Post $post Post to get content from.
     *
     * @return DocumentInterface
     */
    protected function createFromPost(WP_Post $post): DocumentInterface
    {
        $allocations = $this->getDocumentDataFromOptions($post);

        $documentSettings = new DocumentSettings();
        $documentSettings->setSavePdf(! empty($allocations['savePdfFile']));
        $documentSettings->setAttachToWcEmail(! empty($allocations['wcOrderEmailAttachment']));
        $documentSettings->setPdfAttachmentId(
            $this->getAttachedPdfId($post)
        );

        return new Document(
            $documentSettings,
            $post->post_title,
            $post->post_content,
            $allocations['country'] ?? '',
            $allocations['language'] ?? '',
            $allocations['type'] ?? ''
        );
    }

    /**
     * Wrapper for get_post_meta function.
     *
     * @param WP_Post $post
     * @param string $fieldName
     *
     * @return string
     */
    protected function getPostMeta(WP_Post $post, string $fieldName): string
    {
        return (string) get_post_meta($post->ID, $fieldName, true);
    }

    /**
     * Get the id of document post pdf attachment, 0 if not found.
     *
     * @param WP_Post $post
     *
     * @return int
     */
    protected function getAttachedPdfId(WP_Post $post): int
    {
        $args =  [
            'fields' => 'ids',
            'nopaging' => true,
            'no_found_rows' => true,
            'post_mime_type' => 'application/pdf',
            'post_parent' => $post->ID,
            'post_type' => 'attachment',
            'posts_per_page' => 1, //Get only the latest post. By default, WP uses 'date' for sorting by and 'DESC' for order.
        ];

        $attachment = get_posts($args);

        return isset($attachment[0]) ? (int) $attachment[0] : 0;
    }

    /**
     * Get document allocations saved in options by old versions of the plugin.
     *
     * @param WP_Post $post
     *
     * @return array
     */
    protected function getDocumentDataFromOptions(WP_Post $post): array
    {
        $allAllocations = get_option(Plugin::OPTION_TEXT_ALLOCATIONS);

        if (! is_array($allAllocations)) {
            return [];
        }

        foreach ($allAllocations as $documentType => $allocationsOfType) {
            foreach ($allocationsOfType as $documentAllocation) {
                if (isset($documentAllocation['pageId']) && (int) $documentAllocation['pageId'] === $post->ID) {
                    $documentAllocation['type'] = $documentType;
                    return $documentAllocation;
                }
            }
        }

        return [];
    }
}
