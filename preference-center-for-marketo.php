<?php

/*
 * Plugin Name: Preference Center for Marketo
 * Plugin URI: https://www.feedotter.com/email-preference-center-marketo-wordpress/
 * Description: Preference Center for Marketo - Setup an email preference center for your Marketo database on your existing WordPress website. 15 minute seutp. No IT or consultants needed.
 * Version: 1.0
 * Author: Feedotter
 * Author URI: https://www.feedotter.com/
 */

require ( 'api.php' );

define( 'PCFM_FEEDOTTER_HOST', 'https://app.feedotter.com' );
//define('PCFM_FEEDOTTER_HOST', 'http://simpleapp.dev');


define( 'PCFM_PLUGIN_DIR', 'preference-center-for-marketo' );
define( 'PCFM_OPTIONS_PAGE_SLUG', 'marketo-email-preference-centers' );
define( 'PCFM_MANAGE_PREFERENCE_CENTERS_TAB_SLUG', 'manage-centers' );
define( 'PCFM_SUPPORT_TAB_SLUG', 'support' );
define( 'PCFM_API_SETTINGS_TAB_SLUG', 'api-settings' );

define( 'PCFM_OPTIONS_WELCOME_PAGE_SLUG', 'marketo-email-preference-center-welcome' );

add_filter( 'query_vars', 'pcfm_rewrite_query_vars' );

function pcfm_plugin_scripts() {
	wp_register_style( 'pcfm_styles' , plugins_url ( PCFM_PLUGIN_DIR . '/assets/css/settings.css' ) );
	wp_enqueue_style( 'pcfm_styles' );

	wp_register_style( 'pcfm_custom', plugins_url ( PCFM_PLUGIN_DIR . '/assets/css/custom.css' ) );
	wp_enqueue_style( 'pcfm_custom' );

}
add_action( 'admin_enqueue_scripts', 'pcfm_plugin_scripts' );

register_deactivation_hook( __FILE__, 'pcfm_rewrite_deactivation' );

add_action( 'init', 'pcfm_init_hooks');

add_action( 'init', function () {

	$current_path = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;
	if( $current_path ) {
		list( $path, $query ) = array_values( parse_url( $current_path ) );
		$path= trim( $path, '/' );
		$rewrites = get_option('pcfm_rewrites', array());
		$email = isset($_GET['email']) ? sanitize_email( $_GET['email'] ) : null;

		if( in_array( $path, $rewrites) ) {

			// Disable caching plugins. This should take care of:
			//   - W3 Total Cache
			//   - WP Super Cache
			//   - ZenCache (Previously QuickCache)
			if ( ! defined('DONOTCACHEPAGE') ) {
				define('DONOTCACHEPAGE', true);
			}
			if ( ! defined ( 'DONOTCDN' ) ) {
				define ( 'DONOTCDN', true );
			}
			if ( ! defined ( 'DONOTCACHEDB' ) ) {
				define ( 'DONOTCACHEDB', true );
			}
			if ( ! defined ( 'DONOTMINIFY' ) ) {
				define ( 'DONOTMINIFY', true );
			}
			if ( ! defined ( 'DONOTCACHEOBJECT' ) ) {
				define ( 'DONOTCACHEOBJECT', true );
			}

			$pcfm_id = array_search ( $path, $rewrites );
			$api = new PcfmAPI ( PCFM_FEEDOTTER_HOST, get_option ( 'pcfm_api_key' ) );


			if ( $_SERVER ['REQUEST_METHOD'] === 'POST') {

				$pref_center = $api->get_marketo_preference_center ( $pcfm_id );
				$arr_key_values = array ();
				$action = sanitize_text_field ( $_POST ['action'] );
				$email = sanitize_email ( $_POST ['email'] );

				if ( is_email ( $email ) ) {
					foreach ( $pref_center->marketoPreferences as $preference ) {
						$arr_key_values [$preference->marketoCustomField->name] = isset ( $_POST [$preference->marketoCustomField->name] ) ? filter_var ( $_POST [$preference->marketoCustomField->name], FILTER_VALIDATE_BOOLEAN ) : false;
						if ($action == 'UNSUBSCRIBE_ALL') {
							$arr_key_values [$preference->marketoCustomField->name] = false;
						}
					}

					if ( $action == 'UNSUBSCRIBE_ALL' ) {
						$arr_key_values ['trackUnsubscribe'] = true;
						$arr_key_values ['referer'] = $_SERVER ['HTTP_REFERER'];
					} else {
						$arr_key_values ['trackClick'] = true;
					}

					// make updates to lead in marketo
					$api->update_marketo_preference_center_preferences ( $pcfm_id, $email, $arr_key_values );
				}
			}

			$pref_center_render_result = $api->render_marketo_preference_center_template ( $pcfm_id, $email, array (
					'isPost' => $_SERVER ['REQUEST_METHOD'] === 'POST',
					'trackVisit' => $_SERVER ['REQUEST_METHOD'] != 'POST' 
			) );

			echo $pref_center_render_result->body;

			exit ( 0 );
		}
	}
} );

