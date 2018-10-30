<?php // phpcs:ignore
/**
 * Plugin Name: Terms & Conditions Connector of IT-Recht Kanzlei
 * Plugin URI: https://github.com/inpsyde/agb-connector
 * Description: Transfers legal texts from the IT-Recht Kanzlei client portal to your WordPress installation.
 * Author: Inpsyde GmbH
 * Author URI: http://inpsyde.com
 * Version: 1.1.0
 * Text Domain: agb-connector
 * Domain Path: /languages/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Requires PHP: 5.4
 */

/**
 * Class AGBConnector
 *
 * @since 1.0.0
 */
class AGBConnector
{

    /**
     * Plugin Version
     *
     * @var string
     */
    const VERSION = '1.1.0';

    /**
     * The API object
     *
     * @var AGBConnectorAPI
     */
    private $api;

    /**
     * The settings object
     *
     * @var AGBConnectorSettings
     */
    private $settings;

    /**
     * The shortcodes object
     *
     * @var AGBConnectorShortCodes
     */
    private $shortCodes;

    /**
     * Init all actions and filters
     *
     * @since 1.0.0
     */
    public function init()
    {
        add_action('wp_loaded', [$this, 'apiRequest'], PHP_INT_MAX);

        add_filter('woocommerce_email_attachments', [$this, 'attachPdfToEmail'], 99, 3);

        $shortCodes = $this->getShortCodes();
        add_action('init', [$shortCodes, 'setup']);
        add_action('vc_before_init', [$shortCodes, 'vc_maps']);

        if (! is_admin()) {
            return;
        }

        load_plugin_textdomain('agb-connector', false, dirname(plugin_basename(__FILE__)) . '/languages');

        $settings = $this->getSettings();
        add_action('admin_menu', [$settings, 'add_menu']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$settings, 'add_action_links']);
    }

    /**
     * Append Attachments to WooCommerce processing order email
     *
     * @since 1.0.0
     *
     * @param array $attachments The attachments.
     * @param string $status The status.
     * @param mixed $order The order. We only process in case its an WC_Order object.
     *
     * @return array
     */
    public function attachPdfToEmail(array $attachments, $status, $order)
    {
        $validStatuse = [
            'customer_completed_order',
            'customer_processing_order',
            'customer_invoice',
        ];
        if (! in_array($status, $validStatuse, true)) {
            return $attachments;
        }
        if (! $order instanceof \WC_Order) {
            return $attachments;
        }

        $append_email = get_option('agb_connector_wc_append_email', []);
        $uploads = wp_upload_dir();

        if (! empty($append_email['agb'])) {
            $file = trailingslashit($uploads['basedir']) . 'agb.pdf';
            if (file_exists($file)) {
                $attachments[] = $file;
            }
        }
        if (! empty($append_email['widerruf'])) {
            $file = trailingslashit($uploads['basedir']) . 'widerruf.pdf';
            if (file_exists($file)) {
                $attachments[] = $file;
            }
        }
        if (! empty($append_email['datenschutz'])) {
            $file = trailingslashit($uploads['basedir']) . 'datenschutz.pdf';
            if (file_exists($file)) {
                $attachments[] = $file;
            }
        }

        return $attachments;
    }

    /**
     * Handle request from API
     *
     * @since 1.0.0
     */
    public function apiRequest()
    {
        if (is_admin()) {
            return;
        }

        if ((defined('DOING_AJAX') && DOING_AJAX) || (defined('DOING_CRON') && DOING_CRON)) {
            return;
        }

        if (false === strstr($_SERVER['REQUEST_URI'], '/it-recht-kanzlei')) {
            return;
        }

        $xml = '';
        if (! empty($_POST['xml'])) {
            $xml = wp_unslash($_POST['xml']);
        }

        header('Content-type: application/xml; charset=utf-8', true, 200);

        remove_filter('content_save_pre', 'wp_filter_post_kses');
        $api = $this->getApi();
        echo $api->handleRequest($xml);
        die();
    }

    /**
     * Get Api instance
     *
     * @since 1.0.0
     * @return AGBConnectorAPI
     */
    public function getApi()
    {
        if (null === $this->api) {
            $apiKey = get_option('agb_connector_user_auth_token', '');
            $textTypesAllocation = get_option('agb_connector_text_types_allocation', []);
            $this->api = new AGBConnectorAPI(self::VERSION, $apiKey, $textTypesAllocation);
            $language = apply_filters('agb_connector_api_supported_language', substr(get_locale(), 0, 2));
            $this->api->setSupportedLanguage($language);
        }

        return $this->api;
    }

    /**
     * Get Plugin Settings page
     *
     * @since 1.0.0
     * @return AGBConnectorSettings
     */
    public function getSettings()
    {
        if (null === $this->settings) {
            $this->settings = new AGBConnectorSettings(self::VERSION);
        }

        return $this->settings;
    }

    /**
     * @return AGBConnectorShortCodes
     */
    public function getShortCodes()
    {
        if (null === $this->shortCodes) {
            $this->shortCodes = new AGBConnectorShortCodes();
        }

        return $this->shortCodes;
    }
}

/**
 * Function for getting plugin class
 *
 * @since 1.0.0
 * @return AGBConnector
 */
function agb_connector()
{
    static $plugin;

    if (! class_exists('AGBConnectorAPI', false) && file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
    }

    if (null === $plugin) {
        $plugin = new AGBConnector();
    }

    if ('plugins_loaded' === current_action()) {
        $plugin->init();
    }

    return $plugin;
}

/**
 * Run
 */
if (function_exists('add_action')) {
    add_action('plugins_loaded', 'agb_connector');
}

/**
 * Activation
 */
if (function_exists('register_activation_hook')) {
    register_activation_hook(__FILE__, ['AGBConnectorInstall', 'activate']);
}
