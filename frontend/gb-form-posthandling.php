<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Save new entries to the database, when valid.
 * Handle $_POST and check and save entry.
 *
 * global vars used:
 * $gwolle_gb_formdata: the data that was submitted, and will be used to fill the form for resubmit.
 *
 * returns entry->ID on saving, else false.
 */

function gwolle_gb_frontend_posthandling() {

	if ( isset($_POST['gwolle_gb_function']) && $_POST['gwolle_gb_function'] == 'add_entry' ) {

		// Option to allow only logged-in users to post. Don't show the form if not logged-in.
		if ( !is_user_logged_in() && get_option('gwolle_gb-require_login', 'false') == 'true' ) {
			gwolle_gb_add_message( '<p class="require_login"><strong>' . __('Submitting a new guestbook entry is only allowed for logged-in users.', 'gwolle-gb') . '</strong></p>', true, false);
			return false;
		}


		/*
		 * Collect data from the Form
		 */
		$gwolle_gb_formdata = array(); // used to set the data in the entry
		$form_setting = gwolle_gb_get_setting( 'form' );

		/* Name */
		if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_author_name'])) {
				$author_name = trim($_POST['gwolle_gb_author_name']);
				$author_name = gwolle_gb_maybe_encode_emoji( $author_name, 'author_name' );
				$gwolle_gb_formdata['author_name'] = $author_name;
				gwolle_gb_add_formdata( 'author_name', $author_name );
				if ( $author_name == "" ) {
					if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) {
						gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('Your name is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'author_name'); // mandatory
					}
				}
			} else {
				if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('Your name is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'author_name'); // mandatory
				}
			}
		}

		/* City / Origin */
		if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_author_origin'])) {
				$author_origin = trim($_POST['gwolle_gb_author_origin']);
				$author_origin = gwolle_gb_maybe_encode_emoji( $author_origin, 'author_origin' );
				$gwolle_gb_formdata['author_origin'] = $author_origin;
				gwolle_gb_add_formdata( 'author_origin', $author_origin );
				if ( $author_origin == "" ) {
					if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) {
						gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('Your origin is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'author_origin'); // mandatory
					}
				}
			} else {
				if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('Your origin is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'author_origin'); // mandatory
				}
			}
		}

		/* Email */
		if ( isset($form_setting['form_email_enabled']) && $form_setting['form_email_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_author_email'])) {
				$author_email = trim($_POST['gwolle_gb_author_email']);
				$gwolle_gb_formdata['author_email'] = $author_email;
				gwolle_gb_add_formdata( 'author_email', $author_email );
				if ( filter_var( $author_email, FILTER_VALIDATE_EMAIL ) ) {
					// Valid Email address.
				} else if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('Your e-mail address is not filled in correctly, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'author_email'); // mandatory
				}
			} else {
				if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('Your e-mail address is not filled in correctly, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'author_email'); // mandatory
				}
			}
		} else {
			if (isset($_POST['gwolle_gb_author_email'])) {
				$author_email = trim($_POST['gwolle_gb_author_email']);
				$gwolle_gb_formdata['author_email'] = $author_email;
				gwolle_gb_add_formdata( 'author_email', $author_email );
			}
		}

		/* Website / Homepage */
		if ( isset($form_setting['form_homepage_enabled']) && $form_setting['form_homepage_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_author_website'])) {
				$author_website = trim($_POST['gwolle_gb_author_website']);
				$gwolle_gb_formdata['author_website'] = $author_website;
				gwolle_gb_add_formdata( 'author_website', $author_website );
				$pattern = '/^http/';
				if ( !preg_match($pattern, $author_website, $matches) ) {
					$author_website = "http://" . $author_website;
				}
				if ( filter_var( $author_website, FILTER_VALIDATE_URL ) ) {
					// Valid Website URL.
				} else if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('Your website is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'author_website'); // mandatory
				}
			} else {
				if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('Your website is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'author_website'); // mandatory
				}
			}
		}

		/* Message */
		if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_content'])) {
				$content = trim($_POST['gwolle_gb_content']);
				if ( $content == "" ) {
					if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
						gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('There is no message, even though it is mandatory.', 'gwolle-gb') . '</strong></p>',true, 'content'); // mandatory
					}
				} else {
					$content = gwolle_gb_maybe_encode_emoji( $content, 'content' );
					$gwolle_gb_formdata['content'] = $content;
					gwolle_gb_add_formdata( 'content', $content );
				}
			} else {
				if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('There is no message, even though it is mandatory.', 'gwolle-gb') . '</strong></p>',true, 'content'); // mandatory
				}
			}
		}

		/* Custom Anti-Spam */
		if ( isset($form_setting['form_antispam_enabled']) && $form_setting['form_antispam_enabled']  === 'true' ) {
			$antispam_question = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-question') );
			$antispam_answer   = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-answer') );

			if ( isset($antispam_question) && strlen($antispam_question) > 0 && isset($antispam_answer) && strlen($antispam_answer) > 0 ) {
				if ( isset($_POST['gwolle_gb_antispam_answer']) && trim($_POST['gwolle_gb_antispam_answer']) == trim($antispam_answer) ) {
					//echo "You got it!";
				} else {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('The anti-spam question was not answered correctly, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'antispam_answer'); // mandatory
				}
			}
			if ( isset($_POST['gwolle_gb_antispam_answer']) ) {
				$antispam = trim($_POST['gwolle_gb_antispam_answer']);
				$gwolle_gb_formdata['antispam_answer'] = $antispam;
				gwolle_gb_add_formdata( 'antispam_answer', $antispam );
			}
		}

		/* CAPTCHA */
		if ( isset($form_setting['form_recaptcha_enabled']) && $form_setting['form_recaptcha_enabled']  === 'true' ) {
			if ( class_exists('ReallySimpleCaptcha') ) {
				$gwolle_gb_captcha = new ReallySimpleCaptcha();
				// This variable holds the CAPTCHA image prefix, which corresponds to the correct answer
				$gwolle_gb_captcha_prefix = $_POST['gwolle_gb_captcha_prefix'];
				// This variable holds the CAPTCHA response, entered by the user
				$gwolle_gb_captcha_code = $_POST['gwolle_gb_captcha_code'];
				// Validate the CAPTCHA response
				$gwolle_gb_captcha_correct = $gwolle_gb_captcha->check( $gwolle_gb_captcha_prefix, $gwolle_gb_captcha_code );
				// If CAPTCHA validation fails (incorrect value entered in CAPTCHA field) mark comment as spam.
				if ( true != $gwolle_gb_captcha_correct ) {
					gwolle_gb_add_message( '<p class="error_fields"><strong>' . __('The CAPTCHA was not filled in correctly, even though it is mandatory.', 'gwolle-gb') . '</strong></p>', true, 'captcha_code' );
					//gwolle_gb_add_message( '<p style="display_:none"><strong>' . $gwolle_gb_captcha_correct . '</strong></p>' );
				} else {
					// verified!
					//gwolle_gb_add_message( '<p class="error_fields"><strong>Verified.</strong></p>', false, false );
				}
				// Clean up the tmp directory.
				$gwolle_gb_captcha->cleanup();
			}
		}


		/* If there are errors, stop here and return false */
		$gwolle_gb_error_fields = gwolle_gb_get_error_fields();
		if ( is_array( $gwolle_gb_error_fields ) && !empty( $gwolle_gb_error_fields ) ) {
			return false; // no need to check and save
		}


		/* New Instance of gwolle_gb_entry. */
		$entry = new gwolle_gb_entry();


		/* Set the data in the instance */
		$set_data = $entry->set_data( $gwolle_gb_formdata );
		if ( ! $set_data ) {
			// Data is not set in the Instance, something happened
			gwolle_gb_add_message( '<p class="set_data"><strong>' . __('There were errors submitting your guestbook entry.', 'gwolle-gb') . '</strong></p>', true, false );
			return false;
		}


		/* Check for spam and set accordingly */
		$marked_by_akismet = false;
		$isspam = gwolle_gb_akismet( $entry, 'comment-check' );
		if ( $isspam ) {
			// Returned true, so considered spam
			$entry->set_isspam(true);
			$marked_by_akismet = true;
			// Is it wise to make them any wiser? Probably not...
			// gwolle_gb_add_message( '<p><strong>' . __('Your guestbook entry is probably spam. A moderator will decide upon it.', 'gwolle-gb') . '</strong></p>', true, false );
		}


		/* Check for honeypot functionality */
		$marked_by_honeypot = false;
		if ( isset($_POST["gwolle_gb_subject"]) && strlen($_POST["gwolle_gb_subject"]) > 0 ) {
			// Input field was filled in, so considered spam
			$entry->set_isspam(true);
			$marked_by_honeypot = true;
		}


		/* Check Nonce */
		$marked_by_nonce = false;
		if (get_option( 'gwolle_gb-nonce', 'true') == 'true') {
			$verified = wp_verify_nonce( $_REQUEST['gwolle_gb_wpnonce'], 'gwolle_gb_add_entry' );
			if ( $verified == false ) {
				// Nonce is invalid, so considered spam
				$entry->set_isspam(true);
				$marked_by_nonce = true;
			}
		}


		/* If Moderation is off, set it to "ischecked" */
		$user_id = get_current_user_id(); // Returns 0 if no current user.
		if ( get_option('gwolle_gb-moderate-entries', 'true') == 'true' ) {
			// Moderation, only set to checked for moderators.
			if ( gwolle_gb_is_moderator($user_id) ) {
				$entry->set_ischecked( true );
			} else {
				$entry->set_ischecked( false );
			}
		} else {
			// No moderation, set to checked.
			$entry->set_ischecked( true );
		}


		/* Scan for long and abusive text. */
		$marked_by_longtext = false;
		if ( get_option( 'gwolle_gb-longtext', 'true') == 'true' ) {
			// Check for abusive content (too long words). Set it to unchecked, so manual moderation is needed.
			$maxlength = 100;
			$words = explode( " ", $entry->get_content() );
			foreach ( $words as $word ) {
				$pattern = '/^href=http/';
				if ( preg_match($pattern, $word, $matches) ) {
					continue;
				}
				$pattern = '/^href=ftp/';
				if ( preg_match($pattern, $word, $matches) ) {
					continue;
				}
				$pattern = '/^\[img\]http/';
				if ( preg_match($pattern, $word, $matches) ) {
					continue;
				}
				if ( strlen($word) > $maxlength ) {
					$entry->set_ischecked( false );
					$marked_by_longtext = true;
					break;
				}
			}
			$maxlength = 60;
			$words = explode( " ", $entry->get_author_name() );
			foreach ( $words as $word ) {
				if ( strlen($word) > $maxlength ) {
					$entry->set_ischecked( false );
					$marked_by_longtext = true;
					break;
				}
			}
		}


		/* Check for logged in user, and set the userid as author_id, just in case someone is also admin, or gets promoted some day */
		$entry->set_author_id( $user_id );


		/*
		 * Network Information
		 */
		$set_author_ip = apply_filters( 'gwolle_gb_set_author_ip', true );
		if ( $set_author_ip ) {
			$entry->set_author_ip( $_SERVER['REMOTE_ADDR'] );
			$entry->set_author_host( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
		}


		/*
		 * Book ID
		 */
		if ( isset( $_POST['gwolle_gb_book_id'] ) ) {
			$book_id = (int) $_POST['gwolle_gb_book_id'];
			gwolle_gb_add_formdata( 'book_id', $book_id );
		}
		if ( $book_id < 1 ) {
			$book_id = 1;
			gwolle_gb_add_formdata( 'book_id', $book_id );
		}
		$entry->set_book_id( $book_id );


		/*
		 * Check for double post using email field and content.
		 * Only if content is mandatory.
		 */
		if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
			$entries = gwolle_gb_get_entries(array(
					'email'   => $entry->get_author_email(),
					'book_id' => $entry->get_book_id()
				));
			if ( is_array( $entries ) && !empty( $entries ) ) {
				foreach ( $entries as $entry_email ) {
					if ( $entry_email->get_content() == $entry->get_content() ) {
						// Match is double entry
						gwolle_gb_add_message( '<p class="double_post"><strong>' . __('Double post: An entry with the data you entered has already been saved.', 'gwolle-gb') . '</strong></p>', true, 'content' );
						return false;
					}
				}
			}
		}


		/*
		 * Save the Entry
		 */
		$save = $entry->save();

		//if ( WP_DEBUG ) { echo "save: "; var_dump($save); }


		if ( $save ) {
			// We have been saved to the Database.

			gwolle_gb_add_message( '<p class="entry_saved">' . __('Thank you for your entry.','gwolle-gb') . '</p>', false, false );
			if ( $entry->get_ischecked() == 0 || $entry->get_isspam() == 1 ) {
				gwolle_gb_add_message( '<p>' . __('We will review it and unlock it in a short while.','gwolle-gb') . '</p>', false, false );
			}


			/*
			 * No Log for the Entry needed, it has a default post date in the Entry itself.
			 * Only log when something specific happened:
			 */
			if ( $marked_by_akismet ) {
				gwolle_gb_add_log_entry( $entry->get_id(), 'marked-by-akismet' );
			}
			if ( $marked_by_honeypot ) {
				gwolle_gb_add_log_entry( $entry->get_id(), 'marked-by-honeypot' );
			}
			if ( $marked_by_nonce ) {
				gwolle_gb_add_log_entry( $entry->get_id(), 'marked-by-nonce' );
			}
			if ( $marked_by_longtext ) {
				gwolle_gb_add_log_entry( $entry->get_id(), 'marked-by-longtext' );
			}


			/*
			 * Hooks gwolle_gb_clear_cache(), gwolle_gb_mail_moderators() and gwolle_gb_mail_author().
			 */
			do_action( 'gwolle_gb_save_entry_frontend', $entry );

			return $entry->get_id();

		} else {
			// We have not been saved to the Database.

			gwolle_gb_add_message( '<p class="entry_notsaved">' . __('Sorry, something went wrong with saving your entry. Please contact a site admin.','gwolle-gb') . '</p>', true, false );

			return false;

		}
	}
}
