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
     * @var array $textAllocations
     */
    protected $textAllocations;

    /**
     * CheckConfiguration constructor.
     *
     * @param $userAuthToken
     * @param $textAllocations
     */
    public function __construct($userAuthToken, $textAllocations)
    {
        $this->userAuthToken = $userAuthToken;
        $this->textAllocations = $textAllocations;
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
        if (!isset($this->textAllocations[(string)$xml->rechtstext_type])) {
            throw new ConfigurationException(
                'ConfigurationException: no textAllocations configured'
            );
        }
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
