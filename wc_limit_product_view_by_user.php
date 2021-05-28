<?php

/*
Plugin Name: wc_limit_product_view_by_user
Description: This plugin allows you to restrict the visibility of a product to a predefined list of user.  Use WooCommerce : from 5.3.0
Author: MM Informatique
Version: 1.0
Author URI: https://www.mminformatique.fr/
Domain Path: /languages
*/

//register_activation_hook( __FILE__, 'wc_check_function' );

add_action( 'after_setup_theme', 'wclp_my_theme_load_theme_textdomain' );
add_action('woocommerce_product_options_general_product_data',"wclp_limit_product_view");
add_action('save_post', 'wclp_product_update_meta', 10, 3);
add_action( 'pre_get_posts', 'wclp_exclude_products_users' ,5,1);
add_action('template_redirect','wclp_loading_exclude_products_users' ,5,1);
add_action('woocommerce_before_shop_loop', 'wclp_access_denied_article');
add_action('admin_enqueue_scripts','wclp_loading_styles');
add_action('admin_enqueue_scripts','wclp_loading_script');

if( !class_exists('WC_Product') ) {
  		return;
  	}

$log_file_direc = WP_PLUGIN_DIR .'/wc_limit_product_view_by_user/log.txt';

/*
	my_theme_load_theme_textdomain
*/
function wclp_my_theme_load_theme_textdomain() {
    $trad_directory = WP_PLUGIN_DIR . '/wc_limit_product_view_by_user/languages';
	load_theme_textdomain('wclp', $trad_directory);
}

/*
	wclp_loading_styles
*/
function wclp_loading_styles() {
	wp_register_style( 'wclp_styles',plugins_url('wclp_styles.css',__FILE__ ));
  	wp_enqueue_style('wclp_styles');
}

/*
	wclp_loading_scripts
*/
function wclp_loading_script() {
    wp_enqueue_script( 'wclp_js', plugins_url('wclp_scripts.js',__FILE__ ), array(), '1.0.0', true );
}

/*  Limit_product_view 
 	This function manages the display of users in the settings page of each woocommerce product.
	The plugin retrieves the lists of users already authorized, and automatically check the linked checkbox
*/
function wclp_limit_product_view()
{
  	// Prepare get user authorized list
  	$log_file_direc = WP_PLUGIN_DIR .'/wc_limit_product_view_by_user/log.txt';
	$args = array("all");
	$user_query = new WP_User_Query( $args );
	
	// get the user authorized list
	$allow_user_list = get_post_meta(get_the_ID(),'wc_limit_product_view_by_user' ,false );
  	
  	echo '<div id="div_wclp_user_list">';
  	echo "<h3> WC_Limit_Product_View_By_User </h3>";

  	if (in_array(0 , $allow_user_list)) {
			$checkbox_private = "make_private";
		} else
		{
			$checkbox_private = 9999;
		}

  	woocommerce_wp_checkbox(
					array(
						'id' => "private_product", 
						'label' => __('Make this product private','wclp'),
						'value' => $checkbox_private,
						'cbvalue' => "make_private",
					)
	);
  	echo '<div id="div_wclp" >';
		echo '<h4>' . __('Users list','wclp') . '</h4>';

		// User display and traitement loop for checkbox
		foreach  ($user_query->get_results() as $user) {
			// checkbox automatic check if user is already in authorized list
			if (in_array($user->ID , $allow_user_list)) {
				$state = $user->ID;
			} else
			{
				$state = "";
			}
			$allowed_roles = array( 'editor', 'administrator' );
			if ( !array_intersect( $allowed_roles, $user->roles ) ) {
				woocommerce_wp_checkbox(
					array(
						'id' => "wclp_users[]", 
						'label' => $user->user_login,
						'value' => $state,
						'cbvalue' => $user->ID,
					)
				);
			}

		}

	  	

	echo '</div>';
	echo '</div>';
	
}


