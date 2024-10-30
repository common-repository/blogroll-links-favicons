<?php
/*
	Plugin Name: Blogroll Links Favicons
	Plugin URI: http://wordpress.org/extend/plugins/blogroll-links-favicons/
	Description: Add favicon icons to blogroll links. Caches icons locally to keep load times fast. By <a href="http://joshbetz.com">Josh Betz</a> and <a href="http://stephennomura.com">Stephen Nomura</a>
	Version: 2.0.5
	Author: Stephen Nomura
	Author URI: http://stephennomura.com
	License: GPL2
*/
/*
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/

/* @todo
// v2.1 (better, faster refresh)
// Add	- JS refresh dialog
// Add	- Local version control (for update scripts)

// v2.2 (scheduling)
// Add	- Scheduled favicon refreshing (user definable on admin).
// Add	- Actually cache, rather than reference, a new default favicon from URL.

// v2.3 (more control)
// Add	- A way to force specific links to use default favicon in spite of extant favicon.
// Add	- An option in the Links widget to turn favicons on and off.
// Add	- A way to handle links with an image already assigned to them.

// v2.4 (icon management)
// Add	- Icon Manager Admin?

// v3.0 (image handling/conversion)
// Add	- CSS Sprite generation
// Add	- High Quality ICO to PNG conversion, not all browsers support ICO 
//		  and existing plugins that do this butcher image quality.
*/

/**
 * Constants, Variables, Options
 *
 */
define('BLF_VERSION', '2.0.5');

if (!defined('BLF_SETTINGSNAME')) {
    define('BLF_SETTINGSNAME', 'blf_settings');
}
// If magic constant __DIR__ isn't defined, define it.
if(!defined('__DIR__')) {
    $iPos = strrpos(__FILE__, "/");
    define("__DIR__", substr(__FILE__, 0, $iPos) . "/");
}
// Where to store favicons locally
define('BLF_CACHE_DIR', __DIR__.'cache');
define('BLF_CACHE_URI', get_bloginfo('wpurl').'/wp-content/plugins/blogroll-links-favicons/cache');




/* INITIALIZATION / SETUP */
/* ============================================== */
register_activation_hook( __FILE__,'blf_activate' );
if( ! get_option(BLF_SETTINGSNAME) ){
	update_option(BLF_SETTINGSNAME,blf_default_settings());
}

//add_action( 'admin_init','blf_check_version' );

/**
 * Default Plugin Settings
 *
 * @package Blogroll Links Favicons
 * @since 2.0.5
 *
 * @return   array
 */
function blf_default_settings(){
	return array(
		'blf_version'		=> BLF_VERSION,
		'default_favicon'	=> BLF_CACHE_URI.'/default-favicon.png',
		'refresh_interval'	=> 'disabled',
		'refresh_date'		=> null,
	);
}

/**
 * Check if BLF is Up-To-Date
 *
 * @package Blogroll Links Favicons
 * @since 2.0.5
 *
 */
function blf_check_version(){
	$settings = get_option(BLF_SETTINGSNAME);
	if( $settings['blf_version'] != BLF_VERSION ){
		blf_upgrade();
	}
}

/**
 * Activation Script (one time)
 *
 * @package Blogroll Links Favicons
 * @since 2.0.5
 */
function blf_activate(){
//	wp_schedule_event(time(), 'hourly', 'smn_refresh_scheduler');
	smn_refresh_cache();
	update_option(BLF_SETTINGSNAME,blf_default_settings());
}
/**
 * Upgrade Script (every upgrade)
 *
 * @package Blogroll Links Favicons
 * @since 2.0.5
 */
function blf_upgrade(){
	smn_refresh_cache();
	if( !get_option(BLF_SETTINGSNAME) ){
		update_option(BLF_SETTINGSNAME, blf_default_settings());
	}else{
		
	}
}
/**
 * Deactivation Script (one time)
 *
 * @package Blogroll Links Favicons
 * @since 2.0.5
 */
