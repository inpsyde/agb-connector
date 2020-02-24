<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\HtmlTagException;

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
                throw new HtmlTagException(
                    "Html Tag Exception: null provided",
                    6
                );
            }
            if (strlen((string)$xml->rechtstext_html) < 50) {
                throw new HtmlTagException(
                    "Html Tag Exception: length < 50",
                    6
                );
            }
            return parent::process($xml);
        } catch (HtmlTagException $exception) {
            return $exception;
        }
    }
}
