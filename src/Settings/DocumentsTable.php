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
}
