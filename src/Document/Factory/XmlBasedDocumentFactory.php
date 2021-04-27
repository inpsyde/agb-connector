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
            (string) $xml->{XmlMetaFields::XML_FIELD_TITLE},
            (string) $xml->{XmlMetaFields::XML_FIELD_HTML_CONTENT},
            (string) $xml->{XmlMetaFields::XML_FIELD_COUNTRY},
            (string) $xml->{XmlMetaFields::XML_FIELD_LANGUAGE},
            (string) $xml->{XmlMetaFields::XML_FIELD_TYPE},
            (string) $xml->{XmlMetaFields::XML_FIELD_PDF_URL}
        );
    }
}
