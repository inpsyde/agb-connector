<?php

namespace Inpsyde\AGBConnectorTests\Unit\API;

use Inpsyde\AGBConnector\CustomExceptions\LanguageException;
use Inpsyde\AGBConnector\CustomExceptions\NotSimpleXmlInstanceException;
use Inpsyde\AGBConnector\Plugin;
use Inpsyde\AGBConnector\XmlApi;
use Inpsyde\AGBConnector\Middleware\MiddlewareRequestHandler;
use Inpsyde\AGBConnector\XmlApiSupportedService;
use Inpsyde\AGBConnectorTests\TestCase;
use function Brain\Monkey\Functions\when;


class MiddlewareRequestHandlerTest extends TestCase
{
    protected $userAuthToken;
    protected $allocations;
    protected $handler;
    /**
     * @var XmlApiSupportedService
     */
    protected $apiSupportedService;

    protected function setUp(): void
    {
        $this->allocations = ['agb' => 'agb'];
        $this->userAuthToken = '1234567890abcdefghijklmnopqrstuv';

        when('__')
            ->returnArg();


        $this->handler = new MiddlewareRequestHandler($this->userAuthToken, $this->allocations, new XmlApiSupportedService());
        parent::setUp();
    }

    public function testReturnXmlSuccess()
    {
        global $wp_version;
        $wp_version = '4.0.0';

        $xml = '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>success</status>
			<meta_shopversion>' . $wp_version . '</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>';

        $testee = $this->handler->returnXmlWithSuccess(0);

        $this->assertXmlStringEqualsXmlString($xml, $testee);
    }

    public function testReturnXmlSuccessWithUrl()
    {
        global $wp_version;
        $wp_version = '4.0.0';

        $url = 'http://test.de/agb';

        $xml = '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>success</status>
			<target_url><![CDATA[' . $url . ']]></target_url>
			<meta_shopversion>' . $wp_version . '</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>';

        $testee = $this->handler->returnXmlWithSuccess(0, $url);

        $this->assertXmlStringEqualsXmlString($xml, $testee);
    }

    public function testReturnXmlError()
    {
        global $wp_version;
        $wp_version = '4.0.0';
        $errorCode = new LanguageException("LanguageException: not supported Chinese provided");

        $xml = '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>'. $errorCode->getCode() . '</error>
			<error_message><![CDATA[' . $errorCode->getMessage() . ']]></error_message>
			<meta_shopversion>' . $wp_version . '</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>';
        $testee = $this->handler->returnXmlWithError($errorCode);

        $this->assertXmlStringEqualsXmlString($xml, $testee);
    }
    /**
     *
     * @dataProvider handlerDataProvider
     *
     * @param $xml
     * @param $errorResponse
     */
    public function testHandlerThrowsCorrectExceptionCode($xml, $errorResponse)
    {
        global $wp_version;
        $wp_version = '4.0.0';
        $xml = trim(stripslashes($xml));
        if ($xml) {
            $xml = simplexml_load_string($xml);
        }

        $result = $this->handler->handle($xml);

        $this->assertXmlStringEqualsXmlString($errorResponse, $result);
    }

    public function testHandlerThrows12ExceptionCode()
    {
        global $wp_version;
        $wp_version = '4.0.0';
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>12
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>';

        $xml = trim(stripslashes($xml));
        if ($xml) {
            $xml = @simplexml_load_string($xml);
        }
        $errorCode = new NotSimpleXmlInstanceException("Not xml provided");

        $errorResponse = '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>'. $errorCode->getCode() . '</error>
			<error_message><![CDATA[' . $errorCode->getMessage() . ']]></error_message>
			<meta_shopversion>' . $wp_version . '</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>';

        $result = $this->handler->handle($xml);

        $this->assertXmlStringEqualsXmlString($errorResponse, $result);
    }


    /**
     * Data Provider for testHandlerThrowsCorrectExceptionResponse
     *
     * @return array
     */
    public function handlerDataProvider()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>1' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>1</error>
			<error_message><![CDATA[Version provided 11.0 does not match the current one]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '2</api_username>
			<api_password>' . XmlApi::PASSWORD . '2</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>2</error>
			<error_message><![CDATA[Incorrect username or password]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv3</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>3</error>
			<error_message><![CDATA[Auth Exception: userAuthToken doesn\'t match]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb4</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>4</error>
			<error_message><![CDATA[The text type provided is not supported]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>5</error>
			<error_message><![CDATA[The text size must be greater than 50]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>6</error>
			<error_message><![CDATA[Html tag length must be greater than 50]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>' . md5_file(__DIR__ . '/../../../assets/test.pdf') . '</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>7</error>
			<error_message><![CDATA[Pdf url is empty]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
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
			<rechtstext_language>zz</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>9</error>
			<error_message><![CDATA[Language zz is not supported]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
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
			<action>push10</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>10</error>
			<error_message><![CDATA[ActionTag: not push provided: push10]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>impressum</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_text>
			<rechtstext_html>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_html>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>ZZ</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>17</error>
			<error_message><![CDATA[Country ZZ is not supported]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . XmlApi::VERSION . '</api_version>
			<api_username>' . XmlApi::USERNAME . '</api_username>
			<api_password>' . XmlApi::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Ti</rechtstext_title>
			<rechtstext_text>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_text>
			<rechtstext_html>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>18</error>
			<error_message><![CDATA[Title length must be greater than 3]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],
            [
                '<?xml version="1.0" encoding="UTF-8" ?>
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
			<rechtstext_pdf_filename_suggestion></rechtstext_pdf_filename_suggestion>
			<rechtstext_pdf_filenamebase_suggestion></rechtstext_pdf_filenamebase_suggestion>
			<rechtstext_language>zz</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>19</error>
			<error_message><![CDATA[The pdf filename is empty]]></error_message>
			<meta_shopversion>4.0.0</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>'
            ],


        ];
    }


}
