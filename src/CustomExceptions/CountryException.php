<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class CountryException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class CountryException extends XmlApiException
{
    const CODE = 17;
    public function __construct($message)
    {
        parent::__construct($message, self::CODE);
    }
}
