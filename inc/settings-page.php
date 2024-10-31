 <div class="mepc-static-banner">
	<div class="inner-container">
		<div class="logo-wrapper" style="display:block;float:left;">
			<img src="<?php echo plugins_url(PCFM_PLUGIN_DIR . '/assets/images/otter-logo-50.png') ?>" width="50">
		</div>
		<div class="logo-wrapper" style="display:block;float:left;margin-left:15px;">
			<div class="app-title">Marketo Preference Center <span class="byline">by FeedOtter</span></div> 
		</div>
		<div class="static-menu">
			<ul>
				<li>
					<a href="mailto:success@feedotter.com" target="_blank">Send Us Feedback</a>
				</li>
				<li class="mepc-menu-button">
					<a id="mepc-create-new-optin-button" href="https://app.feedotter.com/" class="button button-secondary mepc-create-button" title="Create New Preference Center" target="_blank">Create New Preference Center</a>
				</li>
			</ul>
		</div>
	</div>
</div>
<div class="wrap mepc-page">
	<div class="mepc-ui">
		<div class="mepc-tabs">
			<ul class="mepc-panels">
				<?php if( get_option ( 'pcfm_api_key' ) && get_option ( 'pcfm_api_key_connect_success' ) ) { ?>
				<li class="mepc-panel mepc-panel-support <?php echo $tab == PCFM_MANAGE_PREFERENCE_CENTERS_TAB_SLUG ? 'mepc-panel-active' : '' ?>"><a href="<?php echo pcfm_get_manage_centers_page_url() ?>">Manage Preference Centers</a></li>
				<?php } ?>
				<li class="mepc-panel mepc-panel-api mepc-panel-first <?php echo $tab == PCFM_API_SETTINGS_TAB_SLUG ? 'mepc-panel-active' : '' ?>"><a href="<?php echo pcfm_get_options_page_url() ?>">Api Credentials</a></li>
				<li class="mepc-panel mepc-panel-support <?php echo $tab == PCFM_SUPPORT_TAB_SLUG ? 'mepc-panel-active' : '' ?>"><a href="<?php echo pcfm_get_support_page_url() ?>">Support</a></li>
			</ul>
		</div>
		<div class="mepc-tabs-content">
			<?php switch( $tab ) {
				case PCFM_API_SETTINGS_TAB_SLUG: ?>
	  				<form id="mepc-form-api" class="mepc-form" method="post" action="options.php">
						<?php settings_fields( 'pcfm_plugin_settings' ); ?>
						<?php do_settings_sections( 'pcfm_plugin_settings' ); ?>
						<h3>API Credentials</h3>
						<p class="mepc-red"><strong>You must authenticate your FeedOtter account before you can use Email Preference Centers on this site.</strong></p>
						<p><em>Need a FeedOtter account? <a href="https://feedotter.com/pricing/?utm_source=orgplugin&amp;utm_medium=link&amp;utm_campaign=wpdashboard" title="Click here to view FeedOtter plans and pricing" target="_blank">Click here to view FeedOtter plans and pricing.</a></em></p>
						<div class="mepc-field-box mepc-password-field mepc-field-box-apikey mepc-clear">
							<p class="mepc-field-wrap"><label for="mepc-field-apikey">API Key</label><br>
	                            <input type="text" id="mepc-field-apikey" class="" tabindex="430" name="pcfm_api_key" autocomplete="off" value="<?php echo esc_attr( get_option( 'pcfm_api_key' ) ); ?>" >
	                            <br><span class="mepc-field-desc">A single API Key found in your FeedOtter Account API area.</span>
							</p>
						</div>
						<p class="submit">
							<input class="button button-primary" type="submit" name="pcfm_submit" value="Connect to FeedOtter" tabindex="749">
						</p>
					</form>
					<?php break;?>
				<?php case PCFM_SUPPORT_TAB_SLUG:?>
					<div class="mepc-content mepc-content-support mepc-content-active">
						<h3>Support</h3>
						<p>Comming Soon!</p>
					</div>
					<?php break;?>
				<?php case PCFM_MANAGE_PREFERENCE_CENTERS_TAB_SLUG:?>
					<div class="mepc-content mepc-content-optins mepc-content-active">
						<?php if( count( $pref_centers ) < 1 ) { ?>
							<h3>No Preference Centers Found</h3>
						<?php } else { ?>
							<h3>Preference Centers</h3>
							<?php foreach ( $pref_centers as $key => $pref_center ) { ?>
								<p class="mepc-optin <?php echo( $key = 1 ? 'mepc-optin-first' : '' ) ?> ">
									<a href="" title="Manage output settings for <?php echo esc_attr( $pref_center->name )?>"><?php echo esc_html( ucwords( $pref_center->name ) )?></a>
									<span class="mepc-status">
										<span class="mepc-green" style="display:<?php echo !isset( $pcfm_rewrites[sanitize_key( $pref_center->id )] ) ? "none" : "inherit"?>">Live</span>
										<span class="mepc-red" style="display:<?php echo isset( $pcfm_rewrites[sanitize_key( $pref_center->id )] ) ? "none" : "inherit"?>">Disabled</span>
									</span><br/>
									<a class="mepc-slug" target="_blank" href="<?php echo esc_url_raw( '/'.$pref_center->permalink )?>"><?php echo esc_url_raw( get_site_url() .'/' . $pref_center->permalink ) ?></a>
									<span class="mepc-links">
										<a href="<?php echo esc_url_raw( PCFM_FEEDOTTER_HOST. $pref_center->editURL )?>" title="Edit this preference center in the FeedOtter app." target="_blank">Edit Design</a> 
										|
										<a href="javascript:void(0)" onclick="mepcGoLive(this, '<?php echo sanitize_key($pref_center->id)?>')" data-is-live="<?php echo ( isset( $pcfm_rewrites[sanitize_key( $pref_center->id )] ) && $pcfm_rewrites[sanitize_key( $pref_center->id )] == TRUE ) ? 1 : 0 ?>">
											<?php if ( isset( $pcfm_rewrites[sanitize_key( $pref_center->id )] ) && $pcfm_rewrites[sanitize_key( $pref_center->id )] == TRUE) { ?>
												Disable
											<?php } else { ?>
												Go Live
											<?php } ?>
	     								</a>
									</span>
								</p>
							<?php } ?>
							<p class="submit">
								<a href="" class="button button-primary">Refresh Preference Centers</a>
							</p>
						<?php } ?>
					</div>
					<?php break;?>
			<?php } ?>
		</div>
    </div>
</div>