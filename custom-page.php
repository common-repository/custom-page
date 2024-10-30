<?php
/*
Plugin Name: Custom Page
Plugin URI: http://wordpress.org/extend/plugins/custom-page/
Description: Custom Page plugin is use to customize page design easily and visually, just like using a simple webpage photoshop.
Author: GCLooi (devmsialink)
Author URI: http://dev.msialink.com/
Version: 2.1.0

This file is part of Custom Page Plugin
Copyright (C) 2013 G.C.Looi

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class CustomPage{
	var $version = '2.1.0';
	
	function CustomPage(){
		$this->__construct();
	}
	
	function __construct(){
		//Init Custom Page
		add_action( 'init', array(&$this, 'init_custom_page') );
		
		//Admin Panel
		add_action( 'admin_init', array($this, 'admin_init_custom_page') );
		add_action( 'admin_head', array($this, 'admin_custom_page_css') );
		add_action( 'admin_menu', array($this, 'custom_page_menu') );
		add_action( 'add_meta_boxes', array($this, 'admin_meta_boxes') );
		add_action( 'wp_insert_post', array($this, 'save_custom_page_meta'), 10, 2 );
		
		//Manage Custom Page
		add_action( 'manage_pages_columns', array($this, 'custom_page_columns') );
		add_action( 'manage_pages_custom_column', array($this, 'addon_custom_page_columns'), 10, 2 );
		
		//Render Custom Page
		add_filter( 'template_include', array($this, 'page_template'), 0 );
		add_filter( 'the_content', array($this, 'the_custom_page_content') );
		add_action( 'wp_head', array($this, 'custom_page_head') );
		
		//Notice on Actions
		add_action( 'admin_notices', array($this, 'custom_page_admin_notice') );
	}
	
	function init_custom_page() {
		global $error_cp_msg;

		if(isset($_POST['custom_page_action'])) {
			$post_id = intval($_POST['post_id']);
			$meta = get_post_meta($post_id, 'custom_page', true);
			
			if( $_POST['custom_page_action'] == 'add_element' ) {
				$element_id = esc_html($_POST['element_id']);
				
				if(empty($element_id)) {
					$error_cp_msg['id'] = __('*ID is Required', 'cp');
					return;
				} else {
					$data[$element_id] = $_POST;
					$this->update_elements($post_id, $meta, $data, true);
				}
				unset($_POST);
			} elseif ( $_POST['custom_page_action'] == 'update_elements' ) {
				$data = $_POST['update'];
				$this->update_elements($post_id, $meta, $data);
			}
		}
		
		if(isset($_GET['custom_page_action']) && $_GET['custom_page_action'] == 'reset_custom_page') {
			$post_id = intval($_GET['setup_custom']);
			$this->set_default_page($post_id);
		}
	}
	
	function admin_init_custom_page(){
		if(isset($_GET['activate_custom']) || isset($_GET['deactivate_custom'])){
			$nonce = $_GET['_wpnonce'];
			
			if ( !wp_verify_nonce($nonce) )
				die();
			
			$sendback = wp_get_referer();
			$sendback = remove_query_arg( array('message', 'activate_custom', 'deactivate_custom', 'activate_msg', 'deactivate_msg', '_wpnonce'), $sendback );
			
			if(is_numeric($_GET['activate_custom'])){
				$post_id = intval($_GET['activate_custom']);
				update_post_meta($post_id, 'custom_page_activate', 1);
				$sendback = add_query_arg( array('activate_msg' => '1'), $sendback );
				wp_redirect($sendback);
				exit;
			}
		
			if(is_numeric($_GET['deactivate_custom'])){
				$post_id = intval($_GET['deactivate_custom']);
				update_post_meta($post_id, 'custom_page_activate', 0);
				$sendback = add_query_arg( array('deactivate_msg' => '1'), $sendback );
				wp_redirect($sendback);
				exit;
			}
		}
		
		if(isset($_POST['custom_page_general_css']) || isset($_POST['custom_page_general_script'])) {
			$css = stripslashes($_POST['custom_page_general_css']);
			update_option( 'custom-page-general-css', $css );
			
			$script = stripslashes($_POST['custom_page_general_script']);
			update_option( 'custom-page-general-script', $script );
			
			$sendback = wp_get_referer();
			$sendback = add_query_arg( array('css_script' => '1'), $sendback );
			wp_redirect($sendback);
			exit;
		}
		
		if( isset($_GET['custom_page_action']) ){
			$sendback = wp_get_referer();
			
			$sendback = remove_query_arg( array('custom_page_action') );
			
			if($_GET['custom_page_action'] == 'update_old_custom_page'){
				$this->update_old_custom_page();
				$sendback = add_query_arg( array('update_old' => '1'), $sendback );
				wp_redirect($sendback);
				exit;
			} 
			
			if($_GET['custom_page_action'] == 'remove_old_custom_page') {
				delete_option('custom-page');
				$sendback = add_query_arg( array('remove_old' => '1'), $sendback );
				wp_redirect($sendback);
				exit;
			}
		}
	}
	
	function update_old_custom_page(){
		$options = get_option('custom-page');
		
		if(!empty($options)){
			$settings['css'] = $options['custom-page-settings']['css'];
			
			unset($options['custom-page-settings']);
			
			$elements = $options;
			
			$data = array();

			foreach($elements as $element){
				if($element['element_display'] == 'none'){
					$element['element_display_none'] == true;
					$element['element_display'] = 'text';
				}
				$element['element_link'] = $element['element_url'];
				$data[] = $element;
			}
			
			$my_post = array(
				'post_title'    => 'Custom Page Updated from Ver 1',
				'post_content'  => 'Custom Page update from Ver 1.x.x',
				'post_type' 	=> 'page',
				'post_status'   => 'publish',
				'post_author'   => 1,
			);
			
			$post_id = wp_insert_post( $my_post );
			
			$this->update_elements($post_id, array(), $data);

			update_post_meta( $post_id, 'custom_page_settings', $settings );
		}
	}
	
	function save_custom_page_meta($post_id, $post = null){
		if ( defined('DOING_AJAX') )
			return;
		
		if(!empty($_POST['custom_page_css'])){
			$settings['css'] = $_POST['custom_page_css'];
		}
		
		if(isset($_POST['disable_custom_page_on_mobile'])){
			$settings['disable_custom_page_on_mobile'] = 1;
		}
		
		if(isset($_POST['custom_page_theme_template'])){
			$settings['custom_page_theme_template'] = 1;
		}
		
		update_post_meta( $post_id, 'custom_page_settings', $settings );
	}
	
	function admin_meta_boxes(){
		add_meta_box( 'custom', 'Custom Page Settings', array(&$this, 'custom_page_settings'), 'page', 'normal', 'default' );
	}
	
	function custom_page_settings( $post, $metabox ){
		$post_id = $post->ID;
		$meta = get_post_meta($post_id, 'custom_page_settings', true);
		$custom_activate = get_post_meta($post_id, 'custom_page_activate', true);
		
		$content = '<div class="meta-options">';
		
		if(isset($_GET['post'])){
			if(!$custom_activate)
				$content .= '<div class="custom-pending"></div><a href="'.wp_nonce_url(add_query_arg( array('post' => $post_id, 'action' => 'edit', 'activate_custom'=> $post_id), admin_url( 'post.php' ))).'">'.__('Activate', 'cp').'</a>';
			else
				$content .= '<div class="custom-active"></div><a href="'.wp_nonce_url(add_query_arg( array('post' => $post_id, 'action' => 'edit', 'deactivate_custom'=> $post_id), admin_url( 'post.php' ))).'">'.__('Deactivate', 'cp').'</a>';
			$content .= '&nbsp;|&nbsp;<a href="'.wp_nonce_url(add_query_arg( array('setup_custom' => $post_id), get_permalink( $post_id ))).'">Setup</a><br><br>';
			
			$content .= '<label class="selectit" for="custom_page_theme_template">';
			if($meta['custom_page_theme_template'] == 1)
				$content .= '<input id="custom_page_theme_template" type="checkbox" name="custom_page_theme_template" checked="checked" />';
			else
				$content .= '<input id="custom_page_theme_template" type="checkbox" name="custom_page_theme_template" />';
			$content .= '&nbsp;'.__('Render Custom Page Layout on Theme Template', 'cp');
			$content .= '</label><br><span class="description">'.__('Custom Page Layout will render in Theme Template (e.g.: page.php).', 'cp').'</span><br><br>';
			
			$content .= '<label class="selectit" for="disable_custom_page_on_mobile">';
			if($meta['disable_custom_page_on_mobile'] == 1)
				$content .= '<input id="disable_custom_page_on_mobile" type="checkbox" name="disable_custom_page_on_mobile" checked="checked" />';
			else
				$content .= '<input id="disable_custom_page_on_mobile" type="checkbox" name="disable_custom_page_on_mobile" />';
			$content .= '&nbsp;'.__('Disable on Mobile', 'cp');
			$content .= '</label><br><span class="description">Disable Custom Page Layout on mobile device (post content will be shown).</span><br><br>';
			
			$content .= '<label>';
			$content .= __('CSS Layout', 'cp').'<br>';
			$content .= '<textarea class="custom_page_css" name="custom_page_css" cols="50" rows="10">'.$meta['css'].'</textarea>';
			$content .= '</label>';
		} else {
			$content .= '<p>'.__('*Save the Page before Setup and Activate Custom Page', 'cp').'</p>';
		}
		$content .= '</div>';
		
		echo $content;
	}
	
	function admin_custom_page_css(){
		echo '<style type="text/css">div.custom-active{background:url('.plugins_url('style/admin/tick_cross.png' , __FILE__).'); width:21px; height:20px; background-position:left; display:inline-block; vertical-align:bottom; margin-right: 5px;} div.custom-pending{background:url('.plugins_url('style/admin/tick_cross.png' , __FILE__).'); width:21px; height:20px; background-position:right; display:inline-block; vertical-align:bottom; margin-right: 5px;}textarea.custom_page_css{margin:0px; width:99%;}</style>';
	}
	
	function custom_page_head(){
		global $post;
		
		$css = get_option('custom-page-general-css');
		$script = get_option('custom-page-general-script');
		
		$head = '<style type="text/css">';
		$head .= 'div.custom-page-background *{ margin: 0; padding: 0; border: 0; font-size: 100%; vertical-align: baseline;}';
		$head .= $css;
		$head .= '</style>';
		
		$head .= '<script type="text/javascript">';
		$head .= $script;
		$head .= '</script>';
		
		echo $head;
	}
	
	function custom_page_columns($columns){
		$extra_columns['custom'] = __('Custom Page', 'cp');
		$extra_columns['custom-shortcode'] = __('Custom Page Shortcode', 'cp');
		return array_merge($columns, $extra_columns);
	}
	
	function addon_custom_page_columns($column, $post_id){
		$custom_activate = get_post_meta($post_id, 'custom_page_activate', true);
		
		switch ( $column ){
			case 'custom':
				if(!$custom_activate)
					echo '<div class="custom-pending"></div><a href="'.wp_nonce_url(add_query_arg( array('activate_custom' => $post_id), admin_url('edit.php?post_type=page'))).'">Activate</a>';
				else
					echo '<div class="custom-active"></div><a href="'.wp_nonce_url(add_query_arg( array('deactivate_custom' => $post_id), admin_url('edit.php?post_type=page'))).'">Deactivate</a>';
				echo '&nbsp;|&nbsp;';
				echo '<a href="'.wp_nonce_url(add_query_arg( array('setup_custom' => $post_id), get_permalink( $post_id ))).'">Setup</a>';
				break;
			case 'custom-shortcode':
				echo '[custom-page id="'.$post_id.'"]';
				break;
		}
	}
	
	function page_template($template){
		global $wp_query, $post;
		$post_id = $post->ID;
		$custom_activate = get_post_meta($post_id, 'custom_page_activate', true);
		$settings = get_post_meta($post_id, 'custom_page_settings', true);
		
		wp_enqueue_script( array('jquery') ); //init jquery script for general script
		
		if($wp_query->is_page && isset($_GET['setup_custom']) ) {
			$nonce = $_GET['_wpnonce'];
			
			if ( !wp_verify_nonce($nonce) )
				die();
			
			wp_enqueue_script( array('editor', 'thickbox', 'media-upload') );
			wp_enqueue_script('custom-page', plugins_url('scripts/custom-page.js' , __FILE__), array('jquery'));
			
			wp_register_style('custom-page', plugins_url('style/custom-page.css' , __FILE__));
			wp_enqueue_style('custom-page' );
			
			wp_register_style('jquery-ui-custom', plugins_url('style/custom-theme/jquery-ui-1.8.23.custom.css' , __FILE__));
			wp_enqueue_style('jquery-ui-custom' );
			
			return dirname(__FILE__).'/templates/setup.php';
		} elseif($wp_query->is_page && !isset($_GET['setup_custom']) && $custom_activate && !$settings['custom_page_theme_template']){
			if ( $post_id ){
				$layout[] = "custom-page-$post_id.php";
			}
			$layout[] = "custom-page.php";
			
			$layout = locate_template($layout);
			
			if(!empty($layout)){
				return $layout;
			} else {
				return dirname(__FILE__).'/templates/default.php';
			}
		}
		
		return $template;
	}
	
	function the_custom_page_content($content){
		global $post;
		$post_id = $post->ID;
		$custom_activate = get_post_meta($post_id, 'custom_page_activate', true);
		$settings = get_post_meta($post_id, 'custom_page_settings', true);

		if($settings['custom_page_theme_template'] && $custom_activate)
			return render_custom_page($post_id, false);
		else
			return $content;
	}
	
	function update_elements($post_id = 0, $current = array(), $data = array(), $add = false){
		global $error_cp_msg;
		
		if(empty($post_id)) {
			return;
		}
		
		foreach($data as $val) {
			$element_id = str_replace(array('-', '_'), array('', ''), sanitize_key($val['element_id']));
			
			if($add == true){
				if(array_key_exists($element_id, $current)){
					$error_cp_msg['id'] = __('*ID is Existed', 'cp');
					return;
				}
			}	
			
			if($val['element_remove'] == false) {
				$update[$element_id]['element_layer'] = intval($val['element_layer']);
				
				$width = (intval($val['element_width']) < 10)? 10 : intval($val['element_width']);
				$height = (intval($val['element_height']) < 10)? 10 : intval($val['element_height']);
				$update[$element_id]['element_width'] = $width;
				$update[$element_id]['element_height'] = $height;
				
				$update[$element_id]['element_left'] = intval($val['element_left']);
				$update[$element_id]['element_top'] = intval($val['element_top']);
				$update[$element_id]['element_link'] = esc_url($val['element_link']);
				$update[$element_id]['element_link_target'] = esc_html($val['element_link_target']);
				$update[$element_id]['element_image'] = esc_url($val['element_image']);
				$update[$element_id]['element_text'] = wpautop(stripslashes($val['element_text']));
				
				$display = (empty($val['element_display']))? 'text' : esc_html($val['element_display']);
				$update[$element_id]['element_display'] = $display;
				
				$update[$element_id]['element_display_none'] = (empty($val['element_display_none']))? false: true;
				
				$update[$element_id]['element_text_padding']['top'] = intval($val['element_text_padding']['top']);
				$update[$element_id]['element_text_padding']['right'] = intval($val['element_text_padding']['right']);
				$update[$element_id]['element_text_padding']['bottom'] = intval($val['element_text_padding']['bottom']);
				$update[$element_id]['element_text_padding']['left'] = intval($val['element_text_padding']['left']);
			} else {
				unset($current[$element_id]);
			}
			
			if( empty($update) ) {
				$this->set_default_page($post_id);
				return;
			}
		}
		
		$save = array_merge($current, $update);
		
		update_post_meta($post_id, 'custom_page', $save);
	}
	
	function custom_page_menu(){
		add_menu_page(__('Custom Page', 'cp'), __('Custom Page', 'cp'), 'administrator', 'custom-page',  array($this, 'custom_index'));
		add_submenu_page( 'custom-page', __('General Settings', 'cp'), __('General Settings', 'cp'), 'administrator', 'custom-page-settings', array($this, 'general_settings') );
	}
	
	function custom_index(){
		$content = '<div class="wrap">';
		$content .= '<h2>'.__('Custom Page', 'cp').'</h2>';
		
		$content .= '<h3>'.__('Thank You for Using Custom Page Plugin Ver ', 'cp').$this->version.'</h3>';
		$content .= '<p><ol>';
		$content .= '<li>'.__('Custom Page Plugin let you customize page design easily and visually.', 'cp').'</li>';
		$content .= '<li>'.__('Please visit my website for more information.', 'cp').'&nbsp;<a href="http://dev.msialink.com" target="_blank">http://dev.msialink.com</a>'.'</li>';
		$content .= '<li>'.__('Please email to me if you have any enquiries or suggestions.', 'cp').'&nbsp;<a href="mailto:dev@msialink.com">dev@msialink.com</a>'.'</li>';
		$content .= '<li>'.__('If you found the plugin is useful, rate it 5 stars now!', 'cp').'&nbsp;<a href="http://wordpress.org/extend/plugins/custom-page/" target="_blank">http://wordpress.org/extend/plugins/custom-page/</a>'.'</li>';
		$content .= '<li>'.__('Custom Page Plugin appreciate your donation!', 'cp').'<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="FYVT3QWQT3XNS"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form></li>';
		$content .= '</ol></p>';
		
		$content .= '<h3>'.__('Basic User Manual', 'cp').'</h3>';
		$content .= '<p><ol>';
		$content .= '<li>'.__('Install and Activate Custom Page Plugin.', 'cp').'</li>';
		$content .= '<li>'.__('Click <strong>Pages</strong> tab on Admin Dashboard.', 'cp').'</li>';
		$content .= '<li>'.__('<strong>Setup</strong> the Custom Page and <strong>Activate</strong> after publish the Page.', 'cp').'</li>';
		$content .= '<li>'.__('Add General CSS or Javascript on <strong>Custom Page &raquo; General Settings</strong>.', 'cp').'</li>';
		$content .= '<li>'.__('Create a New Page with Ver 1.x.x Custom Page Elements. <strong>Custom Page &raquo; General Settings</strong>', 'cp').'</li>';
		$content .= '<li>'.__('Customize layout by create <strong>custom-page.php</strong> or specific <strong>custom-page-<i>post_id</i>.php</strong> in the theme templates and call function <strong>&lt;?php render_custom_page(); ?&gt;</strong>.', 'cp').'</li>';
		$content .= '<li>'.__('Use shortcode function <strong>&lt;?php echo do_shortcode( &apos;[custom-page id="<i>post_id</i>"]&apos; ); ?&gt;</strong> to call Custom Page layout at theme templates.', 'cp').'</li>';
		$content .= '<li>'.__('Demo for Custom Page Plugin.', 'cp').'&nbsp;<a href="http://dev.msialink.com/custom-page/" target="_blank">http://dev.msialink.com/custom-page/</a>'.'</li>';
		$content .= '</ol></p>';
		
		$content .= '</div>';
		echo $content;
	}
	
	function general_settings(){
		$css = get_option('custom-page-general-css');
		$script = get_option('custom-page-general-script');
		$old = get_option('custom-page');
		
		$content = '<div class="wrap">';
		$content .= '<h2>'.__('Custom Page', 'cp').'</h2>';
		$content .= '<form action="" method="post">';
		$content .= '<h3>'.__('General Settings', 'cp').'</h3>';
		$content .= '<table class="form-table">';
		$content .= '<tbody>';
		
		$content .= '<tr valign="top">';
		$content .= '<th scope="row">';
		$content .= '<label for="description">'.__('Custom Page General CSS', 'cp').'</label>';
		$content .= '</th>';
		$content .= '<td>';
		$content .= '<textarea id="custom_page_general_css" class="large-text code" cols="50" rows="10" name="custom_page_general_css">'.$css.'</textarea><br>';
		$content .= '<span class="description">'.__('General CSS for Custom Page call on wp_head init.', 'cp').'</span>';
		$content .= '</td>';
		$content .= '<tr>';
		
		$content .= '<tr valign="top">';
		$content .= '<th scope="row">';
		$content .= '<label for="description">'.__('Custom Page JavaScript', 'cp').'</label>';
		$content .= '</th>';
		$content .= '<td>';
		$content .= '<textarea id="custom_page_general_script" class="large-text code" cols="50" rows="10" name="custom_page_general_script">'.$script.'</textarea><br>';
		$content .= '<span class="description">'.__('JavaScript for Custom Page call on wp_head init (jQuery is embeded).', 'cp').'</span>';
		$content .= '</td>';
		$content .= '<tr>';
		
		$content .= '</tbody>';
		$content .= '</table>';
		
		$content .= '<p class="submit"><input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit"></input></p>';
		
		$content .= '</form>';
		
		if($old) {
			$content .= '<h3>'.__('Update Old Custom Page', 'cp').'</h3>'; 
			$content .= '<p>'.__('Create a New Page with Ver 1.x.x Custom Page Elements. <br>The Layout Maybe Vary due to New Structure of DOM.', 'cp').'</p>';
			$content .= '<a onClick="return confirm(&#39;'.__('A New Page will be Create with the Ver 1.x.x Elements.','cp').'&#39;)" href="'.add_query_arg( array('page' => 'custom-page-settings', 'custom_page_action' => 'update_old_custom_page'), admin_url( 'admin.php' )).'">Update</a>&nbsp;|&nbsp;';
			$content .= '<a onClick="return confirm(&#39;'.__('Confirm Remove Old Custom Page?','cp').'&#39;)" href="'.add_query_arg( array('page' => 'custom-page-settings', 'custom_page_action' => 'remove_old_custom_page'), admin_url( 'admin.php' )).'">Remove</a>';
		}
		
		$content .= '</div>';
		
		echo $content;
	}
	
	function set_default_page($post_id){
		if(!empty($post_id)) {
			$save['background']['element_layer'] = 0;
			$save['background']['element_width'] = 1024;
			$save['background']['element_height'] = 768;
			$save['background']['element_left'] = 0;
			$save['background']['element_top'] = 0;
			$save['background']['element_link'] = '';
			$save['background']['element_link_target'] = '';
			$save['background']['element_image'] = '';
			$save['background']['element_text'] = '<p style="text-align: center;">Thank you for using Custom Page plugin. Please click <a href="http://dev.msialink.com/custom-page" target="_blank">here</a> for more information.</p>';
			$save['background']['element_display'] = 'text';
			$save['background']['element_text_padding']['top'] = 0;
			$save['background']['element_text_padding']['right'] = 0;
			$save['background']['element_text_padding']['bottom'] = 0;
			$save['background']['element_text_padding']['left'] = 0;
				
			update_post_meta($post_id, 'custom_page', $save);
		} else {
			return;
		}
	}
	
	function custom_page_admin_notice() {
		if($_GET['activate_msg'])
			$msg = '<div class="updated"><p>'.__('Custom Page Activated.', 'cp').'</p></div>';
		elseif($_GET['deactivate_msg'])
			$msg = '<div class="updated"><p>'.__('Custom Page Deactivated.', 'cp').'</p></div>';
		elseif($_GET['css_script'])
			$msg = '<div class="updated"><p>'.__('General Settings for Custom Page Updated.', 'cp').'</p></div>';
		elseif($_GET['update_old'])
			$msg = '<div class="updated"><p>'.__('A New Page with Ver 1.x.x Custom Page Elements is Created.', 'cp').'</p></div>';
		elseif($_GET['remove_old'])	
			$msg = '<div class="updated"><p>'.__('Ver 1.x.x Custom Page Elements is Removed.', 'cp').'</p></div>';
			
		echo $msg;
	}
}

global $cp;
$cp = new CustomPage();

/*
 * Render Custom Page in Page Template Using Function
 */
