<?php # -*- coding: utf-8 -*-


/**
 * Class AGB_Connector_API
 *
 * @since 1.0.0
 */
class AGB_Connector_API {

	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	private $plugin_version = '';

	/**
	 * API Version
	 *
	 * @var string
	 */
	public $api_version = '1.0';

	/**
	 * API Username that must match. Left empty fo no checking
	 *
	 * @var string
	 */
	public $api_username = 'inpsyde';

	/**
	 * API Password that must match
	 *
	 * @var string
	 */
	public $api_password = 'oIN9pBGPp98g';

	/**
	 * User auth token that must match
	 *
	 * @var string
	 */
	private $user_auth_token = '';

	/**
	 * Supported actions
	 *
	 * @var array
	 */
	private $supported_actions = array(
		'push'
	);

	/**
	 * The text types
	 *
	 * @var array with txt types
	 */
	private $text_types = array( 'agb', 'datenschutz', 'widerruf', 'impressum' );

	/**
	 * Txt types and post ids
	 *
	 * @var array
	 */
	private $text_types_allocation = array();

	/**
	 * Supported Language
	 *
	 * @var string
	 */
	private $supported_language = 'de';


	/**
	 * Define some values.
	 *
	 * @param string $plugin_version Plugin Version.
	 * @param string $user_auth_token User Auth Token.
	 * @param array  $text_types_allocation Page ids for Text.
	 */
	public function __construct(
		$plugin_version, $user_auth_token, array $text_types_allocation = array(
		'agb'         => 0,
		'datenschutz' => 0,
		'widerruf'    => 0,
		'impressum'   => 0,
	)
	) {

		$this->plugin_version        = $plugin_version;
		$this->user_auth_token       = $user_auth_token;
		$this->text_types_allocation = $text_types_allocation;
	}

	/**
	 * Language in with the Text will be stored ISO 639-1.
	 *
	 * @param string $lang The language code.
	 *
	 * @return bool
	 */
	function set_supported_language( $lang ) {

		if ( ! $lang || is_numeric( $lang ) || strlen( $lang ) !== 2  ) {
			return FALSE;
		}

		$this->supported_language = $lang;
		return TRUE;
	}

	/**
	 * Get the request and answers it.
	 *
	 * @param string $xml XML from push.
	 *
	 * @return string xml response
	 */
	public function handle_request( $xml ) {

		$xml = trim( stripslashes( $xml ) );
		if ( $xml ) {
			$xml = @simplexml_load_string( $xml );
		}

		$check_pdf = true;
		if ( isset( $xml->rechtstext_type ) && 'impressum' === strtolower( (string) $xml->rechtstext_type ) ) {
			$check_pdf = false;
		}
		$error = $this->check_xml_for_error( $xml, $check_pdf );
		if ( $error ) {
			return $this->return_xml( $error );
		}

		if ( 'push' === (string) $xml->action ) {

			if ( ! isset( $this->text_types_allocation[ (string) $xml->rechtstext_type ] ) ) {
				return $this->return_xml( 0 );
			}

			$post = get_post( $this->text_types_allocation[ (string) $xml->rechtstext_type ] );
			if ( ! $post instanceof WP_Post ) {
				return $this->return_xml( 99 );
			}
			$post->post_content = (string) $xml->rechtstext_html;

			if ( 'impressum' !== (string) $xml->rechtstext_type ) {
				$error = $this->push_pdf_file( $xml );
				if ( $error ) {
					return $this->return_xml( $error );
				}
			}

			$error = $this->save_post( $post );

			return $this->return_xml( $error );
		}

		return $this->return_xml( 99 );
	}

