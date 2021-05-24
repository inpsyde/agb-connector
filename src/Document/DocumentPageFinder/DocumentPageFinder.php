<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\DocumentPageFinder;

use Inpsyde\AGBConnector\Plugin;

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
        return array_merge(
            $this->findTextUsageInPosts($documentId),
            $this->findTextUsageInPostsLegacy($documentId)
        );
    }

    /**
     * Return ids of the posts having at least one of the plugin shortcodes in the content.
     *
     * @param int $documentId
     *
     * @return array
     */
    protected function findTextUsageInPostsLegacy(int $documentId): array
    {
        $byAllocation = $this->findTextUsagesInPostsByAllocation($documentId);
        $byShortcode = $this->findTextUsagesWithShortcodes($documentId);
        return array_unique(array_merge($byAllocation, $byShortcode));
    }

    /**
     * Find the pages displaying documents selected from the old plugin settings.
     *
     * @param int $documentId
     *
     * @return int[]
     */
    protected function findTextUsagesInPostsByAllocation(int $documentId): array
    {
        $allocations = get_option(Plugin::OPTION_TEXT_ALLOCATIONS, []);

        $found = [];

        foreach ($allocations as $allocationsOfType){
            foreach ($allocationsOfType as $allocation){
                if(isset($allocation['pageId']) && (int) $allocation['pageId'] === $documentId) {
                    $found[] = (int) $allocation['pageId'];
                }
            }
        }

        return $found;
    }

    protected function findTextUsagesWithShortcodes(int $documentId): array
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

    protected function findTextUsageInPosts(int $documentId): array
    {
        $foundPostsWithDocuments = get_posts(
            [
                'numberposts' => -1, //todo: think about optimization or limits
                'meta_key' => 'agb_page_contain_documents',
                'fields' => 'ids'
            ]
        );

        $posts = [];

        foreach ($foundPostsWithDocuments as $postId) {
            $documents = get_post_meta($postId, 'abg_page_contain_documents', true);
            $documents = array_map( 'intval', $documents);
            if(in_array($documentId, $documents, true)){
                $posts[] = get_post($postId);
            }
        }

        return $posts;
    }
}
