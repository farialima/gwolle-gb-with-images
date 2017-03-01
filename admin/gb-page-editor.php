<?php
/*
 * Editor for editing entries and writing admin entries.
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_editor() {
	global $entry;

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	}

	gwolle_gb_admin_enqueue();

	$gwolle_gb_errors = '';
	$gwolle_gb_messages = '';

	$sectionHeading = __('Edit guestbook entry', 'gwolle-gb');

	// Always fetch the requested entry, so we can compare the $entry and the $_POST.
	$entry = new gwolle_gb_entry();

	if ( isset($_POST['entry_id']) ) { // _POST has preference over _GET
		$entry_id = intval($_POST['entry_id']);
	} else if ( isset($_GET['entry_id']) ) {
		$entry_id = intval($_GET['entry_id']);
	}
	if ( isset($entry_id) && $entry_id > 0 ) {
		$result = $entry->load( $entry_id );
		if ( !$result ) {
			$gwolle_gb_messages .= '<p class="error">' . __('Entry could not be found.', 'gwolle-gb') . '</p>';
			$gwolle_gb_errors = 'error';
			$sectionHeading = __('Guestbook entry (error)', 'gwolle-gb');
		}
	} else {
		$sectionHeading = __('New guestbook entry', 'gwolle-gb');
	}


	/*
	 * Handle the $_POST
	 */
	if ( isset($_POST['gwolle_gb_page']) && $_POST['gwolle_gb_page'] == 'editor' && $gwolle_gb_errors == '' ) {

		/* Check Nonce */
		$continue_on_nonce_checked = false;
		if ( isset($_POST['gwolle_gb_wpnonce']) ) {
			$verified = wp_verify_nonce( $_POST['gwolle_gb_wpnonce'], 'gwolle_gb_page_editor' );
			if ( $verified == true ) {
				$continue_on_nonce_checked = true;
			} else {
				// Nonce is invalid, so considered spam
				$gwolle_gb_messages .= '<p>' . __('Nonce check failed. Please try again.', 'gwolle-gb') . '</p>';
				$gwolle_gb_errors = 'error';
			}
		}

		if ( !isset($_POST['entry_id']) || $_POST['entry_id'] != $entry->get_id() ) {
			$gwolle_gb_messages .= '<p class="error">' . __('Something strange happened.', 'gwolle-gb') . '</p>';
			$gwolle_gb_errors = 'error';
		} else if ( $_POST['entry_id'] > 0 && $entry->get_id() > 0 && $continue_on_nonce_checked ) {

			/*
			 * Check for changes, and update accordingly. This is on an Existing Entry!
			 */

			$changed = false;

			/* Set as checked or unchecked, and by whom */
			if ( isset($_POST['ischecked']) && $_POST['ischecked'] == 'on' ) {
				if ( $_POST['ischecked'] == 'on' && $entry->get_ischecked() == 0 ) {
					$entry->set_ischecked( true );
					$user_id = get_current_user_id(); // returns 0 if no current user
					$entry->set_checkedby( $user_id );
					gwolle_gb_add_log_entry( $entry->get_id(), 'entry-checked' );
					gwolle_gb_clear_cache( $entry );
					$changed = true;
				}
			} else if ( $entry->get_ischecked() == 1 ) {
				$entry->set_ischecked( false );
				gwolle_gb_add_log_entry( $entry->get_id(), 'entry-unchecked' );
				$changed = true;
			}

			/* Set as spam or not, and submit as ham or spam to Akismet service */
			if ( isset($_POST['isspam']) && $_POST['isspam'] == 'on' ) {
				if ( $_POST['isspam'] == 'on' && $entry->get_isspam() == 0 ) {
					$entry->set_isspam( true );
					$result = gwolle_gb_akismet( $entry, 'submit-spam' );
					if ( $result ) {
						$gwolle_gb_messages .= '<p>' . __('Submitted as Spam to the Akismet service.', 'gwolle-gb') . '</p>';
					}
					gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-spam' );
					$changed = true;
				}
			} else if ( $entry->get_isspam() == 1 ) {
				$entry->set_isspam( false );
				$result = gwolle_gb_akismet( $entry, 'submit-ham' );
				if ( $result ) {
					$gwolle_gb_messages .= '<p>' . __('Submitted as Ham to the Akismet service.', 'gwolle-gb') . '</p>';
				}
				gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-not-spam' );
				$changed = true;
			}

			/* Set as trash or not */
			if ( isset($_POST['istrash']) && $_POST['istrash'] == 'on' ) {
				if ( $_POST['istrash'] == 'on' && $entry->get_istrash() == 0 ) {
					$entry->set_istrash( true );
					gwolle_gb_add_log_entry( $entry->get_id(), 'entry-trashed' );
					$changed = true;
				}
			} else if ( $entry->get_istrash() == 1 ) {
				$entry->set_istrash( false );
				gwolle_gb_add_log_entry( $entry->get_id(), 'entry-untrashed' );
				$changed = true;
			}

			/* Check if the content changed, and update accordingly */
			if ( isset($_POST['gwolle_gb_content']) && $_POST['gwolle_gb_content'] != '' ) {
				if ( trim($_POST['gwolle_gb_content']) != $entry->get_content() ) {
					$entry_content = gwolle_gb_maybe_encode_emoji( $_POST['gwolle_gb_content'], 'content' );
					$entry->set_content( $entry_content );
					$changed = true;
				}
			}

			/* Check if the website changed, and update accordingly */
			if ( isset( $_POST['gwolle_gb_author_website'] ) ) {
				$website = trim( $_POST['gwolle_gb_author_website'] );
			} else {
				$website = '';
			}
			if ( $website != $entry->get_author_website() ) {
				$entry->set_author_website( $website );
				$changed = true;
			}

			/* Check if the author_origin changed, and update accordingly */
			if ( isset($_POST['gwolle_gb_author_origin']) ) {
				if ( $_POST['gwolle_gb_author_origin'] != $entry->get_author_origin() ) {
					$entry_origin = gwolle_gb_maybe_encode_emoji( $_POST['gwolle_gb_author_origin'], 'author_origin' );
					$entry->set_author_origin( $entry_origin );
					$changed = true;
				}
			}

			/* Check if the admin_reply changed, and update and log accordingly */
			if ( isset($_POST['gwolle_gb_admin_reply']) ) {
				if ( trim($_POST['gwolle_gb_admin_reply']) != $entry->get_admin_reply() ) {
					$gwolle_gb_admin_reply = gwolle_gb_maybe_encode_emoji( $_POST['gwolle_gb_admin_reply'], 'admin_reply' );
					if ( $gwolle_gb_admin_reply != '' && $entry->get_admin_reply() == '' ) {
						$entry->set_admin_reply_uid( get_current_user_id() );
						gwolle_gb_add_log_entry( $entry->get_id(), 'admin-reply-added' );
					} else if ( $gwolle_gb_admin_reply == '' && $entry->get_admin_reply() != '' ) {
						$entry->set_admin_reply_uid( 0 );
						gwolle_gb_add_log_entry( $entry->get_id(), 'admin-reply-removed' );
					} else if ( $gwolle_gb_admin_reply != '' && $entry->get_admin_reply() != '' ) {
						gwolle_gb_add_log_entry( $entry->get_id(), 'admin-reply-updated' );
					}
					$entry->set_admin_reply( $gwolle_gb_admin_reply );
					$changed = true;
				}
			}

			/* Mail the author about the Admin Reply, if so requested */
			if ( isset($_POST['gwolle_gb_admin_reply_mail_author']) ) {
				if ( $_POST['gwolle_gb_admin_reply_mail_author'] == 'on' ) {
					gwolle_gb_mail_author_on_admin_reply( $entry );
				}
			}

			/* Check if the author_name changed, and update accordingly */
			if ( isset($_POST['gwolle_gb_author_name']) ) {
				if ( $_POST['gwolle_gb_author_name'] != $entry->get_author_name() ) {
					$entry_name = gwolle_gb_maybe_encode_emoji( $_POST['gwolle_gb_author_name'], 'author_name' );
					$entry->set_author_name( $entry_name );
					$changed = true;
				}
			}

			/* Check if the datetime changed, and update accordingly */
			if ( isset($_POST['gwolle_gb_timestamp']) && is_numeric($_POST['gwolle_gb_timestamp']) ) {
				if ( $_POST['gwolle_gb_timestamp'] != $entry->get_datetime() ) {
					$entry->set_datetime( (int) $_POST['gwolle_gb_timestamp'] );
					$changed = true;
				}
			}

			/* Check if the book_id changed, and update accordingly */
			if ( isset($_POST['gwolle_gb_book_id']) && is_numeric($_POST['gwolle_gb_book_id']) ) {
				if ( $_POST['gwolle_gb_book_id'] != $entry->get_book_id() ) {
					$entry->set_book_id( (int) $_POST['gwolle_gb_book_id'] );
					$changed = true;
				}
			}

			/* Save the entry */
			if ( $changed ) {
				$result = $entry->save();
				if ($result ) {
					gwolle_gb_add_log_entry( $entry->get_id(), 'entry-edited' );
					$gwolle_gb_messages .= '<p>' . __('Changes saved.', 'gwolle-gb') . '</p>';
					do_action( 'gwolle_gb_save_entry_admin', $entry );
				} else {
					$gwolle_gb_messages .= '<p>' . __('Error happened during saving.', 'gwolle-gb') . '</p>';
					$gwolle_gb_errors = 'error';
				}
			} else {
				$gwolle_gb_messages .= '<p>' . __('Entry was not changed.', 'gwolle-gb') . '</p>';
			}

			/* Remove permanently */
			if ( isset($_POST['istrash']) && $_POST['istrash'] == 'on' && isset($_POST['remove']) && $_POST['remove'] == 'on' ) {
				if ( $entry->get_istrash() == 1 ) {
					$entry->delete();
					$entry->set_id(0);
					$changed = true;
					// Overwrite any other message, only removal is relevant.
					$gwolle_gb_messages = '<p>' . __('Entry removed.', 'gwolle-gb') . '</p>';
					$entry = new gwolle_gb_entry();
				}
			}

		} else if ( $_POST['entry_id'] == 0 && $entry->get_id() == 0 && $continue_on_nonce_checked ) {

			/*
			 * Check for input, and save accordingly. This is on a New Entry! (So no logging)
			 */

			$saved = false;
			$data = Array();

			/* Set as checked anyway, new entry is always by an admin */
			$data['ischecked'] = true;
			$user_id = get_current_user_id(); // returns 0 if no current user
			$data['checkedby'] = $user_id;
			$data['author_id'] = $user_id;

			/* Set metadata of the admin */
			$userdata = get_userdata( $user_id );

			if (is_object($userdata)) {
				if ( isset( $userdata->display_name ) ) {
					$author_name = $userdata->display_name;
				} else {
					$author_name = $userdata->user_login;
				}
				$author_email = $userdata->user_email;
			}
			$data['author_name'] = $author_name;
			$data['author_name'] = gwolle_gb_maybe_encode_emoji( $data['author_name'], 'author_name' );
			$data['author_email'] = $author_email;

			/* Set as Not Spam */
			$data['isspam'] = false;

			/* Do not set as trash */
			$data['istrash'] = false;

			/* Check if the content is filled in, and save accordingly */
			if ( isset($_POST['gwolle_gb_content']) && $_POST['gwolle_gb_content'] != '' ) {
				$data['content'] = $_POST['gwolle_gb_content'];
				$data['content'] = gwolle_gb_maybe_encode_emoji( $data['content'], 'content' );
				$saved = true;
			} else {
				$form_setting = gwolle_gb_get_setting( 'form' );
				if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' && isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
					$gwolle_gb_messages .= '<p>' . __('Entry has no content, even though that is mandatory.', 'gwolle-gb') . '</p>';
					$gwolle_gb_errors = 'error';
				} else {
					$data['content'] = '';
					$saved = true;
				}
			}

			/* Check if the website is set, and save accordingly */
			if ( isset($_POST['gwolle_gb_author_website']) ) {
				if ( $_POST['gwolle_gb_author_website'] != '' ) {
					$data['author_website'] = $_POST['gwolle_gb_author_website'];
				} else {
					$data['author_website'] = home_url();
				}
			}

			/* Check if the author_origin is set, and save accordingly */
			if ( isset($_POST['gwolle_gb_author_origin']) ) {
				if ( $_POST['gwolle_gb_author_origin'] != '' ) {
					$data['author_origin'] = $_POST['gwolle_gb_author_origin'];
					$data['author_origin'] = gwolle_gb_maybe_encode_emoji( $data['author_origin'], 'author_origin' );
				}
			}

			/* Check if the admin_reply is set, and save accordingly */
			if ( isset($_POST['gwolle_gb_admin_reply']) ) {
				if ( $_POST['gwolle_gb_admin_reply'] != '' ) {
					$data['admin_reply'] = gwolle_gb_maybe_encode_emoji( $_POST['gwolle_gb_admin_reply'], 'admin_reply' );
					$data['admin_reply_uid'] = get_current_user_id();
					gwolle_gb_add_log_entry( $entry->get_id(), 'admin-reply-added' );
				}
			}

			/* Check if the book_id is set, and save accordingly */
			if ( isset($_POST['gwolle_gb_book_id']) && is_numeric($_POST['gwolle_gb_book_id']) ) {
				$entry->set_book_id( (int) $_POST['gwolle_gb_book_id'] );
			}

			/* Network Information */
			$set_author_ip = apply_filters( 'gwolle_gb_set_author_ip', true );
			if ( $set_author_ip ) {
				$entry->set_author_ip( $_SERVER['REMOTE_ADDR'] );
				$entry->set_author_host( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
			}

			$result1 = $entry->set_data( $data );
			if ( $saved ) {
				$result2 = $entry->save();
				if ( $result1 && $result2 ) {
					$gwolle_gb_messages .= '<p>' . __('Entry saved.', 'gwolle-gb') . '</p>';
					gwolle_gb_clear_cache( $entry );
					do_action( 'gwolle_gb_save_entry_admin', $entry );
				} else {
					$gwolle_gb_messages .= '<p>' . __('Error happened during saving.', 'gwolle-gb') . '</p>';
					$gwolle_gb_errors = 'error';
				}
			} else {
				$gwolle_gb_messages .= '<p>' . __('Entry was not saved.', 'gwolle-gb') . '</p>';
			}

		}
	}


	/*
	 * Build the Page and the Form
	 */
	?>
	<div class="wrap gwolle_gb">
		<div id="icon-gwolle-gb"><br /></div>
		<h1><?php echo $sectionHeading; ?></h1>

		<?php
		if ( $gwolle_gb_messages ) {
			echo '
				<div id="message" class="updated fade notice is-dismissible ' . $gwolle_gb_errors . ' ">' .
					$gwolle_gb_messages .
				'</div>';
		}
		?>

		<form name="gwolle_gb_editor" id="gwolle_gb_editor" method="POST" action="" accept-charset="UTF-8">
			<input type="hidden" name="gwolle_gb_page" value="editor" />
			<input type="hidden" name="entry_id" value="<?php echo $entry->get_id(); ?>" />

			<?php
			/* Nonce */
			$nonce = wp_create_nonce( 'gwolle_gb_page_editor' );
			echo '<input type="hidden" id="gwolle_gb_wpnonce" name="gwolle_gb_wpnonce" value="' . $nonce . '" />';
			?>

			<div id="poststuff" class="gwolle_gb_editor">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<?php
						add_meta_box('gwolle_gb_editor_postbox_content', __('Guestbook entry','gwolle-gb'), 'gwolle_gb_editor_postbox_content', 'gwolle_gb_editor', 'normal');
						add_meta_box('gwolle_gb_editor_postbox_website', __('Website', 'gwolle-gb'), 'gwolle_gb_editor_postbox_website', 'gwolle_gb_editor', 'normal');
						add_meta_box('gwolle_gb_editor_postbox_author', __('Author', 'gwolle-gb'), 'gwolle_gb_editor_postbox_author', 'gwolle_gb_editor', 'normal');
						add_meta_box('gwolle_gb_editor_postbox_admin_reply', __('Admin Reply', 'gwolle-gb'), 'gwolle_gb_editor_postbox_admin_reply', 'gwolle_gb_editor', 'normal');

						do_meta_boxes( 'gwolle_gb_editor', 'normal', '' );
						?>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<?php
						add_meta_box('gwolle_gb_editor_postbox_icons', __('Visibility', 'gwolle-gb'), 'gwolle_gb_editor_postbox_icons', 'gwolle_gb_editor', 'side');
						add_meta_box('gwolle_gb_editor_postbox_actions', __('Actions', 'gwolle-gb'), 'gwolle_gb_editor_postbox_actions', 'gwolle_gb_editor', 'side');
						add_meta_box('gwolle_gb_editor_postbox_details', __('Details','gwolle-gb'), 'gwolle_gb_editor_postbox_details', 'gwolle_gb_editor', 'side');
						add_meta_box('gwolle_gb_editor_postbox_logs', __('Log','gwolle-gb'), 'gwolle_gb_editor_postbox_logs', 'gwolle_gb_editor', 'side');

						do_meta_boxes( 'gwolle_gb_editor', 'side', '' );
						?>
					</div>
				</div>
			</div>
		</form>
	</div><!-- .wrap -->
	<?php
}


