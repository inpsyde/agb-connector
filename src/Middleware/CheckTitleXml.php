<?php

namespace Inpsyde\AGBConnector\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\TitleException;
use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;

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
     * @return bool|XmlApiException|TitleException
     */
    public function process($xml)
    {
        try {
            if (null === $xml->rechtstext_title) {
                throw new TitleException(
                    "There must be a title, null provided"
                );
            }
            if (strlen((string)$xml->rechtstext_title) < 3) {
                throw new TitleException(
                    "Title length must be greater than 3"
                );
            }
            return parent::process($xml);
        } catch (TitleException $exception) {
            return $exception;
        }
    }
}