function blf_deactivate(){
	//@todo
//	wp_clear_scheduled_hook('smn_refresh_scheduler');
//	delete settings
}

 
/**
 * Filter Default Blogroll Markup
 *
 * This method generates the markup for display.
 *
 * @package Blogroll Links Favicons
 * @since {{@internal Missing Description}}}
 *
 * @param    {{@internal Missing Description}}}
 * @return   {{@internal Missing Description}}}
 */
function blogroll_favicons($output){
//	global $blf_default_favicon;
	$settings = get_option(BLF_SETTINGSNAME);
	$blf_default_favicon = $settings['default_favicon'];
	
	$bookmark_arr = explode("\n",$output);
	foreach($bookmark_arr as $k => $v){
		if(preg_match('/href="(.*)"/', $v, $matches) && !preg_match('/img/', $v)){
			preg_match('/http:\/\/[a-zA-Z0-9-\.]+(\/|\s|$|\")/', $matches[1],$urls);
			preg_match('/http:\/\/[a-zA-Z0-9-\.\/\?\&=#;]*(\s|$|\")/', $matches[1],$fullurls);
			$bookmark_url = str_replace('"', "", $urls[0]);
			$bookmark_fullurl = str_replace('"', "", $fullurls[0]);

			$favicon = smn_get_local_favicon_url($bookmark_url); // Steve
			
			$str = explode('<li>', $v);
			$a = explode(' ', $str[1]); // Explode the anchor link
			
			// Setup the background image on the link
			$new_bookmark = '<li><a class="blogroll-favicon" style="background: url('. $favicon .') left center no-repeat; -webkit-background-size: 16px; -moz-background-size: 16px; display: block; text-indent: 20px;" ';
			
			//Get the rest of the pieces of the link
			foreach($a as $b) {
				if($b != '<a') {
					$new_bookmark .= $b . ' ';
				}
			}
			$bookmark_arr[$k] = $new_bookmark;
		}
	}
	$output = implode("\n",$bookmark_arr);
	return $output;
}
add_filter('wp_list_bookmarks', 'blogroll_favicons');


/* Admin Panel */
/* ============================================ */

/**
 * blogroll_favicons_items
 *
 * @package Blogroll Links Favicons
 * @since {{@internal Missing Description}}}
 */
function blogroll_favicons_items() {
	add_submenu_page(
		'link-manager.php',
		__('Link Favicons Options ', 'blogrollfavicons')
		, __('Link Favicons', 'blogrollfavicons')
		, 8 
		, basename(__FILE__)
		, 'blogroll_favicons_options'
	);
}
add_action('admin_menu', 'blogroll_favicons_items');

/**
 * blogroll_favicons_options
 *
 * @package Blogroll Links Favicons
 * @since {{@internal Missing Description}}}
 */
