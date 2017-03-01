<?php

/*
 * Handles AJAX request from Gwolle-GB AJAX Submit.
 *
 * Returns json encoded data, which is handled with by frontend/js/script.js.
 */


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_form_ajax_callback() {

	$saved = gwolle_gb_frontend_posthandling();

	$data = array();
	$data['saved']                  = $saved;
	$data['gwolle_gb_messages']     = gwolle_gb_get_messages();
	$data['gwolle_gb_errors']       = gwolle_gb_get_errors();
	$data['gwolle_gb_error_fields'] = gwolle_gb_get_error_fields();

	if ( $saved ) {
		$entry = new gwolle_gb_entry();
		$result = $entry->load( $saved );
		if ( $result ) {
			if ( $entry->get_isspam() === 1 || $entry->get_istrash() === 1 || $entry->get_ischecked() === 0 ) {
				// Invisible.
			} else {
				// Try to load and require_once the template from the themes folders.
				if ( locate_template( array('gwolle_gb-entry.php'), true, true ) == '') {
					$data['entry'] = '<!-- Gwolle-GB Entry: Default Template Loaded -->
						';
					// No template found and loaded in the theme folders.
					// Load the template from the plugin folder.
					require_once('gwolle_gb-entry.php');
				} else {
					$data['entry'] = '<!-- Gwolle-GB Entry: Custom Template Loaded -->
						';
				}
				$data['entry'] .= gwolle_gb_entry_template( $entry, true, 0 );
			}
		}
	}

	echo json_encode( $data );

	die(); // This is required to return a proper result.

}
add_action( 'wp_ajax_gwolle_gb_form_ajax', 'gwolle_gb_form_ajax_callback' );
add_action( 'wp_ajax_nopriv_gwolle_gb_form_ajax', 'gwolle_gb_form_ajax_callback' );
