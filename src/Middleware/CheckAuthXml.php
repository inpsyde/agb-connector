<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\AuthException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class CheckAuthXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckAuthXml extends Middleware
{
    /**
     * @var string $userAuth
     */
    protected $userAuth;

    /**
     * CheckAuthXml constructor.
     *
     * @param string $userAuthToken
     */
    public function __construct($userAuthToken)
    {
        $this->userAuth = $userAuthToken;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return int
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if ($xml->user_auth_token === null) {
            throw new AuthException(
                "Auth Exception: null user_auth_token"
            );
        }
        if ((string)$xml->user_auth_token !== $this->userAuth) {
            throw new AuthException(
                "Auth Exception: userAuthToken doesn't match"
            );
        }
        return parent::process($xml);
    }
}
