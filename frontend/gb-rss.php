<?php


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/* Add the feed. */
function gwolle_gb_rss_init(){
	add_feed('gwolle_gb', 'gwolle_gb_rss');
}
add_action('init', 'gwolle_gb_rss_init');


/* Set the correct HTTP header for Content-type. */
function gwolle_gb_rss_content_type( $content_type, $type ) {
	if ( 'gwolle_gb' === $type ) {
		return feed_content_type( 'rss2' );
	}
	return $content_type;
}
add_filter( 'feed_content_type', 'gwolle_gb_rss_content_type', 10, 2 );


/* Show the XML Feed */
function gwolle_gb_rss() {

	// Only show the first page of entries.
	$entriesPerPage = (int) apply_filters( 'gwolle_gb_rss_nr_entries', 20 );

	/* Get the entries for the RSS Feed */
	$entries = gwolle_gb_get_entries(
		array(
			'offset'      => 0,
			'num_entries' => $entriesPerPage,
			'checked'     => 'checked',
			'trash'       => 'notrash',
			'spam'        => 'nospam'
		)
	);

	/* Get the time of the last entry, else of the last edited post */
	if ( is_array($entries) && !empty($entries) ) {
		$lastbuild = gmdate( 'D, d M Y H:i:s', $entries[0]->get_datetime() );
	} else {
		$lastbuild = mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false);
	}

	$postid = gwolle_gb_get_postid();
	if ( $postid ) {
		$permalink = get_bloginfo('url') . '?p=' . $postid;
	} else {
		$permalink = get_bloginfo('url');
	}

	/* Get the Language setting */
	$WPLANG = get_option('WPLANG', false);
	if ( ! $WPLANG ) {
		$WPLANG = WPLANG;
	}
	if ( ! $WPLANG ) {
		$WPLANG = 'en-us';
	}
	$WPLANG = str_replace( '_', '-', $WPLANG );
	$WPLANG = strtolower( $WPLANG );

	/* Build the XML content */
	header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
	echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';
	?>

	<rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:wfw="http://wellformedweb.org/CommentAPI/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:atom="http://www.w3.org/2005/Atom"
		xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
		xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
		<?php do_action('rss2_ns'); ?>>

		<channel>
			<title><?php bloginfo_rss('name'); echo " - " . __('Guestbook Feed', 'gwolle-gb'); ?></title>
			<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
			<link><?php echo $permalink; ?></link>
			<description><?php bloginfo_rss('description'); echo " - " . __('Guestbook Feed', 'gwolle-gb'); ?></description>
			<lastBuildDate><?php echo $lastbuild; ?></lastBuildDate>
			<language><?php echo $WPLANG; ?></language>
			<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
			<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
			<?php do_action('rss2_head'); ?>

			<?php
			if ( is_array($entries) && !empty($entries) ) {
				foreach ( $entries as $entry ) { ?>
					<item>
						<title>
							<?php _e('Guestbook Entry by', 'gwolle-gb'); echo " " . trim( $entry->get_author_name() ) . " (" . trim(date_i18n( get_option('date_format'), $entry->get_datetime() )) . " " . trim(date_i18n( get_option('time_format'), $entry->get_datetime() )) . ")"; ?>
						</title>
						<link><?php
							$permalink_entry = add_query_arg( 'entry_id', $entry->get_id(), $permalink );
							$permalink_entry = htmlspecialchars($permalink_entry, ENT_COMPAT, 'UTF-8');
							echo $permalink_entry; ?>
						</link>
						<pubDate><?php echo gmdate( 'D, d M Y H:i:s', $entry->get_datetime() ); ?></pubDate>
						<dc:creator><?php echo trim( $entry->get_author_name() ); ?></dc:creator>
						<guid isPermaLink="false"><?php echo $permalink; ?></guid>
						<description><![CDATA[<?php echo wp_trim_words( $entry->get_content(), 12, '...' ) ?>]]></description>
						<content:encoded><![CDATA[<?php echo wp_trim_words( $entry->get_content(), 25, '...' ) ?>]]></content:encoded>
						<?php rss_enclosure(); ?>
						<?php do_action('rss2_item'); ?>
					</item>
					<?php
				}
			} ?>
		</channel>
	</rss>
	<?php
}
