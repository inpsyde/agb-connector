<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\CountryException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\XmlApi;

/**
 * Class CheckCountryXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckCountryXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool
     * @throws CountryException
     */
    public function process($xml)
    {
        if (null === $xml->rechtstext_country) {
            throw new CountryException(
                'Country Exception: null provided'
            );
        }
        if (! array_key_exists((string)$xml->rechtstext_country, XmlApi::supportedCountries())) {
            throw new CountryException(
                "Country Exception: provided {$xml->rechtstext_country} is not supported"
            );
        }
        return parent::process($xml);
    }
}
