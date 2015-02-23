<?php

/*
 *  ====== The Guardian Content API Wordpress Class ======
 *
 *  This is a custom class for the Guardians Open Platform API.
 *
 *  To find out more about this powerful API and its creative uses
 *  then please visit http://www.guardian.co.uk/open-platform
 *
 *  Kind Regards,
 *  Daniel Levitt
 *  daniel.levitt@guardian.co.uk
 *  Guardian News & Media Ltd
 *
 */

class GuardianOpenPlatformAPI {

    /**
     * This is the endpoint of the Content API
     * @var str
     */
    public static $GUARDIAN_API_ENDPOINT = "http://content.guardianapis.com";

    /**
     * Sets the Max Keywords because searching the API for too many terms will return an empty call
     * @var int
     */
    public static $GUARDIAN_API_MAX_KEYWORDS = 4;

    /**
     * Returns the keyword value above
     */
    public function guardian_api_max_keywordsValue() {
    	return self::$GUARDIAN_API_MAX_KEYWORDS;
    }

    /**
     * Sets the API Keyname so it can be changed just here and used throughout the application
     * e.g in reference to a database field
     * @var str
     */
    public static $GUARDIAN_API_KEYNAME = "guardian_api_key";

    /**
     * Returns the api keyname value above
     */
    public function guardian_api_keynameValue() {
    	return self::$GUARDIAN_API_KEYNAME;
    }

    /**
     * This is a url used to get the user tier status. This is a quick call returning one token result.
     * @var str
     */
    public static $GUARDIAN_API_TIER_STATUS = "/search?format=json&page-size=1";

    /**
     * This is the search url to be used with a Sprintf function, returns the headline
     * standfirst and thumbnail for browsing/searching.
     *
     * Page number is required.
     *
     * @var str
     */
    public static $GUARDIAN_API_SEARCH_URL = "/search?";

    /**
     * This is the sections url to be used with a Sprintf function
     */
    public static $GUARDIAN_API_SECTIONS_URL = "/sections?";

    /**
     * The url for accessing just one article. Returns all available fields and tags.
     * API Key required for this url.
     * @var str
     */
    public static $GUARDIAN_API_ITEM_URL = "/%s?show-fields=all&show-tags=all&format=json";

    /**
     * @var String $apikey Your Guardian API Key which is required to make calls to premium Guardian Content.
     */
    public $apikey;

    public function __construct($apikey = "") {
        $this->apikey = $apikey;
    }

    /**
     * Returns status of the user as a string for the supplied API key.
     */
    public function guardian_get_tier() {

        $str_api_url = self::$GUARDIAN_API_ENDPOINT . self::$GUARDIAN_API_TIER_STATUS;

        if (!empty($this->apikey)) {
		    $str_api_url .= "&api-key=".$this->apikey;
    	}

    	$arr_api_result = $this->convertJson( $str_api_url );

      if ($arr_api_result ['response']['status'] !== 'ok') {
        return false;
      }
    	$str_tier = ucfirst(strtolower($arr_api_result ['response']['userTier']));
    	return $str_tier;
    }

	/**
     * Calls the Guardian API passing the ID of the article you need
     *
     * @param $str_item_id				String of the ID
     */
    public function guardian_api_item($str_item_id) {

        $str_api_url = self::$GUARDIAN_API_ENDPOINT . self::$GUARDIAN_API_ITEM_URL;

        if (!empty($this->apikey)) {

        	$str_api_url .= "&api-key=".$this->apikey;

        	// If the user is in the Partner tier let them ask for media, otherwise the api call will error.
		    $tier = self::guardian_get_tier();
		    if ($tier == 'Partner') {
		    	$str_api_url .= "&show-media=all";
		    }

		    $str_api_url = sprintf( $str_api_url, $str_item_id );

		    $arr_api_result = $this->convertJson( $str_api_url );
    	    return $arr_api_result ['response']['content'];
    	}
    	return null;
    }

    /**
     * Calls the Guardian API passing the supplied array of keywords
     *
     * @param $arr_keywords		Array of keywords to search for.
     * @param $page				Integer of page number.
     */
    public function guardian_api_search( $options = null, $random = false ) {

    	// Validate keywords - ( url encoding, keyword limits, etc )
      if($options['q']) {
      	$options['q'] = $this->guardian_valdiate_api_search_terms( $options['q'], $random );
      } else {
        unset($options['q']);
      }

    	// Set the default encoding to be JSON.
    	$options['format'] = 'json';

    	$str_api_url = self::$GUARDIAN_API_ENDPOINT . self::$GUARDIAN_API_SEARCH_URL;
    	if (!empty($this->apikey)) {
		    $str_api_url .= "&api-key=".$this->apikey;
    	}

    	foreach ( $options as $key => $value ) {
    		$str_api_url .= "&".$key."=".$value;
    	}

    	$arr_api_result = $this->convertJson( $str_api_url );
    	return $arr_api_result ['response'];
    }

    /**
     * Calls the Guardian API section search
     *
     */
    public function guardian_api_sections( ) {

      // Set the default encoding to be JSON.
      $options['format'] = 'json';

      $str_api_url = self::$GUARDIAN_API_ENDPOINT . self::$GUARDIAN_API_SECTIONS_URL;
      if (!empty($this->apikey)) {
        $str_api_url .= "&api-key=".$this->apikey;
      }

      foreach ( $options as $key => $value ) {
        $str_api_url .= "&".$key."=".$value;
      }

      $arr_api_result = $this->convertJson( $str_api_url );
      return $arr_api_result ['response'];
    }

    /**
     * Function to make sure there are not too many keywords plus url_encode an array into a string
     *
     * @param $arr_related_keywords 	A one dimensional array of assorted keywords
     */
    public function guardian_valdiate_api_search_terms($arr_related_keywords, $random = false) {

    	if ($random == true) {
	    	$num_keywords = count ( $arr_related_keywords );
	    	if ($num_keywords > self::$GUARDIAN_API_MAX_KEYWORDS) {
	    		$arr_related_keywords = array_slice($arr_related_keywords, 0, self::$GUARDIAN_API_MAX_KEYWORDS);
	    	}
    	}
    	if (is_array($arr_related_keywords)) {
    		$arr_related_keywords = implode($arr_related_keywords, ' ');
    	}
    	return urlencode($arr_related_keywords);
    }

    /**
     * Function that takes the JSON encoding and turns is into an associative array
     *
     * @param $str_api_url 		A string to grab and convert
     */
    public function convertJson ($str_api_url) {
    	$data = wp_remote_retrieve_body( wp_remote_get($str_api_url) );
		  return ( json_decode($data, true) );
    }

}

?>
