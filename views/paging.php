<?php
// $Author: Steffen Blaszkowski $
// $Date: 2017-05-30 15:33 +0200 $
// $Id: paging.php 22:38f92c369e2b 2017-05-30 15:33 +0200 steffen $
// $Revision: 22:38f92c369e2b $
// $Lastlog: - add license infos for WP and HG $

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
<div class="panthermedia_paging_content">
   
   <?php if($page > 1) { ?>
   <!-- &#9664; -->
   <a href="<?php echo ($page - 1); ?>" class="panthermedia_page panthermedia_paging_back" 
      onclick="return false;">&nbsp;</a>
   <?php } ?>

   <?php if($page < $last_page) { ?>
   <!-- &#9654; -->
   <a href="<?php echo ($page + 1); ?>" class="panthermedia_page panthermedia_paging_next" 
      onclick="return false;">&nbsp;</a>
   <?php } ?>

   <div class="panthermedia_paging_info">
      <?php
      echo sprintf( __('%d to %d of %s', self::domain), $info_first, $info_last, $total );
      ?>
   </div>

   <div class="panthermedia_paging_pager">
      <?php
      $iconUrl = $this->getUrl().'/files/refresh_w.png';
      $input = '<input type="number" min="1" max="'.$last_page.'" value="'.$page.'">';
      $button = '<button class="button-primary"><img src="'.$iconUrl.'" alt title></button>';
      echo sprintf( __('Page %s%s of %s', self::domain), $button, $input, $info_lastPage );
      ?>
   </div>
</div>

