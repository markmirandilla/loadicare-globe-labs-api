<?php

require_once('OAuth.php');

class SNSoAuth
{
    
    /* Contains the last HTTP status code returned. */
    public $http_code;
    /* Contains the last API call. */
    public $url;
    /* Set timeout default. */
    public $timeout = 30;
    /* Set connect timeout. */
    public $connecttimeout = 30;
    /* Verify SSL Cert. */
    public $ssl_verifypeer = FALSE;
    /* Respons format. */
    public $format = 'json';
    /* Decode returned json data. */
    public $decode_json = TRUE;
    /* Contains the last HTTP headers returned. */
    public $http_info;
    /* Set the useragnet. */
    public $useragent = 'RECMNOAuth';

/**
* Debug helpers
*/
    function lastStatusCode()
    {
        return $this->http_status;
    }
    function lastAPICall()
    {
        return $this->last_api_call;
    }
    
    function __construct()
    {
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
    }
    /**
* PUT wrapper for oAuthRequest.
*/
    function put($url, $parameters = array())
    {
        
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }
    
    /**
* GET wrapper for oAuthRequest.
*/
    function get($url, $parameters = array())
    {
        
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }
    
    /**
* POST wrapper for oAuthRequest.
*/
    function post($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }
    
    /**
* DELETE wrapper for oAuthReqeust.
*/
    function delete($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }
    
    /**
* Format and sign an OAuth / API request
*/
    function oAuthRequest($url, $method, $parameters)
    {
        if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
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
    
    function http($url, $method, $postfields = NULL)
    {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method);
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
		
        curl_close($ci);
        return $response;
    }
    
    /**
* Get the header info to store.
*/
    function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }
    
    function redirectToURL($url, $parameters = NULL)
    {
        header('Location: '. $this->buildURL($url, $parameters));
    }

    function buildURL($url, $parameters){
        $parameters = ($parameters) ? "?" . http_build_query($parameters) : "";
        return $url . $parameters;
    }

    function authenticate()
    {
        $this->redirectToURL($this->getAuthenticateUrl());
    }

    function appendtoCallback($parameters){
        $parameters = ($parameters) ? "&" . http_build_query($parameters) : "";
        $this->redirect_uri = $this->redirect_uri . $parameters;
    }
    
}
