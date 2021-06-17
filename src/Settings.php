<?php

declare(strict_types=1);

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\CustomExceptions\GeneralException;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentFinderInterface;
use Inpsyde\AGBConnector\Document\Map\WpPostMetaFields;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepository;
use Inpsyde\AGBConnector\Settings\DocumentsTable;
use InvalidArgumentException;

/**
 * Class Settings
 */
class Settings
{

    const AJAX_ACTION = 'agb-update-document-settings';

    const MIGRATION_FAILED_FLAG_OPTION_NAME = 'agbc_flag_migration_failed';

    /**
     * @var DocumentRepository
     */
    protected $repository;

    /**
     * @var DocumentFinderInterface
     */
    protected $documentPageFinder;
    /**
     * @var ShortCodes
     */
    protected $shortCodes;

    /**
     * @var string
     */
    protected $menuPageSlug;

    /**
     * The save message.
     *
     * @var string
     */
    private $message = '';

    /**
     * Message to be displayed if migration failed.
     *
     * @var string
     */
    protected $migrationFailedMessage = '';

    /**
     * @param DocumentRepository $repository
     * @param DocumentFinderInterface $documentPageFinder
     * @param ShortCodes $shortCodes
     */
    public function __construct(
        DocumentRepository $repository,
        DocumentFinderInterface $documentPageFinder,
        ShortCodes $shortCodes
    ) {

        $this->repository = $repository;
        $this->documentPageFinder = $documentPageFinder;
        $this->migrationFailedMessage = sprintf(
            __(
                'AGB Connector couldn\'t migrate your documents from the old plugin version. Please, contact %1$sInpsyde support%2$s for help',
                'agb-connector'
            ),
            '<a href="mailto:agb-connector@inpsyde.com" target="_blank">',
            '</a>'
        );
        $this->shortCodes = $shortCodes;
        $this->menuPageSlug = 'agb_connector_settings';
    }

