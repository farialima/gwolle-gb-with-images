<?php


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * gwolle_gb_get_entries
 * Function to get guestbook entries from the database.
 *
 * Parameter $args is an Array:
 * - num_entries   int: Number of requested entries. -1 will return all requested entries.
 * - offset        int: Start after this entry.
 * - checked       string: 'checked' or 'unchecked', List the entries that are checked or unchecked.
 * - trash         string: 'trash' or 'notrash', List the entries that are in trash or not in trash.
 * - spam          string: 'spam' or 'nospam', List the entries marked as spam or as no spam.
 * - author_id     string: All entries associated with this author_id (since 1.5.0).
 * - email         string: All entries associated with this emailaddress.
 * - no_moderators string: 'true', Only entries not written by a moderator (might be expensive with many users) (since 1.5.0).
 * - book_id       int: Only entries from this book. Default in the shortcode is 1 (since 1.5.1).
 *
 * Return:
 * - Array of objects of gwolle_gb_entry
 * - false if no entries found.
 */

function gwolle_gb_get_entries($args = array()) {
	global $wpdb;

	$where = " 1 = %d";
	$values = Array(1);

	if ( !is_array($args) ) {
		return false;
	}

	if ( isset($args['checked']) ) {
		if ( $args['checked'] == 'checked' || $args['checked'] == 'unchecked' ) {
			$where .= "
				AND
				ischecked = %d";
			if ( $args['checked'] == 'checked' ) {
				$values[] = 1;
			} else if ( $args['checked'] == 'unchecked' ) {
				$values[] = 0;
			}
		}
	}
	if ( isset($args['spam']) ) {
		if ( $args['spam'] == 'spam' || $args['spam'] == 'nospam' ) {
			$where .= "
				AND
				isspam = %d";
			if ( $args['spam'] == 'spam' ) {
				$values[] = 1;
			} else if ( $args['spam'] == 'nospam' ) {
				$values[] = 0;
			}
		}
	}
	if ( isset($args['trash']) ) {
		if ( $args['trash'] == 'trash' || $args['trash'] == 'notrash' ) {
			$where .= "
				AND
				istrash = %d";
			if ( $args['trash'] == 'trash' ) {
				$values[] = 1;
			} else if ( $args['trash'] == 'notrash' ) {
				$values[] = 0;
			}
		}
	}
	if ( isset( $args['author_id']) ) {
		$where .= "
			AND
			author_id = %d";
		$values[] = (int) $args['author_id'];
	}
	if ( isset($args['email']) ) {
		$where .= "
			AND
			author_email = %s";
		$values[] = $args['email'];
	}
	if ( isset($args['no_moderators']) ) {
		$no_moderators = $args['no_moderators'];
		if ( $no_moderators === 'true' ) {
			$users = gwolle_gb_get_moderators();
			if ( is_array($users) && !empty($users) ) {
				foreach ( $users as $user_info ) {
					$where .= "
						AND
						author_id != %d";
					$values[] = $user_info->ID;
				}
			}
		}
	}
	if ( isset( $args['book_id']) && ((int) $args['book_id']) > 0 ) {
		$where .= "
			AND
			book_id = %d";
		$values[] = (int) $args['book_id'];
	}

	// Offset
	$offset = " OFFSET 0 "; // default
	if ( isset($args['offset']) && (int) $args['offset'] > 0 ) {
		$offset = " OFFSET " . (int) $args['offset'];
	}

	// Limit
	if ( is_admin() ) {
		$perpage_option = (int) get_option('gwolle_gb-entries_per_page', 20);
	} else {
		$perpage_option = (int) get_option('gwolle_gb-entriesPerPage', 20);
	}

	$limit = " LIMIT " . $perpage_option; // default
	if ( isset($args['num_entries']) && (int) $args['num_entries'] > 0 ) {
		$limit = " LIMIT " . (int) $args['num_entries'];
	} else if ( isset($args['num_entries']) && (int) $args['num_entries'] == -1 ) {
		$limit = ' LIMIT 999999999999999 ';
		$offset = ' OFFSET 0 ';
	}


	$tablename = $wpdb->prefix . "gwolle_gb_entries";

	$sql_nonprepared = "
			SELECT
				`id`,
				`author_name`,
				`author_id`,
				`author_email`,
				`author_origin`,
				`author_website`,
				`author_ip`,
				`author_host`,
				`content`,
				`datetime`,
				`ischecked`,
				`checkedby`,
				`istrash`,
				`isspam`,
				`admin_reply`,
				`admin_reply_uid`,
				`book_id`
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			ORDER BY
				datetime DESC
			" . $limit . " " . $offset . "
			;";

	$sql = $wpdb->prepare( $sql_nonprepared, $values );

	/*
	 * Make sure to use wpdb->prepare in your function, avoid SQL injection attacks.
	 * - $sql is the value with the prepared sql query.
	 * - $sql_nonprepared is the sql query with placeholders still.
	 * - $values is an array with values that will replace those placeholders
	 * - $args are the additional arguments that were passed to this function.
	 */
	$sql = apply_filters( 'gwolle_gb_get_entries_sql', $sql, $sql_nonprepared, $values, $args );


	/* Support caching of the list of entries. */
	$key         = md5( serialize( $sql ) );
	$cache_key   = "gwolle_gb_get_entries:$key";
	$cache_value = wp_cache_get( $cache_key );

	if ( false === $cache_value ) {

		// Do a real query.
		$datalist = $wpdb->get_results( $sql, ARRAY_A );

		wp_cache_add( $cache_key, $datalist );

		// $wpdb->print_error();
		// echo "number of rows: " . $wpdb->num_rows;

	} else {

		// This is data from cache.
		$datalist = $cache_value;

	}


	if ( is_array($datalist) && !empty($datalist) ) {
		$entries = array();

		foreach ( $datalist as $data ) {

			// Use the fields that the setter method expects
			$item = array(
				'id'              => (int) $data['id'],
				'author_name'     => stripslashes($data['author_name']),
				'author_id'       => (int) $data['author_id'],
				'author_email'    => stripslashes($data['author_email']),
				'author_origin'   => stripslashes($data['author_origin']),
				'author_website'  => stripslashes($data['author_website']),
				'author_ip'       => $data['author_ip'],
				'author_host'     => $data['author_host'],
				'content'         => stripslashes($data['content']),
				'datetime'        => $data['datetime'],
				'ischecked'       => (int) $data['ischecked'],
				'checkedby'       => (int) $data['checkedby'],
				'istrash'         => (int) $data['istrash'],
				'isspam'          => (int) $data['isspam'],
				'admin_reply'     => stripslashes($data['admin_reply']),
				'admin_reply_uid' => (int) $data['admin_reply_uid'],
				'book_id'         => (int) $data['book_id'],
			);

			$entry = new gwolle_gb_entry();

			$entry->set_data( $item );

			// Add entry to the array of all entries
			$entries[] = $entry;
		}
		return $entries;
	}
	return false;
}


