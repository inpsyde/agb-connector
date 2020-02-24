<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\TextException;

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
     * @return bool|Exception|textException\
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_text) {
                throw new TextException(
                    "Text Exception: null provided",
                    5
                );
            }
            if (strlen((string)$xml->rechtstext_text) < 50) {
                throw new TextException(
                    "Text Exception: length < 50",
                    5
                );
            }
            return parent::process($xml);
        } catch (TextException $exception) {
            return $exception;
        }
    }
}