/*
	wclp_product_update_meta
	this function is triggered when the post is updated, it processes the data, it deletes the previous meta values ​​and adds the new values ​​to it

*/
function wclp_product_update_meta($product_id, $post, $update) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	if ($post->post_type == 'product') {
		// Sanitize public checkbox
		$checkbox_private_state = explode("\"", sanitize_text_field($_POST['private_product']) );

		// Sanitize checkbox
		$checkbox_safe = array();
		foreach (($_POST['wclp_users']) as $to_be_sanitize_checkbox) {
			$checkbox_safe[] = sanitize_text_field($to_be_sanitize_checkbox);
		}

		delete_post_meta(get_the_ID(),'wc_limit_product_view_by_user' );
		if (in_array("make_private", $checkbox_private_state) ) {
			add_post_meta($product_id, 'wc_limit_product_view_by_user', 0 );
			foreach  ($checkbox_safe as $wclp_checkbox) {
				add_post_meta($product_id, 'wc_limit_product_view_by_user', "test" );
				if ( is_numeric($wclp_checkbox) and get_user_by('ID',$wclp_checkbox)) {
					add_post_meta($product_id, 'wc_limit_product_view_by_user', $wclp_checkbox );
				}

				
			}
		} 

			
	}
	
}


/*
	wclp_loading_exclude_products_users
	This function check that the user is authorized to see the product being loaded. If this is not the case, it returns it to the list of products, with an error message
*/
function wclp_loading_exclude_products_users() {
	$user = wp_get_current_user();
	$allowed_roles = array( 'editor', 'administrator' );
	$allow_user_list = get_post_meta(get_the_ID(),'wc_limit_product_view_by_user' ,false );
	if (!in_array(get_current_user_id(), $allow_user_list) and !empty($allow_user_list) and is_product() ) {
		if ( !array_intersect( $allowed_roles, $user->roles) ) {
			$permalink_site = get_permalink( get_page_by_title( 'boutique' ));
    		wp_redirect(add_query_arg( 'wclp_code', 'product_access_denied',$permalink_site) );
    		exit();
    	}
	}
}

/*
	wclp_access_denied_article
	This function allows the display of error message when the user does not have the right to see the product
*/
function wclp_access_denied_article() {
	if (!empty($_GET) and sanitize_text_field($_GET['wclp_code']) == "product_access_denied" ) 
		// sanitize
	{
		$message = __('Sorry, you do not have permission to view this product','wclp');
    	wc_add_notice( $message , 'error' );
	}
}

/*
	wclp_exclude_products_users
	This function retrieves the user's id and compares it to the database if the user is authorized or has administration or editing rights, the function allows the view of the product. Otherwise it returns the user to the list of products with an error message

*/
function wclp_exclude_products_users($query) {
	$user = wp_get_current_user();
	$allowed_roles = array( 'editor', 'administrator' );
	$public_product = get_post_meta(get_the_ID(),'wc_public_product' ,true );
	$user_list_product = get_post_meta(get_the_ID(),'wc_limit_product_view_by_user' ,false );

	if ( !array_intersect( $allowed_roles, $user->roles ) ) {

		if ( (is_tax( 'product_cat' ) || is_post_type_archive('product') ) && !is_admin()) {
		
			
			$query->set( 'meta_query', array(
							'relation'    => 'OR',
							array(
									'key'   => 'wc_limit_product_view_by_user',
									'value'     => array(get_current_user_id()),
									'compare' 	=> 'IN'
								),
							array(
									'key' => 'wc_limit_product_view_by_user',
									'compare' => 'NOT EXISTS'
								)
							
							) 
						);
		}
	}

}


/* Register activation hook. */
register_activation_hook( __FILE__, 'wclp_admin_notice_activation_hook' );
 
/**
 * Runs only when the plugin is activated.
 * 
 */
function wclp_admin_notice_activation_hook() {
 
    /* Create transient data */
    set_transient( 'wclp-admin-notice', true, 5 );
}
 
 
/* Add admin notice */
add_action( 'admin_notices', 'wclp_admin_notice_notice' );
 
 
/**
 * WCLP Admin Notice
 *
*/
function wclp_admin_notice_notice(){
 
    /* Check transient, if available display notice */
    if( get_transient( 'wclp-admin-notice' ) and !class_exists('WC_Product') ) {

        echo "<div class=\"error notice is-dismissible\">";
        echo "<p>" . __('Warning ! WC_limit_product_view_by_user require Woocommerce, Woocommerce does not correctly activated or installed','wclp'). "</p>";
        echo "</div>";
        deactivate_plugins(plugin_basename( __FILE__ ));

        /* Delete transient, only display this notice once. */
        delete_transient( 'wclp-admin-notice' );
    }
}
