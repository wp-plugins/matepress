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

class MatepressUser 
{
	var $user_id;
	var $table;
	
	var $aUser;
	var $result; 
	
	function MatepressUser($user_id = 0) {
	
		
		if (!$user_id)
		 $user_id = get_current_user_id();
		 
		 
		$this->user_id = $user_id;
		$this->table   = 'matepress_users';
		 
	    
	    // TODO: matepress_notice('User not found');
	    
	    /*
	     * Get the Twitme information for 
	     * this user. 
	     */
	     $this->populate();
	}
	
	function user_data() {
		return array(
					 'wp_user' => '',
					 'matepress_username'  => 'username',
					 'matepress_password'  => 'password',
				  	 'matepress_autopost'  => 'on',
					 'matepress_postonedit'  => 'on',
					 );	
	}
	
	function populate()
	{
		global $wpdb;
	
		if (!($wpdb->query("SELECT * FROM $this->table  WHERE user_id=$this->user_id")))
		{
			$new_user            = $this->user_data();
			$new_user['wp_user'] = $this->user_id;
			$this->aUser         = $new_user;

			$this->save();
			echo mysql_error();
			$this->populate();
		} else
		{
			
			$row = $this->result = $wpdb->get_row("SELECT * FROM $this->table  WHERE user_id=$this->user_id", OBJECT);
			
			foreach($row as $field => $value)
			  $this->$field = $value; 
			  
			$this->matepress_exclude_cats = $this->get_excluded_cats();
		}
	}
	
	function set_excluded_cats($cats=array())
	{
		$cat_object = new matepress_categories($this->user_id);
		$cat_object->set($cats);	
	}

	function get_excluded_cats()
	{
		$cat_object = new matepress_categories($this->user_id);
		$cats       = $cat_object->get();
		
		if (!is_array($cats)) $cats = array();
		return $cats;
	}
	
	function add_submitted_post($post_id)
	{
		$posts_object = new matepress_posts($this->user_id);
		$posts_object->add($post_id);
	}

	function get_submitted_posts()
	{
		$posts_object = new matepress_posts($this->user_id);
		return $posts_object->get();
	}
	
	function count_submitted_posts()
	{
		$posts_object = new matepress_posts($this->user_id);
		$posts        = $posts_object->get();
		return count($posts);
	}
	
	function count_blog_posts()
	{
		$posts        = get_posts();
		return count($posts);
	}
	
	function get_short_url($long_url)
	{
		$TwitUrlService = new twitTimeurl();
		return $TwitUrlService->getShortUrl($long_url);
	}
	
	function save() {
		global $wpdb;
		
		
		if (!isset($this->aUser['user_id']))
		{
		return $wpdb->query("
							INSERT INTO
							  matepress_users
							SET
							  wp_user             = ".(int)$this->aUser['wp_user'].",
							  matepress_username     = '".mysql_real_escape_string($this->aUser['matepress_username'])."',
							  matepress_password     = '".mysql_real_escape_string($this->aUser['matepress_password'])."',
							  matepress_postonedit   = '".mysql_real_escape_string($this->aUser['matepress_postonedit'])."',
							  matepress_autopost     = '".mysql_real_escape_string($this->aUser['matepress_autopost'])."'");
		} else 
		return $wpdb->query("
							UPDATE
							  matepress_users
							SET
							  wp_user             = ".(int)$this->aUser['wp_user'].",
							  matepress_username     = '".mysql_real_escape_string($this->aUser['matepress_username'])."',
							  matepress_password     = '".mysql_real_escape_string($this->aUser['matepress_password'])."',
							  matepress_autopost     = '".mysql_real_escape_string($this->aUser['matepress_autopost'])."',
							  matepress_postonedit        = '".mysql_real_escape_string($this->aUser['matepress_postonedit'])."'
							WHERE user_id=".(int)$this->aUser['user_id']);
		
	}
	
	function set_key($key, $value)
	{
		$this->aUser[$key] = $value;
	}
	
	function rehash()
	{
		$this->populate();
	}
	
	function delete(){
		global $wpdb;
		
		if ($this->aUser['user_id'] > 0)
		 $wpdb->get_var("DELETE from matepress_users WHERE user_id=".(int)$this->aUser['user_id']);	
	}
}


/*
** Check this buddypress user exists 
** in the databse.
**
** @returns matepress_uid or false
*/
function matepress_bp_user_exists($uid) {
	global $wpdb;
	return $wpdb->get_var("SELECT user_id FROM matepress_users WHERE bp_user=".(int)$uid);	
}


/*
** Check this buddypress user exists 
** in the databse
**
** @returns matepress_uid or false
*/
function matepress_wp_user_exists($uid) {
	global $wpdb;
	return $wpdb->get_var("SELECT user_id FROM matepress_users WHERE wp_user=".(int)$uid);		
}


/*
** Returns the user from the database
**
** @returns twitmeUser or null
*/
function matepress_fetch_user($uid=-1) {
	global $wpdb;
	
	$aUser = $wpdb->get_row("SELECT * FROM matepress_users WHERE user_id=".(int)$uid,ARRAY_A);
	
	if (!isset($aUser->user_id))
  	  $aUser = twitmeUser::getEmptyRecord();

	
	$oInstance = new twitmeUser($aUser);
	return $oInstance;
}


?>