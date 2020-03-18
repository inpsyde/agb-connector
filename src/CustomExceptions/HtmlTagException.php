<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class HtmlTagException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class HtmlTagException extends XmlApiException
{
    const CODE = 6;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
