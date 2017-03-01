<?php /*
 *
 *	export.php
 *	Lets the user export guestbook entries to a CSV file.
 *
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_export() {

	if ( function_exists('current_user_can') && ! current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	}

	gwolle_gb_admin_enqueue();

	$count = gwolle_gb_get_entry_count(array( 'all'  => 'all' ));
	$num_entries = 5000;
	$parts = ceil( $count / $num_entries );

	/*
	 * Build the Page and the Form
	 */
	?>
	<div class="wrap gwolle_gb">
		<div id="icon-gwolle-gb"><br /></div>
		<h1><?php _e('Export guestbook entries.', 'gwolle-gb'); ?></h1>

		<form name="gwolle_gb_export" id="gwolle_gb_export" method="POST" action="#" accept-charset="UTF-8">
			<input type="hidden" name="gwolle_gb_page" value="gwolle_gb_export" />
			<input type="hidden" name="gwolle_gb_export_part" id="gwolle_gb_export_part" value="1" />
			<input type="hidden" name="gwolle_gb_export_parts" id="gwolle_gb_export_parts" value="<?php echo $parts; ?>" />

			<?php
			/* Nonce */
			$nonce = wp_create_nonce( 'gwolle_gb_page_export' );
			echo '<input type="hidden" id="gwolle_gb_wpnonce" name="gwolle_gb_wpnonce" value="' . $nonce . '" />';
			?>

			<div id="poststuff" class="gwolle_gb_export metabox-holder">
				<div class="postbox-container">
					<?php
					add_meta_box('gwolle_gb_export_postbox', __('Export guestbook entries from Gwolle-GB','gwolle-gb'), 'gwolle_gb_export_postbox', 'gwolle_gb_export', 'normal');

					do_meta_boxes( 'gwolle_gb_export', 'normal', '' );
					?>
				</div>
			</div>

		</form>
	</div><?php

}


function gwolle_gb_export_postbox() {
	$count = gwolle_gb_get_entry_count( array( 'all' => 'all' ) );
	$num_entries = 5000;
	$parts = ceil( $count / $num_entries );
	if ( $count == 0 ) { ?>
		<p><?php _e('No entries were found.', 'gwolle-gb'); ?></p><?php
	} else {
		?>
		<p>
			<?php echo sprintf( _n( '%s entry was found and will be exported.', '%s entries were found and will be exported.', $count, 'gwolle-gb' ), $count ); ?>
			<br />
			<?php echo sprintf( _n( 'The download will happen in a CSV file in %s part.', 'The download will happen in a CSV file in %s parts.', $parts, 'gwolle-gb' ), $parts ); ?>
		</p>
		<p>
			<?php _e('The exporter will preserve the following data per entry:', 'gwolle-gb'); ?>
		</p>
		<ul class="ul-disc">
			<li><?php _e('Name', 'gwolle-gb'); ?></li>
			<li><?php _e('E-Mail address', 'gwolle-gb'); ?></li>
			<li><?php _e('URL/Website', 'gwolle-gb'); ?></li>
			<li><?php _e('Origin', 'gwolle-gb'); ?></li>
			<li><?php _e('Date of the entry', 'gwolle-gb'); ?></li>
			<li><?php _e('IP address', 'gwolle-gb'); ?></li>
			<li><?php _e('Host address', 'gwolle-gb'); ?></li>
			<li><?php _e('Message', 'gwolle-gb'); ?></li>
			<li><?php _e('"is checked" flag', 'gwolle-gb'); ?></li>
			<li><?php _e('"is spam" flag', 'gwolle-gb'); ?></li>
			<li><?php _e('"is trash" flag', 'gwolle-gb'); ?></li>
			<li><?php _e('Admin Reply', 'gwolle-gb'); ?></li>
		</ul>
		<?php _e('The exporter does not delete any data, so your data will still be here.', 'gwolle-gb'); ?>

		<p>
			<label for="start_export_enable" class="selectit">
				<input id="start_export_enable" name="start_export_enable" type="checkbox" />
				<?php _e('Export all entries from this website.', 'gwolle-gb'); ?>
			</label>
		</p>
		<p class="gwolle_gb_export_gif_container">
			<input name="gwolle_gb_start_export" id="gwolle_gb_start_export" type="submit" class="button" disabled value="<?php esc_attr_e('Start export', 'gwolle-gb'); ?>">
			<span class="gwolle_gb_export_gif"></span>
		</p>
		<?php
	}
}


