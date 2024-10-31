<?php
// $Author: Steffen Blaszkowski $
// $Date: 2018-04-19 10:35 +0200 $
// $Id: account.php 67:181965e82a11 2018-04-19 10:35 +0200 steffen $
// $Revision: 67:181965e82a11 $
// $Lastlog: show account corporate values $

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
   <h1><?php echo self::name . ' &#8250; ' . __('Account', self::domain); ?></h1>

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

   <?php if ($data !== NULL && $data !== FALSE) { // user is logged in ?>

      <div class="alert alert-success">
         <strong>
            <?php echo sprintf(__('You are logged in as %s.', self::domain), $data['profile']['username']); ?>
         </strong>
         <br />
         <?php
         echo sprintf(__('Token valid until %s.', self::domain), date($data['date_format'], $data['token_time']));
         ?>

         <?php if (current_user_can('administrator')) { ?>
            <input class="button-primary" id="panthermedia_logout" type="button" name="logout" 
                   title="<?php _e('Logout', self::domain); ?>" 
                   value="<?php _e('Logout', self::domain); ?>" />
                <?php } ?>
      </div>

      <div class="panthermedia_userinfo">
         <h3><?php echo __('Your account', self::domain); ?></h3>
         <hr />

         <div class="panthermedia_profile">
            <label>
               <?php echo __('Customer No', self::domain) . ': ' . $data['profile']['id']; ?>
            </label>
            <label>
               <?php echo __('Username', self::domain) . ': ' . $data['profile']['username']; ?>
            </label>
            <label>
               <?php echo __('Company', self::domain) . ': ' . $data['profile']['company']; ?>
            </label>
            <label>
               <?php echo __('Surname', self::domain) . ': ' . $data['profile']['surname']; ?>
            </label>
            <label>
               <?php echo __('First Name', self::domain) . ': ' . $data['profile']['firstname']; ?>
            </label>
            <label>
               <?php
               echo __('Purchase', self::domain) . ': ';
               ?>
               <br /><a href="<?php echo $this->pmlinks['credits']; ?>" target="_blank"><?php _e('Credits', self::domain); ?></a>
               <br /><a href="<?php echo $this->pmlinks['subscription']; ?>" target="_blank"><?php _e('Subscription', self::domain); ?></a>
            </label>

            <?php
            if (isset($data['profile']['corporate_account'])) {
               $owner = $data['profile']['corporate_account']['owner'];
               $see = $data['profile']['corporate_account']['see_media_from_others'];
               ?>
               <hr />
               <h4><?php _e('Corporate account', self::domain); ?></h4>
               <label>
                  <?php
                  echo __('Owner', self::domain) . ': ' . __((($owner == 'yes') ? 'Yes' : 'No'), self::domain);

                  if ($owner == 'yes') {
                     echo ' - <a href="' . $this->pmlinks['corporate'] . '" target="_blank">' . __('Administration', self::domain) . '</a>';
                  }
                  ?>
               </label>
               <label>
                  <?php echo __('Can you see media from others', self::domain) . '? ' . __((($see == 'yes') ? 'Yes' : 'No'), self::domain); ?>
               </label>
               <?php
            }
            ?>
         </div>

         <div class="panthermedia_deposits">
            <?php
            foreach ($data['deposit'] AS $deposit) {
               if ($deposit['name'] === 'EUR' || $deposit['deposit-remaining'] < 1) {
                  continue;
               }

               $remaining = $deposit['deposit-remaining'];
               $term = (isset($deposit['term']) ? '/' . $deposit['term'] : '');
               $corporate = '';
               if ($deposit['currency'] == 'downloads') {
                  $remaining .= " " . sprintf(__("(of %d%s)", self::domain), $deposit['deposit-total'], $term);
               }
               if ($deposit['deposit-total'] == 'inf') {
                  $remaining = __('Unlimited', self::domain) . $term;
               }
               if(isset($deposit['deposit-remaining-corporate'])) {
                  $corporate .= __('Corporate account', self::domain).': ';
                  $corporate .= $deposit['deposit-remaining-corporate'];
                  $corporate .= " " . sprintf(__("(of %d%s)", self::domain), $deposit['deposit-total-corporate'], $term);
               }
               ?>
               <div>
                  <strong><?php echo $deposit['name']; ?></strong><br />
                  <span>
                     <?php
                     echo __('Remaining', self::domain) . ': ';
                     echo $remaining;
                     ?>
                  </span><br />
                  <?php
                  if($corporate !== '') {
                     ?>
                  <span>
                     <?php echo $corporate; ?>
                  </span><br />
                     <?php
                  }
                  ?>
                  <span>
                     <?php
                     echo __('Valid until', self::domain) . ': ';
                     echo $deposit['valid-until'];
                     ?>
                  </span>
               </div>
               <?php
            }
            ?>
         </div>
         <br class="pm_search_clear" />
      </div>

   <?php } else { // user is NOT logged in  ?>

      <div class="alert alert-danger">
         <strong><?php _e('Not logged in', self::domain); ?></strong><br />
         <?php echo sprintf(__('You are no longer logged into %s.', self::domain), self::name); ?>
         <?php echo sprintf(__('Click on %s to connect the plugin with your PantherMedia account and get access to all your files and images.', self::domain), __('OAuth Login', self::domain)); ?>
      </div>

      <table class="form-table">
         <tr>
            <td style="width:25%;">
               <input class="button-primary" id="panthermedia_openauth" type="button" name="save" 
                      title="<?php _e('OAuth Login', self::domain); ?>"
                      value="<?php _e('OAuth Login', self::domain); ?>" />
               &nbsp;&nbsp;&nbsp;
               <a href="<?php echo $this->pmlinks['register']; ?>" class="button" target="_blank">
                  <?php _e('Register now', self::domain); ?>
               </a>
            </td>
            <td>
               <div class="alert">
                  <?php _e('OAuth provides client applications a secure delegated access to server resources on behalf of a resource owner without sharing their credentials.', self::domain); ?>
               </div>
            </td>
         </tr>
      </table>

   <?php } ?>

</div>
