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
	exit; // Exit if accessed directly.
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

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

/*
	Use untappd ratings instead Woocommerce one's.
*/

if ( $untappd_ratings_allow ) {
	$untappd_beer_link          = '#reviews';
	$untappd_ratings_show_text  = '';
	$untappd_ratings_show_total = false;

	$beer_id    = absint( $product->get_meta( '_untappd_beer_id', true ) );
	$product_id = $product->get_id();

	// Legacy code.
	if ( ! $beer_id ) {
		$beer_id = absint( $product->get_attribute( 'untappd_beer_id' ) );

		if ( $beer_id ) {
			update_post_meta( $product_id, '_untappd_beer_id', $beer_id );
		}
	}

	if ( $beer_id > 0 ) {
		$beer_info = WC_Untappd_Ratings::API()->beer_ratings( $beer_id, $product_id );
		if ( isset( $beer_info['response'] ) && isset( $beer_info['response']['beer'] ) ) {
			$untappd_beer_link = 'https://untappd.com/b/' . $beer_info['response']['beer']['beer_slug'] . '/' . $beer_id;

			$rating_count = $product->get_meta( '_untappd_rating_count', true );
			$review_count = $rating_count;
			$average      = $product->get_meta( '_untappd_average_rating', true );
		}

		$untappd_ratings_show_text  = ( get_option( 'wc_untappd_ratings_show_text' ) === 'yes' ) ? number_format_i18n( $average, 2 ) . '/5' : '';
		$untappd_ratings_show_total = ( get_option( 'wc_untappd_ratings_show_total' ) === 'yes' ) ? true : false;
	}
}

if ( $rating_count > 0 && $average > 0 && $untappd_ratings_allow ) : ?>

		<div class="woocommerce-product-rating">
		<div><?php esc_html_e( 'Untappd Ratings', 'wc-untappd-ratings' ); ?></div>
			<?php echo wc_get_rating_html( $average, $rating_count ) . $untappd_ratings_show_text; // PHPCS:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?>
			<?php //phpcs:disable ?>
			<?php if ($untappd_ratings_show_total): ?>
			<a target="_blank" href="<?php echo $untappd_beer_link; ?>" class="woocommerce-review-link" rel="nofollow">(<span class="count"><?php echo esc_html( $review_count );?> ratings</span>)</a>
			<?php endif; ?>
			<?php // phpcs:enable ?>
		</div>

<?php elseif ( $rating_count > 0 ) : ?>

	<div class="woocommerce-product-rating">
		<?php echo wc_get_rating_html( $average, $rating_count );  // PHPCS:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?>
		<?php if ( comments_open() ) : ?>
			<?php //phpcs:disable ?>
			<a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'wc-untappd-ratings' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a>
			<?php // phpcs:enable ?>
		<?php endif ?>
	</div>

<?php endif; ?>
