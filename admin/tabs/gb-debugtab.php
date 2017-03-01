<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}

/*
 * $debug_test is a bool, if we want a debug test to be done.
 */
function gwolle_gb_page_settingstab_debug( $debug_test ) {

	if ( function_exists('current_user_can') && ! current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_debug" />
	<?php
	settings_fields( 'gwolle_gb_options' );
	do_settings_sections( 'gwolle_gb_options' );

	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_page_settings_debugtab' );
	echo '<input type="hidden" id="gwolle_gb_page_settings_debugtab" name="gwolle_gb_page_settings_debugtab" value="' . $nonce . '" />';
	?>
	<table class="form-table">
		<tbody>

		<tr valign="top">
			<td scope="row" colspan="2">
				<p>
					<?php _e('Please provide this information when posting a support message on the support forum.', 'gwolle-gb'); ?>
				</p>
			</td>
		</tr>

		<?php
		if ( $debug_test ) {
			$entry_id = gwolle_gb_test_add_entry( false );
			$entry_id_emoji = gwolle_gb_test_add_entry( true );
			?>

			<tr>
				<th><?php _e('Standard test:', 'gwolle-gb'); ?></th>
				<td><?php
					if ( $entry_id == 0 ) {
						echo '👎 ';
						_e('Failed.', 'gwolle-gb');
					} else {
						echo '👍 ';
						_e('Succeeded.', 'gwolle-gb');
					} ?>
				</td>
			</tr>
			<tr>
				<th><?php _e('Emoji test:', 'gwolle-gb'); ?></th>
				<td><?php
					if ( $entry_id_emoji == 0 ) {
						echo '👎 ';
						_e('Failed.', 'gwolle-gb');
					} else {
						echo '👍 ';
						_e('Succeeded.', 'gwolle-gb');
					} ?>
				</td>
			</tr>
			<?php
		}
		?>

		<tr valign="top">
			<th scope="row"><label for="blogdescription"><?php _e('Test', 'gwolle-gb'); ?></label></th>
			<td>
				<p>
				<?php _e('This test will attempt to save two test entries, one with standard text and one with Emoji.', 'gwolle-gb'); ?>
				</p>
				<p>
					<input type="submit" name="gwolle_gb_debug" id="gwolle_gb_debug" class="button button-primary" value="<?php esc_attr_e('Run test', 'gwolle-gb'); ?>" />
				</p>
			</td>
		</tr>

		<tr valign="top">
			<?php gwolle_gb_debug_info(); ?>
		</tr>

		</tbody>
	</table>

	<?php
}