function pcfm_rewrite_query_vars( $query_vars ) {
    $query_vars [] = 'email';
    return $query_vars;
}

function pcfm_rewrite_deactivation() {
    delete_option('pcfm_api_welcome');
    delete_option('pcfm_api_key');
    delete_option('pcfm_api_key_connect_success');
    delete_option('pcfm_rewrites');
}


function pcfm_init_hooks() {
    add_action('admin_notices', 'pcfm_display_notice');
    add_action('admin_menu', 'pcfm_create_menu');
}

function pcfm_display_notice() {
    global $hook_suffix;
    // check if user entered API key
    if ( $hook_suffix == 'plugins.php' ) {
        $apiKey = get_option( 'pcfm_api_key' );
        if ( ! $apiKey ) {
            include ( 'inc/api-key-notice.php' );
        }
    }
}

function pcfm_create_menu() {
    add_menu_page( 'Preference  Center', 'Preference Center', 'administrator', PCFM_OPTIONS_PAGE_SLUG, 'pcfm_plugin_settings_page', plugins_url(PCFM_PLUGIN_DIR . '/assets/images/menu-otter.png' ) );

    add_submenu_page( null, // parent slug is null to be hidden in menu
            'Marketo Preference Center', // page title,
            'Welcome', 'manage_options', // cap
            PCFM_OPTIONS_WELCOME_PAGE_SLUG, // slug
            'pcfm_plugin_welcome_page' ); // callback
    // call register settings function
    add_action( 'admin_init', 'pcfm_register_plugin_settings' );
    add_action( 'current_screen', 'pcfm_plugin_check_welcome' );
}

function pcfm_get_options_page_url() {
    return 'admin.php?page=' . PCFM_OPTIONS_PAGE_SLUG .'&tab=' . PCFM_API_SETTINGS_TAB_SLUG;
}

function pcfm_get_support_page_url() {
    return pcfm_get_options_page_url() . '&tab=' . PCFM_SUPPORT_TAB_SLUG;
}

function pcfm_get_manage_centers_page_url() {
    return pcfm_get_options_page_url() . '&tab=' . PCFM_MANAGE_PREFERENCE_CENTERS_TAB_SLUG;
}

function pcfm_plugin_check_welcome() {
    $current_screen = get_current_screen();
    $is_welcomed = get_option( 'pcfm_api_welcome' );

    if ( 'admin_page_marketo-email-preference-center-welcome' === $current_screen->id ) {
        update_option( 'pcfm_api_welcome', 'done' );
    } elseif ( $is_welcomed !== 'done' ) {
        die( wp_redirect( 'admin.php?page=' . PCFM_OPTIONS_WELCOME_PAGE_SLUG ) );
    }
}

