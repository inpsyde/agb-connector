<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\PdfFilenameException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class CheckPdfFilenameXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckPdfFilenameXml extends Middleware
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
        if ($xml->rechtstext_pdf_filename_suggestion === null) {
            throw new PdfFilenameException(
                "No pdf filename provided"
            );
        }
        if ((string)$xml->rechtstext_pdf_filename_suggestion === '') {
            throw new PdfFilenameException(
                "The pdf filename is empty"
            );
        }
        if ($xml->rechtstext_pdf_filenamebase_suggestion === null) {
            throw new PdfFilenameException(
                "No pdf base filename provided"
            );
        }
        if ((string)$xml->rechtstext_pdf_filenamebase_suggestion === '') {
            throw new PdfFilenameException(
                "The pdf base filename is empty"
            );
        }
        return parent::process($xml);
    }
}
