<?php
/*
 * Shows the overview screen with the widget-like windows.
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/* Show the page */
function gwolle_gb_welcome() {

	if ( function_exists('current_user_can') && ! current_user_can('moderate_comments') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	}

	/* Post Handling: Save notification setting */
	if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) {
		gwolle_gb_welcome_post();
	}

	gwolle_gb_admin_enqueue();

	add_meta_box('gwolle_gb_right_now', __('Welcome to the Guestbook!','gwolle-gb'), 'gwolle_gb_overview', 'gwolle_gb_welcome', 'normal');
	add_meta_box('gwolle_gb_visibility', __('Visibility', 'gwolle-gb'), 'gwolle_gb_overview_visibility', 'gwolle_gb_welcome', 'normal');
	add_meta_box('gwolle_gb_notification', __('E-mail Notifications', 'gwolle-gb'), 'gwolle_gb_overview_notification', 'gwolle_gb_welcome', 'normal');
	add_meta_box('gwolle_gb_thanks', __('Third Party','gwolle-gb'), 'gwolle_gb_overview_thanks', 'gwolle_gb_welcome', 'normal');

	add_meta_box('gwolle_gb_help', __('Help', 'gwolle-gb'), 'gwolle_gb_overview_help', 'gwolle_gb_welcome', 'right');
	add_meta_box('gwolle_gb_support', __('Support and Translations', 'gwolle-gb'), 'gwolle_gb_overview_support', 'gwolle_gb_welcome', 'right');
	add_meta_box('gwolle_gb_donate', __('Review and Donate', 'gwolle-gb'), 'gwolle_gb_overview_donate', 'gwolle_gb_welcome', 'right');

	?>
	<div class="wrap gwolle_gb">
		<div id="icon-gwolle-gb"><br /></div>
		<h1><?php _e('Gwolle Guestbook', 'gwolle-gb'); ?></h1>

		<?php
		$gwolle_gb_messages = gwolle_gb_get_messages();
		$gwolle_gb_errors   = gwolle_gb_get_errors();
		$messageclass = '';
		if ( $gwolle_gb_errors ) {
			$messageclass = 'error';
		}

		if ( $gwolle_gb_messages ) {
			echo '
				<div id="message" class="updated fade notice is-dismissible ' . $messageclass . ' ">' .
					$gwolle_gb_messages .
				'</div>';
		} ?>

		<div id="dashboard-widgets-wrap" class="gwolle_gb_welcome">
			<div id="dashboard-widgets" class="metabox-holder">
				<div class="postbox-container">
					<?php do_meta_boxes( 'gwolle_gb_welcome', 'normal', '' ); ?>
				</div>
				<div class="postbox-container">
					<?php do_meta_boxes( 'gwolle_gb_welcome', 'right', '' ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}



