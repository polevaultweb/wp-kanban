<?php

require_once('oauth.php');

/**
 * Trello OAuth class
 */
class trello_oauth {
	/* Contains the last HTTP status code returned. */
	public $http_code;
	/* Contains the last API call. */
	public $url;
	/* Set up the API root URL. */
	public 	$host = 'https://api.trello.com/1/';
	/* Set timeout default. */
	public $timeout = 30;
	/* Set connect timeout. */
	public $connecttimeout = 30; 
	/* Verify SSL Cert. */
	public $ssl_verifypeer = FALSE;
	/* Respons format. */
	public 	$format = 'json';
	/* Decode returned json data. */
	public $decode_json = TRUE;
	/* Contains the last HTTP headers returned. */
	public $http_info;
	/* Set the useragnet. */
	public $useragent = 'Provider Oauth';
	/* Immediately retry the API call if the response was not successful. */
	//public $retry = TRUE;
	private $request_token_url = 'https://trello.com/1/OAuthGetRequestToken';
	private $access_token_url = 'https://trello.com/1/OAuthGetAccessToken';
	private $authorize_url = 'https://trello.com/1/OAuthAuthorizeToken';
	private $authenticate_token_url = '';
	private $app_name = 'WP Trello';
	
	private $consumer_key = '21b4a1e4f755637319c979849147076e';
	private $consumer_secret = '7d5ab8655e15582041cab97459d1c6d9fca51e690247df4fb5214f2691ba786c';

  /**
   * Set API URLS
   */
  function accessTokenURL()  { return $this->access_token_url; }
  function authenticateURL() { return $this->authorize_url; }
  function authorizeURL()    { return $this->authorize_url; }
  function requestTokenURL() { return $this->request_token_url; }

  /**
   * Debug helpers
   */
  function lastStatusCode() { return $this->http_status; }
  function lastAPICall() { return $this->last_api_call; }

  /**
   * construct Provider oAuth object
   */
  function __construct(	$oauth_token = NULL, 
  						$oauth_token_secret = NULL
  						) {
   	
	$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
    $this->consumer = new OAuthConsumer($this->consumer_key, $this->consumer_secret);
    //print 'oauth_token:' . $oauth_token;
    //print 'oauth_token_secret:' . $oauth_token_secret;
    if (!empty($oauth_token) && !empty($oauth_token_secret)) {
      $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
    } else {
      $this->token = NULL;
    }
  }

