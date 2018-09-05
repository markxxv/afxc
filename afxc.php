<?php
/*
Plugin Name: FX Bundle
Plugin URI: https://findexpert.eu
Description: Бандл кастомных плагинов и свойст от FX Digital Agency.
Version: 1.0
Author: Mark Vi
Author URI: https://findexpert.eu
*/


/**
* On activation Actions
**/

register_activation_hook( __FILE__, 'afxc_activate' );

function afxc_activate() {
	include_once( plugin_dir_path( __FILE__ ) . 'includes/install-plugins.php' );
}



/**
* Custom Thumbnails 
**/

add_action( 'after_setup_theme', 'fximages' );
function fximages() {
    add_image_size( 'fxsquad', 600, 600, true );
    add_image_size( 'fxth', 600, 400, true );
    flush_rewrite_rules(); // Clear permalinks cache
}


/**
* Customize Worpdress CSS
**/
function fx_admin_theme_style() {
	if ( current_user_can('administrator') ) {
	    wp_register_style('my_admin_css', plugins_url('css/custom.css', __FILE__));
	    wp_enqueue_style('my_admin_css', plugins_url('css/custom.css', __FILE__));
    }
}

add_action('admin_enqueue_scripts', 'fx_admin_theme_style');
add_action('login_enqueue_scripts', 'fx_admin_theme_style');
add_action( 'wp_enqueue_scripts', 'fx_admin_theme_style' );





/**
* Add /blog path to Posts
**/

function create_new_url_querystring() {
    add_rewrite_rule(
        'blog/([^/]*)$',
        'index.php?name=$matches[1]',
        'top'
    );
    add_rewrite_tag('%blog%','([^/]*)');
}
add_action('init', 'create_new_url_querystring', 999 );
/**
 * Modify post link
 * This will print /blog/post-name instead of /post-name
 */
function append_query_string( $url, $post, $leavename ) {
    if ( $post->post_type == 'post' ) {        
        $url = home_url( user_trailingslashit( "blog/$post->post_name" ) );
    }
    return $url;
}
add_filter( 'post_link', 'append_query_string', 10, 3 );
/**
 * Redirect all posts to new url
 * If you get error 'Too many redirects' or 'Redirect loop', then delete everything below
 */
function redirect_old_urls() {
    if ( is_singular('post') ) {
        global $post;
        if ( strpos( $_SERVER['REQUEST_URI'], '/blog/') === false) {
           wp_redirect( home_url( user_trailingslashit( "blog/$post->post_name" ) ), 301 );
           exit();
        }
    }
}
add_filter( 'template_redirect', 'redirect_old_urls' );

