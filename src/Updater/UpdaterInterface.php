<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Updater;

/**
 * Service able to apply plugin updates
 */
interface UpdaterInterface
{
    /**
     * Update plugin to the latest version.
     */
    public function update(): void;
}