function gwolle_gb_overview(){

	// Calculate the number of entries
	$count = Array();
	$count['checked'] = gwolle_gb_get_entry_count(array(
			'checked' => 'checked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		));
	$count['unchecked'] = gwolle_gb_get_entry_count(array(
			'checked' => 'unchecked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		));
	$count['spam']    = gwolle_gb_get_entry_count(array( 'spam'  => 'spam'  ));
	$count['trash']   = gwolle_gb_get_entry_count(array( 'trash' => 'trash' ));
	$count['all']     = gwolle_gb_get_entry_count(array( 'all'   => 'all'   ));
	?>

	<div class="table table_content gwolle_gb">
		<h3><?php _e('Overview','gwolle-gb'); ?></h3>

		<table>
			<tbody>
				<tr class="first">
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php">
							<?php echo $count['all']; ?>
						</a>
					</td>

					<td class="t" style="color:#0000f0;">
						<?php echo _n( 'Entry total', 'Entries total', $count['all'], 'gwolle-gb' ); ?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=checked">
						<?php echo $count['checked']; ?>
					</a></td>
					<td class="t" style="color:#008000;">
						<?php echo _n( 'Unlocked entry', 'Unlocked entries', $count['checked'], 'gwolle-gb' ); ?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=unchecked">
						<?php echo $count['unchecked']; ?>
					</a></td>
					<td class="t" style="color:#ff6f00;">
						<?php echo _n( 'New entry', 'New entries', $count['unchecked'], 'gwolle-gb' ); ?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=spam">
						<?php echo $count['spam']; ?>
					</a></td>
					<td class="t" style="color:#FF0000;">
						<?php echo _n( 'Spam entry', 'Spam entries', $count['spam'], 'gwolle-gb' ); ?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=trash">
						<?php echo $count['trash']; ?>
					</a></td>
					<td class="t" style="color:#FF0000;">
						<?php echo _n( 'Trashed entry', 'Trashed entries', $count['trash'], 'gwolle-gb' ); ?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

			</tbody>
		</table>
	</div><!-- .table -->
	<div class="versions">
		<p>
			<?php
			$postid = gwolle_gb_get_postid();
			if ( $postid ) {
				$permalink = get_permalink( $postid );
				?>
				<a class="button rbutton button button-primary" href="<?php echo $permalink; ?>"><?php esc_attr_e('View Guestbook','gwolle-gb'); ?></a>
				<?php
			} ?>

			<a class="button rbutton button button-primary" href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php"><?php esc_attr_e('Write admin entry','gwolle-gb'); ?></a>
		</p>
		<p>
			<?php
			global $wp_rewrite;
			$permalinks = $wp_rewrite->permalink_structure;
			if ( $permalinks ) {
				?>
				<a href="<?php bloginfo('url'); ?>/feed/gwolle_gb" /><?php _e('Subscribe to RSS Feed', 'gwolle-gb'); ?></a>
				<?php
			} else {
				?>
				<a href="<?php bloginfo('url'); ?>/?feed=gwolle_gb" /><?php _e('Subscribe to RSS Feed', 'gwolle-gb'); ?></a>
				<?php
			}

			?>
		</p>
	</div>
<?php }


function gwolle_gb_overview_notification() {

	// Check if function mail() exists. If not, display a hint to the user.
	if ( ! function_exists('mail') ) {
		echo '<p class="setting-description">' .
			__('Sorry, but the function <code>mail()</code> required to notify you by mail is not enabled in your PHP configuration. You might want to install a WordPress plugin that uses SMTP instead of <code>mail()</code>. Or you can contact your hosting provider to change this.','gwolle-gb')
			. '</p>';
	}
	$current_user_id = get_current_user_id();;
	$currentUserNotification = false;
	$user_ids = get_option('gwolle_gb-notifyByMail' );
	if ( strlen($user_ids) > 0 ) {
		$user_ids = explode( ",", $user_ids );
		if ( is_array($user_ids) && !empty($user_ids) ) {
			if ( in_array( $current_user_id, $user_ids ) ) {
				$currentUserNotification = true;
			}
		}
	} ?>
	<p>
		<form name="gwolle_gb_welcome" method="post" action="">
			<?php
			settings_fields( 'gwolle_gb_options' );
			do_settings_sections( 'gwolle_gb_options' );

			/* Nonce */
			$nonce = wp_create_nonce( 'gwolle_gb_page_gwolle' );
			echo '<input type="hidden" id="gwolle_gb_wpnonce" name="gwolle_gb_wpnonce" value="' . $nonce . '" />';
			?>
			<input name="notify_by_mail" type="checkbox" id="notify_by_mail" <?php
				if ( $currentUserNotification ) {
					echo 'checked="checked"';
				} ?> >
			<label for="notify_by_mail" class="setting-description"><?php _e('Send me an e-mail when a new entry has been posted.', 'gwolle-gb'); ?></label>
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save setting', 'gwolle-gb'); ?>" />
			</p>
		</form>
	</p>
	<div>
		<?php _e('The following users have subscribed to this service:', 'gwolle-gb');

		if ( is_array($user_ids) && ! empty($user_ids) ) {
			echo '<ul>';
			foreach ( $user_ids as $user_id ) {
				$user_info = get_userdata($user_id);
				if ($user_info === FALSE) {
					// Invalid $user_id
					continue;
				}
				echo '<li>';
				if ( $user_info->ID == get_current_user_id() ) {
					echo '<strong>' . __('You', 'gwolle-gb') . '</strong>';
				} else {
					echo $user_info->first_name . ' ' . $user_info->last_name;
				}
				echo ' (' . $user_info->user_email . ')';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo '<br /><i>(' . __('No subscriber yet', 'gwolle-gb') . ')</i>';
		}
		?>
	</div>
	<?php
}


function gwolle_gb_overview_thanks() {
	echo '<h3>
	' . __('This plugin uses the following scripts and services:', 'gwolle-gb') . '</h3>
	<ul class="ul-disc">
		<li><a href="http://akismet.com/tos/" target="_blank">' . __( 'Akismet', 'gwolle-gb' ) . '</a></li>
		<li><a href="http://markitup.jaysalvat.com/" target="_blank">' . __( 'MarkItUp', 'gwolle-gb' ) . '</a></li>
		<li><a href="https://wordpress.org/plugins/really-simple-captcha/" target="_blank">' . __( 'Really Simple CAPTCHA plugin', 'gwolle-gb' ) . '</a></li>
		<li><a href="http://supersimpleslider.com/" target="_blank">' . __( 'Super Simple Slider', 'gwolle-gb' ) . '</a></li>
	</ul>';
}


/* Metaboxes on the right */
function gwolle_gb_overview_help() {
	echo '<h3>
	' . __('This is how you can get your guestbook displayed on your website:', 'gwolle-gb') . '</h3>
	<ul class="ul-disc">
		<li>' . __('Create a new page.', 'gwolle-gb') . '</li>
		<li>' . __('Choose a title and set &quot;[gwolle_gb]&quot; (without the quotes) as the content.', 'gwolle-gb') . '</li>
		<li>' . __('Shortcode:', 'gwolle-gb') . ' <input type="text" name="gwolle_gb_shortcode" size="10" value="[gwolle_gb]" id="gwolle_gb_shortcode" /></li>
	</ul>';
}


function gwolle_gb_overview_visibility() {
	echo '<h3>
	'.__('These entries will be visible for your visitors:', 'gwolle-gb').'</h3>
	<ul class="ul-disc">
		<li>'.__('Marked as Checked.', 'gwolle-gb').'</li>
		<li>'.__('Not marked as Spam.', 'gwolle-gb').'</li>
		<li>'.__('Not marked as Trash.','gwolle-gb').'</li>
	</ul>';
}


function gwolle_gb_overview_support() {
	?>
	<h3><?php _e('Support.', 'gwolle-gb'); ?></h3>
	<p><?php _e('If you have a problem or a feature request, please post it on the', 'gwolle-gb'); ?>
		<a href="https://wordpress.org/support/plugin/gwolle-gb" target="_blank" title="<?php esc_attr_e('the support forum at wordpress.org.', 'gwolle-gb'); ?>">
			<?php _e('support forum at wordpress.org', 'gwolle-gb'); ?></a>.
		<?php _e('I will do my best to respond as soon as possible.', 'gwolle-gb'); ?><br />
		<?php _e('If you send me an email, I will not reply. Please use the support forum.', 'gwolle-gb'); ?>
	</p>

	<h3><?php _e('Translations.', 'gwolle-gb'); ?></h3>
	<p><?php _e('Translations can be added very easily through', 'gwolle-gb'); ?>
		<a href="https://translate.wordpress.org/projects/wp-plugins/gwolle-gb" target="_blank" title="<?php esc_attr_e('GlotPress', 'gwolle-gb'); ?>">
			<?php _e('GlotPress', 'gwolle-gb'); ?></a>.
		<?php _e("You can start translating strings there for your locale. They need to be validated though, so if there's no validator yet, and you want to apply for being validator (PTE), please post it on the", 'gwolle-gb'); ?>
		<a href="https://wordpress.org/support/plugin/gwolle-gb" target="_blank" title="<?php esc_attr_e('the support forum at wordpress.org.', 'gwolle-gb'); ?>">
			<?php _e('support forum', 'gwolle-gb'); ?></a>.
		<?php _e('I will make a request on', 'gwolle-gb'); ?>
		<a href="https://make.wordpress.org/polyglots/" target="_blank" title="<?php esc_attr_e('make/polyglots', 'gwolle-gb'); ?>"><?php _e('make/polyglots', 'gwolle-gb'); ?></a>
		<?php _e('to have you added as validator for this plugin/locale.', 'gwolle-gb'); ?>
	</p>
	<?php
}


function gwolle_gb_overview_donate() {
	?>
	<h3><?php _e('Review this plugin.', 'gwolle-gb'); ?></h3>
	<p><?php _e('If this plugin has any value to you, then please leave a review at', 'gwolle-gb'); ?>
		<a href="https://wordpress.org/support/view/plugin-reviews/gwolle-gb?rate=5#postform" target="_blank" title="<?php esc_attr_e('The plugin page at wordpress.org.', 'gwolle-gb'); ?>">
			<?php _e('the plugin page at wordpress.org', 'gwolle-gb'); ?></a>.
	</p>

	<h3><?php _e('Donate to the maintainer.', 'gwolle-gb'); ?></h3>
	<p><?php _e('This plugin is being maintained by Marcel Pol from', 'gwolle-gb'); ?>
		<a href="http://zenoweb.nl" target="_blank" title="ZenoWeb">ZenoWeb</a>.</br />
		<?php _e('If you want to donate to the maintainer of the plugin, you can donate through PayPal.', 'gwolle-gb'); ?><br />
		<?php _e('Donate through', 'gwolle-gb'); ?> <a href="https://www.paypal.com" target="_blank" title="<?php esc_attr_e('Donate to the maintainer.', 'gwolle-gb'); ?>"><?php _e('PayPal', 'gwolle-gb'); ?></a>
		<?php _e('to', 'gwolle-gb'); ?> marcel@timelord.nl.
	</p>
	<?php
}


function gwolle_gb_welcome_post() {

	/* Check Nonce */
	$verified = false;
	if ( isset($_POST['gwolle_gb_wpnonce']) ) {
		$verified = wp_verify_nonce( $_POST['gwolle_gb_wpnonce'], 'gwolle_gb_page_gwolle' );
	}
	if ( $verified == false ) {
		gwolle_gb_add_message( '<p>' . __('Nonce check failed. Please try again.', 'gwolle-gb') . '</p>', true, false);
		return;
	}

	/* E-mail notification option. */
	if ( isset($_POST['notify_by_mail']) && $_POST['notify_by_mail'] == 'on' ) {
		// Turn the notification ON for the current user.
		$user_id = get_current_user_id();
		$user_ids = Array();

		$user_ids_old = get_option('gwolle_gb-notifyByMail' );
		if ( strlen($user_ids_old) > 0 ) {
			$user_ids_old = explode( ",", $user_ids_old );
			foreach ( $user_ids_old as $user_id_old ) {
				if ( $user_id_old == $user_id ) {
					continue; // will be added again below the loop
				}
				if ( is_numeric($user_id_old) ) {
					$user_ids[] = $user_id_old;
				}
			}
		}
		$user_ids[] = $user_id; // Really add it.

		$user_ids = implode(",", $user_ids);
		update_option('gwolle_gb-notifyByMail', $user_ids);

		gwolle_gb_add_message( '<p>' . __('Changes saved.', 'gwolle-gb') . '</p>', false, false);
	} elseif ( ! isset($_POST['notify_by_mail'] ) ) {
		// Turn the notification OFF for the current user
		$user_id = get_current_user_id();
		$user_ids = Array();

		$user_ids_old = get_option('gwolle_gb-notifyByMail' );
		if ( strlen($user_ids_old) > 0 ) {
			$user_ids_old = explode( ",", $user_ids_old );
			foreach ( $user_ids_old as $user_id_old ) {
				if ( $user_id_old == $user_id ) {
					continue;
				}
				if ( is_numeric($user_id_old) ) {
					$user_ids[] = $user_id_old;
				}
			}
		}

		$user_ids = implode(",", $user_ids);
		update_option('gwolle_gb-notifyByMail', $user_ids);
		gwolle_gb_add_message( '<p>' . __('Changes saved.', 'gwolle-gb') . '</p>', false, false);
	}
}