/*
 * Function to delete guestbook entries from the database.
 * Removes the log entries as well.
 *
 * Parameter $status is a string:
 * - spam         string: 'spam',  delete the entries marked as spam
 * - trash        string: 'trash', delete the entries that are in trash
 *
 * Return:
 * - int: Number of deleted entries
 * - bool: false if no entries found.
 */

function gwolle_gb_del_entries( $status ) {
	global $wpdb;

	// First get all the id's, so we can remove the logs later

	if ( $status == 'spam' ) {
		$where = "
			isspam = %d";
		$values[] = 1;
	} else if ( $status == 'trash' ) {
		$where = "
			istrash = %d";
		$values[] = 1;
	} else {
		return false; // not the right $status
	}

	$sql = "
			SELECT
				`id`
			FROM
				$wpdb->gwolle_gb_entries
			WHERE
				" . $where . "
			LIMIT 999999999999999
			OFFSET 0
		;";

	$sql = $wpdb->prepare( $sql, $values );

	$datalist = $wpdb->get_results( $sql, ARRAY_A );

	if ( is_array($datalist) && !empty($datalist) ) {

		$sql = "
			DELETE
			FROM
				$wpdb->gwolle_gb_entries
			WHERE
				" . $where . "
			;";

		$result = $wpdb->query(
			$wpdb->prepare( $sql, $values )
		);

		if ( $result > 0 ) {
			gwolle_gb_clear_cache();

			// Also remove the log entries
			foreach ( $datalist as $id ) {
				gwolle_gb_del_log_entries( $id['id'] );
			}

			return $result;
		}
	}
	return false;
}
