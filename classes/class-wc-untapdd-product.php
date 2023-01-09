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
 * WC_Untapdd_Product class.
 */
class WC_Untapdd_Product {

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'woocommerce_product_data_tabs', __CLASS__ . '::woocommerce_product_data_tabs' );
			add_action( 'woocommerce_process_product_meta', __CLASS__ . '::woocommerce_process_product_meta', 100 );
			add_action( 'woocommerce_product_data_panels', __CLASS__ . '::woocommerce_product_data_panels' );
		}

		if ( get_option( 'wc_untappd_ratings_add_to_structured_data' ) === 'yes' ) {
			add_filter( 'woocommerce_structured_data_context', __CLASS__ . '::woocommerce_structured_data_context', 10, 4 );
		}

		$untappd_ratings_allow = get_option( 'wc_untappd_ratings_allow', 'no' ) === 'yes' && WC_Untappd_Ratings::api_is_active() ? true : false;

		if ( $untappd_ratings_allow ) {
			add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'woocommerce_get_catalog_ordering_args' ), 998 );
			add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 999, 2 );
		}
	}

	/**
	 * Returns an array of arguments for ordering products based on Untappd ratings.
	 *
	 * @param array    $posts_clauses An array of arguments for ordering products based on the selected values.
	 * @param WP_Query $wp_query WP_Query object.
	 * @return mixed
	 */
	public function posts_clauses( $posts_clauses, $wp_query ) {
		$orderby = $wp_query->get( 'orderby' );

		switch ( $orderby ) {
			case '_untappd_average_rating':
				$order                    = $wp_query->get( 'order' ) ? esc_sql( $wp_query->get( 'order' ) ) : 'desc';
				$posts_clauses['orderby'] = " wp_postmeta.meta_value+0 {$order}, wp_posts.ID {$order} ";
				break;
		}

		return $posts_clauses;
	}

	/**
	 * Returns an array of arguments for ordering products based on Untappd ratings.
	 *
	 * @param array $args An array of arguments for ordering products based on the selected values.
	 * @return mixed
	 */
	public function woocommerce_get_catalog_ordering_args( $args ) {
		if ( isset( $args['orderby'] ) && 'rating' === $args['orderby'] ) {
			$args['order']    = 'DESC';
			$args['orderby']  = '_untappd_average_rating';
			$args['meta_key'] = '_untappd_average_rating'; //phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		}
		return $args;
	}

	/**
	 * Add Untappd tab to product page tabs.
	 *
	 * @param array $tabs Tabs passed by WC filter.
	 */
	public static function woocommerce_product_data_tabs( $tabs ) {
		$tabs['wc-untappd-ratings'] = array(
			'label'    => esc_html__( 'Untappd', 'wc-untappd-ratings' ),
			'target'   => 'woocommerce_untappd',
			'class'    => array( '' ),
			'priority' => 90,
		);

		return $tabs;
	}

	/**
	 * Add Untappd beer id  & beer volume field to Untappd tab.
	 */
	public static function woocommerce_product_data_panels() {
		global $post;

		echo "<div id='woocommerce_untappd' class='panel woocommerce_options_panel'>";

		wp_nonce_field( 'woocommerce_untappd_nonce', 'woocommerce_untappd_nonce' );

		echo '<div class="options_group">';

		$untappd_beer_id = get_post_meta( $post->ID, '_untappd_beer_id', true );

		woocommerce_wp_text_input(
			array(
				'id'          => 'untappd_beer_id',
				'label'       => esc_html__( 'Untappd beer ID', 'wc-untappd-ratings' ),
				'placeholder' => '',
				'value'       => ( ! empty( $untappd_beer_id ) ? $untappd_beer_id : '' ),
				'desc_tip'    => 'true',
				'description' => esc_html__( 'Enter untappd beer ID', 'wc-untappd-ratings' ),
				'type'        => 'number',
			)
		);

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Update Product Untappd ID.
	 *
	 * @param array $post_id post_id of the product to save meta to. Passed by WC filter.
	 */
	public static function woocommerce_process_product_meta( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$woocommerce_untappd_nonce = filter_input( INPUT_POST, 'woocommerce_untappd_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		// PHPCS:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! $woocommerce_untappd_nonce || ! wp_verify_nonce( $woocommerce_untappd_nonce, 'woocommerce_untappd_nonce' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['untappd_beer_id'] ) ) {
			update_post_meta( $post_id, '_untappd_beer_id', (int) $_POST['untappd_beer_id'] );
		}
	}

	/**
	 * Add Untappd beer data to structured data.
	 *
	 * @param array  $array Context array.
	 * @param array  $data Structured data.
	 * @param string $type Structured data type.
	 * @param mixed  $value Structured data value.
	 */
	public static function woocommerce_structured_data_context( $array, $data, $type, $value ) {

		if ( 'product' === $type && WC_Untappd_Ratings::api_is_active() ) {
			global $product;

			$beer_id = (int) $product->get_meta( '_untappd_beer_id', true );

			if ( $beer_id <= 0 ) {
				return $array;
			}

			$beer_info = WC_Untappd_Ratings::API()->beer_info( $beer_id );

			if ( isset( $beer_info['response'] ) && isset( $beer_info['response']['beer'] ) && isset( $beer_info['response']['beer']['rating_count'] ) && isset( $beer_info['response']['beer']['rating_score'] ) ) {
				$rating_value = round( (float) $beer_info['response']['beer']['rating_score'], 2 );
				$review_count = (int) $beer_info['response']['beer']['rating_count'];

				if ( $rating_value > 0 && $review_count > 0 ) {
					$array['aggregateRating']['@type']       = 'AggregateRating';
					$array['aggregateRating']['ratingValue'] = (string) $rating_value;
					$array['aggregateRating']['reviewCount'] = (string) $review_count;
				}

				$array['brand']['@type'] = 'Brand';
				$array['brand']['name']  = (string) isset( $beer_info['response']['beer']['brewery']['brewery_name'] ) ? $beer_info['response']['beer']['brewery']['brewery_name'] : get_bloginfo( 'name' );

				$array['MPN'] = $product->get_sku();

				if ( empty( $array['MPN'] ) ) {
					$array['MPN'] = 'Untappd' . $beer_id;
				}

				if ( isset( $beer_info['response']['beer']['checkins'] ) && ! empty( $beer_info['response']['beer']['checkins'] ) ) {
					$untappd_checkins = $beer_info['response']['beer']['checkins'];

					if ( $untappd_checkins['count'] > 1 ) {
						foreach ( $untappd_checkins['items'] as $checkin ) {
							$checkin_rating_score                  = round( (float) $checkin['rating_score'], 2 );
							$checkin_rating_score_minimum_required = (float) get_option( 'wc_untappd_ratings_review_min', 3.5 );
							if ( ! empty( $checkin['checkin_comment'] ) && $checkin_rating_score >= $checkin_rating_score_minimum_required && $rating_value > 0 && $review_count > 0 ) {
								$review_author = trim( $checkin['user']['first_name'] );
								$review_author = ( $review_author ) ? $review_author : 'Unknown Author';

								$review_date = gmdate( 'Y-m-d', strtotime( $checkin['created_at'] ) );

								$review_description = trim( $checkin['checkin_comment'] );
								$review_description = ( $review_description ) ? $review_description : 'Untappd Rating';

								$review_name = $checkin['beer']['beer_name'];

								$array['review'][] = array(
									'@type'         => 'Review',
									'author'        => array(
										'@type' => 'person',
										'name'  => $review_author,
									),
									'datePublished' => $review_date,
									'description'   => $review_description,
									'name'          => $review_name,
									'reviewRating'  => array(
										'@type'       => 'Rating',
										'bestRating'  => '5',
										'ratingValue' => (string) $checkin_rating_score,
										'worstRating' => '1',
									),
								);
							}
						}
					}
				}
			}
		}

		return $array;
	}
}
