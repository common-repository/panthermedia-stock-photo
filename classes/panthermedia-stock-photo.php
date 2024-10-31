<?php

// $Author: Steffen Blaszkowski $
// $Date: 2018-07-12 12:16 +0200 $
// $Id: panthermedia-stock-photo.php 74:0e887dd85ef1 2018-07-12 12:16 +0200 steffen $
// $Revision: 74:0e887dd85ef1 $
// $Lastlog: small fix without extended rights $

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

defined('ABSPATH') or die("No script kiddies please!");

if(!class_exists('PantherMedia_HTTP')) {
    require_once 'panthermedia-http.php';
}


class PantherMediaStockPhoto {

   /**
    * API key for this plugin
    * @var string
    */
   const API_key = '9e86b828a9bf908245acb87a2376346c83c3071d528f02938452f7fed782ec87';

   /**
    * API secret for this plugin
    * @var string
    */
   private $API_secret = 'd1e8595bde5c81d3f334a2caee5e1ace';

   /**
    * localization domain
    * @var string
    */
   const domain = "panthermedia-stock-photo";

   /**
    * Our Name
    * @var string
    */
   const name = "PantherMedia";

   /**
    * Version of this plugin
    * @var string
    */
   private $version;

   /**
    * URL from plugin
    * Example: http://YOURSITE.com/wp-content/plugins/panthermedia-stock-photo
    * @var string
    */
   protected $_url;

   /**
    * Path to this plugin
    * Example: /var/www/wordpress/wp-content/plugins/panthermedia-stock-photo
    * @var string 
    */
   protected $_path;

   /**
    * Path to this file
    * Example: /var/www/wordpress/wp-content/plugins/panthermedia-stock-photo/panthermedia-stock-photo.php
    * @var string 
    */
   protected $_file;

   /**
    * Config
    * @var array
    */
   public $config;

   /**
    * REST-Client
    * @var PantherMedia_HTTP 
    */
   private $api;
   
   /**
    * Status from API Request
    * @var array 
    */
   private $status;
   
   /** 
    * Max age of search-filter file
    * @var int 
    */
   private $ageSearchFilter;
   
   /** 
    * Max age of cachefile lang
    * @var int 
    */
   private $ageCachefileLang; // 24 hours
   
   /** 
    * Max age of cachefiles for mediainfo
    * @var int 
    */
   private $mediaInfoCache; // 30 min
   
   /** 
    * Items per Page
    * @var int 
    */
   private $perPage;
   
   /**
    * Language for REST
    * @var string
    */
   private $lang = 'en';
   
   /**
    * Links to PantherMedia
    * @var array 
    */
   public $pmlinks = array();
   
   /**
    * Deactivate seachfilter
    * @var array
    */
   public $deactivate_searchfilters = array();
   
   /**
    * rest testmodus
    * @var boolean
    */
   public $option_testmodus;

   /**
    * api user profile
    * @var array
    */
   private $m_api_userProfile;

   /**
    * 
    * @var boolean
    */
   private $m_show_process = false;

//------------------------------------------------------------------------------------------------------
   
