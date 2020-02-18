<?php


namespace Inpsyde\AGBConnector\Middleware;


use Inpsyde\AGBConnector\customExceptions\languageException;
use Inpsyde\AGBConnector\XmlApi;

class CheckLanguageXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|\Exception|languageException\
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_language) {
                throw new languageException(
                    'languageException: null provided',
                    9
                );
            }
            if (! array_key_exists((string)$xml->rechtstext_language, XmlApi::supportedLanguages()
                )) {
                throw new languageException(
                    "languageException: not supported {$xml->rechtstext_language} provided",
                    9
                );
            }
            return parent::process($xml);
        } catch (languageException $exception) {
            return $exception;
        }
    }
}
