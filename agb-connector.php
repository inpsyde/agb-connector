<?php // phpcs:ignore
/**
 * phpcs:disable Inpsyde.CodeQuality.LineLength.TooLong
 *
 * Plugin Name: Terms & Conditions Connector of IT-Recht Kanzlei
 * Plugin URI: https://github.com/inpsyde/agb-connector
 * Description: Transfers legal texts from the IT-Recht Kanzlei client portal to your WordPress installation.
 * Author: Inpsyde GmbH
 * Author URI: http://inpsyde.com
 * Version: 2.0.2
 * Text Domain: agb-connector
 * License: GPLv2+
 */

/**
 * Function for getting plugin class
 *
 * phpcs:disable NeutronStandard.Globals.DisallowGlobalFunctions.GlobalFunctions
 *
 * @return Inpsyde\AGBConnector\Plugin
 */
function agb_connector()
{
    static $plugin;

    if (null !== $plugin) {
        return $plugin;
    }

    if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50400) {
        return null;
    }

    $pluginClassName = 'Inpsyde\AGBConnector\Plugin';
    /** @var Inpsyde\AGBConnector\Plugin $plugin */

    if (! class_exists($pluginClassName)) {
        require __DIR__.'/vendor/autoload.php';
    }

    $plugin = new $pluginClassName();
    $plugin->init();

    return $plugin;
}

/**
 * Run
 */
if (function_exists('add_action')) {
    add_action('plugins_loaded', 'agb_connector');
}
