var wootalk_settime;

function wootalk_loader(element, off) {

	var _html = '';
	if (off == false) {
		var _html = '<img src="' + wooconvo_vars.plugin_url
				+ '/images/loading.gif">';
	}

	jQuery('#' + element).html(_html);
}

jQuery("document").ready(function(){
	jQuery("body").append('<div class="wootalk_undo_message_wrap"></div>');
	if(jQuery("body").find(".wt-Content.wootalk-send").length){
	  jQuery(".wootalk-chat-container").stop().animate({ scrollTop: jQuery(".wootalk-chat-container")[0].scrollHeight}, 1000);
	}
	
	jQuery("body").on("click",".wootalk-update-logo",function(){
		jQuery("body").find("#wootalk_email_logo").trigger("click");
	});
	
	 jQuery("body").on("click",".wootalk-upload-images",function(){
		 var length=jQuery("body").find(".wootalk-image").length;
		 jQuery("body").find(".wootalk_upload_wrap").append('<input onchange="wootalk_loadImage(event)" style="display:none" type="file" data-value="1" name="imagefile" class="wootalk_'+length+' wootalk-image">');
		  jQuery("body").find(".wootalk_"+length).trigger("click");
		  jQuery("body").find("#wootalk-send").attr("data-file",length);
		  
	 });
	 jQuery("body").on("click",".wootalk-upload-delete",function(){
		jQuery(this).closest(".wootalk_row").remove(); 	  
	 });
	 jQuery("body").on("click",".wootalk-load-template",function(){
		jQuery("body").find(".load-template-wrap").toggle("show"); 	  
	 });
	 jQuery("body").on("click",".wootalk-tab-menu",function(){
		var id=jQuery(this).attr("href");
		jQuery("body").find(".wootalk-tab").hide();
		jQuery("body").find(".wootalk-tab-menu").removeClass("isActive");
		jQuery(this).addClass("isActive");
   		jQuery("body").find(id).show();	 
	 });
	 jQuery("body").on("click",".wt-checkbox",function(){
		if(jQuery(this).find(".active_wt").prop("checked") == true){
			jQuery(this).find(".active_wt").attr("checked",false);
			jQuery(this).find(".active_wt").val("0");
		}
		else{
			jQuery(this).find(".active_wt").attr("checked",true);
			jQuery(this).find(".active_wt").val("1");
		}
	});	
	var hashValue=window.location.hash.substr(1);
	if(hashValue){
		jQuery("body").find(".wootalk-tab").hide();
		jQuery("body").find(".wootalk-tab-menu").removeClass("isActive");
   		jQuery("body").find('#'+hashValue).show(); 
		jQuery("body").find("[href='#"+hashValue+"']").addClass("isActive");
	}
	else{
		jQuery("body").find(".wootalk-dashboard").show();
	}
	
	jQuery("body").on("click",".wootalk-chat-sidebar-action",function(){
		jQuery(this).closest("li").find(".wootalk-chat-sidebar-options-wrap").toggle("show");
	});
	
	
});

var wootalk_delete_chat = function(event) {
	var thisVal=jQuery(event);
	var message_id=jQuery(event).attr("data-id");
	var fd = new FormData();
	fd.append("wootalk_nonce", jQuery('input[name="wootalk_nonce"]').val());
	fd.append('message_id', message_id);
	fd.append('action', 'wootalk_msg_delete');
	jQuery.ajax({
		type: 'POST',
		url: ajax_admin_wootalk.ajaxurl,
		data: fd,
		contentType: false,
		processData: false,
		success: function(result){
			var resp = jQuery.parseJSON(result);
			if(resp.status == 'success'){
				thisVal.closest(".chat-line").remove();
			}
		}
	});
	
};