function blogroll_favicons_options(){
	$msg ='';
	$nonce=$_REQUEST['_wpnonce'];
	$settings = get_option(BLF_SETTINGSNAME);
	
	if (wp_verify_nonce($nonce, 'my-nonce') ) {
		if($_REQUEST['bi_refresh_cache'] == 'true'){
			smn_refresh_cache();
		}
		$settings['default_favicon'] = $_REQUEST['bi_default_favicon'];
		update_option(BLF_SETTINGSNAME, $settings);
		$msg .='<div class="updated fade below-h2" id="message"><p>Updated</p></div>';
	}
	$nonce= wp_create_nonce('my-nonce');
	/*
	if( ini_get('allow_url_fopen') ){
	    $msg .='';
	}else{
	    $msg .='<div class="error" id="error"><p>Please make sure \'allow_url_fopen = On\' is set in your php.ini file.</p></div>';
	}
	*/
	
	foreach ( array('Disabled','Daily','Weekly','Monthly') as $s ) {
		$scheduler_markup .= '<option value="' . strtolower($s) . '"';
		if ( strtolower($s) == $settings['refresh_interval'] ){
			$scheduler_markup .= ' selected="selected"';
		}
		$scheduler_markup .= ">$s</option>";
	}
	
	print('
			<div class="wrap">
				<h2>'.__('Blogroll Links Favicons Options ', 'blogrollfavicons').'</h2>'.$msg.'
				<form id="blogrollfavicons" name="blogrollfavicons" method="post">
				<h3>Default Favicon</h3>
				<table class="form-table">
				<tr valign="top"> 
					<th scope="row">Current Default Favicon:</th>
					<td><img style="padding:3px 0;" height="16px" width="16px" src="'.$settings['default_favicon'].'" /></td>
				</tr>
				<tr valign="top"> 
					<th scope="row">Default Favicon URL:</th>
			    	<td><input size="99%" type="text" name="bi_default_favicon" value="'.$settings['default_favicon'].'" /><span>(If the favicon on the link is missing this will be shown instead.)</span></td>
				</tr>
				</table>

				<h3>Cache Refresh Scheduling</h3>
				<table class="form-table">
				<tr valign="top">
					<th scope="row">Cache Refresh Schedule:</th>
					<td><select name="blf-schedule">'.$scheduler_markup.'</select></td>
				</tr>

				<tr valign="top">
					<th scope="row">Refresh Cache Immediately?</th>
			    	<td><input type="checkbox" name="bi_refresh_cache" value="true" /><span>(This can take awhile depending on how many links you have!)</span></td>
				</tr>
				<tr valign="top"> 
					<td colspan="2">
						<p class="submit">
							<input type="submit" name="submit_button" value="Update" />
						</p>
						<input type="hidden" name="_wpnonce" value="'.$nonce.'" />
					</td>
				</tr>		
				</table>
				</form>
			</div>
		 ');
}

/**
 * blf_admin_styles
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 */
function blf_admin_styles(){
	echo '<style>
		.column-favicon {width:30px;}
		td.column-favicon {padding-top:6px !important;}
/*		td.column-favicon {padding:11px 7px !important;} */
	</style>';
}
add_action( 'admin_print_styles', 'blf_admin_styles' );

/* Admin Columns! */
add_filter('manage_link-manager_columns', 'blf_edit_link_columns');
add_action('manage_link_custom_column', 'blf_manage_link_favicon_column', 10, 2);
/**
 * blf_edit_link_columns
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 *
 * @param    array    $columns
 * @return   array
 */
function blf_edit_link_columns($columns){
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"name" => "Name",
			"favicon" => "Icon",
			"url" => "URL",
			"categories" => "Categories",
			"rel" => "Relationship",
			"visible" => "Visible",
			"rating" => "Rating",
		);

		return $columns;
}
/**
 * blf_manage_link_favicon_column
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 *
 * @param    string    $column_name
 * @param    int    $id
 */
function blf_manage_link_favicon_column($column_name, $id) {
	global $wpdb;
	$bookmark = get_bookmark($id);
	switch($column_name) {
	case 'favicon':
		echo '<img src="'.smn_get_local_favicon_url($bookmark->link_url).'" />';
		break;
	default:
		break;
	}
}

/* Modify Plugins Manager Page */
add_filter('plugin_action_links', 'blf_add_settings_link', 10, 2);
/**
 * blf_add_settings_link
 *
 * @package Blogroll Links Favicons
 * @since 2.0.3
 *
 * @param    array    $links
 * @param    string    $file
 * @return   array
 */
function blf_add_settings_link($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	
	if ($file == $this_plugin ){
		$links['settings'] = '<a href="link-manager.php?page=blogroll-links-favicons.php">Settings</a>';
		//$links[] = '<a href="edit.php">Posts</a>';
	}
	return $links;
}

add_filter('plugin_row_meta', 'blf_add_author_link', 10, 2);
/**
 * blf_add_author_link
 *
 * @package Blogroll Links Favicons
 * @since 2.0.3
 *
 * @param    array    $links
 * @param    string    $file
 * @return   array
 */
function blf_add_author_link($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	
	if ($file == $this_plugin ){
		$links[1] = 'By <a href="http://stephennomura.com">Stephen Nomura</a> & <a href="http://joshbetz.com/">Josh Betz</a>';
	}
	return $links;
}


