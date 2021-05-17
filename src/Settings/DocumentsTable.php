<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Settings;

use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentFinderInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
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
     * DocumentsTable constructor.
     *
     * @param DocumentRepositoryInterface $documentRepository
     * @param DocumentFinderInterface $documentFinder
     * @param array $args
     */
    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        DocumentFinderInterface $documentFinder,
        array $args = array()
    ){

        $this->documentRepository = $documentRepository;

        parent::__construct($args);
        $this->documentFinder = $documentFinder;
    }

    /**
     * @inheritDoc
     */
    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />',
            'title' => __('Title', 'agb-connector'),
            'country' => __('Country', 'agb-connector'),
            'language' => __('Language', 'agb-connector'),
            'page' => __('Page', 'agb-connector'),
            'store_pdf' => __('Store PDF File', 'agb-connector'),
            'attach_pdf_to_wc' => __('Attach PDF on WooCommerce emails', 'agb-connector'),
            'hide_title' => __('Hide title')
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
            case 'country':
                return $item->getCountry();
            case 'language':
                return $item->getLanguage();
            case 'page':
                $postIds = $this->documentFinder->findPagesDisplayingDocument($item->getSettings()->getDocumentId());
                $posts = array_map('get_post', $postIds);
                return $this->buildPagesList($posts);
            case 'store_pdf':
                return $item->getSettings()->getSavePdf() ? 'yes' : 'no';
            case 'attach_pdf_to_wc':
            default:
                return $item->getSettings()->getAttachToWcEmail() ? 'yes' : 'no';

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
                $post->post_title
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
        $deleteLink = get_delete_post_link($item->getSettings()->getDocumentId());
        $actions =  [
            'inline hide-if-no-js' => $this->prepareQuickEditButton(__('Quick Edit', 'agb-connector')),
            'delete' => "<a href=$deleteLink>" . __('Trash', 'agb-connector') . '</a>',
        ];

        return $this->row_actions($actions);
    }

    public function inlineEditFields(int $columnCount): void
    { ?>
        <form method="get">
            <table style="display: none">
                <tbody id="inlineedit">

                <tr id="inline-edit" class="inline-edit-row" style="display: none">
                    <td colspan="<?php echo esc_attr($columnCount); ?>" class="colspanchange">

                        <fieldset>
                            <legend class="inline-edit-legend"><?php _e('Quick Edit'); ?></legend>
                            <div class="inline-edit-col">
                                <label>
                                    <span class="title"><?php
                                        _ex('Save PDF', 'document setting', 'agb-connector');
                                        ?></span>
                                    <span class="input-text-wrap"><input type="checkbox" name="save-pdf"/></span>
                                </label>
                                <label>
                                    <span class="title"><?php
                                        _ex(
                                            'Attach the PDF version of this document to the WooCommerce emails',
                                            'document setting',
                                            'agb-connector'
                                        );
                                        ?></span>
                                    <span class="input-text-wrap"><input type="checkbox" name="attach-to-wc-emails"/></span>
                                </label>
                            </div>
                        </fieldset>

                        <?php
                        $core_columns = array(
                            'cb' => true,
                            'description' => true,
                            'name' => true,
                            'slug' => true,
                            'posts' => true,
                        );

                        list($columns) = $this->get_column_info();

                        foreach ($columns as $column_name => $column_display_name) {
                            if (isset($core_columns[$column_name])) {
                                continue;
                            }

                            /** This action is documented in wp-admin/includes/class-wp-posts-list-table.php */
                            do_action('quick_edit_custom_box', $column_name, 'edit-tags', $this->screen->taxonomy);
                        }
                        ?>

                        <div class="inline-edit-save submit">
                            <button type="button"
                                    class="cancel button alignleft"><?php _e('Cancel'); ?></button>
                            <button type="button"
                                    class="save button button-primary alignright"><?php echo 'Test'//$tax->labels->update_item; ?></button>
                            <span class="spinner"></span>

                            <?php wp_nonce_field('taxinlineeditnonce', '_inline_edit', false); ?>
                            <input type="hidden" name="taxonomy"
                                   value="<?php echo esc_attr($this->screen->taxonomy); ?>"/>
                            <input type="hidden" name="post_type"
                                   value="<?php echo esc_attr($this->screen->post_type); ?>"/>
                            <br class="clear"/>

                            <div class="notice notice-error notice-alt inline hidden">
                                <p class="error"></p>
                            </div>
                        </div>

                    </td>
                </tr>

                </tbody>
            </table>
        </form>
        <?php
    }

    protected function handleBulkAction(): void
    {

        if('bulk-delete' === $this->current_action()){
            //todo: handle bulk delete here
        }
    }

    protected function prepareQuickEditButton(string $title): string
    {
        return sprintf(
            '<button type="button" class="button-link editinline" aria-label="%s" aria-expanded="false">%s</button>',
            /* translators: %s: Post title. */
            esc_attr( sprintf( __( 'Quick edit &#8220;%s&#8221; inline' ), $title ) ),
            __( 'Quick&nbsp;Edit' )
        );
    }

}
