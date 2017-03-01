<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_settingstab_form() {

	if ( function_exists('current_user_can') && ! current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_forms" />
	<?php
	settings_fields( 'gwolle_gb_options' );
	do_settings_sections( 'gwolle_gb_options' );

	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_page_settings_formtab' );
	echo '<input type="hidden" id="gwolle_gb_page_settings_formtab" name="gwolle_gb_page_settings_formtab" value="' . $nonce . '" />';
	?>
	<table class="form-table">
		<tbody>

		<tr valign="top">
			<th scope="row"><label for="require_login"><?php _e('Require Login', 'gwolle-gb'); ?></label></th>
			<td>
				<input type="checkbox" id="require_login" name="require_login" <?php
					if ( get_option( 'gwolle_gb-require_login', 'false' ) === 'true' ) {
						echo 'checked="checked"';
					}
					?> />
				<label for="require_login"><?php _e('Require user to be logged in.', 'gwolle-gb'); ?></label>
				<br />
				<span class="setting-description"><?php _e('Only allow logged-in users to add a guestbook entry.', 'gwolle-gb'); ?></span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="labels_float"><?php _e('Labels float', 'gwolle-gb'); ?></label></th>
			<td>
				<input type="checkbox" id="labels_float" name="labels_float" <?php
					if ( get_option( 'gwolle_gb-labels_float', 'true' ) === 'true' ) {
						echo 'checked="checked"';
					}
					?> />
				<label for="labels_float"><?php _e('Labels in the form float to the left.', 'gwolle-gb'); ?></label>
				<br />
				<span class="setting-description"><?php _e('Labels in the form float to the left. Otherwise the labels will be above the input-fields.', 'gwolle-gb'); ?></span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="header"><?php _e('Header Text', 'gwolle-gb'); ?></label></th>
			<td><?php
				$header = gwolle_gb_sanitize_output( get_option('gwolle_gb-header', false) );
				if ( !$header ) {
					$header = __('Write a new entry for the Guestbook', 'gwolle-gb');
				} ?>
				<input name="header" id="header" class="regular-text" type="text" value="<?php echo $header; ?>" />
				<br />
				<span class="setting-description">
					<?php _e('You can set the header that is shown on top of the form.', 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="notice"><?php _e('Notice Text', 'gwolle-gb'); ?></label></th>
			<td>
				<?php
				$notice = gwolle_gb_sanitize_output( get_option('gwolle_gb-notice', false) );
				if (!$notice) { // No text set by the user. Use the default text.
					$notice = __("
Fields marked with * are required.
Your E-mail address won't be published.
For security reasons we save the IP address %ip%.
It's possible that your entry will only be visible in the guestbook after we reviewed it.
We reserve the right to edit, delete, or not publish entries.
"
, 'gwolle-gb');
							} ?>
				<textarea name="notice" id="notice" style="width:400px;height:180px;" class="regular-text"><?php echo $notice; ?></textarea>
				<br />
				<span class="setting-description">
					<?php _e('You can set the content of the notice that gets shown below the form.', 'gwolle-gb');
					echo '<br />';
					_e('You can use the tag %ip% to show the ip address.', 'gwolle-gb');
					echo '<br /><br />';
					_e('If you use a Multi-Lingual plugin, keep the 2 fields for header and notice empty when saving. That way the default text will be shown from a translated PO file.', 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_ajax"><?php _e('Use AJAX', 'gwolle-gb'); ?></label></th>
			<td>
				<input type="checkbox" id="form_ajax" name="form_ajax" <?php
					if ( get_option( 'gwolle_gb-form_ajax', 'true' ) === 'true' ) {
						echo 'checked="checked"';
					}
					?> />
				<label for="form_ajax"><?php _e('Use AJAX to submit the form.', 'gwolle-gb'); ?></label>
				<br />
				<span class="setting-description"><?php _e('Submit the form while staying on the same page and place, without a new page load.', 'gwolle-gb'); ?></span>
			</td>
		</tr>

		</tbody>
	</table>

	<table class="form-table">
		<tbody>

		<?php $form_setting = gwolle_gb_get_setting( 'form' ); ?>

		<tr valign="top">
			<td colspan="3"><h3><?php _e('Configure the form that is shown to visitors.', 'gwolle-gb'); ?></h3></td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_name_enabled"><?php _e('Name', 'gwolle-gb'); ?>:</label></th>
			<td>
				<input type="checkbox" id="form_name_enabled" name="form_name_enabled"<?php
					if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_name_enabled"><?php _e('Enabled', 'gwolle-gb'); ?></label>
			</td>
			<td>
				<input type="checkbox" id="form_name_mandatory" name="form_name_mandatory"<?php
					if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_name_mandatory"><?php _e('Mandatory', 'gwolle-gb'); ?></label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_city_enabled"><?php _e('City', 'gwolle-gb'); ?>:</label></th>
			<td>
				<input type="checkbox" id="form_city_enabled" name="form_city_enabled"<?php
					if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_city_enabled"><?php _e('Enabled', 'gwolle-gb'); ?></label>
			</td>
			<td>
				<input type="checkbox" id="form_city_mandatory" name="form_city_mandatory"<?php
					if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_city_mandatory"><?php _e('Mandatory', 'gwolle-gb'); ?></label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_email_enabled"><?php _e('Email', 'gwolle-gb'); ?>:</label></th>
			<td>
				<input type="checkbox" id="form_email_enabled" name="form_email_enabled"<?php
					if ( isset($form_setting['form_email_enabled']) && $form_setting['form_email_enabled']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_email_enabled"><?php _e('Enabled', 'gwolle-gb'); ?></label>
			</td>
			<td>
				<input type="checkbox" id="form_email_mandatory" name="form_email_mandatory"<?php
					if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_email_mandatory"><?php _e('Mandatory', 'gwolle-gb'); ?></label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_homepage_enabled"><?php _e('Website', 'gwolle-gb'); ?>:</label></th>
			<td>
				<input type="checkbox" id="form_homepage_enabled" name="form_homepage_enabled"<?php
					if ( isset($form_setting['form_homepage_enabled']) && $form_setting['form_homepage_enabled']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_homepage_enabled"><?php _e('Enabled', 'gwolle-gb'); ?></label>
			</td>
			<td>
				<input type="checkbox" id="form_homepage_mandatory" name="form_homepage_mandatory"<?php
					if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_homepage_mandatory"><?php _e('Mandatory', 'gwolle-gb'); ?></label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_message_enabled"><?php _e('Message', 'gwolle-gb'); ?>:</label></th>
			<td>
				<input type="checkbox" id="form_message_enabled" name="form_message_enabled"<?php
					if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_message_enabled"><?php _e('Enabled', 'gwolle-gb'); ?></label>
			</td>
			<td>
				<input type="checkbox" id="form_message_mandatory" name="form_message_mandatory"<?php
					if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_message_mandatory"><?php _e('Mandatory', 'gwolle-gb'); ?></label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_bbcode_enabled"><?php _e('BBcode and Emoji', 'gwolle-gb'); ?>:</label></th>
			<td>
				<input type="checkbox" id="form_bbcode_enabled" name="form_bbcode_enabled"<?php
					if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_bbcode_enabled"><?php _e('Enabled', 'gwolle-gb'); ?></label>
			</td>
			<td>
				<?php _e('Will only be shown if the Message is enabled.', 'gwolle-gb'); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_antispam_enabled"><?php _e('Custom Anti-spam', 'gwolle-gb'); ?>:</label></th>
			<td>
				<input type="checkbox" id="form_antispam_enabled" name="form_antispam_enabled"<?php
					if ( isset($form_setting['form_antispam_enabled']) && $form_setting['form_antispam_enabled']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_antispam_enabled"><?php _e('Enabled', 'gwolle-gb'); ?></label>
			</td>
			<td>
				<?php _e('When enabled it is mandatory.', 'gwolle-gb'); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="form_recaptcha_enabled"><?php _e('CAPTCHA', 'gwolle-gb'); ?>:</label></th>
			<td>
				<input type="checkbox" id="form_recaptcha_enabled" name="form_recaptcha_enabled"<?php
					if ( isset($form_setting['form_recaptcha_enabled']) && $form_setting['form_recaptcha_enabled']  === 'true' ) {
						echo ' checked="checked"';
					}
					?> />
				<label for="form_recaptcha_enabled"><?php _e('Enabled', 'gwolle-gb'); ?></label>
			</td>
			<td>
				<?php _e('When enabled it is mandatory.', 'gwolle-gb'); ?>
			</td>
		</tr>

		<tr>
			<th colspan="3">
				<p class="submit">
					<input type="submit" name="gwolle_gb_settings_form" id="gwolle_gb_settings_form" class="button-primary" value="<?php esc_attr_e('Save settings', 'gwolle-gb'); ?>" />
				</p>
			</th>
		</tr>

		</tbody>
	</table>

	<?php
}