var wootalk_loadImage = function(event) {
    var reader = new FileReader();
    reader.onload = function(){
		jQuery("body").find(".wootalk-send-btn").attr("disabled",true)
		jQuery("body").find('.wootalk-progress').show();
		jQuery("body").find('.wootalk-progress').text(0 + '%');
		jQuery("body").find('.wootalk-progress').css('width', 0 + '%');
		
		var path=reader.result;
		var fd = new FormData();
		jQuery("body").find('input[name="imagefile"]').each(function(i, file){
			var individual_file = this.files[0];
			fd.append("file", individual_file);
		});
		fd.append("wootalk_nonce", jQuery('input[name="wootalk_nonce"]').val());
		fd.append('action', 'wootalk_upload_file');
        jQuery.ajax({
			type: 'POST',
			url: ajax_admin_wootalk.ajaxurl,
			data: fd,
			contentType: false,
			processData: false,
			xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                 var percentComplete = evt.loaded / evt.total;
                 percentComplete = parseInt(percentComplete * 100);
					jQuery('.wootalk-progress').text(percentComplete + '%');
					jQuery('.wootalk-progress').css('width', percentComplete + '%');
                }
                },
				false);
				return xhr;
            },
			success: function(result){
				var resp = jQuery.parseJSON(result);
				if(resp.status == 'uploaded'){
					if(resp.type=="file"){
						jQuery("body").find(".wootalk_upload_wrap_preview").append('<div class="wootalk_row"><input type="hidden" name="wootak_files" value='+resp.filename+'><img src="'+resp.url+'" height="25" width="30"><span class="dashicons dashicons-no wootalk-upload-delete" title="Delete file"></span></div>');
					}
					else{
						jQuery("body").find(".wootalk_upload_wrap_preview").append('<div class="wootalk_row"><input type="hidden" name="wootak_files" value='+resp.filename+'><img src="'+path+'" height="25" width="30"><span class="dashicons dashicons-no wootalk-upload-delete" title="Delete file"></span></div>');
					}
					jQuery("body").find(".wootalk-send-btn").attr("disabled",false)
				}else{
				}
			}
		});
			
	};
    reader.readAsDataURL(event.target.files[0]);
};
	
function wootalk_send_order_message() {
	var _wrapper = jQuery("#wootalk-send");
	var message=tinymce.activeEditor.getContent();
	if (message != '') {
		_wrapper.find('.wootalk-textarea').css({'border':''});
		var wootalk_files= Array();
		jQuery('input[name^="wootak_files"]').each(function(i, item){
			wootalk_files.push( jQuery(item).val() );
		});
	    var send_type=_wrapper.find('.send_type').val();
		var user_type=_wrapper.find('input[name="is_admin"]').val();
		
		var fd = new FormData();
		fd.append("wootalk_nonce", jQuery('input[name="wootalk_nonce"]').val());
		fd.append('message', message);
		fd.append('is_admin', _wrapper.find('input[name="is_admin"]').val());
		fd.append('existing_wootalk_id', jQuery('input[name="existing_wootalk_id"]').val());
		fd.append('order_id',  _wrapper.find('input[name="order_id"]').val());
		fd.append('files',  wootalk_files);
		fd.append('action', 'wootalk_send_message');
		jQuery.ajax({
			type: 'POST',
			url: ajax_admin_wootalk.ajaxurl,
			data: fd,
			contentType: false,
			processData: false,
			success: function(result){
				var resp = jQuery.parseJSON(result);
				if(resp.status == 'error'){
					jQuery('#sending-order-message').html(resp.message);
				}else{
					jQuery('#sending-order-message').html(resp.message);
					jQuery(".wooconvo-first-message").remove();
					jQuery("body").find(".wootalk_upload_wrap_preview").html("");
					jQuery("body").find(".wootalk-progress").hide();
					var last_msg = resp.last_message;
					jQuery('ul.chat').append(resp.last_message);
					tinyMCE.activeEditor.setContent("");
					jQuery(".wootalk-chat-container").stop().animate({ scrollTop: jQuery(".wootalk-chat-container")[0].scrollHeight}, 1000);
				}
			}
		});
	}
	return false;
}
