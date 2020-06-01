<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\HtmlTagException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class CheckHtmlXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckHtmlXml extends Middleware
{
    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if (null === $xml->rechtstext_html) {
            throw new HtmlTagException(
                "No html tag provided"
            );
        }
        if (mb_strlen((string)$xml->rechtstext_html) < 50) {
            throw new HtmlTagException(
                "Html tag length must be greater than 50"
            );
        }
        return parent::process($xml);
    }
}
