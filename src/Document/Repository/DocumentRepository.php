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
        $documentPostId = $this->getDocumentPostIdByTypeCountryAndLanguage(
            $document->getType(),
            $document->getCountry(),
            $document->getLanguage()
        );

        $args = [
            'ID' => $documentPostId,
            'post_type' => 'wp_block',
            'post_content' => $document->getContent(),
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
     * Create a title for a document using original title, language and country.
     *
     * @param DocumentInterface $document
     *
     * @return string
     */
    protected function buildDocumentTitle(DocumentInterface $document): string
    {
        return sprintf(
            '%1$s %2$s %3$s',
            $document->getTitle(),
            $document->getLanguage(),
            $document->getCountry()
        );
    }

    /**
     * Return document of given type or null if not found.
     *
     * @param string $type
     * @param string $country
     * @param string $language
     *
     * @return int
     */
    protected function getDocumentPostIdByTypeCountryAndLanguage(
        string $type,
        string $country,
        string $language
    ): int {
        $foundPostId = get_posts(
            [
                'numberposts' => 1,
                'post_type' => 'wp_block',
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => WpPostMetaFields::WP_POST_DOCUMENT_TYPE,
                        'value' => $type, //todo: only allow here known types of documents
                    ],
                    [
                        'key'=> WpPostMetaFields::WP_POST_DOCUMENT_COUNTRY,
                        'value' => $country,
                    ],
                    [
                        'key' => WpPostMetaFields::WP_POST_DOCUMENT_LANGUAGE,
                        'value' => $language
                    ]
                ]
            ]
        );

        return (int) reset($foundPostId) ?? 0;
    }
}