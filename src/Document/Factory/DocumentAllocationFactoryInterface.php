<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;


use Inpsyde\AGBConnector\Document\DocumentAllocationInterface;
use WP_Post;

/**
 * Service able to create new DocumentAllocation instances.
 *
 * Interface DocumentAllocationFactoryInterface
 *
 * @package Inpsyde\AGBConnector\Document\Factory
 */
interface DocumentAllocationFactoryInterface
{
    public function createAllocationFromPost(WP_Post $post): DocumentAllocationInterface;

    public function createAllocationFromArray(array $allocationData): DocumentAllocationInterface;
}
