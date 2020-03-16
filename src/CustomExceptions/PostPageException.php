<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class PostPageException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class PostPageException extends XmlApiException
{
    const CODE = 81;
    public function __construct($message)
    {
        parent::__construct($message, self::CODE);
    }
}