function get_content_by_id($post_id) {
	$args = array(
		'page_id' => $post_id
	);
	
	$query = new WP_Query( $args );
	
	$content = $query->post->post_content;
	
	return $content;
}

function render_custom_page($post_id = null, $echo = true){
	global $post;
	
	if(empty($post_id))
		$post_id = $post->ID;
		
	$settings = get_post_meta($post_id, 'custom_page_settings', true);
	
	$content = '';
	
	if((!empty($settings['disable_mobile']) && wp_is_mobile())){
		$content = get_content_by_id($post_id);
		
		if($echo == true) {
			echo $content;
			return;
		} else {
			return $content;
		}
	}
	
	$meta = get_post_meta($post_id, 'custom_page', true);
	
	if( !is_array($meta) ) {
		return get_content_by_id($post_id);
	}
	
	$t_width = $meta['background']['element_width'] - $meta['background']['element_text_padding']['left'] - $meta['background']['element_text_padding']['right'];
	$t_height = $meta['background']['element_height'] - $meta['background']['element_text_padding']['top'] - $meta['background']['element_text_padding']['bottom'];
	
	$style = '';
	$style .= '<style type="text/css">';
	$style .= 'div.page-'.$post_id.'-background{position: relative; margin: auto; display: block; width: '.$meta['background']['element_width'].'px; height: '.$meta['background']['element_height'].'px; z-index: '.$meta['background']['element_layer'].';}';
	$style .= 'div.page-'.$post_id.'-background-text{position: absolute; display: block; top: 0; left: 0; width :'.$t_width.'px; height: '.$t_height.'px; padding: '.$meta['background']['element_text_padding']['top'].'px '.$meta['background']['element_text_padding']['right'].'px '.$meta['background']['element_text_padding']['bottom'].'px '.$meta['background']['element_text_padding']['left'].'px;}';
	$style .= 'img.page-'.$post_id.'-background-image{width: '.$meta['background']['element_width'].'px; height: '.$meta['background']['element_height'].'px}';
	if($meta['background']['element_display_none'] == true){
		$style .= 'div.page-'.$post_id.'-background-text{display: none;}';
		$style .= 'img.page-'.$post_id.'-background-image{display: none;}';
	}
	$style .= '</style>';
	
	//background style
	$elements = '';
	
	switch ($meta['background']['element_display']){
		case 'text':
			$elements .= '<div class="page-'.$post_id.'-background-text">'.do_shortcode($meta['background']['element_text']).'</div>';
			break;
		case 'image':		
			$elements .= '<img class="page-'.$post_id.'-background-image" src="'.$meta['background']['element_image'].'" alt="custom-background" />';
			break;
		case 'both':
			$elements .= '<img class="page-'.$post_id.'-background-image" src="'.$meta['background']['element_image'].'" alt="custom-background" />';
			$elements .= '<div class="page-'.$post_id.'-background-text">'.do_shortcode($meta['background']['element_text']).'</div>';
			break;
		case 'none':
			break;	
	}
	
	unset($meta['background']);
	
	$style .= page_layouts($post_id, $meta); 
	$elements .= page_elements($post_id, $meta);
	
	$content .= $style;
	$content .= '<div class="custom-page-background page-'.$post_id.'-background">';
	$content .= $elements;
	$content .= '</div><!-- Using Custom Page Plugin for Wordpress http://dev.msialink.com !-->';
	
	if($echo == true)
		echo $content;
	else
		return $content;
}

