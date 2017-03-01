<?php

/*
 * WordPress Actions and Filters.
 * See the Plugin API in the Codex:
 * http://codex.wordpress.org/Plugin_API
 */


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Add a menu in the WordPress backend.
 * Load JavaSCript and CSS for Admin.
 */
function gwolle_gb_adminmenu() {
	/*
	 * How to add new menu-entries:
	 * add_menu_page( $page_title, $menu_title, $access_level, $file, $function = '', $icon_url = '' )
	 */

	// Counter
	$count_unchecked = gwolle_gb_get_entry_count(
		array(
			'checked' => 'unchecked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		)
	);

	// Main navigation entry
	// Admin page: admin/welcome.php
	add_menu_page(
		__('Guestbook', 'gwolle-gb'), /* translators: Menu entry */
		__('Guestbook', 'gwolle-gb') . "<span class='update-plugins count-" . $count_unchecked . "'><span class='theme-count'>" . $count_unchecked . "</span></span>",
		'moderate_comments',
		GWOLLE_GB_FOLDER . '/gwolle-gb.php',
		'gwolle_gb_welcome',
		'dashicons-admin-comments'
	);

	// Admin page: admin/entries.php
	add_submenu_page(
		GWOLLE_GB_FOLDER . '/gwolle-gb.php',
		__('Entries', 'gwolle-gb'), /* translators: Menu entry */
		__('Entries', 'gwolle-gb') . "<span class='update-plugins count-" . $count_unchecked . "'><span class='theme-count'>" . $count_unchecked . "</span></span>",
		'moderate_comments',
		GWOLLE_GB_FOLDER . '/entries.php',
		'gwolle_gb_page_entries'
	);

	// Admin page: admin/editor.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Entry editor', 'gwolle-gb'), /* translators: Menu entry */ __('Add/Edit entry', 'gwolle-gb'), 'moderate_comments', GWOLLE_GB_FOLDER . '/editor.php', 'gwolle_gb_page_editor' );

	// Admin page: admin/settings.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Settings', 'gwolle-gb'), /* translators: Menu entry */ __('Settings', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/settings.php', 'gwolle_gb_page_settings' );

	// Admin page: admin/import.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Import', 'gwolle-gb'), /* translators: Menu entry */ __('Import', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/import.php', 'gwolle_gb_page_import' );

	// Admin page: admin/export.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Export', 'gwolle-gb'), /* translators: Menu entry */ __('Export', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/export.php', 'gwolle_gb_page_export' );
}
add_action('admin_menu', 'gwolle_gb_adminmenu');


/*
 * Load CSS for admin.
 */
function gwolle_gb_admin_enqueue_style() {
	wp_enqueue_style( 'gwolle-gb-admin-css', plugins_url( '/admin/css/gwolle-gb-admin.css', __FILE__ ), false, GWOLLE_GB_VER, 'all' );
}
add_action( 'admin_enqueue_scripts', 'gwolle_gb_admin_enqueue_style' );


/*
 * Load JavaScript for admin.
 * It's called directly on the adminpages, it's not being used as a hook.
 */
function gwolle_gb_admin_enqueue() {
	wp_enqueue_script( 'gwolle-gb-admin-js', plugins_url( '/admin/js/gwolle-gb-admin.js', __FILE__ ), 'jquery', GWOLLE_GB_VER, true );
}
//add_action( 'admin_enqueue_scripts', 'gwolle_gb_admin_enqueue' );


/*
 * Add Settings link to the main plugin page
 */
function gwolle_gb_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/gwolle-gb.php' ) ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=gwolle-gb/settings.php' ) . '">'.__( 'Settings', 'gwolle-gb' ).'</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'gwolle_gb_links', 10, 2 );


/*
 * Handle the $_POST for the Frontend on a new entry.
 * Use this action, since $post is populated and we can use get_the_ID().
 */
function gwolle_gb_handle_post() {
	if ( !is_admin() ) {
		// Frontend Handling of $_POST, only one form
		if ( isset($_POST['gwolle_gb_function']) && $_POST['gwolle_gb_function'] == 'add_entry' ) {
			gwolle_gb_frontend_posthandling();
		}
	}
}
add_action('wp', 'gwolle_gb_handle_post');


/*
 * Register Settings
 */
