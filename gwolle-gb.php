<?php
/*
Plugin Name: Gwolle Guestbook
Plugin URI: http://zenoweb.nl
Description: Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle Guestbook and have a real guestbook.
Version: 2.1.2
Author: Marcel Pol
Author URI: http://zenoweb.nl
License: GPLv2 or later
Text Domain: gwolle-gb
Domain Path: /lang/
*/

/*
	Copyright 2009         Wolfgang Timme  (email: gwolle@wolfgangtimme.de)
	Copyright 2014 - 2016  Marcel Pol      (email: marcel@timelord.nl)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Plugin Version
define('GWOLLE_GB_VER', '2.1.2');


/*
 * Todo List:
 *
 * - Entries Admin page, make columns sortable, add order parameters to get* functions.
 * - On Page editor, have a postbox with link to the guestbook admin entries.
 * - When saving new date in editor, change date in postbox too.
 * - Admin Reply form on frontend with AJAX.
 * - Add Searchbox to admin entries page.
 * - BBcode: have sublists work.
 * - On admin pages, have separate functions for $_POST. (editor, entries)
 * - Localize admin ajax script.
 * - Convert db to INNO.
 */


/*
 * Definitions
 */
define('GWOLLE_GB_FOLDER', plugin_basename(dirname(__FILE__)));
define('GWOLLE_GB_DIR', WP_PLUGIN_DIR . '/' . GWOLLE_GB_FOLDER);


global $wpdb;

// Declare database table names
$wpdb->gwolle_gb_entries = $wpdb->prefix . 'gwolle_gb_entries';
$wpdb->gwolle_gb_log = $wpdb->prefix . 'gwolle_gb_log';


// Classes
include_once( GWOLLE_GB_DIR . '/functions/gb-class-entry.php' );

// Functions for the frontend
include_once( GWOLLE_GB_DIR . '/frontend/gb-ajax-infinite_scroll.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-ajax-captcha.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-form.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-form-ajax.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-form-posthandling.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-shortcodes.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-pagination.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-read.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-rss.php' );
include_once( GWOLLE_GB_DIR . '/frontend/gb-widget.php' );

// Functions and pages for the backend
include_once( GWOLLE_GB_DIR . '/admin/gb-ajax-management.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-dashboard-widget.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-page-editor.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-page-entries.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-page-export.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-page-gwolle-gb.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-page-import.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-page-settings.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-pagination.php' );
include_once( GWOLLE_GB_DIR . '/admin/gb-upgrade.php' );

// Tabs for gb-page-settings.php
include_once( GWOLLE_GB_DIR . '/admin/tabs/gb-formtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/gb-readingtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/gb-admintab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/gb-antispamtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/gb-emailtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/gb-debugtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/gb-uninstalltab.php' );

// General Functions
include_once( GWOLLE_GB_DIR . '/functions/gb-akismet.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-bbcode_emoji.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-compatibility.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-debug.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-formatting.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-get_entries.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-get_entry_count.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-log.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-mail.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-messages.php' );
include_once( GWOLLE_GB_DIR . '/functions/gb-misc.php' );

// General Hooks
include_once( GWOLLE_GB_DIR . '/gwolle-gb-hooks.php' );


/*
 * Trigger an install/upgrade function when the plugin is activated.
 */
function gwolle_gb_activation( $networkwide ) {
	global $wpdb;

	$current_version = get_option( 'gwolle_gb_version' );

	if ( function_exists('is_multisite') && is_multisite() ) {
		$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach ($blogids as $blog_id) {
			switch_to_blog($blog_id);
			if ( $current_version == false ) {
				gwolle_gb_install();
			} elseif ($current_version != GWOLLE_GB_VER) {
				gwolle_gb_upgrade();
			}
			restore_current_blog();
		}
	} else {
		if ( $current_version == false ) {
			gwolle_gb_install();
		} elseif ($current_version != GWOLLE_GB_VER) {
			gwolle_gb_upgrade();
		}
	}
}
register_activation_hook(__FILE__, 'gwolle_gb_activation');
