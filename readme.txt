=== Gwolle Guestbook ===
Contributors: Gwolle, mpol
Tags: guestbook, guest book, comments, feedback, antispam, review, gastenboek, livre d'or, Gästebuch, libro de visitas, livro de visitas
Requires at least: 3.7
Tested up to: 4.7
Stable tag: 2.1.2
License: GPLv2 or later

Gwolle Guestbook is the WordPress guestbook you've just been looking for. Beautiful and easy.


== Description ==

Gwolle Guestbook is the WordPress guestbook you've just been looking for. Beautiful and easy.
Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate
a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle Guestbook and
have a real guestbook.


Current features include:

* Easy to use guestbook frontend with a simple form for visitors of your website.
* List of guestbook entries at the frontend with pagination or infinite scroll.
* Widget to display an excerpt of your last or your best entries.
* Simple and clean admin interface that integrates seamlessly into WordPress admin.
* Dashboard Widget to easily manage the latest entries from your Admin Dashboard.
* Easy Import from other guestbooks into Gwolle Guestbook.
* Notification by mail when a new entry has been posted.
* Moderation, so that you can check an entry before it is visible in your guestbook (optional).
* 5 anti-spam features, like Honeypot, Nonce, Akismet, Custom Quiz Question and CAPTCHA.
* Simple Form Builder to select which form-fields you want to use.
* Simple Entry Builder with the parts of each entry that you want to show.
* Multiple guestbooks are possible.
* MultiSite is supported.
* Localization. Own languages can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/gwolle-gb).
* Admins can add a reply to each entry.
* A log for each entry, so that you know which member of the staff released and edited a guestbook-entry to the public and when.
* IP-address and host-logging with link to WHOIS query site.
* RSS Feed.
* BBcode, Emoji and Smiley integration (optional).
* Easy uninstall routine for complete removal of all database changes.

... and all that integrated in the stylish WordPress look.

= Import / Export =

You may have another guestbook installed. That's great, because Gwolle Guestbook enables you to import entries easily.
The importer does not delete any of your data, so you can go back to your previous setup without loss of data, if you want to.
Trying Gwolle Guestbook is as easy as 1-2-3.

Import is supported from:

* DMSGuestbook.
* WordPress comments from a specific post, page or just all comments.
* Gwolle Guestbook itself, with Export supported as well (CSV-file).

= Support =

If you have a problem or a feature request, please post it on the plugin's support forum on [wordpress.org](https://wordpress.org/support/plugin/gwolle-gb). I will do my best to respond as soon as possible.

If you send me an email, I will not reply. Please use the support forum.

= Translations =

Translations can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/gwolle-gb).
You can start translating strings there for your locale. They need to be validated though, so if there's no validator yet,
and you want to apply for being validator (PTE), please post it on the support forum. I will make a request on make/polyglots to
have you added as validator for this plugin/locale.

= Demo =

