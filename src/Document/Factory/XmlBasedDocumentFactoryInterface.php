<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document\Factory;

use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Document\DocumentInterface;
use SimpleXMLElement;

/**
 * Service able to create a new instance of Document from XML.
 */
interface XmlBasedDocumentFactoryInterface
{
    /**
     * Create a new document from XML.
     *
     * @param SimpleXMLElement $xml
     *
     * @return DocumentInterface
     *
     * @throws XmlApiException If couldn't create a new instance.
     */
    public function createDocument(SimpleXMLElement $xml): DocumentInterface;
}
