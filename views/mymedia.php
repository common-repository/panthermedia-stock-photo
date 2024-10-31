<?php
// $Author: Steffen Blaszkowski $
// $Date: 2018-01-16 10:01 +0100 $
// $Id: mymedia.php 62:f0abb1534f36 2018-01-16 10:01 +0100 steffen $
// $Revision: 62:f0abb1534f36 $
// $Lastlog: error info $

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
<div class="panthermedia_wrap<?php echo $data; ?>">
   <h1><?php echo self::name . ' &#8250; ' . __('My Media', self::domain); ?></h1>
   <input type="hidden" name="ajaxStart" value="1" data-type="mymedia" id="pm_ajax_start">

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
   
   <div class="pm_media_search">
      <div class="panthermedia_search_header">
         <div class="panthermedia_search_header_element">
            <label for="pm_per_page"><?php _e('Media per page', self::domain); ?></label>
            <input type="number" min="10" max="40" value="<?php echo $this->getPerPage(); ?>" id="pm_per_page" />
         </div>
         <div class="panthermedia_search_header_element" id="corporateImages">
            <select>
               <option value="yes"><?php _e('From all users', self::domain); ?></option>
               <option value="no" selected><?php _e('Only from me', self::domain); ?></option>
            </select>
         </div>
         <div class="panthermedia_search_header_element">
            <button class="button-primary pm_search_button" data-type="mymedia"><?php _e('Search', self::domain); ?></button>
         </div>
      </div>
      <br class="pm_search_clear" />

      <div id="panthermedia_error_info"></div>
      
      <div class="panthermedia_search_result">
         <div class="panthermedia_paging"></div>
         <div class="pm_search_images"></div>
         <div class="panthermedia_paging panthermedia_paging_bottom"></div>
      </div>
      <br class="pm_search_clear" />
   </div>
   <div class="pm_media_detail"></div>
   
</div>