<?php
// $Author: Steffen Blaszkowski $
// $Date: 2018-07-12 12:20 +0200 $
// $Id: config-plugin.php 75:2cb39b7b28fd 2018-07-12 12:20 +0200 steffen $
// $Revision: 75:2cb39b7b28fd $
// $Lastlog: update version 1.8.4 $

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
    'api' => 'e715f1976b7af2ef2dde20ff7ad0d7af',
    'version' => '1.8.4',
    'ageSearchFilter' => 60 * 60,  // 1 hour
    'ageCachefileLang' => 60 * 60 * 24,  // 24 hours
    'mediaInfoCache' => 60*30, // 30 min
    'perPage' => 20, 
    'option_testmodus' => true,
    'pmlinks' => array(
        'credits' => 'http://stockagency.panthermedia.net/cms/credits?utm_source=Wordpress-Plugin&utm_medium=Text-Link&utm_campaign=Wordpress-Plugin-Credits&hl=',
        'subscription' => 'http://stockagency.panthermedia.net/cms/subscription?utm_source=Wordpress-Plugin&utm_medium=Text-Link&utm_campaign=Wordpress-Plugin-Subscription&hl=',
        'mydownloads' => 'http://stockagency.panthermedia.net/pm/mydownloads?hl=',
        'corporate' => 'http://stockagency.panthermedia.net/pm/corporate?hl=',
        'media' => 'http://stockagency.panthermedia.net/m/stock-photos/#id#?hl=',
        'user' => 'http://stockagency.panthermedia.net/u/media/#user#?hl=',
        'register' => 'https://stockagency.panthermedia.net/pm/sign-up?utm_source=Wordpress-Plugin&utm_medium=Button&utm_campaign=Wordpress-Plugin-Register&hl=',
    ),
    'deactivate_searchfilters' => array(
        'model','ranking'
    ),
);
