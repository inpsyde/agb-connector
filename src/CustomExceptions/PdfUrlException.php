<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class PdfUrlException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class PdfUrlException extends XmlApiException
{
    const CODE = 7;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
