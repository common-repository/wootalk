<?php
/*
 * Wootalk Send button settins
 */


global $wootalk;
wp_nonce_field('doing_wootalk','wootalk_nonce');
wp_enqueue_style('dashicons');
wp_enqueue_style('jquery-blockui');
?>
<div id="wootalk-send" class="wootalk-send">
	<input type="hidden" name="order_id" value="<?php echo $wootalk->order_id; ?>" />
	<?php 
	if ($conversation_order_admin == 'yes'):?>
		<input type="hidden" name="is_admin" value="yes" />
	<?php endif;?>
	<div class="bottom-bottons bottom-bottons-wrap">
	
		<div class="send-message-wrap">	
			<?php if($conversation_order_admin == 'yes') { ?>
				<div class="wt-message-heading">Message to customer 
				<input type="hidden" value="send_now" class="send_type"/>
				</div>
				<?php }else { ?>
				<div class="wt-message-heading">Message to admin</div>
				<input type="hidden" value="send_now" class="send_type"/>
				<?php } ?>
				<?php 
				$content="";
				$args=array(
				'wpautop' => false,
				'media_buttons' => false,
				'textarea_name' => "wo_blue_box",
				'textarea_rows' => 5,
				'tabindex' => '',
				'tabfocus_elements' => ':prev,:next', 
				'editor_css' => '', 
				'editor_class' => '',
				'teeny' => false,
				'dfw' => false,
				'tinymce'       => array(
					'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
					'toolbar2'      => '',
					'toolbar3'      => '',
				),
				'quicktags' => false,
			);
				wp_editor( htmlspecialchars_decode($content), 'wo_blue_box',$args);
			?>
			<div class="wootalk_upload_wrap"></div>
			<div class="wootalk_upload_wrap_preview"></div>
			<div class="wootalk_footer"><div class="wootalk-upload-action-btn">
				<span class="wootalk-upload-images" title="Attach image (jpg,png,gif)"><img class="wt-attchement-logo" class="wootalk-upload-images" width="20" height="20" alt="Logo WooTalk" src="<?php echo plugins_url()?>/wootalk/inc/assets/images/attach.png"></span>
			</div>
			<div class="wootalk-progress-wrap progress">
				<div class="progress-bar progress-bar-success wootalk-progress" role="progressbar" style="width:0%">0%</div>
			</div>
			<input type="button" name="wootalk-send" class="wootalk-send-btn wt-button" value="Send" onclick="return wootalk_send_order_message()"></div>
		</div>
	</div>
</div>

</section>