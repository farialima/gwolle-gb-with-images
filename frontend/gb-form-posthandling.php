<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}

            define('ATT_MAX',   10);

        function getMimeTypes()
        {
            return array(
                'JPG' => array(
                    'image/jpeg',
                    'image/jpg',
                    'image/jp_',
                    'application/jpg',
                    'application/x-jpg',
                    'image/pjpeg',
                    'image/pipeg',
                    'image/vnd.swiftview-jpeg',
                    'image/x-xbitmap'),
                'GIF' => array(
                    'image/gif',
                    'image/x-xbitmap',
                    'image/gi_'),
                'PNG' => array(
                    'image/png',
                    'application/png',
                    'application/x-png'),
/*
                'DOCX'=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'RAR'=> 'application/x-rar-compressed',
                'ZIP' => array(
                    'application/zip',
                    'application/x-zip',
                    'application/x-zip-compressed',
                    'application/x-compress',
                    'application/x-compressed',
                    'multipart/x-zip'),
                'DOC' => array(
                    'application/msword',
                    'application/doc',
                    'application/text',
                    'application/vnd.msword',
                    'application/vnd.ms-word',
                    'application/winword',
                    'application/word',
                    'application/x-msw6',
                    'application/x-msword'),
                'PDF' => array(
                    'application/pdf',
                    'application/x-pdf',
                    'application/acrobat',
                    'applications/vnd.pdf',
                    'text/pdf',
                    'text/x-pdf'),
                'PPT' => array(
                    'application/vnd.ms-powerpoint',
                    'application/mspowerpoint',
                    'application/ms-powerpoint',
                    'application/mspowerpnt',
                    'application/vnd-mspowerpoint',
                    'application/powerpoint',
                    'application/x-powerpoint',
                    'application/x-m'),
                'PPTX'=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'PPS' => 'application/vnd.ms-powerpoint',
                'PPSX'=> 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'ODT' => array(
                    'application/vnd.oasis.opendocument.text',
                    'application/x-vnd.oasis.opendocument.text'),
                'XLS' => array(
                    'application/vnd.ms-excel',
                    'application/msexcel',
                    'application/x-msexcel',
                    'application/x-ms-excel',
                    'application/vnd.ms-excel',
                    'application/x-excel',
                    'application/x-dos_ms_excel',
                    'application/xls'),
                'XLSX'=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'MP3' => array(
                    'audio/mpeg',
                    'audio/x-mpeg',
                    'audio/mp3',
                    'audio/x-mp3',
                    'audio/mpeg3',
                    'audio/x-mpeg3',
                    'audio/mpg',
                    'audio/x-mpg',
                    'audio/x-mpegaudio'),
                'M4A' => 'audio/mp4a-latm',
                'OGG' => array(
                    'audio/ogg',
                    'application/ogg'),
                'WAV' => array(
                    'audio/wav',
                    'audio/x-wav',
                    'audio/wave',
                    'audio/x-pn-wav'),
                'WMA' => 'audio/x-ms-wma',
                'MP4' => array(
                    'video/mp4v-es',
                    'audio/mp4'),
                'M4V' => array(
                    'video/mp4',
                    'video/x-m4v'),
                'MOV' => array(
                    'video/quicktime',
                    'video/x-quicktime',
                    'image/mov',
                    'audio/aiff',
                    'audio/x-midi',
                    'audio/x-wav',
                    'video/avi'),
                'WMV' => 'video/x-ms-wmv',
                'AVI' => array(
                    'video/avi',
                    'video/msvideo',
                    'video/x-msvideo',
                    'image/avi',
                    'video/xmpg2',
                    'application/x-troff-msvideo',
                    'audio/aiff',
                    'audio/avi'),
                'MPG' => array(
                    'video/avi',
                    'video/mpeg',
                    'video/mpg',
                    'video/x-mpg',
                    'video/mpeg2',
                    'application/x-pn-mpg',
                    'video/x-mpeg',
                    'video/x-mpeg2a',
                    'audio/mpeg',
                    'audio/x-mpeg',
                    'image/mpg'),
                'OGV' => 'video/ogg',
                '3GP' => array(
                    'audio/3gpp',
                    'video/3gpp'),
                '3G2' => array(
                    'video/3gpp2',
                    'audio/3gpp2'),
                'FLV' => 'video/x-flv',
                'WEBM'=> 'video/webm',
*/
            );
        }


        function getAllowedFileExtensions()
        {
            $return = array();
            $pluginFileTypes = getMimeTypes();
            foreach($pluginFileTypes as $key => $value){
               $return[] = strtolower($key);
            }
            return $return;
        }


        function checkAttachment($data)
        {
          file_put_contents( "/tmp/checkin.txt", $_FILES['gwolle_gb_attachment']['size']);
            if($_FILES['gwolle_gb_attachment']['size'] > 0 && $_FILES['gwolle_gb_attachment']['error'] == 0){
          file_put_contents( "/tmp/here.txt", 'x');

                $fileInfo = pathinfo($_FILES['gwolle_gb_attachment']['name']);
                $fileExtension = strtolower($fileInfo['extension']);

                if(function_exists('finfo_file')){
                    $fileType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['gwolle_gb_attachment']['tmp_name']);
                } elseif(function_exists('mime_content_type')) {
                    $fileType = mime_content_type($_FILES['gwolle_gb_attachment']['tmp_name']);
                } else {
                    $fileType = $_FILES['gwolle_gb_attachment']['type'];
                }

                // Is: allowed mime type / file extension, and size? extension making lowercase, just to make sure
                if (!in_array($fileType, getMimeTypes()) || !in_array(strtolower($fileExtension), getAllowedFileExtensions()) || $_FILES['gwolle_gb_attachment']['size'] > (ATT_MAX * 1048576)) { // file size from admin
                    return "Fichier trop gros, ou pas un format accept&equote;";
                }

            } elseif($_FILES['gwolle_gb_attachment']['error'] == 2) {
                return '<strong>ERROR:</strong> The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
            } elseif($_FILES['gwolle_gb_attachment']['error'] == 3) {
                return '<strong>ERROR:</strong> The uploaded file was only partially uploaded. Please try again later.';
            } elseif($_FILES['gwolle_gb_attachment']['error'] == 6) {
                return '<strong>ERROR:</strong> Missing a temporary folder.';
            } elseif($_FILES['gwolle_gb_attachment']['error'] == 7) {
                return '<strong>ERROR:</strong> Failed to write file to disk.';
            } elseif($_FILES['gwolle_gb_attachment']['error'] == 7) {
                return '<strong>ERROR:</strong> A PHP extension stopped the file upload.';
            }
        }

        /**
         * Inserts file attachment from your comment to wordpress
         * media library, assigned to post.
         *
         * @param $fileHandler
         * @param $postId
         * @return mixed
         */

        function insertAttachment($fileHandler, $postId)
        {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');
            return media_handle_upload($fileHandler, $postId);
        }


        /**
         * Save attachment to db, with all sizes etc. Assigned
         * to post, or not.
         *
         * @param $commentId
         */

        function saveAttachment($entry_id)
        {
//        echo "SAVING!";
            if($_FILES['gwolle_gb_attachment']['size'] > 0){
          file_put_contents( "/tmp/a.txt", 'saving2');
                $bindId = 0;
                $attachId = insertAttachment('gwolle_gb_attachment', $bindId);
		global $wpdb;

			$sql = "
				UPDATE $wpdb->gwolle_gb_entries
				SET
					attachment_id = %d
				WHERE
					id = %d
				";
			$result = $wpdb->query(
					$wpdb->prepare( $sql, 
                                        array($attachId, $entry_id))
				);

                unset($_FILES);
            }
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

                $attachment_error = checkAttachment($form_setting);
                if  ($attachment_error) {
                   gwolle_gb_add_message( '<p class="error_fields"><strong>'.$attachment_error.'</strong></p>',true, 'content');
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

                        saveAttachment($entry->get_id());

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
