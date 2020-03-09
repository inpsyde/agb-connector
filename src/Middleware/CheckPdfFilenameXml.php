<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\PdfFilenameException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;

/**
 * Class CheckPdfFilenameXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckPdfFilenameXml extends Middleware
{

    /**
     * @param $xml
     *
     * @return XmlApiException|PdfFilenameException|int
     */
    public function process($xml)
    {
        try {
            if ('impressum' === (string)$xml->rechtstext_type) {
                return parent::process($xml);
            }
            if (null === $xml->rechtstext_pdf_filename_suggestion) {
                throw new PdfFilenameException(
                    "No pdf filename provided"
                );
            }
            if ('' === (string)$xml->rechtstext_pdf_filename_suggestion) {
                throw new PdfFilenameException(
                    "The pdf filename is empty"
                );
            }
            if (null === $xml->rechtstext_pdf_filenamebase_suggestion) {
                throw new PdfFilenameException(
                    "No pdf base filename provided"
                );
            }
            if ('' === (string)$xml->rechtstext_pdf_filenamebase_suggestion) {
                throw new PdfFilenameException(
                    "The pdf base filename is empty"
                );
            }
            return parent::process($xml);
        } catch (PdfFilenameException $exception) {
            return $exception;
        }
    }
}
