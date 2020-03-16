<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class PdfMD5Exception
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class PdfMD5Exception extends XmlApiException
{
    const CODE = 8;
    public function __construct($message)
    {
        parent::__construct($message, self::CODE);
    }
}
