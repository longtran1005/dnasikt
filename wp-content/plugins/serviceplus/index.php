<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
 * Plugin Name: Service Plus (SSO)
 * Description: Ett plugin som integrerar Service+ med Wordpress
 * Version: 1.0.0
 * Author: Carl-Gerhard Lindesv&auml;rd
 */

/************************************
 * ADMIN PAGE
 ***********************************/
include_once( plugin_dir_path( __FILE__ ) . 'admin.php' );


class ServicePlus {

	private $API_URL = 'http://api.qa.newsplus.se/v1/';
	private $SSO_URL = 'http://account.qa.newsplus.se';
	private $HOME_URL = 'http://dnkort-prod/';
	private $API_EX_RESOURCE = 'dagensnyheter.se';

	private $roles = array(
	                'dn_free' => array(
	                    'display_name' => 'DN Gratis',
	                    'caps' => array(), // Wordpress Caps
	                    'dn_cap' => array()
	                ),
	                'dn_payed' => array(
	                    'display_name' => 'DN Betald',
	                    'caps' => array(), // Wordpress Caps
	                    'dn_cap' => array( // Our caps
	                        'visit_offers'
	                    )
	                ),
	                'dn_author' => array(
	                    'display_name' => 'DN Författare',
	                    'caps' => array(), // Wordpress Caps
	                    'dn_cap' => array()
	                )
	            );

	public function __construct() {
		// Activation Hook
		register_activation_hook( __FILE__, array( $this, 'onActivation' ) );
		// Deactibation Hook
		register_deactivation_hook( __FILE__, array( $this, 'onDeactivation' ) );
		// Filters
		add_filter( 'init', array( $this, 'init' ) );
		// Disable lost password function
		// The link will still remain
		add_filter ( 'allow_password_reset', '__return_false' );
		add_action ( 'admin_init', array ( $this, 'redirect_non_admin_users' ), 1 );

		// Admin area
		if(class_exists('ServicePlusAdminArea'))
			new ServicePlusAdminArea();

		// Set vars
		$this->SSO_URL 	= get_option( 'sso_serviceplus_url' ) ? get_option( 'sso_serviceplus_url' ) : $this->SSO_URL ;
		$this->API_URL 	= get_option( 'sso_api_url' ) ? get_option( 'sso_api_url' ) : $this->API_URL ;
		$this->HOME_URL = get_option( 'sso_home_url' ) ? get_option( 'sso_home_url' ) : $this->HOME_URL ;
		$this->API_EX_RESOURCE = get_option( 'sso_ex_resource' ) ? get_option( 'sso_ex_resource' ) : $this->API_EX_RESOURCE ;
	}

	public function init() {
		if(isset($_GET['logout'])) {
			wp_logout();
			wp_redirect( home_url('/') );
			exit;
		}
		$token 	= (isset($_GET['token']) AND !empty($_GET['token'])) ? $_GET['token'] : null ;
		$id 	= (isset($_GET['id']) AND !empty($_GET['id'])) ? $_GET['id'] : null ; // S+ id
		$return = (isset($_GET['return']) AND !empty($_GET['return'])) ? $_GET['return'] : null ;
		$pid 	= (isset($_GET['pid']) AND !empty($_GET['pid'])) ? $_GET['pid'] : null ; // post id

		if($token !== null AND $id !== null) {
			// Check if user comes from S+
			// $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null ;
			// if(parse_url($referer, PHP_URL_HOST) !== parse_url($this->SSO_URL, PHP_URL_HOST)) $errors[] = 'Not a valid request';

			// Get User-information from Service+
			$user = $this->_getCurrentUser( $token );
			if(!$user) $errors[] = 'Wrong response from S+';

			// No errors? Go on then
			if(!isset($errors)) {
				// Get User-entitlement from Service+
				$role = $this->_getUserRole( $token );
				// Create or Update existing Wordpress User
				$user_id = $this->_createOrUpdateWordpressUser( $user, $role );

				if(!is_wp_error($user_id)) {
					if( $user ) {
						wp_set_current_user( $user_id, $user->email );
						wp_set_auth_cookie( $user_id );
						do_action( 'wp_login', $user->email );

						add_action( 'wp_head', function() use ($user_id, $token, $id) {
							echo 	"<script>
									(function(win,id) {
										if(typeof win.burtApi !== 'undefined') {
											win.burtApi.push(function() {
												id && win.burtApi.connect('burt.beacon', 'serviceplus-id', id);
												burtApi.annotate('burt.content', 'loggedinbeacon', true);
											});
										}
									})(window,'$id');
									</script>";
						}, 10 );

						$url = strtok($_SERVER["REQUEST_URI"],'?');

						if($pid != null)
						{
							$url = add_query_arg( array(
							    'pid' => $pid
							), $url );
						}

						wp_redirect( esc_url( $url ) );
						exit;
					}
				}
			}
		}
	}

	/**
	 * Return data
     * [id] => 56Qy3zvkkVw0nvQcnEzRL2
     * [created] => 1412588008463
     * [updated] => 1426062488370
     * [location] => /56Qy3zvkkVw0nvQcnEzRL2
     * [brandId] => 0uAK7wv28CxMk2lSBitm9Y
     * [accountId] => 3SuOrqMed0ZXMlRSxxonUz
     * [type] => CUSTOMER
     * [email] => test1234567dntest@test.com
     * [firstName] => TestFörnamn
     * [lastName] => TestEfternamn
     * [phoneNumber] =>
	 * [active] => 1417090547203
	 */
	private function _getCurrentUser( $token, $id = null ) {
        $result = wp_remote_post( $this->API_URL . 'users/current?access_token=' . $token, array('method' => 'GET'));

       if(is_wp_error($result))
        	return false;

    	if($result['response']['code'] == 200) {
        	return json_decode($result['body'])->user;
        }

        return false;
	}

