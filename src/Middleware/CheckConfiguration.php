<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\ConfigurationException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class CheckConfiguration
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckConfiguration extends Middleware
{
    /**
     * @var string $userAuth
     */
    protected $userAuthToken;

    /**
     * CheckConfiguration constructor.
     *
     * @param $userAuthToken
     */
    public function __construct($userAuthToken)
    {
        $this->userAuthToken = $userAuthToken;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        $this->checkConfiguration($this->userAuthToken);
        return parent::process($xml);
    }

    /**
     * Check XML for errors.
     *
     * @param $userAuthToken
     *
     * @return void
     * @throws ConfigurationException
     * @since 1.1.0
     */
    protected function checkConfiguration($userAuthToken)
    {
        if (!$userAuthToken) {
            throw new ConfigurationException(
                'ConfigurationException: no userAuthToken configured'
            );
        }
    }
}
