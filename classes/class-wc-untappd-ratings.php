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
 * @copyright Copyright (c) 2003-2022, ChillCode All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package   WooCommerce Untappd
 *
 * WooCommerce and WordPress are property of their respective trademarks owners.
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Untappd_Ratings class.
 */
final class WC_Untappd_Ratings {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Untappd_Ratings
	 */
	protected static $instance = null;

	/**
	 * The instance of the API class.
	 *
	 * @var mixed
	 */
	protected static $api_instance = null;

	/**
	 * The instance of the Settings class.
	 *
	 * @var mixed
	 */
	protected static $settings_instance = null;

	/**
	 * The instance of the Product class.
	 *
	 * @var mixed
	 */
	protected static $product_instance = null;

	/**
	 * Initialize Untappd for WooCommerce.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ), -1 );

		$woocommerce_plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

		if ( in_array( $woocommerce_plugin_path, (array) wp_get_active_and_valid_plugins(), true ) ||
			in_array( $woocommerce_plugin_path, wp_get_active_network_plugins(), true )
		) {
			add_filter( 'network_admin_plugin_action_links_wc-untappd-ratings/wc-untappd-ratings.php', array( $this, 'plugin_action_links_woocommerce' ) );
			add_filter( 'plugin_action_links_wc-untappd-ratings/wc-untappd-ratings.php', array( $this, 'plugin_action_links_woocommerce' ) );

			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		} else {
			add_action(
				'admin_notices',
				function() {
					global $pagenow;

					if ( 'plugins.php' === $pagenow ) {
						printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error is-dismissible', esc_html__( 'WooCommerce Untappd requires WooCommerce to be installed and active.', 'wc-untappd-ratings' ) );
					}
				}
			);
		}
	}

	/**
	 * Append links to plugin info.
	 *
	 * @param array $actions Actions Array.
	 *
	 * @return array
	 */
	public function plugin_action_links_woocommerce( array $actions ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=untappd_settings' ) . '">' . esc_html__( 'Settings', 'wc-untappd-ratings' ) . '</a>',
			),
			$actions
		);
	}

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	public function init() {
		if ( function_exists( 'load_plugin_textdomain' ) ) {
			load_plugin_textdomain( 'wc-untappd-ratings', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'untappd_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function untappd_enqueue_scripts() {
		wp_enqueue_style( 'untappd-css', plugins_url( 'assets/css/untappd.css', WC_UNTAPPD_RATINGS_PLUGIN_FILE ), array(), WC_UNTAPPD_RATINGS_VERSION );
	}

	/**
	 * After plugins loaded.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		if ( is_admin() ) {
			require_once WC_UNTAPPD_RATINGS_PLUGIN_DIR . 'classes' . DIRECTORY_SEPARATOR . 'class-wc-untappd-settings.php';

			self::$settings_instance = new WC_Untappd_Settings();
		}

		$untappd_params = array(
			'client_id'     => get_option( 'wc_untappd_client_id' ),
			'client_secret' => get_option( 'wc_untappd_client_secret' ),
			'api_url'       => get_option( 'wc_untappd_api_url' ),
			'app_name'      => get_option( 'wc_untappd_api_useragent' ),
		);

		if ( empty( $untappd_params['api_url'] ) || empty( $untappd_params['app_name'] ) || empty( $untappd_params['client_id'] ) || empty( $untappd_params['client_secret'] ) ) {
			add_action(
				'admin_notices',
				function() {
					global $pagenow;

					if ( 'plugins.php' === $pagenow ) {
						printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error is-dismissible', esc_html__( 'Configure Woocommerce Untappd to start using it.', 'wc-untappd-ratings' ) );
					}
				}
			);
		} else {
			add_filter( 'wc_get_template', array( $this, 'global_wc_get_template' ), 11, 5 );

			require_once WC_UNTAPPD_RATINGS_PLUGIN_DIR . 'classes' . DIRECTORY_SEPARATOR . 'class-wc-untappd-error.php';
			require_once WC_UNTAPPD_RATINGS_PLUGIN_DIR . 'classes' . DIRECTORY_SEPARATOR . 'class-wc-untappd-api.php';
			require_once WC_UNTAPPD_RATINGS_PLUGIN_DIR . 'classes' . DIRECTORY_SEPARATOR . 'class-wc-untapdd-product.php';

			self::$api_instance     = new WC_Untappd_API( $untappd_params['client_id'], $untappd_params['client_secret'], $untappd_params['app_name'], $untappd_params['api_url'] );
			self::$product_instance = new WC_Untapdd_Product();

			require_once WC_UNTAPPD_RATINGS_PLUGIN_DIR . '/addons/brewery-activity-feed/class-wc-untapdd-brewery-activity-feed.php';

			do_action( 'untappd_load_addons' );
		}

		if ( 'yes' === get_option( 'wc_untappd_show_logo', 'no' ) ) {
			add_filter(
				'storefront_credit_links_output',
				function( $links_output ) {
					return $links_output . '<div id="powered_by_untappd"><img width="166px" height="40px" src="' . plugin_dir_url( WC_UNTAPPD_RATINGS_PLUGIN_FILE ) . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'powered-by-untappd-logo-40px.png"></div>';
				}
			);
		}
	}

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {
		static::delete_cache();
		static::delete_options();
	}

	/**
	 * Activate plugin, keep for database creation.
	 *
	 * @return void
	 */
	public static function activate() {
	}

	/**
	 * Singleton API.
	 *
	 * @return WC_Untappd_API
	 */
	public static function API() { // PHPCS:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		return self::$api_instance;
	}

	/**
	 * Check if API is initialized.
	 *
	 * @return bool
	 */
	public static function api_is_active() :bool {
		return self::$api_instance instanceof WC_Untappd_API;
	}

	/**
	 * Delete cache.
	 *
	 * @return int|false
	 */
	public static function delete_cache() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%\_transient\_wc\_untappd%'" );
	}

	/**
	 * Delete options.
	 *
	 * @return int|false
	 */
	public static function delete_options() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wc\_untappd%'" );
	}

	/**
	 * Hook WooCommerce templates
	 *
	 *  @since 1.0.0
	 *
	 * @param string $located       Located.
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments. (default: array).
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 */
	public function global_wc_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		$plugin_template_path = untrailingslashit( plugin_dir_path( WC_UNTAPPD_RATINGS_PLUGIN_FILE ) ) . '/templates/woocommerce/' . $template_name;

		if ( is_file( $plugin_template_path ) ) {
			$located = $plugin_template_path;
		}

		return $located;
	}

	/**
	 * Get this as singleton.
	 *
	 * @return WC_Untappd_Ratings
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

/**
 * Main WooCommerce Instance.
 *
 * Ensures only one instance of WooCommerce is loaded or can be loaded.
 *
 * @since 2.1
 * @static
 * @see WC()
 * @return WooCommerce - Main instance.
 */
