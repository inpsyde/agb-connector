<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class GeneralException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class GeneralException extends XmlApiException
{
    const CODE = 99;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
