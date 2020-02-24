<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\htmlTagException;

/**
 * Class CheckHtmlXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckHtmlXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|Exception|htmlTagException\
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_html) {
                throw new htmlTagException(
                    "Html Tag Exception: null provided",
                    6
                );
            }
            if (strlen((string)$xml->rechtstext_html) < 50) {
                throw new htmlTagException(
                    "Html Tag Exception: length < 50",
                    6
                );
            }
            return parent::process($xml);
        } catch (htmlTagException $exception) {
            return $exception;
        }
    }
}
