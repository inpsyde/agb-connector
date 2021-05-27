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
        array $args = array()
    ){

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
        return [
            'cb' => '<input type="checkbox" />',
            'title' => __('Title', 'agb-connector'),
            'type' => __('Type', 'agb-connector'),
            'country' => __('Country', 'agb-connector'),
            'language' => __('Language', 'agb-connector'),
            'page' => __('Page', 'agb-connector'),
            'store_pdf' => __('Store PDF File', 'agb-connector'),
            'attach_pdf_to_wc' => __('Attach PDF to WC emails', 'agb-connector'),
            'hide_title' => __('Hide title', 'agb-connector'),
            'shortcode' => __('Shortcode', 'agb-connector')
        ];
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];

        $this->_column_headers = [
            $columns,
            $hidden,
            $sortable
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
            case 'title':
                return $item->getTitle();
            case 'type':
                return $this->getDocumentTypeForDisplay($item->getType());
            case 'country':
                return $item->getCountry();
            case 'language':
                return $item->getLanguage();
            case 'page':
                $postIds = $this->documentFinder->findPagesDisplayingDocument($item->getSettings()->getDocumentId());
                $posts = array_map('get_post', $postIds);
                return $this->buildPagesList($posts);
            case 'store_pdf':
                return $item->getType() === 'impressum' ? '&mdash;' :
                    $this->renderCheckbox('store_pdf', $item->getSettings()->getSavePdf());
            case 'attach_pdf_to_wc':
                return $this->renderCheckbox(
                    'attach_pdf_to_wc',
                    $item->getSettings()->getAttachToWcEmail(),
                    ! $item->getSettings()->getSavePdf() // Disable attaching PDF option if it's not stored.
                );
            case 'hide_title':
                return $this->renderCheckbox(
                    'hide_title',
                    $item->getSettings()->getHideTitle()
                );
            case 'shortcode':
                return '<code>' . $this->shortCodes->generateShortcodeForDocument($item) . '</code>';
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
        return implode(' ', array_map(function(WP_Post $post): string {
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
    function column_cb( $item ): string {

        assert($item instanceof DocumentInterface);

        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->getSettings()->getDocumentId()
        );
    }

    /**
     * Return list of bulk actions
     *
     * @return array
     */
    public function get_bulk_actions(): array
    {
        $actions = [
            'bulk-delete' => 'Delete'
        ];

        return $actions;
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
        $this->single_row_columns( $item );
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
        if($primary !== $columnName){
            return '';
        }
        $deleteLink = get_delete_post_link($item->getSettings()->getDocumentId(), '', true);
        $actions =  [
            'delete' => "<a href=$deleteLink>" . __('Delete permanently', 'agb-connector') . '</a>',
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
    protected function renderCheckbox(string $name, bool $checked, bool $disabled = false): string {
        return sprintf(
            '<input type="checkbox" name="%1$s" class="agb-document-settings" %2$s %3$s>',
            esc_attr($name),
            checked(true, $checked, false),
            disabled(true, $disabled, false)
        );
    }

    protected function handleBulkAction(): void
    {
        if('bulk-delete' === $this->current_action()){
            check_admin_referer('bulk-' . $this->_args['plural']);
            $postIdsToDelete = filter_input(INPUT_POST, 'bulk-delete', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);

            foreach ($postIdsToDelete as $postId)
            {
                $attachments = get_posts([
                    'post_parent' => $postId,
                    'post_type' => 'attachment',
                    'fields' => 'ids'
                ]);
                wp_delete_post((int) $postId, true);
                foreach ($attachments as $attachment){
                    wp_delete_attachment($attachment, true);
                }
            }
        }
    }

    /**
     * Return a document type prepared for display.
     *
     * @param string $internalType
     *
     * @return string
     */
    protected function getDocumentTypeForDisplay(string $internalType): string
    {
        $types = [
            'agb' => esc_html__('AGB Terms', 'agb-connector'),
            'datenschutz' => esc_html__('AGB Privacy', 'agb-connector'),
            'widerruf' => esc_html__('AGB Revocation', 'agb-connector'),
            'impressum' => esc_html__('AGB Imprint', 'agb-connector')
        ];

        return $types[$internalType] ?? '';
    }

}
