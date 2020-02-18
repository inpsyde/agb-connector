<?php


namespace Inpsyde\AGBConnector\Middleware;


use Inpsyde\AGBConnector\customExceptions\pdfUrlException;
use Inpsyde\AGBConnector\XmlApi;

class CheckPdfUrlXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|\Exception|pdfUrlException\
     */
    public function process($xml)
    {
        try {
            if ('impressum' !== (string)$xml->rechtstext_type) {
                if (null === $xml->rechtstext_pdf_url) {
                    throw new pdfUrlException(
                        "pdfUrlException: null provided",
                        7
                    );
                }
                if ('' === (string)$xml->rechtstext_pdf_url) {
                    throw new pdfUrlException(
                        "pdfUrlException: empty string",
                        7
                    );
                }
            }
            return parent::process($xml);
        } catch (pdfUrlException $exception) {
            return $exception;
        }
    }
}
