<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
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
     * @return Exception|PdfUrlException|int
     */
    public function process($xml)
    {
        try {
            if ('impressum' === (string)$xml->rechtstext_type) {
                return parent::process($xml);
            }
            if (null === $xml->rechtstext_pdf_url) {
                throw new PdfUrlException(
                    "PdfUrlException: null provided",
                    7
                );
            }
            if ('' === (string)$xml->rechtstext_pdf_url) {
                throw new PdfUrlException(
                    "PdfUrlException: empty string",
                    7
                );
            }
        } catch (PdfUrlException $exception) {
            return $exception;
        }
    }
}
