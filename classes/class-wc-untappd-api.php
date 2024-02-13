<?php
/**
 * WC_Untappd_API
 *
 * @author    ChillCode
 * @copyright Copyright (c) 2024, ChillCode All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package   Untappd Ratings for WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Untappd_API class.
 */
class WC_Untappd_API {

	private $untappd_params = array(); // PHPCS:ignore Squiz.Commenting.VariableComment.Missing
	private $untappd_api_url; // PHPCS:ignore Squiz.Commenting.VariableComment.Missing
	private $untappd_app_name; // PHPCS:ignore Squiz.Commenting.VariableComment.Missing
	private $untappd_cache_time; // PHPCS:ignore Squiz.Commenting.VariableComment.Missing
	private $untappt_x_ratelimit_remaining; // PHPCS:ignore Squiz.Commenting.VariableComment.Missing

	/**
	 * Constructor.
	 *
	 * @param string $client_id Client ID to authenticate API calls.
	 * @param string $client_secret Client secret to authenticate API calls.
	 * @param string $app_name The name op the APP required by Untappd.
	 * @param string $api_url Api url.
	 * @return void
	 */
	public function __construct( string $client_id, string $client_secret, string $app_name, string $api_url ) {
		$this->untappd_params = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
		);

		$this->untappd_api_url  = $api_url;
		$this->untappd_app_name = $app_name;

