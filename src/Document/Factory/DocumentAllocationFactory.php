<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;

use Inpsyde\AGBConnector\Document\DocumentSettings;
use Inpsyde\AGBConnector\Document\DocumentSettingsInterface;
use WP_Post;

class DocumentAllocationFactory implements DocumentAllocationFactoryInterface
{

    public function createAllocationFromPost(WP_Post $post): DocumentSettingsInterface
    {
        //todo: add properties.
        return new DocumentSettings();
    }

    public function createAllocationFromArray(array $allocationData): DocumentSettingsInterface
    {
        //todo: add properties.
        return new DocumentSettings();
    }
}
