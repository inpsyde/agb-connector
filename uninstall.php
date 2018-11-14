<?php
/**
 * The uninstall routine.
 *
 * @package agb-connector
 */

// If uninstall not called from WordPress exit.
use Inpsyde\AGBConnector\Plugin;

if (! defined('WP_UNINSTALL_PLUGIN')) {
    die();
}
require_once __DIR__ . '/src/KeysInterface.php';

delete_option(Plugin::OPTION_USER_AUTH_TOKEN);
delete_option(Plugin::OPTION_TEXT_ALLOCATIONS);
