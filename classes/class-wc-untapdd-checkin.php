<?php
/**
 * WC_Untapdd_Checkin
 *
 * @author    ChillCode
 * @copyright Copyright (c) 2024, ChillCode All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package   Untappd Ratings for WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Untapdd_Checkin class.
 */
class WC_Untapdd_Checkin {

	/**
	 * Please Note It's possible that you could pass through a foursquare venue ID, and not get back a "venue" object in the response. This can be due to many reasons, but mainly due to a Foursquare connectivity issue. Your app or service should never depending on a one-to-one match on the foursquare ID send, and a venue object returned as part of the response. Always do a null check to make sure that the object's attributes exists before digging deeper into it.
	 * access_token (string, required) - The access token for the acting user
	 *
	 * @var Array $checkin_data
	 *
	 * @param string $gmt_offset Required - The numeric value of hours the user is away from the GMT (Greenwich Mean Time), such as -5.
	 * @param string $timezone (, required) - The timezone of the user, such as EST or PST./li>
	 * @param bid (int, required) - The numeric Beer ID you want to check into.
	 * @param foursquare_id (string, optional) - The MD5 hash ID of the Venue you want to attach the beer checkin. This HAS TO BE the MD5 non-numeric hash from the foursquare v2.
	 * @param int $geolat Optional - The numeric Latitude of the user. This is required if you add a location.
	 * @param geolng (int, optional) - The numeric Longitude of the user. This is required if you add a location.
	 * @param shout (string, optional) - The text you would like to include as a comment of the checkin. Max of 140 characters.
	 * @param rating (int, optional) - The rating score you would like to add for the beer. This can only be 1 to 5 (half ratings are included). You can't rate a beer a 0.
	 * @param facebook (string, optional) - If you want to push this check-in to the users' Facebook account, pass this value as "on", default is "off"
	 * @param twitter (string, optional) - If you want to push this check-in to the users' Twitter account, pass this value as "on", default is "off"
	 * @param foursquare (string, optional) - If you want to push this check-in to the users' Foursquare account, pass this value as "on", default is "off". You must include a location for this to enabled.
	 */
	private $checkin_data = array(
		'gmt_offset'    => '+1',
		'timezone'      => 'CET',
		'bid'           => null,
		'foursquare_id' => '',
		'geolat'        => 0,
		'geolng'        => 0,
		'shout'         => '',
		'rating'        => 0,
		'facebook'      => 'off',
		'twitter'       => 'off',
		'foursquare'    => 'off',
	);

	/**
	 * Get checkin data.
	 */
	public function data() {
		$checkin_data = array(
			'foursquare_id' => '',
			'geolat'        => 0,
			'geolng'        => 0,
			'shout'         => '',
			'rating'        => 0,
			'facebook'      => 'off',
			'twitter'       => 'off',
			'foursquare'    => 'off',
		);

		foreach ( $this->checkin_data as $name => $value ) {
			if ( array_key_exists( $name, $checkin_data ) && $checkin_data[ $name ] === $value ) {
				unset( $checkin_data[ $name ] );
			} else {
				$checkin_data[ $name ] = $value;
			}
		}

		return $checkin_data;
	}

	/**
	 * Sets the legacy public variables for backwards compatibility.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 */
	public function __set( $key, $value ) {
		if ( array_key_exists( $key, $this->checkin_data ) ) {
			$this->checkin_data[ $key ] = $value;
		}
	}

	/**
	 * Gets the legacy public variables for backwards compatibility.
	 *
	 * @param string $key Key.
	 * @return array|string
	 */
	public function __get( $key ) {
		if ( array_key_exists( $key, $this->checkin_data ) ) {
			return $this->checkin_data[ $key ];
		}

		return null;
	}

	/**
	 * Magic unset method.
	 *
	 * @param mixed $key Key to unset.
	 */
	public function __unset( $key ) {
		if ( array_key_exists( $key, $this->checkin_data ) ) {
			unset( $this->checkin_data[ $key ] );
		}
	}
}
