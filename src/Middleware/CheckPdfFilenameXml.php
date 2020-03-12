<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\PdfFilenameException;

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
     * @return int
     * @throws PdfFilenameException
     */
    public function process($xml)
    {
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
    }
}
