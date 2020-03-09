<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\CredentialsException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
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
     * @return bool|XmlApiException|CredentialsException
     */
    public function process($xml)
    {
        try {
            if (XmlApi::USERNAME !== (string)$xml->api_username &&
                XmlApi::PASSWORD !== (string)$xml->api_password
            ) {
                throw new CredentialsException(
                    "Incorrect username or password"
                );
            }
            return parent::process($xml);
        } catch (CredentialsException $exception) {
            return $exception;
        }
    }
}
