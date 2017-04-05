<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: Terms & Conditions Connector of IT-Recht Kanzlei
 * Plugin URI: https://github.com/inpsyde/agb-connector
 * Description: Transfers legal texts from the IT-Recht Kanzlei client portal to your WordPress installation.
 * Author: Inpsyde GmbH
 * Author URI: http://inpsyde.com
 * Version: 1.0.0
 * Text Domain: agb-connector
 * Domain Path: /languages/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */

/**
 * Class AGB_Connector
 *
 * @since 1.0.0
 */
class AGB_Connector {

	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * The API object
	 *
	 * @var AGB_Connector_API
	 */
	private $api = NULL;

	/**
	 * The settings object
	 *
	 * @var AGB_Connector_Settings
	 */
	private $settings = NULL;

	/**
	 * Instance holder
	 *
	 * @var null
	 */
	private static $instance = NULL;

	/**
	 * Get main Plugin class instance
	 *
	 * @since 1.0.0
	 * @return AGB_Connector
	 */
	public static function get_instance() {

		if ( self::$instance === NULL ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Get plugin version
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_plugin_version() {

		return $this->version;
	}

	/**
	 * Init all actions and filters
	 *
	 * @since 1.0.0
	 */
	public function init() {

		add_action( 'wp_loaded', array( $this, 'api_request' ), PHP_INT_MAX );

		add_filter( 'woocommerce_email_attachments', array( $this, 'attach_pdf_to_email' ), 99, 3 );

		if ( ! is_admin() ) {
			return;
		}

		load_plugin_textdomain( 'agb-connector', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		$settings = $this->get_settings();
		add_action( 'admin_menu', array( $settings, 'add_menu' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $settings, 'add_action_links' ) );
	}


	/**
	 * Append Attachments to WooCommerce processing order email
	 *
	 * @since 1.0.0
	 *
	 * @param array    $attachments The attachments.
	 * @param string   $status The status.
	 * @param mixed    $order The order. We only process in case its an WC_Order object.
	 *
	 * @return array
	 */
	public function attach_pdf_to_email( array $attachments, $status, $order ) {

		$valid_statuse = array(
			'customer_completed_order',
			'customer_processing_order',
			'customer_invoice',
		);
		if ( ! in_array( $status, $valid_statuse, TRUE ) ) {
			return $attachments;
		}
		if ( ! $order instanceof \WC_Order ) {
			return $attachments;
		}

		$append_email = get_option( 'agb_connector_wc_append_email', array() );
		$uploads      = wp_upload_dir();

		if ( ! empty( $append_email['agb'] ) ) {
			$file = trailingslashit( $uploads['basedir'] ) . 'agb.pdf';
			if ( file_exists( $file ) ) {
				$attachments[] = $file;
			}
		}
		if ( ! empty( $append_email['widerruf'] ) ) {
			$file = trailingslashit( $uploads['basedir'] ) . 'widerruf.pdf';
			if ( file_exists( $file ) ) {
				$attachments[] = $file;
			}
		}
		if ( ! empty( $append_email['datenschutz'] ) ) {
			$file = trailingslashit( $uploads['basedir'] ) . 'datenschutz.pdf';
			if ( file_exists( $file ) ) {
				$attachments[] = $file;
			}
		}

		return $attachments;
	}


	/**
	 * Handle request from API
	 *
	 * @since 1.0.0
	 */
	public function api_request() {

		if ( is_admin() ) {
			return;
		}

		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}

		if ( false === strstr( $_SERVER['REQUEST_URI'], '/it-recht-kanzlei' ) ) {
			return;
		}

		$xml = '';
		if ( ! empty( $_POST['xml'] ) ) {
			$xml = wp_unslash( $_POST['xml'] );
		}

		header( 'Content-type: application/xml; charset=utf-8', TRUE, 200 );
		$api = $this->get_api();
		echo $api->handle_request( $xml );
		die();
	}

	/**
	 * Get Api instance
	 *
	 * @since 1.0.0
	 * @return AGB_Connector_API
	 */
	public function get_api() {

		if ( NULL === $this->api ) {
			require dirname( __FILE__ ) . '/inc/AGB_Connector_API.php';
			$api_key               = get_option( 'agb_connector_user_auth_token', '' );
			$text_types_allocation = get_option( 'agb_connector_text_types_allocation', array() );
			$this->api             = new AGB_Connector_API( $this->get_plugin_version(), $api_key, $text_types_allocation );
			$language              = apply_filters( 'agb_connector_api_supported_language', substr( get_locale(), 0, 2 ) );
			$this->api->set_supported_language( $language );
		}

		return $this->api;
	}


	/**
	 * Get Plugin Settings page
	 *
	 * @since 1.0.0
	 * @return AGB_Connector_Settings
	 */
	public function get_settings() {

		if ( NULL === $this->settings ) {
			require dirname( __FILE__ ) . '/inc/AGB_Connector_Settings.php';
			$user_auth_token       = get_option( 'agb_connector_user_auth_token', '' );
			$text_types_allocation = get_option( 'agb_connector_text_types_allocation', array() );
			$this->settings        = new AGB_Connector_Settings( $this->get_plugin_version(), $user_auth_token, $text_types_allocation );
		}

		return $this->settings;
	}

}

/**
 * Function for getting plugin class
 *
 * @since 1.0.0
 * @return AGB_Connector
 */
function agb_connector() {

	$plugin = AGB_Connector::get_instance();

	if ( current_action() === 'plugins_loaded' ) {
		$plugin->init();
	}

	return $plugin;
}

/**
 * Run
 */
if ( function_exists( 'add_action' ) ) {
	add_action( 'plugins_loaded', 'agb_connector' );
}

/**
 * Activation
 */
if ( function_exists( 'register_activation_hook' ) ) {
	require dirname( __FILE__ ) . '/inc/AGB_Connector_Install.php';
	register_activation_hook( __FILE__, array( 'AGB_Connector_Install', 'activate' ) );
}

