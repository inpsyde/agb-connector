<?php


namespace Inpsyde\AGBConnector\Middleware;


use Inpsyde\AGBConnector\customExceptions\versionException;
use Inpsyde\AGBConnector\XmlApi;

class CheckVersionXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|\Exception|versionException\
     */
    public function process($xml)
    {
        try {

            if (XmlApi::VERSION !== (string)$xml->api_version) {
                throw new versionException(
                    "Version Exception: provided {$xml->api_version}",
                    1
                );
            }
            return parent::process($xml);
        } catch (versionException $exception) {
            return $exception;
        }
    }
}
