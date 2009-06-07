<?php 
/*  Copyright 2008  Johnny Mast  (email : info@phpvrouwen.nl)

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



/**
 * When a user starts using the Twitme plugin we still
 * need to install the database functions we use for those
 * users to use Twitme. So basicly what this does is check if 
 * the required tables exist if not on the other hand it will
 * insert them into the database.
 * 
 * @return void
 */
function matepress_check_install()
{
	global $wpdb;
	
	if (!(bool)$wpdb->query(" SHOW TABLES LIKE 'matepress_notified_followers'"))
	{
	
		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `matepress_submited_posts` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) NOT NULL,
			  `post_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `matepress_exclude_cats` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) NOT NULL,
			  `category_id` int(11) NOT NULL,
			  PRIMARY KEY (`ID`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		
		$wpdb->query("						
		CREATE TABLE IF NOT EXISTS `matepress_users` (
		  `user_id` int(11) NOT NULL AUTO_INCREMENT,
		  `wp_user` int(4) NOT NULL DEFAULT '0',
		  `matepress_username` varchar(255) NOT NULL,
		  `matepress_password` varchar(255) NOT NULL,
		  `matepress_autopost` varchar(5) NOT NULL,
		  `matepress_postonedit` varchar(255) NOT NULL,
		  PRIMARY KEY (`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
	}
}
add_action('init', 'matepress_check_install',1);
?>