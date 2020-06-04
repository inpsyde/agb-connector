<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

use WC_Order;

/**
 * Class Plugin
 */
class Plugin
{

    /**
     * Plugin Version
     *
     * @var string
     */
    const VERSION = '2.1.0';

    /**
     * Option to store Text type allocation
     * Format: [
     *      'agb' => [
     *          0 => [
     *              'country' => 'DE',
     *              'language' => 'de',
     *              'pageId' => 15,
     *              'wcOrderEmailAttachment' => true,
     *              'savePdfFile' => true
     *          ]
     *      ]
     * ]
     */
    const OPTION_TEXT_ALLOCATIONS = 'agb_connector_text_allocations';

    /**
     * Option to store the auth token
     * Format: string
     */
    const OPTION_USER_AUTH_TOKEN = 'agb_connector_user_auth_token';

    /**
     * The settings object
     *
     * @var Settings
     */
    private $settings;

    /**
     * The shortcodes object
     *
     * @var ShortCodes
     */
    private $shortCodes;

    /**
     * Init all actions and filters
     */
    public function init()
    {
        Install::activate();

        add_action('wp_loaded', [$this, 'apiRequest'], PHP_INT_MAX);

        add_filter('woocommerce_email_attachments', [$this, 'attachPdfToEmail'], 99, 3);

        $shortCodes = $this->shortCodes();
        add_action('init', [$shortCodes, 'setup']);
        add_action('vc_before_init', [$shortCodes, 'vcMaps']);

        if (! is_admin()) {
            return;
        }

        $settings = $this->settings();
        add_action('admin_menu', [$settings, 'addMenu']);
        add_filter(
            'plugin_action_links_' . plugin_basename(dirname(__DIR__) . '/agb-connector.php'),
            [$settings, 'addActionLinks']
        );
    }

    /**
     * Append Attachments to WooCommerce customer order emails
     *
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     *
     * @param array $attachments The attachments.
     * @param string $status The status.
     * @param mixed $order The order. We only process in case its an WC_Order object.
     *
     * @return array
     */
    public function attachPdfToEmail($attachments, $status, $order)
    {
        $validStatuses = [
            'customer_on_hold_order',
            'customer_processing_order',
            'customer_completed_order',
            'customer_refunded_order',
            'customer_invoice',
        ];
        if (! $order instanceof WC_Order || ! in_array($status, $validStatuses, true)) {
            return $attachments;
        }

        $textAllocations = get_option(self::OPTION_TEXT_ALLOCATIONS, []);
        foreach ($textAllocations as $type => $allocations) {
            foreach ($allocations as $allocation) {
                if (empty($allocation['wcOrderEmailAttachment'])) {
                    continue;
                }
                $attachmentId = XmlApi::attachmentIdByPostParent($allocation['pageId']);
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

        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }

        add_filter('w3tc_can_print_comment', '__return_false', 10, 1);

        $xml = filter_input(INPUT_POST, 'xml');
        $xml = wp_unslash($xml);

        $apiKey = get_option(self::OPTION_USER_AUTH_TOKEN, '');
        $textAllocations = get_option(self::OPTION_TEXT_ALLOCATIONS, []);
        $api = new XmlApi($apiKey, $textAllocations);

        nocache_headers();
        header('Content-type: application/xml; charset=utf-8', true, 200);
        die($api->handleRequest($xml)); //phpcs:ignore
    }

    /**
     * Get Plugin settings page
     *
     * @return Settings
     */
    public function settings()
    {
        if (null === $this->settings) {
            $supportedConfig = new XmlApiSupportedService();
            $this->settings = new Settings(
                $supportedConfig->supportedCountries(),
                $supportedConfig->supportedLanguages(),
                $supportedConfig->supportedTextTypes()
            );
        }

        return $this->settings;
    }

    /**
     * @return ShortCodes
     */
    public function shortCodes()
    {
        if (null === $this->shortCodes) {
            $supportedConfig = new XmlApiSupportedService();
            $this->shortCodes = new ShortCodes(
                $supportedConfig->supportedCountries(),
                $supportedConfig->supportedLanguages()
            );
        }

        return $this->shortCodes;
    }
}
