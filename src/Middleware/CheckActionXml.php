<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\ActionTagException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;

/**
 * Class CheckActionXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckActionXml extends Middleware
{

    /**
     * @param $xml
     *
     * @return XmlApiException|ActionTagException|int
     */
    public function process($xml)
    {
        try {
            if (null === $xml->action) {
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
        } catch (ActionTagException $exception) {
            return $exception;
        }
    }
}