   /**
    * Initialize
    * @param string $file 
    * @return void
    */
   public function __construct($file = false) {
      try {
         $this->_url = plugins_url('', $file);
         $this->_path = dirname($file);
         $this->_file = $file;

         // settings
         $this->config();

         // load domain language on plugins_loaded action
         add_action('plugins_loaded', array(&$this, 'plugins_loaded'));

         // stuff in the admin backend
         if (is_admin()) {
            // load style and scripts
            add_action('admin_enqueue_scripts', array(&$this, 'load_style_and_scripts'));

            // API
            $this->api = new PantherMedia_HTTP(self::API_key, $this->API_secret);
            $this->api->setSSL($this->is_https());
            $this->api_setUserAgent();

            // set API lang
            $lang = substr(get_bloginfo('language'), 0, 2);
            $this->lang = (in_array($lang, $this->api_getLang()) ? $lang : 'en');
            $this->config_pmlinks();

            // load search-filter
            $this->searchFilter();

            // admin menu
            add_action('admin_menu', array(&$this, 'admin_menu'));

            // Form Admin-Settings
            //add_action( 'admin_post_pm_setting_update', array($this, 'post_admin_options_update' ) );
            add_action('wp_ajax_' . self::domain, array(&$this, 'ajax_admin'));

            // Media-tab
            add_action('init', array(&$this, 'check_capability'));
         }

         // on activation/uninstallation hooks
         register_activation_hook($this->_file, array(&$this, 'plugin_activation'));
         register_deactivation_hook($this->_file, array(&$this, 'plugin_deactivation'));
         register_uninstall_hook($this->_file, 'plugin_uninstall');
      } catch (Exception $e) {
         PantherMediaStockPhoto_debug($e, __METHOD__);
      }
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * On activation
    * @global type $wp_roles
    * @return void
    */
   public function plugin_activation() {
      // save default settings
      add_option('plugin_' . self::domain . '_settings', $this->config);

      // add capability for this plugin
      $wp_roles = new WP_Roles;
      $wp_roles->add_cap('administrator', 'plugin_' . self::domain);
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * On deactivation
    * @return void
    */
   public function plugin_deactivation() {
      $option1 = 'plugin_' . self::domain . '_api_connection';
      $option2 = 'plugin_' . self::domain . '_api_search';
      
      // delete general options
      delete_option('plugin_'.self::domain.'_settings');
      
      // delete login options
      delete_option($option1);
      
      // delete user logins
      $wpdb = $GLOBALS['wpdb'];
      $wpdb->delete( $GLOBALS["table_prefix"].'usermeta', array('meta_key' => $GLOBALS["table_prefix"].$option1));
      
      // delete user search
      $wpdb->delete( $GLOBALS["table_prefix"].'usermeta', array('meta_key' => $GLOBALS["table_prefix"].$option2));
      
      // delete Cache folder
      $cache = $this->_path.'/cache';
      if(is_dir($cache)) {
         $this->delTree($cache);
      }
      
      // delete Logs folder
      $logs = $this->_path.'/logs';
      if(is_dir($logs)) {
         $this->delTree($logs);
      }
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Uninstall PantherMedia Stock Photo
    * @return void
    */
   static function plugin_uninstall() {
      $wp_roles = $GLOBALS['wp_roles'];
      
      // delete DB entrys and cache files
      $this->ajax_admin_saveSettings_setDefault();

      // delete capabilities 
      $wp_roles->remove_cap('administrator', 'plugin_' . self::domain);
      $wp_roles->remove_cap('editor', 'plugin_' . self::domain);
      $wp_roles->remove_cap('author', 'plugin_' . self::domain);
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * load language files
    * @return void
    */
   public function plugins_loaded() {
      try {
         load_plugin_textdomain(self::domain, false, self::domain . '/languages/plugin/');
         load_plugin_textdomain(self::domain.'-search', false, self::domain . '/languages/search/');
      }catch(Exception $e) {
         PantherMediaStockPhoto_debug($e, __METHOD__);
      }
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * loading config methods
    * @return void
    */
   private function config() {
      $this->config_default();
      $this->config_plugin();
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * loading plugin config
    * @return void
    */
   private function config_default() {
      $config_default = include_once $this->_path . '/files/config-default.php';

      if (!is_array($config_default)) {
         $config_default = (is_array($this->config)) ? $this->config : array();
      }

      // Settings from database
      $settings = get_option('plugin_' . self::domain . '_settings', FALSE);
      if ($settings) {
         foreach ($settings AS $k => $v) {
            $config_default[$k] = $v;
         }
      }

      // Settings from JSON-File
      $file = $this->_path . '/cache/filter_settings.json';
      if (file_exists($file)) {
         $json = file_get_contents($file);
         $config_default['filter'] = json_decode($json, true);
      }

      $this->config = $config_default;
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Plugin-Settings
    * @return void
    */
   private function config_plugin() {
      $config_plugin = include_once $this->_path . '/files/config-plugin.php';
      if(is_array($config_plugin)) {
         $this->API_secret .= $config_plugin['api'];
         $this->version = $config_plugin['version'];
         $this->ageSearchFilter = $config_plugin['ageSearchFilter'];
         $this->ageCachefileLang = $config_plugin['ageCachefileLang'];
         $this->mediaInfoCache = $config_plugin['mediaInfoCache'];
         $this->perPage = $config_plugin['perPage'];
         $this->option_testmodus = $config_plugin['option_testmodus'];
         $this->pmlinks = $config_plugin['pmlinks'];
         $this->deactivate_searchfilters = $config_plugin['deactivate_searchfilters'];
      }
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Add language to PantherMedia urls
    * @return void
    */
   private function config_pmlinks() {
      foreach($this->pmlinks AS $key => $value) {
         if($this->is_https()) {
            $value = str_ireplace('http://', 'https://', $value);
         }
         $this->pmlinks[$key] = $value . $this->lang;
      }
      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Loading stylesheet- and script-files
    * @param mixed $hook
    * @return void
    */
   public function load_style_and_scripts($hook) {
      try {
         $testmode = '';
         if($this->option_testmodus && $this->config['api_testmode']==='true') {
            $testmode = __('Test mode is active!', self::domain);
         }
         $script_array = array(
             'ajax_url' => admin_url('admin-ajax.php?action=' . self::domain),
             'messages' => array(
                 'ajax_error' => __('Cannot send ajax request.', self::domain),
                 'confirm' => __('Are you sure?', self::domain),
                 'testmode' => $testmode,
                 'download' => __('Download', self::domain),
                 'error' => __('Failed.', self::domain),
                 'success' => __('Success.', self::domain)
             ),
         );

         // styles
         wp_register_style(self::domain . '_css', $this->_url . '/files/' . self::domain . '.css');
         wp_enqueue_style(self::domain . '_css');
         wp_register_style('stickytooltip', $this->_url . '/files/stickytooltip/stickytooltip.css');
         wp_enqueue_style('stickytooltip');

         // scripts
         wp_register_script(self::domain . '_js', $this->_url . '/files/' . self::domain . '.js');
         wp_enqueue_script(self::domain . '_js');
         wp_localize_script(self::domain . '_js', __CLASS__, $script_array);
         wp_register_script('stickytooltip', $this->_url . '/files/stickytooltip/stickytooltip.js');
         wp_enqueue_script('stickytooltip');
      }catch(Exception $e) {
         PantherMediaStockPhoto_debug($e, __METHOD__);
      }
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * extend menu on the plugin listing page
    * @param array $l
    * @param string $file
    * @return array 
    */
   public function link_plugin_admin_settings($l, $file) {
      $admin_url = 'admin.php?page=' . self::domain . '/classes/' . self::domain . '_settings';
      $settings_link = '<a href="' . $admin_url . '">' . __('Settings', self::domain) . '</a>';
      array_unshift($l, $settings_link);
      return $l;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Return the contents of the output buffer
    * @param string $file
    * @param array $vals
    * @return mixed
    */
   protected function getContent($file, $vals = array()) {
      extract($vals);
      ob_start();
      require_once $file;
      $c = ob_get_contents();
      ob_end_clean();
      return $c;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Delete folder with files
    * Source code from: http://php.net/manual/de/function.rmdir.php#110489
    * @param string $dir
    * @return boolean
    */
   private function delTree($dir) { 
      $files = array_diff(scandir($dir), array('.','..')); 
      foreach ($files as $file) {
         (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file"); 
      } 
      return rmdir($dir); 
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Output of dimensions from image (width x height)
    * @param mixed $width
    * @param mixed $height
    * @return string
    */
   private function image_dimension($width, $height) {
      $width_ = str_replace('px', '', $width);
      $height_ = str_replace('px', '', $height);
      
      if($width_ < 1 || $height_ < 1) {
         return $width_.' x '.$height_;
      }
      
      //$mp = round($width * $height / 1000000, 2);
      return $width_ . ' x ' . $height_.' '.__('Pixel', self::domain);// . ' (' . $mp . ' MP)';
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Get image-info string (inch, dpi and file type [optional])
    * @param string|int $width
    * @param string|int $height
    * @param string|boolean $mimetype
    * @param int $dpi
    * @return string
    */
   private function image_info($width, $height, $mimetype = false, $dpi = 300) {
      $width_ = str_replace('px', '', $width);
      $height_ = str_replace('px', '', $height);
      
      if($width_ < 1 || $height_ < 1) {
         return $width_.' x '.$height_;
      }
      
      $iW = round(($width_ / $dpi) * 10) / 10;
      $iH = round(($height_ / $dpi) * 10) / 10;
      
      $inches = "$iW\" x $iH\" ".__('Inches', self::domain)." @$dpi DPI";
      $fileType = '';
      
      if($mimetype) {
         $mType = $this->mimetype($mimetype);
         $fileType = ' - '.__("File Type", self::domain).": ".strtoupper($mType);
      }
      
      return $inches.$fileType;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Get image-info string (mm and dpi)
    * @param string|int $width
    * @param string|int $height
    * @param int $dpi
    * @return string
    */
   private function image_info_mm($width, $height, $dpi = 300) {
      $width_ = str_replace('px', '', $width);
      $height_ = str_replace('px', '', $height);
      
      if($width_ < 1 || $height_ < 1) {
         return $width_.' x '.$height_;
      }
      
      //  Anzahl der Pixel * 25,4 / Wert in dpi = MaÃŸ in mm
      $iW = round( ($width_ * 25.4) / $dpi);
      $iH = round( ($height_ * 25.4) / $dpi);
      
      return "$iW x $iH ".__('mm', self::domain)." @$dpi DPI";
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * returned short-version of mimetype
    * Example: image/jpeg => JPG
    * @param string $type mimetype 
    * @return string
    */
   private function mimetype($type) {
      $mType = "";
      switch($type) {
         case "image/jpeg": $mType = "JPG"; break;
         default: $mType = $type; break;
      }
      return strtoupper($mType);
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * When folder not exists, create
    * @param string $folder
    * @return string
    */
   private function getFolder($folder) {
      $path = $this->_path.'/'.$folder;
      if (!is_dir($path)) {
         mkdir($path, 0775);
      } else {
         chmod($path, 0775);
      }
      return $path;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Get login settings for REST-api in wordpress-db
    * @param string $option
    * @param array $default
    * @return array
    */
   private function getLoginSettings() {
      $option = 'plugin_' . self::domain . '_api_connection';
      $default = $this->getDefaultAPISettings();
      $settings = $default;
      
      if (isset($this->config['private_or_public'])) {
         switch ($this->config['private_or_public']) {
            case "private":
               $settings = get_user_option($option, get_current_user_id());
               if (!$settings) {
                  $settings = $default;
               }
               break;

            case "public":
               $settings = get_option($option, $default);
               break;

            default: break;
         }
      }

      return $settings;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Set login settings for REST-api in wordpress-db
    * @param mixed $value
    * @return void
    */
   private function setLoginSettings($value) {
      $option = 'plugin_' . self::domain . '_api_connection';
      if (isset($this->config['private_or_public'])) {
         switch ($this->config['private_or_public']) {
            case "private":
               update_user_option(get_current_user_id(), $option, $value, false);
               break;

            case "public":
               update_option($option, $value);
               break;

            default: break;
         }
      }
      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * save user search in wordpress-db
    * @param mixed $value
    * @return void
    */
   private function setSearchSettings($value) {
      $option = 'plugin_' . self::domain . '_api_search';
      update_user_option(get_current_user_id(), $option, $value, false);
      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * load user search in wordpress-db
    * @return array
    */
   private function getSearchSettings() {
      $option = 'plugin_' . self::domain . '_api_search';
      return get_user_option($option, get_current_user_id());
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Load default REST-settings
    * @return array
    */
   private function getDefaultAPISettings() {
      return include $this->_path . '/files/config-rest.php';
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Check rights for using Media Upload TAB
    * @return void
    */
   public function check_capability() {
      try {
         $check = current_user_can('plugin_' . self::domain, get_current_user_id());
         if ($check) {
            // media-upload link
            add_filter('media_upload_tabs', array(&$this, 'load_media_tab_link'), 10, 1);

            // media-upload frame
            add_action('media_upload_' . self::domain . '_search', array(&$this, 'tab_iframe_search'));
            add_action('media_upload_' . self::domain . '_mymedia', array(&$this, 'tab_iframe_mymedia'));
            //add_action( 'media_upload_tab_slug', array(&$this, 'custom_media_upload_tab_content' ));
         }
      }catch(Exception $e) {
         PantherMediaStockPhoto_debug($e, __METHOD__);
      }
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Returned OS from user for API UserAgent
    * @return string
    */
   private function getOS(){
      $os_platform = "Unknown OS Platform";

      $os_array = array(
          '/windows nt 10/i' => 'Windows 10',
          '/windows nt 6.3/i' => 'Windows 8.1',
          '/windows nt 6.2/i' => 'Windows 8',       
          '/windows nt 6.1/i' => 'Windows 7',
          '/windows nt 6.0/i' => 'Windows Vista',
          '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
          '/windows nt 5.1/i' => 'Windows XP',
          '/windows xp/i' => 'Windows XP',
          '/windows nt 5.0/i' => 'Windows 2000',
          '/windows me/i' => 'Windows ME',
          '/win98/i' => 'Windows 98',
          '/win95/i' => 'Windows 95',
          '/win16/i' => 'Windows 3.11',
          '/macintosh|mac os x/i' => 'Mac OS X',
          '/mac_powerpc/i' => 'Mac OS 9',
          '/linux/i' => 'Linux (X11)',
          '/open bsd/i' => 'OpenBSD',
          '/sun os/i' => 'SunOS',
          '/ubuntu/i' => 'Ubuntu',
          '/iphone/i' => 'iPhone',
          '/ipod/i' => 'iPod',
          '/ipad/i' => 'iPad',
          '/android/i' => 'Android',
          '/blackberry/i' => 'BlackBerry',
          '/webos/i' => 'Mobile',
          '/meego/i' => 'MeeGo',
          '/bb10/i' => 'BlackBerry',
          '/rim/i' => 'BlackBerry',
          '/kindle/i' => 'Amazon Kindle',
          '/silk/i' => 'Amazon Android',
          '/windows phone/i' => 'Windows Phone',
          '/windows phone 8/i' => 'Windows 8 Mobile',
          '/windows phone 10/i' => 'Windows 10 Mobile',
      );

      // pass all checks for correct detecton and don't change order
      foreach($os_array as $regex => $value){
         if(preg_match($regex, $GLOBALS['_SERVER']['HTTP_USER_AGENT'])){
            $os_platform = $value;
         }
      }

      return $os_platform;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Format a title-string to an file- and url-string
    * @param string $string
    * @param string $separator
    * @return string
    */
   protected function format_string($string, $separator = '-') {
      $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
      $special_cases = array('&' => 'and');
      $string = strtolower(trim($string));
      $string = str_replace(array_keys($special_cases), array_values($special_cases), $string);
      $string = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
      $string = preg_replace("/[^a-z0-9\.]/u", "$separator", $string);
      $string = preg_replace("/[$separator]+/u", "$separator", $string);
      return $string;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Get URL to media details from PantherMedia image
    * @param int $id Download-ID from PantherMedia
    * @return boolean|string
    */
   private function getMediaLink($id) {
      if($id < 1) {
         return false;
      }
      
      $wpdb = $GLOBALS['wpdb'];
      $meta = $wpdb->get_row("SELECT * FROM $wpdb->postmeta WHERE meta_value LIKE '%i:" . $id . ";%' AND meta_key = '_" . self::domain . "_image_data';");
      if (isset($meta) && !empty($meta) && isset($meta->post_id)) {
         return admin_url('upload.php?item=') . $meta->post_id;
      }
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * JSON fix - Single Element    
    * 
    * @param array $element
    */
   private function JSON_FixSingleElementArray(&$element) {
      /*
      This code is part of the crVCL PHP Freamework
      
      The contents of this file are subject to the Mozilla Public License
      Version 1.1 (the "License"); you may not use this file except in compliance
      with the License. You may obtain a copy of the License at
      http://www.mozilla.org/MPL/MPL-1.1.html or see MPL-1.1.txt

      Software distributed under the License is distributed on an "AS IS" basis,
      WITHOUT WARRANTY OF ANY KIND, either expressed or implied. See the License for
      the specific language governing rights and limitations under the License.
      
      The Initial Developers of the Original Code are: 
      opyright (c) 2003-2017, CR-Solutions (http://www.cr-solutions.net), Ricardo Cescon
      All Rights Reserved.
       */

      if (!isset($element[0])) {
         $single = $element; // make a copy of the single element
         // fix the expected structure
         $element = array();
         $element[0] = $single;
      }
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * returned URL from p
    * @return stringlugin
    * @return string
    */
   public function getUrl() {
      return $this->_url;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * return number of per page
    * @return int
    */
   public function getPerPage() {
      return $this->perPage;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Returned size of mediainfo-cache folder
    * @return string
    */
   public function usedMediaCache() {
      $return = '';
      
      $cache = $this->getFolder('cache/mediainfo');
      $dir_usage = 0;
      $scanned = scandir($cache);
      foreach ($scanned as $dir_entry) {
         if (!is_file($cache.'/'.$dir_entry)) {
            continue;
         }
         $dir_usage += filesize($cache.'/'.$dir_entry);
      }
      
      return $this->formatBytes($dir_usage);
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * From bytes to KB or MB ..
    * http://stackoverflow.com/a/2510459
    * @param int $bytes
    * @param int $precision
    * @return string
    */
   private function formatBytes($bytes, $precision = 2) { 
      $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

      $bytes_ = max($bytes, 0); 
      $pow_ = floor(($bytes_ ? log($bytes) : 0) / log(1024)); 
      $pow = min($pow_, count($units) - 1); 

      // Uncomment one of the following alternatives
       $bytes /= pow(1024, $pow);
      // $bytes /= (1 << (10 * $pow)); 

      return round($bytes, $precision) . ' ' . $units[$pow]; 
   }
   
//------------------------------------------------------------------------------------------------------
   /**
   * check the connection from client to the script is over HTTPS, independent from internal connections over WAN as sample CDN to webserver or loadbalancer to webserver
   *
   * @return bool
   */
  function is_https(){
      if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
         return true;
      }
      if(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https'){
         return true;
      }   
      if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
         return true;
      }
      return false;      
   }
   
//------------------------------------------------------------------------------------------------------
   /** ####################################################################################################
    * Begin of Search-Filter functions
    */
   
   /**
    * Update search filter
    * @return boolean
    */
   private function searchFilter() {
      $get = TRUE;
      $path = $this->getFolder('cache');
      
      $search_file = $path.'/filter_search.json';
      $file = $path.'/filter.json';
      if(file_exists($file)) {
         $get = FALSE;
         if(filemtime($file) < (time()-$this->ageSearchFilter) ) {
            $get = TRUE;
         }
      }
      
      $r = FALSE;
      if($get) {
         $r = $this->api_getSearchFilter();
      }
      
      if($r || !file_exists($search_file)) {
         $this->createSearchFilter();
         $this->config();
         return true;
      }
      return false;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Create filter json for search
    * @return boolean
    */
   private function createSearchFilter() {
      $path = $this->getFolder('cache');
      $file_search = $path.'/filter_search.json';
      $file = $path.'/filter.json';
      
      if(file_exists($file)){
         $json = file_get_contents($file);
         $filter = json_decode($json, true);

         foreach($filter AS $f) {
            if(isset($f['name'])) {
               $adv_search[ $f['name'] ] = array(
                  'desc' => $f['desc'],
                  'type' => $f['type'],
                  'default' => $f['default'],
                  'value' => array(),
               );

               if(isset($f['value']) && is_array($f['value'])) {
                  if(isset($f['value']['name'])) {
                     $adv_search[ $f['name'] ]['value'][ $f['value']['name'] ] = $f['value']['desc'];
                  } else {
                     foreach($f['value'] AS $value) {
                        $adv_search[ $f['name'] ]['value'][ $value['name'] ] = $value['desc'];
                     }
                  }
               }
            }
         }

         file_put_contents($file_search, json_encode( $adv_search ));
         chmod($file_search, 0664);
         return true;
      }
      return;
   }

   /** ####################################################################################################
    * End of Search-Filter functions
    */
//------------------------------------------------------------------------------------------------------
   /** ####################################################################################################
    * Begin of Admin-Menu-Functions
    */
   
   /**
    * Adminmenu
    * @return void
    */
   public function admin_menu() {
      try {
         $textPM = self::name;
         $textSearch = __('Search', self::domain);
         $textMedia = __('My Media', self::domain);
         $textLogin = __('Account', self::domain);
         $textSettings = __('Settings', self::domain);
         $url = self::domain . '/classes/' . self::domain;


         // Mainpage
         //add_media_page($page_title, $menu_title, $capability, $menu_slug, $function)
         add_menu_page($textPM, $textPM, 'plugin_' . self::domain, __FILE__, array(&$this, 'admin_menu_media'), $this->_url . '/files/panthermedia-favicon.png');

         // Subpages
         // different name for first item - http://wordpress.stackexchange.com/a/66499
         add_submenu_page(__FILE__, $textSearch . ' - ' . $textPM, $textSearch, 'plugin_' . self::domain, $url . '.php', array(&$this, 'admin_menu_media'));
         add_submenu_page(__FILE__, $textMedia . ' - ' . $textPM, $textMedia, 'plugin_' . self::domain, $url . '_mymedia', array(&$this, 'admin_menu_mymedia'));
         add_submenu_page(__FILE__, $textLogin . ' - ' . $textPM, $textLogin, 'plugin_' . self::domain, $url . '_login', array(&$this, 'admin_menu_login'));
         add_submenu_page(__FILE__, $textSettings . ' - ' . $textPM, $textSettings, 'administrator', $url . '_settings', array(&$this, 'admin_menu_settings'));

         // Link to settings
         //add_filter($name, $function_to_add, $priority, $accepted_args)
         add_filter('plugin_action_links_' . plugin_basename($this->_file), array($this, 'link_plugin_admin_settings'), 10, 2);
      }catch(Exception $e) {
         PantherMediaStockPhoto_debug($e, __METHOD__);
      }
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Adminmenu - login
    * @return void
    */
   public function admin_menu_login() {
      try {
         $isLogged = $this->api_isLogged();
      } catch (Exception_PantherMedia_HTTP $e) {
         if(($this->api->getUser_Authentication() && $e->getCode() == 401) || $e->getCode() == 419) {
            $isLogged = false;
            $this->setLoginSettings($this->getDefaultAPISettings());
         }
         else {
            throw $e;
         }
      } catch (Exception $e) {
         throw $e;
      }

      $data = NULL;
      if($isLogged) {
         $data = $this->api_getUserData();
         if($data) {
            $data['date_format'] = get_option('links_updated_date_format', 'r');
         }
      }
      echo $this->getContent($this->_path . '/views/account.php', array('data' => $data) );
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Adminmenu - mymedia
    * @param string $data differentiate between media tab and normal site for CSS
    * @return void
    */
   public function admin_menu_mymedia($data = '') {
      echo $this->getContent($this->_path . '/views/mymedia.php', array('data' => $data) );
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Adminmenu - media
    * @param string $data differentiate between media tab and normal site for CSS
    * @return void
    */
   public function admin_menu_media($data = '') {
      echo $this->getContent($this->_path . '/views/search.php', array('data' => $data) );
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Adminmenu - settings
    * @return void
    */
   public function admin_menu_settings() {
      echo $this->getContent($this->_path . '/views/options.php');
   }

   /** ####################################################################################################
    * End of Admin-Menu-Functions
    */
//------------------------------------------------------------------------------------------------------
   /** ####################################################################################################
    * Begin of AJAX-Functions
    */
   
   /**
    * AJAX-Response
    * @return void
    */
   public function ajax_admin() {
      try {
         header("Content-Type: application/json");
         $POST = $GLOBALS['_POST'];
         $a = (isset($POST['a'])) ? sanitize_text_field($POST['a']) : false;
         if (!$a) { exit(); }

         #if($this->m_show_process) { PantherMediaStockPhoto_writeDebug(__METHOD__.'/'.__LINE__, 'process'); }
         $r = json_encode(array('status' => 404, 'message' => __('No content found.', self::domain)));
         switch ($a) {
            case "saveSettings-general": $r = $this->ajax_admin_saveSettings(); break;
            case "saveSettings-filter": $r = $this->ajax_admin_saveSettings_filter(); break;
            case "saveSettings-getFilter": $r = $this->ajax_admin_saveSettings_getFilter(); break;
            case "saveSettings-filterDelete": $r = $this->ajax_admin_saveSettings_filterDelete(); break;
            case "saveSettings-setDefault": $r = $this->ajax_admin_saveSettings_setDefault(); break;
            case "clearCache": $r = $this->ajax_admin_clearCache(); break;
            case "openauth": $r = $this->ajax_admin_openauth(); break;
            case "logout": $r = $this->ajax_admin_logout(); break;
            case "media_search": $r = $this->ajax_admin_media_search(); break;
            case "media_detail": $r = $this->ajax_admin_media_detail(); break;
            case "media_licenses": $r = $this->ajax_admin_media_licenses(); break;
            case "media_available": $r = $this->ajax_admin_media_available(); break;
            case "image_download": $r = $this->ajax_admin_image_download(); break;
            case "image_buy": $r = $this->ajax_admin_image_buy(); break;
            default: break;
         }

         echo ($r===NULL) ? json_encode($this->status) : $r;
      }catch(Exception $e) {
         PantherMediaStockPhoto_debug($e, __METHOD__);
         $this->api_checkInfo($e->getCode());
         //$this->status['message'] = $e->getMessage();
         
         if($e->getCode() === 401) {
            $this->setLoginSettings($this->getDefaultAPISettings());
         }
         echo json_encode($this->status);
      }
      exit;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Delete media info cache files
    * @return void
    */
   private function ajax_admin_clearCache() {
      $dir = $this->getFolder('cache/mediainfo');
      if(is_dir($dir)) {
         $this->delTree($dir);
      }
      
      $this->api_checkInfo(200);
      $this->status['message'] = __('Cache cleared', self::domain);
      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Save main settings
    * @return boolean
    */
   private function ajax_admin_saveSettings() {
      $POST = $GLOBALS['_POST'];
      $data = (isset($POST['data']) && is_array($POST['data'])) ? ($POST['data']) : array();
      $settings = array();
      foreach ($data as $arr) {
         if (array_key_exists($arr['name'], $this->config)) {
            $settings[$arr['name']] = sanitize_text_field($arr['value']);
         }
      }
      
      // save
      update_option('plugin_' . self::domain . '_settings', $settings);
      $this->ajax_admin_saveSettings_capability($settings);
      $this->ajax_admin_saveSettings_usability($settings);
      
      $this->api_checkInfo(200);
      $this->status['message'] = __('Settings saved.', self::domain);
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Save user capability for using this plugin
    * @param array $settings
    * @return void
    */
   private function ajax_admin_saveSettings_capability($settings) {
      $wp_roles = new WP_Roles();
      if (isset($settings['role_to_use_add_photos'])) {
         switch ($settings['role_to_use_add_photos']) {
            case 'administrator':
               $wp_roles->remove_cap('editor', 'plugin_' . self::domain);
               $wp_roles->remove_cap('author', 'plugin_' . self::domain);
               break;

            case 'editor':
               $wp_roles->add_cap('editor', 'plugin_' . self::domain);
               $wp_roles->remove_cap('author', 'plugin_' . self::domain);
               break;

            case 'autor':
               $wp_roles->add_cap('editor', 'plugin_' . self::domain);
               $wp_roles->add_cap('author', 'plugin_' . self::domain);
               break;
            default: break;
         }
      }
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Save default REST settigs und delete unused
    * @param array $settings
    * @return void
    */
   private function ajax_admin_saveSettings_usability($settings) {
      if(isset($settings['private_or_public'])) {
         $option = 'plugin_' . self::domain . '_api_connection';
         switch($settings['private_or_public']) {
            case "private":
               delete_option($option);
               break;
            
            case "public":
               add_option($option, $this->getDefaultAPISettings());
               $wpdb = $GLOBALS['wpdb'];
               $wpdb->delete( $GLOBALS["table_prefix"].'usermeta', array('meta_key' => $GLOBALS["table_prefix"].$option));
               break;
            
            default: break;
         }
      }
      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Save settings for search filters
    * @return boolean
    */
   private function ajax_admin_saveSettings_filter() {
      $POST = $GLOBALS['_POST'];
      $data = (isset($POST['data']) && is_array($POST['data'])) ? ($POST['data']) : array();
      $settings = array();

      foreach ($data AS $arr) {
         if (strpos($arr['name'], 'filter-') > -1) {
            $expl = explode('-', $arr['name']);
            $type1 = sanitize_text_field($expl[1]);
            $type2 = sanitize_text_field($expl[2]);
            if(isset($settings[$type1][$type2]) && $type2 == 'default') {
               $settings[$type1][$type2] .= ",".sanitize_text_field($arr['value']);
            }
            else {
               $settings[$type1][$type2] = sanitize_text_field($arr['value']);
            }
         }
      }

      $path = $this->getFolder('cache');
      $file = $path . '/filter_settings.json';
      file_put_contents($file, json_encode($settings));
      chmod($file, 0664);

      $this->api_checkInfo(200);
      $this->status['message'] = __('Settings saved.', self::domain);
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Get filter options from PantherMedia
    * @return boolean
    */
   private function ajax_admin_saveSettings_getFilter() {
      $r = $this->searchFilter();
      if($r === true) {
         $this->api_checkInfo(200);
         $this->status['message'] = __('Settings saved.', self::domain);
         $this->status['reload'] = $GLOBALS['_SERVER']['HTTP_REFERER'];
         return;
      }
      $this->api_checkInfo(500);
      $this->status['message'] = __('Failed.', self::domain);
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Delete filter settings file
    * @return boolean
    */
   private function ajax_admin_saveSettings_filterDelete() {
      $filterSettings = $this->_path . '/cache/filter_settings.json';
      if(file_exists($filterSettings)) {
         unlink($filterSettings);
      }
      $this->api_checkInfo(200);
      $this->status['message'] = __('Settings saved.', self::domain);
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Set default settings
    * @return boolean
    */
   private function ajax_admin_saveSettings_setDefault() {
      $option1 = 'plugin_' . self::domain . '_api_connection';
      $option2 = 'plugin_' . self::domain . '_api_search';
      
      // delete general options
      delete_option('plugin_'.self::domain.'_settings');
      
      // delete login options
      delete_option($option1);
      
      // delete user logins
      $wpdb = $GLOBALS['wpdb'];
      $wpdb->delete( $GLOBALS["table_prefix"].'usermeta', array('meta_key' => $GLOBALS["table_prefix"].$option1));
      
      // delete user search
      $wpdb->delete( $GLOBALS["table_prefix"].'usermeta', array('meta_key' => $GLOBALS["table_prefix"].$option2));
      
      // delete Cache folder
      $cache = $this->_path.'/cache';
      if(is_dir($cache)) {
         $this->delTree($cache);
      }
      
      $this->api_checkInfo(200);
      $this->status['message'] = __('Settings saved.', self::domain);
      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * OAuth Login
    * @return boolean|array
    */
   private function ajax_admin_openauth() {
      $isLoggedIn = $this->api_isLogged();
      
      if(!$isLoggedIn) {
         $getAuth = $this->api_getAuthURL();
         $this->api_checkInfo();
         
         if(is_string($getAuth)) {
            // https://developer.wordpress.org/reference/functions/wp_redirect/
            $this->status['reload'] = $getAuth;
            return;
         }
         return $this->api_checkInfo(412);
      }
      
      return $this->api_checkInfo(403);
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Logout
    * @return array
    */
   private function ajax_admin_logout() {
      $this->setLoginSettings($this->getDefaultAPISettings());
      return $this->api_checkInfo(200);
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * MyMedia
    * @return boolean|array
    */
   private function ajax_admin_media_licenses() {
      $POST = $GLOBALS['_POST'];
      $page = (isset($POST['page']) ? (int) $POST['page'] : 1);
      $perPage = ((isset($POST['per_page']) && $POST['per_page'] > 0) ? (int) $POST['per_page'] : 24);
      
      $isLogged = $this->api_isLogged();
      if(!$isLogged) {
         return $this->api_checkInfo(401);
      }

      $myImages = $this->api_getDownloadedImages();
      if ($myImages) {
         $this->api_token_valid();

         $images = $this->ajax_admin_media_licenses_images($myImages);
         $paging = $this->ajax_admin_media_paging($page, (int) $myImages['media_list']['total'], $perPage);
         
         $this->status['info'] = sprintf(__("We found %s images.", self::domain), number_format($myImages['media_list']['total'], 0, '', ','));
         $this->status['images'] = $images['content'];
         $this->status['paging'] = $paging;

         $corporateImages = FALSE;
         if(isset($isLogged['user']['corporate_account']['see_media_from_others'])){
            if($isLogged['user']['corporate_account']['see_media_from_others'] == 'yes') {
               $corporateImages = TRUE;
            }
         }
         $this->status['corporate_images'] = $corporateImages;
         
         if($images['error'] > 0) {
            $string = _n("%s Image could not be loaded.","%s Images could not be loaded.",$images['error'],self::domain);
            $number = number_format($images['error'], 0, '', ',');
            $this->status['info'] .= ' '.sprintf($string,$number);
            $this->status['error_info'] .= sprintf($string,$number);
         }
         return;
      }

      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Loading template media images
    * @param array $media
    * @return array
    */
   private function ajax_admin_media_licenses_images($media) {
      $return = array('content' => '', 'error' => 0);
      $arr = array();
      if ($media['media_list']['items'] > 0) {
         $settings = $this->getLoginSettings();
         $this->api->setUser_Authentication($settings['login'], $settings['password']);
         foreach ($media['media_list']['media'] AS $m) {
            try {
               $ret = $this->api_getMediaInfo_check($m['id-media']);
               
               if($ret) {
                  $arr['media'][] = array(
                      'id' => $ret['media']['id'],
                      'title' => $ret['metadata']['title'],
                      'thumb_url' => $ret['media']['thumb_170_url'],
                      'preview_url' => $ret['media']['preview_url'],
                      'author' => $ret['metadata']['author_username'],
                      'info' => $m,
                  );
               } else  {
                  ++$return['error'];
               }
            } catch (Exception $e) {
               ++$return['error'];
            }
         }
      }
      
      if (!empty($arr)) {
         $return['content'] = $this->getContent($this->_path . '/views/media_licenses.php', $arr);
      }

      return $return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Paging 
    * @param int $page
    * @param int $total
    * @param int $per_page
    * @return string
    */
   private function ajax_admin_media_paging($page, $total = 0, $per_page = 10) {
      $return = '';

      if ($total > 0) {
         $last_page = ceil($total / $per_page);

         if ($page > $last_page) {
            $page = $last_page;
         }
         
         $info_first = ($page * $per_page) - ($per_page - 1);
         $info_last = $page * $per_page;
         
         if($info_last > $total) {
            $info_last = $total;
         }

         $return = $this->getContent($this->_path . '/views/paging.php', array(
             'page' => $page,
             'last_page' => $last_page,
             'total' => number_format($total, 0, '', ','),
             'info_first' => $info_first,
             'info_last' => $info_last,
             'info_lastPage' => number_format($last_page, 0, '', ','),
         ));
      }

      return $return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Mediapage
    * @return boolean|array
    */
   private function ajax_admin_media_search() {
      $POST = $GLOBALS['_POST'];
      
      $keywords = (isset($POST['q']) ? sanitize_text_field($POST['q']) : '');
      $page = (isset($POST['page']) ? (int) $POST['page'] : 1);
      $perPage = ((isset($POST['per_page']) && $POST['per_page'] > 0) ? (int) $POST['per_page'] : 24);
      $filters = $this->ajax_admin_media_search_filters();

      $extra_info = 'title,preview,keywords,author_username';
      $this->setSearchSettings($POST);
      $this->api_token_valid();
      $ret = $this->api->search($keywords, $this->lang, ($page - 1), $perPage, $extra_info, $filters);
      $images = $this->ajax_admin_media_search_thumb($ret['items']['media']);
      $paging = $this->ajax_admin_media_paging($page, (int) $ret['items']['total'], $perPage);
      $this->api_checkInfo();
      $this->status['info'] = sprintf(__("We found %s images.", self::domain), number_format($ret['items']['total'], 0, '', ','));
      $this->status['images'] = $images;
      $this->status['paging'] = $paging;

      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * set filters for REST media-search
    * @return string
    */
   private function ajax_admin_media_search_filters() {
      $filters = '';
      $filterArray = array();

      $POST = $GLOBALS['_POST'];
      if(isset($POST['sort']) && !empty($POST['sort'])) {
         $filterArray['sort'] = $POST['sort'];
      }
	  
      if (isset($POST['data']) && is_array($POST['data'])) {
         foreach ($POST['data'] AS $data) {
            $name = (isset($data['name']) ? str_ireplace('filter-', '', $data['name']) : false);
            $value = str_ireplace('#', '', $data['value']);

            $controlValue = $this->ajax_admin_media_search_filters_control($name, $value);
            if($controlValue && $name && !empty($value)) {
               if (isset($filterArray[$name])) {
                  $filterArray[$name] .= ',' . $value;
               } else {
                  $filterArray[$name] = $value;
               }
            }
         }
      }
      
      foreach ($filterArray AS $key => $value) {
         $filters .= "$key:$value;";
      }
      
      return $filters;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Check option to exists in filter
    * @param string $name
    * @param string $value
    * @return boolean
    */
   private function ajax_admin_media_search_filters_control($name, $value) {
      $file = $this->_path.'/cache/filter_search.json';
      if(file_exists($file)) {
         $filters = json_decode(file_get_contents( $file ),true );
         
         if(isset($filters[$name]) && !empty($value)) {
            if($filters[$name]['type'] == 'string' || $filters[$name]['type'] == 'hexcode') {
               return true;
            } else if(array_key_exists($value, $filters[$name]['value'])) {
               return true;
            }
         }
      }
      
      return false;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Loading template media-images
    * @param array $media
    * @return string
    */
   private function ajax_admin_media_search_thumb($media) {
      $this->JSON_FixSingleElementArray($media);
      
      $return = '';
      $arr = array();
      if(is_array($media)) {
         foreach ($media AS $m) {
            $arr['media'][] = array(
                'id' => $m['id'],
                'title' => $m['title'],
                'thumb_url' => $m['thumb'],
                'preview_url' => $m['preview'],
                'author' => $m['author-username'],
            );
         }
      }
      
      if (!empty($arr)) {
         $return = $this->getContent($this->_path . '/views/thumb.php', $arr);
      }

      return $return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Media - detail
    * @return boolean|array
    */
   private function ajax_admin_media_detail() {
      $POST = $GLOBALS['_POST'];
      $id = (isset($POST['id']) ? sanitize_text_field($POST['id']) : false);
      if ($id) {
         $mediainfo = $this->api_getMediaInfo($id);
         $this->api_checkInfo();
         if ($mediainfo) {
            $downloaded = $this->ajax_admin_media_detail_downloaded($id);
            $deposit = false;
            $user_data = $this->api_getUserData();
            if(isset($user_data['deposit']) && is_array($user_data['deposit'])) {
               foreach($user_data['deposit'] AS $arr) {
                  $deposit[] = array(
                      'type' => $arr['type'],
                      'name' => $arr['name'],
                      'remaining' => $arr['deposit-remaining'],
                  );
               }
            }
            $info = array(
                'info' => $mediainfo,
                'downloaded' => $downloaded,
                'deposit' => $deposit,
            );
            $this->status['detail'] = $this->getContent($this->_path . '/views/media_detail.php', $info);
            $this->status['object'] = $info;
         }
         return;
      }
      
      return $this->api_checkInfo(412);
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Check image if it was downloaded (API)
    * @param int $id
    * @return boolean|array
    */
   private function ajax_admin_media_detail_downloaded($id) {
      $ret = $this->api_getDownloadedImages($id);
      if(isset($ret['stat']) && $ret['stat'] == 'ok') {
         if(isset($ret['media_list']['total']) && $ret['media_list']['total'] > 0) {
            $return = array(
                'width' => 0,
                'downloaded' => array(),
            );

            $control = $this->ajax_admin_media_detail_downloaded_controlDB($id);
            $this->JSON_FixSingleElementArray($ret['media_list']['media']);
            foreach($ret['media_list']['media'] AS $downloaded) {
               $return['downloaded'][$downloaded['width']] = $downloaded;
               $width = (int)str_ireplace('px', '', $downloaded['width']);
               if($width > $return['width']) {
                  $return['width'] = $width;
               }

               if(isset($control['width']) && $control['width'] == $width) {
                  $control['value']['download'] = (int)$downloaded['id-download'];
               }
            }

            if(isset($control['value']['download']) && !empty($control['value']['download'])) {
               update_metadata('post', $control['id'], '_'.self::domain.'_image_data', $control['value']);
            }
            return $return;
         }
      }
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Check image if it was downloaded (DB)
    * @param int $id
    * @return boolean|array
    */
   private function ajax_admin_media_detail_downloaded_controlDB($id) {
      $len = strlen($id);
      $like = 's:2:"id";s:'.$len.':"'.$id.'";s:8:"download";N;';
      $wpdb = $GLOBALS['wpdb'];
      $select = "SELECT * FROM $wpdb->postmeta WHERE meta_value LIKE '%$like%' AND meta_key = '_".self::domain."_image_data';";
      $row = $wpdb->get_row( $select );
      if ( null !== $row ) {
         $meta = get_post_meta($row->post_id,'_wp_attachment_metadata', true);
         if(isset($meta['width'])) {
            $return = array();
            $return['width'] = $meta['width'];
            $return['id'] = $row->post_id;
            $return['value'] = maybe_unserialize($row->meta_value);
            return $return;
         }
      }
      return false;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Media - available licenses and sizes
    * @return boolean|array
    */
   private function ajax_admin_media_available() {
      $POST = $GLOBALS['_POST'];
      $id = (isset($POST['id']) ? sanitize_text_field($POST['id']) : false);
      
      if ($id) {
         $isLogged = $this->api_isLogged();
         if(!$isLogged) {
            return $this->api_checkInfo(401);
         }
         
         $ret = $this->api_getDownloadedImages($id);
         $this->api_checkInfo();
         
         if(isset($ret['media_list']['total']) && $ret['media_list']['total'] > 0) {
            $mediainfo = $this->api_getMediaInfo($id);
            $mediainfo['mymedia'] = $ret['media_list'];
            $info = array( 'info' => $mediainfo );
            $this->status['detail'] = $this->getContent($this->_path . '/views/media_available.php', $info);
            return;
         }
      }
      return $this->api_checkInfo(412);
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Re-Download an image, rights available
    * @return boolean|array
    */
   private function ajax_admin_image_download() {
      $POST = $GLOBALS['_POST'];
      $id = (isset($POST['id']) ? sanitize_text_field($POST['id']) : false);
      $download = (isset($POST['download']) ? (int) $POST['download'] : 0);
      if ($id && $download > 0) {
         $isLogged = $this->api_isLogged();
         if(!$isLogged) {
            return $this->api_checkInfo(401);
         }
         
         $this->api_downloadImage($id, NULL, $download);
         return;
      }
      return $this->api_checkInfo(412);
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Buy an image incl. rights
    * @return boolean|array
    */
   private function ajax_admin_image_buy() {
      $POST = $GLOBALS['_POST'];
      $id = (isset($POST['id']) ? sanitize_text_field($POST['id']) : false);
      $article = (isset($POST['article']) ? sanitize_text_field($POST['article']) : false);
      if ($id && !empty($article)) {
         $isLogged = $this->api_isLogged();
         if(!$isLogged) {
            return $this->api_checkInfo(401);
         }
         
         $this->api_downloadImage($id, $article, NULL);
         return;
      }
      return $this->api_checkInfo(412);
   }

   /** ####################################################################################################
    * End of AJAX-Functions
    */
//------------------------------------------------------------------------------------------------------
   /** ####################################################################################################
    * Begin of API-Functions
    */
   
   /**
    * Set useragent for client
    * @return void
    */
   private function api_setUserAgent() {
      $restVersion = $this->api->get_rest_version();
      $os = $this->getOS();
      $plugin = __CLASS__ . '/' . $this->version;
      $agent = 'Mozilla/5.0 (' . $os . ' +www.panthermedia.net) PantherMediaRESTfulClient/' . $restVersion . ' ' . $plugin;
      $this->api->set_user_agent($agent);
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Check and set status for REST requests (returned for AJAX)
    * http://rest.panthermedia.net/v1.0/index/#Error-Codes
    * @param int $error Status-Code
    * @return boolean
    */
   private function api_checkInfo($error = FALSE) {
      $info = $this->api->getResponseCode();
      
      if( is_bool($error) && ($info === null || !isset($info['code'])) ) {
         $this->status = array('status' => 404, 'message'=>__('No content found.', self::domain));
         return;
      }
      $status = ($error>0) ? $error : $info['code'];
      
      
      $this->status = array('status' => $status, 'debug' => WP_DEBUG);
      switch($status) {
         case 200: $this->status['message'] = __('Success.', self::domain); break;
         
         case 401: $this->status['message'] = __('You must be logged in.', self::domain); break;
         case 402: $this->status['message'] = __('Payment required.', self::domain); break;
         case 403: $this->status['message'] = __('Forbidden.', self::domain); break;
         case 404: $this->status['message'] = __('No content found.', self::domain); break;
         case 409: $this->status['message'] = __('File available.', self::domain); break;
         case 410: $this->status['message'] = __('Resources no longer available.', self::domain); break;
         
         case 400:
         case 412: $this->status['message'] = __('Script-Error.', self::domain); break;
         
         case 420: $this->status['message'] = __('test-download not possible with partner media', self::domain); break;
         
         case 421:
         case 429: $this->status['message'] = __('Too many Request.', self::domain).' '.__('Try it again later.', self::domain); break;
         
         case 500:
         case 510:
         case 501: $this->status['message'] = __('Internal Server Error', self::domain); break;

         default: $this->status['message'] = __('Unknown Error', self::domain); break;
      }
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Check is logged in - if true => return profile
    * @return mixed
    */
   private function api_isLogged() {
      if($this->m_api_userProfile !== null) {
         return $this->m_api_userProfile;
      }
      
      $api = $this->api;
      $credentials = $api->getUser_Authentication();

      if ($credentials === false) {
         $settings = $this->getLoginSettings();

         $token = (isset($settings['login']) ? $settings['login'] : '');
         $secret = (isset($settings['password']) ? $settings['password'] : '');

         $api->setUser_Authentication($token, $secret);
      }
      $this->m_api_userProfile = $api->get_user_profile();
      return $this->m_api_userProfile;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Set new token time
    * @return void
    */
   private function api_token_valid() {
      $settings = $this->getLoginSettings();
      
      $tokenTime = (isset($settings['token_time']) ? $settings['token_time'] : 0);
      $hours24 = time()-(60*60*24);
      
      if($tokenTime < $hours24) {
         $isLoggedIn = $this->api_isLogged();
         
         if($isLoggedIn) {
            $ts = time() + ( (60*60*24) * $this->config['age_api_token']);
            $valid_until = str_replace('+0000', 'UTC', gmdate(DATE_RSS, $ts));
            
            $this->api->token_valid_until($valid_until); // REQUEST
            $settings['token_time'] = time();
            $this->setLoginSettings($settings);
         }
      }
      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * returns all downloaded images (REST)
    * @param string $imageID
    * @return mixed
    */
   private function api_getDownloadedImages($imageID = FALSE) {
      $settings = $this->getLoginSettings();
      $this->api->setUser_Authentication($settings['login'], $settings['password']);
      
      $POST = $GLOBALS['_POST'];
      $page = (isset($POST['page']) ? (int) $POST['page'] : 1);
      $perPage = ((isset($POST['per_page']) && $POST['per_page'] > 0) ? (int) $POST['per_page'] : 24);
      $corporate = (isset($POST['corporate']) ? sanitize_text_field($POST['corporate']) : 'no');
      if ($corporate !== 'yes' && $corporate !== 'no') {
         $corporate = 'no';
      }

      $offset = ($page * $perPage) - $perPage;
      $post_data = array(
         'content_type' => 'application/json',
         'limit' => $perPage,
         'offset' => ($offset>0)?$offset:0,
         'extra_info' => 'width,height,mimetype,date,ref,state',
         'license_info' => 'yes',
         'corporate_all_user' => $corporate,
      );

      if($imageID) {
         $post_data['id_media'] = $imageID;
      }

      $return = $this->api->request('get-downloaded-images', $post_data, null, $this->is_https());
      $this->api_checkInfo();

      return $return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Get OpenAUTH url
    * @return boolean|string
    */
   private function api_getAuthURL() {
      $data = $this->getLoginSettings();
      $data['password'] = uniqid();
      
      $admin_url = 'admin.php?page=' . self::domain . '/classes/' . self::domain . '_login';
      $app_name = 'WordPress ' . self::domain . ' ' . $this->version . ' - ' . $GLOBALS['_SERVER']['SERVER_NAME'];
      $imageIcon = ($this->is_https()?'https':'http')."://stockagency.panthermedia.net/images/logos/pm_app_wordpress.jpg";
      $ret = $this->api->request_token($data['password'], $app_name, $imageIcon, admin_url($admin_url), null, $this->lang);

      if(isset($ret['stat']) && $ret['stat'] == 'ok') {
         $data['login'] = $ret['token'];
         $data['openauth_url'] = $ret['auth_url'];
         $this->setLoginSettings($data);
         return $data['openauth_url'];
      }
      
      return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Get userdata
    * @return mixed
    */
   private function api_getUserData() {
      $this->api_token_valid();
      $profile = $this->api->get_user_profile();
      $deposit = $this->api->get_user_deposit($this->lang);

      $userDeposits = array();
      if (isset($deposit['deposits']['deposit']) && is_array($deposit['deposits']['deposit'])) {
         if(isset($deposit['deposits']['deposit']['name'])) {
            $userDeposits[] = $deposit['deposits']['deposit'];
         } else {
            $userDeposits = $deposit['deposits']['deposit'];
         }
      }

      // token_valid_until
      $token_valid_until = $profile['user']['token_valid_until'];
      $token_valid_until_locale_ts = strtotime($token_valid_until);
      return array(
          'token_time' => $token_valid_until_locale_ts,
          'credits_link' => $this->pmlinks['credits'],
          'subscribe_link' => $this->pmlinks['subscription'],
          'profile' => $profile['user'],
          'deposit' => $userDeposits,
      );
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Mediainfo
    * @param int $id
    * @return mixed
    */
   private function api_getMediaInfo($id) {
      $return = false;
      $settings = $this->getLoginSettings();
      $this->api->setUser_Authentication($settings['login'], $settings['password']);

      $r = $this->api_getMediaInfo_check($id);
      
      if (isset($r['stat']) && $r['stat'] == 'ok') {
         $licenses = $this->api_getMediaInfo_licenses($r, $r['options']['rights_managed']);

         $return = array();
         $return['image_page'] = str_ireplace('#id#', $r['media']['id'], $this->pmlinks['media']);
         $return['user_page'] = str_ireplace('#user#', $r['metadata']['author_username'], $this->pmlinks['user']);
         $return['title'] = ($r['metadata']['title'] ? $r['metadata']['title'] : implode(', ', array_slice(explode(',', $r['metadata']['keywords']), 0, 5)) );
         $return['media'] = $r['media'];
         $return['metadata'] = $r['metadata'];
         $return['options'] = $r['options'];
         $return['licenses'] = $licenses;
      }
      return $return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Check if mediainfo-File exists in Cache
    * @param string $id
    * @return boolean|array
    */
   private function api_getMediaInfo_check($id) {
      $cache = $this->getFolder('cache/mediainfo');
      $file = $cache.'/'.$id.'_'.$this->lang.'.json';
      if(!defined('PM_CACHE') || PM_CACHE !== FALSE) {
         if(file_exists($file) && filemtime($file) > (time()-$this->mediaInfoCache) ) {
            $json = file_get_contents($file);
            $decode = json_decode($json, TRUE);

            if($decode && is_array($decode)) {
               return $decode;
            }
         }
      }
      
      $return = $this->api->get_media_info($id, $this->lang, true, false);
      file_put_contents( $file , json_encode($return) );
      chmod($file, 0664);
      return $return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * License-info
    * @param array $cu
    * @param string $rm
    * @return array
    */
   private function api_getMediaInfo_licenses($cu, $rm) {
      $return = array(
          'subscription' => array(),
          'packet' => array(),
      );

      foreach ($cu['articles'] AS $type => $articles) {
         if (isset($articles['total']) && $articles['total'] > 0) {
            $key = str_ireplace('_list', '', $type);
            switch ($key) {
               case "marking": 
                  $return[$key][] = $this->api_getMediaInfo_licenses_marking($articles);
                  break;
               case "subscription":
               case "packet":
                  $return[$key] = $this->api_getMediaInfo_licenses_other($key, $articles);
                  break;
               case "singlebuy":
                  $return['credits'] = $this->api_getMediaInfo_licenses_singlebuy('credits', $articles, $rm);
                  $return['credits_extended_rights'] = $this->api_getMediaInfo_licenses_singlebuy_extended_rights('credits', $articles, $rm);
                  break;
               default: break;
            }
         }
      }
      
      return $return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * License - singlebuy (credits/eur)
    * @param string $type
    * @param array $article
    * @param string $rm
    * @return array
    */
   private function api_getMediaInfo_licenses_singlebuy($type, $article, $rm) {
      $this->JSON_FixSingleElementArray($article['singlebuy']);

      $return = array();
      foreach($article['singlebuy'] AS $product) {
         if ($product['currency'] == $type) {
            $total = intval($product['sizes']['total']);
            if($total === 0) {
               continue;
            }
            if($total === 1) {
               $sizes = $product['sizes']['article'];
               if ($sizes['mimetype'] == 'image/jpeg') {
                  $dimension = $this->image_dimension($sizes['width'], $sizes['height']);
                  $key = $sizes['width'];
                  if($rm === 'yes') {
                     $key = $sizes['id'];
                  }
                  $return[$key] = array(
                     'info' => '',
                     'article-id' => $sizes['id'],
                     'name' => $sizes['name'],
                     'term' => (isset($product['term']) ? $product['term'] : ''),
                     'width' => $sizes['width'],
                     'dimension' => $dimension,
                     'price' => $sizes['price'],
                     'total' => false,
                     'remaining' => $product['deposit-remaining'],
                     'percent' => false,
                     'currency' => $product['currency'],
                  );
               }
            } else {
               foreach ($product['sizes']['article'] as $sizes) {
                  if ($sizes['mimetype'] == 'image/jpeg') {
                     $dimension = $this->image_dimension($sizes['width'], $sizes['height']);
                     $key = $sizes['width'];
                     if($rm === 'yes') {
                        $key = $sizes['id'];
                     }
                     $return[$key] = array(
                        'info' => '',
                        'article-id' => $sizes['id'],
                        'name' => $sizes['name'],
                        'term' => (isset($product['term']) ? $product['term'] : ''),
                        'width' => $sizes['width'],
                        'dimension' => $dimension,
                        'price' => $sizes['price'],
                        'total' => false,
                        'remaining' => $product['deposit-remaining'],
                        'percent' => false,
                        'currency' => $product['currency'],
                     );
                  }
               }
            }
         }
      }
      return $return;
   }
    
//------------------------------------------------------------------------------------------------------
   
   /**
    * License - singlebuy (credits/eur) extended rights
    * @param string $type
    * @param array $article
    * @return array
    */
   private function api_getMediaInfo_licenses_singlebuy_extended_rights($type, $article) {
      $this->JSON_FixSingleElementArray($article['singlebuy']);
      
      $return = array();
      foreach ($article['singlebuy'] AS $product) {
         if ($product['currency'] == $type) {
            $total = intval($product['extended_rights']['total']);
            if($total === 0) {
               continue;
            }
            
            foreach ($product['extended_rights']['article'] as $er) {
               $return[] = array(
                   'info' => '',
                   'article-id' => $er['id'],
                   'name' => $er['name'],
                   'price' => $er['price'],
                   'remaining' => $product['deposit-remaining'],
                   'currency' => $product['currency'],
               );
            }
         }
      }
      return $return;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * License - marking
    * @param array $article
    * @return mixed
    */
   private function api_getMediaInfo_licenses_marking($article) {
      if (isset($article['article']['mimetype']) && $article['article']['mimetype'] == "image/jpeg") {
         $dimension = $this->image_dimension($article['article']['width'], $article['article']['height']);
         $inch = $this->image_info($article['article']['width'], $article['article']['height'], $article['article']['mimetype']);
         return array(
            'info' => $article['info'],
            'article-id' => $article['article']['id'],
            'name' => $article['article']['name'],
            'term' => (isset($article['term']) ? $article['term'] : ''),
            'dimension' => $dimension,
            'inch' => $inch,
            'price' => $article['article']['price'],
            'total' => false,
            'remaining' => false,
            'percent' => false,
         );
      }

      return null;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * License - subscription/packet
    * @param string $type
    * @param array $article
    * @return array
    */
   private function api_getMediaInfo_licenses_other($type, $article) {
      $this->JSON_FixSingleElementArray($article[$type]);

      $return = array();
      if(isset($article[$type])) {
         foreach ($article[$type] AS $product) {
            if ($product['type'] == $type) {
               if ($product['article']['mimetype'] == 'image/jpeg') {
                  $dimension = $this->image_dimension($product['article']['width'], $product['article']['height']);
                  $inch = $this->image_info($product['article']['width'], $product['article']['height'], $product['article']['mimetype']);
                  $return[] = array(
                     'info' => $product['name'],
                     'article-id' => $product['article']['id'],
                     'name' => $product['article']['name'],
                     'term' => (isset($product['term']) ? $product['term'] : ''),
                     'dimension' => $dimension,
                     'inch' => $inch,
                     'price' => $product['article']['price'],
                     'total' => $product['deposit-total'],
                     'remaining' => $product['deposit-remaining'],
                     'percent' => $product['deposit-remaining-percent'],
                  );
               }
            }
         }
      }
      return $return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * (Re-)download (and buy) image from PantherMedia
    * @param string $image
    * @param boolean|string $article
    * @param boolean|int $download
    * @return boolean|string
    */
   private function api_downloadImage($image, $article, $download) {
      $mediainfo = $this->api_getMediaInfo($image);
      if($mediainfo) {
         $settings = $this->api_downloadImage_settings($mediainfo, $download, $image);
         $file = (isset($settings['attachment']['guid']) ? $settings['attachment']['guid'] : false);

         if(file_exists($file)) {
            return $this->api_checkInfo(409);
         }

         $testmode = ($this->option_testmodus && (isset($this->config['api_testmode']) && $this->config['api_testmode'] == 'false') ? false : true);
         if($testmode == true && $image[0] < 1) {
            return $this->api_checkInfo(420);
         }
         $bin = $this->api->download_image($image, $article, $this->lang, 'all', $download, $testmode);
         $this->api_checkInfo();
         if(empty($bin)) {
            $this->api_checkInfo(500);
         }
         if($this->status['status'] === 200) {

            // save file
            file_put_contents($file, $bin);
            chmod($file, 0664);
            $wp_filetype = wp_check_filetype($file);
            $settings['attachment']['post_mime_type'] = $wp_filetype['type'];

            // save file in db
            $attach_id = wp_insert_attachment($settings['attachment'], $file, 0);
            if(empty($attach_id) && file_exists($file)) {
               unlink($file);
               return $this->api_checkInfo(412);
            }
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata( $attach_id,  $attach_data );

            // save image alt-text
            update_metadata('post', $attach_id, '_wp_attachment_image_alt', $settings['_image_alt']);

            // agency information about this image
            update_metadata('post', $attach_id, '_'.self::domain.'_image_data', $settings['_data']);
            $this->status['info'] = __('File downloaded successfully', self::domain);
            return;
         }
         
      }
      
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Set image-settings for WordPress
    * @param array $mediainfo
    * @param boolean|int $download
    * @param boolean|string $mediaid
    * @return array
    */
   private function api_downloadImage_settings($mediainfo, $download = null, $mediaid = null) {
      $return = array();
      
      $id = ($download) ? $download : $mediaid;
      $testmode = ($this->option_testmodus && (isset($this->config['api_testmode']) && $this->config['api_testmode'] == 'false') ? false : true);
      $ifTestmode = ($testmode===TRUE) ? '-'.time() : '';
      
      $upload_dir = wp_upload_dir();
      $image = $this->format_string($mediainfo['title'].'-'.$id.'-panthermedia'.$ifTestmode.'.jpg');
      $file = $upload_dir['path'].DIRECTORY_SEPARATOR.$image;
      
      $excerpt = '<a href="'.$mediainfo['image_page'].'" target="_blank">&copy; PantherMedia.net / '
              .$mediainfo['metadata']['author_username'].'</a>';
      
      $content = $mediainfo['metadata']['description'];
      if(empty($content)) {
         $content = $mediainfo['metadata']['keywords'];
      }
      $return['attachment'] = array(
         'guid' => $file,
         'post_mime_type' => 'image/jpeg',
         'post_title' => $mediainfo['title'],
         'post_content' => (!empty($content) ? $content : ''), // description | keywords
         'post_excerpt' => $excerpt, // label
         'post_status' => 'inherit'
      );
      
      $return['_image_alt'] = $mediainfo['title'];
      
      $return['_data'] = array(
          'id' => $mediainfo['media']['id'],
          'download' => $download,
          'name' => 'PantherMedia Stock Photo',
          'author' => $mediainfo['metadata']['author_username'],
          'page' => $mediainfo['image_page'],
      );
      
      return $return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Get search filter from panthermedia
    * @return boolean
    */
   private function api_getSearchFilter() {
      $r = $this->api->get_search_filter();

      if (isset($r['stat']) && $r['stat'] == 'ok' && isset($r['filters']['filter'])) {
         $path = $this->getFolder('cache');
         $file = $path.'/filter.json';
         file_put_contents($file, json_encode($r['filters']['filter']));
         chmod($file, 0664);
         return true;
      }
      return;
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Get user langs for API
    * @return array
    */
   private function api_getLang() {
      $path = $this->getFolder('cache');
      $file = $path.'/lang.json';
      
      if(!file_exists($file) || filemtime($file) < (time()-$this->ageCachefileLang) ) {
         $post_data = array('content_type' => 'application/json');
         $r = $this->api->request('get-user-content-langs', $post_data, null, $this->is_https());

         if(isset($r['stat']) && $r['stat'] == 'ok') {
            file_put_contents($file, json_encode($r['languages']['lang']));
            chmod($file, 0664);
         }
      }
      
      $return = array('en');
      if(file_exists($file)) {
         $array = json_decode(file_get_contents($file) , true );
         $return = array();
         foreach($array AS $arr) {
            $return[] = $arr['id'];
         }
      }
      
      return $return;
   }
   
   /** ####################################################################################################
    * End of API-Functions
    */

//------------------------------------------------------------------------------------------------------
   /** ####################################################################################################
    * End of Media-TAB-Functions
    */
   
   /**
    * Return plugin TAB links
    * @param array $tabs
    * @return array
    */
   public function load_media_tab_link($tabs) {
      $tabs[self::domain . '_mymedia'] = self::name . ' - ' . __('My images', self::domain);
      $tabs[self::domain . '_search'] = self::name . ' - ' . __('Search', self::domain);
      return $tabs;
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Set style and content for SEARCH
    */
   public function tab_iframe_search() {
      $this->load_style_and_scripts(false);

      // load iframe function
      wp_iframe(array(&$this, 'admin_tabmenu_media'));
   }

//------------------------------------------------------------------------------------------------------
   
   /**
    * Set style and content for MYMEDIA
    */
   public function tab_iframe_mymedia() {
      $this->load_style_and_scripts(false);

      // load iframe function
      wp_iframe(array(&$this, 'admin_tabmenu_mymedia'));
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Media-Tab Frame for search
    */
   public function admin_tabmenu_media() {
      $this->admin_menu_media('_tab');
   }
   
//------------------------------------------------------------------------------------------------------
   
   /**
    * Media-Tab Frame for my images
    */
   public function admin_tabmenu_mymedia() {
      $this->admin_menu_mymedia('_tab');
   }
}
