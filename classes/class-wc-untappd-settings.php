<?php
/**
 * Copyright (C) 2022 ChillCode
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author    ChillCode
 * @copyright Copyright (c) 2022, ChillCode All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package   WooCommerce Untappd
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Untappd_Settings class.
 */
class WC_Untappd_Settings extends WC_Settings_API {

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
	 * Add tab to Woocommerce options tabs.
	 *
	 * @param array $settings_tabs Woocommerce options tabs passed by filter woocommerce_settings_tabs_array.
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
		$settings[] = array(
			'title' => __( 'Config Untappd API', 'wc-untappd-ratings' ),
			'type'  => 'title',
			'desc'  => 'Config Untappd API',
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
			'default'  => 'Woocommerce Untappd APP Version ' . WC_UNTAPPD_RATINGS_VERSION,
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
			'desc'     => __( 'Overwrite Woocommerce ratings with Untappd one\'s.', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_ratings_allow',
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

		$settings[] = array(
			'title' => __( 'Untappd Map Configurtation', 'wc-untappd-ratings' ),
			'type'  => 'title',
			'desc'  => 'Configure how Untappd ratings are displayed on the map.',
			'id'    => 'wc_untappd_settings',
		);

		$settings[] = array(
			'title'    => __( 'Brewery ID', 'wc-untappd-ratings' ),
			'desc'     => __( 'ID of the brewery to get data from', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_map_brewery_id',
			'default'  => '',
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:340px;',
		);

		$settings[] = array(
			'title'    => __( 'Number of checkins to show on the map', 'wc-untappd-ratings' ),
			'desc'     => __( 'Number of checkins to show on the map, maximum 300.', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_map_total_checkins',
			'default'  => '25',
			'type'     => 'number',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'title'    => __( 'Use Untappd icon', 'wc-untappd-ratings' ),
			'desc'     => __( 'Use the Untappd icon to mark <i>Checkins</i> on the map.', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_map_use_icon',
			'default'  => 'no',
			'type'     => 'checkbox',
			'desc_tip' => true,
			'css'      => 'width:140px;',
		);

		$settings[] = array(
			'title'    => __( 'Use Custom Icon', 'wc-untappd-ratings' ),
			'desc'     => __( 'Use a custom icon to mark <i>Checkins</i> on the map.', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_map_use_url_icon',
			'default'  => '',
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:340px;',
		);

		$settings[] = array(
			'title'    => __( 'Untappd at home default coordinates', 'wc-untappd-ratings' ),
			'desc'     => __( 'Overwrite the default coordinates for Untappd At Home', 'wc-untappd-ratings' ),
			'id'       => 'wc_untappd_map_at_home_coordinates',
			'default'  => '34.2346598,-77.9482096',
			'type'     => 'text',
			'desc_tip' => true,
			'css'      => 'width:240px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wc_untappd_map_settings',
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
					$('[id^=wc_untappd_ratings]:not(#wc_untappd_ratings_allow)').prop('disabled', false);
				}
			);

			var selector = $('input#wc_untappd_ratings_allow');

			$('[id^=wc_untappd_ratings]:not(#wc_untappd_ratings_allow)').prop('disabled', !selector.prop('checked'));

			selector.on('change', function()
				{
					$('[id^=wc_untappd_ratings]:not(#wc_untappd_ratings_allow)').prop('disabled', !this.checked);
				}
			);
  		"
		);
	}
}
