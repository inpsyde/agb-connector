<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class NotSimpleXmlInstanceException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class NotSimpleXmlInstanceException extends XmlApiException
{
    public function __construct($message, $code = 12, XmlApiException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
