<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\actionTagException;

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
     * @return Exception|actionTagException|int
     */
    public function process($xml)
    {
        try {
            if (null === $xml->action) {
                throw new actionTagException(
                    'actionTagException: null provided',
                    10
                );
            }
            if ('push' !== (string)$xml->action) {
                throw new actionTagException(
                    "actionTagException: not push provided: {$xml->action}",
                    10
                );
            }
            return parent::process($xml);
        } catch (actionTagException $exception) {
            return $exception;
        }
    }
}
