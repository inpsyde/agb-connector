<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\NotSimpleXmlInstanceException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;

/**
 * Class CheckInstanceSimpleXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckInstanceSimpleXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if (!$xml) {
            throw new NotSimpleXmlInstanceException('Not xml provided');
        }
        if (!$xml instanceof \SimpleXMLElement) {
            throw new NotSimpleXmlInstanceException('This is not a simple xml instance');
        }
        return parent::process($xml);
    }
}
