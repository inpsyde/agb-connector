<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\VersionException;
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
     * @return Exception|VersionException|int
     */
    public function process($xml)
    {
        try {
            if (XmlApi::VERSION !== (string)$xml->api_version) {
                throw new VersionException(
                    "Version Exception: provided {$xml->api_version}",
                    1
                );
            }
            return parent::process($xml);
        } catch (VersionException $exception) {
            return $exception;
        }
    }
}
