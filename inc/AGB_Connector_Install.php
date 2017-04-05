<?php # -*- coding: utf-8 -*-

/**
 * Class AGB_Connector_Install
 *
 * @since 1.0.0
 */
class AGB_Connector_Install {

	/**
	 * Initiate some things on activation
	 *
	 * @since 1.0.0
	 */
	public static function activate() {

		self::convert_agb_connector_plugin_options();

		$user_auth_token = get_option( 'agb_connector_user_auth_token', '' );
		if ( ! $user_auth_token ) {
			$user_auth_token = md5( wp_generate_password( 32, TRUE, TRUE ) );
			update_option( 'agb_connector_user_auth_token', $user_auth_token );
		}

		$text_types = array(
			'agb'         => 0,
			'datenschutz' => 0,
			'widerruf'    => 0,
			'impressum'   => 0,
		);

		$text_types_allocation = get_option( 'agb_connector_text_types_allocation', array() );
		$text_types_allocation = array_merge( $text_types, $text_types_allocation );
		update_option( 'agb_connector_text_types_allocation', $text_types_allocation );

	}

	/**
	 * Convert old Plugin data to new
	 *
	 * @since 1.0.0
	 */
	public static function convert_agb_connector_plugin_options() {

		$agb_connectors_options = get_option( 'agb_connectors_settings', array() );

		if ( ! $agb_connectors_options ) {
			return;
		}

		$text_types_allocation = array();
		$wc_email_append_pdf   = array();

		$text_types_allocation['agb']         = isset( $agb_connectors_options['agb_connector_agb_page'] ) ? absint( $agb_connectors_options['agb_connector_agb_page'] ) : 0;
		$text_types_allocation['impressum']   = isset( $agb_connectors_options['agb_connector_impressum_page'] ) ? absint( $agb_connectors_options['agb_connector_impressum_page'] ) : 0;
		$text_types_allocation['datenschutz'] = isset( $agb_connectors_options['agb_connector_datenschutz_page'] ) ? absint( $agb_connectors_options['agb_connector_datenschutz_page'] ) : 0;
		$text_types_allocation['widerruf']    = isset( $agb_connectors_options['agb_connector_widerruf_page'] ) ? absint( $agb_connectors_options['agb_connector_widerruf_page'] ) : 0;

		if ( ! empty( $agb_connectors_options['agb_connector_api'] ) ) {
			update_option( 'agb_connector_user_auth_token', $agb_connectors_options['agb_connector_api'] );
		}

		if ( ! empty( $agb_connectors_options['agb_connector_agb_pdf'] ) ) {
			$wc_email_append_pdf['datenschutz'] = TRUE;
		};

		if ( ! empty( $agb_connectors_options['agb_connector_widerruf_pdf'] ) ) {
			$wc_email_append_pdf['widerruf'] = TRUE;
		};

		$updated_wc = update_option( 'agb_connector_wc_append_email', $wc_email_append_pdf );
		$updated    = update_option( 'agb_connector_text_types_allocation', $text_types_allocation );
		if ( $updated && $updated_wc ) {
			delete_option( 'agb_connectors_settings' );
		}

	}
}
