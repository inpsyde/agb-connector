<?php

namespace Inpsyde\AGBConnector\Middleware;

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
     * @return int
     * @throws ActionTagException
     */
    public function process($xml)
    {
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
    }
}
