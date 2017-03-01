<?php


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Is User alowed to manage comments
 *
 * Args: $user_id
 *
 * Return:
 * - user_nicename or user_login if allowed
 * - false if not allowed
 */
function gwolle_gb_is_moderator($user_id) {

	if ( $user_id > 0 ) {
		if ( function_exists('user_can') && user_can( $user_id, 'moderate_comments' ) ) {
			// Only moderators
			$userdata = get_userdata( $user_id );
			if (is_object($userdata)) {
				if ( isset( $userdata->display_name ) ) {
					return $userdata->display_name;
				} else {
					return $userdata->user_login;
				}
			}
		}
	}
	return false;
}


/*
 * Get all the users with capability 'moderate_comments'.
 *
 * Return: Array with User objects.
 */
function gwolle_gb_get_moderators() {

	$role__in = array( 'Administrator', 'Editor', 'Author' );
	$role__in = apply_filters( 'gwolle_gb_get_moderators_role__in', $role__in );

	// role__in will only work since WP 4.4.
	$users_query = new WP_User_Query( array(
		'role__in' => $role__in,
		'fields'   => 'all',
		'orderby'  => 'display_name'
		) );
	$users = $users_query->get_results();

	$moderators = array();

	if ( is_array($users) && !empty($users) ) {
		foreach ( $users as $user_info ) {

			if ($user_info === FALSE) {
				// Invalid $user_id
				continue;
			}

			// No capability
			if ( ! user_can( $user_info, 'moderate_comments' ) ) {
				continue;
			}

			$moderators[] = $user_info;
		}
	}

	return $moderators;
}

/*
 * Get the setting for Gwolle-GB that is saved as serialized data.
 *
 * Args: $request, string with value 'form' or 'read'.
 *
 * Return:
 * - Array with settings for that request.
 * - or false if no setting.
 */
function gwolle_gb_get_setting($request) {

	$provided = array('form', 'read');
	if ( in_array( $request, $provided ) ) {
		switch ( $request ) {
			case 'form':
				$defaults = Array(
					'form_name_enabled'       => 'true',
					'form_name_mandatory'     => 'true',
					'form_city_enabled'       => 'true',
					'form_city_mandatory'     => 'false',
					'form_email_enabled'      => 'true',
					'form_email_mandatory'    => 'true',
					'form_homepage_enabled'   => 'true',
					'form_homepage_mandatory' => 'false',
					'form_message_enabled'    => 'true',
					'form_message_mandatory'  => 'true',
					'form_bbcode_enabled'     => 'false',
					'form_antispam_enabled'   => 'false',
					'form_recaptcha_enabled'  => 'false'
					);
				$setting = get_option( 'gwolle_gb-form', Array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_array($setting) && !empty($setting) ) {
					$setting = array_merge( $defaults, $setting );
					return $setting;
				}
				return $defaults;
				break;
			case 'read':
				if ( get_option('show_avatars') ) {
					$avatar = 'true';
				} else {
					$avatar = 'false';
				}

				$defaults = Array(
					'read_avatar'   => $avatar,
					'read_name'     => 'true',
					'read_city'     => 'true',
					'read_datetime' => 'true',
					'read_date'     => 'false',
					'read_content'  => 'true',
					'read_editlink' => 'true'
					);
				$setting = get_option( 'gwolle_gb-read', Array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_array($setting) && !empty($setting) ) {
					$setting = array_merge( $defaults, $setting );
					return $setting;
				}
				return $defaults;
				break;
			default:
				return false;
				break;
		}
	}
	return false;
}


/*
 * Uses intermittent meta_key to determine the permalink. See hooks.php and below gwolle_gb_set_meta_keys().
 * return (int) postid if found, else 0.
 */
function gwolle_gb_get_postid( $book_id = 1 ) {

	$the_query = new WP_Query( array(
		'post_type'           => 'any',
		'ignore_sticky_posts' => true,
		'meta_query'          => array(
			array(
				'key'   => 'gwolle_gb_read',
				'value' => 'true',
			),
			array(
				'key'   => 'gwolle_gb_book_id',
				'value' => $book_id,
			),
		)
	));
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$postid = get_the_ID();
			return $postid;
			break; // only one postid is needed.
		endwhile;
		wp_reset_postdata();
	}
	return 0;

}


