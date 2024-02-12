<?php
/**
 * Single Product Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/rating.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
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

$rating_count = (int) $product->get_rating_count();
$review_count = (int) $product->get_review_count();
$average      = (float) $product->get_average_rating();

/*
	Use untappd ratings instead WooCommerce  one's.
*/

$wc_untappd_ratings_enabled = wc_untappd_ratings_enabled();

if ( $wc_untappd_ratings_enabled ) {
	$untappd_beer_link         = '#reviews';
	$untappd_ratings_show_text = '';

	$beer_id = absint( $product->get_meta( '_untappd_beer_id', true ) );

	if ( $beer_id > 0 ) {
		$product_id = $product->get_id();

		WC_Untapdd_Product::update_beer_meta( $beer_id, $product_id );

		$rating_count = absint( $product->get_meta( '_untappd_rating_count', true ) );
		$average      = (float) $product->get_meta( '_untappd_average_rating', true );
		$beer_slug    = $product->get_meta( '_untappd_beer_slug', true );

		$untappd_beer_link         = 'https://untappd.com/b/' . $beer_slug . '/' . $beer_id;
		$untappd_ratings_show_text = ( wc_untappd_ratings_show_text() ) ? number_format_i18n( $average, 2 ) . '/5' : '';
	}
}

if ( $rating_count > 0 && $average > 0 && $wc_untappd_ratings_enabled ) : ?>
	<div class="woocommerce-product-rating">
		<div><?php esc_html_e( 'Untappd Ratings', 'wc-untappd-ratings' ); ?></div>
			<?php echo wc_get_rating_html( $average, $rating_count ) . $untappd_ratings_show_text; // PHPCS:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?>
			<?php if ( wc_untappd_ratings_show_total() ) : ?>
			<a target="_blank" href="<?php echo esc_attr( $untappd_beer_link ); ?>" class="woocommerce-review-link" rel="noopener nofollow">(<?php printf( /* translators: %s rating: Total ratings */ _n( '%s rating', '%s ratings', $rating_count, 'wc-untappd-ratings' ), '<span class="count">' . esc_html( $rating_count ) . '</span>' );  // PHPCS:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?>)</a> 
			<?php endif; ?>
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
