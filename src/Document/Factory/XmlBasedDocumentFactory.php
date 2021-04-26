<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;

use Inpsyde\AGBConnector\Document\Document;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\Map\XmlMetaFields;
use SimpleXMLElement;

class XmlBasedDocumentFactory implements XmlBasedDocumentFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function createDocument(SimpleXMLElement $xml): DocumentInterface
    {
        return new Document(
           $this->getTag(XmlMetaFields::XML_FIELD_TITLE, $xml),
           $this->getTag(XmlMetaFields::XML_FIELD_HTML_CONTENT, $xml),
           $this->getTag(XmlMetaFields::XML_FIELD_COUNTRY, $xml),
           $this->getTag(XmlMetaFields::XML_FIELD_LANGUAGE, $xml),
           $this->getTag(XmlMetaFields::XML_FIELD_PDF_URL, $xml)
        );
    }

    /**
     * Get content of the top-level XML tag, empty string if tag not found.
     *
     * @param string $tagName Tag name to get.
     * @param SimpleXMLElement $xml XML to search in.
     *
     * @return string
     */
    protected function getTag(string $tagName, SimpleXMLElement $xml): string
    {
        return $xml->offsetExists($tagName) ? (string) $xml->offsetGet($tagName) : '';
    }
}
