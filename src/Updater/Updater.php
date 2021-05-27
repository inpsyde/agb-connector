<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Updater;

use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentFinderInterface;
use Inpsyde\AGBConnector\Document\Factory\WpPostBasedDocumentFactoryInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use Inpsyde\AGBConnector\Plugin;
use Inpsyde\AGBConnector\Settings;

class Updater implements UpdaterInterface
{

    /**
     * @var DocumentFinderInterface
     */
    protected $documentPageFinder;
    /**
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;
    /**
     * @var array
     */
    protected $allocations;
    /**
     * @var WpPostBasedDocumentFactoryInterface
     */
    protected $documentFactory;

    public function __construct(
        DocumentFinderInterface $documentPageFinder,
        DocumentRepositoryInterface $documentRepository,
        WpPostBasedDocumentFactoryInterface $documentFactory,
        array $allocations
    ){

        $this->documentPageFinder = $documentPageFinder;
        $this->documentRepository = $documentRepository;
        $this->allocations = $allocations;
        $this->documentFactory = $documentFactory;
    }

    /**
     * @inheritDoc
     */
    public function update(): void
    {
        $this->update200to220();
    }

    /**
     *
     */
    protected function update200to220(): void
    {
        foreach ($this->allocations as $allocationsOfType){
            foreach($allocationsOfType as $allocation){
                $documentPostId = (int)$allocation['pageId'] ?? 0;


                if($documentPostId){
                    $post = get_post($documentPostId);

                    try{
                        $document = $this->documentFactory->createDocument($post);
                        $this->documentRepository->saveDocument($document);
                    } catch (XmlApiException $exception){
                        //todo: log here
                        update_option(Settings::MIGRATION_FAILED_FLAG_OPTION_NAME, true);
                        return;
                    }

                }
            }
        }

        delete_option(Plugin::OPTION_TEXT_ALLOCATIONS);
        delete_option(Settings::MIGRATION_FAILED_FLAG_OPTION_NAME);
    }
}
