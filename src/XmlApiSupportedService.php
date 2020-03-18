<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnector;

/**
 * Class XmlApi
 */
class XmlApiSupportedService
{
    /**
     * Get Supported languages
     *
     * @return array
     */
    public function supportedLanguages()
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
    public function supportedCountries()
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
    public function supportedTextTypes()
    {
        return [
            'agb' => __('Terms and Conditions', 'agb-connector'),
            'datenschutz' => __('Privacy', 'agb-connector'),
            'widerruf' => __('Revocation', 'agb-connector'),
            'impressum' => __('Imprint', 'agb-connector'),
        ];
    }
}
