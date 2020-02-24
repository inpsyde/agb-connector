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
     * @return bool|Exception|pdfUrlException\
     */
    public function process($xml)
    {
        try {
            if ('impressum' !== (string)$xml->rechtstext_type) {
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
            }
            return parent::process($xml);
        } catch (PdfUrlException $exception) {
            return $exception;
        }
    }
}
