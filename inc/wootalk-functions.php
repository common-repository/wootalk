<?php 



function wootalk_get_vendor_name($order_id, $is_admin='no', $vendor_email='') {
	$order_admin_name = '';
	if( defined('WCMp_PLUGIN_TOKEN') && $is_admin == 'yes') {
		$vendor_email = $vendor_email[0];
		$vendors = get_vendor_from_an_order($order_id);
		if ($vendors) {
            foreach ($vendors as $vendor) {
               $vendor_obj = get_wcmp_vendor_by_term($vendor);
                if( $vendor_obj->user_data->user_email == $vendor_email) {
                	$order_admin_name = $vendor_obj->page_title;
                }
            }
        }
	}
	if( empty($order_admin_name) ) {
		$order_admin_name = get_bloginfo('name');
	}
	return apply_filters('wootalk_shop_admin_name', $order_admin_name, $order_id);
}


function wootalk_get_order_admin_email(){
	$order_admin_emails = array();
	if( defined('WCMp_PLUGIN_TOKEN') ) {
		if( $is_admin == 'yes' ) {
			$user_id = get_current_user_id();
	        if (is_user_wcmp_vendor($user_id)) {
	    		$vendor = get_wcmp_vendor($user_id);
	    		$order_admin_emails[] = $vendor->user_data->user_email;
	       }
		} else {
			$vendors = get_vendor_from_an_order($order_id);
	        if ($vendors) {
	            foreach ($vendors as $vendor) {
	                $vendor_obj = get_wcmp_vendor_by_term($vendor);
	                $order_admin_emails[] = $vendor_obj->user_data->user_email;
	            }
	        }
		}
	} else {
		$order_admin_emails[] = get_bloginfo('admin_email');
	}
	return apply_filters('order_admin_email', $order_admin_emails);
}


// Get order detail URL
function wootalk_get_order_detail_url( $order, $is_admin ) {
	$order_url = '';
	if( $is_admin == 'yes'){
		$order_url = $order -> get_view_order_url();
	}else{
		$order_url = admin_url( 'post.php?post='.$order->get_id().'&action=edit' );
		if( defined('WCMp_PLUGIN_TOKEN') ) {
			$order_url = wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders'), $order->get_id());
		}
	}
	return $order_url;
}

?>