function WC_Untappd_Ratings() : WC_Untappd_Ratings { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WC_Untappd_Ratings::instance();
}

register_activation_hook(
	WC_UNTAPPD_RATINGS_PLUGIN_FILE,
	array( 'WC_Untappd_Ratings', 'activate' )
);

register_deactivation_hook(
	WC_UNTAPPD_RATINGS_PLUGIN_FILE,
	array( 'WC_Untappd_Ratings', 'deactivate' )
);

/**
 * Get a shared logger instance.
 *
 * Use the woocommerce_logging_class filter to change the logging class. You may provide one of the following:
 *     - a class name which will be instantiated as `new $class` with no arguments
 *     - an instance which will be used directly as the logger
 * In either case, the class or instance *must* implement WC_Logger_Interface.
 *
 * @see WC_Logger_Interface
 *
 * @param string $message Log message.
 * @param string $level One of the following:
 *     'emergency': System is unusable.
 *     'alert': Action must be taken immediately.
 *     'critical': Critical conditions.
 *     'error': Error conditions.
 *     'warning': Warning conditions.
 *     'notice': Normal but significant condition.
 *     'info': Informational messages.
 *     'debug': Debug-level messages.
 * @return void
 */
function wc_untappd_logger( $message, $level = 'debug' ) {
	if ( function_exists( 'wc_get_logger' ) ) {
		$logger = wc_get_logger();

		$context = array( 'source' => 'wc-untappd-ratings' );

		$logger->log( $level, $message, $context );
	} else {
		error_log( "WCU error ({$level}): {$message}" ); // PHPCS:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