function gwolle_gb_editor_postbox_content() {
	global $entry;
	?>
	<textarea rows="10" name="gwolle_gb_content" id="gwolle_gb_content" class="wp-exclude-emoji" tabindex="1" placeholder="<?php _e('Message', 'gwolle-gb'); ?>"><?php echo gwolle_gb_sanitize_output( $entry->get_content() ); ?></textarea>
	<?php
	if (get_option('gwolle_gb-showLineBreaks', 'false') == 'false') {
		echo '<p>' . sprintf( __('Line breaks will not be visible to the visitors due to your <a href="%s">settings</a>.', 'gwolle-gb'), 'admin.php?page=' . GWOLLE_GB_FOLDER . '/settings.php' ) . '</p>';
	}
	$form_setting = gwolle_gb_get_setting( 'form' );

	if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled']  === 'true' ) {
		wp_enqueue_script( 'markitup', plugins_url('../frontend/markitup/jquery.markitup.js', __FILE__), 'jquery', GWOLLE_GB_VER, false );
		wp_enqueue_script( 'markitup_set', plugins_url('../frontend/markitup/set.js', __FILE__), 'jquery', GWOLLE_GB_VER, false );
		wp_enqueue_style('gwolle_gb_markitup_css', plugins_url('../frontend/markitup/style.css', __FILE__), false, GWOLLE_GB_VER,  'screen');

		$dataToBePassed = array(
			'bold'      => /* translators: MarkItUp menu item */ __('Bold', 'gwolle-gb' ),
			'italic'    => /* translators: MarkItUp menu item */ __('Italic', 'gwolle-gb' ),
			'bullet'    => /* translators: MarkItUp menu item */ __('Bulleted List', 'gwolle-gb' ),
			'numeric'   => /* translators: MarkItUp menu item */ __('Numeric List', 'gwolle-gb' ),
			'picture'   => /* translators: MarkItUp menu item */ __('Picture', 'gwolle-gb' ),
			'source'    => /* translators: MarkItUp menu item */ __('Source', 'gwolle-gb' ),
			'link'      => /* translators: MarkItUp menu item */ __('Link', 'gwolle-gb' ),
			'linktext'  => /* translators: MarkItUp menu item */ __('Your text to link...', 'gwolle-gb' ),
			'clean'     => /* translators: MarkItUp menu item */ __('Clean', 'gwolle-gb' ),
			'emoji'     => /* translators: MarkItUp menu item */ __('Emoji', 'gwolle-gb' )
		);
		wp_localize_script( 'markitup_set', 'gwolle_gb_localize', $dataToBePassed );

		// Emoji symbols
		echo '<div class="gwolle_gb_emoji gwolle_gb_hide">';
		$emoji = gwolle_gb_get_emoji();
		// make it into images for nice colors.
		if ( function_exists('wp_staticize_emoji') ) {
			$emoji = wp_staticize_emoji( $emoji );
		}
		echo $emoji;
		echo '</div>';
	}
}