function gwolle_gb_register_settings() {
	register_setting( 'gwolle_gb_options', 'gwolle_gb-admin_style',       'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-adminMailContent',  'strval' ); // empty by default
	register_setting( 'gwolle_gb_options', 'gwolle_gb-akismet-active',    'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-antispam-question', 'strval' ); // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-antispam-answer',   'strval' ); // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-authorMailContent', 'strval' ); // empty by default
	register_setting( 'gwolle_gb_options', 'gwolle_gb-entries_per_page',  'intval' ); // 20
	register_setting( 'gwolle_gb_options', 'gwolle_gb-entriesPerPage',    'intval' ); // 20
	register_setting( 'gwolle_gb_options', 'gwolle_gb-excerpt_length',    'intval' ); // 0
	register_setting( 'gwolle_gb_options', 'gwolle_gb-form',              'strval' ); // serialized Array, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-form_ajax',         'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-header',            'strval' ); // string, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-honeypot',          'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-labels_float',      'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-linkAuthorWebsite', 'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-longtext',          'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail-from',         'strval' ); // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail_admin_replyContent', 'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail_author',       'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-moderate-entries',  'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-navigation',        'intval' ); // 0 or 1, default is 0
	register_setting( 'gwolle_gb_options', 'gwolle_gb-nonce',             'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-notifyByMail',      'strval' ); // array, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-notice',            'strval' ); // string, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-paginate_all',      'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-read',              'strval' ); // serialized Array, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-require_login',     'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showEntryIcons',    'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showLineBreaks',    'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showSmilies',       'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb_version',           'strval' ); // string, mind the underscore
}
add_action( 'admin_init', 'gwolle_gb_register_settings' );


/*
 * Check if we need to install or upgrade.
 * Supports MultiSite since 1.5.2.
 */
function gwolle_gb_init() {

	global $wpdb;

	$current_version = get_option( 'gwolle_gb_version' );

	if ($current_version && version_compare($current_version, GWOLLE_GB_VER, '<')) {
		// Upgrade, if this version differs from what the database says.

		if ( function_exists('is_multisite') && is_multisite() ) {
			$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				gwolle_gb_upgrade();
				restore_current_blog();
			}
		} else {
			gwolle_gb_upgrade();
		}
	}
}
add_action('admin_init', 'gwolle_gb_init');


/*
 * Install database tables for new blog on MultiSite.
 * since 1.5.2.
 */
function gwolle_gb_activate_new_site($blog_id) {
	switch_to_blog($blog_id);
	gwolle_gb_install();
	restore_current_blog();
}
add_action( 'wpmu_new_blog', 'gwolle_gb_activate_new_site' );


/*
 * Support uninstall for MultiSite through a filter.
 * Take Note: This will do an uninstall on all sites.
 * since 2.1.0.
 */
function gwolle_gb_multisite_uninstall() {
	global $wpdb;

	if ( is_admin() ) {
		if ( function_exists('is_multisite') && is_multisite() ) {
			$do_uninstall = apply_filters( 'gwolle_gb_multisite_uninstall', false );
			if ( $do_uninstall ) {
				$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					gwolle_gb_uninstall();
					restore_current_blog();
				}
				// Avoid database errors and PHP notices, don't run these actions anymore.
				remove_action('admin_menu', 'gwolle_gb_adminmenu', 10, 1);
				remove_action('wp_dashboard_setup', 'gwolle_gb_dashboard_setup', 10, 1);
				remove_action( 'admin_bar_menu', 'gwolle_gb_admin_bar_menu', 61, 1 );
			}
		}
	}
}
add_action('wp_loaded', 'gwolle_gb_multisite_uninstall');


/*
 * Register styles and scripts.
 * Enqueue them in the frontend function only when we need them.
 */
function gwolle_gb_register() {

	// Always load jQuery, it's just easier this way.
	wp_enqueue_script('jquery');

	// Register script for frontend. Load it later.
	wp_register_script( 'gwolle_gb_frontend_js', plugins_url('frontend/js/gwolle-gb-frontend.js', __FILE__), 'jquery', GWOLLE_GB_VER, true );
	$dataToBePassed = array(
		'ajax_url'     => admin_url('admin-ajax.php'),
		'load_message' => /* translators: Infinite Scroll */ __('Loading more...', 'gwolle-gb'),
		'end_message'  => /* translators: Infinite Scroll */ __('No more entries.', 'gwolle-gb')
	);
	wp_localize_script( 'gwolle_gb_frontend_js', 'gwolle_gb_frontend_script', $dataToBePassed );


	// Register style for frontend. Load it later.
	wp_register_style('gwolle_gb_frontend_css', plugins_url('frontend/css/gwolle-gb-frontend.css', __FILE__), false, GWOLLE_GB_VER,  'screen');
}
add_action('wp_enqueue_scripts', 'gwolle_gb_register');


