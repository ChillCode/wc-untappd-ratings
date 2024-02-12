<?php
/**
 * WC_Untappd_Settings
 *
 * @author    ChillCode
 * @copyright Copyright (c) 2024, ChillCode All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package   Untappd Ratings for WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Untappd_Settings class.
 */
class WC_Untappd_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::woocommerce_settings_tabs_array', 100 );

		add_action( 'woocommerce_settings_tabs_untappd_settings', __CLASS__ . '::woocommerce_settings_tabs_untappd_settings' );
		add_action( 'woocommerce_update_options_untappd_settings', __CLASS__ . '::woocommerce_update_options_untappd_settings' );

		$this->enque_settings_js();
	}

	/**
	 * Add tab to WooCommerce options tabs.
	 *
	 * @param array $settings_tabs WooCommerce options tabs passed by filter woocommerce_settings_tabs_array.
	 */
	public static function woocommerce_settings_tabs_array( $settings_tabs ) {
		$settings_tabs['untappd_settings'] = esc_html__( 'Untappd', 'wc-untappd-ratings' );

		return $settings_tabs;
	}

	/**
	 * Output Untappd related settings to Untappd options tab.
	 */
	public static function woocommerce_settings_tabs_untappd_settings() {
		woocommerce_admin_fields( self::get_settings() );
	}

	/**
	 * Update Untappd related settings.
	 */
	public static function woocommerce_update_options_untappd_settings() {

		WC_Untappd_Ratings::delete_cache();

		woocommerce_update_options( self::get_settings() );
	}

	/**
	 * Untappd related settings.
	 */
	public static function get_settings() {
		$ratelimit_remaining = absint( get_option( 'wc_untappd_ratelimit_remaining', true ) );

		$settings[] = array(
			'title' => __( 'Config Untappd API', 'wc-untappd-ratings' ),
			'type'  => 'title',
			/* translators: %s: API ratelimit remaining */
			'desc'  => sprintf( __( 'Rate limit remaining per next hour: %s calls', 'woocommerce' ), $ratelimit_remaining ),
			'id'    => 'wc_untappd_api_settings',
		);

		$settings[] = array(
			'title'    => __( 'Untappd API Client ID', 'wc-untappd-ratings' ),
			'desc'     => __( 'Untappd API Client ID required to connect to Untappd API. Ask for it.', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_client_id',
			'default'  => '',
			'type'     => 'password',
			'desc_tip' => true,
			'css'      => 'width:340px;',
		);

		$settings[] = array(
			'title'    => __( 'Untappd API Client Secret', 'wc-untappd-ratings' ),
			'desc'     => __( 'Untappd API Client Secret required to connect to Untappd API', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_client_secret',
			'default'  => '',
			'type'     => 'password',
			'desc_tip' => true,
			'css'      => 'width:340px;',
		);

		$settings[] = array(
			'title'    => __( 'API Url', 'wc-untappd-ratings' ),
			'desc'     => __( 'API server address', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_api_url',
			'default'  => 'https://api.untappd.com/v4/',
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:240px;',
		);

		$settings[] = array(
			'title'    => __( 'APP Name', 'wc-untappd-ratings' ),
			'desc'     => __( 'Used to identify the application on the server', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_api_useragent',
			'default'  => 'WooCommerce  Untappd APP Version ' . WC_UNTAPPD_RATINGS_VERSION,
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:240px;',
		);

		$settings[] = array(
			'title'    => __( 'Show "Powered by Untappd" logo', 'wc-untappd-ratings' ),
			'desc'     => __( 'Show "Powered by Untappd" logo at Storefront credit links', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_show_logo',
			'default'  => 'no',
			'type'     => 'checkbox',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wc_untappd_api_settings',
		);

		$settings[] = array(
			'title' => __( 'Config Untappd ratings', 'wc-untappd-ratings' ),
			'type'  => 'title',
			'desc'  => 'Config how ratings are shown',
			'id'    => 'wc_untappd_settings',
		);

		$settings[] = array(
			'title'    => __( 'Use Untappd ratings', 'wc-untappd-ratings' ),
			'desc'     => __( 'Overwrite WooCommerce  ratings with Untappd one\'s.', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_ratings_enabled',
			'default'  => 'no',
			'type'     => 'checkbox',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'title'    => __( 'Ratings text', 'wc-untappd-ratings' ),
			'desc'     => __( 'Text displayed over the ratings.', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_ratings_text',
			'default'  => 'Untappd ratings',
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:240px;',
		);

		$settings[] = array(
			'title'    => __( 'Display ratings text', 'wc-untappd-ratings' ),
			'desc'     => __( 'Display ratings in text format x/5', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_ratings_show_text',
			'default'  => 'no',
			'type'     => 'checkbox',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'title'    => __( 'Display total ratings', 'wc-untappd-ratings' ),
			'desc'     => __( 'Display a link to Untappd with total ratings.', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_ratings_show_total',
			'default'  => 'no',
			'type'     => 'checkbox',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'title'    => __( 'Structured data', 'wc-untappd-ratings' ),
			'desc'     => __( 'Add rating data to structured data to display it on search engines (Google, Bing etc...)', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_ratings_add_to_structured_data',
			'default'  => 'no',
			'type'     => 'checkbox',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'title'    => __( 'Cache time', 'wc-untappd-ratings' ),
			'desc'     => __( 'Time the API query is cached', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_ratings_cache_time',
			'default'  => '3',
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'title'    => __( 'Customer reviews minimum rating', 'wc-untappd-ratings' ),
			'desc'     => __( 'Minimum rating for indexing customer reviews', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_ratings_review_min',
			'default'  => '3.5',
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wc_untappd_settings',
		);

		return $settings;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enque_settings_js() {
		wc_enqueue_js(
			"
			$('#mainform').submit(function()
				{
					$('[id^=wc_untappd_ratings]:not(#wc_untappd_ratings_enabled)').prop('disabled', false);
				}
			);

			var selector = $('input#wc_untappd_ratings_enabled');

			$('[id^=wc_untappd_ratings]:not(#wc_untappd_ratings_enabled)').prop('disabled', !selector.prop('checked'));

			selector.on('change', function()
				{
					$('[id^=wc_untappd_ratings]:not(#wc_untappd_ratings_enabled)').prop('disabled', !this.checked);
				}
			);
  		"
		);
	}
}
