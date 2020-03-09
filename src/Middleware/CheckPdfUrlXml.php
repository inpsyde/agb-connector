<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\PdfUrlException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;

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
     * @return XmlApiException|PdfUrlException|int
     */
    public function process($xml)
    {
        try {
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
        } catch (PdfUrlException $exception) {
            return $exception;
        }
    }
}
