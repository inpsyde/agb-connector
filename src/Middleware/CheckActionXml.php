<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\ActionTagException;

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
     * @return Exception|ActionTagException|int
     */
    public function process($xml)
    {
        try {
            if (null === $xml->action) {
                throw new ActionTagException(
                    'ActionTagException: null provided',
                    10
                );
            }
            if ('push' !== (string)$xml->action) {
                throw new ActionTagException(
                    "ActionTagException: not push provided: {$xml->action}",
                    10
                );
            }
            return parent::process($xml);
        } catch (ActionTagException $exception) {
            return $exception;
        }
    }
}