function gwolle_gb_editor_postbox_website() {
	global $entry;
	?>
	<input type="url" name="gwolle_gb_author_website" tabindex="2" value="<?php echo gwolle_gb_sanitize_output( $entry->get_author_website() ); ?>" id="author_website" placeholder="<?php _e('Website', 'gwolle-gb'); ?>" />
	<p><?php _e("Example: <code>http://www.example.com/</code>", 'gwolle-gb'); ?></p>
	<?php
}


function gwolle_gb_editor_postbox_author() {
	global $entry;
	?>
	<input type="text" name="gwolle_gb_author_origin" tabindex="3" class="wp-exclude-emoji" placeholder="<?php _e('City', 'gwolle-gb'); ?>" value="<?php echo gwolle_gb_sanitize_output( $entry->get_author_origin() ); ?>" id="author_origin" />
	<?php
}


function gwolle_gb_editor_postbox_admin_reply() {
	global $entry;
	$form_setting = gwolle_gb_get_setting( 'form' );
	?>

	<textarea rows="10" name="gwolle_gb_admin_reply" id="gwolle_gb_admin_reply" class="wp-exclude-emoji" tabindex="4" placeholder="<?php _e('Admin Reply', 'gwolle-gb'); ?>"><?php echo gwolle_gb_sanitize_output( $entry->get_admin_reply() ); ?></textarea>

	<?php
	if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled']  === 'true' ) {
		echo '<div class="gwolle_gb_admin_reply_emoji gwolle_gb_hide">';
		// Emoji symbols
		$emoji = gwolle_gb_get_emoji();
		// make it into images for nice colors.
		if ( function_exists('wp_staticize_emoji') ) {
			$emoji = wp_staticize_emoji( $emoji );
		}
		echo $emoji;
		echo '</div>';
	}

	/* Admin Reply Author */
	$admin_reply_name = gwolle_gb_is_moderator( $entry->get_admin_reply_uid() );
	if ( $admin_reply_name ) { ?>
		<p class="gb-admin_reply_uid"><?php
			$admin_reply_header = '<em>' . __('Admin Reply by:', 'gwolle-gb') . ' ' . $admin_reply_name . '</em>';
			echo apply_filters( 'gwolle_gb_admin_reply_header', $admin_reply_header, $entry );
			?>
		</p><?php
	} ?>

	<p>
		<input type="checkbox" name="gwolle_gb_admin_reply_mail_author" id="gwolle_gb_admin_reply_mail_author">
		<label for="gwolle_gb_admin_reply_mail_author">
		<?php _e('Mail the author a notification about this reply.', 'gwolle-gb'); ?>
		</label>
	</p>

	<?php
	if (get_option('gwolle_gb-showLineBreaks', 'false') == 'false') {
		echo '<p>' . sprintf( __('Line breaks will not be visible to the visitors due to your <a href="%s">settings</a>.', 'gwolle-gb'), 'admin.php?page=' . GWOLLE_GB_FOLDER . '/settings.php' ) . '</p>';
	}
}