		$this->untappd_cache_time            = absint( get_option( 'wc_untappd_ratings_cache_time', 3 ) * HOUR_IN_SECONDS );
		$this->untappt_x_ratelimit_remaining = absint( get_option( 'wc_untappd_ratelimit_remaining', 100 ) );
	}

	/**
	 * This method allows you the obtain all the friend check-in feed of the authenticated user. This includes only beer checkin-ins from Friends. By default it will return at max 25 records.
	 *
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @param int    $max_id (Optional) The checkin ID that you want the results to start with.
	 * @param int    $min_id (Optional) Returns only checkins that are newer than this value.
	 * @param int    $limit (Optional) The number of results to return, max of 50, default is 25.
	 * @return array
	 */
	public function activity_feed( string $access_token = '', int $max_id = null, int $min_id = null, int $limit = 25 ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( is_null( $max_id ) ) ? null : 'max_id' => $max_id,
			( is_null( $min_id ) ) ? null : 'min_id' => $min_id,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'checkin/recent', $untappd_params );
	}

	/**
	 * This method allows you the obtain all the check-in feed of the selected user. By default it will return at max 25 records.
	 *
	 * @param string $user_name (Required) The username that you wish to call the request upon. If you do not provide a username - the feed will return results from the authenticated user (if the access_token is provided).
	 * @param int    $max_id (Optional) The checkin ID that you want the results to start with.
	 * @param int    $min_id (Optional) Returns only checkins that are newer than this value.
	 * @param int    $limit (Optional) The number of results to return, max of 25, default is 25.
	 * @return array
	 */
	public function user_activity_feed( string $user_name, int $max_id = null, int $min_id = null, int $limit = 25 ) {
		$untappd_params = array(
			( is_null( $max_id ) ) ? null : 'max_id' => $max_id,
			( is_null( $min_id ) ) ? null : 'min_id' => $min_id,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'user/checkins/' . $user_name, $untappd_params );
	}

	/**
	 * This method allows you the obtain all the check-in feed of the selected user. By default it will return at max 25 records.
	 * Note: When using authentication, you can drop off the USERNAME parameter and it will return the authenticated users'results.
	 *
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @param string $user_name (Optional) The username that you wish to call the request upon. If you do not provide a username - the feed will return results from the authenticated user (if the access_token is provided).
	 * @param int    $max_id (Optional) The checkin ID that you want the results to start with.
	 * @param int    $min_id (Optional) Returns only checkins that are newer than this value.
	 * @param int    $limit (Optional) The number of results to return, max of 25, default is 25.
	 * @return array
	 */
	public function authenticated_user_activity_feed( string $access_token = '', string $user_name = '', int $max_id = null, int $min_id = null, int $limit = 25 ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( is_null( $max_id ) ) ? null : 'max_id' => $max_id,
			( is_null( $min_id ) ) ? null : 'min_id' => $min_id,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'user/checkins/' . $user_name, $untappd_params );
	}

	/**
	 * This method allows you the obtain all the public feed for Untappd, within a certain location. By default it will return at max 25 records.
	 * Note: This will return only users who have made their account public.
	 *
	 * @param float  $lat (Required) The latitude of the query.
	 * @param float  $lng (Required) The longitude of the query.
	 * @param int    $max_id (Optional) The checkin ID that you want the results to start with.
	 * @param int    $min_id (Optional) Returns only checkins that are newer than this value.
	 * @param int    $limit (Optional) The number of results to return, max of 25, default is 25.
	 * @param int    $radius (Optional) The max radius you would like the check-ins to start within, max of 25, default is 25.
	 * @param string $dist_pref (Optional) If you want the results returned in miles or km. Available options: "m", or "km". Default is "m".
	 * @return array
	 */
	public function the_pub_local( float $lat, float $lng, int $max_id = null, int $min_id = null, int $limit = 25, $radius = 25, $dist_pref = 'km' ) {
		$untappd_params = array(
			'lat'                                     => $lat,
			'lng'                                     => $lng,
			( is_null( $max_id ) ) ? null : 'max_id'  => $max_id,
			( is_null( $min_id ) ) ? null : 'min_id'  => $min_id,
			( ! is_int( $limit ) ) ? null : 'limit'   => $limit,
			( ! is_int( $radius ) ) ? null : 'radius' => $radius,
			( ! in_array( strtolower( $dist_pref ), array( 'm', 'km' ), true ) ) ? null : 'dist_pref' => strtolower( $dist_pref ),
		);

		return $this->get( 'thepub/local', $untappd_params );
	}

	/**
	 * This method allows you the obtain an activity feed for a single venue for Untappd. By default it will return at max 25 records.
	 * Note: This will return only users who have made their account public.
	 *
	 * @param int $venue_id (Required) The Venue ID that you want to display checkins.
	 * @param int $max_id (Optional) The checkin ID that you want the results to start with.
	 * @param int $min_id (Optional) Returns only checkins that are newer than this value.
	 * @param int $limit (Optional) The number of results to return, max of 25, default is 25.
	 * @return array
	 */
	public function venue_activity_feed( int $venue_id, int $max_id = null, int $min_id = null, int $limit = 25 ) {
		$untappd_params = array(
			( is_null( $max_id ) ) ? null : 'max_id' => $max_id,
			( is_null( $min_id ) ) ? null : 'min_id' => $min_id,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'venue/checkins/' . $venue_id, $untappd_params );
	}

	/**
	 * This method allows you the obtain an activity feed for a single beer for Untappd. By default it will return at max 25 records
	 * Note: This will return only users who have made their account public.
	 *
	 * @param int $beer_id (Required) The beer ID that you want to display checkins.
	 * @param int $max_id (Optional) The checkin ID that you want the results to start with.
	 * @param int $min_id (Optional) Returns only checkins that are newer than this value.
	 * @param int $limit (Optional) The number of results to return, max of 25, default is 25.
	 * @return array
	 */
	public function beer_activity_feed( int $beer_id, int $max_id = null, int $min_id = null, int $limit = 25 ) {
		$untappd_params = array(
			( is_null( $max_id ) ) ? null : 'max_id' => $max_id,
			( is_null( $min_id ) ) ? null : 'min_id' => $min_id,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'beer/checkins/' . $beer_id, $untappd_params );
	}

	/**
	 * This method allows you the obtain an activity feed for a single brewery for Untappd. By default it will return at max 25 records.
	 *
	 * @param int $brewery_id (Required) The Brewery ID that you want to display checkins.
	 * @param int $max_id (Optional) The checkin ID that you want the results to start with.
	 * @param int $min_id (Optional) Returns only checkins that are newer than this value.
	 * @param int $limit (Optional) The number of results to return, max of 25, default is 25.
	 * @return array
	 */
	public function brewery_activity_feed( int $brewery_id, int $max_id = null, int $min_id = null, int $limit = 25 ) {
		$untappd_params = array(
			( is_null( $max_id ) ) ? null : 'max_id' => $max_id,
			( is_null( $min_id ) ) ? null : 'min_id' => $min_id,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'brewery/checkins/' . $brewery_id, $untappd_params );
	}

	/**
	 * This method will allow you pull in a feed of notifications (toasts and comments) on the authenticated user. It will return the 25 items by default and pagination is not supported. It will also show the last 25 news items in the order of created date.
	 *
	 * @param string $access_token (Required) The access token for the acting user.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of records that you will return (max 25, default 25).
	 * @return array
	 */
	public function notifications( string $access_token = '', int $offset = null, int $limit = 25 ) {
		$untappd_params = array(
			'access_token'                           => $access_token,
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'notifications', $untappd_params );
	}

	/**
	 * This method will return the user information for a selected user.
	 *
	 * @param string $user_name (Required) The username that you wish to call the request upon.
	 * @param bool   $compact (Optional) You can pass "true" here only show the user infomation, and remove the "checkins", "media", "recent_brews", etc attributes.
	 * @return array
	 */
	public function user_info( string $user_name, bool $compact = false ) {
		$untappd_params = array(
			( false === $compact ) ? null : 'compact' => 'true',
		);

		return $this->get( 'user/info/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return the user information for a selected user. Note: When using authentication, you can drop off the USERNAME parameter and it will return the authenticated users'results.
	 * Note: When using authentication, you can drop off the USERNAME parameter and it will return the authenticated users'results.
	 *
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @param string $user_name (Optional) The username that you wish to call the request upon. If you do not provide a username - the feed will return results from the authenticated user (if the access_token is provided).
	 * @param bool   $compact (Optional) You can pass "true" here only show the user infomation, and remove the "checkins", "media", "recent_brews", etc attributes.
	 * @return array
	 */
	public function authenticated_user_info( string $access_token = '', string $user_name = '', bool $compact = false ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( false === $compact ) ? null : 'compact' => 'true',
		);

		return $this->get( 'user/info/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return a list of 25 of the user's wish listed beers.
	 *
	 * @param string $user_name (Required) The username that you wish to call the request upon.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of results to return, max of 50, default is 25.
	 * @param string $sort (Optional) You can sort the results using these values: date - sorts by date (default), checkin - sorted by highest checkin, highest_rated - sorts by global rating descending order, lowest_rated - sorts by global rating ascending order, highest_abv - highest ABV from the wishlist, lowest_abv - lowest ABV from the wishlist.
	 * @return array
	 */
	public function user_wishlist( string $user_name, int $offset = null, int $limit = 25, string $sort = 'date' ) {
		$untappd_params = array(
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
			( ! in_array( strtolower( $sort ), array( 'date', 'checkin', 'highest_rated', 'lowest_rated', 'highest_abv', 'lowest_abv' ), true ) ) ? null : 'sort' => strtolower( $sort ),
		);

		return $this->get( 'user/wishlist/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return a list of 25 of the user's wish listed beers.
	 * Note: When using authentication, you can drop off the USERNAME parameter and it will return the authenticated users'results.
	 *
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @param string $user_name (Optional) The username that you wish to call the request upon.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of results to return, max of 50, default is 25.
	 * @param string $sort (Optional) You can sort the results using these values: date - sorts by date (default), checkin - sorted by highest checkin, highest_rated - sorts by global rating descending order, lowest_rated - sorts by global rating ascending order, highest_abv - highest ABV from the wishlist, lowest_abv - lowest ABV from the wishlist.
	 * @return array
	 */
	public function authenticated_user_wishlist( string $access_token = '', string $user_name = '', int $offset = null, int $limit = 25, string $sort = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
			( ! in_array( strtolower( $sort ), array( 'date', 'checkin', 'highest_rated', 'lowest_rated', 'highest_abv', 'lowest_abv' ), true ) ) ? null : 'sort' => strtolower( $sort ),
		);

		return $this->get( 'user/wishlist/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return the last 25 friends for a selected.
	 *
	 * @param string $user_name (Required)  The username that you wish to call the request upon.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of records that you will return (max 25, default 25).
	 * @return array
	 */
	public function user_friends( string $user_name, int $offset = null, int $limit = 25 ) {
		$untappd_params = array(
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'user/friends/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return the last 25 friends for a selected.
	 * Note: When using authentication, you can drop off the USERNAME parameter and it will return the authenticated users'results.
	 *
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @param string $user_name  (Optional) The username that you wish to call the request upon. If you do not provide a username - the feed will return results from the authenticated user (if the access_token is provided).
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of records that you will return (max 25, default 25).
	 * @return array
	 */
	public function authenticated_user_friends( string $access_token = '', string $user_name = '', int $offset = null, int $limit = 25 ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'user/friends/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return a list of the last 50 the user's earned badges.
	 *
	 * @param string $user_name (Required)  The username that you wish to call the request upon.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of records that you will return (max 25, default 25).
	 * @return array
	 */
	public function user_badges( string $user_name, int $offset = null, int $limit = 25 ) {
		$untappd_params = array(
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'user/badges/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return a list of the last 50 the user's earned badges.
	 * Note: When using authentication, you can drop off the USERNAME parameter and it will return the authenticated users'results.
	 *
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @param string $user_name (Optional) The username that you wish to call the request upon. If you do not provide a username - the feed will return results from the authenticated user (if the access_token is provided).
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of records that you will return (max 25, default 25).
	 * @return array
	 */
	public function authenticated_user_badges( string $access_token = '', string $user_name = '', int $offset = null, int $limit = 25 ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'user/badges/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return a list of 25 of the user's distinct beers.
	 *
	 * @param string $user_name (Required)  The username that you wish to call the request upon.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of records that you will return (max 25, default 25).
	 * @param string $sort (Optional) You can sort the results using these values: date - sorts by date (default), checkin - sorted by highest checkin, highest_rated - sorts by global rating descending order, lowest_rated - sorts by global rating ascending order, highest_abv - highest ABV from the wishlist, lowest_abv - lowest ABV from the wishlist.
	 * @param string $start_date (Optional) .
	 * @param string $end_date (Optional) .
	 * @return array
	 */
	public function user_beers( string $user_name, int $offset = null, int $limit = 25, string $sort = '', string $start_date = '', string $end_date = '' ) {
		$untappd_params = array(
			( is_null( $offset ) ) ? null : 'offset'       => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'        => $limit,
			( ! in_array( strtolower( $sort ), array( 'date', 'checkin', 'highest_rated', 'lowest_rated', 'highest_abv', 'lowest_abv' ), true ) ) ? null : 'sort' => strtolower( $sort ),
			( empty( $start_date ) ) ? null : 'start_date' => $start_date,
			( empty( $end_date ) ) ? null : 'end_date'     => $end_date,
		);

		return $this->get( 'user/beers/' . $user_name, $untappd_params );
	}

	/**
	 * This method will return a list of 25 of the user's distinct beers.
	 * Note: When using authentication, you can drop off the USERNAME parameter and it will return the authenticated users'results.
	 *
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @param string $user_name (Optional) The username that you wish to call the request upon. If you do not provide a username - the feed will return results from the authenticated user (if the access_token is provided).
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of records that you will return (max 25, default 25).
	 * @param string $sort (Optional) You can sort the results using these values: date - sorts by date (default), checkin - sorted by highest checkin, highest_rated - sorts by global rating descending order, lowest_rated - sorts by global rating ascending order, highest_abv - highest ABV from the wishlist, lowest_abv - lowest ABV from the wishlist.
	 * @param string $start_date (Optional) .
	 * @param string $end_date (Optional) .
	 * @return array
	 */
	public function authenticated_user_beers( string $access_token = '', string $user_name = '', int $offset = null, int $limit = 25, string $sort = '', string $start_date = '', string $end_date = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( is_null( $offset ) ) ? null : 'offset'       => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'        => $limit,
			( ! in_array( strtolower( $sort ), array( 'date', 'checkin', 'highest_rated', 'lowest_rated', 'highest_abv', 'lowest_abv' ), true ) ) ? null : 'sort' => strtolower( $sort ),
			( empty( $start_date ) ) ? null : 'start_date' => $start_date,
			( empty( $end_date ) ) ? null : 'end_date'     => $end_date,
		);

		return $this->get( 'user/beers/' . $user_name, $untappd_params );
	}

	/**
	 * This method will allow you to see extended information about a brewery.
	 *
	 * @param int  $brewery_id (Required) The Brewery ID that you want to display checkins.
	 * @param bool $compact (Optional) You can pass "true" here only show the brewery infomation, and remove the "checkins", "media", "beer_list", etc attributes.
	 * @return array
	 */
	public function brewery_info( int $brewery_id, bool $compact = false ) {
		$untappd_params = array(
			( false === $compact ) ? null : 'compact' => 'true',
		);

		return $this->get( 'brewery/info/' . $brewery_id, $untappd_params );
	}

	/**
	 * This method will allow you to see extended information about a beer.
	 *
	 * @param int  $beer_id (Required) The Beer ID that you want to display checkins.
	 * @param bool $compact (Optional) You can pass "true" here only show the beer infomation, and remove the "checkins", "media", "variants", etc attributes.
	 * @return array
	 */
	public function beer_info( int $beer_id, bool $compact = false ) {
		$untappd_params = array(
			( false === $compact ) ? null : 'compact' => 'true',
		);

		return $this->get( 'beer/info/' . $beer_id, $untappd_params );
	}

	/**
	 * This method will allow you to see extended information about a venue.
	 *
	 * @param int  $venue_id (Required) The Venue ID that you want to display checkins.
	 * @param bool $compact (Optional) You can pass "true" here only show the venue infomation, and remove the "checkins", "media", "top_beers", etc attributes.
	 * @return array
	 */
	public function venue_info( int $venue_id, bool $compact = false ) {
		$untappd_params = array(
			( false === $compact ) ? null : 'compact' => 'true',
		);

		return $this->get( 'beer/info/' . $venue_id, $untappd_params );
	}

	/**
	 * This will allow you to search across the Untappd database for beers and breweries.
	 * Note: The best way to search is always "Brewery Name + Beer Name", such as "Dogfish 60 Minute".
	 *
	 * @param string $q (Required) The search term that you want to search.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of results to return, max of 50, default is 25.
	 * @param string $sort (Optional) Your can sort the results using these values: checkin - sorts by checkin count (default), name - sorted by alphabetic beer name.
	 * @return array
	 */
	public function beer_search( string $q, int $offset = null, int $limit = 25, string $sort = '' ) {
		$untappd_params = array(
			'q'                                      => $q,
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
			( ! in_array( strtolower( $sort ), array( 'checkin', 'name' ), true ) ) ? null : 'sort' => strtolower( $sort ),
		);

		return $this->get( 'search/beer', $untappd_params );
	}

	/**
	 * This will allow you to search exclusively for breweries in the Untappd system.
	 *
	 * @param string $q (Required) The search term that you want to search.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of results to return, max of 50, default is 25.
	 * @return array
	 */
	public function brewery_search( string $q, int $offset = null, int $limit = 25 ) {
		$untappd_params = array(
			'q'                                      => $q,
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'search/brewery', $untappd_params );
	}

	/**
	 * This will allow you to check-in to a beer as the authenticated user.
	 * Note: It's possible that you could pass through a foursquare venue ID, and not get back a "venue" object in the response. This can be due to many reasons, but mainly due to a Foursquare connectivity issue. Your app or service should never depending on a one-to-one match on the foursquare ID send, and a venue object returned as part of the response. Always do a null check to make sure that the object's attributes exists before digging.
	 *
	 * @param WC_Untapdd_Checkin $untappd_params (Required).
	 * @return array
	 */
	public function checkin_add( WC_Untapdd_Checkin $untappd_params ) {
		return $this->post( 'checkin/add', $untappd_params );
	}

	/**
	 * This method will allow you to toast a checkin. Please note, if the user has already toasted this check-in, it will delete the toast.
	 * Note: If you want to un-toast a check-in, you call the same method. The resulting response will tell you if the authenticated user has toasted the check-in.
	 *
	 * @param int    $checkin_id (Required) The checkin ID of checkin you want to toast.
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @return array
	 */
	public function toast_untoast_checkin( int $checkin_id, string $access_token = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
		);

		return $this->post( 'checkin/toast/' . $checkin_id, $untappd_params );
	}

	/**
	 * This will allow you to return your pending friends requests. By default, it will return up all results, but you can limit the results via the limit paramater.
	 *
	 * @param string $access_token (Optional) The access token for the acting user.
	 * @param int    $offset (Optional) The numeric offset that you what results to start.
	 * @param int    $limit (Optional) The number of results to return. (default is all).
	 * @return array
	 */
	public function pending_friends( string $access_token = '', int $offset = null, int $limit = null ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( is_null( $offset ) ) ? null : 'offset' => $offset,
			( ! is_int( $limit ) ) ? null : 'limit'  => $limit,
		);

		return $this->get( 'user/pending', $untappd_params );
	}

	/**
	 * This will allow you to request a person to be your friend.
	 *
	 * @param int    $target_id (Required) The target user id that you wish to accept.
	 * @param string $access_token (Optional) The access token for the acting user.
	 *
	 * @return array
	 */
	public function request_friend( int $target_id, string $access_token = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
		);

		return $this->get( 'friend/request/' . $target_id, $untappd_params );
	}

	/**
	 * This will allow you to remove a current friend.
	 *
	 * @param int    $target_id (Required) The target user id that you wish to accept.
	 * @param string $access_token (Optional) The access token for the acting user.
	 *
	 * @return array
	 */
	public function remove_friend( int $target_id, string $access_token = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
		);

		return $this->get( 'friend/remove/' . $target_id, $untappd_params );
	}

	/**
	 * This will allow you to accept a pending friend request.
	 *
	 * @param int    $target_id (Required) The target user id that you wish to accept.
	 * @param string $access_token (Optional) The access token for the acting user.
	 *
	 * @return array
	 */
	public function accept_friend( int $target_id, string $access_token = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
		);

		return $this->get( 'friend/accept/' . $target_id, $untappd_params );
	}

	/**
	 * This will allow you to reject a pending friend request.
	 *
	 * @param int    $target_id (Required) The target user id that you wish to accept.
	 * @param string $access_token (Optional) The access token for the acting user.
	 *
	 * @return array
	 */
	public function reject_friend( int $target_id, string $access_token = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
		);

		return $this->get( 'friend/reject/' . $target_id, $untappd_params );
	}

	/**
	 * This method will allow you comment on a checkin.
	 *
	 * @param int    $checkin_id Required. The checkin ID of the check-in you want ot add the comment.
	 * @param string $comment Required. The text of the comment you want to add. Max of 140 characters.
	 * @param string $access_token (Optional) The access token for the acting user.
	 *
	 * @return array
	 */
	public function add_comment( int $checkin_id, string $comment, string $access_token = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
			( empty( $comment ) ) ? null : 'comment' => $comment,
		);

		return $this->post( 'checkin/addcomment/' . $checkin_id, $untappd_params );
	}

	/**
	 * This method will allow you to delete your comment on a checkin.
	 *
	 * @param int    $comment_id (Required) The comment ID of comment you want to delete.
	 * @param string $access_token (Optional) The access token for the acting user.
	 *
	 * @return array
	 */
	public function delete_comment( int $comment_id, string $access_token = '' ) {
		$untappd_params = array(
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
		);

		return $this->post( 'checkin/deletecomment/' . $comment_id, $untappd_params );
	}

	/**
	 * This method will allow you to add a beer to your wish list.
	 *
	 * @param int    $beer_id (Required) The numeric BID of the beer you want to add your list.
	 * @param string $access_token (Optional) The access token for the acting user.
	 *
	 * @return array
	 */
	public function add_to_wishlist( int $beer_id, string $access_token = '' ) {
		$untappd_params = array(
			'bid' => $beer_id,
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
		);

		return $this->get( 'user/wishlist/add', $untappd_params );
	}

	/**
	 * This method will allow you to remove a beer from your wish list.
	 *
	 * @param int    $beer_id (Required) The numeric BID of the beer you want to remove from your list.
	 * @param string $access_token (Optional) The access token for the acting user.
	 *
	 * @return array
	 */
	public function remove_from_wishlist( int $beer_id, string $access_token = '' ) {
		$untappd_params = array(
			'bid' => $beer_id,
			( empty( $access_token ) ) ? null : 'access_token' => $access_token,
		);

		return $this->get( 'user/wishlist/delete', $untappd_params );
	}

	/**
	 * This method will allow you to pass in a foursquare v2 ID and return a Untappd Venue ID to be used for /v4/venue/info or /v4/venue/checkins.
	 *
	 * @param int $venue_id (Required) The foursquare venue v2 ID that you wish to translate into a Untappd venue ID.
	 *
	 * @return array
	 */
	public function foursquare_lookup( int $venue_id ) {
		return $this->get( 'venue/foursquare_lookup/' . $venue_id );
	}

	/**
	 * This method call's Untappd API endpoint.
	 *
	 * @param string $untappd_method (Required) The method to call.
	 * @param array  $untappd_params (Optional) The parameters to send to Untappd API endpoint.
	 * @param int    $cache_time (Optional) The time the returned data will persist on the cache.
	 *
	 * @return mixed JSON|WC_Untappd_Error
	 */
	private function get( string $untappd_method, array $untappd_params = array(), int $cache_time = null ) {
		if ( empty( $untappd_method ) ) {
			return new WC_Untappd_Error( 400, '_invalid_method', 400 );
		}

		if ( $this->untappt_x_ratelimit_remaining <= 0 ) {
			return new WC_Untappd_Error( 429, '_limit_reached', 429 );
		}

		$untappd_params = wp_parse_args(
			$untappd_params,
			$this->untappd_params
		);

		$cache_key = 'wc_untappd_get_' . hash( 'md5', $untappd_method . wp_json_encode( $untappd_params ) );

		$cache_data = get_transient( $cache_key );

		if ( false !== $cache_data ) {
			return $cache_data;
		}

		$arguments = array(
			'timeout' => 2,
			'headers' => array(
				'User-Agent' => $this->untappd_app_name,
			),
		);

		$response_data = wp_safe_remote_get( $this->untappd_api_url . $untappd_method . '?' . http_build_query( $untappd_params ), $arguments );

		/**
		 * Set API remaining limit.
		 */
		$response_remaining = wp_remote_retrieve_header( $response_data, 'x-ratelimit-remaining' );

		if ( $response_remaining ) {
			$this->untappt_x_ratelimit_remaining = $response_remaining;

			update_option( 'wc_untappd_ratelimit_remaining', $this->untappt_x_ratelimit_remaining );
		}

		$response_code = wp_remote_retrieve_response_code( $response_data );

		if ( 200 !== (int) $response_code ) {
			return new WC_Untappd_Error( $response_code, '_invalid_response_code', $response_code );
		}

		$response_data_body = wp_remote_retrieve_body( $response_data );

		if ( empty( $response_data_body ) ) {
			return new WC_Untappd_Error( $response_code, '_invalid_response_body', $response_code );
		}

		$response_data_body_ary = json_decode( $response_data_body, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! isset( $response_data_body_ary['meta']['code'] ) ) {
			return new WC_Untappd_Error( $response_code, '_invalid_response_json', $response_code );
		}

		if ( 200 !== (int) $response_data_body_ary['meta']['code'] ) {
			return new WC_Untappd_Error( $response_code, '_invalid_response_untappd_code', $response_data_body_ary['meta']['code'] );
		}

		set_transient( $cache_key, $response_data_body_ary, $cache_time );

		return $response_data_body_ary;
	}

	/**
	 * POST as authenticated user.
	 *
	 * @param string $untappd_method The API request.
	 * @param array  $untappd_params The API request params.
	 *
	 * @return json|WC_Untappd_Error
	 */
	private function post( string $untappd_method, array $untappd_params = array() ) {
		if ( empty( $untappd_method ) ) {
			return new WC_Untappd_Error( 400, '_invalid_method', 400 );
		}

		if ( $this->untappt_x_ratelimit_remaining <= 0 ) {
			return new WC_Untappd_Error( 429, '_limit_reached', 429 );
		}

		$arguments = array(
			'timeout' => 5,
			'headers' => array(
				'User-Agent' => $this->untappd_app_name,
			),
			'body'    => $untappd_params,
		);

		$response_data = wp_safe_remote_post( $this->untappd_api_url . $untappd_method . '?' . http_build_query( $untappd_params['access_token'] ), $arguments );

		/**
		 * Set API remaining limit.
		 */
		$response_remaining = wp_remote_retrieve_header( $response_data, 'x-ratelimit-remaining' );

		if ( $response_remaining ) {
			$this->untappt_x_ratelimit_remaining = $response_remaining;

			update_option( 'wc_untappd_ratelimit_remaining', $this->untappt_x_ratelimit_remaining );
		}

		$response_code = wp_remote_retrieve_response_code( $response_data );

		if ( 200 !== (int) $response_code ) {
			return new WC_Untappd_Error( $response_code, '_invalid_response_code', $response_code );
		}

		$response_data_body = wp_remote_retrieve_body( $response_data );

		if ( empty( $response_data_body ) ) {
			return new WC_Untappd_Error( $response_code, '_invalid_response_body', $response_code );
		}

		$response_data_body_ary = json_decode( $response_data_body, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! isset( $response_data_body_ary['meta']['code'] ) ) {
			return new WC_Untappd_Error( $response_code, '_invalid_response_json', $response_code );
		}

		if ( 200 !== (int) $response_data_body_ary['meta']['code'] ) {
			return new WC_Untappd_Error( $response_code, '_invalid_response_untappd_code', $response_data_body_ary['meta']['code'] );
		}

		return $response_data_body_ary;
	}

	/**
	 * Get rate limit calls.
	 *
	 * @return int
	 */
	public function get_limit() : int {
		return absint( $this->untappt_x_ratelimit_remaining );
	}

	/**
	 * Get the time data will last into the cache.
	 *
	 * @return int
	 */
	public function get_cache_time() : int {

		return absint( $this->untappd_cache_time );
	}
}
