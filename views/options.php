<?php
// $Author: Steffen Blaszkowski $
// $Date: 2017-06-21 16:53 +0200 $
// $Id: options.php 39:123ed12f5264 2017-06-21 16:53 +0200 steffen $
// $Revision: 39:123ed12f5264 $
// $Lastlog: option testmode and deactivate filter ranking $

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
?>

<div class="wrap">
   <h1><?php echo self::name . ' &#8250; ' . __('Settings', self::domain); ?></h1>
   
   <!-- notice -->
   <div class="panthermedia_loading">
      <div class="panthermedia_loading_notice">
         <h2>
            <?php _e('Please wait', self::domain); ?>
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
   $file = $this->_path . '/cache/filter.json';
   if(!file_exists($file)) {
      echo '<input type="hidden" id="getFilter">';
   }
   ?>
   <!-- tabbar -->
   <div class="panthermedia_tab">
      <label>
         <input type="radio" class="panthermedia_tab_radio" name="pm_tab_option" value="general" checked />
         <?php _e('General', self::domain); ?>
      </label>
      
      <?php if(!file_exists($file)) { ?>
      <label>
         <input type="radio" class="panthermedia_tab_radio" name="pm_tab_option" value="getFilter" />
         <?php _e('Get filter', self::domain); ?>
      </label>
      <?php } ?>
      
      <?php if(file_exists($file)) { ?>
      <label>
         <input type="radio" class="panthermedia_tab_radio" name="pm_tab_option" value="filter" />
         <?php _e('Search filter', self::domain); ?>
      </label>
      <?php } ?>
   </div>
   <hr />
   
   
   <!-- general -->
   <div class="panthermedia_tab_div panthermedia_tab_div_general">
      <form action="admin-post.php" method="POST" onsubmit="return false;">
         <table class="form-table">
            <tbody>
               <tr>
                  <th>
                     <label><?php _e('Cache', self::domain); ?></label>
                  </th>
                  <td>
                     <button class="button-secondary panthermedia_clear_cache">
                        <?php _e('Clear cache', self::domain); ?>
                     </button>
                     <p>
                        <?php
                        $size = $this->usedMediaCache();
                        echo __('Space used', self::domain).': '.$size;
                        ?>
                     </p>
                  </td>
               </tr>
               <tr>
                  <th>
                     <label for="role_to_use_add_photos"><?php _e('Role', self::domain); ?></label>
                  </th>
                  <td>
                     <select id="role_to_use_add_photos" name="role_to_use_add_photos" class="regular-text">
                        <option value="administrator" <?php echo (($this->config['role_to_use_add_photos'] == 'administrator') ? 'selected="selected"' : ''); ?>><?php _e('Administrator', self::domain); ?></option>
                        <option value="editor" <?php echo (($this->config['role_to_use_add_photos'] == 'editor') ? 'selected="selected"' : ''); ?>><?php _e('Editor', self::domain); ?></option>
                        <option value="author" <?php echo (($this->config['role_to_use_add_photos'] == 'author') ? 'selected="selected"' : ''); ?>><?php _e('Author', self::domain); ?></option>
                     </select>
                     <p><?php _e('Smallest role to use the add-function?', self::domain); ?></p>
                     <!-- Kleinst benötigste Rolle zum verwenden der Hinzufügen-Funktion -->
                  </td>
               </tr>
               <tr>
                  <th>
                     <label for="private_or_public"><?php _e('Private or Public', self::domain); ?></label>
                  </th>
                  <td>
                     <select id="private_or_public" name="private_or_public" class="regular-text">
                        <option value="private" <?php echo (($this->config['private_or_public'] == 'private') ? 'selected="selected"' : ''); ?>><?php _e('Private', self::domain); ?></option>
                        <option value="public" <?php echo (($this->config['private_or_public'] == 'public') ? 'selected="selected"' : ''); ?>><?php _e('Public', self::domain); ?></option>
                     </select>
                     <p>
                        <?php echo sprintf( __('Private: Each member has their own %s account', self::domain), self::name ); ?><br />
                        <?php echo sprintf( __('Public: All members use the same %s account', self::domain), self::name ); ?>
                     </p>
                     <!-- Private: Jedes Mitglied hat seinen eigenen Panthermedia-Account -->
                     <!-- Public: Alle Mitglieder verwenden den gleichen Panthermedia-Account -->
                  </td>
               </tr>
               <?php if($this->option_testmodus) { ?>
               <tr>
                  <th>
                     <label for="api_testmode"><?php _e('Using test mode', self::domain); ?></label>
                  </th>
                  <td>
                     <select id="api_testmode" name="api_testmode" class="regular-text">
                        <option value="true" <?php echo (($this->config['api_testmode'] == 'true') ? 'selected="selected"' : ''); ?>><?php _e('Yes', self::domain); ?></option>
                        <option value="false" <?php echo (($this->config['api_testmode'] == 'false') ? 'selected="selected"' : ''); ?>><?php _e('No', self::domain); ?></option>
                     </select>
                     <p>
                        <?php _e('If YES, the downloaded image has a watermark and no deposit is settled.', self::domain); ?><br />
                        (<?php _e('Nevertheless you see the info how much deposit would be settled', self::domain); ?>)<br />
                        <?php echo sprintf(__('Only images from %s can be downloaded in testmode.', self::domain), self::name); ?>
                     </p>
                  </td>
               </tr>
               <?php } ?>
               <tr>
                  <th>
                     <label for="age_api_token"><?php _e('API token valid until', self::domain); ?></label>
                  </th>
                  <td>
                     <input type="number" name="age_api_token" id="age_api_token" min="1" max="90" value="<?php echo $this->config['age_api_token']; ?>">
                     <p>
                        <?php _e('Duration of an API token in days', self::domain); ?>
                     </p>
                     <!-- Dauer der Gültigkeit eines API Token in Tagen -->
                  </td>
               </tr>
            </tbody>
         </table>
      </form>
      <p class="submit">
         <button class="button-primary panthermedia_settings_save" data-type="general">
            <?php _e('Save', self::domain); ?>
         </button>
         <button class="button-secondary panthermedia_settings_save" data-type="setDefault" data-check="true">
            <?php _e('Reset all settings to default', self::domain); ?>
         </button>
      </p>
   </div>
   <!-- end general -->
   
   
   <!-- getFilter -->
   <?php if(!file_exists($file)) { ?>
   <div class="panthermedia_tab_div panthermedia_tab_div_getFilter">
      <form action="admin-post.php" method="POST" onsubmit="return false;"></form>
      <p class="submit">
         <button class="button-primary panthermedia_settings_save" data-type="getFilter">
            <?php echo sprintf(__('Get search filter from %s', self::domain), self::name); ?>
         </button>
      </p>
   </div>
   <?php } ?>
   <!-- end getFilter -->
   
   
   <!-- filter -->
   <?php 
   if(file_exists($file)) {
      $json = file_get_contents($file);
      $filter = json_decode($json, true);
      ?>
   <div class="panthermedia_tab_div panthermedia_tab_div_filter">
      <form action="admin-post.php" method="POST" onsubmit="return false;">
         
         <table class="form-table panthermedia_option_table">
            <thead>
               <tr>
                  <th><?php _e('Option', self::domain); ?></th>
                  <th><?php _e('Show', self::domain); ?></th>
                  <th><?php _e('Default', self::domain); ?></th>
               </tr>
            </thead>
            <tbody>
               
               <?php
         if(is_array($filter)) {
            foreach($filter AS $f) {
               if(in_array($f['name'], $this->deactivate_searchfilters)) {
                  continue;
               }
               $show = (isset($this->config['filter'][$f['name']]['show'] ) ? $this->config['filter'][$f['name']]['show'] : 'true');
            ?>
            <tr>
               <th><?php _e($f['name'], self::domain.'-search'); ?></th>
               <td>
                  <select name="filter-<?php echo $f['name']; ?>-show" class="regular-text">
                     <option value="true" <?php echo (($show=='true')?'selected="selected"':''); ?>><?php _e('Yes', self::domain); ?></option>
                     <option value="false" <?php echo (($show=='false')?'selected="selected"':''); ?>><?php _e('No', self::domain); ?></option>
                  </select>
               </td>
               <td>
                  <?php
                  
                  
                  // single values
                  if($f['type'] == 'single value') {
                     if($f['values'] > 1) {
                        foreach($f['value'] AS $val) {
                           if(isset($this->config['filter'][$f['name']]['default'])) {
                              $def = $this->config['filter'][$f['name']]['default'];
                              $checked = ($def == $val['name']) ? 'checked="checked"' : '';
                           } else {
                              $checked = ($f['default'] == $val['name']) ? 'checked="checked"' : '';
                           }
                           ?>
                  <label>
                     <input type="radio" name="filter-<?php echo $f['name']; ?>-default" 
                            value="<?php echo $val['name']; ?>" <?php echo $checked; ?>>
                     <?php _e($val['desc'], self::domain.'-search'); ?>
                  </label>
                           <?php
                        }
                     } else {
                        $def = $f['default'];
                        if(isset($this->config['filter'][$f['name']]['default'])) {
                           $def = $this->config['filter'][$f['name']]['default'];
                        }
                        $checked = ($def == $f['value']['name']) ? 'checked="checked"' : '';
                        ?>
                  <label>
                     <input type="checkbox" name="filter-<?php echo $f['name']; ?>-default" 
                            value="<?php echo $f['value']['name']; ?>" <?php echo $checked; ?>>
                     <?php _e($f['value']['desc'], self::domain.'-search'); ?>
                  </label>
                        <?php
                     }
                  }
                  
                  
                  // strings
                  else if($f['type'] == 'string') {
                     $def = $f['default'];
                     if(isset($this->config['filter'][$f['name']]['default'])) {
                        $def = $this->config['filter'][$f['name']]['default'];
                     }
                     ?>
                  <label>
                     <input type="text" name="filter-<?php echo $f['name']; ?>-default" 
                            value="<?php echo $def; ?>" placeholder="<?php _e($f['desc'], self::domain.'-search'); ?>">
                  </label>
                     <?php
                  }
                  
                  
                  // hexcode
                  else if($f['type'] == 'hexcode') {
                     $def = $f['default'];
                     if(isset($this->config['filter'][$f['name']]['default'])) {
                        $def = $this->config['filter'][$f['name']]['default'];
                     }
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
                     <input id="panthermedia_hexcode_input" type="checkbox" name="filter-<?php echo $f['name']; ?>-status" value="1" <?php echo $checked; ?>>
                     <span id="panthermedia_hexcode_span" data-active="<?php echo $active; ?>" data-inactive="<?php echo $inactive; ?>"><?php echo $span; ?></span>
                  </label>
                  <label>
                     <input id="panthermedia_hexcode_color" type="<?php echo $type; ?>" name="filter-<?php echo $f['name']; ?>-default" 
                            data-value="<?php echo $def; ?>" value="<?php echo $def; ?>" placeholder="<?php echo $f['desc']; ?>">
                  </label>
                     <?php
                  }
                  
                  
                  // multi value
                  else if(strpos($f['type'], 'multi value') > -1) {
                     $def = explode(',', $f['default']);
                     if(isset($this->config['filter'][$f['name']]['default'])) {
                        $def = explode(',',$this->config['filter'][$f['name']]['default']);
                     }
                     if($f['values'] > 1) {
                        foreach($f['value'] AS $val) {
                           $checked = (in_array($val['name'],$def)) ? 'checked="checked"' : '';
                           ?>
                  <label>
                     <input type="checkbox" name="filter-<?php echo $f['name']; ?>-default" 
                            value="<?php echo $val['name']; ?>" <?php echo $checked; ?>>
                     <?php _e($val['desc'], self::domain.'-search'); ?>
                  </label>
                           <?php
                        }
                     } else {
                        if(isset($this->config['filter'][$f['name']]['default'])) {
                           $def = $this->config['filter'][$f['name']]['default'];
                           $checked = ($def == $val['name']) ? 'checked="checked"' : '';
                        } else {
                           $checked = ($f['default'] == $f['value']['name']) ? 'checked="checked"' : '';
                        }
                        ?>
                  <label>
                     <input type="checkbox" name="filter-<?php echo $f['name']; ?>-default" 
                            value="<?php echo $f['value']['name']; ?>" <?php echo $checked; ?>>
                     <?php _e($f['value']['desc'], self::domain.'-search'); ?>
                  </label>
                        <?php
                     }
                  }
                  ?>
                  
                  
               </td>
            </tr>
            <?php
            }
         } ?>
               
            </tbody>
         </table>
         
      </form>
      <p class="submit">
         <button class="button-primary panthermedia_settings_save" data-type="filter">
            <?php _e('Save', self::domain); ?>
         </button>
         <?php
         $filterSettings = $this->_path . '/cache/filter_settings.json';
         $disabled = "disabled";
         if(file_exists($filterSettings)) {
            $disabled = "";
         }
         ?>
         <button class="button-secondary panthermedia_settings_save" data-type="filterDelete" data-check="true" <?php echo $disabled; ?>>
            <?php _e('Reset to default filter settings', self::domain); ?>
         </button>
      </p>
   </div>
         <?php
   } ?>
   <!-- end filter -->
   
   
</div>