function gwolle_gb_editor_postbox_icons() {
	global $entry, $class;

	$class = '';
	// Attach 'spam' to class if the entry is spam
	if ( $entry->get_isspam() === 1 ) {
		$class .= ' spam';
	} else {
		$class .= ' nospam';
	}

	// Attach 'trash' to class if the entry is in trash
	if ( $entry->get_istrash() === 1 ) {
		$class .= ' trash';
	} else {
		$class .= ' notrash';
	}

	// Attach 'checked/unchecked' to class
	if ( $entry->get_ischecked() === 1 ) {
		$class .= ' checked';
	} else {
		$class .= ' unchecked';
	}

	// Attach 'visible/invisible' to class
	if ( $entry->get_isspam() === 1 || $entry->get_istrash() === 1 || $entry->get_ischecked() === 0 ) {
		$class .= ' invisible';
	} else {
		$class .= ' visible';
	}

	// Add admin-entry class to an entry from an admin
	$author_id = $entry->get_author_id();
	$is_moderator = gwolle_gb_is_moderator( $author_id );
	if ( $is_moderator ) {
		$class .= ' admin-entry';
	}

	$postid = gwolle_gb_get_postid( (int) $entry->get_book_id() );
	if ( $postid ) {
		$permalink = get_permalink( $postid );
		?>
		<div id="gwolle_gb_frontend">
			<a class="button rbutton button" href="<?php echo $permalink; ?>"><?php esc_attr_e('View Guestbook','gwolle-gb'); ?></a>
		</div>
		<?php
	}

	// Optional Icon column where CSS is being used to show them or not
	if ( get_option('gwolle_gb-showEntryIcons', 'true') === 'true' ) { ?>
		<span class="entry-icons <?php echo $class; ?>">
			<span class="visible-icon" title="<?php _e('Visible', 'gwolle-gb'); ?>"></span>
			<span class="invisible-icon" title="<?php _e('Invisible', 'gwolle-gb'); ?>"></span>
			<span class="spam-icon" title="<?php _e('Spam', 'gwolle-gb'); ?>"></span>
			<span class="trash-icon" title="<?php _e('Trash', 'gwolle-gb'); ?>"></span>
			<?php
			$admin_reply = gwolle_gb_sanitize_output( $entry->get_admin_reply() );
			if ( strlen( trim($admin_reply) ) > 0 ) { ?>
				<span class="admin_reply-icon" title="<?php _e('Admin Replied', 'gwolle-gb'); ?>"></span><?php
			} ?>
			<span class="gwolle_gb_ajax" title="<?php _e('Wait...', 'gwolle-gb'); ?>"></span>
		</span>
		<?php
	}

	if ( $entry->get_id() == 0 ) {
		echo '<h3 class="h3_invisible">' . __('This entry is not yet visible.', 'gwolle-gb') . '</h3>';
	} else {
		if ($entry->get_ischecked() == 1 && $entry->get_isspam() == 0 && $entry->get_istrash() == 0 ) {
			echo '
				<h3 class="h3_visible">' . __('This entry is Visible.', 'gwolle-gb') . '</h3>
				<h3 class="h3_invisible" style="display:none;">' . __('This entry is Not Visible.', 'gwolle-gb') . '</h3>
				';
		} else {
			echo '
				<h3 class="h3_visible" style="display:none;">' . __('This entry is Visible.', 'gwolle-gb') . '</h3>
				<h3 class="h3_invisible">' . __('This entry is Not Visible.', 'gwolle-gb') . '</h3>
				';
		} ?>

		<label for="ischecked" class="selectit">
			<input id="ischecked" name="ischecked" type="checkbox" <?php
				if ($entry->get_ischecked() == '1' || $entry->get_id() == 0) {
					echo 'checked="checked"';
				}
				?> />
			<?php _e('Checked', 'gwolle-gb'); ?>
		</label>

		<br />
		<label for="isspam" class="selectit">
			<input id="isspam" name="isspam" type="checkbox" <?php
				if ($entry->get_isspam() == '1') {
					echo 'checked="checked"';
				}
				?> />
			<?php _e('Spam', 'gwolle-gb'); ?>
		</label>

		<br />
		<label for="istrash" class="selectit">
			<input id="istrash" name="istrash" type="checkbox" <?php
				if ($entry->get_istrash() == '1') {
					echo 'checked="checked"';
				}
				?> />
			<?php _e('Trash', 'gwolle-gb'); ?>
		</label>

		<?php
		$trashclass = '';
		if ( $entry->get_istrash() == '0' ) { $trashclass = 'gwolle_gb_hide'; } ?>
		<br />
		<label for="remove" class="selectit gwolle_gb_remove <?php echo $trashclass; ?>">
			<input id="remove" name="remove" type="checkbox" />
			<?php _e('Remove this entry Permanently.', 'gwolle-gb'); ?>
		</label>
		<?php
	} ?>

	<div id="publishing-action">
		<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="<?php esc_attr_e('Save', 'gwolle-gb'); ?>" />
	</div> <!-- .publishing-action -->
	<div class="clear"></div>
	<?php
}


