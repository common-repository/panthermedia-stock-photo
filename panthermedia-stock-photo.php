<?php

// $Author: Steffen Blaszkowski $
// $Date: 2018-07-12 12:20 +0200 $
// $Id: panthermedia-stock-photo.php 75:2cb39b7b28fd 2018-07-12 12:20 +0200 steffen $
// $Revision: 75:2cb39b7b28fd $
// $Lastlog: update version 1.8.4 $

// https://codex.wordpress.org/Writing_a_Plugin#Updating_Your_Plugin

/**
 * PantherMedia Stock Photo
 * 
 * @author      PantherMedia GmbH - Steffen Blaszkowski
 * @copyright   2017 PantherMedia GmbH
 * @license     GPL-2.0+
 * 
 * @wordpress-plugin
 * Plugin Name: PantherMedia Stock Photo
 * Description: PantherMedia Stock Photo WordPress Plugin
 * Version: 1.8.4
 * Author: PantherMedia
 * Author URI: http://www.panthermedia.net
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: PM-WP-StockPhoto
 */
defined('ABSPATH') or die("No script kiddies please!");

if(!defined('CRLF')) { define('CRLF', "\r\n"); }

/**
 * Error-Output if WP_DEBUG and WP_DEBUG_DISPLAY is TRUE
 * @param Exception $e
 * @param string $calledFrom
 * @return void
 */
function PantherMediaStockPhoto_debug($e, $calledFrom = '') {
   if (WP_DEBUG){
      
      $msg = CRLF . $calledFrom . CRLF . $e->getCode() . ' - ' . $e->getMessage() . CRLF . 
              ' Line ' . $e->getLine() . ' File ' . $e->getFile() . CRLF . CRLF . $e->getTraceAsString();
      
      PantherMediaStockPhoto_writeDebug($msg);
      
      if(WP_DEBUG_DISPLAY){
         if (defined('DOING_AJAX') && DOING_AJAX) {
            return $msg;
         }
         trigger_error( nl2br($msg), E_USER_WARNING);
      }
   }
}

/**
 * Write log if WP_DEBUG and WP_DEBUG_LOG is TRUE
 * @param string|array $msg
 * @param string $logid
 * @return void
 */
function PantherMediaStockPhoto_writeDebug($msg, $logid = null) {
   if(WP_DEBUG_LOG) {
      if ($logid === null) {
         $logid = 'debug';
      }
      $path = dirname(__FILE__) . '/logs';
      if (!is_dir($path)) {
         mkdir($path, 0775);
      } else {
         chmod($path, 0775);
      }
      $file = $path . '/' . $logid . '.log';
      if (is_file($file) && filesize($file) > 1 * 1024 * 1024) {
         unlink($file);
      }

      $content = '';
      if(file_exists($file)) {
         $content = file_get_contents($file);
      }

      //file_put_contents($file, date('Y-m-d H:i:s') . "\r\n" . print_r($msg, true) . "\r\n\r\n", FILE_APPEND);
      $newMessage = date('Y-m-d H:i:s') . "\r\n" . print_r($msg, true) . "\r\n\r\n";
      file_put_contents($file, $newMessage . $content);
   }
}

require_once dirname(__FILE__) . '/classes/panthermedia-stock-photo.php';
try{
   new PantherMediaStockPhoto(__FILE__);
}catch(Exception $e) {
   PantherMediaStockPhoto_debug($e, __METHOD__);
}
