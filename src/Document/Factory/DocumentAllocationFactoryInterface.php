<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;


use Inpsyde\AGBConnector\Document\DocumentSettingsInterface;
use WP_Post;

/**
 * Service able to create new DocumentSettings instances.
 *
 * Interface DocumentAllocationFactoryInterface
 *
 * @package Inpsyde\AGBConnector\Document\Factory
 */
interface DocumentAllocationFactoryInterface
{
    public function createAllocationFromPost(WP_Post $post): DocumentSettingsInterface;

    public function createAllocationFromArray(array $allocationData): DocumentSettingsInterface;
}