function gwolle_gb_editor_postbox_actions() {
	global $entry, $class;
	if ( $entry->get_id() > 0 ) {
		echo '
		<p class="gwolle_gb_actions ' . $class . '">
			<span class="gwolle_gb_check">
				<a id="check_' . $entry->get_id() . '" href="#" class="vim-a" title="' . __('Check entry', 'gwolle-gb') . '">' . __('Check', 'gwolle-gb') . '</a>
			</span>
			<span class="gwolle_gb_uncheck">
				<a id="uncheck_' . $entry->get_id() . '" href="#" class="vim-u" title="' . __('Uncheck entry', 'gwolle-gb') . '">' . __('Uncheck', 'gwolle-gb') . '</a>
			</span>
			<span class="gwolle_gb_spam">&nbsp;|&nbsp;
				<a id="spam_' . $entry->get_id() . '" href="#" class="vim-s vim-destructive" title="' . __('Mark entry as spam.', 'gwolle-gb') . '">' . __('Spam', 'gwolle-gb') . '</a>
			</span>
			<span class="gwolle_gb_unspam">&nbsp;|&nbsp;
				<a id="unspam_' . $entry->get_id() . '" href="#" class="vim-a" title="' . __('Mark entry as not-spam.', 'gwolle-gb') . '">' . __('Not spam', 'gwolle-gb') . '</a>
			</span>
			<span class="gwolle_gb_trash">&nbsp;|&nbsp;
				<a id="trash_' . $entry->get_id() . '" href="#" class="vim-d vim-destructive" title="' . __('Move entry to trash.', 'gwolle-gb') . '">' . __('Trash', 'gwolle-gb') . '</a>
			</span>
			<span class="gwolle_gb_untrash">&nbsp;|&nbsp;
				<a id="untrash_' . $entry->get_id() . '" href="#" class="vim-d" title="' . __('Recover entry from trash.', 'gwolle-gb') . '">' . __('Untrash', 'gwolle-gb') . '</a>
			</span><br />
			<span class="gwolle_gb_ajax">
				<a id="ajax_' . $entry->get_id() . '" href="#" class="ajax vim-d vim-destructive" title="' . __('Please wait...', 'gwolle-gb') . '">' . __('Wait...', 'gwolle-gb') . '</a>
			</span><br />
		</p>
		';
	}
}