	/**
	 * Check XML for errors.
	 *
	 * @since 1.0.0
	 *
	 * @param SimpleXMLElement $xml       The XML object.
	 * @param boolean          $check_pdf Whether to check the PDF or not..
	 *
	 * @return int Error code
	 */
	public function check_xml_for_error( $xml, $check_pdf ) {

		if ( ! $xml || ! $xml instanceof SimpleXMLElement ) {
			return 12;
		}

		if ( $this->api_version !== (string) $xml->api_version ) {
			return 1;
		}

		if ( ! empty( $this->api_username ) ) {
			if ( $this->api_username !== (string) $xml->api_username && $this->api_password !== (string) $xml->api_password ) {
				return 2;
			}
		}

		if ( empty( $xml->user_auth_token ) || (string) $xml->user_auth_token !== $this->user_auth_token ) {
			return 3;
		}

		if ( empty( $xml->rechtstext_type ) || ! in_array( (string) $xml->rechtstext_type, $this->text_types, TRUE ) ) {
			return 4;
		}

		if ( empty( $xml->rechtstext_country ) || 'DE' !== strtoupper( (string) $xml->rechtstext_country ) ) {
			return 17;
		}

		if ( strlen( (string) $xml->rechtstext_text ) < 50 ) {
			return 5;
		}

		if ( strlen( (string) $xml->rechtstext_html ) < 50 ) {
			return 6;
		}

		if ( $check_pdf ) {

			if ( empty( $xml->rechtstext_pdf_url ) ) {
				return 7;
			}

			$pdf = @file_get_contents( (string) $xml->rechtstext_pdf_url, false );
			if ( empty( $pdf ) || substr( $pdf, 0, 4 ) !== '%PDF' ) {
				return 7;
			}

			if ( empty( $xml->rechtstext_pdf_md5hash ) || strtolower( (string) $xml->rechtstext_pdf_md5hash ) !== md5( $pdf ) ) {
				return 8;
			}
		}

		if ( empty( $xml->rechtstext_language ) || (string) $xml->rechtstext_language !== $this->supported_language ) {
			return 9;
		}

		if ( empty( $xml->action ) || ! in_array( (string) $xml->action, $this->supported_actions, TRUE ) ) {
			return 10;
		}

		return 0;
	}


	/**
	 * Returns the XML answer
	 *
	 * @param int $code Error code 0 on success.
	 *
	 * @return string with xml response
	 */
	private function return_xml( $code = 0 ) {
		global $wp_version;

		$response = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
		$response .= '<response>' . PHP_EOL;
		if ( empty( $code ) ) {
			$response .= '	<status>success</status>' . PHP_EOL;
		} else {
			$response .= '	<status>error</status>' . PHP_EOL;
			$response .= '	<error>' . (string) $code . '</error>' . PHP_EOL;
		}
		if ( ! empty( $wp_version ) ) {
			$response .= '	<meta_shopversion>' . $wp_version . '</meta_shopversion>' . PHP_EOL;
		}
		if ( ! empty( $this->plugin_version ) ) {
			$response .= '	<meta_modulversion>' . $this->plugin_version . '</meta_modulversion>' . PHP_EOL;
		}
		$response .= '</response>';

		return $response;
	}


	/**
	 * Transfers the PDF file to uploads
	 *
	 * @param SimpleXMLElement $xml The XML Object.
	 *
	 * @return int returns error code
	 * @global $wpdb wpdb
	 */
	private function push_pdf_file( SimpleXMLElement $xml ) {
		global $wpdb;

		$uploads = wp_upload_dir();
		$file    = trailingslashit( $uploads['basedir'] ) . (string) $xml->rechtstext_type . '.pdf';

		if ( file_exists( $file ) ) {
			unlink( $file );
		}

		$pdf = @file_get_contents( (string) $xml->rechtstext_pdf_url, FALSE );
		if ( ! $pdf ) {
			return 7;
		}
		$result = file_put_contents( $file, $pdf );
		chmod( $file, 0644 );
		if ( ! $result ) {
			return 7;
		}

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$guid          = trailingslashit( $uploads['baseurl'] ) . (string) $xml->rechtstext_type . '.pdf';
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid = %s LIMIT 1", $guid ) );
		$post_parent   = 0;
		if ( ! empty( $this->text_types_allocation[ (string) $xml->rechtstext_type ] ) ) {
			$post_parent = $this->text_types_allocation[ (string) $xml->rechtstext_type ];
		}
		$attachment = array(
			'post_mime_type' => 'application/pdf',
			'guid'           => $guid,
			'post_parent'    => $post_parent,
			'post_type'      => 'attachment',
			'file'           => $file,
			'post_title'     => (string) $xml->rechtstext_type,
		);
		if ( $attachment_id ) {
			$attachment['ID'] = $attachment_id;
			$post_id          = wp_update_post( $attachment );
		} else {
			$post_id = wp_insert_post( $attachment );
		}

		if ( is_wp_error( $post_id ) ) {
			return 7;
		}

		return 0;
	}

	/**
	 * Save post and pdf after checks
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return int returns error code
	 */
	private function save_post( WP_Post $post ) {

		$post_id = wp_update_post( $post );

		if ( is_wp_error( $post_id ) ) {
			return 99;
		}

		return 0;
	}

}
