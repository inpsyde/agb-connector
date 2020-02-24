<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\titleException;

/**
 * Class CheckTitleXml
 *
 * @package Inpsyde\AGBConnector\Middleware
 */
class CheckTitleXml extends Middleware
{
    /**
     * @param $xml
     *
     * @return bool|Exception|titleException\
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_title) {
                throw new titleException(
                    "Title Exception: null provided",
                    18
                );
            }
            if (strlen((string)$xml->rechtstext_title) < 3) {
                throw new titleException(
                    "Title Exception: length < 3",
                    18
                );
            }
            return parent::process($xml);
        } catch (titleException $exception) {
            return $exception;
        }
    }
}