function gwolle_gb_editor_postbox_details() {
	global $entry;
	?>
	<p>
		<?php _e('Author', 'gwolle-gb'); ?>: <span><?php
			if ( $entry->get_author_name() ) {
				echo gwolle_gb_sanitize_output( $entry->get_author_name() );
			} else {
				echo '<i>(' . __('Unknown', 'gwolle-gb') . ')</i>';
			} ?>
		</span><br />
		<?php _e('Email', 'gwolle-gb'); ?>: <span><?php
			if (strlen(str_replace( ' ', '', $entry->get_author_email() )) > 0) {
				echo gwolle_gb_sanitize_output( $entry->get_author_email() );
			} else {
				echo '<i>(' . __('Unknown', 'gwolle-gb') . ')</i>';
			} ?>
		</span><br />
		<?php _e('Date and time', 'gwolle-gb'); ?>: <span><?php
			if ( $entry->get_datetime() > 0 ) {
				echo date_i18n( get_option('date_format'), $entry->get_datetime() ) . ', ';
				echo date_i18n( get_option('time_format'), $entry->get_datetime() );
			} else {
				echo '(' . __('Not yet', 'gwolle-gb') . ')';
			} ?>
		</span><br />
		<?php _e("Author's IP-address", 'gwolle-gb'); ?>: <span><?php
			if (strlen( $entry->get_author_ip() ) > 0) {
				echo '<a href="http://www.db.ripe.net/whois?form_type=simple&searchtext=' . $entry->get_author_ip() . '"
						title="' . __('Whois search for this IP', 'gwolle-gb') . '" target="_blank">
							' . $entry->get_author_ip() . '
						</a>';
			} else {
				echo '<i>(' . __('Unknown', 'gwolle-gb') . ')</i>';
			} ?>
		</span><br />
		<?php _e('Host', 'gwolle-gb'); ?>: <span><?php
			if (strlen( $entry->get_author_host() ) > 0) {
				echo $entry->get_author_host();
			} else {
				echo '<i>(' . __('Unknown', 'gwolle-gb') . ')</i>';
			} ?>
		</span><br />
		<?php _e('Book', 'gwolle-gb'); ?>: <span><?php echo $entry->get_book_id(); ?>
		</span><br />
		<span class="gwolle_gb_edit_meta">
			<a href="#" title="<?php _e('Edit metadata', 'gwolle-gb'); ?>"><?php _e('Edit', 'gwolle-gb'); ?></a>
		</span>
	</p>

	<div class="gwolle_gb_edit_meta_inputs">
		<label for="gwolle_gb_author_name"><?php _e('Author', 'gwolle-gb'); ?>: </label><br />
		<input type="text" name="gwolle_gb_author_name" size="24" value="<?php echo gwolle_gb_sanitize_output( $entry->get_author_name() ); ?>" id="gwolle_gb_author_name" class="wp-exclude-emoji" /><br />

		<span><?php _e('Date and time', 'gwolle-gb'); ?>: </span><br />
		<div class="gwolle_gb_date"><?php
			gwolle_gb_touch_time( $entry ); ?>
		</div>

		<label for="gwolle_gb_book_id"><?php _e('Book ID', 'gwolle-gb'); ?>: </label><br />
		<input type="text" name="gwolle_gb_book_id" size="4" value="<?php echo (int) $entry->get_book_id(); ?>" id="gwolle_gb_book_id" />
	</div>

	<?php
}

function gwolle_gb_editor_postbox_logs() {
	global $entry;
	?>
	<ul>
		<?php
		if ($entry->get_datetime() > 0) {
			echo '<li>';
			echo date_i18n( get_option('date_format'), $entry->get_datetime() ) . ', ';
			echo date_i18n( get_option('time_format'), $entry->get_datetime() );
			echo ': ' . /* translators: Log on Editor */ __('Written', 'gwolle-gb') . '</li>';

			$log_entries = gwolle_gb_get_log_entries( $entry->get_id() );
			if ( is_array($log_entries) && !empty($log_entries) ) {
				foreach ($log_entries as $log_entry) {
					echo '<li class="log_id_' . $log_entry['id'] . '">' . $log_entry['msg_html'] . '</li>';
				}
			}
		} else {
			echo '<li>(' . __('No log yet.', 'gwolle-gb') . ')</li>';
		}
		?>
	</ul>
	<?php
}
