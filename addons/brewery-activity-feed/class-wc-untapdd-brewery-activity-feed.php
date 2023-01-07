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
	 * @param array $content (Optional) Shortcode attributes.
	 * @param array $code (Optional) Shortcode attributes.
	 */
	public function wc_untappd_map_sc( $atts, $content = null, $code = '' ) {
		$atts = shortcode_atts(
			apply_filters(
				'wc_untappd_map_atts',
				array(
					'api_key'          => '',
					'zoom'             => 14,
					'height'           => '500',
					'custom_style'     => '',
					'map_type'         => 'interactive',
					'brewery_id'       => get_option( 'wc_untappd_map_brewery_id', '' ),
					'center_map'       => '',
					'lat_lng'          => '',
					'map_use_icon'     => get_option( 'wc_untappd_map_use_icon', 'no' ) === 'yes' ? true : false,
					'map_use_url_icon' => get_option( 'wc_untappd_map_use_url_icon', null ),
					'el_style'         => '',
				)
			),
			$atts,
			'wc_untappd_map'
		);

		$class_master = 'untappd_map';

		$class = array( $class_master );

		if ( ! empty( $atts['el_class'] ) ) {
			$class[] = $atts['el_class'];
		}

		if ( 'yes_no_overlay' === $atts['center_map'] ) {
			$class[] = 'untappd_map_no_overlay';
		}

		if ( ! empty( $atts['map_type'] ) ) {
			$class[] = 'untappd_map_type_' . $atts['map_type'];
		}

		$id_attr = '';
		if ( ! empty( $atts['el_id'] ) ) {
			$id_attr = ' id="' . esc_attr( $atts['el_id'] ) . '"';
		}

		$style_attr = '';
		if ( ! empty( $atts['el_style'] ) ) {
			$style_attr = ' style="' . esc_attr( $atts['el_style'] ) . '"';
		}

		if ( ! empty( $atts['api_key'] ) ) {
			wp_enqueue_script(
				'brewery-activity-feed-map-googleapis-js',
				'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $atts['api_key'] ) . '&language=' . apply_filters( 'wpml_current_language', get_locale() ),
				array(),
				WC_UNTAPPD_RATINGS_VERSION,
				true
			);
		} else {
			wp_enqueue_script(
				'brewery-activity-feed-map-googleapis-js',
				'https://maps.googleapis.com/maps/api/js?language=' . apply_filters( 'wpml_current_language', get_locale() ),
				array(),
				WC_UNTAPPD_RATINGS_VERSION,
				true
			);
		}

		$this->wc_untappd_enqueue_js();

		if ( 'static' === $atts['map_type'] && ! empty( $atts['custom_style'] ) ) {
			$_custom_feature = '';
			// PHPCS:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$_custom_style = base64_decode( $atts['custom_style'] );
			if ( $_custom_style ) {
				$_custom_style_array = json_decode( $_custom_style, true );
				if ( $_custom_style_array ) {
					foreach ( $_custom_style_array as $feature ) {
						if ( isset( $feature['featureType'] ) ) {
							$_custom_feature .= '&style=feature:' . $feature['featureType'] . '%7C';
							if ( isset( $feature['elementType'] ) ) {
								$_custom_feature .= 'element:' . $feature['elementType'] . '%7C';
							}
							if ( isset( $feature['stylers'] ) ) {
								foreach ( $feature['stylers'] as $styler ) {
									foreach ( $styler as $style => $value ) {
										$_custom_feature .= $style . ':' . ( ( 'color' === $style ) ? rawurlencode( str_replace( '#', '0x', $value ) ) : $value ) . '%7C';
									}
								}
							}
						}
					}
					$atts['custom_style'] = $_custom_feature;
					unset( $_custom_style_array, $_custom_feature, $_custom_style );
				} else {
					$atts['custom_style'] = '';
				}
			} else {
				$atts['custom_style'] = '';
			}
		}

		$style_height = '';

		if ( ! empty( $atts['height'] ) && 'interactive' === $atts['map_type'] ) {
			$style_height = ' style="height:' . $atts['height'] . 'px;"';
		}

		$at_home_coordinates = $this->get_home_coordinates( $atts['lat_lng'] );

		$map_id = uniqid( 'map_canvas' );

		$class = apply_filters( 'wc_untappd_map_class', $class, $atts );

		$output = '<div class="' . esc_attr( $class_master ) . '" id="' . esc_attr( $map_id ) . '"' . $style_height . '></div>';
		$output = '<div' . $id_attr . ' class="' . esc_attr( implode( ' ', $class ) ) . '"' . $style_attr . '>' . $output . '</div>';

		$output_script = 'jQuery(' . esc_attr( $map_id ) . ').UntappdMap({map_type: "' . esc_attr( $atts['map_type'] ) . '", map_use_icon: ' . esc_attr( $atts['map_use_icon'] ) . ', center_lat: "' . esc_attr( $at_home_coordinates['lat'] ) . '", center_lng: "' . esc_attr( $at_home_coordinates['lng'] ) . '", center_map: "' . esc_attr( $atts['center_map'] ) . '", zoom: ' . intval( $atts['zoom'] ) . ', custom_style:  "' . esc_attr( $atts['custom_style'] ) . '", height: ' . intval( $atts['height'] ) . ', brewery_id: ' . intval( $atts['brewery_id'] ) . ', api_key: "' . esc_attr( $atts['api_key'] ) . '"});';

		wp_add_inline_script( 'brewery-activity-feed-js', $output_script );

		$output = apply_filters( 'wc_untappd_map_output', $output, $atts );

		return $output;
	}

	/**
	 * Enqueue scripts.
	 */
	public function wc_untappd_enqueue_js() {

		wp_enqueue_script( 'brewery-activity-feed-pagination-js', plugins_url( 'assets/js/brewery-activity-feed-pagination.min.js', __FILE__ ), array( 'jquery', 'brewery-activity-feed-map-googleapis-js' ), WC_UNTAPPD_RATINGS_VERSION, true );
		wp_enqueue_script( 'brewery-activity-feed-js', plugins_url( 'assets/js/brewery-activity-feed.min.js', __FILE__ ), array( 'jquery', 'brewery-activity-feed-map-googleapis-js' ), WC_UNTAPPD_RATINGS_VERSION, true );

		wp_localize_script(
			'brewery-activity-feed-js',
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
	}

	/**
	 * Generate a map feed.
	 */
	public function wc_untappd_map_feed() {

		if ( WC_Untappd_Ratings::api_is_active() ) {
 			// PHPCS:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( ! isset( $_GET['wc_untappd_map_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['wc_untappd_map_nonce'] ), 'wc_untappd_map_nonce' ) ) {
				wp_send_json( array( 'error' => __( 'Ivalid Request', 'wc-untappd-ratings' ) ) );
			}

			$brewery_id = filter_input( INPUT_GET, 'brewery_id', FILTER_VALIDATE_INT );

			if ( ! $brewery_id ) {
				$brewery_id = get_option( 'wc_untappd_map_brewery_id', '' );
			}

			if ( empty( $brewery_id ) ) {
				wp_send_json( array( 'error' => __( 'Brewery ID is empty, please set it at Woocommerce Untappd Options Tab', 'wc-untappd-ratings' ) ) );
			}

			$max_checkins = $this->max_checkins();

			$cache_key = 'wc_untappd_map_feed_' . $brewery_id . ( ( current_user_can( 'edit_posts' ) ) ? '_is_admin_' : '_' ) . apply_filters( 'wpml_current_language', '' ) . '_' . $max_checkins;

			$data = get_transient( $cache_key );

			if ( false !== $data && ! is_array( $data ) ) {
				if ( ! headers_sent() ) {
					header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
				}

				// PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $data;

				if ( wp_doing_ajax() ) {
					wp_die(
						'',
						'',
						array(
							'response' => null,
						)
					);
				} else {
					die;
				}
			}

			if ( get_option( 'wc_untappd_map_cache_is_working' ) === 'no' ) {
				wp_send_json( array( 'error' => __( 'Untappd Cache not working', 'wc-untappd-ratings' ) ) );
			}

			$brewery_feed_result = WC_Untappd_Ratings::API()->get( 'brewery/checkins/' . $brewery_id );

			if ( is_untappd_error( $brewery_feed_result ) ) {
				wp_send_json( array( 'error' => __( 'Untappd API not working', 'wc-untappd-ratings' ) ) );
			}

			$brewery_feed_result = json_decode( $brewery_feed_result, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				wp_send_json( array( 'error' => __( 'Untappd invalid response', 'wc-untappd-ratings' ) ) );
			}

			$wc_untappd_map_feed = $this->brewery_feed( $brewery_feed_result );

			if ( empty( $wc_untappd_map_feed ) ) {
				wp_send_json( array( 'error' => __( 'Untappd invalid data', 'wc-untappd-ratings' ) ) );
			}

			for ( $i = 1; $i < $max_checkins; $i++ ) {
				if ( ! isset( $brewery_feed_result['response']['pagination']['max_id'] ) ) {
					break;
				}

				$brewery_feed_result = WC_Untappd_Ratings::API()->get( 'brewery/checkins/' . $brewery_id, array( 'max_id' => $brewery_feed_result['response']['pagination']['max_id'] ) );

				if ( is_untappd_error( $brewery_feed_result ) ) {
					wp_send_json( $wc_untappd_map_feed );
				}

				$brewery_feed_result = json_decode( $brewery_feed_result, true );

				$wc_untappd_map_feed = $this->array_merge_recursive_distinct( $wc_untappd_map_feed, $this->brewery_feed( $brewery_feed_result ) );
			}

			$cache_time = absint( get_option( 'wc_untappd_ratings_cache_time', 3 ) ) * HOUR_IN_SECONDS;

			if ( set_transient( $cache_key, wp_json_encode( $wc_untappd_map_feed ), $cache_time ) === false ) {
				update_option( 'wc_untappd_map_cache_is_working', 'no' );
			} else {
				update_option( 'wc_untappd_map_cache_is_working', 'yes' );
			}

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

		if ( empty( $at_home_coordinates ) ) {
			$at_home_coordinates = get_option( 'wc_untappd_map_at_home_coordinates', null );
		}

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
	 */
	private function brewery_feed( array $brewery_feed_result ) {

		$brewery_feed = array();

		if ( ! empty( $brewery_feed_result ) && isset( $brewery_feed_result['response']['checkins']['count'] ) ) {

			foreach ( $brewery_feed_result['response']['checkins']['items'] as $untappd_checkin ) {

				if ( isset( $untappd_checkin['venue']['location'] ) && ! empty( $untappd_checkin['venue']['location']['lat'] && ! empty( $untappd_checkin['venue']['location']['lng'] ) ) ) {
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
						'tax_query'      => array(  // PHPCS:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'slug',
								'terms'    => array( 'keykeg' ),
								'operator' => 'NOT IN',
							),
						),
					);

					$products = get_posts( $args );

					$product_id = null;
					$permalink  = '';

					if ( count( $products ) ) {
						$product_id = apply_filters( 'wpml_object_id', $products[0]->ID, 'product', true );
						$permalink  = get_permalink( $product_id );
					}

					if ( 9917985 === absint( $untappd_checkin['venue']['venue_id'] ) ) {

						$at_home_coordinates = $this->get_home_coordinates();

						$untappd_checkin['venue']['location']['lat'] = (float) $at_home_coordinates['lat'];
						$untappd_checkin['venue']['location']['lng'] = (float) $at_home_coordinates['lng'];
					}

					$untappd_checkin_comment = '';

					if ( current_user_can( 'edit_posts' ) ) {
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
						'product_id'     => (int) $product_id,
						'location'       => ( ! empty( $untappd_checkin['user']['location'] ) && current_user_can( 'edit_posts' ) ) ? sanitize_text_field( $untappd_checkin['user']['location'] ) : '',
						'venue_name'     => $untappd_checkin['venue']['venue_name'],
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
	 */
	private function max_checkins() {
		$max_checkins = absint( get_option( 'wc_untappd_map_total_checkins', 300 ) ) / 25;

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
