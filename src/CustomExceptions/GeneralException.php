<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class GeneralException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class GeneralException extends XmlApiException
{
    public function __construct($message, $code = 99, XmlApiException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
