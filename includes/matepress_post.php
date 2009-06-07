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





function matepress_notify_twitter ($postID, $post, $force = false)
	{
	global $current_user;
	include MATEPRESS_PATH.'includes/matepress_globals.php';
	
	global $wpdb;
	
	
	/**
	 * Make sure we dont submit anything to 
	 * Twitter if the user does not want to 
	 * do this.
	 */
	if ($matepress_current_user->matepress_autopost == 'off')
	 return false;
	 
	
	 
	/**
	 * If the user has chosen to exclude some categories from
	 * being send to Twitter then we need to check if
	 * we can proceed with the current post or not.
	 */
	foreach($matepress_current_user->get_excluded_cats() as $key => $val)
	{
		if ($val > 0) {
		   if (in_category($val, $post->ID))
			return false;
		}
	}
	
	/**
	 * If this post is a draft or a revision ignore this
	 * post.
	 */
	if ($post->post_status == 'draft' || $post->post_status == 'inherit')
	 return false;
	
	 	
	/**
	 * Check if the current post is 
	 * a update or not. If so sent
	 */
	$matepress_current_user->matepress_postonedit = 'no';
	
	if ($matepress_current_user->matepress_postonedit == 'no') {
		
		if ($post->post_status != 'publish' && $force==false)
		  return false;

		if ($post->post_date != $post->post_modified && $force == false)
		  return false;
	}

	$settings = array(
	  'post'        => $post,
	  'user'        => $matepress_current_user
	);
	
	

	include_once(ABSPATH . WPINC . '/class-IXR.php');

	//just give us your endpoint and we'll take it from there SparkyÉ
	$client = new IXR_Client("http://".$matepress_current_user->matepress_username.'.matepress.com/xmlrpc.php');

	//struct = structure for post data
	 $newpost['title']       = $post->post_title;
	 $newpost['description'] = $post->post_content;
	 $newpost['categories']  = array(MATEPRESS_CAT_NAME);
		
	 
	 #/use 'query' to interact with the server, set publish to 0 (draft) for now

	 if (!$client->query('metaWeblog.newPost', 6, $matepress_current_user->matepress_username, $matepress_current_user->matepress_password, $newpost, 1)) {
		// die('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage());
	 }
     //echo $client->getResponse();
	 
	$matepress_current_user->add_submitted_post($post->ID);
}

function matepress_proccess_scheduled_post($postID)
{
	$post = get_post($postID);
	matepress_notify_matepress($postID, $post, true);
}



add_action ('wp_insert_post', 'matepress_notify_twitter', 1,2);
add_action ('publish_future_post', 'matepress_proccess_scheduled_post',10,1);
?>