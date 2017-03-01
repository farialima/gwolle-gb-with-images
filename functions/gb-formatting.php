<?php


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Function to sanitize values from input fields for the database.
 * $input: string
 */
function gwolle_gb_sanitize_input($input) {
	$input = strval($input);
	$input = htmlspecialchars_decode($input, ENT_COMPAT);
	$input = strip_tags($input);
	$input = stripslashes($input); // Make sure we're not just adding lots of slashes (or WordPress does).
	$input = str_replace('\\', '&#92;', $input);
	$input = str_replace('"', '&quot;', $input);
	$input = str_replace("'", '&#39;', $input);
	$input = trim($input);
	return $input;
}


/*
 * Function to sanitize values for output in a form or div.
 * $output: string
 */
function gwolle_gb_sanitize_output($output) {
	$output = strval($output);
	$output = trim($output);
	$output = htmlspecialchars_decode($output, ENT_COMPAT);
	//$output = html_entity_decode($output, ENT_COMPAT, 'UTF-8'); // the opposite of htmlentities, for backwards compat. Breaks encoding...
	// Still wanting this encoded
	$output = strip_tags($output);
	$output = str_replace('\\', '&#92;', $output);
	$output = str_replace('"', '&quot;', $output);
	$output = str_replace("'", '&#39;', $output);
	return $output;
}


/*
 * Function to format values for beeing send by mail.
 * Since users can input malicious code we have to make
 * sure that this code is being taken care of.
 */
function gwolle_gb_format_values_for_mail($value) {
	$value = htmlspecialchars_decode($value, ENT_COMPAT);
	$value = str_replace('<', '{', $value);
	$value = str_replace('>', '}', $value);
	$value = str_replace('&quot;','\"', $value);
	$value = str_replace('&#039;', '\'', $value);
	$value = str_replace('&#39;', '\'', $value);
	$value = str_replace('&#47;', '/', $value);
	$value = str_replace('&#92;', '\\', $value);
	return $value;
}


/*
 * Function to build the excerpt
 *
 * Args: $content: (string) content of the entry to be shortened
 *       $excerpt_length: (int) the maximum length to return in number of words (uses wp_trim_words)
 *
 * Return: $excerpt: (string) the shortened content
 */
function gwolle_gb_get_excerpt( $content, $excerpt_length = 20 ) {
	$excerpt = wp_trim_words( $content, $excerpt_length, '...' );
	$excerpt = gwolle_gb_sanitize_output( $excerpt );
	if ( trim($excerpt) == '' ) {
		$excerpt = '<i>' . __('No content to display. This entry is empty.', 'gwolle-gb') . '</i>';
	}
	return $excerpt;
}


/*
 * Get Author name in the right format as html
 *
 * Args: $entry object
 *
 * Return: $author_name_html string with html
 */
function gwolle_gb_get_author_name_html($entry) {

	$author_name = gwolle_gb_sanitize_output( trim( $entry->get_author_name() ) );

	// Registered User gets italic font-style
	$author_id = $entry->get_author_id();
	$is_moderator = gwolle_gb_is_moderator( $author_id );
	if ( $is_moderator ) {
		$author_name_html = '<i>' . $author_name . '</i>';
	} else {
		$author_name_html = $author_name;
	}

	if ( function_exists('bp_core_get_user_domain') ) {
		// Link to Buddypress profile.
		$author_website = trim( bp_core_get_user_domain( $author_id ) );
		if ($author_website) {
			$author_name_html = '<a href="' . $author_website . '" target="_blank"
				title="' . /* translators: BuddyPress profile */ __( 'Visit the profile of', 'gwolle-gb' ) . ' ' . $author_name . ': ' . $author_website . '">' . $author_name_html . '</a>';
		}
	} else if ( get_option('gwolle_gb-linkAuthorWebsite', 'true') === 'true' ) {
		// Link to author website if set in options.
		$author_website = trim( $entry->get_author_website() );
		if ($author_website) {
			$pattern = '/^http/';
			if ( ! preg_match($pattern, $author_website, $matches) ) {
				$author_website = "http://" . $author_website;
			}
			$author_name_html = '<a href="' . $author_website . '" target="_blank"
				title="' . __( 'Visit the website of', 'gwolle-gb' ) . ' ' . $author_name . ': ' . $author_website . '">' . $author_name_html . '</a>';
		}
	}

	$author_name_html = apply_filters( 'gwolle_gb_author_name_html', $author_name_html, $entry );

	return $author_name_html;
}
