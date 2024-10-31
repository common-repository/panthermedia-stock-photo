<?php
// $Author: Steffen Blaszkowski $
// $Date: 2017-06-22 15:48 +0200 $
// $Id: media_licenses.php 41:cc7fae54939d 2017-06-22 15:48 +0200 steffen $
// $Revision: 41:cc7fae54939d $
// $Lastlog: small fix my media corporate and licence type and adding new translations $

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

<div class="panthermedia_license">
   <table cellspacing="0" cellpadding="0">
      <thead>
         <tr>
            <th>&nbsp;</th>
            <th><?php _e('Media info', self::domain); ?></th>
            <th><?php _e('License info', self::domain); ?></th>
            <th>&nbsp;</th>
         </tr>
      </thead>
      <tbody>
         <?php
   foreach($media AS $did => $arr) {
      ?>
         <tr>
            <td>
               <a href="#<?php echo $arr['info']['id-media']; ?>" class="panthermedia_action" 
                  data-type="available">
                  <img src="<?php echo $arr['thumb_url']; ?>" title="<?php echo esc_attr($arr['title']); ?>" 
                    max-height="170" data-tooltip-pm="panthermedia_sticky_<?php echo $did; ?>" />
               </a>
            </td>
            <td>
               <?php
               echo __('Image-id', self::domain).' '.$arr['info']['id-media'].'<br />';
               echo __('Type of media', self::domain).': '.$this->mimetype($arr['info']['mimetype']).'<br />';
               echo $this->image_dimension($arr['info']['width'],$arr['info']['height']).'<br />';
               echo $this->image_info_mm($arr['info']['width'],$arr['info']['height']).'<br />';
               echo $this->image_info($arr['info']['width'],$arr['info']['height']).'<br />';
               ?>
            </td>
            <td>
               <?php
               $licence_info = explode(': ', $arr['info']['license_info']['license_type']);
               $counter = 0;
               if(count($licence_info) == 2) {
                  echo __($licence_info[0], self::domain).': ';
                  $licence_type = explode(',', $licence_info[1]);
                  foreach($licence_type AS $lt) {
                     ++$counter;
                     if($counter > 1) {
                        echo ", ";
                     }
                     echo __( trim($lt), self::domain);
                  }
               } else {
                  echo __($arr['info']['license_info']['license_type'], self::domain);
               }
               echo "<br />";
               
               $counter = 0;
               $legal_info = explode(',', $arr['info']['license_info']['legal_info']);
               foreach($legal_info AS $li) {
                  ++$counter;
                  if($counter > 1) {
                     echo ", ";
                  }
                  echo __( trim($li), self::domain);
               }
               ?>
            </td>
            <td>
               <?php if($arr['info']['mimetype'] != 'image/eps') { ?>
               <a href="#<?php echo $arr['info']['id-media']; ?>" class="button-primary panthermedia_action" 
                  data-type="download" data-download="<?php echo $arr['info']['id-download']; ?>">
                  <?php echo __('Download', self::domain); ?>
               </a><br />
               <?php } else { ?>
               <a href="#<?php echo $arr['info']['id-media']; ?>" class="button-primary panthermedia_action" 
                  data-type="detail">
                  <?php echo __('Download', self::domain); ?>
               </a><br />
               <?php } ?>
               
               <a href="#<?php echo $arr['info']['id-media']; ?>" class="button-secondary panthermedia_action" 
                  data-type="detail">
                  <?php echo __('Detail', self::domain); ?>
               </a><br />
               <a href="#<?php echo $arr['info']['id-media']; ?>" class="button-secondary panthermedia_action" 
                  data-type="available">
                  <?php echo __('Available sizes', self::domain); ?>
               </a><br />
               <a href="<?php echo $this->pmlinks['mydownloads']; ?>" class="button-secondary" target="_blank">
                  <?php echo __('License Proof', self::domain); ?>
               </a><br />
            </td>
         </tr>
    <?php 
   } ?>
      </tbody>
   </table>
</div>

<div id="panthermedia_sticky" class="stickytooltip">
    <div style="padding:5px">
    <?php
    foreach($media AS $did => $arr) {
    ?>
        <div id="panthermedia_sticky_<?php echo $did; ?>" class="atip">
            <div style="overflow: hidden;">
                <img src="<?php echo $arr['preview_url']; ?>" alt="<?php echo esc_attr($arr['title']); ?>" /><br />
            </div>
            <div style="float: left;">
                <?php echo $arr['title']; ?>
            </div>
            <br class="pm_search_clear" />
        </div>
    <?php
    }
    ?>
    </div>
</div>