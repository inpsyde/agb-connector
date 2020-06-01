<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\CountryException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\XmlApiSupportedService;
use SimpleXMLElement;

/**
 * Class CheckCountrySetXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckCountrySetXml extends Middleware
{
    /**
     * @var XmlApiSupportedService
     */
    protected $supportedCountries;

    /**
     * CheckCountrySetXml constructor.
     *
     * @param array $supported
     */
    public function __construct(array $supported)
    {
        $this->supportedCountries = $supported;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if ($xml->rechtstext_country === null) {
            throw new CountryException(
                'No country provided'
            );
        }
        if (! array_key_exists((string)$xml->rechtstext_country, $this->supportedCountries)) {
            throw new CountryException(
                "Country {$xml->rechtstext_country} is not supported"
            );
        }
        return parent::process($xml);
    }
}
