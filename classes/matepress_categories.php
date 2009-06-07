<?php 

class matepress_categories {
	
	var $categories;
	var $user_id;
	var $table;
	
	function matepress_categories($user_id)
	{
		
		$this->user_id = $user_id;
		$this->table   = 'matepress_exclude_cats';
		$this->categories = array();
	}
	
	function set($cats=array())
	{
		global $wpdb;
		
		if (empty($cats))
		 $this->delete();
		 
		if (!empty($cats))
		{
			$this->delete();
			
			foreach($cats as $cat_id => $value) {
				$wpdb->query("INSERT INTO $this->table SET user_id=$this->user_id, category_id=$cat_id");	
			}
		}
	}
	
	function get()
	{
		global $wpdb;

		$cats   = array();
		$result = $wpdb->get_results("SELECT *  FROM $this->table WHERE user_id=$this->user_id", OBJECT);

		if (!empty($result))
		{
			foreach($result as $cat)
			{
			   $cats [] = $cat->category_id;	
			}
		}
		return $cats;
	}
	
	
	function delete()
	{
		global $wpdb;
		$wpdb->query("DELETE FROM $this->table WHERE user_id=$this->user_id");
	}
	
}


?>