<?php # -*- coding: utf-8 -*-

/**
 * Class AGB_Connector_Shortcodes
 */
class AGB_Connector_Shortcodes {

	/**
	 * The Plugin version.
	 *
	 * @var string
	 */
	private $plugin_version = '';

	/**
	 * @var array
	 */
	private $registered_shortcodes = array();

	/**
	 * Constructor.
	 *
	 * @param string $plugin_version The plugin version.
	 */
	public function __construct( $plugin_version ) {

		$this->plugin_version = $plugin_version;
	}

	/**
	 * Settings for All AGB shortcodes.
	 *
	 * @return array
	 */
	public function settings() {

		return (array) apply_filters( 'agb_shortcodes', array(
			'agb_terms'      => array(
				'name'        => esc_html__( 'AGB Terms', 'agb-connector' ),
				'setting_key' => 'agb',
			),
			'agb_privacy'    => array(
				'name'        => esc_html__( 'AGB Privacy', 'agb-connector' ),
				'setting_key' => 'datenschutz',
			),
			'agb_revocation' => array(
				'name'        => esc_html__( 'AGB Revocation', 'agb-connector' ),
				'setting_key' => 'widerruf',
			),
			'agb_imprint'    => array(
				'name'        => esc_html__( 'AGB Imprint', 'agb-connector' ),
				'setting_key' => 'impressum',
			),
		) );
	}

	/**
	 * Returns settings for a AGB Shortcode.
	 *
	 * @param $shortcode
	 *
	 * @return bool|array
	 */
	public function get_setting( $shortcode ) {

		$settings = $this->settings();

		return isset( $settings[ $shortcode ] ) ? $settings[ $shortcode ] : false;
	}

	/**
	 * Helper function to cleanup and do_shortcode on content.
	 *
	 * @see do_shortcode()
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function callback_content( $content ) {
		$array = array(
			'<p>['       => '[',
			']</p>'      => ']',
			'<br /></p>' => '</p>',
			']<br />'    => ']'
		);

		$content = shortcode_unautop( balanceTags( trim( $content ), true ) );
		$content = strtr( $content, $array );

		return do_shortcode( $content );
	}

	/**
	 * Register AGB shortcodes.
	 */
	public function setup() {

		foreach ( $this->settings() as $shortcode => $setting ) {

			if ( ! $setting ) {
				return;
			}

			$this->registered_shortcodes[ $shortcode ] = $shortcode;

			remove_shortcode( $shortcode );
			add_shortcode( $shortcode, array( $this, 'do_shortcode_callback' ) );
		}
	}

	/**
	 * Map AGB shorcodes for Visual Composer.
	 *
	 * @see https://wpbakery.atlassian.net/wiki/spaces/VC/pages/524332/vc+map
	 */
	public function vc_maps() {

		foreach ( $this->settings() as $shortcode => $setting ) {

			if ( ! $setting ) {
				return;
			}

			vc_map( array(
					'name'     => $setting['name'],
					'base'     => $shortcode,
					'class'    => "$shortcode-container",
					'category' => esc_html__( 'Content', 'agb-connector' ),
					'params'   => array(
						array(
							'type'        => 'textfield',
							'holder'      => 'div',
							'class'       => "$shortcode-id",
							'heading'     => esc_html__( 'Element ID', 'agb-connector' ),
							'param_name'  => 'id',
							'value'       => '',
							/* translators: %s is the w3c specification link. */
							'description' => sprintf( esc_html__( 'Enter element ID (Note: make sure it is unique and valid according to %s).', 'agb-connector' ),
								'<a href="https://www.w3schools.com/tags/att_global_id.asp">' . esc_html__( 'w3c specification', 'agb-connector' ) . '</a>'
							),
						),
						array(
							'type'        => 'textfield',
							'holder'      => 'div',
							'class'       => "$shortcode-class",
							'heading'     => esc_html__( 'Extra class name', 'agb-connector' ),
							'param_name'  => 'class',
							'value'       => '',
							'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'agb-connector' )
						),
					),
				)
			);
		} // Endforeach().
	}

	/**
	 * Do the shortcode callback.
	 *
	 * @param $attr
	 * @param string $content
	 * @param string $shortcode
	 *
	 * @return string
	 */
	public function do_shortcode_callback( $attr, $content = '', $shortcode ) {

		$setting = $this->get_setting( $shortcode );

		if ( ! $setting || empty( $this->registered_shortcodes[ $shortcode ] ) ) {
			return '';
		}

		// Get Page ID from settings.
		$page_id       = 0;
		$page_settings = get_option( 'agb_connector_text_types_allocation', array() );

		if ( ! empty( $page_settings[ $setting['setting_key'] ] ) ) {
			$page_id = (int) $page_settings[ $setting['setting_key'] ];
		}

		if ( ! $page_id ) {
			/* translators: %s is the AGB shortcode name. */
			return sprintf( esc_html__( 'No valid page found for %s.' ), $setting['name'] );
		}

		// Get the Page Content.
		$page_object = get_post( $page_id );

		if ( ! is_wp_error( $page_object ) ) {
			$page_content = $this->callback_content( $page_object->post_content );
		}

		if ( empty( $page_content ) ) {
			/* translators: %s is the AGB shortcode name. */
			$page_content = sprintf( esc_html__( 'No content found for %s.' ), $setting['name'] );
		}

		// Prepare the output.
		$attr = (object) shortcode_atts( array(
			'id'    => '',
			'class' => '',
		), $attr, $shortcode );

		$attr->class = preg_split( '#\s+#', $attr->class );

		$id      = ( $attr->id !== '' ) ? 'id="' . $attr->id . '"' : '';
		$classes = array( 'agb_content', $shortcode );
		$classes = array_merge( $classes, $attr->class );
		$classes = implode( ' ', array_map( 'sanitize_html_class', array_unique( $classes ) ) );

		// Return output for the shortcode.
		return sprintf(
			'<div %1$s class="%2$s">%3$s</div>',
			esc_attr( $id ),
			esc_attr( $classes ),
			$page_content
		);
	}
}
