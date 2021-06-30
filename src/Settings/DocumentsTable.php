<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Settings;

use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentFinderInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use Inpsyde\AGBConnector\ShortCodes;
use WP_List_Table;
use WP_Post;

class DocumentsTable extends WP_List_Table
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;
    /**
     * @var DocumentFinderInterface
     */
    protected $documentFinder;
    /**
     * @var ShortCodes
     */
    protected $shortCodes;

    /**
     * DocumentsTable constructor.
     *
     * @param DocumentRepositoryInterface $documentRepository
     * @param DocumentFinderInterface $documentFinder
     * @param ShortCodes $shortCodes
     * @param array $args
     */
    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        DocumentFinderInterface $documentFinder,
        ShortCodes $shortCodes,
        array $args = []
    ) {

        $this->documentRepository = $documentRepository;

        parent::__construct($args);
        $this->documentFinder = $documentFinder;
        $this->shortCodes = $shortCodes;
    }

    /**
     * @inheritDoc
     */
    public function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'agb-column-title' => __('Title', 'agb-connector'),
            'agb-column-type' => __('Type', 'agb-connector'),
            'agb-column-country-language' => __('Country/Language', 'agb-connector'),
            'agb-column-page' => __('Displayed at', 'agb-connector'),
            'agb-column-hide_title' => __('Hide page title', 'agb-connector'),
            'agb-column-store_pdf' => __('Store PDF File', 'agb-connector'),
            'agb-column-attach_pdf_to_wc' => __('Attach PDF to WC emails', 'agb-connector'),
            'agb-column-placement-code' => sprintf(
                '%2$s
                <span class="vers comment-grey-bubble no-content" title="%1$s">
                    <span class="screen-reader-text">%1$s</span>
                    </span>',
                esc_html__(
                    'Copy and paste shortcode or block code to the page to display document.',
                    'agb-connector'
                ),
                esc_html__('Placement code', 'agb-connector')
            ),
        ];

        if (! $this->wcActive()) {
            unset($columns['agb-column-attach_pdf_to_wc']);
        }

        return $columns;
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];

        $this->_column_headers = [
            $columns,
            $hidden,
            $sortable,
        ];

        $this->handleBulkAction();

        $this->items = $this->documentRepository->getAllDocuments();
    }

    /**
     * @param DocumentInterface $item
     * @param string $column_name
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'agb-column-title':
                return $item->getTitle();
            case 'agb-column-type':
                return $this->shortCodes->getDocumentTypeNameByType($item->getType());
            case 'agb-column-country-language':
                return sprintf(
                    '%1$s/%2$s',
                    strtoupper($item->getCountry()),
                    strtolower($item->getLanguage())
                );
            case 'agb-column-page':
                $postIds = $this->documentFinder->findPagesDisplayingDocument(
                    $item->getSettings()->getDocumentId()
                );
                $posts = array_map('get_post', $postIds);
                return $this->buildPagesList($posts);
            case 'agb-column-store_pdf':
                return $item->getType() === 'impressum' ? '&mdash;' :
                    $this->renderCheckbox('store_pdf', $item->getSettings()->getSavePdf());
            case 'agb-column-attach_pdf_to_wc':
                return $item->getType() === 'impressum' ? '&mdash;' :
                    $this->renderCheckbox(
                        'attach_pdf_to_wc',
                        $item->getSettings()->getAttachToWcEmail(),
                        ! $item->getSettings()->getSavePdf() // Disable attaching PDF option if it's not stored.
                    );
            case 'agb-column-hide_title':
                return $this->renderCheckbox(
                    'hide_title',
                    $item->getSettings()->getHideTitle()
                );
            case 'agb-column-placement-code':
                return '<p><code>' . esc_html($this->shortCodes->generateShortcodeForDocument($item)) .
                    '</code></p>' .
                    '<p class="agb-block-or-shortcode">' . esc_html__('or', 'agb-connector') . '</p>' .
                    '<p><code>' . esc_html($this->shortCodes->generateBlockCodeForDocumentId(
                        $item->getSettings()->getDocumentId()
                    )) . '</code></p>';
            default:
                return '';
        }
    }

    /**
     * @param WP_Post[] $posts
     *
     * @return string
     */
    protected function buildPagesList(array $posts): string
    {
        return implode(' ', array_map(function (WP_Post $post): string {
            return sprintf(
                '<p><a href="%1$s" target="_blank">%2$s</a></p>',
                get_permalink($post),
                wp_kses_post($post->post_title)
            );
        }, $posts));
    }

    public function no_items()
    {
        return __('No documents saved yet.', 'agb-connector');
    }

    /**
     * Checkbox for the bulk edit
     *
     * @param DocumentInterface[] $item
     *
     * @return string
     */
    function column_cb($item): string
    {
        assert($item instanceof DocumentInterface);

        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            $item->getSettings()->getDocumentId()
        );
    }

    /**
     * Return list of bulk actions
     *
     * @return array
     */
    public function get_bulk_actions(): array
    {
        return [
            'bulk-delete' => 'Delete',
        ];
    }

    public function row_actions($actions, $always_visible = false)
    {
        return parent::row_actions($actions, $always_visible);
    }

    /**
     * @param DocumentInterface $item
     */
    public function single_row($item)
    {
        echo sprintf('<tr id="post-%1$d">', $item->getSettings()->getDocumentId());
        $this->single_row_columns($item);
        echo '</tr>';
    }

    /**
     * @param DocumentInterface $item
     * @param string $columnName
     * @param string $primary
     *
     * @return string
     */
    public function handle_row_actions($item, $columnName, $primary): string
    {
        if ($primary !== $columnName) {
            return '';
        }
        $documentId = $item->getSettings()->getDocumentId();
        $actions =  [
            'delete' => '<a href="' .
                get_delete_post_link($documentId, '', true) . '">' .
                __('Delete permanently', 'agb-connector') .
                '</a>',
        ];

        return $this->row_actions($actions);
    }

    /**
     * Render a settings checkbox.
     *
     * @param string $name
     * @param bool $checked
     * @param bool $disabled
     *
     * @return string
     */
    protected function renderCheckbox(string $name, bool $checked, bool $disabled = false): string
    {
        $pluginDirUrl = plugin_dir_url(agb_connector()->pluginFilePath());
        $loadedImgUrl = $pluginDirUrl . 'assets/images/tick.png';

        return sprintf(
            '<div style="float: left">
                        <input type="checkbox" name="%1$s" class="agb-document-settings" %2$s %3$s>
                    </div>
                    <div class="agbc-loading"></div>
                    <div class="agbc-loaded"><img src="%4$s" alt="comment icon"></div>',
            esc_attr($name),
            checked(true, $checked, false),
            disabled(true, $disabled, false),
            $loadedImgUrl
        );
    }

    protected function handleBulkAction(): void
    {
        if ('bulk-delete' === $this->current_action()) {
            check_admin_referer('bulk-' . $this->_args['plural']);
            $postIdsToDelete = filter_input(INPUT_POST, 'bulk-delete', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);

            foreach ($postIdsToDelete as $postId) {
                $attachments = get_posts([
                    'post_parent' => $postId,
                    'post_type' => 'attachment',
                    'fields' => 'ids',
                ]);
                wp_delete_post((int) $postId, true);
                foreach ($attachments as $attachment) {
                    wp_delete_attachment($attachment, true);
                }
            }
        }
    }

    /**
     * Check whether WooCommerce active
     *
     *
     * @return bool
     */
    protected function wcActive(): bool
    {
        return in_array(
            'woocommerce/woocommerce.php',
            apply_filters('active_plugins', get_option('active_plugins'))
        );
    }
}
