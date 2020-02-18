<?php


namespace Inpsyde\AGBConnector\Middleware;


use Inpsyde\AGBConnector\customExceptions\authException;
use Inpsyde\AGBConnector\XmlApi;

class CheckAuthXml extends Middleware
{
    private $userAuth;
    /**
     * CheckAuthXml constructor.
     */
    public function __construct($userAuthToken)
    {
        $this->userAuth = $userAuthToken;
    }

    /**
     * @param      $xml
     *
     * @param null $userAuthToken
     *
     * @return bool|\Exception|authException
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
