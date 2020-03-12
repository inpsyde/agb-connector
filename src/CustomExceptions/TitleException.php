<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class TitleException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class TitleException extends XmlApiException
{
    const CODE = 18;
    public function __construct($message)
    {
        parent::__construct($message,self::CODE);
    }
}
