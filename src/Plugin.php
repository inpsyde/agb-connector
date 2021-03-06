<?php

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentFinderInterface;
use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentPageFinder;
use Inpsyde\AGBConnector\Document\Factory\WpPostBasedDocumentFactory;
use Inpsyde\AGBConnector\Document\Factory\WpPostBasedDocumentFactoryInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepository;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use Inpsyde\AGBConnector\Document\View\RemoveDocumentPageTitleIfEnabled;
use Inpsyde\AGBConnector\Settings\DocumentsTable;
use Inpsyde\AGBConnector\Updater\Updater;
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
    const VERSION = '3.0.0';

    /**
     * Option to store Text type allocation (used before version 3.0.0)
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
     * Type of the Gutenberg block containing Documents.
     */
    const DOCUMENT_BLOCK_TYPE = 'agb-connector/agb-document';
    /**
     * @var string
     */
    protected $pluginFilePath;

    /**
     * The settings object
     *
     * @var Settings
     */
    private $settings;

    /**
     * @var DocumentRepositoryInterface
     */
    private $documentRepository;

    /**
     * The shortcodes object
     *
     * @var ShortCodes
     */
    private $shortCodes;
    /**
     * @var WpPostBasedDocumentFactory
     */
    private $postBasedDocumentFactory;

    /**
     * @var DocumentFinderInterface
     */
    protected $documentPageFinder;

    /**
     * @var DocumentsTable
     */
    protected $documentsTable;

    /**
     * Path to the main plugin file.
     *
     * @param string $pluginFilePath
     */
    public function __construct(string $pluginFilePath)
    {
        $this->pluginFilePath = $pluginFilePath;
    }

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

        add_action('init', function () use ($shortCodes) {
            (new PostSavingListener($this->documentRepository(), $shortCodes))->init();
            (new PostDeleteListener())->init();
        });

        (new RemoveDocumentPageTitleIfEnabled($this->documentRepository()))();

        if (! is_admin()) {
            return;
        }

        if (! wp_doing_ajax()) {
            add_action('admin_init', [$this, 'update']);
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
    public function attachPdfToEmail($attachments, $status, $order): array
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

        $documentsToAttach = $this->documentRepository->getDocumentsForWcEmail();

        foreach ($documentsToAttach as $document) {
            $pdfAttachmentId = $document->getSettings()->getPdfAttachmentId();
            if (! $pdfAttachmentId) {
                continue;
            }

            $pdfAttachment = get_attached_file($pdfAttachmentId);
            if ($pdfAttachment) {
                $attachments[] = $pdfAttachment;
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
        if (false === strpos($requestUri, 'it-recht-kanzlei')) {
            return;
        }

        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }

        add_filter('w3tc_can_print_comment', '__return_false', 10, 1);

        $xml = filter_input(INPUT_POST, 'xml');
        $xml = wp_unslash($xml);

        $apiKey = get_option(self::OPTION_USER_AUTH_TOKEN, '');
        $api = new XmlApi($apiKey, $this->documentRepository);

        nocache_headers();
        header('Content-type: application/xml; charset=utf-8', true, 200);
        die($api->handleRequest($xml)); //phpcs:ignore
    }

    /**
     * Get Plugin settings page
     *
     * @return Settings
     */
    public function settings(): Settings
    {
        if (null === $this->settings) {
            $this->settings = new Settings(
                $this->documentRepository(),
                $this->documentPageFinder(),
                $this->shortCodes()
            );

            $this->settings->init();
        }

        return $this->settings;
    }

    /**
     * Get document repository.
     *
     * @return DocumentRepositoryInterface
     */
    public function documentRepository(): DocumentRepositoryInterface
    {
        if (null === $this->documentRepository) {
            $this->documentRepository = new DocumentRepository(
                $this->postBasedDocumentFactory()
            );
        }

        return $this->documentRepository;
    }

    /**
     * Return Post-based document factory.
     *
     * @return WpPostBasedDocumentFactoryInterface
     */
    public function postBasedDocumentFactory(): WpPostBasedDocumentFactoryInterface
    {
        if (null === $this->postBasedDocumentFactory) {
            $this->postBasedDocumentFactory = new WpPostBasedDocumentFactory();
        }

        return $this->postBasedDocumentFactory;
    }

    public function documentPageFinder(): DocumentFinderInterface
    {
        if (null === $this->documentPageFinder) {
            $this->documentPageFinder = new DocumentPageFinder($this->shortCodes()->getShortcodeTags());
        }

        return $this->documentPageFinder;
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
                $supportedConfig->supportedLanguages(),
                $this->documentRepository()
            );
        }

        return $this->shortCodes;
    }

    /**
     * Update DB to the latest version.
     */
    public function update(): void
    {
        $allocations = get_option(self::OPTION_TEXT_ALLOCATIONS, []);

        if (! is_array($allocations)) {
            return;
        }

        $updater = new Updater(
            $this->documentPageFinder(),
            $this->documentRepository(),
            $this->postBasedDocumentFactory(),
            $this->shortCodes(),
            $allocations
        );

        $updater->update();
    }

    /**
     * Return path to the main plugin file.
     *
     * @return string
     */
    public function pluginFilePath(): string
    {
        return $this->pluginFilePath;
    }
}
