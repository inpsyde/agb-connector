<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

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
    const VERSION = '1.1.0';

    /**
     * Option to store Text type allocation
     * Format: [
     *      'agb' => [
     *          0 => [
     *              'country' => 'DE',
     *              'language' => 'de',
     *              'pageId' => 15,
     *              'wcOrderConfirmationEmailAttachment' => true
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

        $textAllocations = get_option(self::OPTION_TEXT_ALLOCATIONS, []);
        foreach ($textAllocations as $type => $allocations) {
            foreach ($allocations as $allocation) {
                if (empty($allocation['wcOrderConfirmationEmailAttachment'])) {
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
     * Handle request from XmlApi
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

        $apiKey = get_option(self::OPTION_USER_AUTH_TOKEN, '');
        $textAllocations = get_option(self::OPTION_TEXT_ALLOCATIONS, []);
        $api = new XmlApi($apiKey, $textAllocations);

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
            $this->settings = new Settings();
        }

        return $this->settings;
    }

    /**
     * @return ShortCodes
     */
    public function shortCodes()
    {
        if (null === $this->shortCodes) {
            $this->shortCodes = new ShortCodes();
        }

        return $this->shortCodes;
    }
}
