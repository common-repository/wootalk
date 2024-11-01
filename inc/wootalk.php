<?php
/**
 * WooTalk main class
 *
 */

defined( 'ABSPATH' ) || exit;
 /**
 * Include model WootalkModel
 */	
if(!class_exists('WootalkModel')){
	$_model = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'model/WootalkModel.php';
	
	if( file_exists($_model))
		include_once($_model);
	else
		die('model! not found '.$_fra_modelmework);
}

/**
 * Include Global function file
 */	
 
$_functionVal = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wootalk-functions.php';
if( file_exists($_functionVal))
	include_once($_functionVal);
else
	die('functions ! not found '.$_functionVal);

/**
 * Include Global function file
 */	
 
$_woo_image = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wootalk-images.php';
if( file_exists($_woo_image))
	include_once($_woo_image);
else
	die('functions ! not found '.$_woo_image);

 
/**
 * Main WooTalk Class.
 *
 * @class wootalk
 */	
 
class wootalk {
	
	
	public $version = '1.0';
	
	protected static $_instance = null;
    
	public $plugin_basename;
	
	protected $order_id,$model;
	
	protected $order_valid;
	
	protected $is_active_settings;
	
	/**
	* WooTalk tables.
	*/
	
	static $tbl_wootalk = 'wootalk_conversation';

	/**
	* WooTalk file dir.
	*/
	protected $upload_dir_name = 'wootalk_files';
    
	/**
	* WooTalk Constructor.
	*/
	 
	public function __construct() {
		$this -> order_id = isset($_REQUEST['order']) ? intval($_REQUEST['order']) : true;
		if((isset($_REQUEST['email'] ) && $_REQUEST['email'] == '') || (isset($_REQUEST['email']) && $_REQUEST['email'] != get_post_meta($this -> order_id, '_billing_email', true))){
			$this -> order_valid = false;
		}
		else{
			$this -> order_valid = true;
		}
		
		$this->is_active_settings=get_option('wootalk_status');
		$this->init_wootalk_hooks();
		$this->model= new WootalkModel();
	}
	
	/**
	 * Hook into actions and filters.
	 *
	 */
	private function init_wootalk_hooks() {
		
		
	/**
	 * Implementation activated_plugin hook on start 
	 */
	add_action( 'activated_plugin', array( $this, 'activated_plugin' ) );
	/**
	 * Implementation plugins_loaded hook.
	 */
	 
	add_action( 'plugins_loaded', array( $this, 'wootalk_load_classes' ), 9 );
	
	/**
	 * Implementation in_plugin_update_message hook.
	 */
	 add_action( 'in_plugin_update_message-'.$this->plugin_basename, array( $this, 'in_plugin_update_message' ) );
     
	/**
	 * Implementation admin_menu hook for admin menu.
	 */
	 add_action( 'admin_menu', array(&$this,'wootalk_settings'));
	 
	 /**
	 * Implementation admin_top_menu hook for admin menu.
	 */
	 add_action( 'admin_bar_menu', array(&$this,'wootalk_add_toolbar_items'),100);
	
	
	/**
	 * Implementation wp_enqueue_scripts for enable script and style.
	 */
	add_action( 'wp_enqueue_scripts', array(&$this,'wootalk_scripts' ));
	
	/**
	* Implementation wp_enqueue_scripts for enable script and style.
	*/
	add_action( 'admin_enqueue_scripts', array(&$this,'wootalk_admin_scripts' ) );
	
	/**
	* Implementation admin_init for WooTalk Chat box in order details page backed.
	*/
	add_action( 'admin_init',  array(&$this,'wootalk_render_talk_form'));
	
	/**
	* Implementation woocommerce_order_details_before_order_table for WooTalk Chat box in order details page fronted.
	*/	
	add_action("woocommerce_order_details_after_order_table", array($this, 'render_wootalk_frontend_chat_box'), 10, 1);
	
    /**
	* Implementation wp_ajax_ used for ajax process.
	*/		
	add_action("wp_ajax_wootalk_send_message", array(&$this,'wootalk_send_message' ) );
	add_action("wp_ajax_nopriv_wootalk_send_message", array(&$this,'wootalk_send_message' ) );
	
	/**
	* Implementation wp_ajax upload attchment.
	*/
	
	add_action("wp_ajax_wootalk_upload_file", array(&$this,'wootalk_upload_file' ) );
	add_action("wp_ajax_nopriv_wootalk_upload_file", array(&$this,'wootalk_upload_file' ) );
	
	/**
	* Implementation wp_ajax delete msg.
	*/
	
	add_action("wp_ajax_wootalk_msg_delete", array(&$this,'wootalk_msg_delete' ) );
	
	/**
	* Implementation wp_mail_content_type for email sending.
	*/
	
	add_filter("wp_mail_content_type", array(&$this,'wootalk_email_set_content_type' ));

	/*
	* Implementation secure download
	*/
	add_action('pre_get_posts', array($this, 'wootalk_file_download'));
	
	
		
   /**
	* Implementation filter for order list colon 
	*/	
		add_filter( 'manage_edit-shop_order_columns', array(&$this,'wootalk_new_order_notifications') );
		add_filter( 'manage_shop_order_posts_custom_column', array(&$this,'wootalk_new_order_order_columns'), 20 );
			
	/**
	* Implementation filter for add columns in order list my account
	*/
	
		add_filter( 'woocommerce_my_account_my_orders_columns', array(&$this,'wootalk_add_my_account_orders_column'));
		add_filter( 'woocommerce_my_account_my_orders_column_order-wootalk-alert', array(&$this,'wootalk_my_orders_ship_to_column'));
	}
	
	
	
