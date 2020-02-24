<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\textTypeException;
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
     * @return bool|Exception|versionException
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_type) {
                throw new textTypeException(
                    "Text type Exception: null provided",
                    4
                );
            }
            if (! \array_key_exists((string)$xml->rechtstext_type, XmlApi::supportedTextTypes())
            ) {
                throw new textTypeException(
                    "Text type Exception: not supported",
                    4
                );
            }
            return parent::process($xml);
        } catch (textTypeException $exception) {
            return $exception;
        }
    }
}
