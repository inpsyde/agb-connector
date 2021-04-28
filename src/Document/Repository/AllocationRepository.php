<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Repository;

use Inpsyde\AGBConnector\Document\DocumentSettingsInterface;
use Inpsyde\AGBConnector\Document\Factory\DocumentAllocationFactoryInterface;
use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;
use WP_Post;

class AllocationRepository implements AllocationRepositoryInterface
{

    /**
     * @var DocumentAllocationFactoryInterface
     */
    protected $allocationFactory;

    /**
     * @param DocumentAllocationFactoryInterface $allocationFactory
     */
    public function __construct(DocumentAllocationFactoryInterface $allocationFactory)
    {

        $this->allocationFactory = $allocationFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id): DocumentSettingsInterface
    {
        $post = get_post($id);

        return $this->allocationFactory->createAllocationFromPost($post);
    }

    /**
     * @inheritDoc
     */
    public function getByTypeCountryAndLanguage(string $type, string $country, string $language): ?DocumentSettingsInterface
    {
        $foundPost = get_posts(
            [
                'numberposts' => 1,
                'post_type' => 'wp_block',
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => WpPostMetaFields::WP_POST_DOCUMENT_TYPE,
                        'value' => $type,
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

        if(! $foundPost instanceof WP_Post){
            return null;
        }

        return $this->allocationFactory->createAllocationFromPost($foundPost);
    }

    /**
     * @inheritDoc
     */
    public function saveDocumentAllocation(DocumentSettingsInterface $allocation): void
    {
        wp_insert_post([
            'ID' => $allocation->getDocumentId(),
            'post_type' => 'wp_block',
            'post_status' => 'publish',
            'meta_input' => [
                WpPostMetaFields::WP_POST_DOCUMENT_TYPE => $allocation->getType(),
                WpPostMetaFields::WP_POST_DOCUMENT_LANGUAGE => $allocation->getLanguage(),
                WpPostMetaFields::WP_POST_DOCUMENT_COUNTRY => $allocation->getCountry(),
            ]
        ]);
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

        return array_map([$this->allocationFactory, 'createAllocationFromPost'], $foundPosts);
    }
}
