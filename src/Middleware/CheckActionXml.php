<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\ActionTagException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use SimpleXMLElement;

/**
 * Class CheckActionXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckActionXml extends Middleware
{

    /**
     * @param SimpleXMLElement $xml
     *
     * @return int
     * @throws XmlApiException
     */
    public function process($xml)
    {
        if ($xml->action === null) {
            throw new ActionTagException(
                'ActionTag: null provided'
            );
        }
        if ('push' !== (string)$xml->action) {
            throw new ActionTagException(
                "ActionTag: not push provided: {$xml->action}"
            );
        }
        return parent::process($xml);
    }
}
