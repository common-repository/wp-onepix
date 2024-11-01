<?php
/*
Plugin Name: One Pixel Notifications
Plugin URI: http://onepix.me/wordpress
Description: Get notified when people visit a specific page or post
Version: 1.0
Author: Dave Holowiski
Author URI: http://onepix.me
License: GPL2

  Copyright 2011  DAVE HOLOWISKI  (email : david@holowiski.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('admin_menu', 'wp_onepix2_menu');

function wp_onepix2_menu() {

	add_options_page('One Pixel Notification Options', 'One Pixel Notifications', 'manage_options', 'wp_onepix2', 'wp_onepix2_options');
}

register_activation_hook(__FILE__,'wp_onepix2_install'); 
register_deactivation_hook( __FILE__, 'wp_onepix2_remove' );

add_action ( 'the_content', 'add_pixel_to_body');

function add_pixel_to_body($content) {
    #get custom fields
    $custom_fields = get_post_custom();
    $custom_onepix = $custom_fields['onepix_token'];
    foreach ( $custom_onepix as $key => $value ) {
    	#if ($key=trim(strtolower('onepix_token'))) {
    		$onepix_url='<img src="http://onepix.me/pixels/visit?token='.$value.'" style="visibility:hidden;">'.$onepix_url;
    	#}
    }
    $content=$content.$onepix_url;
    return $content;
}

function wp_onepix2_install() {
/* Creates new database field */

add_option("wp_onepix2_pixels[]", '', '', 'yes');
}

function wp_onepix2_remove() {
/* Deletes the database field */

delete_option('wp_onepix2_pixels');
}

function wp_onepix2_options() {
	$rails_env="0.0.0.0:3000";
	
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '<div class="wrap">';
	echo '<h2>One Pixel Notification Options</h2>';
	?>
	<?php 
	$wp_onepix2_pixels=get_option('wp_onepix2_pixels');
	#echo("farfenlugen ".$wp_onepix2_pixels[apikey]);
	if ($wp_onepix2_pixels[apikey]=="")
	?><h3>You must enter your API Key to continue</h3>
	<p>To use this plugin, you need to create a free account on <a href="http://onepix.me">OnePix.Me</a>. <br/>
	Once you've created your free account, click on the WordPress button to get your API Key.</br>
	Copy your API Key and paste it in the box below, and click Save<br/>
	<?php
	end
	?>
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	Your Notification API Key <?php #echo("farfenlugen2 ".$wp_onepix2_pixels[apikey]); ?>
	<input name="wp_onepix2_pixels[apikey]" type="text" id="wp_onepix2_pixels[apikey]" value="<?php echo($wp_onepix2_pixels[apikey]); ?>" />
	
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="wp_onepix2_pixels" />
	
	<p>
	<?php
	if ($wp_onepix2_pixels[apikey] !="")
		echo("<h3>Account Information</h3>");
		$url='http://'.$rails_env.'/wp_plugin/get_info?api_key='.$wp_onepix2_pixels[apikey];
		$account_info=file_get_contents($url);
		$account_features=explode(",", $account_info);
		//depends on API Version 1.0. Location of variables is hard coded.
		if ($account_features[2]=="Sub Active:1") {
			echo("<h3>");
			if ($account_features[4]=="SMS Included:1") {
				echo("SMS Notifications Remaining: ".$account_features[6]);
			}
			if ($account_features[7]=="Email Included:1") {
				echo(", Email Notifications Remaining: ".$account_features[9]);
			}
			if ($account_features[10]=="IRC Included:1") {
				echo(", IRC Notifications Remaining: ".$account_features[12]);
			}
			echo("</h3>");
			
			#get a list of tokens
			$url2='http://'.$rails_env.'/wp_plugin/get_pixels?api_key='.$wp_onepix2_pixels[apikey];
			
			$pixels=file_get_contents($url2);
			$pixels=explode(",", $pixels);
			$pixels_count=(count($pixels)-1);
			if ($pixels_count > 0 ) {
			?>
			<ul>
			<?php
			}
			$i=0;
			do {
				echo("<li>Pixel: ");
				if ($pixels[1+$i]=="") {echo($pixels[2+$i]); } else { echo($pixels[1+$i]); }
				echo(", Notification ".$pixels[3+$i]);
				echo(" <strong>Token : <font color=\"blue\">".$pixels[2+$i]."</font></strong></li>");
				?>
				<?php
				$i=$i+4;
			} while ($i < $pixels_count);
			if ($pixels_count > 0 ) {
			?>
			</ul>
			<?php
			}
			
		} else {
			#echo("<h3>Sorry, your account has expired. Please visit onepix.me to renew your account</h3>");
		}
		
	end
	?>
	
	<input type="submit" value="<?php _e('Save Changes') ?>" />
	</p>
	<p>
	<h2>How to use the plugin?</h2>
	Edit ANY post or page that you want the notification pixel to appear on. <br/>
	Add a "Custom Field" (below the post body. If you don't see it you might need to click on Screen Options and select Custom Fields).<br/>
	The Custom Field Name is: <strong>onepix_token</strong> and in the "Value" box, enter the "token" from above<br/>
	Publish your post, and enjoy!
	</p>
	</form>
	<?php
	
	echo '</div>';
}
?>