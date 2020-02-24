<?php

namespace Inpsyde\AGBConnector\Middleware;

use Exception;
use Inpsyde\AGBConnector\CustomExceptions\TitleException;

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
                throw new TitleException(
                    "Title Exception: null provided",
                    18
                );
            }
            if (strlen((string)$xml->rechtstext_title) < 3) {
                throw new TitleException(
                    "Title Exception: length < 3",
                    18
                );
            }
            return parent::process($xml);
        } catch (TitleException $exception) {
            return $exception;
        }
    }
}