Check out the demo at [http://demo.zenoweb.nl](http://demo.zenoweb.nl/wordpress-plugins/gwolle-gb/)

== Installation ==

= Installation =

* Install the plugin through the admin page "Plugins".
* Alternatively, unpack and upload the contents of the zipfile to your '/wp-content/plugins/' directory.
* Activate the plugin through the 'Plugins' menu in WordPress.
* Place '[gwolle_gb]' in a page. That's it.

As an alternative for the shortcode, you can use the function `show_gwolle_gb();` to show the guestbook in your templates.
It couldn't be easier.

= Updating from an old version =

With version 1.0 there have been some changes:

* Gwolle Guestbook uses the Shortcode API now. Make sure your Guestbook page uses '[gwolle_gb]' instead of the old one.
* The entries that are visible to visitors have changed. Make sure to check if you have everything visible that you want and nothing more.
* CSS has changed somewhat. If you have custom CSS, you want to check if it still applies.

= License =

The plugin itself is released under the GNU General Public License. A copy of this license can be found at the license homepage or
in the gwolle-gb.php file at the top.

= Hooks: Actions and Filters =

There are many hooks available in this plugin. Documentation is included in the zip file in /docs/actions and /docs/filters. Examples are included. If you have a need for a hook, please request this in the support forum.

= Add an entry with PHP code =

It is not that hard to add an entry in PHP code.

	<?php
		$entry = new gwolle_gb_entry();

		// Set the data in the instance, returns true
		$set_data = $entry->set_data( $args );

		// Save entry, returns the id of the entry
		$save = $entry->save();
	?>

The Array $args can have the following key/values:

* id, int with the id, leave empty for a new entry.
* author_name, string with the name of the autor.
* author_id, id with the WordPress user ID of the author.
* author_email, string with the email address of the author.
* author_origin, string with the city of origin of the author.
* author_website, string with the website of the author.
* author_ip, string with the ipaddress of the author.
* author_host, string with the hostname of that ip.
* content, string with content of the message.
* datetime, timestamp of the entry.
* ischecked, bool if it is checked by a moderator.
* checkedby, int with the WordPress ID of that moderator.
* istrash, bool if it is in trash or not.
* isspam, bool if it is spam or not.
* admin_reply, string with content of the admin reply message.
* admin_reply_uid, id with the WordPress user ID of the author of the admin_reply.
* book_id, int with the Book ID of that entry, default is 1.


= Format for importing through CSV-file =

The importer expects a certain format of the CSV-file. If you need to import from a custom solution, your CSV needs to conform.
The header needs to look like this:

	<?php
	array(
		'id',
		'author_name',
		'author_email',
		'author_origin',
		'author_website',
		'author_ip',
		'author_host',
		'content',
		'datetime',
		'isspam',
		'ischecked',
		'istrash',
		'admin_reply'
	)
	?>

The next lines are made up of the content.

Date needs to be a UNIX timestamp. For manually creating a timestamp, look at
the [timestamp generator](http://www.timestampgenerator.com/).

It expects quotes around each field, and when no quotes are used the import process can break when having quotes or comma's inside the content of the entry.

With version 1.4.1 and older, the field datetime was called date.

You could make a test-entry, export that, and look to see what the importer expects from the CSV. There is also an example CSV file included in the zipfile of the plugin under '/docs/import_example/'.

Make sure you use UNIX line-endings. Any decent text-editor can transform a textdocument to UNIX line-endings.


== Frequently Asked Questions ==

= How do I get people to post messages in my guestbook? =

You could start by writing the first entry yourself, and invite people to leave a message.

= Which entries are visible on the Frontend? =

Starting with version 1.0, the following entries are listed on the Frontend:

* Checked
* Not marked as Spam
* Not in the Trash

Before that, in 0.9.7, all the 'checked' entries were visible.

= I have a lot of unchecked entries. What do I do? =

* For the entries that you consider spam, but were not automatically marked as spam, you can manually mark them as spam, and they will not be visible anymore.
* For entries that are not spam, but you still don't want them visible, you can move them to trash.
* The entries that you want visible, set them to checked.

= I want to translate this plugin =

Translations can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/gwolle-gb).
You can start translating strings there for your locale. They need to be validated though, so if there's no validator yet,
and you want to apply for being validator (PTE), please post it on the support forum. I will make a request on make/polyglots to
have you added as validator for this plugin/locale.

= What about Spam? =

By default this plugin uses a Honeypot feature and a Nonce. If spambots try to post guestbook entries this should work sufficiently.

If you still have problems there are more options:

* Honeypot feature: Hidden input field that only spambots would fill in.
* Nonce: Will verify if you really loaded the page with the form first, before posting an entry. Spambots will just submit the form without having a Nonce.
* Akismet: Third party spamfilter by Automattic. Works really well, but not everybody likes to use a third party service.
* Custom Anti-Spam question: Use a simple quiz question to test if you are human.
* CAPTCHA: Fill in numbers and letters from an image. This should be your last resort, since it has bad usability and will scare off some visitors.

= I enabled the CAPTCHA, but I don't see it in the form. =

The CAPTCHA uses the one provided by the [Really Simple Captcha plugin](https://wordpress.org/plugins/really-simple-captcha/).
Please install and activate that plugin.

If it still doesn't show, it could be that the plugin has no write permission in the '/tmp' folder of the Really Simple Captcha plugin.
Please fix this in your install.

= How can I use Multiple Guestbooks? =

You can add a parameter to the shortcode, like:

	[gwolle_gb book_id="2"]

This will make that page show all the entries in Book ID 2.

If you use the template function, you can use it like this:

	show_gwolle_gb( array('book_id'=>2) );

= With multiple guestbooks, how do I keep track? =

There is no need to use id's that are incrementing. If you have a lot of guestbooks on lots of pages,
you can just use the id of the post as the id of the guestbook. That way you won't have double id's.
You can set the book_id automatically to the post_id with this shortcode:

	[gwolle_gb book_id="post_id"]

= I don't see the labels in the form. =

This plugin doesn't apply any CSS to the label elements. It is possible that your label elements have a white color on a white background.
You can check this with the Inspector in your browser. If that is the case, you have a theme or plugin that is applying that CSS to your
label elements. Please contact them.

= I don't get a notification email. =

First check your spambox in your mailaccount.

Second, on the settingspage you can change the From address for the email that is sent.
Sometimes there are problems sending it from the default address, so this is a good thing to change to a real address.

There are also several SMTP plugins, where you can configure a lot of settings for email.

If it still doesn't work, request the maillog at your hosting provider, or ask if they can take a look.

= I want to show the form and the list on different pages =

There are different shortcodes that you can use. Instead of the '[gwolle_gb]' shortcode, you can use '[gwolle_gb_write]' for just the form,
and '[gwolle_gb_read]' for the list of entries.

= I want to show the form immediately, without the button =

The shortcodes '[gwolle_gb]' and '[gwolle_gb_write]' have a parameter for the button. You can use them as '[gwolle_gb button="false"]' or '[gwolle_gb_write button="true"]', to deviate from the default.

= Moderation is enabled, but my entry is marked as checked =

If a user with capability of 'moderate_comments' posts an entry, it will be marked as checked by default, because he can mark it as checked anyway.

= Moderation is disabled, but some entries are still unchecked =

There is validation of the length of words in the content and author name. If the words are too long and it looks
abusive, it will be marked as unchecked. A moderator will still be needed to manually edit and check these entries.

= When opening the RSS Feed, I get a Error 404 =

You can refresh your rewrite rules, by going to Settings / Permalinks, and save your permalinks again.
This will most likely add the rewrite rule for the RSS Feed.

= I use a caching plugin, and my entries are not visible after posting =

When you have moderation disabled, Gwolle Guestbook will try to refresh the cache. If it doesn't on your setup,
please let me know which caching plugin you use, and support for it might be added.

You can also refresh or delete your cache manually. Most caching plugins offer support for that.

= I use a Multi-Lingual plugin =

There are 2 settings that you need to pay attention to. If you saved the settings for the form tab, you should save an
empty header and notice text. It will fill in the default there after saving, but that is okay.
As long as you saved an empty option, or it is still not-saved, then it will show the translated text from your MO file.

= I use a theme with AJAX =

Using a theme with AJAX navigation can give issues. Only on the guestbook page is the JavaScript and CSS loaded.
So you would need to load it on every page to have it available for the guestbook.

	<?php
	function my_gwolle_gb_register() {
		wp_enqueue_script('gwolle_gb_frontend_js');
		wp_enqueue_style('gwolle_gb_frontend_css');
	}
	add_action('wp_enqueue_scripts', 'my_gwolle_gb_register', 20);
	?>

= What capabilities are needed? =

For moderating comments you need the capability 'moderate_comments'.

For managing options you need the capability 'manage_options'.

= Can I override a template? =

You can look at 'frontend/gwolle_gb-entry.php', and copy it to your theme folder. Then it will be loaded by the plugin.
Make sure you keep track of changes in the default templatefile though.

= What hooks are available for customization? =

There are many hooks available in this plugin. Documentation is included in the zip file in /docs/actions and /docs/filters. Examples are included. If you have a need for a hook, please request this in the support forum.

= Should I really not use WordPress comments for a guestbook? =

Sure you can if you want to. In my personal opinion however it can be a good thing to keep comments and guestbook entries separated.
So if you already have a blog with comments, the guestbook entries might get lost in there, and keeping a separate guestbook can be good.
But if you don't use standard comments, you can just as easily use the comment section for a guestbook.


== Screenshots ==

1. Frontend View of the list of guestbook entries. On top the button that will show the form when clicked. Then pagination. Then the list of entries.
2. Widget with different options.
3. Main Admin Page with the overview panel, so that you easily can see what's the overall status.
4. List of guestbook entries. The icons display the status of an entry.
5. The Editor for a single entry. The Actions are using AJAX. There is a log of each entry what happened to this entry.
6. Settings Page. This is the first tab where you can select which parts of the form to show and use.
7. Dashboard Widget with new and unchecked entries.


== Changelog ==

= 2.1.2 =
* 2016-11-06
* Wrap text '(no entries yet)' inside the standard div#gwolle_gb_entries so we always list the submitted entry.
* Add filter for gwolle_gb_admin_reply_header.
* Long words check shouldn't match http, https or ftp strings.
* Explain scan for long text better.
* Again fix for Twenty Sixteen and Twenty Seventeen.
* On import, run the clear_cache function only once.
* Slightly improve error handling for admin AJAX.

= 2.1.1 =
* 2016-09-07
* Security fix: fix XSS on editor view (Thanks Radjnies of securify.nl).
* Security fix: fix CSRF on admin pages (Thanks Radjnies of securify.nl).
* Use str_replace on quotes the right way (no need to escape).
* Add Nonces to admin pages and check on them.
* Check the max number of checked entries on bulk edit on admin list.
* Flush cache on mass delete.
* Update text of metaboxes on main admin page.

= 2.1.0 =
* 2016-08-23
* Fix html validation in form buttons.
* Add setting for the scan for long text.
* Set that scan from 80 chars to 100 chars.
* Support persistent object cache.
* Flush cache on saving an entry on admin too.
* Add save-hook to mass edit.
* Add save-hook to AJAX admin actions.
* Add save-hook to importer.
* Load admin CSS on all pages (not JS).
* Offer MultiSite uninstall through a filter.
* Set wpdb prefix correctly on uninstall.
* Rename most files with prefix.

= 2.0.2 =
* 2016-08-19
* Fix loading images in CSS.
* Better AJAX icon.
* Improve a11y of toolbar menu-item.

= 2.0.1 =
* 2016-08-16
* Fix upgrade in MultiSite.
* More subtle styling of AJAX icon.
* More consistent naming in error fields.
* Append Infinite Scroll load message to div#gwolle_gb_entries.
* Don't use 'focus()' in form ajax, no point to it.
* Rename CSS and JS files.
* Rename infinite_scroll.php to ajax-infinite_scroll.php.
* Rename captcha-ajax.php to ajax-captcha.php.
* Rename admin/upgrade.php to admin/gwolle-gb-upgrade.php.

= 2.0.0 =
* 2016-08-08
* Add AJAX Form Submit (default).
* Add container div around gwolle_gb_messages div.
* Small adaptations to form-posthandling.php.
* Function 'gwolle_gb_clear_cache()' is now hooked to 'gwolle_gb_save_entry_frontend' action.
* Moderator and author mail are now hooked to 'gwolle_gb_save_entry_frontend' action.
* Rename frontend/write.php to frontend/form.php.
* Rename frontend/posthandling.php to frontend/form-posthandling.php.
* Rename admin/ajax.php to admin/ajax-management.php.
* Remove more br elements from BBcode lists.
* Add a 'read more' link to each entry in the widget in the form of a '&raquo;'.
* Set CSS width of '.input input[type="email"]' and '.input input[type="url"]' to 100%.
* Set z-index for infinite scroll load message.
* Make CSS reset for MarkItUp more specific.
* Add some bootstrap classes to the form.
* Add comments for translators.
* Cleanup changelog. Add changelog.txt for v0 and v1.