/*
 * Load Language files for frontend and backend.
 */
function gwolle_gb_load_lang() {
	load_plugin_textdomain( 'gwolle-gb', false, GWOLLE_GB_FOLDER . '/lang' );
}
add_action('plugins_loaded', 'gwolle_gb_load_lang');


/*
 * Add the RSS link to the html head.
 * There is no post_content yet, but we do have get_the_ID().
 */
function gwolle_gb_rss_head() {
	if ( is_singular() && function_exists('has_shortcode') ) {
		$post = get_post( get_the_ID() );
		if ( has_shortcode( $post->post_content, 'gwolle_gb' ) || has_shortcode( $post->post_content, 'gwolle_gb_read' ) ) {

			// Remove standard RSS links.
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );

			// And add our own RSS link.
			global $wp_rewrite;
			$permalinks = $wp_rewrite->permalink_structure;
			if ( $permalinks ) {
				?>
				<link rel="alternate" type="application/rss+xml" title="<?php esc_attr_e("Guestbook Feed", 'gwolle-gb'); ?>" href="<?php bloginfo('url'); ?>/feed/gwolle_gb" />
				<?php
			} else {
				?>
				<link rel="alternate" type="application/rss+xml" title="<?php esc_attr_e("Guestbook Feed", 'gwolle-gb'); ?>" href="<?php bloginfo('url'); ?>/?feed=gwolle_gb" />
				<?php
			}
		}
	}
}
add_action('wp_head', 'gwolle_gb_rss_head', 1);


/*
 * Set meta_keys so we can find the post with the shortcode back.
 */
function gwolle_gb_save_post($id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	if ( function_exists('has_shortcode') ) {
		$post = get_post( $id );

		if ( has_shortcode( $post->post_content, 'gwolle_gb' ) || has_shortcode( $post->post_content, 'gwolle_gb_read' ) ) {
			// Set a meta_key so we can find the post with the shortcode back.
			$meta_value = get_post_meta( $id, 'gwolle_gb_read', true );
			if ( $meta_value != 'true' ) {
				update_post_meta( $id, 'gwolle_gb_read', 'true' );
			}
		} else {
			// Remove the meta_key in case it is set.
			delete_post_meta( $id, 'gwolle_gb_read' );
		}

		if ( has_shortcode( $post->post_content, 'gwolle_gb' ) || has_shortcode( $post->post_content, 'gwolle_gb_read' ) || has_shortcode( $post->post_content, 'gwolle_gb_write' ) ) {
			// Nothing to do
		} else {
			delete_post_meta( $id, 'gwolle_gb_book_id' );
		}
	}
}
add_action('save_post', 'gwolle_gb_save_post');


/*
 * Add number of unchecked entries to admin bar, if > 0.
 */
function gwolle_gb_admin_bar_menu( $wp_admin_bar ) {
	if ( !current_user_can('moderate_comments') )
		return;

	// Counter
	$count_unchecked = gwolle_gb_get_entry_count(
		array(
			'checked' => 'unchecked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		)
	);

	$count_unchecked_i18n = number_format_i18n( $count_unchecked );
	$awaiting_text = esc_attr( sprintf( /* translators: Toolbar */ _n(
			'%s guestbook entry awaiting moderation',
			'%s guestbook entries awaiting moderation',
			$count_unchecked_i18n,
			'gwolle-gb' ),
		$count_unchecked_i18n ) );

	if ( $count_unchecked > 0 ) {
		$icon  = '<span class="ab-icon"></span>';
		$title = '<span id="ab-unchecked-entries" class="ab-label awaiting-mod pending-count count-' . $count_unchecked . '" aria-hidden="true">' . $count_unchecked_i18n . '</span>';
		$title .= '<span class="screen-reader-text">' . $awaiting_text . '</span>';

		$wp_admin_bar->add_menu( array(
			'id'    => 'gwolle-gb',
			'title' => $icon . $title,
			'href'  => admin_url('admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&amp;show=unchecked'),
		) );
	}
}
add_action( 'admin_bar_menu', 'gwolle_gb_admin_bar_menu', 61 );
