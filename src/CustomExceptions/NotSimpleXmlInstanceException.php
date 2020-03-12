<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class NotSimpleXmlInstanceException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class NotSimpleXmlInstanceException extends XmlApiException
{
    const CODE = 12;
    public function __construct($message)
    {
        parent::__construct($message,self::CODE);
    }
}
