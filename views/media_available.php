<?php
// $Author: Steffen Blaszkowski $
// $Date: 2017-06-06 14:02 +0200 $
// $Id: media_available.php 28:02a4ba4cad27 2017-06-06 14:02 +0200 steffen $
// $Revision: 28:02a4ba4cad27 $
// $Lastlog: fix translate and readme $

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

<hr />

<!-- headline -->
<div class="panthermedia_detail_headline">
   <div>
      <button class="button-secondary panthermedia_detail_back"><?php _e('Back', self::domain); ?></button>
      <h1>
         #<?php echo $info['media']['id']; ?> - <?php echo $info['title']; ?>
      </h1>
      <button class="button-secondary panthermedia_download_reload"><?php _e('Reload', self::domain); ?></button>
   </div>
</div>
<br class="pm_search_clear" />
<!-- end headline -->


<div class="panthermedia_detail">
   
   <!-- image info -->
   <div class="panthermedia_detail_image">
      <a href="<?php echo $info['image_page']; ?>" target="_blank">
         <img src="<?php echo $info['media']['preview_url']; ?>" class="imgDetail">
      </a>
      <div class="pnlHeadline2">
         <h2>
            <a href="<?php echo $info['image_page']; ?>" target="_blank">
               <?php echo $info['title']; ?>
            </a>
         </h2>
      </div>
   </div>
   <!-- end image info -->

   
   <div class="panthermedia_detail_right">
      
      <!-- detail info -->
      <div class="panthermedia_detail_image_info">
         <h3 class="plain">
            <?php echo __('Author', self::domain).': '; ?>
            <a href="<?php echo $info['user_page']; ?>" target="_blank">
               <?php echo $info['metadata']['author_username']; ?>
            </a>
         </h3>
         <h4>
            <?php 
            if(isset($info['media']['mime_type']) && $info['media']['mime_type'] == 'image/eps') {
               echo __('Vector', self::domain).' ';
            } else {
               echo __('Stock photo', self::domain).' ';
            }
            echo __('Image-id', self::domain).': ';
            echo $info['media']['id'];
            ?>
         </h4>

         <!-- commercial and editorial -->
         <div>
            <?php
            if(isset($info['metadata']['editorial']) && $info['metadata']['editorial'] == 'yes') {
               echo __('Editorial use', self::domain);
            } else if(isset($info['metadata']['editorial']) && $info['metadata']['editorial'] == 'no') {
               echo __('Commercial and editorial use', self::domain);
            }
            ?>
         </div>
         <!-- end commercial and editorial -->

         <!-- model and property -->
         <div>
            <?php
            if(isset($info['metadata']['model_release']) && $info['metadata']['model_release'] == 'yes') {
               echo __('Modelrelease exist', self::domain);
            } else {
               echo __('Modelrelease does not exist', self::domain);
            }
            echo " / ";
            if(isset($info['metadata']['property_release']) && $info['metadata']['property_release'] == 'yes') {
               echo __('Propertyrelease exist', self::domain);
            } else {
               echo __('Propertyrelease does not exist', self::domain);
            }
            ?>
         </div>
         <!-- end model and property -->
         
         
      </div>
      <!-- end detail info -->
      
      
      <div class="panthermedia_detail_licenses">
         <div class="panthermedia_download_info">
            <?php echo __('Download this picture in the following sizes', self::domain).':'; ?>
         </div>
         
         <table cellspacing="0" cellpadding="0">
            <thead>
               <tr>
                  <th align="left"><?php _e('Size', self::domain); ?></th>
                  <th align="left"><?php _e('Type', self::domain); ?></th>
                  <th align="right">&nbsp;</th>
               </tr>
            </thead>
            <tbody>
               <?php
               $array = $info['mymedia']['media'];
               if($info['mymedia']['total']==1) {
                  $array = array();
                  $array[] = $info['mymedia']['media'];
               }

               foreach($array AS $prod) {
                  $MediaURL = $this->getMediaLink($prod['id-download']);
                  //echo "<pre>"; print_r( $uploadURL ); echo "</pre>";
                  ?>
               <tr>
                  <td><?php echo $this->image_dimension($prod['width'], $prod['height']); ?></td>
                  <td><?php echo $prod['mimetype']; ?></td>
                  <td align="right">
                     <?php if($prod['mimetype'] == 'image/jpeg') { ?>
                        <?php if($MediaURL) { ?>
                        <a class="button-secondary" href="<?php echo $MediaURL; ?>" target="_blank">
                           <?php echo __('Show media', self::domain); ?>
                        </a>
                        <?php } else { ?>
                        <button class="button-primary panthermedia_download_image" 
                                data-download="<?php echo $prod['id-download']; ?>" data-media="<?php echo $prod['id-media']; ?>">
                           <?php echo __('Download', self::domain); ?>
                        </button>
                        <?php } ?>
                     <?php } ?>
                  </td>
               </tr>
                  <?php
               }
               ?>
            </tbody>
         </table>
      </div>
   </div>
   
   <br class="pm_search_clear" />
</div>

