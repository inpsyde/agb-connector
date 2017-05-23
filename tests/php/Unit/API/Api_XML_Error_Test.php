<?php # -*- coding: utf-8 -*-
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\WP\Filters;

/**
 * Class Api_XML_Error_Test
 */
class Api_XML_Error_Test extends TestCase {

	protected $pdf_file = '';
	protected $pdf_file_md5 = '';
	/**
	 * @var \AGB_Connector
	 */
	protected $plugin = null;

	public function setUp() {

		$this->pdf_file = __DIR__ . '/../../../assets/test.pdf';
		$this->pdf_file_md5 = md5_file( $this->pdf_file );
		$this->pdf_file_url = '';


		Functions::when( 'wp_remote_get' )->alias(
			function( $url, $args = array() ) {

				if ( ! file_exists( $url ) ) {
					return FALSE;
				}
				$content = file_get_contents( $url, FALSE );

				if ( ! $content ) {
					return FALSE;
				}

				return array(
					'body'     => $content,
					'response' => array(
							'code' => 200,
						),
				);
			}
		);
		Functions::when( 'is_wp_error' )->alias(
			function( $possible_error ) {
				if ( FALSE === $possible_error )
					return TRUE;
				return FALSE;
			}
		);
		Functions::when( 'wp_remote_retrieve_body' )->alias(
			function( $response ) {
				if ( ! isset( $response['body'] ) )
					return '';
				return $response['body'];
			}
		);
		Functions::when( 'wp_remote_retrieve_response_code' )->alias(
			function( $response ) {
				return $response['response']['code'];
			}
		);

		$this->plugin = new \AGB_Connector();
	}


	public function test_API_Error_0() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>" . $this->pdf_file . "</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>" . $this->pdf_file_md5 . "</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 0 );
	}

	public function test_API_Error_1() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>1" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 1 );
	}

	public function test_API_Error_2() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "2</api_username>
			<api_password>" . $api->api_password . "2</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 2 );

	}

	public function test_API_Error_3() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv3</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 3 );

	}

	public function test_API_Error_4() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb4</rechtstext_type>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 4 );

	}


	public function test_API_Error_5() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 5 );

	}


	public function test_API_Error_6() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 6 );

	}


	public function test_API_Error_7() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>" . $this->pdf_file . "7</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>" . $this->pdf_file_md5 . "</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 7 );

	}


	public function test_API_Error_8() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>" . $this->pdf_file . "</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>098f6bcd4621d373cade4e832627b4f6</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 8 );

	}


	public function test_API_Error_9() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );
		$api->set_supported_language( 'en' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>" . $this->pdf_file . "</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>" . $this->pdf_file_md5 . "</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 9 );
	}


	public function test_API_Error_10() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text>123456789012345678901234567890123456789012345678901</rechtstext_text>
			<rechtstext_html>123456789012345678901234567890123456789012345678901</rechtstext_html>
			<rechtstext_pdf_url>" . $this->pdf_file . "</rechtstext_pdf_url>
			<rechtstext_pdf_md5hash>" . $this->pdf_file_md5 . "</rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push10</action>
		</api>";

		$error = $api->check_xml_for_error( simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 10 );
	}

	public function test_API_Error_12() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>12
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>agb</rechtstext_type>
			<rechtstext_text></rechtstext_text>
			<rechtstext_html></rechtstext_html>
			<rechtstext_pdf_url></rechtstext_pdf_url>
			<rechtstext_pdf_md5hash></rechtstext_pdf_md5hash>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( @simplexml_load_string( $xml ), true );

		$this->assertEquals( $error, 12 );
	}

	public function test_donotcheck_pdf() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>impressum</rechtstext_type>
			<rechtstext_text>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_text>
			<rechtstext_html>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_html>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>DE</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( @simplexml_load_string( $xml ), false );

		$this->assertEquals( $error, 0 );
	}

	public function test_country_not_supported() {

		$api = new \AGB_Connector_API( $this->plugin->get_plugin_version(), '1234567890abcdefghijklmnopqrstuv' );

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<api>
			<api_version>" . $api->api_version . "</api_version>
			<api_username>" . $api->api_username . "</api_username>
			<api_password>" . $api->api_password . "</api_password>
			<user_auth_token>1234567890abcdefghijklmnopqrstuv</user_auth_token>
			<rechtstext_type>impressum</rechtstext_type>
			<rechtstext_text>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_text>
			<rechtstext_html>1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv1234567890abcdefghijklmnopqrstuv</rechtstext_html>
			<rechtstext_language>de</rechtstext_language>
			<rechtstext_country>FR</rechtstext_country>
			<action>push</action>
		</api>";

		$error = $api->check_xml_for_error( @simplexml_load_string( $xml ), false );

		$this->assertEquals( $error, 17 );
	}


}
