<?php

// $Date: 2018-04-19 10:44 +0200 $
// $Id: panthermedia-http.php 68:b71dec5a2905 2018-04-19 10:44 +0200 steffen $
// $Revision: 68:b71dec5a2905 $
// $Lastlog: remove unused method and add more attempts for requests $

/*
PantherMedia Stock Photo Plugin for WordPress
Copyright (C) 2017  PantherMedia GmbH

This file is part of PantherMedia Stock Photo Plugin for WordPress.

PantherMedia Stock Photo Plugin for WordPress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or any later version.

PantherMedia Stock Photo Plugin for WordPress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with PantherMedia Stock Photo Plugin for WordPress.  If not, see <http://www.gnu.org/licenses/>.

Contributor(s): PantherMedia GmbH (http://www.panthermedia.net), Steffen Blaszkowski
*/

class Exception_PantherMedia_HTTP extends Exception {};

class PantherMedia_HTTP {
   const REST_URI = 'rest.panthermedia.net';
   const REST_VERSION = '1.0';
   
   private $m_uri = null;
   private $m_api_key = null;
   
   private $m_api_secret = null;
   private $m_user_agent = null;
   
   private $m_auth_token = null;
   private $m_token_secret = null;
   
   public $m_timeout_connect = 3000;
   public $m_algo = 'sha1';
   
   
   private $m_response = null;
   private $m_response_body = null;
   private $m_response_header = null;
   
   private $m_ssl = false;

