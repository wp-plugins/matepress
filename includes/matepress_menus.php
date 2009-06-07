<?php

/**
 * Add the management pages to the Wordpress dashboard.
 *
 */
function matepress_setup_navigation() 
{
	add_options_page('Matepress Settings', 'Matepress Settings', '8', __FILE__, 'matepressgetPage');
}

/**
 * This function will be called if a page has been choosen.
 *
 */
function matepressgetPage ()
{

	require_once MATEPRESS_PATH.'html/settings.php';
}


/**
 * This function loads the settings page for wordpress 2.6.x
 *
 */
function matepress_load_settings()
{	
	require_once MATEPRESS_PATH.'html/settings.php';
}

/**
 * This function loads te Twitme`s page for wordpress 2.6.x
 *
 */
function matepress_manage_twits()
{
    require_once MATEPRESS_PATH.'html/manage.php';
}
add_action('admin_menu', 'matepress_setup_navigation',2);

?>
