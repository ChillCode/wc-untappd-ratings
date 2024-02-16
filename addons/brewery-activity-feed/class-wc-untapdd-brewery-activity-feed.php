<?php
/**
 * Copyright (C) 2024 ChillCode
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
 * @copyright Copyright (c) 2024, ChillCode All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package   WooCommerce Untappd
 */

/**
 * WC_Untapdd_Brewery_Activity_Feed class.
 *
 * @package WooCommerce Untappd\Addons
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Untapdd_Brewery_Activity_Feed class.
 */
class WC_Untapdd_Brewery_Activity_Feed {

	/**
	 * Untappd default coordinates.
	 *
	 * @var array
	 */
	protected $default_coordinates;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wc_untappd_map_feed', array( $this, 'wc_untappd_map_feed' ) );
		add_action( 'wp_ajax_nopriv_wc_untappd_map_feed', array( $this, 'wc_untappd_map_feed' ) );

		add_shortcode( 'wc_untappd_map', array( $this, 'wc_untappd_map_sc' ) );

		/** Untappd coordinates */
		$this->default_coordinates = array(
			0 => '34.2346598',
			1 => '-77.9482096',
		);
	}

	/**
	 *
	 * Display a map with checkins.
	 *
	 * @param array $atts (Required) Shortcode attributes.
	 */
	public function wc_untappd_map_sc( $atts ) {
		$atts = shortcode_atts(
			apply_filters(
				'wc_untappd_map_atts',
				array(
					'api_key'          => '',
					'zoom'             => 4,
					'height'           => '500',
					'map_type'         => 'interactive',
					'brewery_id'       => 0,
					'max_checkins'     => 25,
					'center_map'       => 'yes',
					'lat_lng'          => '',
					'map_use_icon'     => true,
					'map_use_url_icon' => false,
					'map_style'        => '',
					'map_class'        => '',
					'map_id'           => '',
				)
			),
			$atts,
			'wc_untappd_map'
		);

		if ( empty( $atts['api_key'] ) ) {
			return '';
		}

		$class_master = 'untappd_map';

		$class = array( $class_master );

		if ( ! empty( $atts['map_class'] ) ) {
			$class[] = $atts['map_class'];
		}

		if ( 'yes_no_overlay' === $atts['center_map'] ) {
			$class[] = 'untappd_map_no_overlay';
		}

		if ( ! empty( $atts['map_type'] ) ) {
			$class[] = 'untappd_map_type_' . $atts['map_type'];
		}

		$id_attr = '';
		if ( ! empty( $atts['map_id'] ) ) {
			$id_attr = ' id="' . esc_attr( $atts['map_id'] ) . '"';
		}

		$style_attr = '';
		if ( ! empty( $atts['map_style'] ) ) {
			$style_attr = ' style="' . esc_attr( $atts['map_style'] ) . '"';
		}

		$suffix = wp_scripts_get_suffix();

		wp_enqueue_script( 'brewery-activity-feed-pagination', plugins_url( 'assets/js/brewery-activity-feed-pagination' . $suffix . '.js', __FILE__ ), array( 'jquery' ), WC_UNTAPPD_RATINGS_VERSION, true );
		wp_enqueue_script( 'brewery-activity-feed', plugins_url( 'assets/js/brewery-activity-feed' . $suffix . '.js', __FILE__ ), array( 'jquery' ), WC_UNTAPPD_RATINGS_VERSION, true );

		wp_localize_script(
			'brewery-activity-feed',
			'ajax_untappd_config',
			array(
				'wc_untappd_map_nonce' => wp_create_nonce( 'wc_untappd_map_nonce' ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'map_current_lang'     => apply_filters( 'wpml_current_language', get_locale() ),
				'languages'            => array(
					0 => __( ' on ', 'wc-untappd-ratings' ),
					1 => __( 'Comment', 'wc-untappd-ratings' ),
					2 => __( 'Translated comment', 'wc-untappd-ratings' ),
					3 => __( 'is drinking a', 'wc-untappd-ratings' ),
					4 => __( ' at ', 'wc-untappd-ratings' ),
					5 => __( 'View product', 'wc-untappd-ratings' ),
				),
			)
		);

		wp_enqueue_style( 'brewery-activity-feed-css', plugins_url( 'assets/css/brewery-activity-feed.min.css', __FILE__ ), array(), WC_UNTAPPD_RATINGS_VERSION );

		$map_script = '(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
			key: "' . esc_attr( $atts['api_key'] ) . '",
			v: "weekly",
			language: "' . apply_filters( 'wpml_current_language', get_locale() ) . '",
		  });
		';

		wp_add_inline_script( 'brewery-activity-feed', $map_script );

		$style_height = '';

		if ( ! empty( $atts['height'] ) && 'interactive' === $atts['map_type'] ) {
			$style_height = ' style="height:' . absint( $atts['height'] ) . 'px;"';
		}

		$at_home_coordinates = $this->get_home_coordinates( $atts['lat_lng'] );

		$map_id = uniqid( 'untappd_map_canvas_' );

		$class = apply_filters( 'wc_untappd_map_class', $class, $atts );

		$output = '<div class="' . esc_attr( $class_master ) . '" id="' . esc_attr( $map_id ) . '"' . $style_height . '></div>';
		$output = '<div' . $id_attr . ' class="' . esc_attr( implode( ' ', $class ) ) . '"' . $style_attr . '>' . $output . '</div>';

		$output_script = 'async function initMap(){ const { Map } = await google.maps.importLibrary("maps"); jQuery(' . esc_attr( $map_id ) . ').UntappdMap({map_type: "' . esc_attr( $atts['map_type'] ) . '", max_checkins: ' . absint( $atts['max_checkins'] ) . ', map_use_icon: ' . (int) $atts['map_use_icon'] . ', map_use_url_icon: "' . esc_attr( $atts['map_use_url_icon'] ) . '", center_lat: "' . esc_attr( $at_home_coordinates['lat'] ) . '", center_lng: "' . esc_attr( $at_home_coordinates['lng'] ) . '", center_map: "' . esc_attr( $atts['center_map'] ) . '", zoom: ' . absint( $atts['zoom'] ) . ', height: ' . absint( $atts['height'] ) . ', brewery_id: ' . absint( $atts['brewery_id'] ) . ', api_key: "' . esc_attr( $atts['api_key'] ) . '"});}initMap();';

		$output_script = apply_filters( 'wc_untappd_map_output_script', $output_script, $atts );

		wp_add_inline_script( 'brewery-activity-feed', $output_script );

		$output = apply_filters( 'wc_untappd_map_output', $output, $atts );

		return $output;
	}

	/**
	 * Generate a map feed.
	 */
	public function wc_untappd_map_feed() {

		if ( WC_Untappd_Ratings::api_is_active() ) {

			/** Get and validate nonce. */
			$wc_untappd_map_nonce = filter_input( INPUT_GET, 'wc_untappd_map_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( ! $wc_untappd_map_nonce || ! wp_verify_nonce( $wc_untappd_map_nonce, 'wc_untappd_map_nonce' ) ) {
				wp_send_json( array( 'error' => __( 'Invalid Request', 'wc-untappd-ratings' ) ) );
			}

			/** Get and validate brewery id. */
			$brewery_id = filter_input( INPUT_GET, 'brewery_id', FILTER_VALIDATE_INT );

			if ( empty( $brewery_id ) ) {
				wp_send_json( array( 'error' => __( 'Brewery ID is empty, please set it at WooCommerce Untappd Options Tab', 'wc-untappd-ratings' ) ) );
			}

			/** Set max checkins. */
			$max_checkins = filter_input( INPUT_GET, 'max_checkins', FILTER_VALIDATE_INT );

			/** Get data from cache. */
			$cache_key = 'wc_untappd_map_feed_' . $brewery_id . ( ( current_user_can( 'edit_posts' ) ) ? '_is_admin_' : '_' ) . apply_filters( 'wpml_current_language', '' ) . '_' . $max_checkins;

			$cache_data = get_transient( $cache_key );

			if ( false !== $cache_data && is_array( $cache_data ) ) {
				wp_send_json( $cache_data );
			}

			/** Check if cache failed twice. */
			if ( get_option( 'wc_untappd_map_cache_is_working' ) === 'no' ) {
				wp_send_json( array( 'error' => __( 'Untappd Cache not working', 'wc-untappd-ratings' ) ) );
			}

			/** Populate feed. */
			$limit = $max_checkins < 25 ? $max_checkins : 25;

			$brewery_activity_feed = WC_Untappd_Ratings::API()->brewery_activity_feed( $brewery_id, null, null, $limit );

			if ( is_untappd_error( $brewery_activity_feed ) ) {
				wp_send_json( array( 'error' => __( 'Untappd API not working', 'wc-untappd-ratings' ) ) );
			}

			$wc_untappd_map_feed = $this->brewery_feed( $brewery_activity_feed );

			if ( empty( $wc_untappd_map_feed ) ) {
				wp_send_json( array( 'error' => __( 'Untappd invalid data', 'wc-untappd-ratings' ) ) );
			}

			$max_checkins = $this->max_checkins( $max_checkins );

			if ( $max_checkins > 1 ) {
				for ( $i = 1; $i < $max_checkins; $i++ ) {
					if ( ! isset( $brewery_activity_feed['response']['pagination']['max_id'] ) ) {
						break;
					}

					$brewery_activity_feed = WC_Untappd_Ratings::API()->brewery_activity_feed( $brewery_id, $brewery_activity_feed['response']['pagination']['max_id'] );

					if ( empty( $brewery_activity_feed ) || is_untappd_error( $brewery_activity_feed ) ) {
						break;
					}

					$wc_untappd_map_feed = $this->array_merge_recursive_distinct( $wc_untappd_map_feed, $this->brewery_feed( $brewery_activity_feed ) );
				}
			}

			$set_transient = set_transient( $cache_key, $wc_untappd_map_feed, WC_Untappd_Ratings::API()->get_cache_time() );

			if ( false === $set_transient ) {
				delete_transient( $cache_key );
				$set_transient = set_transient( $cache_key, $wc_untappd_map_feed, WC_Untappd_Ratings::API()->get_cache_time() );
			}

			update_option( 'wc_untappd_map_cache_is_working', ( $set_transient ) ? 'yes' : 'no' );

			wp_send_json( $wc_untappd_map_feed );
		} else {
			$wc_untappd_map_feed = array(
				'result' => 'error',
			);

			wp_send_json( $wc_untappd_map_feed );
		}
	}

	/**
	 * Get Untappd at Home corrdinates.
	 *
	 * @param string $at_home_coordinates Latitude, Longitude.
	 */
	private function get_home_coordinates( $at_home_coordinates = '' ) {
		if (
			empty( $at_home_coordinates ) ||
			! is_string( $at_home_coordinates ) ||
			substr_count( $at_home_coordinates, ',' ) !== 1 ||
			// phpcs:ignore
			! ( $home_coordinates = explode( ',', $at_home_coordinates ) ) ) {
			$home_coordinates = $this->default_coordinates;
		}

		$home_coordinates['lat'] = $home_coordinates[0];
		$home_coordinates['lng'] = $home_coordinates[1];

		return $home_coordinates;
	}

	/**
	 * Generate a brewey feed array from Untappd results.
	 *
	 * @param array $brewery_feed_result Json decoded result from Untappd call.
	 * @param bool  $add_product_link add a link to the product if present on shop.
	 * @param bool  $show_ratings_to_admin_only Show comments and ratings on infoWindows only to administrators.
	 */
	private function brewery_feed( array $brewery_feed_result, bool $add_product_link = true, $show_ratings_to_admin_only = false ) {

		$brewery_feed = array();

		if ( ! empty( $brewery_feed_result ) && isset( $brewery_feed_result['response']['checkins']['count'] ) ) {

			foreach ( $brewery_feed_result['response']['checkins']['items'] as $untappd_checkin ) {
				// Do not add checkins that not belong to any venue.
				if ( isset( $untappd_checkin['venue']['location'] ) && ! empty( $untappd_checkin['venue']['location']['lat'] && ! empty( $untappd_checkin['venue']['location']['lng'] ) ) ) {
					// If product exists on WooCommerce store, add a link to it on infoWindow().
					$permalink = '';

					if ( $add_product_link ) {
						$args = array(
							'posts_per_page' => 1,
							'orderby'        => 'title',
							'order'          => 'asc',
							'post_type'      => 'product',
							'post_status'    => 'publish',
							'meta_query'     => array( // PHPCS:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								'relation' => 'AND',
								array(
									'key'   => '_untappd_beer_id',
									'value' => absint( $untappd_checkin['beer']['bid'] ),
								),
							),
						);

						$products = get_posts( $args );

						$product_id = null;

						if ( count( $products ) ) {
							$product_id = apply_filters( 'wpml_object_id', $products[0]->ID, 'product', true );
							$permalink  = get_permalink( $product_id );
						}
					}

					// If configuration set change Untappd at home coordinates.
					if ( 9917985 === absint( $untappd_checkin['venue']['venue_id'] ) ) {

						$at_home_coordinates = $this->get_home_coordinates();

						$untappd_checkin['venue']['location']['lat'] = (float) $at_home_coordinates['lat'];
						$untappd_checkin['venue']['location']['lng'] = (float) $at_home_coordinates['lng'];
					}

					// Show comments and rating to admin or to all.
					$untappd_checkin_comment = '';

					if ( $show_ratings_to_admin_only && current_user_can( 'edit_posts' ) || false === $show_ratings_to_admin_only ) {
						$untappd_checkin_comment = ( ! empty( $untappd_checkin['checkin_comment'] ) ) ? $untappd_checkin['checkin_comment'] : '';
					} else {
						$untappd_checkin['rating_score'] = '';
					}

					$brewery_feed[ $untappd_checkin['venue']['venue_id'] ][ $untappd_checkin['checkin_id'] ] = array(
						'lat'            => (float) $untappd_checkin['venue']['location']['lat'],
						'lng'            => (float) $untappd_checkin['venue']['location']['lng'],
						'beer_name'      => sanitize_text_field( $untappd_checkin['beer']['beer_name'] ),
						'beer_label'     => sanitize_text_field( $untappd_checkin['beer']['beer_label'] ),
						'user_name'      => sanitize_text_field( $untappd_checkin['user']['user_name'] ),
						'comment'        => sanitize_textarea_field( $untappd_checkin_comment ),
						'permalink'      => $permalink,
						'location'       => ( ! empty( $untappd_checkin['user']['location'] ) ) ? sanitize_text_field( $untappd_checkin['user']['location'] ) : '',
						'venue_name'     => sanitize_text_field( $untappd_checkin['venue']['venue_name'] ),
						'foursquare_url' => ( filter_var( $untappd_checkin['venue']['foursquare']['foursquare_url'], FILTER_VALIDATE_URL ) ) ? $untappd_checkin['venue']['foursquare']['foursquare_url'] : '',
						'checkin_date'   => date_i18n( get_option( 'date_format' ), strtotime( $untappd_checkin['created_at'] ) ),
						'rating_score'   => ( ! empty( $untappd_checkin['rating_score'] ) ) ? number_format_i18n( $untappd_checkin['rating_score'], 2 ) : '',
					);
				}
			}
		}

		return $brewery_feed;
	}

	/**
	 * Get maximum calls to make, 25 checkins per call, max 12.
	 *
	 * @param int $max_checkins Chekins to show.
	 */
	private function max_checkins( int $max_checkins = null ) {
		$max_checkins = ceil( ( $max_checkins ? $max_checkins : 300 ) / 25 );
		return ( $max_checkins > 12 ) ? 12 : $max_checkins;
	}

	/**
	 * Merge array recursively with new brewery feed results.
	 *
	 * @param array $array1 array to merge.
	 * @param array $array2 array to merge.
	 *
	 * @return array $merged The resulting array
	 */
	private function array_merge_recursive_distinct( array $array1, array $array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = $this->array_merge_recursive_distinct( $merged[ $key ], $value );
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}
}

new WC_Untapdd_Brewery_Activity_Feed();
