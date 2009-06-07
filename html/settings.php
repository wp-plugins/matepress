<?php
global $current_user;
include MATEPRESS_PATH.'includes/matepress_globals.php';

if (!empty($_POST))
{
	if (isset($_POST['matepress_exclude_cat']))
	{

		$matepress_current_user->set_excluded_cats($_POST['matepress_exclude_cat']);
		unset($_POST['matepress_exclude_cat']);
	} else
	$matepress_current_user->set_excluded_cats(array());
	
	foreach($_POST as $key => $value)
	{
		$matepress_current_user->set_key($key, $value);
	}
	/*
	 * Save the settings and rehash the 
	 * user settings so that the changed settings
	 * show up at the form below.
	 */
	$matepress_current_user->save();
	$matepress_current_user->rehash();	
}

?>
<link rel='stylesheet' href='<?php echo MATEPRESS_PLUGINURL.'/style/style.css'; ?>' type='text/css' media='all' />

<div class="wrap"">
<img class="logo" src='<?php echo MATEPRESS_PLUGINURL.'/images/matepress_logo.png'; ?>' />
<div class="toc">
 <ul>
   <li><a href="#posts"><?php echo __('Posting options',MATEPRESS_TRANSLATION_DOMAIN)?></a></li>
   <li><a href="#categories"><?php echo __('Category management',MATEPRESS_TRANSLATION_DOMAIN)?></a></li>
 </ul> 

</div>
<form action="" method="post">
	<div class="setting-row">
		<div class="setting-desc">
		   <h2><?php echo __('Login Info', MATEPRESS_TRANSLATION_DOMAIN)?></h2>
		   <p style="color:#999;">
		   <?php echo __('In order to connect to Matepress you will need to provide your Matepress login information.',MATEPRESS_TRANSLATION_DOMAIN);?>
		   </p>
		    <p><input class="button" type="submit" value="<?php echo __('Save Settings',MATEPRESS_TRANSLATION_DOMAIN)?>" /></p>
		</div>
		<div class="setting-form">
		   <h4><?php echo __('Username',MATEPRESS_TRANSLATION_DOMAIN)?></h4>
		   <p><input type="text" name="matepress_username" id="username" value="<?php echo $matepress_current_user->matepress_username;?>" /></p>
		   <h4><?php echo __('Password',MATEPRESS_TRANSLATION_DOMAIN)?></h4>
		   <p><input type="password" name="matepress_password"  id="password" value="<?php echo $matepress_current_user->matepress_password;?>" /></p>
		   <span id="connection_results"></span>
		</div>

		
		<div class="setting-spacer"></div>
	</div>

	<div class="setting-row" id="posts">
		<div class="setting-desc">
		   <h2><?php echo __('Posting options',MATEPRESS_TRANSLATION_DOMAIN)?></h2>
		   

			<h3><?php echo __('Auto posts',MATEPRESS_TRANSLATION_DOMAIN)?></h3>
			<p style="color:#999;">
			<?php echo __('If disable the option the plugin will no longer automaticaly send your posts (this option is turned on by default)', MATEPRESS_TRANSLATION_DOMAIN)?>
			</p>
			<br />
		</div>
		
		<div class="setting-form">
		   <h3><?php echo __('Automaticaly post new Posts to Matepress',MATEPRESS_TRANSLATION_DOMAIN)?></h3>
		   <p>
	        <?
					$aSelected = array (
									 ($matepress_current_user->matepress_autopost == 'on') ? 'checked="checked"' : '',
									 ($matepress_current_user->matepress_autopost == 'off') ? 'checked="checked"' : '');
					
			?>
	        <label>
	          <input type="radio"  name="matepress_autopost" id="matepress_autopost_0" value="on" <?=$aSelected[0]?> />
	            <?=__('On', MATEPRESS_TRANSLATION_DOMAIN)?>
	        </label>
	        <br />
	        <label>
	           <input type="radio" name="matepress_autopost" id="matepress_autopost_1" value="off" <?=$aSelected[1]?> />
	            <?=__('Off', MATEPRESS_TRANSLATION_DOMAIN)?>
	        </label>
	         <br />
	       </p> 
	       <br />

 		</div>
		<div class="setting-spacer"></div>
	</div>
	<br />






	<div class="setting-row" id="categories">
		<div class="setting-desc">
		   <h2><?=__("Categories Management", MATEPRESS_TRANSLATION_DOMAIN)?></h2>
		   <h3><?=__("Exclude categories", MATEPRESS_TRANSLATION_DOMAIN)?></h3>
		   <p style="color:#999;">
		   <?=__("If you are creating new posts from rss feeds, or you just want to exclude any category from being posted to Matepress, please mark the related category checkbox(es). By not using this option, you could risk mass posting to Matepress.", MATEPRESS_TRANSLATION_DOMAIN)?>
		   </p>
		</div>
		
		<div class="setting-form">
		<h3><?=__("Exclude categories", MATEPRESS_TRANSLATION_DOMAIN)?></h3>
		<p>
        <?php
		  
          $args  = array ('hide_empty' => false);
		  $aCats = get_categories($args);
		  $i     = 0;
		  
		  if (empty($aCats)) echo __("You did not add any categories yet.", MATEPRESS_TRANSLATION_DOMAIN);
		  else
		   foreach($aCats as $thisCat)
		   {
			  if ($i == MATEPRESS_CATS_PER_ROW)
			   echo '<br />';
			  ?>
               <label>
                 <?=$thisCat->cat_name;?>
                 <?php
				   $sValue = '';

				   if (is_array($matepress_current_user->matepress_exclude_cats)) {
				    if (in_array($thisCat->term_id, array_values($matepress_current_user->matepress_exclude_cats)))
				    $sValue = 'checked="checked"';
				   }
				 ?>
                 &nbsp;<input type="checkbox" class="matepress_exclude_category" <?php echo $sValue?> id="matepress_exclude_cat[<?=$thisCat->term_id;?>]" name="matepress_exclude_cat[<?=$thisCat->term_id;?>]" />
               </label>&nbsp;
              <?php 
			  $i++;
		   }
        ?>
        </p>
        <br />
		</div>
		<div class="setting-spacer"></div>
	</div>

	
	<input name="wp_user" type="hidden" value="<?php echo $matepress_current_user->wp_user; ?>" />
	<input name="user_id" type="hidden" value="<?php echo $matepress_current_user->user_id; ?>" />
	<p><input class="button" type="submit" value="<?php echo __('Save Settings',MATEPRESS_TRANSLATION_DOMAIN)?>" /></p>
	</form>
</div>