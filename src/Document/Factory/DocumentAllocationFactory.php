<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;

use Inpsyde\AGBConnector\Document\DocumentAllocation;
use Inpsyde\AGBConnector\Document\DocumentAllocationInterface;
use WP_Post;

class DocumentAllocationFactory implements DocumentAllocationFactoryInterface
{

    public function createAllocationFromPost(WP_Post $post): DocumentAllocationInterface
    {
        //todo: add properties.
        return new DocumentAllocation();
    }

    public function createAllocationFromArray(array $allocationData): DocumentAllocationInterface
    {
        //todo: add properties.
        return new DocumentAllocation();
    }
}