/*
 * Delete author_id (and maybe checkedby) after deletion of user.
 * Will trim down db requests, because non-existent user do not get cached.
 */
function gwolle_gb_deleted_user( $user_id ) {
	$entries = gwolle_gb_get_entries(array(
		'author_id'   => $user_id,
		'num_entries' => -1
	));
	if ( is_array( $entries ) && !empty( $entries ) ) {
		foreach ( $entries as $entry ) {
			// method will take care of it...
			$save = $entry->save();
		}
	}
}
add_action( 'deleted_user', 'gwolle_gb_deleted_user' );


/*
 * Taken from wp-admin/includes/template.php touch_time()
 * Adapted for simplicity.
 */
function gwolle_gb_touch_time( $entry ) {
	global $wp_locale;

	$date = $entry->get_datetime();
	if ( !$date ) {
		$date = current_time('timestamp');
	}

	$dd = date( 'd', $date );
	$mm = date( 'm', $date );
	$yy = date( 'Y', $date );
	$hh = date( 'H', $date );
	$mn = date( 'i', $date );

	// Day
	echo '<label><span class="screen-reader-text">' . __( 'Day', 'gwolle-gb' ) . '</span><input type="text" id="dd" name="dd" value="' . $dd . '" size="2" maxlength="2" autocomplete="off" /></label>';

	// Month
	echo '<label for="mm"><span class="screen-reader-text">' . __( 'Month', 'gwolle-gb' ) . '</span><select id="mm" name="mm">\n';
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise($i, 2);
		echo "\t\t\t" . '<option value="' . $monthnum . '" ' . selected( $monthnum, $mm, false ) . '>';
		/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
		echo sprintf( __( '%1$s-%2$s', 'gwolle-gb' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
	}
	echo '</select></label>';

	// Year
	echo '<label for="yy"><span class="screen-reader-text">' . __( 'Year', 'gwolle-gb' ) . '</span><input type="text" id="yy" name="yy" value="' . $yy . '" size="4" maxlength="4" autocomplete="off" /></label>';
	echo '<br />';
	// Hour
	echo '<label for="hh"><span class="screen-reader-text">' . __( 'Hour', 'gwolle-gb' ) . '</span><input type="text" id="hh" name="hh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" /></label>:';
	// Minute
	echo '<label for="mn"><span class="screen-reader-text">' . __( 'Minute', 'gwolle-gb' ) . '</span><input type="text" id="mn" name="mn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" /></label>';
	?>

	<div class="gwolle_gb_timestamp">
		<!-- Clicking OK will place a timestamp here. -->
		<input type="hidden" id="gwolle_gb_timestamp" name="gwolle_gb_timestamp" value="" />
	</div>

	<p>
		<a href="#" class="gwolle_gb_save_timestamp hide-if-no-js button" title="<?php _e('Save the date and time', 'gwolle-gb'); ?>">
			<?php _e('Save Date', 'gwolle-gb'); ?>
		</a>
		<a href="#" class="gwolle_gb_cancel_timestamp hide-if-no-js button-cancel" title="<?php _e('Cancel saving date and time', 'gwolle-gb'); ?>">
			<?php _e('Cancel', 'gwolle-gb'); ?>
		</a>
	</p>
	<?php
}


/*
 * Set Meta_keys so we can find the post back.
 * Args: $shortcode, string with value 'write' or 'read'.
 *       $shortcode_atts, array with the shortcode attributes.
 *
 * Since 1.5.6
 */
function gwolle_gb_set_meta_keys( $shortcode, $shortcode_atts ) {

	if ( $shortcode = 'read' ) {
		// Set a meta_key so we can find the post with the shortcode back.
		$meta_value_read = get_post_meta( get_the_ID(), 'gwolle_gb_read', true );
		if ( $meta_value_read != 'true' ) {
			update_post_meta( get_the_ID(), 'gwolle_gb_read', 'true' );
		}
	}

	$book_id = 1; // default
	if ( isset($shortcode_atts['book_id']) ) {
		$book_id = $shortcode_atts['book_id'];
	}
	$meta_value_book_id = get_post_meta( get_the_ID(), 'gwolle_gb_book_id', true );
	if ( $meta_value_book_id != $book_id ) {
		update_post_meta( get_the_ID(), 'gwolle_gb_book_id', $book_id );
	}

}
