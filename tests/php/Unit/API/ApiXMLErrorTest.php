<?php # -*- coding: utf-8 -*-
namespace Inpsyde\AGBConnector;

use Inpsyde\AGBConnector\CustomExceptions\LanguageException;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiXMLErrorTest
 */
class ApiXMLErrorTest extends TestCase
{
    public function testReturnXmlSuccess()
    {
        global $wp_version;

        $wp_version = '4.0.0';
        $api = new XmlApi('');

        $xml = '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>success</status>
			<meta_shopversion>' . $wp_version . '</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>';

        $testee = $api->returnXmlWithSuccess(0);

        $this->assertXmlStringEqualsXmlString($xml, $testee);
    }

    public function testReturnXmlSuccessWithUrl()
    {
        global $wp_version;

        $wp_version = '4.0.0';
        $api = new XmlApi('');
        $url = 'http://test.de/agb';

        $xml = '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>success</status>
			<target_url><![CDATA[' . $url . ']]></target_url>
			<meta_shopversion>' . $wp_version . '</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>';

        $testee = $api->returnXmlWithSuccess(0, $url);

        $this->assertXmlStringEqualsXmlString($xml, $testee);
    }

    public function testReturnXmlError()
    {
        global $wp_version;

        $wp_version = '4.0.0';
        $api = new XmlApi('');
        $errorCode = new LanguageException("LanguageException: not supported Chinese provided",
                                           9);

        $xml = '<?xml version="1.0" encoding="utf-8" ?>
		<response>
			<status>error</status>
			<error>'. $errorCode->getCode() . '</error>
			<error_message><![CDATA[' . $errorCode->getMessage() . ']]></error_message>
			<meta_shopversion>' . $wp_version . '</meta_shopversion>
			<meta_modulversion>' . Plugin::VERSION . '</meta_modulversion>
			<meta_phpversion>' . PHP_VERSION . '</meta_phpversion>
		</response>';

        $testee = $api->returnXmlWithError($errorCode);

        $this->assertXmlStringEqualsXmlString($xml, $testee);
    }
}