    public function init()
    {
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'handleAjaxRequest']);
        add_action('before_delete_post', [$this, 'filterRedirectAfterDocumentDeleted']);
    }

    /**
     * Update document settings on AJAX request.
     */
    public function handleAjaxRequest(): void
    {
        check_admin_referer(self::AJAX_ACTION, 'nonce');

        $documentId = (int) filter_input(INPUT_POST, 'documentId', FILTER_SANITIZE_NUMBER_INT);
        $document = $this->repository->getDocumentById($documentId);

        if ($document === null) {
            wp_send_json_error(
                [
                    'message' => __('Document not found.', 'agb-connector'),
                ]
            );
        }

        $fieldName = filter_input(INPUT_POST, 'fieldName', FILTER_SANITIZE_STRING);
        $fieldValue = filter_input(INPUT_POST, 'fieldValue', FILTER_VALIDATE_BOOLEAN);

        try {
            $this->updateDocumentSettings($document, $fieldName, $fieldValue);
        } catch (InvalidArgumentException $exception) {
            wp_send_json_error(['message' => $exception->getMessage()]);
        }

        wp_send_json_success(['nonce' => wp_create_nonce(self::AJAX_ACTION)]);
    }

    /**
     * Add the menu entry
     */
    public function addMenu()
    {
        $hook = add_options_page(
            __('Terms & Conditions Connector of IT-Recht Kanzlei', 'agb-connector'),
            'AGB Connector',
            'edit_pages',
            $this->menuPageSlug,
            [
                $this,
                'page',
            ]
        );

        if ($hook !== false) {
            add_action('load-' . $hook, [$this, 'load']);
        }
    }

    /**
     * Add settings link to plugin actions.
     *
     * @param array $links The links.
     *
     * @return array
     */
    public function addActionLinks($links)
    {
        $addLinks = [
            '<a href="' . admin_url('options-general.php?page=agb_connector_settings') . '">' . __(
                'Settings',
                'agb-connector'
            ) . '</a>',
        ];

        return array_merge($addLinks, $links);
    }

    /**
     * All things must done in load
     *
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function load()
    {
        wp_enqueue_style(
            'agb-connector',
            plugins_url('/assets/css/style.css', __DIR__),
            [],
            Plugin::VERSION,
            'all'
        );

        wp_enqueue_script(
            'agb-connector',
            plugins_url('/assets/js/settings.js', __DIR__),
            ['jquery'],
            Plugin::VERSION,
            true
        );

        wp_localize_script('agb-connector', 'agbConnectorSettings', [
            'action' => self::AJAX_ACTION,
            'nonce' => wp_create_nonce(self::AJAX_ACTION),
            ]);

        $getRegen = filter_input(INPUT_GET, 'regen', FILTER_SANITIZE_NUMBER_INT);
        if (null !== $getRegen) {
            check_admin_referer('agb-connector-settings-page-regen');
            $userAuthToken = md5(wp_generate_password(32, true, true));
            update_option(Plugin::OPTION_USER_AUTH_TOKEN, $userAuthToken);
            $this->message = __('New APT-Token generated.', 'agb-connector');
        }
    }

    /**
     * The settings page content
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function page()
    {
        $table = new DocumentsTable(
            $this->repository,
            $this->documentPageFinder,
            $this->shortCodes,
            [
                'singular' => __('Document', 'agb-connector'),
                'plural' => __('Documents', 'agb-connector'),
                'ajax' => true,
            ]
        );

        ?>
        <div class="wrap" id="agb-connector-settings">
            <h2>
                <?php
                printf(
                    '%s &rsaquo; %s',
                    esc_html__('Settings', 'agb-connector'),
                    esc_html__('Terms & Conditions Connector of IT-Recht Kanzlei', 'agb-connector')
                );
                ?>
            </h2>
            <div class="box-container">
                <div id="it-recht-kanzlei" class="metabox-holder postbox">
                    <div class="inside">
                        <a href="https://www.it-recht-kanzlei.de" class="it-kanzlei-logo"
                           title="IT-Recht Kanzlei München">IT-Recht Kanzlei München</a><br>
                    </div>
                </div>
                <div id="inpsyde" class="metabox-holder postbox">
                    <div class="inside">
                        <a href="
                        <?php esc_html_e(
                            'https://inpsyde.com/en/?utm_source=Plugin&utm_medium=Banner&utm_campaign=Inpsyde',
                            'agb-connector'
                        ); ?>
                        " class="inpsyde-logo" title="
                        <?php esc_html_e('An Inpsyde GmbH Product', 'agb-connector'); ?>
                        ">Inpsyde GmbH</a>
                        <br>
                    </div>
                </div>
                <div id="inpsyde" class="metabox-holder postbox">
                    <div class="inside">
                        <h3>Support</h3>
                        <p>
                            <?php esc_html_e(
                                'If you have questions please contact “IT-Recht Kanzlei München” directly',
                                'agb-connector'
                            ); ?>
                            <br/>
                            <?php esc_html_e('via +49 89 13014330 or ', 'agb-connector'); ?>
                            <a href="mailto:info@it-recht-kanzlei.de">info@it-recht-kanzlei.de</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="settings-container">
                <?php
                if ($this->message) {
                    echo '<div id="message" class="updated"><p>' . esc_html($this->message) . '</p></div>';
                }
                if (get_option(self::MIGRATION_FAILED_FLAG_OPTION_NAME, false)) {
                    echo  '<div id="agbc-migration-failed" class="notice notice-warning"><p>' . wp_kses_post($this->migrationFailedMessage) . '</p></div>';
                }
                ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="regen">
                                    <?php esc_html_e('API-Token', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td>
                                <p>
                                    <code><?php echo esc_attr(get_option(Plugin::OPTION_USER_AUTH_TOKEN)); ?></code>
                                    <a class="button" href="
                                    <?php echo esc_url(
                                        wp_nonce_url(
                                            add_query_arg(
                                                [
                                                    'regen' => '',
                                                    'page' => 'agb_connector_settings',
                                                ],
                                                admin_url('options-general.php')
                                            ),
                                            'agb-connector-settings-page-regen'
                                        )
                                    );
                                    ?>">
                                        <?php esc_html_e('Regenerate', 'agb-connector'); ?>
                                    </a>
                                </p>
                                <p class="description">
                                    <?php esc_html_e(
                                        'If you change the token, this must also be adjusted in the client portal.',
                                        'agb-connector'
                                    ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="regen">
                                    <?php esc_html_e('Your shop URL', 'agb-connector'); ?>
                                </label>
                            </th>
                            <td>
                                <p>
                                    <code>
                                        <?php
                                            //Directly use option that that WPML can't change it
                                            $homeUrl = trailingslashit(get_option('home'));
                                            $homeUrl = set_url_scheme($homeUrl);
                                            echo esc_attr($homeUrl);
                                        ?>
                                    </code>
                                </p>
                            </td>
                        </tr>
                    </table>
            </div>
            <?php
                $table->prepare_items();

                echo '<form method="post">';
                $table->display();
                echo '</form>';
            ?>
        </div>
        <?php
    }

    /**
     * Update document settings.
     *
     * @param DocumentInterface $document
     * @param string $fieldName
     * @param bool $fieldValue
     *
     * @throws InvalidArgumentException If document settings field not found.
     * @throws GeneralException
     */
    protected function updateDocumentSettings(DocumentInterface $document, string $fieldName, bool $fieldValue): void
    {
        switch ($fieldName) {
            case 'store_pdf':
                $document->getSettings()->setSavePdf($fieldValue);
                if (! $fieldValue) {
                    $document->getSettings()->setAttachToWcEmail(false);
                }
                break;
            case 'attach_pdf_to_wc':
                $document->getSettings()->setAttachToWcEmail($fieldValue);
                break;
            case 'hide_title':
                $document->getSettings()->setHideTitle($fieldValue);
                break;
            default:
                throw new InvalidArgumentException(
                    __('Failed to update document: no such field found.', 'agb-connector')
                );
        }

        $this->repository->saveDocument($document);
    }

    /**
     * Filter redirect url after document deletion.
     *
     * @param int $postId Deleted document id
     */
    public function filterRedirectAfterDocumentDeleted($postId): void
    {
        $isDocument = get_post_meta($postId, WpPostMetaFields::WP_POST_DOCUMENT_TYPE);

        if (! $isDocument) {
            return;
        }

        add_filter('wp_redirect', function () {
            return menu_page_url($this->menuPageSlug, false);
        });
    }
}
