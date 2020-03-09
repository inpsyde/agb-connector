<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class TitleException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class TitleException extends XmlApiException
{
    public function __construct($message, $code = 18, XmlApiException $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
