<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\authException;

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
     * @return Exception|authException|int
     */
    public function process($xml)
    {
        try {
            if (null === $xml->user_auth_token) {
                throw new authException(
                    "Auth Exception: null user_auth_token",
                    3
                );
            }
            if ((string)$xml->user_auth_token !== $this->userAuth) {
                throw new authException(
                    "Auth Exception: userAuthToken doesn't match",
                    3
                );
            }
            return parent::process($xml);
        } catch (authException $exception) {
            return $exception;
        }
    }
}
