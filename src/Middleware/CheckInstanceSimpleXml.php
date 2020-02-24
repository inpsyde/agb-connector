<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\NotSimpleXmlInstanceException;

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
     * @return bool|Exception|NotSimpleXmlInstanceException
     */
    public function process($xml)
    {
        try {
            if (!$xml) {
                throw new NotSimpleXmlInstanceException('Not xml provided', 12);
            }
            if (!$xml instanceof \SimpleXMLElement) {
                throw new NotSimpleXmlInstanceException('Not a simple xml instance', 12);
            }
            return parent::process($xml);
        } catch (NotSimpleXmlInstanceException $exception) {
            return $exception;
        }
    }

}
