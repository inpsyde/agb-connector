<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class ActionTagException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class ActionTagException extends XmlApiException
{
    public function __construct($message, $code = 10, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
