<?php
/**
 * The uninstall routine.
 *
 * @package agb-connector
 */

// If uninstall not called from WordPress exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

delete_option( 'agb_connector_user_auth_token' );
delete_option( 'agb_connector_text_types_allocation' );
delete_option( 'agb_connector_wc_append_email' );
