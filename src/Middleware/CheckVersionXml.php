<?php

namespace Inpsyde\AGBConnector\Middleware;

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
     * @return int
     * @throws VersionException
     */
    public function process($xml)
    {
        if (XmlApi::VERSION !== (string)$xml->api_version) {
            throw new VersionException(
                "Version provided {$xml->api_version} does not match the current one"
            );
        }
        return parent::process($xml);
    }
}
