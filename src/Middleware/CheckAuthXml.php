<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\AuthException;

/**
 * Class CheckAuthXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckAuthXml extends Middleware
{
    /**
     * @var API $userAuth
     */
    private $userAuth;
    /**
     * CheckAuthXml constructor.
     */
    public function __construct($userAuthToken)
    {
        $this->userAuth = $userAuthToken;
    }

    /**
     * @param $xml
     *
     * @return int
     * @throws AuthException
     */
    public function process($xml)
    {
        if (null === $xml->user_auth_token) {
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
