<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\LanguageException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class CheckLanguageXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckLanguageXml extends Middleware
{
    /**
     * @var array
     */
    protected $supportedLanguages;

    /**
     * CheckCountrySetXml constructor.
     *
     * @param array $supported
     */
    public function __construct(array $supported)
    {
        $this->supportedLanguages = $supported;
    }
    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if ($xml->rechtstext_language === null) {
            throw new LanguageException(
                'No language provided'
            );
        }
        if (!array_key_exists(
            (string)$xml->rechtstext_language,
            $this->supportedLanguages
        )
        ) {
            throw new LanguageException(
                "Language {$xml->rechtstext_language} is not supported"
            );
        }
        return parent::process($xml);
    }
}