	/**
	* WooTalk instance.
	*/
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	
	
	/**
	* Method used for rending admin top menu
	*/
	
	public function wootalk_add_toolbar_items( \WP_Admin_Bar $admin_bar )
	{
		$woo_conversations=array();
		$woo_conversations = $this->wootalk_latest_message();
		$wootalk_chat=array();
		if(isset($woo_conversations) && !empty($woo_conversations)){
			foreach($woo_conversations as $key=>$chat){
			  $wootalk_chat[$chat->order_id][]=$chat->order_id;
			}
		}
		
		$admin_bar->add_menu( array(
			'id'    => 'woo-talk',
			'title' => '<span class="wootalk_top_bar_icon dashicons-before dashicons-format-chat"> WooTalk <span class="wootalk_count"> <span>'.count($woo_conversations).'</span></span></span> ',
			'href'  => 'admin.php?page=wootalk',
			'meta'  => array(
				'title' => __('WooTalk'),            
			),
		));
		if(isset($wootalk_chat) && !empty($wootalk_chat)){
			foreach($wootalk_chat as $index=>$wootalk_chat_inner){
				$admin_bar->add_menu( array(
					'id'    => 'wootalk-chat'.$index,
					'parent' => 'woo-talk',
					'title' => 'New messages on #'.$index.' Order ('.count($wootalk_chat_inner).')',
					'href'  => 'post.php?post='.$index.'&action=edit',
				));
			}
		}
	}
	
	/**
	* Method used for rending admin menu
	*/
	
	public function wootalk_settings()
	{
		add_menu_page('WooTalk','WooTalk', 'manage_options','wootalk', array(&$this,'admin_wootalk'),'dashicons-format-chat',10);
		add_submenu_page('wootalk','WooTalk','WooTalk', 'manage_options', 'wootalk', array(&$this,'admin_wootalk'),'dashicons-format-chat',10);
	}

	/**
	* Method used for rendering fronted javascript and style
	*/
	
