<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\PdfUrlException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class CheckPdfUrlXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckPdfUrlXml extends Middleware
{
    /**
     * @param SimpleXMLElement $xml
     *
     * @return int
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if ('impressum' === (string)$xml->rechtstext_type) {
            return parent::process($xml);
        }
        if ($xml->rechtstext_pdf_url === null) {
            throw new PdfUrlException(
                "No url for the pdf provided"
            );
        }
        if ((string)$xml->rechtstext_pdf_url === '') {
            throw new PdfUrlException(
                "Pdf url is empty"
            );
        }
        return parent::process($xml);
    }
}
