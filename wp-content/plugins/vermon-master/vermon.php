<?php
/*
Plugin Name: Vermon
Plugin URI: http://github.com/elastx/vermon
Description: Plugin that monitors versions of core and plugins
Author: Tobias Jakobsson
Version: 0.9
Author URI: http://github.com/tjakobsson
*/

defined( 'ABSPATH' ) or die( 'Cannot be accessed directly.' );

if ( !class_exists( 'Vermon' ) ) {
  class Vermon {
    protected $tag = 'vermon';
    protected $name = 'Vermon';
    protected $version = '0.9';
    protected $settings = array(
      'token' => array(
        'description' => 'Token to be used as authentication for getting information about versions'
      )
    );

    public function __construct() {
      if ( is_admin() ) {
        add_action( 'admin_init' , array( &$this, 'vm_settings_api_init' ) );
      }

      register_deactivation_hook( __FILE__, array( &$this, 'vm_flush_rewrites_rules' ) );
      register_activation_hook( __FILE__, array( &$this, 'vm_add_endpoint' ) );

      add_filter( 'query_vars' , array( &$this, 'vm_add_query_vars' ) );
      add_action( 'template_redirect' , array( &$this, 'vm_route_template_redirect' ) );
    }

    public function vm_settings_api_init() {
      add_settings_section(
        'vm_setting_section',
        'Vermon',
        array( &$this, 'vm_setting_section_callback_function' ),
        'general'
      );

      add_settings_field(
        'vm_setting_token',
        'Auth token',
        array( &$this, 'vm_setting_callback_function' ),
        'general',
        'vm_setting_section'
      );

      register_setting( 'general', 'vm_setting_token' );
    }

    public function vm_setting_section_callback_function() {
      echo '<p>Access token to Vermon (URL friendly)</p>';
    }

    public function vm_setting_callback_function() {
      echo '<input name="vm_setting_token" id="vm_setting_token" type="text" value="' . get_option( 'vm_setting_token' , 'no token') . '" class="code" />';
    }

    public function vm_flush_rewrites_rules() {
      flush_rewrite_rules();
    }

    public function vm_add_endpoint() {
      add_rewrite_endpoint( 'vermon', EP_ROOT );
      flush_rewrite_rules();
    }
    
    public function vm_add_query_vars( $vars ) {
      $vars[] = 'vermon';
      $vars[] = 'token';
      return $vars;
    }
    
    public function vm_route_template_redirect() {
      global $wp_query, $wp_version;

      if ( ! isset( $wp_query->query_vars['vermon'] ) )
        return;
      
      // ToDo: Look into "never cache" strategies
      nocache_headers();
      if ( !defined('DONOTCACHEPAGE') ){
	define('DONOTCACHEPAGE',true);
      }

      if ( $this->vm_auth( $wp_query->query_vars['token'] ) ) {
        
        if ( ! function_exists( 'get_plugins' ) ) {
	  require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $vermon_response = array(
          "wp_version" => $wp_version,
          "plugins" =>  get_plugins()
        );
        
        wp_send_json( $vermon_response );
        
      } else {
        header('HTTP/1.0 401 Unauthorized');
        wp_send_json( array( "message" => "Not authorized" ) ) ;
      }
    }

    private function vm_auth( $token ) {
      if ( ( get_option( 'vm_setting_token' ) !==  false ) && ( get_option( 'vm_setting_token' ) === $token ) )
        return true;

      return false;
    }

  }
  $vermon = new Vermon;
}

?>
