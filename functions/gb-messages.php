<?php
/*
 * Functions to handle global variables for messages and errors.
 */


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Add messages from the form to show again after submitting an entry.
 * Parameters:
 * - message: string with html and text to show.
 * - error: if it is a validation error for the form (default false).
 * - error_field: which field does not validate.
 * Since 1.5.6
 *
 */
function gwolle_gb_add_message( $message = false, $error = false, $error_field = false ) {
	global $gwolle_gb_messages, $gwolle_gb_errors, $gwolle_gb_error_fields;

	// Messages to show on the frontend.
	if ( ! isset( $gwolle_gb_messages ) ) {
		$gwolle_gb_messages = array();
	}
	if ( $message ) {
		$gwolle_gb_messages[] = $message;
	}

	// Error, true or false.
	if ( $error === true ) {
		$gwolle_gb_errors = true;
	}

	// Error fields in the form.
	if ( ! isset( $gwolle_gb_error_fields ) ) {
		$gwolle_gb_error_fields = array();
	}
	if ( $error_field ) {
		$gwolle_gb_error_fields[] = $error_field;
	}
}


/*
 * Returns string with html with messages.
 * Since 1.5.6
 *
 */
function gwolle_gb_get_messages() {
	global $gwolle_gb_messages;

	// Messages to show on the frontend.
	if ( ! isset( $gwolle_gb_messages ) ) {
		$gwolle_gb_messages = array();
	}

	$messages = '';
	$returned_messages = array();
	$gwolle_gb_error_fields = gwolle_gb_get_error_fields();
	if ( is_array( $gwolle_gb_error_fields ) && !empty( $gwolle_gb_error_fields ) ) {
		// There was no data filled in, even though that was mandatory.
		$returned_messages[] = '<p class="error_fields"><strong>' . __('There were errors submitting your guestbook entry.', 'gwolle-gb') . '</strong></p>';
		$returned_messages[] = '<p class="error_fields" style="display: none;">' . print_r( $gwolle_gb_error_fields, true ) . '</p>';
	}
	foreach ( $gwolle_gb_messages as $message ) {
		$returned_messages[] = $message;
	}

	$returned_messages = apply_filters( 'gwolle_gb_messages', $returned_messages );

	foreach ( $returned_messages as $message ) {
		$messages .= $message; // string
	}

	return $messages;
}


/*
 * Returns bool, if errors were found.
 * Since 1.5.6
 *
 */
function gwolle_gb_get_errors() {
	global $gwolle_gb_errors;

	// Messages to show on the frontend.
	if ( ! isset( $gwolle_gb_errors ) ) {
		$gwolle_gb_errors = false;
	}

	$_gwolle_gb_errors = apply_filters( 'gwolle_gb_errors', $gwolle_gb_errors );

	return $_gwolle_gb_errors;
}


/*
 * Returns array with the fields that didnot validate.
 * Since 1.5.6
 *
 */
function gwolle_gb_get_error_fields() {
	global $gwolle_gb_error_fields;

	// Messages to show on the frontend.
	if ( ! isset( $gwolle_gb_error_fields ) ) {
		$gwolle_gb_error_fields = array();
	}

	$_gwolle_gb_error_fields = apply_filters( 'gwolle_gb_error_fields', $gwolle_gb_error_fields );

	return $_gwolle_gb_error_fields;
}


/*
 * Add formdata from the form to show again after submitting an entry.
 * Parameters:
 * - field: string with name of the formfield.
 * - value: value of the formfield to be used again.
 * Since 1.5.6
 *
 */
function gwolle_gb_add_formdata( $field, $value = '' ) {
	global $gwolle_gb_formdata;

	if ( ! isset( $gwolle_gb_formdata ) ) {
		$gwolle_gb_formdata = array();
	}
	if ( $value ) {
		$gwolle_gb_formdata[ $field ] = $value;
	}
}


/*
 * Returns string with html with formdata to be used again on the frontend.
 * Since 1.5.6
 *
 */
function gwolle_gb_get_formdata() {
	global $gwolle_gb_formdata;

	if ( ! isset( $gwolle_gb_formdata ) ) {
		$gwolle_gb_formdata = array();
	}

	$_gwolle_gb_formdata = apply_filters( 'gwolle_gb_formdata', $gwolle_gb_formdata );

	return $_gwolle_gb_formdata;
}
