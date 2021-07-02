<?php

namespace Inpsyde\AGBConnectorTests\Unit\Middleware;

use Inpsyde\AGBConnector\CustomExceptions\XmlApiException;
use Inpsyde\AGBConnector\Middleware\CheckConfiguration;
use Inpsyde\AGBConnector\XmlApi;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function testCheckConfigurationExceptionCode()
    {
        $userAuthToken = '';
        $allocations = ['agb' => 'agb'];
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>' . __DIR__ . '/../../../assets/test.pdf' . '</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>' . md5_file(__DIR__ . '/../../../assets/test.pdf') . '</rechtstext_pdf_md5hash>
			<rechtstext_pdf_filename_suggestion>AGB_DE.pdf</rechtstext_pdf_filename_suggestion>
			<rechtstext_pdf_filenamebase_suggestion>AGB</rechtstext_pdf_filenamebase_suggestion>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>';

        $xml = trim(stripslashes($xml));
        if ($xml) {
            $xml = simplexml_load_string($xml);
        }

        $middleware = new CheckConfiguration($userAuthToken);
        try {
            $middleware->process($xml);
        }
        catch (XmlApiException $exception){
            $result = $exception;
        }


        self::assertEquals(80, $result->getCode());
    }
}
