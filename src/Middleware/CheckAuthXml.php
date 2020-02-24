<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
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
     * @return Exception|AuthException|int
     */
    public function process($xml)
    {
        try {
            if (null === $xml->user_auth_token) {
                throw new AuthException(
                    "Auth Exception: null user_auth_token",
                    3
                );
            }
            if ((string)$xml->user_auth_token !== $this->userAuth) {
                throw new AuthException(
                    "Auth Exception: userAuthToken doesn't match",
                    3
                );
            }
            return parent::process($xml);
        } catch (AuthException $exception) {
            return $exception;
        }
    }
}