  function get_authorise_url($callback = '', $source = '') {
  
  	$request_token = $this->getRequestToken($callback);

	$_SESSION[$source .'_oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION[$source .'_oauth_token_secret'] = $request_token['oauth_token_secret'];

	$url = '#';
	switch ($this->http_code) {
	  case 200:
	    $url = $this->getAuthorizeURL($token);
	    return $url;
	    break;
	}

  }
  /**
   * Get a request_token from Provider
   *
   * @returns a key/value array containing oauth_token and oauth_token_secret
   */
  function getRequestToken($oauth_callback = NULL) {
    $parameters = array();
    if (!empty($oauth_callback)) {
      $parameters['oauth_callback'] = $oauth_callback;
    }  
    $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
    $token = OAuthUtil::parse_parameters($request);
    $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }

  /**
   * Get the authorize URL
   *
   * @returns a string
   */
  function getAuthorizeURL($token, $sign_in_with_twitter = TRUE) {
    if (is_array($token)) {
      $token = $token['oauth_token'];
    }
    $app_name = isset($this->app_name) ? '&name='. urlencode($this->app_name) : '';
    if (empty($sign_in_with_twitter)) {
      return $this->authorizeURL() . "?oauth_token={$token}&expiration=never". $app_name;
    } else {
       return $this->authenticateURL() . "?oauth_token={$token}&expiration=never". $app_name;
    }
  }

  /**
   * Exchange request token and secret for an access token and
   * secret, to sign API calls.
   *
   */
  function getAccessToken($oauth_verifier = FALSE, $return_uri = NULL) {
    $parameters = array();
    if (!empty($oauth_verifier)) {
      $parameters['oauth_verifier'] = $oauth_verifier;
    }
    $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
    $token = OAuthUtil::parse_parameters($request);
    $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }

  /**
   * One time exchange of username and password for access token and secret.
   *
   */  
  function getXAuthToken($username, $password) {
    $parameters = array();
    $parameters['x_auth_username'] = $username;
    $parameters['x_auth_password'] = $password;
    $parameters['x_auth_mode'] = 'client_auth';
    $request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
    $token = OAuthUtil::parse_parameters($request);
    $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }

  /**
   * GET wrapper for oAuthRequest.
   */
  function get($url, $parameters = array()) {
	$response = $this->oAuthRequest($url, 'GET', $parameters);  
    if ($this->format === 'json' && $this->decode_json) {
      return json_decode($response);
    }
    return $response;
  }
  
  /**
   * POST wrapper for oAuthRequest.
   */
  function post($url, $parameters = array()) {
    $response = $this->oAuthRequest($url, 'POST', $parameters);
    if ($this->format === 'json' && $this->decode_json) {
      return json_decode($response);
    }
    return $response;
  }

  /**
   * DELETE wrapper for oAuthReqeust.
   */
  function delete($url, $parameters = array()) {
    $response = $this->oAuthRequest($url, 'DELETE', $parameters);
    if ($this->format === 'json' && $this->decode_json) {
      return json_decode($response);
    }
    return $response;
  }

  function getFormat($url) { return "{$this->host}{$url}"; }
  
  /**
   * Format and sign an OAuth / API request
   */
  function oAuthRequest($url, $method, $parameters) {
    if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
      $url = $this->getFormat($url);
    }
    $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
    $request->sign_request($this->sha1_method, $this->consumer, $this->token);
    switch ($method) {
    case 'GET':
      return $this->http($request->to_url(), 'GET');
    default:
      return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
    }
  }
  
  
  /**
   * Format and sign an OAuth / API request
   */
  function oAuthRequest2($url, $method, $parameters) {
    if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
      $url = $this->getFormat($url);
    }
    $defaults = array();
    $token = $this->token;
    $defaults['access_token'] = $token->key;
    $parameters = array_merge($defaults, $parameters);
    $request = new OAuthRequest($method, $url, $parameters);
    switch ($method) {
    case 'GET':
      return $this->http($request->to_url(), 'GET');
    default:
      return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
    }
  }


  /**
   * Make an HTTP request
   *
   * @return API results
   */
  function http($url, $method, $postfields = NULL) {
    $this->http_info = array();
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
    curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
    curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
    curl_setopt($ci, CURLOPT_HEADER, FALSE);

    switch ($method) {
      case 'POST':
        curl_setopt($ci, CURLOPT_POST, TRUE);
        if (!empty($postfields)) {
          curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
        }
        break;
      case 'DELETE':
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if (!empty($postfields)) {
          $url = "{$url}?{$postfields}";
        }
    }
    curl_setopt($ci, CURLOPT_URL, $url);
    $response = curl_exec($ci);
    $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
    $this->url = $url;
    curl_close ($ci);
    return $response;
  }

  /**
   * Get the header info to store.
   */
  function getHeader($ch, $header) {
    $i = strpos($header, ':');
    if (!empty($i)) {
      $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
      $value = trim(substr($header, $i + 2));
      $this->http_header[$key] = $value;
    }
    return strlen($header);
  }
  
  /**
   * filter text
   */	
   function filter_text($text) { return trim(filter_var($text, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW)); }
   
   public function show_details() { return $this->settings; }
   
   function getMember() {
		$params = array();
		$member = $this->get('members/my/', $params, 0);
		return $member;
	}
	
   function getOrganizations($id = '') {
		$params = array();
		$all_orgs = $this->get('members/my/organizations/', $params, 0);
		return $all_orgs;
	}
	
	function getOrganization($id) {
		$params = array();
		$org = $this->get('organizations/'. $id, $params, 0);
		return $org;
	}

	function getBoards($org) {
		$params = array();
		if ($org != '1') {
			$url = 'organizations/'. $org. '/boards/';
		} else {
			$url = 'members/my/boards/';
			$params = array('filter' => 'members');
		}
		
		$all_boards = $this->get($url, $params, 0);
		return $all_boards;
	}
	
	function getBoard($id) {
		$params = array();
		$board = $this->get('boards/'. $id, $params, 0);
		return $board;
	}
	
	function getLists($board) {
		$params = array();
		$all_lists = $this->get('boards/'. $board .'/lists', $params, 0);
		return $all_lists;
	}
	
	function getList($id) {
		$params = array();
		$list = $this->get('lists/'. $id, $params, 0);
		return $list;
	}
	
	function getCards($list) {
		$params = array();
		$all_cards = $this->get('lists/'. $list .'/cards/', $params, 0);
		return $all_cards;
	}
	
	function getCard($id) {
		$params = array();
		$card = $this->get('cards/'. $id, $params, 0);
		return $card;
	}
	
	function getDropdown($data, $object) {
		$select[0] = 'Select '. ucfirst($object);
		if ($object == 'organization') $select[1] = 'My Boards';
		foreach($data as $item) $select[$item->id] = isset($item->displayName) ? $item->displayName : $item->name;
		return $select;
	}

}