/*
 * Return Element 
 */
function page_elements($post_id, $meta){
	$elements = '';
	
	foreach($meta as $key => $val) {
		switch( $val['element_display'] ) {
			case 'text':
				$elements .= '<div class="element page-'.$post_id.'-'.$key.'-element">';
				if(!empty($val['element_text']))
					$elements .= '<div class="element-text page-'.$post_id.'-'.$key.'-text">'.do_shortcode($val['element_text']).'</div>';
				$elements .= '</div>';
				break;
			case 'image':
				$elements .= '<div class="element page-'.$post_id.'-'.$key.'-element">';
				if(!empty($val['element_link']))
					$elements .= '<a class="element-link page-'.$post_id.'-'.$key.'-link" href="'.$val['element_link'].'" target="'.$val['element_link_target'].'">';
				$elements .= '<img class="element-image page-'.$post_id.'-'.$key.'-image" src="'.$val['element_image'].'" alt="'.$key.'-image" />';
				if(!empty($val['element_link']))
					$elements .= '</a>';
				$elements .= '</div>';
				break;
			case 'link':
				$elements .= '<div class="element page-'.$post_id.'-'.$key.'-element">';
				if(!empty($val['element_link']))
					$elements .= '<a class="element-link page-'.$post_id.'-'.$key.'-link" href="'.$val['element_link'].'" target="'.$val['element_link_target'].'"></a>';
				$elements .= '</div>';	
				break;	
			case 'both':
				$elements .= '<div class="element page-'.$post_id.'-'.$key.'-element">';
				$elements .= '<img class="element-image page-'.$post_id.'-'.$key.'-image" src="'.$val['element_image'].'" alt="'.$key.'-image" />';
				$elements .= '<div class="element-text page-'.$post_id.'-'.$key.'-text">'.do_shortcode($val['element_text']).'</div>';
				$elements .= '</div>';
				break;	
		}
	}
	
	return $elements;
}