/* Favicon Caching */
/* ============================================================ */
//add_action('my_task_hook','smn_refresh_cache');
// Hook on save link. ('edit_link' is also called when adding a new link)
add_action('edit_link','smn_refresh_favicon');

/**
 * smn_refresh_favicon
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 *
 * @param    UNKNOWN_TYPE    $link_ID
 */
function smn_refresh_favicon($link_ID){
	global $wpdb;
	$bookmark = get_bookmark($link_ID);	
	$remote_favicon_url = smn_get_remote_favicon_url($bookmark->link_url);
	$save_as = smn_generate_local_favicon_filename($bookmark->link_url);
	smn_cache_favicon($remote_favicon_url,$save_as);
}
/**
 * smn_refresh_cache
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 */
function smn_refresh_cache(){
	global $wpdb;
	$settings = get_option(BLF_SETTINGSNAME);
	
	$bookmark_arr = get_bookmarks();
	foreach( $bookmark_arr as $bookmark ){
		$remote_favicon_url = smn_get_remote_favicon_url($bookmark->link_url);
		$save_as = smn_generate_local_favicon_filename($bookmark->link_url);
		smn_cache_favicon($remote_favicon_url,$save_as);
	}
	blf_cleanup_cache();
	$settings['refresh_date'] = time();
	update_option(BLF_SETTINGSNAME, $settings);
}
/**
 * smn_refresh_scheduler
 *
 * @package Blogroll Links Favicons
 * @since 2.0.5
 */
function smn_refresh_scheduler(){

}

/**
 * Save Local Copy of Remote Image File
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 *
 * @param    string    $remote_file_url
 * @param    string    $save_as
 */
function smn_cache_favicon($remote_file_url, $save_as){
	/* Variables */
	// note: filetype verification is probably redundant at this point but whatever
	$valid_extensions = array('ico','png','jpg','jpeg','gif','tif','tiff');
	$actual_extension = strtolower(end(explode(".",$save_as)));

	/* Main */
	if( in_array($actual_extension,$valid_extensions) ){
		// Get remote contents
		$remote_file_contents = file_get_contents($remote_file_url);
		// Name & Reference local
		$local_file_url = BLF_CACHE_DIR.'/'.$save_as;
		// Save local (if already exists, overwrite. if not exists, create new.)
		$local_file_actual = fopen($local_file_url, 'w+');
		chmod($local_file_url,0755);
		fwrite($local_file_actual, $remote_file_contents);
		fclose($local_file_actual);
	}
}

/**
 * Cleanup Icon Cache
 *
 * Deletes bunk (0 byte) favicons.
 *
 * @package Blogroll Links Favicons
 * @since 2.0.5
 */
function blf_cleanup_cache(){
	//	update_option(BLF_SETTINGSNAME,blf_default_settings());
	
	$cache_dir_contents = scandir(BLF_CACHE_DIR);
	foreach( $cache_dir_contents as $file ){
		if(filesize(BLF_CACHE_DIR.'/'.$file)==0){
			unlink(BLF_CACHE_DIR.'/'.$file);
//			echo $file."\r";
		}
	}
}
//add_action( 'admin_init','blf_cleanup_cache' );


/* HELPER METHODS */
/* ================================================== */

/**
 * smn_generate_local_favicon_filename
 *
 * Generate what to save the favicon as locally
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 *
 * @param    string    $bookmark_url
 * @return   string
 */
function smn_generate_local_favicon_filename($bookmark_url){
	$remote_favicon_url = smn_get_remote_favicon_url($bookmark_url);
	// We have to use the BOOKMARK's domain because sometimes the favicon's domain differs. (e.g. Wordpress.com's favicon is stored at somewhere like s1.wp.com)
	$bookmark_domain = parse_url($bookmark_url);
	$bookmark_domain = $bookmark_domain['host'];
	$remote_favicon_filename = basename($remote_favicon_url);
	$remote_favicon_extension = strtolower(end(explode(".",$remote_favicon_filename)));
	return $bookmark_domain.'.'.$remote_favicon_extension;
}


