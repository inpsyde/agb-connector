<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\credentialsException;
use Inpsyde\AGBConnector\XmlApi;

/**
 * Class CheckCredentialsXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckCredentialsXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|Exception|credentialsException
     */
    public function process($xml)
    {
        try {
            if (XmlApi::USERNAME !== (string)$xml->api_username &&
                XmlApi::PASSWORD !== (string)$xml->api_password
            ) {
                throw new credentialsException(
                    "Credentials Exception: username provided {$xml->api_username}",
                    2
                );
            }
            return parent::process($xml);
        } catch (credentialsException $exception) {
            return $exception;
        }
    }
}