function page_layouts($post_id, $meta){
	$settings = get_post_meta($post_id, 'custom_page_settings', true);
	
	$layouts = '<style type="text/css">';
	
	foreach($meta as $key => $val) {
		$layouts .= 'div.page-'.$post_id.'-'.$key.'-element{';
		$layouts .= 'position: absolute; display: block;';
		$layouts .= 'left: '.$val['element_left'].'px;';
		$layouts .= 'top: '.$val['element_top'].'px;';
		$layouts .= 'width: '.$val['element_width'].'px;';
		$layouts .= 'height: '.$val['element_height'].'px;';
		$layouts .= 'z-index: '.$val['element_layer'].';';
		$layouts .= '}';
		$layouts .= 'div.page-'.$post_id.'-'.$key.'-text{';
		$layouts .= 'position: absolute; left:0; top:0;';
		$layouts .= 'width: '.($val['element_width'] - $val['element_text_padding']['right'] - $val['element_text_padding']['left']).'px;';
		$layouts .= 'height: '.($val['element_height'] - $val['element_text_padding']['top'] - $val['element_text_padding']['bottom']).'px;';
		$layouts .= 'padding: '.$val['element_text_padding']['top'].'px '.$val['element_text_padding']['right'].'px '.$val['element_text_padding']['bottom'].'px '.$val['element_text_padding']['left'].'px ;';
		$layouts .= '}';
		$layouts .= 'img.page-'.$post_id.'-'.$key.'-image{';
		$layouts .= 'width: '.$val['element_width'].'px;';
		$layouts .= 'height: '.$val['element_height'].'px;';
		$layouts .= '}';
		$layouts .= 'a.page-'.$post_id.'-'.$key.'-link{';
		$layouts .= 'display: block;';
		$layouts .= 'width: '.$val['element_width'].'px;';
		$layouts .= 'height: '.$val['element_height'].'px;';
		$layouts .= '}';
		if( $val['element_display_none'] ) {
			$layouts .= 'div.page-'.$post_id.'-'.$key.'-element{';
			$layouts .= 'display: none;';
			$layouts .= '}';
		}
	}
	
	$layouts .= $settings['css'];
	
	$layouts .= '</style>';
	
	return $layouts;
}

/*
 * Call Custom Page Using Shortcode
 */
function custom_page_shortcode($atts) {
	extract( shortcode_atts(array('id' => 0), $atts) );
	
	if($id == 0){
		return __('<span style="color:#F00;">*No ID is specific.</span>', 'cp');	
	} else {
		global $post;
		
		if((intval($id) == $post->ID)){
			$content = __('<span style="color:#F00;">*ID is illegal.</span>', 'cp');
		} else {
			$content = render_custom_page($id, false);
		}
		
		return $content;
	}
}

add_shortcode( 'custom-page', 'custom_page_shortcode' );
