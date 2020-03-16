<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\Middleware\MiddlewareRequestHandler;

/**
 * Class XmlApi
 */
class XmlApi
{

    /**
     * API Version
     *
     * @var string
     */
    const VERSION = '1.0';

    /**
     * API Username that must match. Left empty fo no checking
     *
     * @var string
     */
    const USERNAME = 'inpsyde';

    /**
     * API Password that must match
     *
     * @var string
     */
    const PASSWORD = 'oIN9pBGPp98g';

    /**
     * User auth token that must match
     *
     * @var string
     */
    private $userAuthToken;

    /**
     * Text allocations
     *
     * @var array
     */
    private $textAllocations;

    /**
     * Define some values.
     *
     * @param string $userAuthToken User Auth Token.
     * @param array $textAllocations allocations for Texts.
     */
    public function __construct($userAuthToken, array $textAllocations = null)
    {
        $this->userAuthToken = $userAuthToken;
        $this->textAllocations = $textAllocations;
    }

    /**
     * Get the request and answers it.
     *
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     *
     * @param string $xml XML from push.
     *
     * @return string xml response
     */
    public function handleRequest($xml)
    {
        $xmlErrorState = libxml_use_internal_errors(true);
        $xml = trim(stripslashes($xml));
        if ($xml) {
            $xml = simplexml_load_string($xml);
        }
        libxml_use_internal_errors($xmlErrorState);

        $handler = new MiddlewareRequestHandler($this->userAuthToken, $this->textAllocations);

        return $handler->handle($xml);
    }

    /**
     * Get Supported languages
     *
     * @return array
     */
    public static function supportedLanguages()
    {
        return [
            'de' => __('German', 'agb-connector'),
            'fr' => __('French', 'agb-connector'),
            'en' => __('English', 'agb-connector'),
            'es' => __('Spanish', 'agb-connector'),
            'it' => __('Italian', 'agb-connector'),
            'nl' => __('Dutch', 'agb-connector'),
            'pl' => __('Polish', 'agb-connector'),
            'sv' => __('Swedish', 'agb-connector'),
            'da' => __('Danish', 'agb-connector'),
            'cs' => __('Czech', 'agb-connector'),
            'sl' => __('Slovenian', 'agb-connector'),
            'pt' => __('Portuguese', 'agb-connector'),
        ];
    }

    /**
     * Get Supported countries
     *
     * @return array
     */
    public static function supportedCountries()
    {
        return [
            'DE' => __('Germany', 'agb-connector'),
            'AT' => __('Austria', 'agb-connector'),
            'CH' => __('Switzerland', 'agb-connector'),
            'SE' => __('Sweden', 'agb-connector'),
            'ES' => __('Spain', 'agb-connector'),
            'IT' => __('Italy', 'agb-connector'),
            'PL' => __('Poland', 'agb-connector'),
            'GB' => __('England', 'agb-connector'),
            'FR' => __('France', 'agb-connector'),
            'BE' => __('Belgium', 'agb-connector'),
            'NL' => __('Netherlands', 'agb-connector'),
            'US' => __('USA', 'agb-connector'),
            'CA' => __('Canada', 'agb-connector'),
            'IE' => __('Ireland', 'agb-connector'),
            'CZ' => __('Czech Republic', 'agb-connector'),
            'DK' => __('Denmark', 'agb-connector'),
            'LU' => __('Luxembourg', 'agb-connector'),
            'SI' => __('Slovenia', 'agb-connector'),
            'AU' => __('Australia', 'agb-connector'),
            'PT' => __('Portugal', 'agb-connector'),
        ];
    }

    /**
     * Get supported text types
     * @return array
     */
    public static function supportedTextTypes()
    {
        return [
            'agb' => __('Terms and Conditions', 'agb-connector'),
            'datenschutz' => __('Privacy', 'agb-connector'),
            'widerruf' => __('Revocation', 'agb-connector'),
            'impressum' => __('Imprint', 'agb-connector'),
        ];
    }
}
