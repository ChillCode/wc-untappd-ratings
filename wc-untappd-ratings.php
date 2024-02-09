<?php
/**
 * Plugin Name: Untappd Ratings for WooCommerce
 * Plugin URI: https://github.com/chillcode/wc-untappd-ratings
 * Documentation URI: https://github.com/chillcode/wc-untappd-ratings
 * Description: Connect your WooCommerce Store with Untappd
 * Author: ChillCode
 * Author URI: https://github.com/chillcode/
 * Version: 1.0.4
 * Text Domain: wc-untappd-ratings
 * Domain Path: /languages/
 * php version 8.0
 *
 * Copyright: (c) 2003-2024 ChillCode (https://github.com/chillcode/)
 *
 * @author    ChillCode
 * @copyright Copyright (c) 2003-2024, ChillCode (https://github.com/chillcode/)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package   Untappd Ratings for WooCommerce
 *
 * Domain Path: /languages/
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 6.0.1
 * WC tested up to: 8.5.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define global constants.
define( 'WC_UNTAPPD_RATINGS_PLUGIN_FILE', __FILE__ );
define( 'WC_UNTAPPD_RATINGS_PLUGIN_DIR', dirname( WC_UNTAPPD_RATINGS_PLUGIN_FILE ) . DIRECTORY_SEPARATOR );
define( 'WC_UNTAPPD_RATINGS_VERSION', '1.0.5' );

require_once WC_UNTAPPD_RATINGS_PLUGIN_DIR . 'classes' . DIRECTORY_SEPARATOR . 'class-wc-untappd-ratings.php';

WC_Untappd_Ratings();
