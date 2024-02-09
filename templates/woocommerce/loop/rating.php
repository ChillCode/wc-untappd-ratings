<?php
/**
 * Loop Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/rating.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
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

$wc_untappd_average_rating = 0;

if ( wc_untappd_ratings_enbaled() ) {
	$beer_id = absint( $product->get_meta( '_untappd_beer_id', true ) );

	if ( $beer_id > 0 ) {
		if ( WC_Untappd_Ratings::API()->beer_ratings( $beer_id, $product->get_id() ) ) {
			$wc_untappd_average_rating = $product->get_meta( '_untappd_average_rating', true );
		}
	}
} else {
	$wc_untappd_average_rating = $product->get_average_rating();
}

echo wc_get_rating_html( (float) $wc_untappd_average_rating ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
