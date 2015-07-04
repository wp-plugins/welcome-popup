<?php 
/*
Plugin Name: Welcome Popup
Plugin URI: http://technet.weblineindia.com/plugins/wordpress/wordpress-plugin-welcome-popup/
Description: Increase user interactivity and create curiosity by welcoming your visitors with a personalized message via Popup message. This plugin will allow WordPress site admin to set a personalized message for every visitor, they visit the site first time.
Version: 1.0.1
Author: Weblineindia
Author URI: http://www.weblineindia.com
License: GPL
*/

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );
$plugin_version = $plugin_data['Version'];

$file='';

$url = plugin_dir_url($file) . dirname(plugin_basename(__FILE__)) .'/js/modal.js';

register_activation_hook(__FILE__, 'welcome_popup_activate');
register_uninstall_hook(__FILE__, 'welcome_popup_uninstall');


/**
 *  This function is called when the plugin is activated.
 *
 *  @return             void
 *  @var                No arguments passed
 *  @author             AC
 */
function welcome_popup_activate() {
	global $plugin_version;
	
	$default_value = array(
			'version' => $plugin_version,
			'title' => 'Title',
			'content' => 'This is the default content',
			'first_visit' => '1',
			'time' => '0',
			'display_never' => '1',
			'exclude_fields' => '');

	add_option('welcome_popup_settings',$default_value);
}


/**
 *  This function is called when the plugin is uninstalled.
 *
 *  @return             void
 *  @var                No arguments passed
 *  @author             AC
 */
function welcome_popup_uninstall()
{
	delete_option('welcome_popup_settings',$default_value);
}


/**
 *  This function is use to link the admin css file.
 *
 *  @return             void
 *  @var                No arguments passed
 *  @author             AC
 */
function welcome_popup_load_preview(){
	global $file;
	$option_css = plugin_dir_url($file) . dirname(plugin_basename(__FILE__)) .'/css/options.css';
	wp_register_style( 'option_css', $option_css );
	wp_enqueue_style( 'option_css' );
}
add_action('admin_enqueue_scripts','welcome_popup_load_preview');


/**
 *  This function is used to include js file for the popup.
 *
 *  @return             void
 *  @var                No arguments passed
 *  @author             AC
 */
function welcome_popup_jquery_enqueuescripts() {
	global $url, $plugin_version;
	$popup_time = get_welcome_popup_setting( 'time' );

	if( !wp_script_is( 'jquery' ) )
	{
		wp_enqueue_script('jquery');
	}

	wp_enqueue_script('jquery_welcome_model', $url, array('jquery'), $plugin_version);

	$translation_array = array( 'popup_time' =>  $popup_time );

	wp_localize_script( 'jquery_welcome_model', 'welcomePopup', $translation_array );
}
add_action('wp_enqueue_scripts', 'welcome_popup_jquery_enqueuescripts');


/**
 *  When the plugin is loaded this function is called to load the plugin's translated string.
 *
 *  @return             void
 *  @var                No arguments passed
 *  @author             AC
 */
