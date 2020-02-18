<?php

namespace Inpsyde\AGBConnector;

use CustomExceptionsTests;
use Inpsyde\AGBConnector\customExceptions\notSimpleXmlInstanceException;
use PHPUnit\Framework\TestCase;

class CustomExceptionsTest extends TestCase
{
    /**
     * @test
     */
    public function customException()
    {
        $message = 'this is not a xml instance';
        $code = '12';
        try {
            throw new notSimpleXmlInstanceException($message, $code);
        }catch (notSimpleXmlInstanceException $exception){
            $catchedMessage = $exception->getMessage();
            $catchedCode = $exception->getCode();
        }
        self::assertEquals($message, $catchedMessage);
        self::assertEquals($code, $catchedCode);
    }
    //hay que hacer un data provider con message, code y la excepción para cada una
    // y pasarlo en un único test

}
