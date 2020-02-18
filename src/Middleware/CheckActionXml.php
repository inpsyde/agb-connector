<?php


namespace Inpsyde\AGBConnector\Middleware;


use Inpsyde\AGBConnector\customExceptions\actionTagException;
use Inpsyde\AGBConnector\XmlApi;

class CheckActionXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|\Exception|actionTagException\
     */
    public function process($xml)
    {
        try {
            if (null === $xml->action) {
                throw new actionTagException(
                    'actionTagException: null provided',
                    10
                );
            }
            if ('push' !== (string)$xml->action) {
                throw new actionTagException(
                    "actionTagException: not push provided: {$xml->action}",
                    10
                );
            }
            return parent::process($xml);
        } catch (actionTagException $exception) {
            return $exception;
        }
    }
}
