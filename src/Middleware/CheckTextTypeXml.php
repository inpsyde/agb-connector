<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\TextTypeException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

use function array_key_exists;

/**
 * Class CheckTextTypeXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckTextTypeXml extends Middleware
{
    /**
     * @var array
     */
    protected $supportedTextTypes;

    /**
     * CheckCountrySetXml constructor.
     *
     * @param array $supported
     */
    public function __construct(array $supported)
    {
        $this->supportedTextTypes = $supported;
    }
    /**
     * @param SimpleXMLElement $xml
     *
     * @return bool
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if ($xml->rechtstext_type === null) {
            throw new TextTypeException(
                "No text type provided"
            );
        }
        if (! array_key_exists((string)$xml->rechtstext_type, $this->supportedTextTypes)
        ) {
            throw new TextTypeException(
                "The text type provided is not supported"
            );
        }
        return parent::process($xml);
    }
}
