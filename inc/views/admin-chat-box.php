<?php
/*
 * this template is loading all conversations
 */
global $wootalk;

$conversations = $wootalk->get_order_conversations(); 
$wootalk->update_notification($wootalk->order_id,$conversation_order_admin);
?>
<section class="wt-Content wootalk-send">
<div class="wootalk-chat-container">
<?php
	if(empty($conversations) ) {
		$default_message = apply_filters('wootalk_default_message', __("Chat box..",'wootalk'));
		?>
		<ul class="chat">
			<li class="wooconvo-first-message"><?php echo $default_message ?></li>
		</ul>
		
		<?php 
	}
	echo '<input type="hidden" name="existing_wootalk_id" value="100" />';
	

	
	$vendor_emails = wootalk_get_order_admin_email($wootalk->order_id);
	$vendor_email = $vendor_emails[0];
	
?>



<?php 
if(isset($conversations) && !empty($conversations)){ ?>
<ul class="chat">
<?php
foreach ($conversations as $wootalks_con) {
	
$thread = json_decode($wootalks_con -> wootalk_thread);
	// showing last message on top
	if( apply_filters('wootalk_show_latest_on_top', false) ) {
		$thread = array_reverse($thread);
	}
	
foreach ($thread as $msg) {
        if ( $conversation_order_admin == 'yes' ) {
            $css_class = ($msg->sent_by == wootalk_get_vendor_name($wootalk->order_id, $conversation_order_admin, $vendor_emails[0])) ? 'other' : 'self' ;
        } else {
            $css_class = ($msg->sent_by == wootalk_get_vendor_name($wootalk->order_id)) ? 'self' : 'other' ;
        }
		
		if ( $conversation_order_admin == 'no' && !empty($wootalks_con->date)) {
			continue;
		}
		if(isset($wootalks_con->date) && !empty($wootalks_con->date)){
			$chat_type="send_later";
		}
		else{
			$chat_type="send_now";
		}
	?>
    <li data-type="<?php echo $chat_type ?>" class="wootalk_chat_<?php echo $wootalks_con->wootalk_id ?> chat-line <?php echo $css_class; ?>">
	
            <?php
            
                if ($msg->user == $vendor_email) { 
				?>
				<img class="avatar" alt="" src="<?php echo get_avatar_url( $vendor_email, 128 ) ?>">
                 <?php    
                } else {
					?>
					<img class="avatar" alt="" src="<?php echo get_avatar_url( $msg->user, 128 ) ?>">
                  <?php }
				
			?>
        <div class="wt-msg-wrap msg">
		   <span class="wootalk-arrow"> </span>
		    <span class="wt-user"><?php echo $msg->sent_by; ?></span>
			<?php if(isset($wootalks_con->date) && !empty($wootalks_con->date)){ ?>
			<span class="datetime"> send later on <?php echo date('d-m-y, H:i',$wootalks_con->date); ?> </span>
          
			<?php } else{ ?>
			 	  <span class="datetime"> at <?php echo $wootalk->time_difference($msg->senton); ?> </span>
          
			<?php } ?>
		    <span class="wootalk-body">
                <?php echo stripslashes($msg->message); ?>
				
				<?php if ($msg->files != '') {
					$wootalk -> render_attachments($msg->files);
				} ?>     
			</span>
		  
			
        </div>
       <?php  if ( $conversation_order_admin == 'yes' ) { ?>
		<img class="wootalk-chat-sidebar-action" src="<?php echo plugins_url() ?>/wootalk/inc/assets/images/sidebar-icon.png">
		<ul class="wootalk-chat-sidebar-options-wrap">
			<li data-id="<?php echo $wootalks_con->wootalk_id ?>" onClick="return wootalk_delete_chat(this)" class="wootalk-chat-sidebar-options-inner wootalk-delete-chat dashicons-before dashicons-trash"> Delete</li>
		 </ul>
	   <?php } ?> 
    </li>        	
<?php }
}
} ?>
</ul>

</div>	

