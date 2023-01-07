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
 * WC_Untappd_Error class.
 */
class WC_Untappd_Error extends Exception {

	// phpcs:ignore Squiz.Commenting.VariableComment.Missing
	protected $error_code;

	/**
	 * Constructor
	 *
	 * @param string $error_code       Error code.
	 * @param string $error_message    Error message.
	 * @param string $http_status_code http status code.
	 */
	public function __construct( $error_code, $error_message, $http_status_code ) {
		$this->error_code = $error_code;

		parent::__construct( $error_message, $http_status_code );
	}

	/**
	 * Get error code.
	 */
	public function getErrorCode() {
		return $this->error_code;
	}
}

/**
 * Check whether variable is a Untappd Error.
 *
 * Returns true if $thing is an object of the WC_Untappd_Error class.
 *
 * @since 1.0.4
 *
 * @param mixed $thing Check if unknown variable is a WC_Untappd_Error object.
 * @return bool True, if WC_Untappd_Error. False, if not WC_Untappd_Error.
 */
function is_untappd_error( $thing ) {
	return ( $thing instanceof WC_Untappd_Error );
}
