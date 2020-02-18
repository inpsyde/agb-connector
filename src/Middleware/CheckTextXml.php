<?php


namespace Inpsyde\AGBConnector\Middleware;


use Inpsyde\AGBConnector\customExceptions\textException;
use Inpsyde\AGBConnector\XmlApi;

class CheckTextXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|\Exception|textException\
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_text) {
                throw new textException(
                    "Text Exception: null provided",
                    5
                );
            }
            if (strlen((string)$xml->rechtstext_text) < 50) {
                throw new textException(
                    "Text Exception: length < 50",
                    5
                );
            }
            return parent::process($xml);
        } catch (textException $exception) {
            return $exception;
        }
    }
}
