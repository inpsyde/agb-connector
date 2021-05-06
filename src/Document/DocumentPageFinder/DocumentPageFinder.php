<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\DocumentPageFinder;

use WP_Post;

class DocumentPageFinder implements DocumentFinderInterface
{

    protected $posts = null;

    /**
     * The list of available plugin shortcodes.
     *
     * @var string[]
     */
    protected $shortcodes;

    /**
     * The list of documents ids.
     *
     * @var int[]
     */
    protected $documentsIds;
    /**
     * @var string
     */
    protected $blockName;

    protected function __construct(
        array $shortcodes,
        array $documentsIds,
        string $blockName
    ){

        $this->shortcodes = $shortcodes;
        $this->documentsIds = $documentsIds;
        $this->blockName = $blockName;
    }

    /**
     * @inheritDoc
     */
    public function findPagesDisplayingDocument(int $documentId): array
    {
        return array_merge(
            $this->findTextUsageWithShortcode(),
            $this->findTextUsageWithBlock()
        );
    }

    /**
     * Return ids of the posts having at least one of the plugin shortcodes in the content.
     *
     * @return array
     */
    protected function findTextUsageWithShortcode(): array
    {
        $posts = $this->getPostsToSearchIn();

        return $this->filterPostsContainingPluginShortcodes($posts);
    }

    protected function findTextUsageWithBlock(): array
    {
        $posts = $this->getPostsToSearchIn();

        $postsWithBlock = [];

        foreach ($posts as $post) {
            if(has_block($this->blockName)) {
                $postsWithBlock[] = $post;
            }
        }

        return array_map(function(WP_Post $post){
            return $post->ID;
        }, $postsWithBlock);
    }

    /**
     * Get list of posts to look for the plugin shortcodes.
     *
     * @return WP_Post[]
     */
    protected function getPostsToSearchIn(): array
    {
        if($this->posts === null) {
            $this->posts = get_posts( //todo: find a more efficient way to check all the posts
                ['numberposts' => -1]
            );
        }

        return $this->posts;
    }

    /**
     * Return ids of the posts containing plugin shortcodes.
     *
     * @param WP_Post[] $posts
     *
     * @return int[]
     */
    protected function filterPostsContainingPluginShortcodes(array $posts): array
    {
        $postsWithShortcodes = array_map(function (WP_Post $post): ?int {
            foreach ($this->shortcodes as $shortcode) {
                $content = is_string($post->post_content) ? $post->post_content : '';
                if (has_shortcode($content, $shortcode)) {
                    return $post->ID;
                }
            }
            return null;
        }, $posts);

        return array_filter($postsWithShortcodes);
    }
}