	public function wootalk_scripts()
	{
		wp_enqueue_style( 'wootalk_front_css', plugins_url('/assets/css/wt-front.css', __FILE__));
		wp_enqueue_style( 'wootalk_admin_css', plugins_url('/assets/css/wt-admin.css', __FILE__));
		wp_enqueue_script( 'wootalk_admin_js', plugins_url('/assets/js/wt-admin.js', __FILE__), array('jquery'));
		wp_localize_script( 'wootalk_admin_js', 'ajax_admin_wootalk', array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
		));
		
	}
	
	/**
	* Method used for rendering admin javascript and style
	*/

	public function wootalk_admin_scripts()
	{
		/*if(isset($_GET['page']) &&  $_GET['page'] == 'wootalk' ){
			
		}*/
		
		wp_enqueue_style( 'wootalk_admin_css', plugins_url('/assets/css/wt-admin.css', __FILE__));
		wp_enqueue_style( 'wootalk_admin_common_css', plugins_url('/assets/css/wt-admin-common.css', __FILE__));
		wp_enqueue_script( 'wootalk_admin_js', plugins_url('/assets/js/wt-admin.js', __FILE__), array('jquery'));
		wp_localize_script( 'wootalk_admin_js', 'ajax_admin_wootalk', array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
		));
		
	}
	
	/**
	* Method used for calling fronted chat box
	*/
	
	public function wootalk_user_form($attr)
	{
		ob_start();
		include ('views/user_form.php');
		$content = ob_get_clean();
		return $content;
	}
	
	/**
	* Method used for calling wootalk settings form action process
	*/
	
	public function admin_wootalk()
	{
		
		
		if(!empty($_POST))
		{
			if($_POST['action']=="main"){
				$_POST['wootalk_status']=(isset($_POST['wootalk_status']) && !empty($_POST['wootalk_status'])) ? $_POST['wootalk_status'] : 0;
				
				foreach($_POST as $name=>$value)
				{
					$value = sanitize_text_field($value);
					update_option($name, $value);
				}
				add_settings_error('WooTalkSaveSettings',esc_attr( 'settings_updated' ),	'Successfully updated WooTalk details','updated');
			}
			if($_POST['action']=="settings"){
				$_POST['wootalk_email_notification']=(isset($_POST['wootalk_email_notification']) && !empty($_POST['wootalk_email_notification'])) ? $_POST['wootalk_email_notification'] : 0;
				$_POST['wootalk_welcome_notification']=(isset($_POST['wootalk_welcome_notification']) && !empty($_POST['wootalk_welcome_notification'])) ? $_POST['wootalk_welcome_notification'] : 0;
				$_POST['send_later_via_cron']=(isset($_POST['send_later_via_cron']) && !empty($_POST['send_later_via_cron'])) ? $_POST['send_later_via_cron'] : 0;
				if(isset($_FILES['wootalk_email_logo']) && !empty($_FILES['wootalk_email_logo'])){
					$tempFile = $_FILES['wootalk_email_logo']['tmp_name'];
					$type = strtolower(substr(strrchr($_FILES['wootalk_email_logo']['name'],'.'),1));
					$dirPath=$this->wootalk_setup_file_directory();
					$targetPath = $dirPath;
					$new_filename =strtotime("now").$type;
					$targetFile = rtrim($targetPath,'/') . '/' .$new_filename;
					if(move_uploaded_file($tempFile,$targetFile)){
						update_option('wootalk_email_logo',$new_filename);
					}
				}
				foreach($_POST as $name=>$value)
				{
					$value = sanitize_text_field($value);
					update_option($name, $value);
				}
				add_settings_error('WooTalkSaveSettings',esc_attr( 'settings_updated' ),	'Successfully updated WooTalk settings','updated');
			}
			
		}
		include('views/wootalk_form.php');
	}
	/**
    * Instantiate classes when woocommerce is activated
    */
	
    function wootalk_load_classes() {
		if ( $this->is__wootalk_woocommerce_activated() === false ) {
			add_action( 'admin_notices', array ( $this, 'wootalk_need_woocommerce' ) );
            return;
        }
	}
       
	function is__wootalk_woocommerce_activated() {
        $blog_plugins = get_option( 'active_plugins', array() );
        $site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();
        if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
             return true;
        } else {
            return false;
        }
    }
    function wootalk_need_woocommerce() {
        $error = sprintf( __( 'WooTalk requires %sWooCommerce%s to be installed & activated!' , 'wootalk' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
        $message = '<div class="error"><p>' . $error . '</p></div>';
        echo $message;
    }
	/**
	* Get the plugin url.
	*/
	
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
	
	/**
	* Method used for calling rendering meta box in order details page.
	*/
	public function wootalk_render_talk_form(){
		if($this->is_active_settings==1){
			add_meta_box( 'wootalk_conversation', 'WooTalk Conversation',array($this,'render_conversation_admin'),'shop_order', 'normal', 'default');
		}
	}
	/**
	* Method used for Wootalk conversation process.
	*/
	
	function render_conversation_admin($order){

		$order_id = '';
		if( is_a($order, 'WC_Order') ) {
			$order_id = $order->get_id();
		} else {
			$order_id = $order->ID;
		}
		
		$this -> order_id = $order_id;
		
		$this -> load_template('admin-chat-box.php', array('conversation_order_admin'=>'yes'));
		$this -> load_template('send-button.php', array('conversation_order_admin'=>'yes'));
	}
	
	/**
	* Method used for laod template files.
	*/
	function load_template($file_name, $variables=array('')){
     	extract($variables);
        include('views/'.$file_name);
	}

	/*
	 * print array in readable form
	*/
	function dd($arr){
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
	}
	public function plugin_path() {
		$dir = plugin_dir_path( __DIR__ );
		return $dir;
	}
	
	/**
	* Method used for sending message both customer and admin.
	*/
	
	function wootalk_send_message()
	{
		if ( empty($_POST) || !wp_verify_nonce($_POST['wootalk_nonce'], 'doing_wootalk') )
		{
			print 'Sorry, you are fake user.';
			exit;
		}
		$is_admin=$_POST['is_admin'];
		extract($_REQUEST);
			$email_from = '';
			$order_admin_email = wootalk_get_order_admin_email($order_id, $is_admin);
			$order_admin_name = wootalk_get_vendor_name($order_id, $is_admin, $order_admin_email);
			$unread_by_admin=0;
			$unread_by_user=0;
			$sender_name = get_post_meta($order_id, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true);
			if($is_admin == 'yes'){
				$email_to 		= get_post_meta($order_id, '_billing_email', true);
				$sent_by 		= $order_admin_name;
				$email_from 	= implode(',', $order_admin_email);
				$user 			= $email_from;
				$unread_by_admin    =1;

			}else{
				$sender_name = get_post_meta($order_id, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true);
				$email_to 		= $order_admin_email;
				$sent_by 		= $sender_name;
				$email_from 	= get_post_meta($order_id, '_billing_email', true);
				$user 			= get_post_meta($order_id, '_billing_email', true);
				$unread_by_user=1;
			}
		
			$res = '';
			$thread[] = array(
				'sent_by'	=> $sent_by,
				'message'	=>stripslashes($message),
				'files'		=> $files,
				'user'		=> $user,
				'seenByadmin'=>$seenByadmin,
				'seenByuser'=>$seenByuser,
				'senton'	=> current_time('mysql'),
			);
			$data = array(
				'order_id'			=> $order_id,
				'unread_by'			=> $email_to,
				'read_by_admin'   => $unread_by_admin,
				'read_by_user'   => $unread_by_user,
				'type'			=>		'send_now',
				'wootalk_thread'	=> json_encode($thread),
			);
			$format = array('%d','%s','%d','%d','%s','%s');
			$res = $this->model->wootalk_insert_msg(self::$tbl_wootalk, $data, $format);
			$notification_sent = true;
			if($wootalk_email_notification=get_option('wootalk_email_notification')){
				if( ! $this -> wootalk_email_alert($email_to, $email_from, $sent_by, $order_id, $message, $is_admin) ) {
					$notification_sent = false;
				}
			}
			$response = array();
			$type="send_now";
			$dateVal="";
			if ($res){
				$message_sent = ($message_sent == '') ? __('Message sent successfully', 'wootalk') : $message_sent;
				if( ! $notification_sent ) {
					$message_sent .= __("<br> Email notification couldn't be sent",'wootalk');
				}
				$response['status'] = 'success';
				$response['message'] = $message_sent;
				$response['last_message'] = $this->get_last_message_html($email_from, $sent_by, $message, current_time('mysql'), $files,$res,$is_admin,$type,$dateVal);

		}else{
			$response['status'] = 'error';
			$response['message'] = __('Please try again', 'wootalk');
		}
	
		echo json_encode($response);
		die;	
	}
	
	
	
	
	/**
	* Method used for sending message both customer and admin.
	*/
	
	function wootalk_send_later_message()
	{
		if ( empty($_POST) || !wp_verify_nonce($_POST['wootalk_nonce'], 'doing_wootalk') )
		{
			print 'Sorry, you are fake user.';
			exit;
		}
		extract($_REQUEST);
		$email_from = '';
		$order_admin_email = wootalk_get_order_admin_email($order_id, $is_admin);
		$order_admin_name = wootalk_get_vendor_name($order_id, $is_admin, $order_admin_email);
		$unread_by_admin=0;
		$unread_by_user=0;
		if($is_admin == 'yes'){
			$email_to 		= get_post_meta($order_id, '_billing_email', true);
			$sent_by 		= $order_admin_name;
			$email_from 	= implode(',', $order_admin_email);
			$user 			= $email_from;
			$unread_by_admin    =1;

		}else{
			$sender_name = get_post_meta($order_id, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true);
			$email_to 		= $order_admin_email;
			$sent_by 		= $sender_name;
			$email_from 	= get_post_meta($order_id, '_billing_email', true);
			$user 			= get_post_meta($order_id, '_billing_email', true);
			$unread_by_user=1;
		}
		
		$dateVal=$date.' '.$time;
		$res = '';
			$thread[] = array(
					'sent_by'	=> $sent_by,
					'message'	=> stripslashes($message),
					'files'		=> $files,
					'user'		=> $user,
					'seenByadmin'=>$seenByadmin,
					'seenByuser'=>$seenByuser,
					'senton'	=> current_time('mysql'),
			);
			$data = array(
					'order_id'			=> $order_id,
					'unread_by'			=> $email_to,
					'read_by_admin'   => $unread_by_admin,
					'read_by_user'   => $unread_by_user,
					'type'			=>		'send_later',
					'date'			=> strtotime($dateVal),
					'wootalk_thread'	=> json_encode($thread),
			);
			$format = array('%d','%s','%d','%d','%s','%s','%s');
			$res = $this->model->wootalk_insert_msg(self::$tbl_wootalk, $data, $format);
			$dateVal=strtotime($dateVal);
			$response = array();
			$type="send_later";
			if ($res){
				$response['status'] = 'success';
				$response['message'] = $message_sent;
				$response['last_message'] = $this->get_last_message_html($email_from, $sent_by, $message, current_time('mysql'), $files,$res,$is_admin,$type,$dateVal);

			}else{
				$response['status'] = 'error';
				$response['message'] = __('Please try again', 'wootalk');
			}
		echo json_encode($response);
		die;
	}
	
	
	
	/**
	* Method used for delete message.
	*/
	function wootalk_msg_delete(){
		if ( empty($_POST) || !wp_verify_nonce($_POST['wootalk_nonce'], 'doing_wootalk') )
		{
			print 'Sorry, you are fake user.';
			exit;
		}
		if(isset($_POST['message_id']) && !empty($_POST['message_id'])){
			$res = $this->model->wootalk_delete_msg(self::$tbl_wootalk,$_POST['message_id']);
			$response['status'] = 'success';
		}
		else{
			$response['status'] = 'error';
		}
		echo json_encode($response);
		die;
	}
	
	/**
	* Method used for upload files.
	*/
	
	function wootalk_upload_file()
	{
		if ( empty($_POST) || !wp_verify_nonce($_POST['wootalk_nonce'], 'doing_wootalk') )
		{
			print 'Sorry, you are fake user.';
			exit;
		}
		
		$dirPath = $this -> wootalk_setup_file_directory();
		$response = array();
		if (!empty($_FILES)) {
			$tempFile = $_FILES['file']['tmp_name'];
			$targetPath = $dirPath;
			$new_filename = strtotime("now").'-'.preg_replace("![^a-z0-9.]+!i", "_", $_FILES['file']['name']);
			$targetFile = rtrim($targetPath,'/') . '/' .$new_filename;
			$thumb_size = 75;
			$url="";
			$type = strtolower(substr(strrchr($new_filename,'.'),1));
			if(move_uploaded_file($tempFile,$targetFile)){
				if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg") ){
					$this ->wootalk_create_thumb($targetPath, $this -> wootalk_setup_file_directory_thumbs(), $new_filename, $thumb_size);
					$response['type']		= 'images';
				}
				else{
					$response['type']		= 'file';
					$response['url']=plugins_url().'/wootalk/inc/assets/images/no-file.png';
				}
				$response['status']		= 'uploaded';
				$response['filename']	= $new_filename;
			}
			else{
				$response['status']		= 'error';
				$response['message']	= 'Error while uploading file';
			}
		}
		echo json_encode($response);
		die;
		
	}
	
	function render_attachments($files){

		$files = explode(',', $files);
		$html = '<ul class="wootalk-attachments-files">';
		foreach ($files as $file){
			$file_path_dir = $this -> get_file_dir_path() . $file;
			if( ! file_exists($file_path_dir) ) continue;
			$args = array('wootalk_download'=>'file', 'filename'=>$file);
			$secure_download_url = add_query_arg( $args, site_url() );
			if( $this -> wootalk_is_image($file) ){
				$thumb_url = $this->wootalk_get_file_dir_url(true) . $file;
			}else{
				$thumb_url = plugins_url().'/wootalk/inc/assets/images/no-file.png';
			}
			$file_size = size_format( filesize( $file_path_dir ));
			$html .= '<li class="wootalk-file-item">';
			$html .= '<span><a href="'.esc_url($secure_download_url).'"><img class="wootalk-attchment-img" width="30" src="'.esc_url($thumb_url).'"></a></span>';
			//$html .= '<span>'.$file_size.'</span>';
			$html .= '</li>';
		}
		$html .= '</ul>';
		echo apply_filters('wootalk_render_attachments', $html, $files);
	}
	
	function get_file_dir_path(){
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'].'/'.$this -> upload_dir_name.'/';
	}
	
	
	
	function wootalk_setup_file_directory(){


		$upload_dir = wp_upload_dir ();
		
		$file_dir_path = $upload_dir ['basedir'] . '/' . $this->upload_dir_name . '/';
		
		if (! is_dir ( $file_dir_path )) {
			if (mkdir ( $file_dir_path, 0775, true ))
				$dirThumbPath = $file_dir_path . 'images/';
			if (mkdir ( $dirThumbPath, 0775, true ))
				return $file_dir_path;
			else
				return 'errDirectory';
		} else {
			$dirThumbPath = $file_dir_path . 'images/';
			if (! is_dir ( $dirThumbPath )) {
				if (mkdir ( $dirThumbPath, 0775, true ))
					return $file_dir_path;
				else
					return 'errDirectory';
			} else {
				return $file_dir_path;
			}
		}
		
	}
	
	function  wootalk_setup_file_directory_thumbs(){
		$upload_dir = wp_upload_dir();
		$dirPath = $upload_dir['basedir'].'/'.$this -> upload_dir_name.'/images/';
		
		if(!is_dir($dirPath))
		{
			if(mkdir($dirPath, 0775, true))
				return $dirPath;
			else
				return false;
		}else{
			
			return $dirPath;
		}
		
	}
	function wootalk_get_file_dir_url($thumbs=false){
		$content_url = content_url( 'uploads' );
		if ($thumbs)
			return $content_url . '/' . $this->upload_dir_name . '/images/';
		else
			return $content_url . '/' . $this->upload_dir_name . '/';
	}

	function wootalk_create_thumb($mainTarget, $target_file, $image_name, $thumb_size) {
		
		$img=$mainTarget.$image_name;
		$t=$target_file.$image_name;
		$image = new wootalkImages();
		$image->load($img);
		$image->resize(150, 150,true);
		$image->save($t);
	}
	
	function wootalk_is_image($file){
		$type = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
		if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg"))
			return true;
		else 
			return false;
	}

	
	function time_difference($date)
	{
		if(empty($date)) {
			return "No date provided";
		}

		$periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths         = array("60","60","24","7","4.35","12","10");

		$now             = current_time('timestamp');
		$unix_date       = strtotime($date);

		// check validity of date
		if(empty($unix_date)) {
			return "Bad date";
		}

		// is it future date or past date
		if($now > $unix_date) {
			$difference     = $now - $unix_date;
			$tense         = "ago";

		} else {
			$difference     = $unix_date - $now;
			$tense         = "from now";
		}

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}

		$difference = round($difference);

		if($difference != 1) {
			$periods[$j].= "s";
		}

		return "$difference $periods[$j] {$tense}";
	}

	function get_last_message_html($sender_email, $sender_name, $msg, $time, $files = '', $id,$is_admin,$type,$date){
		ob_start();
		?>
		
        <li data-type="<?php echo $type ?>" class="wootalk_chat_<?php echo $id ?> chat-line other">
				<img class="avatar" alt="" src="<?php echo get_avatar_url( $sender_email, 128 ) ?>">
				
            <div class="wt-msg-wrap msg">
			 <span class="wootalk-arrow"> </span>
				<span class="wt-user"><?php echo $sender_name; ?></span>
			 	  <span class="datetime"> at <?php echo $this->time_difference($time); ?> </span>
			    <span class="wootalk-body">
                <?php echo stripslashes($msg) ; ?>
				<?php if ($files != '') {
						$this -> render_attachments($files);
					} ?>
                </p>
               
                </span>
			</div>	
				<?php if($is_admin=="yes"){ ?>
				<img class="wootalk-chat-sidebar-action" src="<?php echo plugins_url() ?>/wootalk/inc/assets/images/sidebar-icon.png">
					<ul class="wootalk-chat-sidebar-options-wrap">
						<li data-id="<?php echo $id ?>" onClick="return wootalk_delete_chat(this)" class="wootalk-chat-sidebar-options-inner wootalk-delete-chat dashicons-before dashicons-trash"> Delete</li>
				</ul>
				<?php } ?>
				
         </li> 		
		<?php

		return ob_get_clean();
	}
	
	/**
	* Method used for creating database table while activate plugins.
	*/
	
	public function activated_plugin(){
		global $wpdb;
		
		$sql1 = "CREATE TABLE `".$wpdb->prefix . self::$tbl_wootalk."` (
		`wootalk_id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`order_id` INT( 7 ) NOT NULL,
		`unread_by` VARCHAR( 100 ) NOT NULL,
		`read_by_admin` VARCHAR( 10 ) NOT NULL,
		`read_by_user` VARCHAR( 10 ) NOT NULL,
		`type` VARCHAR( 10 ) NOT NULL,
		`date` VARCHAR( 100 ) NOT NULL,
		`wootalk_thread` MEDIUMTEXT NOT NULL);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql1);
	}
	
	/*
	 ** Get Conversations order_id
	*/

	function get_order_conversations()
	{
		if(!$this -> order_valid){
			return NULL;
		}else{
			$select = array(self::$tbl_wootalk	=> '*');
			$where = array('d'	=>	array('order_id'	=> $this -> order_id));
			$order_conversations = $this->model->get_wootalk_rows_data($select, $where);
			return $order_conversations;
		}

	}
	
	/*
	 ** get notification on order list
	*/

	function wootalk_latest_message()
	{
		$select = array(self::$tbl_wootalk	=> '*');
		$where = array('d'	=>	array('read_by_admin'	=> 0));
		$order_conversations = $this->model->get_wootalk_rows_data($select, $where);
		return $order_conversations;
		
	}
	
	/*
	 ** Get Send later Conversations
	*/

	function get_order_send_later_conversations()
	{
		if(!$this -> order_valid){
			return NULL;
		}else{
			$select = array(self::$tbl_temp_wootalk	=> '*');
			$where = array('d'	=>	array('order_id'	=> $this -> order_id));
			$order_conversations = $this->model->get_wootalk_rows_data($select, $where);
			return $order_conversations;
		}

	}
	
	/*
	 ** get notification on order list
	*/

	function get_conversations_notification($order_id)
	{
		$select = array(self::$tbl_wootalk	=> '*');
		$where['d']=array('order_id'	=> $order_id);
		$where['d2']=array('read_by_admin'	=> 0);
		$order_conversations = $this->model->get_wootalk_rows_data($select, $where);
		return $order_conversations;
	}
	
	function get_conversations_user_notification($order_id)
	{
		$select = array(self::$tbl_wootalk	=> '*');
		$where['d']=array('order_id'	=> $order_id);
		$where['d2']=array('read_by_user'	=> 0);
		$order_conversations = $this->model->get_wootalk_rows_data($select, $where);
		return $order_conversations;
	}
	
	/*
	 ** get notification on order list
	*/

	function update_notification($order_id,$conversation_order_admin)
	{
		$where = array('d'	=>	array('order_id'	=> $order_id));
		if($conversation_order_admin=='yes'){
			$data = array('read_by_admin'=> 1);
		}
		else{
			$data = array('read_by_user'=> 1);
		}
		$format = array('%d');
		$where = array('order_id'	=> $order_id);
		$where_format = array('%d');
		
		$res = $this->model->wootalk_update_msg_notification(self::$tbl_wootalk, $data, $where, $format, $where_format);
	}
	
	/*
	 ** Method used for render new WooTalk Chat column in shop list.
	*/
	function wootalk_new_order_notifications( $columns ) {
		if($this->is_active_settings==1){
			$columns['wootalk_chat'] = 'WooTalk Chat';
		}
		return $columns;
	}
	
	/*
	 ** Method used for render get WooTalk Chat column value.
	*/
	
	function wootalk_new_order_order_columns( $columns ) {
		global $post;

		if ($columns=='wootalk_chat') {
			$count=count($this->get_conversations_notification($post->ID));
			echo '<span class="dashicons-before dashicons-format-chat wootalk-notification"><span class="processing-count">( '.$count.' )</span></span>';
		}

	}
	
	
	/*
	 * secure wootalk download file
	*/
	function wootalk_file_download($query){

		if(isset($_REQUEST['wootalk_download']) && $_REQUEST['wootalk_download'] != '' && $_REQUEST['wootalk_download'] == 'file'){

			$dir_path = $this -> get_file_dir_path();
			$filename = sanitize_text_field($_REQUEST['filename']);
			$file_path = $this -> get_file_dir_path() . $filename;
			
			// var_dump(filesize($file_path)); exit;

			if (file_exists($file_path)){

				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.basename($file_path));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file_path));
				@ob_end_flush();
				flush();
				
				$fileDescriptor = fopen($file_path, 'rb');
				
				while ($chunk = fread($fileDescriptor, 8192)) {
				    echo $chunk;
				    @ob_end_flush();
				    flush();
				}
				
				fclose($fileDescriptor);
				exit;
			}

		}
	}
	
	
	
	function render_wootalk_frontend_chat_box($order){
		if($this->is_active_settings ==1){
			$this -> order_id =  $order->get_id();
			$this -> load_template('admin-chat-box.php', array('conversation_order_admin'=>'no'));
			$this -> load_template('send-button.php', array('conversation_order_admin'=>'no'));
		}
	}
	
	
	/*
	* Admin or Customer Email notification
	*/
	function wootalk_email_alert($to, $from_email, $from_name, $order_id, $wootalk_message, $is_admin){
		$order = new WC_Order($order_id);
		$logo_url=plugins_url()."/wootalk/inc/assets/images/wootalk-logo.png"; 
		$headers[] = "From: $from_name <$from_email>";
		$headers[] = "Content-Type: text/html";
		$headers   = apply_filters('wootalk_email_headers', $headers, $order_id, $is_admin);
		$subject ='New messages from '.$from_name.' - order:# '.$order_id;
		$message ="";
		$message.='<table style="padding-bottom:40px;" align="center" height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f0f0f0">';
		$message.='<tbody><tr><td>';
	    $message.='<table style="padding-bottom:40px;" align="center" height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f0f0f0">';
		$message.='<tbody><tr><td>';
		$message.='<table style="padding-top:50px" width="690" align="center" border="0" cellspacing="0" cellpadding="0"><tbody>';
		$message.='<tr style="background: #f0f0f0 url('.plugins_url().'/wootalk/inc/assets/images/email-header.png><td width="100%" align="center">';
		$message.='<a href="#" target="_blank"><img src="'.$logo_url.'"  style="display:block;padding:30px" width="100" border="0">';
		$message.='</a></td></tr><tr style="background-color:#fff"><td style="padding-top: 5px;"><h1 align="center">You have a new message from '.$from_name.'</h1></td></tr><tr style="background: #fff;width: 95%;padding: 18px;padding-top: 0px;display: inline-block;margin-bottom: 5px;">';
		$message.='<td class="s1" style="padding-left: 30px;font-size:18px;"><p style="padding:0;margin:0;color:#161e2c;font-size:18px;line-height:28px;font-weight:normal;font-family:Helvetica,Arial,sans-serif">';
		$message.=stripslashes($wootalk_message);
		$message.='</p></td></tr>';
		$message.='<tr><td align="center" height="48" style="border-radius:3px;font-weight:bold;font-family:Helvetica,Arial,sans-serif;background-color:#f8a613">';
		$message.='<span style="font-family:Helvetica,Arial,sans-serif;font-weight:bold">';
		$message.='<a href="'.home_url().'/my-account/view-order/'.$order_id.'" style="font-weight:bold;color:#ffffff;text-decoration:none;font-size:18px;line-height:48px;display:block;width:100%" target="_blank">';
		$message.='View And Reply →</a></span></td></tr><tr><td style="text-align: center;padding-top: 25px;">Powered by <a href="https://www.wpproduct.in/">Wootalk</a></td></tr></tbody></table></td></tr></tbody></table>';
		$to  = apply_filters('wootalk_message_receivers', $to, $is_admin);
		$message  = apply_filters('wootalk_message_text', $message, $is_admin);
		if (wp_mail($to, $subject, $message, $headers)){
			return true;
		}else{
			return false;
		}
	}
	
	function wootalk_email_set_content_type(){
		return "text/html";
	}
	
	function get_wootalk_times( $default = '12:00', $interval = '+15 minutes' ) {
		$output = '';
		$current = strtotime( '00:00' );
		$end = strtotime( '23:59' );
		while( $current <= $end ) {
			$time = date( 'H:i', $current );
			$sel = ( $time == $default ) ? ' selected' : '';
			$output .= "<option value=\"{$time}\"{$sel}>" . date( 'H.i A', $current ) .'</option>';
			$current = strtotime( $interval, $current );
		}
		return $output;
	}
	function wootalk_add_my_account_orders_column($columns) {
		
		$new_columns = array();
		foreach ( $columns as $key => $name ) {
			$new_columns[ $key ] = $name;
			if ( 'order-total' === $key ) {
				$new_columns['order-wootalk-alert'] = __( 'Alert', 'wootalk' );
			}
		}
		return $new_columns;
	}
	
	function wootalk_my_orders_ship_to_column($order) {
		$count=count($this->get_conversations_user_notification($order->ID));
		 echo '<img width="30" height="20" src="'.plugins_url().'/wootalk/inc/assets/images/wootalk-chat-icon.png"/>('.$count.')';
	}
	
}

?>