/**
 * smn_get_local_favicon_url
 *
 * Find URL of a Link's Locally Cached Favicon
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 *
 * @param    string    $link
 * @return   string
 */
function smn_get_local_favicon_url($link){
	$blf_settings = get_option(BLF_SETTINGSNAME);
	$blf_default_favicon = $blf_settings['default_favicon'];
		
	$link_domain = parse_url($link);
	$link_domain = $link_domain['host'];
	
//	$cache_dir_contents = scandir(BLF_CACHE_DIR);
	// add matching cached favicons
	$local_favicon_arr = glob(BLF_CACHE_DIR.'/'.$link_domain.'.*');
	// add default
	$local_favicon_arr[] = $blf_default_favicon;
	$local_favicon = end(explode('/',$local_favicon_arr[0]));
	return BLF_CACHE_URI.'/'.$local_favicon;
}

/**
 * smn_get_remote_favicon_url
 *
 * Find URL of a Link's Associated Favicon
 *
 * @package Blogroll Links Favicons
 * @since 2.0
 *
 * @param    string    $link
 * @return   string
 */
function smn_get_remote_favicon_url($link){
	$link = parse_url(noio_locate_icon($link));
	return $link['scheme'].'://'.$link['host'].$link['path'];
}


/*   ======================================================   */
/* ========================== NOIO ========================== */
/*   ======================================================   */


/**
 * noio_locate_icon
 *
 * @package {{@internal Missing Description}}}
 * @since {{@internal Missing Description}}}
 *
 * @param    string    $url
 * @return   string|bool
 */
function noio_locate_icon($url) {
	require_once( ABSPATH . 'wp-includes/class-snoopy.php');
	$snoopy = new Snoopy();
	$result = $snoopy->fetch($url);
    $html = $snoopy->results;
	if( $result ) {
		if (preg_match('/<link[^>]+rel="(?:shortcut )?icon"[^>]+?href="([^"]+?)"/si', $html, $matches)) {
			$linkUrl = html_entity_decode($matches[1]);
			if (substr($linkUrl, 0, 1) == '/') {
				$urlParts = parse_url($url);
				$faviconURL = $urlParts['scheme'].'://'.$urlParts['host'].$linkUrl;
			} else if (substr($linkUrl, 0, 7) == 'http://') {
				$faviconURL = $linkUrl;
			} else if (substr($url, -1, 1) == '/') {
				$faviconURL = $url.$linkUrl;
			} else {
				$faviconURL = $url.'/'.$linkUrl;
			}
		} else {
			$urlParts = parse_url($url);
			$faviconURL = $urlParts['scheme'].'://'.$urlParts['host'].'/favicon.ico';
		}
		if( $faviconURL_exists = noio_url_validate($faviconURL) )
			return $faviconURL;
	} 
	return false;
}

/**
 * noio_url_validate
 *
 * @package {{@internal Missing Description}}}
 * @since {{@internal Missing Description}}}
 *
 * @param    UNKNOWN_TYPE    $link
 * @return   BOOL
 */
function noio_url_validate( $link ) {
	$url_parts = @parse_url( $link );
	if ( empty( $url_parts["host"] ) )
		return false;
	if ( !empty( $url_parts["path"] ) ) {
		$documentpath = $url_parts["path"];
	} else {
		$documentpath = "/";
	}
	if ( !empty( $url_parts["query"] ) )
		$documentpath .= "?" . $url_parts["query"];
	$host = $url_parts["host"];
	$port = $url_parts["port"];
	if ( empty($port) )
		$port = "80";
	$socket = @fsockopen( $host, $port, $errno, $errstr, 30 );
	if ( !$socket )
		return false;
	fwrite ($socket, "HEAD ".$documentpath." HTTP/1.0\r\nHost: $host\r\n\r\n");
	$http_response = fgets( $socket, 22 );
	$responses = "/(200 OK)|(200 ok)|(30[0-9] Moved)/";
	if ( preg_match($responses, $http_response) ) {
		fclose($socket);
		return true;
	} else {
		return false;
	}
}

?>