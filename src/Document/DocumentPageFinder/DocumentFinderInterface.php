<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\DocumentPageFinder;


interface DocumentFinderInterface
{
    /**
     * Find all the pages displaying given document.
     *
     * Checks both ways of including document to the page: using WP blocks and shortcodes.
     *
     * @param int $documentId
     *
     * @return int[] Found pages ids
     */
    function findPagesDisplayingDocument(int $documentId): array;

    /**
     * Find all the posts displaying any AGB document in any way (shortcode, wp_block,
     *  allocations from previous plugin versions).
     *
     * May take more time for processing than usual requests and shouldn't be used for them. It's recommended
     * to run this with background processing or WP_CLI commands.
     *
     * @return int[] Found pages ids
     */
    public function findAllPostsDisplayingDocuments(): array;
}
