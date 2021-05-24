<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\DocumentPageFinder;

use Inpsyde\AGBConnector\Plugin;

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
                'numberposts' => -1, //todo: think about optimization or limits
                'meta_key' => 'agb_page_contain_documents',
                'fields' => 'ids'
            ]
        );

        $foundPostIds = [];

        foreach ($foundPostsWithDocuments as $postId) {
            $documents = get_post_meta($postId, 'abg_page_contain_documents', true);
            $documents = array_map( 'intval', $documents);
            if(in_array($documentId, $documents, true)){
                $foundPostIds[] = $postId;
            }
        }

        return $foundPostIds;
    }

    /**
     * @inheritDoc
     */
    public function findAllPostsDisplayingDocuments(): array
    {
        $byAllocation = $this->findAllPostsDisplayingDocumentsByAllocation();
        $byShortcode = $this->findAllPostsDisplayingDocumentsByShortcode();
        return array_unique(array_merge($byAllocation, $byShortcode));
    }

    /**
     * Find the pages displaying documents selected from the old plugin settings.
     *
     * @return int[]
     */
    protected function findAllPostsDisplayingDocumentsByAllocation(): array
    {
        $allocations = get_option(Plugin::OPTION_TEXT_ALLOCATIONS, []);

        $found = [];

        foreach ($allocations as $allocationsOfType){
            $foundPortion = array_column($allocationsOfType, 'pageId');
            $found = array_merge($found, $foundPortion);
        }

        return $found;
    }

    protected function findAllPostsDisplayingDocumentsByShortcode(): array
    {
        $foundPosts = [];
        $args = [
            'numberposts' => '-1',
            'fields' => 'ids',
            'post_status' => 'publish'
        ];

        foreach($this->shortcodes as $shortcode){
            $args['s'] = $shortcode;

            $postsWithShortcodeText = get_posts($args);

            $postsWithShortcode = array_filter($postsWithShortcodeText, function ($postId) use ($shortcode){
                $postContent = get_post_field('post_content', $postId);
                return has_shortcode($postContent, $shortcode);
            });


            $foundPosts = array_merge($foundPosts, $postsWithShortcode);
        }

        return $foundPosts;
    }
}
