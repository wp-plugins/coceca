<?php
/*
    Plugin Name: CoCeCa
    Description: CTA plugin is an innovative solution designed to help you grow your WordPress blog. It creates an opportunity for you to promote your WordPress Websites & Blogs and engage your site visitors, in more ways than one.
    Author: CoCeCa
    Version: 1.6
    Plugin URI: http://coceca.com/
    Author URI: http://coceca.com/help/
    Provides : CoCeCa
*/

if(is_admin()){

    defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );
    define( 'COCECA_PLUGIN_VERSION', '1.6' );
    define( 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
    define( 'COCECA_PLUGIN_NAME', trim( dirname( PLUGIN_BASENAME ), '/' ) );
    define( 'COCECA_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
    define( 'COCECA_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );



    require_once COCECA_PLUGIN_DIR . '/includes/functions.php';
    require_once COCECA_PLUGIN_DIR . '/includes/coceca_plugin-class.php';
    require_once COCECA_PLUGIN_DIR . '/includes/plugin-dependencies.php';



    if(class_exists('Coceca_Plugin'))
    {
        // Installation and uninstallation hooks
       register_activation_hook(__FILE__, array('Coceca_Plugin', 'activate'));
       register_deactivation_hook(__FILE__, array('Coceca_Plugin', 'deactivate'));
    }

}


define('EXT_SITE_URL','https://coceca.com/members_area/');
define('COCECA_SITE_URL','https://coceca.com/');

add_action('wp_enqueue_scripts', 'coceca_front_enqueue_scripts' );
function coceca_front_enqueue_scripts(){
    wp_enqueue_script('jQuery_cookie', EXT_SITE_URL.'coceca/js/jquery.cookie.js', array('jquery'));
}