add_action('admin_init', 'gwolle_gb_export_action');
function gwolle_gb_export_action() {
	if ( is_admin() ) {
		if ( isset( $_POST['gwolle_gb_page']) &&  $_POST['gwolle_gb_page'] == 'gwolle_gb_export' ) {
			gwolle_gb_export_callback();
		}
	}
}


/*
 * Callback function for request generated from the Export page
 */
function gwolle_gb_export_callback() {

	if ( function_exists('current_user_can') && ! current_user_can('manage_options') ) {
		echo 'error, no permission.';
		die();
	}

	/* Check Nonce */
	$verified = false;
	if ( isset($_POST['gwolle_gb_wpnonce']) ) {
		$verified = wp_verify_nonce( $_POST['gwolle_gb_wpnonce'], 'gwolle_gb_page_export' );
	}
	if ( $verified == false ) {
		// Nonce is invalid.
		_e('Nonce check failed. Please go back and try again.', 'gwolle-gb');
		die();
	}

	$count = gwolle_gb_get_entry_count(array( 'all'  => 'all' ));
	$num_entries = 5000;
	$parts = ceil( $count / $num_entries );
	if ( isset( $_POST['gwolle_gb_export_part']) && ( (int) $_POST['gwolle_gb_export_part'] < ($parts + 1) ) ) {
		$part = (int) $_POST['gwolle_gb_export_part'];
	} else {
		echo '(Gwolle-GB) Wrong part requested.';
		die();
	}
	$offset = ($part * 5000) - 5000;

	$entries = gwolle_gb_get_entries(array(
			'num_entries' => $num_entries,
			'offset'      => $offset,
			'all'         => 'all'
		));

	if ( is_array($entries) && !empty($entries) ) {

		// Clean everything before here
		ob_end_clean();

		// Output headers so that the file is downloaded rather than displayed
		$filename = 'gwolle_gb_export_' . GWOLLE_GB_VER . '_' . date('Y-m-d_H-i') . '-part_' . $part . '_of_' . $parts . '.csv';
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		// Create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');

		// Output the column headings
		fputcsv($output, array(
				'id',
				'author_name',
				'author_email',
				'author_origin',
				'author_website',
				'author_ip',
				'author_host',
				'content',
				'datetime',
				'isspam',
				'ischecked',
				'istrash',
				'admin_reply'
			));

		$saved = 0;
		foreach ( $entries as $entry ) {

			$row = Array();

			$row[] = $entry->get_id();
			$row[] = addslashes($entry->get_author_name());
			$row[] = addslashes($entry->get_author_email());
			$row[] = addslashes($entry->get_author_origin());
			$row[] = addslashes($entry->get_author_website());
			$row[] = $entry->get_author_ip();
			$row[] = $entry->get_author_host();
			$row[] = addslashes($entry->get_content());
			$row[] = $entry->get_datetime();
			$row[] = $entry->get_isspam();
			$row[] = $entry->get_ischecked();
			$row[] = $entry->get_istrash();
			$row[] = $entry->get_admin_reply();

			fputcsv($output, $row);

			gwolle_gb_add_log_entry( $entry->get_id(), 'exported-to-csv' );
			$saved++;

		}

		fclose($output);
		die();
	}

	echo '(Gwolle-GB) Error, no entries.';
	die();
}
