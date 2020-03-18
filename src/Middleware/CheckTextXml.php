<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\TextException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;

/**
 * Class CheckTextXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckTextXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if ($xml->rechtstext_text === null) {
            throw new TextException(
                "No text provided"
            );
        }
        if (strlen((string)$xml->rechtstext_text) < 50) {
            throw new TextException(
                "The text size must be greater than 50"
            );
        }
        return parent::process($xml);
    }
}
