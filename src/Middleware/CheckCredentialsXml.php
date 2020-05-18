<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\CredentialsException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\XmlApi;
use SimpleXMLElement;

/**
 * Class CheckCredentialsXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckCredentialsXml extends Middleware
{
    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if (XmlApi::USERNAME !== (string)$xml->api_username &&
            XmlApi::PASSWORD !== (string)$xml->api_password
        ) {
            throw new CredentialsException(
                "Incorrect username or password"
            );
        }
        return parent::process($xml);
    }
}
