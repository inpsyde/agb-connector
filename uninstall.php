<?php
/**
 * The uninstall routine.
 */
use Inpsyde\AGBConnector\Plugin;

if (! defined('WP_UNINSTALL_PLUGIN')) {
    die();
}

if (! class_exists('Inpsyde\AGBConnector\Plugin')) {
    require_once __DIR__ . '/src/Plugin.php';
}

delete_option(Plugin::OPTION_USER_AUTH_TOKEN);
delete_option(Plugin::OPTION_TEXT_ALLOCATIONS);
