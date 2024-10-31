<?php
// $Author: Steffen Blaszkowski $
// $Date: 2017-06-23 11:47 +0200 $
// $Id: search.php 42:6ae332118eef 2017-06-23 11:47 +0200 steffen $
// $Revision: 42:6ae332118eef $
// $Lastlog: fix rm and premium and define pm_cache $

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


if(defined('PM_CACHE') && PM_CACHE === FALSE) {
   echo '<div class="panthermedia_without_cache">';
   echo __('Cache is deactivated', self::domain);
   echo '</div>';
}

?>
<div class="panthermedia_wrap<?php echo $data; ?>">
   <h1><?php echo self::name . ' &#8250; ' . __('Search', self::domain); ?> <span class="pm_media_detail_h1"> &#8250; <?php _e('Detail', self::domain); ?></span></h1>

   <!-- notice -->
   <div class="panthermedia_loading">
      <div class="panthermedia_loading_notice">
         <h2>
            <font><?php _e('Please wait', self::domain); ?></font>
            <button>&#10006;</button>
         </h2>
         <hr />
         <div>
            <span class="panthermedia_notice_message"></span>
            <span class="panthermedia_notice_loader">
               <img src="<?php echo $this->_url; ?>/files/loader.gif" width="15" height="15" />
               <?php _e('Loading', self::domain); ?>
            </span>
         </div>
      </div>
      <div class="panthermedia_loading_backdrop"></div>
   </div>
   
   <?php
   
   // filter array
   $adv_search = array();
   $file_search = $this->_path.'/cache/filter_search.json';
   if(file_exists($file_search)) {
      $adv_search = json_decode(file_get_contents( $file_search ),true );
   }
   
   // config filter
   $filterSettings = false;
   if(isset($this->config['filter']) && is_array($this->config['filter'])) {
         $filterSettings = $this->config['filter'];
   }
   
   // user default filter
   $userFilter = $this->getSearchSettings();
   ?>
   <div class="pm_media_search">
      <input type="hidden" name="ajaxStart" value="1" data-type="media" id="pm_ajax_start">
      <div class="panthermedia_search_header">
         
         <?php if(is_array($adv_search) && !empty($adv_search)) { ?>
         <label class="panthermedia_search_header_element" id="pm_search_adv">
            <?php _e('Advanced Search', self::domain); ?>
            <span></span>
         </label>
         <?php } ?>
         
         <div class="panthermedia_search_header_element">
            <input type="text" placeholder="<?php esc_attr_e('Search for Photos, Vectors and Illustrations', self::domain); ?>" 
                   class="pm_search_input" name="mpp_search_input" value="<?php echo isset($userFilter['q']) ? $userFilter['q'] : ''; ?>" />
         </div>
         
         <?php if(isset($adv_search['sort'])) {
            $arr = $adv_search['sort'];
            $def = (isset($filterSettings['sort']['default']) ? $filterSettings['sort']['default'] : $arr['default']);
            ?>
         <div class="panthermedia_search_header_element">
            <select name="filter-sort" id="filter-sort">
               <?php
               foreach($arr['value'] AS $key => $value) {
                  $checked = ($def == $key) ? 'selected="selected"' : '';
                  if(isset($userFilter['sort'])) {
                     if($key == $userFilter['sort']) {
                     $checked = 'selected="selected"';
                     } else {
                        $checked = '';
                     }
                  }
                  ?>
               <option value="<?php echo $key; ?>" <?php echo $checked; ?>>
                  <?php _e($value, self::domain.'-search'); ?>
               </option>
               <?php
               }
               ?>
            </select>
         </div>
         <?php } ?>
         
         <div class="panthermedia_search_header_element">
            <?php
            $perPage = (isset($userFilter['per_page']) ? $userFilter['per_page'] : $this->getPerPage());
            ?>
            <label for="pm_per_page"><?php _e('Media per page', self::domain); ?></label>
            <input type="number" min="10" max="40" value="<?php echo $perPage; ?>" id="pm_per_page" />
         </div>
         <div class="panthermedia_search_header_element">
            <button class="button-primary pm_search_button" data-type="search"><?php _e('Search', self::domain); ?></button>
         </div>
      </div>
      <br class="pm_search_clear" />
      <div class="panthermedia_search_advanced">
         <form id="pm_search_filter" onsubmit="return false;">
         <?php
         foreach($adv_search AS $name => $arr) {
            if($name === 'sort' || in_array($name, $this->deactivate_searchfilters)) {
               continue;
            }
            
            $def = (isset($filterSettings[$name]['default']) ? $filterSettings[$name]['default'] : $arr['default']);
            $multiFilter = array();
            if(isset($userFilter['data'])) {
               $found = 0;
               foreach($userFilter['data'] AS $arrUF) {
                  if($arrUF['name'] == 'filter-'.$name) {
                     if($found>0) {
                        if(empty($multiFilter)) {
                           $multiFilter[] = $def;
                        }
                        $multiFilter[] = $arrUF['value'];
                     } else {
                        $def = $arrUF['value'];
                     }
                     $found++;
                  }
               }
            }
		  
            if(!$filterSettings || (isset($filterSettings[$name]['show']) && $filterSettings[$name]['show'] == 'true' )) {
            ?>
            <div class="panthermedia_search_advanced_element">
               <div>
                  <span></span>
                  <?php _e($name, self::domain.'-search'); ?>
               </div>
			
               <?php 
               if($arr['type'] == 'string') {
                     ?>
               <label>
                  <input type="text" name="filter-<?php echo $name; ?>" 
                        value="<?php echo $def; ?>" placeholder="<?php _e($arr['desc'], self::domain.'-search'); ?>">
               </label>
                     <?php
               }
			
			
               if($arr['type'] == 'hexcode') {
                  $checked = 'checked="checked"';
                  $active = __('Active', self::domain);
                  $inactive = __('Inactive', self::domain);
                  $span = $active;
                  $type = "color";
                  if(empty($def)) {
                     $checked = "";
                     $span = $inactive;
                     $type = "hidden";
                  }
                  ?>
               <label>
                  <input id="panthermedia_hexcode_input" type="checkbox" value="1" <?php echo $checked; ?>>
                  <span id="panthermedia_hexcode_span" data-active="<?php echo $active; ?>" data-inactive="<?php echo $inactive; ?>"><?php echo $span; ?></span>
               </label>
               <label>
                  <input id="panthermedia_hexcode_color" type="<?php echo $type; ?>" name="filter-<?php echo $name; ?>" 
                         data-value="<?php echo $def; ?>" value="<?php echo $def; ?>" placeholder="<?php echo $arr['desc']; ?>">
               </label>
                     <?php
               }
			
			
               if($arr['type'] == 'single value' && isset($arr['value'])) {				   
                  $type = (count($arr['value']) == 1) ? "checkbox" : "radio";
                  foreach($arr['value'] AS $key => $value) {
                     $checked = ($def == $key) ? 'checked="checked"' : '';
                     ?>
               <label>
                  <input type="<?php echo $type; ?>" name="filter-<?php echo $name; ?>" 
                         value="<?php echo $key; ?>" <?php echo $checked; ?>>
                  <?php _e($value, self::domain.'-search'); ?>
               </label>
                     <?php
                  }
               }
			
			
               if($arr['type'] == 'multi value, comma-separated') {
                  $def = explode(',', $arr['default']);
                  if(isset($filterSettings[$name]['default'])) {
                     $def = explode(',', $filterSettings[$name]['default']);
                  }
                  foreach($arr['value'] AS $key => $value) {
                     $checked = (in_array($key, $def)) ? 'checked="checked"' : '';
                     
                     if(isset($userFilter['data']) && !empty($multiFilter)) {
                        if(in_array($key, $multiFilter)) {
                           $checked = 'checked="checked"';
                        }
                        else {
                           $checked = '';
                        }
                     }
                     ?>
               <label>
                  <input type="checkbox" name="filter-<?php echo $name; ?>" 
                         value="<?php echo $key; ?>" <?php echo $checked; ?>>
                  <?php _e($value, self::domain.'-search'); ?>
               </label>
                     <?php
                  }
               }
               ?>
			
            </div>
            <?php
            }
		  
            // show false
            else { ?>
               <div class="panthermedia_search_advanced_hidden_element">
                  <input type="hidden" name="filter-<?php echo $name; ?>" value="<?php echo $def; ?>">
               </div>
               <?php
           }
         }
         ?>
         </form>
     </div>
     <div class="panthermedia_search_result">
         <div class="pm_status_info"></div>
         <div class="panthermedia_paging"></div>
         <div class="pm_search_images"></div>
         <div class="panthermedia_paging panthermedia_paging_bottom"></div>
     </div>
      <br class="pm_search_clear" />
	  
   </div>
   <div class="pm_media_detail"></div>
   
</div>