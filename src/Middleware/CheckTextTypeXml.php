<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\TextTypeException;
use Inpsyde\AGBConnector\XmlApi;

/**
 * Class CheckTextTypeXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckTextTypeXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool
     * @throws TextTypeException
     */
    public function process($xml)
    {
        if (null === $xml->rechtstext_type) {
            throw new TextTypeException(
                "No text type provided"
            );
        }
        if (! \array_key_exists((string)$xml->rechtstext_type, XmlApi::supportedTextTypes())
        ) {
            throw new TextTypeException(
                "The text type provided is not supported"
            );
        }
        return parent::process($xml);
    }
}
