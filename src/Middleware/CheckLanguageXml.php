<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\LanguageException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\XmlApi;

/**
 * Class CheckLanguageXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckLanguageXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|XmlApiException|LanguageException
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_language) {
                throw new LanguageException(
                    'No language provided'
                );
            }
            if (!array_key_exists(
                (string)$xml->rechtstext_language,
                XmlApi::supportedLanguages()
            )
            ) {
                throw new LanguageException(
                    "Language {$xml->rechtstext_language} is not supported"
                );
            }
            return parent::process($xml);
        } catch (LanguageException $exception) {
            return $exception;
        }
    }
}