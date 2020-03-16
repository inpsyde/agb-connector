<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class PdfFilenameException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class PdfFilenameException extends XmlApiException
{
    const CODE = 19;
    public function __construct($message)
    {
        parent::__construct($message, self::CODE);
    }
}
