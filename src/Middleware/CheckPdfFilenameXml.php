<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
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
     * @return Exception|PdfFilenameException|int
     */
    public function process($xml)
    {
        try {
            if ('impressum' === (string)$xml->rechtstext_type) {
                return parent::process($xml);
            }
            if (null === $xml->rechtstext_pdf_filename_suggestion) {
                throw new PdfFilenameException(
                    "PdfFilenameException: rechtstext_pdf_filename_suggestion null provided",
                    19
                );
            }
            if ('' === (string)$xml->rechtstext_pdf_filename_suggestion) {
                throw new PdfFilenameException(
                    "PdfFilenameException: rechtstext_pdf_filename_suggestion empty string",
                    19
                );
            }
            if (null === $xml->rechtstext_pdf_filenamebase_suggestion) {
                throw new PdfFilenameException(
                    "PdfFilenameException: rechtstext_pdf_filenamebase_suggestion null provided",
                    19
                );
            }
            if ('' === (string)$xml->rechtstext_pdf_filenamebase_suggestion) {
                throw new PdfFilenameException(
                    "PdfFilenameException: rechtstext_pdf_filenamebase_suggestion empty string",
                    19
                );
            }
        } catch (PdfFilenameException $exception) {
            return $exception;
        }
    }
}
