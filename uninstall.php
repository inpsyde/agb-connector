<?php
/**
 * The uninstall routine.
 */
use Inpsyde\AGBConnector\Plugin;

if (! defined('WP_UNINSTALL_PLUGIN')) {
    die();
}
require_once __DIR__ . '/src/Plugin.php';

delete_option(Plugin::OPTION_USER_AUTH_TOKEN);
delete_option(Plugin::OPTION_TEXT_ALLOCATIONS);
