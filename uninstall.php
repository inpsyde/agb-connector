<?php
/**
 * The uninstall routine.
 *
 * @package agb-connector
 */

// If uninstall not called from WordPress exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    die();
}
require_once __DIR__ . '/src/AGBConnectorKeysInterface.php';

delete_option(AGBConnectorKeysInterface::OPTION_USER_AUTH_TOKEN);
delete_option(AGBConnectorKeysInterface::OPTION_TEXT_ALLOCATIONS);
