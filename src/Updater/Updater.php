<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Updater;

use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\DocumentPageFinder\DocumentFinderInterface;
use Inpsyde\AGBConnector\Document\Factory\WpPostBasedDocumentFactoryInterface;
use Inpsyde\AGBConnector\Document\Repository\DocumentRepositoryInterface;
use Inpsyde\AGBConnector\Plugin;

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
        $this->update200to300();
    }

    protected function update200to300(): void
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
                        return; //todo: add message about migration was failed.
                    }

                }
            }
        }

        delete_option(Plugin::OPTION_TEXT_ALLOCATIONS);
    }
}
