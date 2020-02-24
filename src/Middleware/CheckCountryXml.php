<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\countryException;
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
     * @return bool|Exception|countryException
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_country) {
                throw new countryException(
                    'Country Exception: null provided',
                    17
                );
            }
            if (! array_key_exists((string)$xml->rechtstext_country, XmlApi::supportedCountries())) {
                throw new countryException(
                    "Country Exception: provided {$xml->rechtstext_country} is not supported",
                    17
                );
            }
            return parent::process($xml);
        } catch (countryException $exception) {
            return $exception;
        }
    }
}
