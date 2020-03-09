<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class PostPageException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class PostPageException extends XmlApiException
{
    public function __construct($message, $code = 81, XmlApiException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
