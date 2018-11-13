<?php // phpcs:ignore
/**
 * phpcs:disable Inpsyde.CodeQuality.LineLength.TooLong
 *
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

//phpcs:disable Inpsyde.CodeQuality.Psr4.WrongFilename

/**
 * Class AGBConnector
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
     */
    public function init()
    {
        AGBConnectorInstall::install();

        add_action('wp_loaded', [$this, 'apiRequest'], PHP_INT_MAX);

        add_filter('woocommerce_email_attachments', [$this, 'attachPdfToEmail'], 99, 3);

        $shortCodes = $this->shortCodes();
        add_action('init', [$shortCodes, 'setup']);
        add_action('vc_before_init', [$shortCodes, 'vcMaps']);

        if (! is_admin()) {
            return;
        }

        load_plugin_textdomain('agb-connector', false, dirname(plugin_basename(__FILE__)) . '/languages');

        $settings = $this->settings();
        add_action('admin_menu', [$settings, 'addMenu']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$settings, 'addActionLinks']);
    }

    /**
     * Append Attachments to WooCommerce processing order email
     *
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
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

        $textAllocations = get_option(AGBConnectorKeysInterface::OPTION_TEXT_ALLOCATIONS, []);
        foreach ($textAllocations as $type => $allocations) {
            foreach ($allocations as $allocation) {
                if (empty($allocation['wcOrderConfirmationEmailAttachment'])) {
                    continue;
                }
                $attachmentId = AGBConnectorAPI::attachmentIdByPostParent($allocation['pageId']);
                $pdfAttachment = get_attached_file($attachmentId);
                if ($pdfAttachment) {
                    $attachments[] = $pdfAttachment;
                }
            }
        }

        return $attachments;
    }

    /**
     * Handle request from API
     */
    public function apiRequest()
    {
        if ((defined('DOING_AJAX') && DOING_AJAX) || (defined('DOING_CRON') && DOING_CRON) || is_admin()) {
            return;
        }

        $requestUri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL); //phpcs:ignore
        if (false === strpos($requestUri, '/it-recht-kanzlei')) {
            return;
        }

        $xml = filter_input(INPUT_POST, 'xml');
        $xml = wp_unslash($xml);

        $apiKey = get_option(AGBConnectorKeysInterface::OPTION_USER_AUTH_TOKEN, '');
        $textAllocations = get_option(AGBConnectorKeysInterface::OPTION_TEXT_ALLOCATIONS, []);
        $api = new AGBConnectorAPI(self::VERSION, $apiKey, $textAllocations);

        header('Content-type: application/xml; charset=utf-8', true, 200);
        die($api->handleRequest($xml)); //phpcs:ignore
    }

    /**
     * Get Plugin settings page
     *
     * @return AGBConnectorSettings
     */
    public function settings()
    {
        if (null === $this->settings) {
            $this->settings = new AGBConnectorSettings(self::VERSION);
        }

        return $this->settings;
    }

    /**
     * @return AGBConnectorShortCodes
     */
    public function shortCodes()
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
 * phpcs:disable NeutronStandard.Globals.DisallowGlobalFunctions.GlobalFunctions
 *
 * @return AGBConnector
 */
function agb_connector()
{
    static $plugin;

    if (! class_exists('AGBConnectorKeysInterface', false)) {
        require_once __DIR__ . '/src/AGBConnectorKeysInterface.php';
        require_once __DIR__ . '/src/AGBConnectorInstall.php';
        require_once __DIR__ . '/src/AGBConnectorSettings.php';
        require_once __DIR__ . '/src/AGBConnectorAPI.php';
        require_once __DIR__ . '/src/AGBConnectorShortCodes.php';
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
