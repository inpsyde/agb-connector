<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\HtmlTagException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;

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
     * @return bool|XmlApiException|HtmlTagException
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_html) {
                throw new HtmlTagException(
                    "No html tag provided"
                );
            }
            if (strlen((string)$xml->rechtstext_html) < 50) {
                throw new HtmlTagException(
                    "Html tag length must be greater than 50"
                );
            }
            return parent::process($xml);
        } catch (HtmlTagException $exception) {
            return $exception;
        }
    }
}