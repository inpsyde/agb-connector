<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;

use Inpsyde\AGBConnector\Document\Document;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use Inpsyde\AGBConnector\Document\DocumentSettings;
use Inpsyde\AGBConnector\Document\Map\XmlMetaFields;
use SimpleXMLElement;

class XmlBasedDocumentFactory implements XmlBasedDocumentFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function createDocument(SimpleXMLElement $xml): DocumentInterface
    {
        $documentType = (string) $xml->{XmlMetaFields::XML_FIELD_TYPE};
        $documentSettings = new DocumentSettings();

        if($documentType === 'impressum'){
            $documentSettings->setSavePdf(false);
            $documentSettings->setAttachToWcEmail(false);
        }

        return new Document(
            $documentSettings,
            $this->buildTitle($xml),
            (string) $xml->{XmlMetaFields::XML_FIELD_HTML_CONTENT},
            (string) $xml->{XmlMetaFields::XML_FIELD_COUNTRY},
            (string) $xml->{XmlMetaFields::XML_FIELD_LANGUAGE},
            $documentType
        );
    }

    protected function buildTitle(SimpleXMLElement $documentXml): string
    {
        return sprintf(
            '%1$s (%2$s_%3$s)',
            $documentXml->{XmlMetaFields::XML_FIELD_TITLE},
            $documentXml->{XmlMetaFields::XML_FIELD_LANGUAGE},
            $documentXml->{XmlMetaFields::XML_FIELD_COUNTRY}
        );
    }
}
