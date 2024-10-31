<?php
// $Author: Steffen Blaszkowski $
// $Date: 2017-06-27 09:28 +0200 $
// $Id: media_detail.php 48:db17e4759297 2017-06-27 09:28 +0200 steffen $
// $Revision: 48:db17e4759297 $
// $Lastlog: rm duration $

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
      <button class="button-secondary panthermedia_detail_reload"><?php _e('Reload', self::domain); ?></button>
   </div>
</div>
<br class="pm_search_clear" />
<!-- end headline -->

<?php
if(!$this->api_isLogged()) {
   ?>
<div class="notice notice-warning is-dismissible">
   <p>
      <?php echo sprintf(__('You are no longer logged into %s.', self::domain ), self::name); ?>
      <a href="<?php echo admin_url('admin.php?page=' . self::domain . '/classes/' . self::domain . '_login');?>"><?php _e('Account', self::domain) ?></a>
   </p>
</div>
<br class="pm_search_clear" />
   <?php
}
?>

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
      
      <?php
      // check fullsize
      $width = $info['media']['width'];
      $fullDownload = FALSE;
      if(isset($downloaded['width']) && $width == $downloaded['width'] && 
              !empty($downloaded['downloaded'][$width]['id-download'])) {
         $fullDownload = TRUE;
      }
      ?>
      
      <?php if($this->api_isLogged() && isset($info['licenses']['marking']) && !$fullDownload) {
         $link = '<a href="'.$this->pmlinks['mydownloads'].'" target="blank">'.__('Purchased images', self::domain).'</a>';
         ?>
      <div class="panthermedia_license_marking">
         <h4><?php _e('HiRes Download', self::domain); ?></h4>
         <button class="button-secondary panthermedia_buy_media" >
            <?php _e('Free Hires Download', self::domain); ?>
         </button>
         <div>
            <?php 
            echo __('This image can be licensed upon request later.', self::domain);
            echo sprintf(__('You can see a list of all your free layout downloads at %s', self::domain), $link);
            ?>
         </div>
      </div>
      <?php } ?>
      
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
               echo __('Modelrelease exists', self::domain);
            } else {
               echo __('Modelrelease does not exist', self::domain);
            }
            echo " / ";
            if(isset($info['metadata']['property_release']) && $info['metadata']['property_release'] == 'yes') {
               echo __('Propertyrelease exists', self::domain);
            } else {
               echo __('Propertyrelease does not exist', self::domain);
            }
            ?>
         </div>
         <!-- end model and property -->
         
         <?php
         if($info['options']['premium'] == 'yes' || $info['options']['rights_managed'] == 'yes') {
            $img_src = plugins_url(self::domain.'/files/premium.png');
            $img_title = __('Premium', self::domain);
            $text = __('This image is part of our higher priced Premium Collection.', self::domain);
            
            if($info['options']['rights_managed'] == 'yes') {
               $img_src = plugins_url(self::domain.'/files/rm.png');
               $img_title = __('Rights-Managed', self::domain);
               $text = __('This is a rights-managed (RM) image.', self::domain);
            }
            ?>
         <div class="panthermedia_premium_rm">
            <img src="<?php echo $img_src; ?>" title="<?php echo $img_title; ?>" alt="<?php echo $img_title; ?>">
            <div><?php echo $text; ?></div>
         </div>
         <br class="pm_search_clear" />
            <?php
         }
         ?>
      </div>
      <!-- end detail info -->
      
      
      <div class="panthermedia_detail_licenses">
         
         <?php 
         if($fullDownload) {
            ?>
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
               foreach($downloaded['downloaded'] AS $prod) {
                  $MediaURL = $this->getMediaLink($prod['id-download']);
                  //echo "<pre>"; print_r( $uploadURL ); echo "</pre>";
                  ?>
               <tr>
                  <td><?php echo $this->image_dimension($prod['width'], $prod['height']); ?></td>
                  <td><?php echo $prod['mimetype']; ?></td>
                  <td align="right">
                     <?php if($MediaURL) { ?>
                     <a class="button-secondary" href="<?php echo $MediaURL; ?>"  target="_blank">
                        <?php echo __('Show media', self::domain); ?>
                     </a>
                     <?php } else { ?>
                     <button class="button-primary panthermedia_download_image" 
                             data-download="<?php echo $prod['id-download']; ?>" data-media="<?php echo $prod['id-media']; ?>">
                        <?php echo __('Download', self::domain); ?>
                     </button>
                     <?php } ?>
                  </td>
               </tr>
                  <?php
               }
               ?>
            </tbody>
         </table>
            <?php
         } else {
         ?>
         
         <!-- method -->
         <div class="panthermedia_tab">
            <?php if(isset($info['licenses']['marking'])) { ?>
            <label>
                <input type="radio" class="panthermedia_tab_radio" name="pm_tab_option" value="marking" /> <?php _e('Marking', self::domain); ?>
            </label>
            <?php } ?>
            
            <label>
                <input type="radio" class="panthermedia_tab_radio" name="pm_tab_option" value="subscription" /> <?php _e('Subscription', self::domain); ?>
            </label>
            
            <label>
                <input type="radio" class="panthermedia_tab_radio" name="pm_tab_option" value="packet" /> <?php _e('Packet', self::domain); ?>
            </label>
            
            <label>
                <input type="radio" class="panthermedia_tab_radio" checked  name="pm_tab_option" value="credits" /> <?php _e('Credits', self::domain); ?>
            </label>
         </div>
         <!-- end method -->
        
         
         <?php
         if(isset($info['licenses']) && is_array($info['licenses'])) {
            foreach($info['licenses'] AS $type => $arr) {
               if($type == 'credits' || $type == 'credits_extended_rights') {
                  continue;
               }
               ?>
         <div class="panthermedia_tab_div panthermedia_tab_div_<?php echo $type; ?>">
               <?php
               $ucType = ucfirst($type); // packet or subscription
               
               if(!$this->api_isLogged()) {
                  echo sprintf(__('You are no longer logged into %s.', self::domain ), self::name);
               }
               else if($info['options'][$type] == 'yes' || $type == 'marking') {
                  if(empty($arr)) {
                     ?>
            <a class="button-primary" href="<?php echo $this->pmlinks['subscription']; ?>" target="_blank">
               <?php echo sprintf(__('Buy %s', self::domain), __($ucType, self::domain) ); ?>
            </a>
                  <?php }
               
                  foreach($arr AS $prod) {
                     $quota = '';
                     if($type == 'subscription' || $type == 'packet') {
                        $term = (isset($prod['term']) && !empty($prod['term']) ? '/'.$prod['term'] : '');
                        $quota = __('Your download-quota', self::domain).": ";
                        if($prod['total'] == 'inf') {
                           $quota .= __('Unlimited', self::domain).$term;
                        } else {
                           $quota .= $prod['remaining'].' ('.$prod['total'].$term.')';
                        }
                     }
                  ?>
            <div class="panthermedia_license_packets">
               <h4><?php echo $prod['info']; ?></h4>
                  <?php
                  if(!empty($quota)) {
                     echo "<div>$quota</div>";
                  }
                  ?>
               <div><?php echo __('Size', self::domain).": ".$prod['dimension']; ?></div>
               <div><?php echo $prod['inch']; ?></div>
               <button class="button-primary panthermedia_buy_media_full" 
                       data-id="<?php echo $info['media']['id']; ?>"
                       data-article="<?php echo $prod['article-id']; ?>">
                  <?php _e('Download', self::domain); ?>
               </button>
            </div>
                     <?php
                  }
               } else {
                  echo sprintf(__('This file is not available with %s', self::domain), __($ucType, self::domain) );
               }
                  ?>
         </div>
               <?php
            }
         } ?>
        
        
         <!-- credits -->
         <div class="panthermedia_tab_div panthermedia_tab_div_credits">
            <?php if(isset($info['licenses']['credits'])) { 
               $SizeDuration = __('Size', self::domain);
               
               if($info['options']['rights_managed'] == 'yes') {
                  $SizeDuration = __('Duration', self::domain);
               }
               ?>
            <table cellspacing="0" cellpadding="0">
               <thead>
                  <tr>
                     <th align="left"><?php echo $SizeDuration; ?></th>
                     <th align="left"><?php _e('Pixel', self::domain); ?></th>
                     <th align="right"><?php _e('Price', self::domain); ?></th>
                  </tr>
               </thead>
               <tbody>
            <?php
            $downloadWidth = (isset($downloaded['width']) ? $downloaded['width'] : false);
            $yourCredits = 0;
            foreach($deposit AS $arr) {
               if($arr['type'] == 'singlebuy' && $arr['name'] == 'Credits') {
                  $yourCredits = $arr['remaining'];
               }
            }
            
            foreach($info['licenses']['credits'] AS $prod) {
               $disabled = "disabled";
               if($this->api_isLogged()) {
                  $disabled = (($prod['price'] > $yourCredits) ? "disabled" : "");
               }
               $prodWidth = str_ireplace('px','', $prod['width']);
               if($prodWidth <= $downloadWidth) {
                  $disabled = "disabled";
               }
               ?>
                  <tr>
                     <td>
                        <input type="radio" name="pm_buy_license" value="<?php echo $prod['article-id']; ?>" 
                               id="<?php echo $prod['article-id']; ?>"
                              <?php echo $disabled; ?> data-cost="<?php echo $prod['price']; ?>"/>
                        <label for="<?php echo $prod['article-id']; ?>">
                           <?php echo _e($prod['name'], self::domain); ?>
                        </label>
                     </td>
                     <td><label for="<?php echo $prod['article-id']; ?>"><?php echo $prod['dimension']; ?></label></td>
                     <td align="right">
                        <label for="<?php echo $prod['article-id']; ?>">
                           <?php 
                           if($prodWidth <= $downloadWidth) {
                              if(isset($downloaded['downloaded'][$prod['width']])) {
                                 $MediaURL = $this->getMediaLink($downloaded['downloaded'][$prod['width']]['id-download']);
                                 if($MediaURL) {
                                    ?>
                           <a class="button-secondary" href="<?php echo $MediaURL; ?>" target="_blank">
                              <?php echo __('Show media', self::domain); ?>
                           </a>
                                    <?php
                                 }
                              } else if(isset($info['licenses']['credits'][$downloadWidth.'px'])) {
                                 ?>
                           <font>
                              <?php
                              $name = $info['licenses']['credits'][$downloadWidth.'px']['name'];
                              echo sprintf( __('Included in %s', self::domain), __($name, self::domain));
                              ?>
                           </font>
                                 <?php
                              }
                           } else {
                              echo $prod['price'].' '.__('Credits', self::domain);
                           }
                           ?>
                        </label>
                     </td>
                  </tr>
               <?php
            }
            ?>
                </tbody>
            </table>
            <hr />
            <table cellspacing="0" cellpadding="0">
               <thead>
                  <tr>
                     <th align="left"><?php _e('Name', self::domain); ?></th>
                     <th align="right">&nbsp;</th>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <td>
                        <input type="radio" name="pm_buy_license_er" value="false" id="false" data-cost="0" <?php if($yourCredits<1) {echo "disabled";} ?>/>
                        <label for="false">
                           <?php _e('Standard license', self::domain); ?>
                        </label>
                     </td>
                     <td align="right">&nbsp;</td>
                  </tr>
            <?php
            foreach ($info['licenses']['credits_extended_rights'] AS $er) {
               $disabled = "disabled";
               if ($this->api_isLogged()) {
                  $disabled = (($prod['price'] > $yourCredits) ? "disabled" : "");
               }
                  ?>
                  <tr>
                     <td>
                        <input type="radio" name="pm_buy_license_er" value="<?php echo $er['article-id']; ?>" 
                               id="<?php echo $er['article-id']; ?>"
                           <?php echo $disabled; ?> data-cost="<?php echo $er['price']; ?>"/>
                        <label for="<?php echo $er['article-id']; ?>">
                           <?php _e($er['name'], self::domain); ?>
                        </label>
                     </td>
                     <td align="right">
                        <label for="<?php echo $er['article-id']; ?>">
                           + <?php echo $er['price']; ?> <?php _e('Credits', self::domain); ?>
                        </label>
                     </td>
                  </tr>
                <?php
            }
            ?>
               </tbody>
            </table>
           
            <div class="panthermedia_detail_licenses_footer">
               <div class="panthermedia_total_credits">
                  <?php echo __('Total', self::domain).': '; ?>
                  <span id="panthermedia_total_credits">0</span>
                  <?php _e('Credits', self::domain); ?>
               </div>
               <?php if($yourCredits > 0) { ?>
               <button class="button-primary panthermedia_buy_media" disabled 
                       data-id="<?php echo $info['media']['id']; ?>">
                  <?php _e('Please select license', self::domain); ?>
               </button>
               <?php } ?>
               <div class="panthermedia_your_credits">
                  <?php 
                  echo __('Your credits', self::domain).': ';
                  echo $yourCredits.' ';
                  echo __('Credits', self::domain); 
                  ?>
               </div>
            </div>
            <div class="panthermedia_detail_buy_credits">
               <a class="button-secondary" href="<?php echo $this->pmlinks['credits']; ?>" target="_blank">
                  <?php echo sprintf(__('Buy %s', self::domain), __('Credits', self::domain) ); ?>
               </a>
            </div>
            <br class="pm_search_clear" />
            <?php } else { ?>
            <a class="button-primary" href="<?php echo $this->pmlinks['credits']; ?>" target="_blank">
               <?php echo sprintf(__('Buy %s', self::domain), __('Credits', self::domain) ); ?>
            </a>
            <?php } ?>
         </div>
         <!-- end credits -->
        
         <?php } ?>
      </div>
      
   </div>
   
   <br class="pm_search_clear" />
</div>