   private $m_try_requests = 1;
   private $m_wait_ms = 100000;
//----------------------------------------------------------------------------------------------------------------------
   function __construct($api_key, $api_secret){
      $this->m_api_key = $api_key;
      $this->m_api_secret = $api_secret;
      $this->m_user_agent = "EMPTY USERAGENT";
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * create a access-token/key from api-key / auth-token
    * 
    * @param string $token
    * @param string $secret
    * @param string $timestamp
    * @param string $nonce
    * @return string
    */
   function createToken($token, $secret, &$timestamp=null, &$nonce=null){
      if($timestamp === null){
         $timestamp = str_replace('+0000', 'UTC', gmdate(DATE_RSS, time()));
      }
      if($nonce === null){
         $nonce = rand(900000, 999999);
      }
      $data = $timestamp . $token . $nonce;
      return hash_hmac($this->m_algo, $data, $secret, false);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * 
    * @param string  $method
    * @param array $post_data
    * @param string $version
    * @param bool $ssl 
    * @return array
    * @throws Exception_PantherMedia_HTTP
    */
   function request($method, $post_data, $version=null, $ssl=false){
      $timestamp = null;
      $nonce = null;
      
      $post_data['api_key'] = $this->m_api_key;
      $post_data['access_key'] = $this->createToken($this->m_api_key, $this->m_api_secret, $timestamp, $nonce);
      $post_data['timestamp'] = $timestamp;
      $post_data['nonce'] = $nonce;
      $post_data['algo'] = $this->m_algo;
      
      if($this->m_auth_token !== null && $this->m_token_secret !== null){
         $post_data['auth_token'] = $this->m_auth_token;
         $post_data['access_token'] = $this->createToken($this->m_auth_token, $this->m_token_secret, $timestamp, $nonce);
      }
      
      if($version === null){
         $version = PantherMedia_HTTP::REST_VERSION;
      }
      
      if($this->m_uri === null){
         $this->m_uri = PantherMedia_HTTP::REST_URI;
      }
      
      if(defined('PM_REST_URI')){
         $this->m_uri = PM_REST_URI;
      }
      
      $url_file = ($ssl?'https://':'http://').$this->m_uri.'/'.$method;
      
      $headers = array(
         'Connection' => 'Keep-Alive',
         'Keep-Alive' => 300,
         'Accept-Version' => $version,
      );
      
      $args = array(
          'body' => $post_data,
          'headers' => $headers,
          'cookies' => array(),
          'method' => 'POST',
          'user-agent' => $this->m_user_agent,
          'timeout' => $this->m_timeout_connect,
          'redirection' => '5',
          'httpversion' => '1.0',
          'blocking' => true,
          'sslverify' => false,
      );

      for($i=1;$i<=$this->m_try_requests;$i++) {
         $return = wp_remote_post( $url_file, $args );
         PantherMediaStockPhoto_writeDebug($url_file . ' ' . http_build_query($post_data), 'rest');
         if(isset($return['body']) && isset($return['response'])) {
            PantherMediaStockPhoto_writeDebug($return['body'], 'rest');
            PantherMediaStockPhoto_writeDebug($return['response'], 'rest');
         } else {
            PantherMediaStockPhoto_writeDebug($return, 'rest');
         }
         
         if ( is_wp_error( $return ) ) {
            if($i < $this->m_try_requests) {
               usleep($this->m_wait_ms);
               continue;
            }
            throw new Exception_PantherMedia_HTTP($return->get_error_message(), -99);
         }

         $body = isset($return['body']) ? $return['body'] : '';
         $this->m_response = $return['response'];
         $this->m_response_body = $body;
         $this->m_response_header = $return['headers'];

         if($post_data['content_type'] === 'application/json'){ // JSON
            try{
               $body = json_decode($body, true);

               if(!isset($body['stat'])) {
                  if($i < $this->m_try_requests) {
                     usleep($this->m_wait_ms);
                     continue;
                  }
                  throw new Exception_PantherMedia_HTTP('JSON Error: can\'t decode response', 510);
               }
            }catch(Exception $e){
               if($i < $this->m_try_requests) {
                  usleep($this->m_wait_ms);
                  continue;
               }
               throw new Exception_PantherMedia_HTTP('JSON Error: can\'t decode response', $e->getCode());
            }
         }
      }
      
      if(isset($this->m_response['code']) && $this->m_response['code'] != '200'){
         $body_msg = (isset($body['err']['msg'])) ? "\n".$body['err']['msg'] : '';
         $body_internal = (isset($body['internal'])) ? "\n\nInternal:\n".$body['internal'] : '';
         
         PantherMediaStockPhoto_writeDebug($return['body'], 'rest_error');
         if(isset($this->m_response['message'])){
            throw new Exception_PantherMedia_HTTP($this->m_response['message'].$body_msg.$body_internal, $this->m_response['code']);
         }
         throw new Exception_PantherMedia_HTTP('HTTP Error', $this->m_response['code']);
      }
      
      return $body;
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * setter function
    */
//----------------------------------------------------------------------------------------------------------------------
   /**
    * only used to debug with other URI (for SDK developers)
    */
   function setURI($uri){
      $this->m_uri = $uri;
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * set USER Agent
    * @param string $agent
    */
   function set_user_agent($agent) {
      $this->m_user_agent = $agent;
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * set the User authentication, to reset - set both to null
    * 
    * @param string $auth_token
    * @param string $token_secret
    */
   function setUser_Authentication($auth_token, $token_secret){
      $this->m_auth_token = (!empty($auth_token) ? $auth_token : null);
      $this->m_token_secret = (!empty($token_secret) ? $token_secret : null);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * set ssl
    * 
    * @param bool $ssl
    */
   function setSSL($ssl){
      $this->m_ssl = (bool)$ssl;
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * getter function
    */
//----------------------------------------------------------------------------------------------------------------------
   /**
    * return REST Version
    * @return string
    */
   function get_rest_version() {
      return self::REST_VERSION;
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * 
    * @return array like array('token'=>'a3ce21', 'secret'=>'12345');
    */
   function getUser_Authentication(){
      if($this->m_auth_token !== null && $this->m_token_secret !== null){
         return array('token'=>$this->m_auth_token, 'secret'=>$this->m_token_secret);
      }else{
         return false;
      }   
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * 
    * @return array like array('code'=>200, 'message'=>'Ok');
    */
   function getResponseCode(){
      return $this->m_response;
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * request functions
    */
//----------------------------------------------------------------------------------------------------------------------
   /**
    * request a authentication token for a user login to authorize your app to access user data
    * 
    * @param string $token_secret
    * @param string $app_name
    * @param string $app_logo
    * @param string $app_callback
    * @param string $additional_device_info
    * @return array
    * @throws Exception_PantherMedia_HTTP
    */
   function request_token($token_secret, $app_name, $app_logo=null, $app_callback=null, $additional_device_info=null, $lang='en'){
      $post_data = array('content_type'=>'application/json');
      $post_data['token_secret'] = $token_secret;
      $post_data['app_name'] = $app_name.($additional_device_info!==null?' - '.$additional_device_info:'');
      $post_data['app_name'] = urlencode($post_data['app_name']);
      if($app_logo !== null) { $post_data['app_logo'] = $app_logo; }
      if($app_callback !== null) { $post_data['app_callback'] = $app_callback; }
      $post_data['lang'] = $lang;
      
      return $this->request('request-token', $post_data, null, true);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * set the validation of a token<br><br>Sample for $valid_until:<br>$ts = time() + (7 * 24 * 60 * 60);// one week<br>$valid_until = str_replace('+0000', 'UTC', gmdate(DATE_RSS, $ts));
    * 
    * @param string $valid_until as UTC formated DateTime
    */
   function token_valid_until($valid_until){
      $post_data = array('content_type'=>'application/json');
      $post_data['valid_until'] = $valid_until;
      
      return $this->request('token-valid-until', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------   
   /**
    * return infos about the API Host
    * 
    * @return array
    * @throws Exception_PantherMedia_HTTP
    */
   function host_info(){
      $post_data = array('content_type'=>'application/json');
      return $this->request('host-info', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * search on PantherMedia Database, for full search capabilities see: http://rest.panthermedia.net
    * 
    * @param string $q
    * @param string $lang
    * @param int $page
    * @param int $limit
    * @param string $extra_info
    * @param string $filters
    * @return array
    * @throws Exception_PantherMedia_HTTP
    */
   function search($q='',$lang='en',$page=0, $limit=40, $extra_info=null, $filters=null){
      $post_data = array('content_type'=>'application/json');
      $post_data['q'] = $q;
      $post_data['lang'] = $lang;
      $post_data['page'] = $page;
      $post_data['limit'] = $limit;
      if($extra_info !== null) { $post_data['extra_info'] = $extra_info; }
      if($filters !== null) { $post_data['filters'] = $filters; }
      
      return $this->request('search', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * get detail infos (metadata) and possible articels/pricing
    * 
    * @param string $id_media
    * @param string $lang
    * @param bool $show_articles
    * @param bool $show_top10_keywords
    * @return array
    * @throws Exception_PantherMedia_HTTP
    */
   function get_media_info($id_media, $lang='en', $show_articles=false, $show_top10_keywords=false){
      $post_data = array('content_type'=>'application/json');
      $post_data['id_media'] = $id_media;
      $post_data['lang'] = $lang;
      if($show_articles) { $post_data['show_articles'] = 'yes'; }
      if($show_top10_keywords) { $post_data['show_top10_keywords'] = 'yes'; }
      
      return $this->request('get-media-info', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * download a preview image file
    * 
    * @param string $id_media
    * @return string as binary
    * @throws Exception_PantherMedia_HTTP
    */
   function download_image_preview($id_media){
      $post_data = array('content_type'=>'application/octet-stream, application/json');
      $post_data['id_media'] = $id_media;
      
      return $this->request('download-image-preview', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * 
    * @param string $id_media
    * @param string $id_article
    * @param string $lang
    * @param string $metadata as sample all, iptc, xmp, none
    * @param int $id_download
    * @param bool $test
    * @return string as binary
    */
   function download_image($id_media, $id_article, $lang='en', $metadata='all', $id_download=null, $test=false){
      $post_data = array('content_type'=>'application/octet-stream, application/json');
      $post_data['id_media'] = $id_media;
      $post_data['id_article'] = $id_article;
      $post_data['lang'] = $lang;
      $post_data['metadata'] = $metadata;
      if($id_download !== null) { $post_data['id_download'] = $id_download; }
      if($test) { $post_data['test'] = 'yes'; }
      
      return $this->request('download-image', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------
   function get_search_filter(){
      $post_data = array('content_type'=>'application/json');
      return $this->request('get-search-filter', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------
  /**
    * returns all downloaded images 
    * 
    * @param string $lang
    * @param int $offset
    * @param int $limit
    * @param string $extra_info    
    * @return array
    * @throws Exception_PantherMedia_HTTP
    */
   function get_downloaded_images($lang='en',$offset=0, $limit=40, $extra_info=null){
      $post_data = array('content_type'=>'application/json');
      $post_data['lang'] = $lang;
      $post_data['offset'] = $offset;
      $post_data['limit'] = $limit;
      if($extra_info !== null) { $post_data['extra_info'] = $extra_info; }
      
      return $this->request('get-downloaded-images', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * returns the available deposit of an account and the URL where to buy new deposit
    * 
    * @param string $lang
    * @return array
    * @throws Exception_PantherMedia_HTTP
    */
   function get_user_deposit($lang='en'){
      $post_data = array('content_type'=>'application/json');
      $post_data['lang'] = $lang;
      
      return $this->request('get-user-deposit', $post_data, null, $this->m_ssl);
   }
//----------------------------------------------------------------------------------------------------------------------
   /**
    * 
    * @return array
    * @throws Exception_PantherMedia_HTTP
    */
   function get_user_profile(){
      if(!$this->getUser_Authentication()) {
         return false;
      }
      $post_data = array('content_type'=>'application/json');
      return $this->request('get-user-profile', $post_data, null, $this->m_ssl);
   }
}
