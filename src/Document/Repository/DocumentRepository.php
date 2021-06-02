<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Repository;

use Inpsyde\AGBConnector\CustomExceptions\GeneralException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\AttributesAdder\AttributesAdderInterface;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\Factory\WpPostBasedDocumentFactory;
use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;
use WP_Post;

class DocumentRepository implements DocumentRepositoryInterface
{

    /**
     * @var WpPostBasedDocumentFactory
     */
    protected $documentFactory;
    /**
     * @var AttributesAdderInterface
     */
    protected $attributesAdder;

    /**
     * List of documents already processed by AttributesAdded.
     *
     * @var int[]
     */
    protected $processedDocuments = [];

    /**
     * @param WpPostBasedDocumentFactory $documentFactory
     * @param AttributesAdderInterface $attributesAdder
     */
    public function __construct(
        WpPostBasedDocumentFactory $documentFactory,
        AttributesAdderInterface $attributesAdder
    ) {

        $this->documentFactory = $documentFactory;
        $this->attributesAdder = $attributesAdder;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentById(int $id): ?DocumentInterface
    {
        if (! $id) {
            return null;
        }
        $post = get_post($id);

        if (! $post instanceof WP_Post || ! get_post_meta($id, WpPostMetaFields::WP_POST_DOCUMENT_TYPE)) {
            return null;
        }

        try {
            return $this->documentFactory->createDocument($post);
        } catch (XmlApiException $exception) {
            return null;
        }
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
                        'compare' => 'EXISTS',
                    ],
                ],
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
                'meta_value' => $type,
            ]
        );

        return array_map([$this->documentFactory, 'createDocument'], $foundPosts);
    }

    /**
     * @inheritDoc
     */
    public function getDocumentsForWcEmail(): array
    {
        $foundPosts = get_posts(
            [
                'numberposts' => -1,
                'post_type' => 'wp_block',
                'meta_key' => WpPostMetaFields::WP_POST_DOCUMENT_FLAG_ATTACH_TO_WC_EMAIL,
                'meta_value' => '1',
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
                        'value' => $language,
                    ],
                ],
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
                WpPostMetaFields::WP_POST_DOCUMENT_FLAG_HIDE_TITLE => $documentSettings->getHideTitle(),
            ],
        ];
        remove_filter('content_save_pre', 'wp_filter_post_kses');

        $result = $document->getSettings()->getDocumentId() ?
            wp_update_post($args, true) : //we need wp_update_post instead of wp_insert_post to preserve original post_date.
            wp_insert_post($args, true);

        add_filter('content_save_pre', 'wp_filter_post_kses');

        if (is_wp_error($result)) {
            throw new GeneralException(
                sprintf(
                    'Failed to save the post, WP_Error received when tried: %1$s',
                    $result->get_error_message()
                )
            );
        }
        $documentId = $result; //Just for clarity. If it's not a WP_Error, then it's inserted post id.

        //Add HTML attributes to document.
        //These attributes needed to hide document title if this feature is enabled in settings.
        //We need to do it after document saving, because new documents has no document id yet which we need to add as attribute value.
        if (! $this->attributesAdded($document)) { //check if already processed to avoid endless loop
            $this->processedDocuments[] = $documentId;
            $document->getSettings()->setDocumentId($documentId);
            $document = $this->attributesAdder->addAttributes($document);
            $this->saveDocument($document);
        }

        $attachmentId = $documentSettings->getPdfAttachmentId();

        if ($attachmentId) {
            $attachmentPost = get_post($attachmentId);

            if ($attachmentPost) {
                $attachmentPostData = wp_slash(get_object_vars($attachmentPost));
                $attachmentPostData['post_parent'] = $documentId;

                wp_insert_attachment($attachmentPostData);
            }
        }
        return $documentId;
    }

    /**
     * Check if HTML attributes already was added during current document saving.
     *
     * @param DocumentInterface $document
     *
     * @return bool
     */
    protected function attributesAdded(DocumentInterface $document): bool
    {
        $documentId = $document->getSettings()->getDocumentId();

        return in_array($documentId, $this->processedDocuments, true);
    }
}
