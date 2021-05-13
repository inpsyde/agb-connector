<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Settings;

use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentFinderInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use WP_List_Table;

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
            case 'country':
                return $item->getCountry();
            case 'language':
                return $item->getLanguage();
            case 'page':
                $pages = $this->documentFinder->findPagesDisplayingDocument($item->getSettings()->getDocumentId());
                return '';
            case 'store_pdf':
                return $item->getSettings()->getSavePdf() ? 'yes' : 'no';
            case 'attach_pdf_to_wc':
            default:
                return $item->getSettings()->getAttachToWcEmail() ? 'yes' : 'no';

        }
    }
}
