<?php
/*  Copyright 2008  Johnny Mast  (email : info@webnation.nl)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
	Plugin Name: Matepress
	Plugin URI:http://www.matepress.com/apps
	Description: This plugin allows you to send your blog posts directly to Matepress.
	Version: 1.0
	Author: Johnny Mast
	Author URI: http://www.matepress.com
*/
define('MATEPRESS_PATH', '../'.PLUGINDIR.'/matepress/');
define('MATEPRESS_TRANSLATION_DOMAIN', 'matepress');
define('MATEPRESS_TRANSLATEDIR', PLUGINDIR.'/matepress/translation');
define('MATEPRESS_WP_VERSION', (float)substr($wp_version,0,3));
define('MATEPRESS_PLUGINURL', get_bloginfo ( 'wpurl' ).'/wp-content/plugins/matepress/');
define('MATEPRESS_CATS_PER_ROW', 10);
define('MATEPRESS_CAT_NAME', 'Matepress post category');

require_once 'includes/matepress_translation.php';
require_once 'classes/matepress_posts.php';
require_once 'classes/matepress_categories.php';
require_once 'classes/MatepressUser.php';
require_once 'includes/matepress_menus.php';
require_once 'includes/matepress_install.php';
require_once 'includes/matepress_post.php';




?>