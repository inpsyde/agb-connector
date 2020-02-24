<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\versionException;
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
     * @return Exception|versionException|int
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
