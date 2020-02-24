<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\ConfigurationException;

/**
 * Class CheckConfiguration
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckConfiguration extends Middleware
{
    /**
     * @var API $userAuth
     */
    private $userAuthToken;
    /**
     * @var API $textAllocations
     */
    private $textAllocations;

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
     * @param $xml
     *
     * @return bool|Exception|configurationException\
     */
    public function process($xml)
    {
        try {
            if (!$this->checkConfiguration($this->userAuthToken)) {
                throw new ConfigurationException(
                    'ConfigurationException: no userAuthToken configured',
                    80
                );
            }
            if (!isset($this->textAllocations[(string)$xml->rechtstext_type])) {
                throw new ConfigurationException(
                    'ConfigurationException: no textAllocations configured',
                    80
                );
            }
            return parent::process($xml);
        } catch (ConfigurationException $exception) {
            return $exception;
        }
    }

    /**
     * Check XML for errors.
     *
     * @return bool
     * @since 1.1.0
     *
     */
    public function checkConfiguration($userAuthToken)
    {
        if (!$userAuthToken) {
            return false;
        }

        return true;
    }
}
