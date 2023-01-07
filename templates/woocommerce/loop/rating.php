<?php
/**
 * Copyright 2021 ChillCode All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    ChillCode
 * @copyright Copyright (c) 2003-2021, ChillCode All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package   WooCommerce Untappd
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Global Product Variable.
 *
 * @var WC_Product $product
 */
global $product;

if ( ! wc_review_ratings_enabled() ) {
	return;
}

$untappd_ratings_allow = get_option( 'wc_untappd_ratings_allow', 'no' ) === 'yes' && WC_Untappd_Ratings::api_is_active() ? true : false;
$average               = 0;

if ( $untappd_ratings_allow ) {
	$beer_id = (int) $product->get_meta( '_untappd_beer_id', true );

	if ( $beer_id > 0 ) {
		$beer_info = WC_Untappd_Ratings::API()->beer_ratings( $beer_id, $product->get_id() );
		if ( isset( $beer_info['response'] ) && isset( $beer_info['response']['beer'] ) ) {
			$average = $product->get_meta( '_untappd_average_rating', true );
		}
	}
} else {
	$average = $product->get_average_rating();
}

echo wc_get_rating_html( $average ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
