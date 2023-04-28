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
			$review_count = absint( $rating_count );
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
