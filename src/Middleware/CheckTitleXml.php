<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\TitleException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class CheckTitleXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckTitleXml extends Middleware
{
    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if ($xml->rechtstext_title === null) {
            throw new TitleException(
                "There must be a title, null provided"
            );
        }
        if (mb_strlen((string)$xml->rechtstext_title) < 3) {
            throw new TitleException(
                "Title length must be greater than 3"
            );
        }
        return parent::process($xml);
    }
}
