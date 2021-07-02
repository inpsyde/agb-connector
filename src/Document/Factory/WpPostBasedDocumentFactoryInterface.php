<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;

use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use WP_Post;

/**
 * Interface WpPostBasedDocumentFactoryInterface
 *
 * @package Inpsyde\AGBConnector\Document\Factory
 *
 * Service able to create DocumentInterface instance from WP post.
 */
interface WpPostBasedDocumentFactoryInterface
{
    /**
     * Create a new document from WP post.
     *
     * @param WP_Post $post The WP post to create document from.
     *
     * @return DocumentInterface
     *
     * @throws XmlApiException If couldn't create a new instance.
     */
    public function createDocument(WP_Post $post): DocumentInterface;
}
