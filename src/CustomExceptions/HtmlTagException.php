<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class HtmlTagException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class HtmlTagException extends XmlApiException
{
    public function __construct($message, $code = 6, XmlApiException $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
