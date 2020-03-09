<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\VersionException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\XmlApi;

/**
 * Class CheckVersionXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckVersionXml extends Middleware
{

    /**
     * @param $xml
     *
     * @return XmlApiException|VersionException|int
     */
    public function process($xml)
    {
        try {
            if (XmlApi::VERSION !== (string)$xml->api_version) {
                throw new VersionException(
                    "Version provided {$xml->api_version} does not match the current one"
                );
            }
            return parent::process($xml);
        } catch (VersionException $exception) {
            return $exception;
        }
    }
}
