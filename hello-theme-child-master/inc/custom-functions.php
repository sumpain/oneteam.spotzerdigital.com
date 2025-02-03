<?php

// Force login to access site
function sp_force_login(){
	if ( (defined('DOING_AJAX') && DOING_AJAX) || (defined('DOING_CRON') && DOING_CRON) || (defined('WP_CLI') && WP_CLI) ) { return; }
	if ( is_user_logged_in() ) { return; }
	$url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if ( preg_replace('/\?.*/', '', wp_login_url()) === preg_replace( '/\?.*/', '', $url) ) { return; }
	// nocache_headers(); // not sure this is needed
	wp_safe_redirect( wp_login_url( $url ), 302 );
	exit;
}
add_action( 'template_redirect', 'sp_force_login' );

// Disable the user admin bar on public side on registration
function sp_remove_admin_bar($user_ID) {
    update_user_meta( $user_ID, 'show_admin_bar_front', 'false' );
	wp_redirect(site_url());
	exit();
}
add_action('user_register','sp_remove_admin_bar');

// Disable Gutenberg
add_filter('use_block_editor_for_post', '__return_false', 10);

// Customize Login Page
function sp_custom_login_logo_url(){
	return "";
}
add_filter( 'login_headerurl', 'sp_custom_login_logo_url' );

function sp_custom_login_logo_text(){
	return 'Welcome to the Spotzer Intranet<br /><small>Please login using your Spotzer Google account</small>';
}
//add_filter( 'login_headertext', 'sp_custom_login_logo_text' );

function sp_custom_login_scripts() {
	wp_enqueue_style( 'custom-login-style', get_stylesheet_directory_uri() . '/assets/login-style.css' );
	wp_enqueue_script( 'custom-login-script', get_stylesheet_directory_uri() . '/assets/login-script.js' );
}
//add_action( 'login_head', 'sp_custom_login_scripts' );