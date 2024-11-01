<!---Admin settings-------------->

<?php 
global $wootalk;

?>
<div class="wt-wrap wrap main-wootalk">
	<h1 class="screen-reader-text">
	<font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('Wootalk Settings','wootalk') ?></font></font></h1>
		<?php settings_errors(); ?>
		<div class="wootalk-header">
			<div class="wootalk-header-inner-wrap">
			   <div class="wootalk-tile-icon wootalk-header-item">
				<h1>Wootalk</h1> 
				<p>Free Version 1.0.0</p>
				</div>
				<div class="header-action-wrap wootalk-header-item">
					<div class="wootalk-header-item">
						<a href="https://www.wpproduct.in/"  target="_blank" class="wootalk-upgreate wootalk-inner-header-item ">UPGRADE TO THE PREMIUM VERSION </a>
						<a href="https://www.wpproduct.in/supprt" target="_blank" class="wootalk-support wootalk-inner-header-item ">SUPPORT </a>
					</div>
					
				</div>	
				
			</div>
		</div>
		<div class="wt-body">
			<header class="wt-Header">
				<div class="wt-Header-logo"><img class="wootalk-logo" width="130" height="100" alt="Logo WooTalk" src="<?php echo plugins_url()?>/wootalk/inc/assets/images/wootalk-logo.png"></div>
				<div class="wt-Header-nav">
					<a href="#dashboard" id="wootalk-nav-dashboard" class="wootalk-tab-menu wt-menuItem isActive">
						<div class="wt-menuItem-title"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('Dashboard','wootalk') ?></font></font></div>
						<div class="wt-menuItem-description"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('WooTalk Settings Info','wootalk') ?></font></font></div>
						<img class="wootalk-tab-icon" alt="Wootalk dashboard tab" src="<?php echo plugins_url()?>/wootalk/inc/assets/icon/home.png">
					</a>
					<a href="#email-settings" id="wootalk-nav-settings" class="wootalk-tab-menu wt-menuItem">
						<div class="wt-menuItem-title"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('Settings','wootalk') ?></font></font></div>
						<div class="wt-menuItem-description"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('WooTalk Settings','wootalk') ?></font></font></div>
						<img class="wootalk-tab-icon" alt="Wootalk dashboard tab" src="<?php echo plugins_url()?>/wootalk/inc/assets/icon/settings.png">
					</a>
					
					<a href="javascript:void(0)" class="wt-menuItem">
						<div class="wt-menuItem-title"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('My Templates','wootalk') ?></font></font></div>
						<div class="wt-menuItem-description"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('Templates Settings','wootalk') ?></font></font></div>
						<img class="wootalk-tab-icon" alt="Wootalk dashboard tab" src="<?php echo plugins_url()?>/wootalk/inc/assets/icon/template.png">
					<div class="wootalk-pro-wrap">
					<?php _e('My Templates Pro Version','wootalk') ?>
					</div>
					</a>
					<a href="javascript:void(0)" class="wt-menuItem">
						<div class="wt-menuItem-title"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('Support','wootalk') ?></font></font></div>
						<div class="wt-menuItem-description"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php _e('Support info','wootalk') ?></font></font></div>
						<img class="wootalk-tab-icon" alt="Wootalk dashboard tab" src="<?php echo plugins_url()?>/wootalk/inc/assets/icon/support.png">
					<div class="wootalk-pro-wrap">
					<?php _e('Support Pro Version','wootalk') ?>
					</div>
					</a>
					
					
				</div>
				<div class="wt-Header-footer">
					<font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Version 1.0.0	 Powered by <a href="http://wp-product.com/" target="_blank">Wootalk</a></font></font>
				</div>
			</header>

			<section class="wt-Content">
				<!-----------Wootalk activation form--------------->
				<div id="dashboard" class="wootalk-tab wootalk-dashboard wt-Page" style="display: none;">
					<form  method="POST" class="wootalk_options" enctype="multipart/form-data">
					<?php wp_nonce_field( 'security-nonce', 'security' ); ?>
						<div class="wt-sectionHeader">
								<h2 class="wt-title1">	
								<img class="wootalk-header-icon" alt="Wootalk header" src="<?php echo plugins_url()?>/wootalk/inc/assets/icon/home.png">
							<font style="vertical-align: inherit;">
								<font style="vertical-align: inherit;"><?php _e('WooTalk','wootalk') ?></font></font></h2>
							<div class="wt-inside wootalk-btn-wrap">
								<input type="hidden" name="action" value="main" />
								<input type="submit" name="submit" id="submit" class="wt-button" value="Save Changes">
							</div>
						</div>
							
						<div class="wt-field wt-field-wrap">
							<div class="wt-checkbox innner-fields">
							<?php 
							$wootalk_status=0;
							$wootalk_status=get_option('wootalk_status') ?>
								<input type="checkbox" id="active_wt" class="active_wt" name="wootalk_status" value="1" <?php echo ($wootalk_status==1) ? 'checked' : '' ?>>
								<label for="active_wt" class=""><?php _e('Active WooTalk','wootalk') ?></label>
							</div>
						</div>
					</form>		
				</div>
				
				<div id="email-settings" class="wootalk-tab wt-Page" style="display: none;">
				
				<!-----------Wootalk settings form--------------->
					<form  method="POST" class="wootalk_options" enctype="multipart/form-data">
							<?php wp_nonce_field( 'security-nonce', 'security' ); ?>
							<div class="wt-sectionHeader">
								<h2 class="wt-title1">
								<img class="wootalk-header-icon" alt="Wootalk header" src="<?php echo plugins_url()?>/wootalk/inc/assets/icon/settings.png">
							
								<font style="vertical-align: inherit;">
								<font style="vertical-align: inherit;"><?php _e('WooTalk Settings','wootalk') ?></font></font></h2>
								<div class="wt-inside wootalk-btn-wrap">
								<input type="submit" name="submit" id="submit" class="wootalk-btn wt-button" value="Save Changes">
							</div>
							</div>
							
							<div class="wt-field">
							 <div class="wt-inner-form">
								<p  class="wootalk-p-h"><?php _e('Email logo','wootalk') ?></p>
								<div class="wootalk-email-logo-wrap">
								<img class="wootalk-email-preview" width="100" height="100" src="<?php echo plugins_url()?>/wootalk/inc/assets/images/wootalk-logo.png"/>
								</div>
								<p  class="wootalk-p-b"><?php _e('Edit logo in pro version','wootalk') ?></p>
									
								</div>
								<div class="wt-checkbox">
								<?php 
								$wootalk_email_notification=0;
								$wootalk_email_notification=get_option('wootalk_email_notification') ?>
									<input type="checkbox" id="wootalk_email_notification" class="active_wt wootalk-checkbox" name="wootalk_email_notification" value="1" <?php echo ($wootalk_email_notification==1) ? 'checked' : '' ?>>
									<label for="active_wt" class=""><?php _e('Enable email notification','wootalk') ?></label>
								</div>
								
								<div class="wt-checkbox">
									<label for="active_wt" class=""><span class="wootalk-pro-text">Pro Version</span> <?php _e('Enable welcome notification','wootalk') ?> </label>
								</div>
									<div class="wt-checkbox">
									<label for="active_wt" class=""><span class="wootalk-pro-text">Pro Version</span> <?php _e('Enable send later via cron','wootalk') ?></label><br>
								</div>
								<input type="hidden" name="action" value="settings" />
							</div>
							
						</form>	
					</div>
			</section>
		</div>
			
</div>
