<?php


namespace Inpsyde\AGBConnector\Middleware;


use Inpsyde\AGBConnector\customExceptions\notSimpleXmlInstanceException;

class checkInstanceSimpleXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|\Exception|notSimpleXmlInstanceException
     */
    public function process($xml)
    {
        try {
            if (! $xml ) {
                throw new notSimpleXmlInstanceException('Not xml provided', 12);
            }
            if(! $xml instanceof \SimpleXMLElement){
                throw new notSimpleXmlInstanceException('Not a simple xml instance', 12);
            }
            return parent::process($xml);
        }catch (notSimpleXmlInstanceException $exception){
            return $exception;
        }

    }

}
