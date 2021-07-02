<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\DocumentPageFinder;

use WP_Post;

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
    ) {

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
                'post_type' =>  'any',

            ]
        );

        $foundPostIds = [];

        foreach ($foundPostsWithDocuments as $postId) {
            if (! $this->isPostPubliclyViewable($postId)) {
                continue;
            }

            $documentsList = get_post_meta($postId, 'agb_page_contain_documents', true);

            if (! is_array($documentsList)) {
                continue;
            }

            $documents = array_map('intval', $documentsList);
            if (in_array($documentId, $documents, true)) {
                $foundPostIds[] = $postId;
            }
        }

        return $foundPostIds;
    }

    /**
     * Check if post publicly viewable (has both viewable post type and post status).
     *
     * Does the same check as is_post_publicly_viewable() function. We cannot use it because it was
     * added in the WordPress 5.7, but we are supporting older versions.
     *
     * @param int $postId
     *
     * @return bool
     */
    protected function isPostPubliclyViewable(int $postId): bool
    {
        $post = get_post($postId);

        if (! $post instanceof WP_Post) {
            return false;
        }

        return $this->isPostTypeViewable($post) && $this->isPostStatusViewable($post);
    }

    /**
     * Check whether the type of the given post is publicly viewable.
     *
     * @param WP_Post $post Post to check.
     *
     * @return bool The check result.
     */
    protected function isPostTypeViewable(WP_Post $post): bool
    {
        $postType = get_post_type($post);

        return is_string($postType) && is_post_type_viewable($postType);
    }

    /**
     * Check if post status viewable.
     *
     * @param WP_Post $post
     *
     * @return bool
     */
    protected function isPostStatusViewable(WP_Post $post): bool
    {
        $postStatus = get_post_status($post);

        if (! is_string($postStatus)) {
            return false;
        }

        $postStatusObject = get_post_status_object($postStatus);

        if (! is_object($postStatusObject) ||
            $postStatusObject->internal ||
            $postStatusObject->protected
        ) {
            return false;
        }

        return $postStatusObject->publicly_queryable ||
            ( $postStatusObject->_builtin && $postStatusObject->public );
    }
}