function welcome_popup_init() {
	load_plugin_textdomain( 'welcome_popup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'welcome_popup_init');


/**
 *  This function is used to get the options value from the database
 *
 *  @return             It will return the current value for the specified key
 *  @var                The key is passed to retrieve its value
 *  @author             AC
 */
function get_welcome_popup_setting($key= '')
{
	if($key == '')
		return '';
	else
	{
		$current_option = get_option('welcome_popup_settings');
		if(isset($current_option[$key])) {
			return $current_option[$key];
		}
		else
			return '';
	}
}


/**
 *  This function is used to update the option value pair to the database.
 *
 *  @return             The function returns true if the value is updated else false
 *  @var                The key to update and the new value for this key is passed
 *  @author             AC
 */
function update_all_settings($key= '', $value = '')
{
	$msg = 0;
	if($key == '')
		return true;
	else
	{
		update_option('welcome_popup_settings',$value);
		$msg = 1;
	}
	return $msg;
}


/**
 *  This function is use to add the submenu page to the setting menu.
 *
 *  @return             String
 *  @var                No arguments passed
 *  @author             AC
 */
function welcome_popup_admin_add_page() {
	add_options_page('Welcome Popup Settings Page', 'Welcome Popup', 'manage_options', 'welcome_popup_page', 'welcome_popup_options_page');
}
add_action('admin_menu', 'welcome_popup_admin_add_page');


/**
 *  This function is use to make the html page for the settings page.
 *
 *  @return             form
 *  @var                No arguments passed
 *  @author             AC
 */
function welcome_popup_options_page() {
	global $msg_box;
	if(isset($_POST['welcome_popup_submit'])) {

		if(isset($_POST['display_never'])){
			$dis_never = $_POST['display_never'];
		}
		else {
			$dis_never = 0;
		}

		if(isset($_POST['first_visit'])){
			$fir_visit = $_POST['first_visit'];
		}
		else {
			$fir_visit = 0;
		}


		if(isset($_POST['exclude_fields'])){
			$exclude_page = $_POST['exclude_fields'];
		}
		else{
			$exclude_page = '';
		}

		$changed_value = array(
				'title' => $_POST['title'],
				'content' => $_POST['content'],
				'first_visit' => $fir_visit,
				'time' => $_POST['time'],
				'display_never' => $dis_never,
				'exclude_fields' => $exclude_page
		);
		$msg_box = update_all_settings('welcome_popup_settings', $changed_value);
	}
	global $file;
?>
<div class="wrap">
	<h2>
		<?php _e('Welcome Popup Options','welcome_popup');?>
	</h2>
	<?php if($msg_box) {?>
	<div class="updated">
		<p>
			<strong><?php _e('Settings Saved','welcome_popup');?></strong>
		</p>
	</div>
	<?php }?>
	<form method="post">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><?php _e('Title:','welcome_popup');?></th>
					<td><input name="title" type="text" id="title" class="regular-text" value="<?php echo esc_attr(get_welcome_popup_setting( 'title' ));?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Content:','welcome_popup');?></th>
					<td>
					<?php 
					wp_editor(
						stripslashes(get_welcome_popup_setting( 'content' )), 
						'popup_content', 
						array(
							'media_buttons' => false,
							'quicktags'     => array("buttons"=>"strong,em,link,b-quote,del,ins,img,ul,ol,li,code,close"),
							'textarea_name' => 'content',
							'textarea_rows' => 4,
							'tinymce'	=> false,
						) 
					);
					?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Only on first visit:','welcome_popup');?></th>
					<td><input name="first_visit" type="checkbox" value="1" <?php  checked( '1', get_welcome_popup_setting( 'first_visit' ) ); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Popup delay (in seconds):','welcome_popup');?></th>
					<td><input name="time" type="text" id="time" class="regular-text" value="<?php echo esc_attr(get_welcome_popup_setting( 'time' ));?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Show Display never link:','welcome_popup');?></th>
					<td><input type="checkbox" name="display_never" value="1" <?php checked( '1', get_welcome_popup_setting( 'display_never' ) ); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Exclude Pages:','welcome_popup');?></th>
					<?php  $exclude_fields = get_welcome_popup_setting('exclude_fields');  ?>
					<td>
						<select name="exclude_fields[]" size="3" multiple="multiple" tabindex="1" id="exclude_pages">
						<?php 
						$pages = get_pages();
						foreach ( $pages as $page ) { ?>
						<option
						<?php if (is_array($exclude_fields) && in_array($page->ID,$exclude_fields)) {echo "selected=selected";}?>
							value="<?php echo esc_attr($page->ID); ?>">
							<?php echo __("$page->post_title",'welcome_popup'); ?>
						</option>
						<?php 
						echo $option;
						}
						?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"></th>
					<td>
						<p class="submit">
							<input type="submit" name="welcome_popup_submit" id="welcome_popup_submit" class="button button-primary" value="<?php _e('Save Changes','welcome_popup');?>">
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<?php 
}


/**
 *  This function is use to set the cookie on certain conditions.
 *
 *  @return             Cookie on  UI side
 *  @var                No arguments passed
 *  @author             AC
 */
function welcome_popup_set_cookie() {
	global $post,$file, $url,$flag;
	$flag = 0;
	$post->ID;
	$exclude_fields = get_welcome_popup_setting('exclude_fields');

	$url_css = plugin_dir_url($file) . dirname(plugin_basename(__FILE__)) .'/css/mystyle.css';
	wp_register_style( 'plugin_css', $url_css );
	wp_enqueue_style( 'plugin_css' );
	
	if($exclude_fields!='')
	{
		if(!in_array($post->ID, $exclude_fields)){
			$flag = 1;
			if (!isset($_COOKIE['visit'])) {
				ob_start();
				setcookie('visit', 'set', time()+60,COOKIEPATH, COOKIE_DOMAIN, false);
				ob_flush();
			}
		}
	}
	else {
		$flag = 1;
		if (!isset($_COOKIE['visit'])) {
			ob_start();
			setcookie('visit', 'set', time()+60,COOKIEPATH, COOKIE_DOMAIN, false);
			ob_flush();
		}
	}
}
add_action( 'wp', 'welcome_popup_set_cookie' );


/**
 *  This function is used to show popup on the front side on certain conditions.
 *
 *  @return             It will show the popup
 *  @var                No arguments passed
 *  @author             AC
 */
function get_welcome_popup() {
	global $flag;
	$show_hide = get_welcome_popup_setting('display_never');
	$fir_visit = get_welcome_popup_setting( 'first_visit' );
	if($fir_visit == '1')
	{
		if (isset($_COOKIE['visit']))
		{
			/* echo "dontshowpopup"; */
		}
		else
		{
			if(!isset($_COOKIE['popup'])){

				$popup_title = get_welcome_popup_setting( 'title' );
				$content = stripslashes(get_welcome_popup_setting( 'content' ));
				$popup_content = apply_filters('the_content', $content);
				$popup_time = get_welcome_popup_setting( 'time' );

				if($popup_title == '') {
					$output = '<div class="popup_bg">
					<div class="popup_block">
					<div class="inner">
					<a href="#" class="btn_close" title="'.__("Close","welcome_popup").'">'.__("Close","welcome_popup").'</a>
					<div class="content_box blank">
					<p>'.$popup_content.'</p>';
				}
				
				else {
					$output = '<div class="popup_bg">
					<div class="popup_block">
					<div class="inner">
					<a href="#" class="btn_close" title="'.__("Close","welcome_popup").'">'.__("Close","welcome_popup").'</a>
					<div class="heading_block">
					<span class="sprite icon01"></span>
					<div class="heading01">'.$popup_title.'</div>
					</div>
					<div class="content_box">
					<p>'.$popup_content.'</p>';
				}

				if($show_hide == 1) {
					$output = $output.'<p class="display"><a href="#">'.__("Dont Display Again","welcome_popup").'</a></p>';
				}

				$output = $output.'</div></div></div></div><div id="overlay" style="display: block;"></div>';

				if($flag == 1) {
					echo $output;
					$flag=0;
				}
			}
			else {
				/* echo "cookie set for display never so will not appear"; */
			}
		}
	}
	else {
		if(!isset($_COOKIE['popup'])){
			$exclude_fields = get_welcome_popup_setting('exclude_fields');
			$show_hide = get_welcome_popup_setting('display_never');
			$popup_title = get_welcome_popup_setting( 'title' );
			$content = stripslashes(get_welcome_popup_setting( 'content' ));
			$popup_content = apply_filters('the_content', $content);
			$popup_time = get_welcome_popup_setting( 'time' );

			if($popup_title == '') {
				$output = '<div class="popup_bg">
				<div class="popup_block">
				<div class="inner">
				<a href="#" class="btn_close" title="'.__("Close","welcome_popup").'">'.__("Close","welcome_popup").'</a>
				<div class="content_box blank">
				<p>'.$popup_content.'</p>';
			}
			else {
				$output = '<div class="popup_bg">
				<div class="popup_block">
				<div class="inner">
				<a href="#" class="btn_close" title="'.__("Close","welcome_popup").'">'.__("Close","welcome_popup").'</a>
				<div class="heading_block">
				<span class="sprite icon01"></span>
				<div class="heading01">'.$popup_title.'</div>
				</div>
				<div class="content_box">
				<p>'.$popup_content.'</p>';
			}
			
			if($show_hide == 1) {
				$output = $output.'<p class="display"><a href="#">'.__("Dont Display Again","welcome_popup").'</a></p>';
			}

			$output = $output.'</div></div></div></div><div id="overlay" style="display: block;"></div>';

			if($flag == 1) {
				echo $output;
				$flag=0;
			}
		}
		else {
			/* echo "cookie set for display never so will not appear and first visit not checked"; */
		}
	}
}

add_action('wp_head', 'get_welcome_popup');?>