	private function _getUserRole( $token ) {
		$result = wp_remote_post( $this->API_URL . 'resources/verify-entitlement?externalResourceId='. $this->API_EX_RESOURCE .'&access_token=' . $token, array('method' => 'GET'));

		$wp_role = 'dn_free';

       if(is_wp_error($result))
        	return 'dn_free';

        if($result['response']['code'] == 200) {
			if(json_decode($result['body'])->entitled == 'true')
				$wp_role  = 'dn_payed';
		}

		return $wp_role;
	}

	private function _createOrUpdateWordpressUser( $user, $wp_role = 'subscriber'  ) {

		$userdata = array(
			'user_login' 	=> $user->email,
			'user_email' 	=> $user->email,
			'user_pass' 	=> $this->_generateRandomString( 15 ),
			'display_name' 	=> $user->firstName . ' ' . $user->lastName,
			'first_name' 	=> $user->firstName,
			'last_name' 	=> $user->lastName,
			'role' 			=> $wp_role
			);

		$wp_user = $this->_findUserByServicePlusId($user->id);

		// apply_filters( 'send_email_change_email', '__return_false', $wp_user, $userdata );
		// apply_filters( 'send_password_change_email', '__return_false', $wp_user, $userdata );
		add_filter( 'send_password_change_email', '__return_false');

		if($wp_user) {
			// Assign WP_User ID to $userdata
			$userdata['ID'] = $wp_user->ID;

			// Update user with $userdata
			// IF NOT an ADMIN or EDITOR
			if(!$this->_user_in_role(array('administrator','editor'),$wp_user)) {
				$wp_user_id = wp_update_user( $userdata );
			} else {
				$wp_user_id = $wp_user->ID;
			}
		} else {
			// Check if email exits!
			if(username_exists( $user->email ) && email_exists( $user->email )) {
				// Get User By Email
				$wp_user = get_user_by( 'email', $user->email );
				// Assign WP_User ID to $userdata
				$userdata['ID'] = $wp_user->ID;

				// Update user with $userdata
				// IF NOT an ADMIN or EDITOR
				if(!$this->_user_in_role(array('administrator','editor'),$wp_user)) {
					$wp_user_id = wp_update_user( $userdata );
				} else {
					$wp_user_id = $wp_user->ID;
				}
			} else {
				// Create User with $userdata
				$wp_user_id = wp_insert_user( $userdata );
			}

			// New user is created, but no meta keys added
			add_user_meta( $wp_user_id, 'serviceplus_id', $user->id, true );
			add_user_meta( $wp_user_id, 'serviceplus_brand_id', $user->brandId, false );
			add_user_meta( $wp_user_id, 'serviceplus_account_id', $user->accountId, false );
		}
		return $wp_user_id;
	}

	// Check if user is in a specific (Array)roles
	private function _user_in_role($in_roles, $user) {
		foreach((array)$user->roles as $role) {
			if(in_array($role, (array)$in_roles)) return true;
		}
		return false;
	}

	private function _findUserByServicePlusId( $id ) {
		$user = get_users(array(
		    'meta_key'     => 'serviceplus_id',
		    'meta_value'     => $id,
		    'meta_compare' => '=',
		    'number' => 1
		));

		if(count($user) > 0) {
			return $user[0];
		} else {
			return false;
		}

	}

	private function _generateRandomString( $length = 10 ) {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < $length; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}


	public function onActivation(){
	    foreach($this->roles as $role => $array) {

	        $role = add_role( $role, $array['display_name'], $array['caps'] );
	        if( ! $role ) continue;
	        if(count($array['dn_cap'] ) > 0) {
		        foreach($array['dn_cap'] as $cap) {
		            if( $role ) $role->add_cap( $cap );
		        }
	        }
	    }
	}

	public function onDeactivation(){
	    foreach($this->roles as $role => $array) {
	        $role_object = get_role( $role );
	        if(count($array['dn_cap'] ) > 0) {
		        foreach($array['dn_cap'] as $cap) {
		            if( $role_object ) $role_object->remove_cap( $cap );
		        }
	        }
	        remove_role( $role );
	    }
	}

	public function login( $cb ) {
		$url = $this->SSO_URL . '?appId='. $this->API_EX_RESOURCE .'&lc=sv';
		if($cb) $url .= '&callback=' . urlencode( $cb );
		return $url;
	}

	public function logout( ) {
		$url = $this->SSO_URL . '/logout?appId='. $this->API_EX_RESOURCE .'&lc=sv';
		$url .= '&callback=' . urlencode( get_home_url() . '?logout=true' );
		return $url;
	}

	public function redirect_non_admin_users() {
		if ( ! current_user_can( 'edit_posts' ) && '/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF'] ) {
			wp_redirect( home_url() );
			exit;
		}
	}

	// Logging and Debugging
	private function log() {
	    $logfile = fopen(plugin_dir_path( __FILE__ ) . "logs/log-".date("Ymd").".txt", "a") or die("Unable to open file!");
	    fwrite($logfile, "========================\r");
	    fwrite($logfile, "Date: " . date("Y-m-d H:i:s") . "\r");
	    fwrite($logfile, "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r");
	    fwrite($logfile, "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\r");
	    if(count($arr) > 0) {
	        foreach($arr as $key => $val) {
	            fwrite($logfile, $key . " = " . $val . "\r");
	        }
	    }
	    fwrite($logfile, "========================\n\r\n\r");
	    fclose($logfile);
	}

	private function debug($str) {
		echo "<pre>";
		print_r($str);
		echo "</pre>";
	}
}
if(class_exists('ServicePlus'))
	$ServicePlus = new ServicePlus();
?>
