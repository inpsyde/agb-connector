<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\AttributesAdder;

use DOMDocument;
use DOMElement;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use RuntimeException;

/**
 * Class AttributesAdder
 *
 * @package Inpsyde\AGBConnector\Document\AttributesAdder
 */
class AttributesAdder implements AttributesAdderInterface
{

    /**
     * @inheritDoc
     */
    public function hasAttributes(DocumentInterface $document): bool
    {
        $domDocument = new DOMDocument();

        if(! $domDocument->loadHTML($document->getContent())) {
            return false;
        }

        $headers = $domDocument->getElementsByTagName('h1');

        foreach($headers as $header) {
            if($header instanceof DOMElement && $header->hasAttribute('data-agbc-document-id')){
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function addAttributes(DocumentInterface $document): DocumentInterface
    {

        try {
            $domDocument = $this->documentToDomDocument($document);
        } catch (RuntimeException $exception){
            //todo: add logging.
            return $document;
        }

        foreach($this->getDocumentHeaders($domDocument) as $header) {
            $documentId = $document->getSettings()->getDocumentId();
            $hideTitle = $document->getSettings()->getHideTitle();
            $this->setElementAttributes($header, $documentId, $hideTitle);
        }

        $updatedDocumentContent = $domDocument->saveHTML();

        if($updatedDocumentContent){
            $document->setContent($updatedDocumentContent);
        }


        return $document;
    }

    /**
     * Parse Document content and return it as DomDocument.
     *
     * @param DocumentInterface $document
     *
     * @return DOMDocument
     *
     * @throws RuntimeException
     */
    protected function documentToDomDocument(DocumentInterface $document): DOMDocument
    {
        $domDocument = new DOMDocument();
        if(! $domDocument->loadHTML($document->getContent())){
            throw new RuntimeException(
                "Couldn't parse document as HTML."
            );
        }

        return $domDocument;
    }

    /**
     * Parse DomDocument instance and return all h1 headers.
     *
     * We have no guarantee document content follows standards and uses only one h1,
     * so there can be multiple.
     *
     * @return DOMElement[]
     */
    protected function getDocumentHeaders(DOMDocument $domDocument): array
    {
        $headers = $domDocument->getElementsByTagName('h1');

        $domElements = [];

        foreach ($headers as $header){
            if ($header instanceof DOMElement){
                $domElements[] = $header;
            }
        }

        return $domElements;
    }

    /**
     * Set attributes to the DomElement.
     *
     * @param DOMElement $domElement
     * @param int $documentId
     * @param bool $hideTitle
     */
    protected function setElementAttributes(DOMElement $domElement, int $documentId, bool $hideTitle): void
    {
        $classes = $domElement->getAttribute('class');
        $classes .= ' agbc-document-title';
        if($hideTitle){
            $classes .= ' agbc-hidden';
        }
        $domElement->setAttribute('class', $classes);
        $domElement->setAttribute('data-agbc-document-id', $documentId);
    }
}
