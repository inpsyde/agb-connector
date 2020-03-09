<?php

namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\XmlApi;
use Inpsyde\AGBConnector\Middleware\MiddlewareRequestHandler;
use PHPUnit\Framework\TestCase;


class MiddlewareRequestHandlerTest extends TestCase
{
    /**
     *
     * @dataProvider handlerDataProvider
     *
     * @param $xml
     * @param $code
     */
    public function testHandlerThrowsCorrectExceptionCode($xml, $code)
    {
        $userAuthToken = '1234567890abcdefghijklmnopqrstuv';
        $allocations = ['agb' => 'agb'];

        $xml = trim(stripslashes($xml));
        if ($xml) {
            $xml = simplexml_load_string($xml);
        }

        $handler = new MiddlewareRequestHandler($userAuthToken, $allocations);
        $result = $handler->handle($xml);

        self::assertEquals($code, $result->getCode());
    }

    public function testHandlerThrows12ExceptionCode()
    {
        $userAuthToken = '1234567890abcdefghijklmnopqrstuv';

        $allocations = ['agb' => 'agb'];
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

        $handler = new MiddlewareRequestHandler($userAuthToken, $allocations);
        $result = $handler->handle($xml);

        self::assertEquals(12, $result->getCode());
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
                1
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
                2
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
                3
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
                4
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
                5
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
                6
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
                7
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
                9
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
                10
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
                17
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
                18
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
                19
            ],


        ];
    }

    /**
     * Test the MiddlewareRequestHandler finish middleware line
     * without Exceptions thrown. Returns zero.
     *
     * @dataProvider xmlForZeroDataProvider
     */
    public function testHandlerFinishWithZero($xml, $allocations)
    {
        $userAuthToken = '1234567890abcdefghijklmnopqrstuv';

        $xml = trim(stripslashes($xml));
        if ($xml) {
            $xml = simplexml_load_string($xml);
        }

        $handler = new MiddlewareRequestHandler($userAuthToken, $allocations);
        $result = $handler->handle($xml);
        self::assertEquals(0, $result);
    }

    /**
     * Data Provider for testHandlerFinishWithZero
     *
     * @return array
     */
    public function xmlForZeroDataProvider()
    {
        return [
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
			<action>push</action>
		</api>',
                ['agb' => 'agb']
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
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>',
                ['impressum' => 'impressum']
            ],
        ];
    }
}
