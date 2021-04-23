<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Repository;

use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\Factory\WpPostBasedDocumentFactory;
use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;

class DocumentRepository implements DocumentRepositoryInterface
{

    /**
     * @var WpPostBasedDocumentFactory
     */
    protected $documentFactory;

    public function __construct(WpPostBasedDocumentFactory $documentFactory)
    {

        $this->documentFactory = $documentFactory;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentById(int $id): ?DocumentInterface
    {
        //todo: handle exceptions
        $post = get_post($id);
        return $this->documentFactory->createDocument($post);
    }

    /**
     * @inheritDoc
     */
    public function getAllDocuments(DocumentInterface $document): array
    {
        $posts = get_posts();

        return array_map([$this->documentFactory, 'createDocument'], $posts);
    }

    /**
     * @inheritDoc
     */
    public function saveDocument(DocumentInterface $document): void
    {
        $documentPostId = $this->getDocumentPostIdByType($document->getType());

        $args = [
            'ID' => $documentPostId,
            'post_type' => 'wp_block',
            'post_content' => $document->getContentAsHtml(),
            'post_title' => $document->getTitle(),
            'post_status' => 'publish',
            'meta_input' => [
                WpPostMetaFields::WP_POST_DOCUMENT_TYPE => $document->getType(),
                WpPostMetaFields::WP_POST_DOCUMENT_LANGUAGE => $document->getLanguage(),
                WpPostMetaFields::WP_POST_DOCUMENT_COUNTRY => $document->getCountry(),
            ]
        ];

        wp_insert_post( $args, true);

        //todo: handle errors
    }

    /**
     * Return document of given type or null if not found.
     *
     * @param string $type
     *
     * @return int
     */
    protected function getDocumentPostIdByType(string $type): int
    {
        $foundPostId = get_posts(
            [
                'numberposts' => 1,
                'post_type' => 'wp_block',
                'meta_key' => WpPostMetaFields::WP_POST_DOCUMENT_TYPE,
                'meta_value' => $type, //todo: only allow here known types of documents
            ]
        );

        return (int) reset($foundPostId) ?? 0;
    }
}
