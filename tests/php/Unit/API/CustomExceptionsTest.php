<?php

namespace Inpsyde\AGBConnector;

use CustomExceptionsTests;
use Inpsyde\AGBConnector\customExceptions\NotSimpleXmlInstanceException;
use PHPUnit\Framework\TestCase;

class CustomExceptionsTest extends TestCase
{
    public function testCustomException()
    {
        $message = 'this is not a xml instance';
        $code = '12';
        try {
            throw new NotSimpleXmlInstanceException($message, $code);
        }catch (NotSimpleXmlInstanceException $exception){
            $catchedMessage = $exception->getMessage();
            $catchedCode = $exception->getCode();
        }
        self::assertEquals($message, $catchedMessage);
        self::assertEquals($code, $catchedCode);
    }
}
