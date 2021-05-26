<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\DocumentPageFinder;

/**
 * Service for finding pages displaying documents.
 */
class DocumentPageFinder implements DocumentFinderInterface
{

    protected $foundPosts = null;

    /**
     * The list of available plugin shortcodes.
     *
     * @var string[]
     */
    protected $shortcodes;

    public function __construct(
        array $shortcodes
    ){
        $this->shortcodes = $shortcodes;
    }

    /**
     * @inheritDoc
     */
    public function findPagesDisplayingDocument(int $documentId): array
    {
        $foundPostsWithDocuments = get_posts(
            [
                'numberposts' => -1,
                'meta_key' => 'agb_page_contain_documents',
                'fields' => 'ids',
                'post_type' =>  'any'

            ]
        );

        $foundPostIds = [];

        foreach ($foundPostsWithDocuments as $postId) {
            $documentsList = get_post_meta($postId, 'agb_page_contain_documents', true);

            if(! is_array($documentsList)){
                continue;
            }

            $documents = array_map( 'intval', $documentsList);
            if(in_array($documentId, $documents, true)){
                $foundPostIds[] = $postId;
            }
        }

        return $foundPostIds;
    }
}
