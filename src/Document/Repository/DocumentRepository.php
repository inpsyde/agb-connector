<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Repository;

use Inpsyde\AGBConnector\CustomExceptions\GeneralException;
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
    public function getAllDocuments(): array
    {
        $posts = get_posts(
            [
                'numberposts' => -1,
                'post_type' => 'wp_block',
                'meta_query' => [
                    [
                        'key' => WpPostMetaFields::WP_POST_DOCUMENT_TYPE,
                        'compare' => 'EXISTS'
                    ]
                ]
            ]
        );

        return array_map([$this->documentFactory, 'createDocument'], $posts);
    }

    public function getAllDocumentsInTrash(): array
    {
        $posts = get_posts(
            [
                'numberposts' => -1,
                'post_type' => 'wp_block',
                'post_status' => 'trash',
                'meta_query' => [
                    [
                        'key' => WpPostMetaFields::WP_POST_DOCUMENT_TYPE,
                        'compare' => 'EXISTS'
                    ]
                ]
            ]
        );

        return array_map([$this->documentFactory, 'createDocument'], $posts);
    }

    /**
     * @inheritDoc
     */
    public function getAllOfType(string $type): array
    {
        $foundPosts = get_posts(
            [
                'numberposts' => -1,
                'post_type' => 'wp_block',
                'meta_key' => WpPostMetaFields::WP_POST_DOCUMENT_TYPE,
                'meta_value' => $type
            ]
        );

        return array_map([$this->documentFactory, 'createDocument'], $foundPosts);
    }

    /**
     * @inheritDoc
     */
    public function getDocumentPostIdByTypeCountryAndLanguage(
        string $type,
        string $country,
        string $language
    ): int {
        $foundPostId = get_posts(
            [
                'numberposts' => 1,
                'fields' => 'ids',
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

    /**
     * @inheritDoc
     */
    public function saveDocument(DocumentInterface $document): int
    {
        $documentSettings = $document->getSettings();


        $documentPostId = $documentSettings->getDocumentId() ?: $this->getDocumentPostIdByTypeCountryAndLanguage(
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
                WpPostMetaFields::WP_POST_DOCUMENT_FLAG_SAVE_PDF => $documentSettings->getSavePdf(),
                WpPostMetaFields::WP_POST_DOCUMENT_FLAG_ATTACH_TO_WC_EMAIL => $documentSettings->getAttachToWcEmail(),
                WpPostMetaFields::WP_POST_DOCUMENT_FLAG_HIDE_TITLE => $documentSettings->getHideTitle()
            ]
        ];
        remove_filter('content_save_pre', 'wp_filter_post_kses');

        $result = wp_insert_post( $args, true);

        add_filter('content_save_pre', 'wp_filter_post_kses');

        if (is_wp_error($result)) {
            throw new GeneralException(
                sprintf('Failed to save the post, WP_Error received when tried: %1$s',
                    $result->get_error_message())
            );
        }

        return $result;
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
}
