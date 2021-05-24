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
}
