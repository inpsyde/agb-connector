<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\PdfUrlException;

/**
 * Class CheckPdfUrlXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckPdfUrlXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return int
     * @throws PdfUrlException
     */
    public function process($xml)
    {
        if ('impressum' === (string)$xml->rechtstext_type) {
            return parent::process($xml);
        }
        if (null === $xml->rechtstext_pdf_url) {
            throw new PdfUrlException(
                "No url for the pdf provided"
            );
        }
        if ('' === (string)$xml->rechtstext_pdf_url) {
            throw new PdfUrlException(
                "Pdf url is empty"
            );
        }
        return parent::process($xml);
    }
}
