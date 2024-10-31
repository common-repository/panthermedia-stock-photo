<?php
// $Author: Steffen Blaszkowski $
// $Date: 2017-05-30 15:33 +0200 $
// $Id: config-default.php 22:38f92c369e2b 2017-05-30 15:33 +0200 steffen $
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

defined('ABSPATH') or die("No script kiddies please!");

return array(
    
    // role to use this plugin
    'role_to_use_add_photos' => 'administrator',
    
    // private option
    'private_or_public' => 'private',
    
    // use testmode in api
    'api_testmode' => 'false',
    
    // default age of API token (days)
    'age_api_token' => 90,
    
);
