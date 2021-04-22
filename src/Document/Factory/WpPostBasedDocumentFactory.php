<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;

use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\Document;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;
use WP_Post;

class WpPostBasedDocumentFactory implements WpPostBasedDocumentFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function createDocument(WP_Post $post): DocumentInterface
    {
        if($post->post_type === 'wp_block'){
            return $this->createFromWpBlock($post);
        }

        return $this->createFromPost($post);
    }

    /**
     * @param WP_Post $post Post to get content from.
     *
     * @return DocumentInterface
     */
    protected function createFromWpBlock(WP_Post $post): DocumentInterface
    {
        return new Document(
            $post->post_title,
            '', //todo: decide whether we need text version of the document
            $post->post_content,
            $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_COUNTRY),
            $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_LANGUAGE),
            $this->getPostMeta($post, WpPostMetaFields::WP_POST_DOCUMENT_TYPE)
            //todo: add pdf link

        );
    }

    /**
     * @param WP_Post $post Post to get content from.
     *
     * @return DocumentInterface
     */
    protected function createFromPost(WP_Post $post): DocumentInterface
    {
        //todo
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
}
