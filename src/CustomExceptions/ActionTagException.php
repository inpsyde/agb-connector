<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class ActionTagException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class ActionTagException extends XmlApiException
{
    const CODE = 10;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
