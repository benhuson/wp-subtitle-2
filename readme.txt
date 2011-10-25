=== WP Subtitle 2 ===
Contributors: husobj
Tags: subtitle, content, title, subheading, subhead, alternate title
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.0

Add subtitles (subheadings) to your pages, posts and custom post types.

== Description ==

The WP Subtitle 2 plugin enables you to add subtitles to your pages, posts and custom post types.

It is based on the functionality of the <a href="http://www.husani.com/ventures/wordpress-plugins/wp-subtitle/">WP Subtitle</a> plugin but is completely re-coded from scratch to work with WordPress 3.0+ including support for custom post types.

It supports the same functions as WP Subtitle so you can use either plugin - but not both at the same time, obviously. The functions can be called slightly different though so please check the usage notes below.

&lt;?php the_subtitle(); ?&gt; can be used inside The Loop. Outside The Loop, use &lt;?php echo get_the_subtitle( $id ); ?&gt;, where $id is theor post's ID.

Just like the WordPress &lt;?php the_title(); ?&gt; function, &lt;?php the_subtitle(); ?&gt; accepts three parameters:

= &lt;?php the_subtitle( $before = '', $after = '', $echo = true ); ?&gt; =

$before (string) Text before the subtitle. Defaults to "".

$after (string) Text after the subtitle. Defaults to "".

$echo (boolean) If true, outputs the subtitle HTML. If false, return the subtitle for use in PHP.  Defaults to true.

= &lt;?php echo get_the_subtitle( $id = 0 ); ?&gt; =

$id (integer) Post (or page) ID.

= Add support form a custom post type =

&lt;?php add_post_type_support( 'my_post_type', 'wps_subtitle' ); ?&gt;

== Installation ==

1. Upload the WP Subtitle 2 plugin to your WordPress plugins folder (/wp-content/plugins) or install it via your WordPress admin. Then activate it from the plugin admin page.
2. Edit your theme template and add &lt;?php the_subtitle(); ?&gt; or &lt;?php get_the_subtitle(); ?&gt; where you would like the subtitle to appear.

== Screenshots ==

1. Post edit screen
2. A single post showing a subtitle

== Changelog ==

= 1.0 =

* First version
