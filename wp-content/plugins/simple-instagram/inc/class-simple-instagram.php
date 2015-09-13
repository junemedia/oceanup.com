<?php

/**
 * Simple Instagram Class
 *
 * Handles Instagram API Calls
 *
 * @package simple-instagram
 */
class Simple_Instagram {

    private $api_url = 'https://api.instagram.com/v1',
            $access_token;

    function __construct() {

        $this->access_token = get_option( 'si_access_token' );

    }

    /**
     * Get User - Retrieves a User's profile
     *
     * @return str - A stdClass obj containing User Data
     */
    public function get_user() {

        $url = $this->api_url . '/users/self?access_token=' . $this->access_token;

        return $this->make_call( $url );

    }

    /**
     * Get Own Feed - Retrieves a User's own Feed
     *
     * @param int $count - The number of Items to return
     * @return obj - A stdClass obj containing User Feed
     */
    public function get_own_feed( $count = 6 ) {

        $url = $this->api_url . '/users/self/feed?access_token=' . $this->access_token . '&count=' . $count;

        return $this->make_call( $url );

    }

    /**
     * Get User Media - Retrieves a set of User's Media
     *
     * @param str $user - The user whose feed to retrieve
     * @param int $count - The number of Items to return
     * @return obj - A stdClass obj containing the Media
     */
    public function get_user_media( $user, $count = 6 ) {

        $url = $this->api_url . '/users/' . $user . '/media/recent?access_token=' . $this->access_token . '&count=' . $count;

        return $this->make_call( $url );

    }

    /**
     * Get Tagged Media - Retrieve a set of Media by Tag
     *
     * @param str $tag - The tag to search by
     * @param int $count - The number of Items to return
     * @return obj - A stdClass obj containing the Media
     */
    public function get_tagged_media( $tag, $count = 6 ) {

        $url = $this->api_url . '/tags/' . $tag . '/media/recent?access_token=' . $this->access_token . '&count=' . $count;

        return $this->make_call( $url );

    }

    /**
     * Get Popular Media - Retrieve a set of Popular Media
     *
     * @param int $count - The number of Items to return
     * @return obj - A stdClass obj containing the Media
     */
    public function get_popular_media( $count = 6 ) {

        $url = $this->api_url . '/media/popular?access_token=' . $this->access_token . '&count=' . $count;

        return $this->make_call( $url );

    }

    /**
     * Get User ID - Retrieve the User ID by username
     *
     * @param str $username - The username to search by
     * @return obj - A stdClass obj containing the users
     */
    public function get_user_id( $username ) {

        $url = $this->api_url . '/users/search?q=' . $username. '&access_token=' . $this->access_token;
        return $this->make_call( $url );

    }

    /**
     * Make Call - Perform the remote request
     *
     * @param str $url - The URL to hit
     * @return obj - A stdClass obj containing the data
     */
    private function make_call( $url ) {

        $request = wp_remote_get( $url );
        $data = $this->parse_data( $request['body'] );

        return $data;

    }

    /**
     * Parse Data - Return the Data portion of the provided
     * JSON string from Instagram
     *
     * @param str $response - The JSON response from Instagram
     * @return obj - A stdClass obj containing the Data
     */
    private function parse_data( $response ) {

        $return = json_decode( $response );
        if ( ! $return || is_null( $return ) || $return->meta->code == 400 ) {
            return false;
        }

        $data = $return->data;

        return $data;

    }

}