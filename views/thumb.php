<?php
// $Author: Steffen Blaszkowski $
// $Date: 2017-05-30 15:33 +0200 $
// $Id: thumb.php 22:38f92c369e2b 2017-05-30 15:33 +0200 steffen $
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
<div class="panthermedia_thumbnails">
   <?php
    foreach($media AS $did => $arr) {
       $download = (isset($arr['download']) ? $arr['download'] : '' );
        ?>
    <div class="panthermedia_thumbnail">
        <a data-tooltip-pm="panthermedia_sticky_<?php echo $did; ?>" class="panthermedia_action" data-type="detail"
           href="#<?php echo $arr['id']; ?>" onclick="return false;" data-download="<?php echo $download; ?>">
            <div class="panthermedia_thumbnail_div">
                <table width="100%" height="170" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center">
                            <img max-height="170" src="<?php echo $arr['thumb_url']; ?>" title="<?php echo esc_attr($arr['title']); ?>" />
                        </td>
                    </tr>
                </table>
            </div>
            <div class="panthermedia_thumbnail_title"><?php echo $arr['id']; ?> # <?php echo $arr['author']; ?></div>
        </a>
    </div>
        <?php
    }
    ?>
</div>

<br class="pm_search_clear" />

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