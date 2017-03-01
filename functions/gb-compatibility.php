<?php

/*
 * Compatibility hooks for plugins, etc.
 */


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Clear the cache of the most common Cache plugins.
 */
function gwolle_gb_clear_cache( $entry = false ) {

	/* Default WordPress */
	wp_cache_flush();

	/* Cachify */
	if ( class_exists('Cachify') ) {
		$cachify = new Cachify();
		if ( method_exists($cachify, 'flush_total_cache') ) {
			$cachify->flush_total_cache(true);
		}
	}

	/* W3 Total Cache */
	if ( function_exists('w3tc_pgcache_flush') ) {
		w3tc_pgcache_flush();
	}

	/* WP Fastest Cache */
	if ( class_exists('WpFastestCache') ) {
		$WpFastestCache = new WpFastestCache();
		if ( method_exists($WpFastestCache, 'deleteCache') ) {
			$WpFastestCache->deleteCache();
		}
	}

	/* WP Super Cache */
	if ( function_exists('wp_cache_clear_cache') ) {
		$GLOBALS["super_cache_enabled"] = 1;
		wp_cache_clear_cache();
	}

}
add_action( 'gwolle_gb_save_entry_admin', 'gwolle_gb_clear_cache' );
add_action( 'gwolle_gb_save_entry_frontend', 'gwolle_gb_clear_cache' );


/*
 * Support Shortcode UI (since WP 4.6).
 */
function gwolle_gb_shortcode_ui() {
	if ( function_exists( 'shortcode_ui_register_for_shortcode' ) ) {
		if ( isset($_GET['post']) ) {
			$postid = (int) $_GET['post'];
			$description = esc_html( sprintf( __( 'Book ID for when using multiple guestbooks. Default is 1. Your current post ID is %d.', 'gwolle-gb' ), $postid ) );
		} else {
			$description = esc_html__( 'Book ID for when using multiple guestbooks. Default is 1.', 'gwolle-gb' );
		}

		$ui_args = array(
			'label'         => esc_html__( 'Gwolle Guestbook', 'gwolle-gb' ),
			'listItemImage' => 'dashicons-comments',
			'attrs'         => array(
				array(
					'label'       => esc_html__( 'Gwolle Guestbook', 'gwolle-gb' ),
					'attr'        => 'book_id',
					'type'        => 'number',
					'description' => $description,
				),
				),
			);
		shortcode_ui_register_for_shortcode( 'gwolle_gb', $ui_args );
	}
}
add_action( 'init', 'gwolle_gb_shortcode_ui' );
