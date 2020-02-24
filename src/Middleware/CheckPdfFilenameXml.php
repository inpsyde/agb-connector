<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\pdfFilenameException;

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
     * @return bool|Exception|pdfFilenameException\
     */
    public function process($xml)
    {
        try {
            if ('impressum' !== (string)$xml->rechtstext_type) {
                if (null === $xml->rechtstext_pdf_filename_suggestion) {
                    throw new pdfFilenameException(
                        "pdfFilenameException: rechtstext_pdf_filename_suggestion null provided",
                        19
                    );
                }
                if ('' === (string)$xml->rechtstext_pdf_filename_suggestion) {
                    throw new pdfFilenameException(
                        "pdfFilenameException: rechtstext_pdf_filename_suggestion empty string",
                        19
                    );
                }
                if (null === $xml->rechtstext_pdf_filenamebase_suggestion) {
                    throw new pdfFilenameException(
                        "pdfFilenameException: rechtstext_pdf_filenamebase_suggestion null provided",
                        19
                    );
                }
                if ('' === (string)$xml->rechtstext_pdf_filenamebase_suggestion) {
                    throw new pdfFilenameException(
                        "pdfFilenameException: rechtstext_pdf_filenamebase_suggestion empty string",
                        19
                    );
                }
            }
            return parent::process($xml);
        } catch (pdfFilenameException $exception) {
            return $exception;
        }
    }
}
