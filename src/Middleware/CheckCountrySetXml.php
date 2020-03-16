<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\CountryException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\XmlApi;

/**
 * Class CheckCountrySetXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckCountrySetXml extends Middleware
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
                'No country provided'
            );
        }
        if (! array_key_exists((string)$xml->rechtstext_country, XmlApi::supportedCountries())) {
            throw new CountryException(
                "Country {$xml->rechtstext_country} is not supported"
            );
        }
        return parent::process($xml);
    }
}
