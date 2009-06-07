<?php 

class matepress_posts {
	
	var $posts;
	var $user_id;
	var $table;
	
	function matepress_posts($user_id)
	{
		
		$this->user_id = $user_id;
		$this->table   = 'matepress_submited_posts';
		$this->posts  = array();
	}
	
	
	function get()
	{
		global $wpdb;

		$posts   = array();
		$result  = $wpdb->get_results("SELECT *  FROM $this->table WHERE user_id=$this->user_id", OBJECT);

		if (!empty($result))
		{
			foreach($result as $post)
			{
			   $posts [] = get_post($post->post_id);	
			}
		}
		return $posts;
	}
	
	function add($post_id)
	{
		global $wpdb;
		$wpdb->query("INSERT INTO $this->table SET post_id=$post_id, user_id=$this->user_id");
	}
	
	
	function get_que ()
	{
		$all_posts       = get_posts();
		$submitted_posts = $this->get();
		$que             = array();
		
		foreach ($all_posts as $post)
		{
			if ($post->post_status == 'publish')
			{
				if (!in_array ($post->ID,  $submitted_posts))
				{
					$que [] = get_post ($post->ID);
				}
			}
		}
		return $que;
	}
	
	function delete()
	{
		global $wpdb;
		$wpdb->query("DELETE FROM $this->table WHERE user_id=$this->user_id");
	}
	
}


?>