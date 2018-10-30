<?php # -*- coding: utf-8 -*-
use PHPUnit\Framework\TestCase;

/**
 * Class ApiXMLErrorTest
 */
class ApiXMLErrorTest extends TestCase
{

    protected $pdf_file = '';
    protected $pdf_file_md5 = '';

    public function setUp()
    {

        $this->pdf_file = __DIR__ . '/../../../assets/test.pdf';
        $this->pdf_file_md5 = md5_file($this->pdf_file);
    }


    public function testAPIError0()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>' . $this->pdf_file . '</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>' . $this->pdf_file_md5 . '</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 0);
    }

    public function testAPIError1()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>1' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
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

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 1);
    }

    public function testAPIError2()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '2</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '2</api_password>
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

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 2);

    }

    public function testAPIError3()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
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
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 3);

    }

    public function testAPIError4()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
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
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 4);

    }


    public function testAPIError5()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
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
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 5);

    }


    public function testAPIError6()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
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
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 6);

    }


    public function testAPIError7()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>' . $this->pdf_file . '7</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>' . $this->pdf_file_md5 . '</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 7);

    }


    public function testAPIError8()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>' . $this->pdf_file . '</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>098f6bcd4621d373cade4e832627b4f6</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 8);

    }


    public function testAPIError9()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');
        $api->setSupportedLanguage('en');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>' . $this->pdf_file . '</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>' . $this->pdf_file_md5 . '</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 9);
    }


    public function testAPIError10()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>' . $this->pdf_file . '</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>' . $this->pdf_file_md5 . '</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push10</action>
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 10);
    }

    public function testAPIError12()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>12
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
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

        $error = $api->checkXmlForError(@simplexml_load_string($xml), true);

        $this->assertEquals($error, 12);
    }

    public function testDonotCheckPdf()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>impressum</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_text>
			<rechtstext_html>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_html>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), false);

        $this->assertEquals($error, 0);
    }

    public function testCountryNotSupported()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>impressum</rechtstext_type>
			<rechtstext_title>Title</rechtstext_title>
			<rechtstext_text>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_text>
			<rechtstext_html>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_html>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>FR</rechtstext_country>
			<rechtstext_language_iso639_2b>ger</rechtstext_language_iso639_2b>
			<action>push</action>
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), false);

        $this->assertEquals($error, 17);
    }

    public function testAPIError18()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, '1234567890abcdefghijklmnopqrstuv');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<api>
			<api_version>' . AGBConnectorAPI::VERSION . '</api_version>
			<api_username>' . AGBConnectorAPI::USERNAME . '</api_username>
			<api_password>' . AGBConnectorAPI::PASSWORD . '</api_password>
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
		</api>';

        $error = $api->checkXmlForError(simplexml_load_string($xml), true);

        $this->assertEquals($error, 18);
    }

    public function testAPIError80()
    {

        $api = new \AGBConnectorAPI(AGBConnector::VERSION, 'qqweqweqewq');

        $this->assertFalse($api->checkConfiguration());
    }

}