function pcfm_register_plugin_settings() {
    // register our settings
    register_setting( 'pcfm_plugin_settings', 'pcfm_api_key' );
    register_setting( 'pcfm_plugin_etc', 'pcfm_api_key_connect_success' );
    register_setting( 'pcfm_plugin_general', 'pcfm_api_welcome' );
    register_setting( 'pcfm_plugin_general', 'pcfm_rewrites' );

    add_action( 'update_option_pcfm_api_key' , function( $old_value, $value ) {
    	//delete enabled/disabled status if api key changed so not stale data is stored
    	delete_option('pcfm_rewrites');

    	$api = new PcfmAPI( PCFM_FEEDOTTER_HOST, get_option( 'pcfm_api_key' ) );
    	$pref_centers = $api->list_marketo_preference_centers();
    	
    	
    	if( $pref_centers == FALSE ) {
    		update_option( 'pcfm_api_key_connect_success', false );
    	}else{
    		update_option( 'pcfm_api_key_connect_success', true );
    	}
    }, 10, 2);
}

function pcfm_plugin_welcome_page() {
    include ( 'inc/welcome-page.php' );
}

function pcfm_plugin_settings_page() {

	if( ! get_option ( 'pcfm_api_key' ) || ! get_option ( 'pcfm_api_key_connect_success' ) ) {
		$tab = isset( $_GET['tab'] ) && in_array( sanitize_key( $_GET['tab'] ), array(PCFM_API_SETTINGS_TAB_SLUG, PCFM_SUPPORT_TAB_SLUG) ) ? sanitize_key( $_GET['tab'] )  : PCFM_API_SETTINGS_TAB_SLUG;
	}else{
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : PCFM_MANAGE_PREFERENCE_CENTERS_TAB_SLUG;
	}

	//only load pref centers if api key is entered
	if( get_option ( 'pcfm_api_key' ) ) {
		// if manage tab load preference centers using api
	    if ( $tab == PCFM_MANAGE_PREFERENCE_CENTERS_TAB_SLUG ) {
	    		$api = new PcfmAPI( PCFM_FEEDOTTER_HOST, get_option( 'pcfm_api_key' ) );
	    		$pref_centers = $api->list_marketo_preference_centers();
	    		if( $pref_centers == FALSE ) {
	    			//mean error connecting
	    		}
	    }
	}
    $pcfm_rewrites = get_option( 'pcfm_rewrites', array() );

    include ( 'inc/settings-page.php' );
}

add_action( 'admin_footer', 'pcfm_ajax_go_live_action' ); // Write our JS below here

add_action( 'wp_ajax_pcfm_ajax_go_live_handler', 'pcfm_ajax_go_live_handler' );

function pcfm_ajax_go_live_handler() {
    $enable = filter_var( $_POST ['enable'], FILTER_VALIDATE_BOOLEAN );
    $rewrites = get_option( 'pcfm_rewrites') ;
    if ($enable === TRUE) {

        $api = new PcfmAPI(PCFM_FEEDOTTER_HOST, get_option( 'pcfm_api_key' ) );
        $pref_center = $api->get_marketo_preference_center( sanitize_text_field( $_POST ['pcfm_id'] ) );
        if ( ! is_array( $rewrites ) ) {
            $rewrites = array();
        }

        $rewrites[$pref_center->id] = $pref_center->permalink;
        update_option( 'pcfm_rewrites', $rewrites );
    } else {
        if ( ! is_array( $rewrites ) ) {
            $rewrites = array();
        }
        if ( isset( $rewrites [$_POST ['pcfm_id']] ) ) {
            unset( $rewrites [$_POST ['pcfm_id']] );
            update_option( 'pcfm_rewrites', $rewrites );
        }
    }
}

function pcfm_ajax_go_live_action() {
?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            window.mepcGoLive = function (el, id) {

                $(el).data('isLive', $(el).data('isLive') == 0 ? true : false);

                var data = {
                    'action': 'pcfm_ajax_go_live_handler',
                    'enable': $(el).data('isLive') == 0 ? false : true,
                    'pcfm_id': id
                };

                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                jQuery.post( ajaxurl, data, function ( response ) {
                    $(el).closest('.mepc-optin').find('.mepc-status .mepc-red, .mepc-status .mepc-green').toggle();
                    $(el).html($(el).data('isLive') == 0 ? "Go Live" : "Disable");
                });
            }
        });
    </script>
<?php
}
?>