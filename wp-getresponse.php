<?php
/*
Plugin Name: WP GetResponse
Plugin URI: http://core.sproutventure.com
Description: Send registration info to GetResponse Service
Version: .5.1
Author: Dan Cameron of Sproutventure
Author URI: http://sproutventure.com 
*/


/**
* Get_Response Class
*/
define('GR_ABSPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

if ( !class_exists('GetResponse') ) {
	
	class GetResponse
	{
		var $options;
		
		private $api_url = 'http://api2.getresponse.com';
		private $debug = FALSE;
		
		function __construct() {
			
			// Create array of options
			$this->options = get_option('gr_options');
			
			// Return the value of the full name field option
			$this->add_first_last = $this->options['gr_add_full_name'];
			
			// Add teh field if above is true
			if ($this->add_first_last) {
				add_action('register_form', array ($this, 'add_first_last_name_fields') );
				//add_action('register_post', array ($this, 'error_check_first_last',10,3) );
				add_action('user_register', array ($this, 'register_first_last_fields') );
			}
			
			// Options panel
			if (is_admin()) {
				include ( GR_ABSPATH . 'views/admin.php' );
				$GetOptions = new GetResponseOptions();
			}
			
			// Add contacts to Get response on registration
			add_action('user_register', array ($this, 'send_getresponse_contact') );
		}
		
		// The field that will be added below the registration form
		function add_first_last_name_fields(){
			include_once('views/admin-fields.php');
		}

		// Error checking ( disabled for now )
		function error_check_first_last($login, $email, $errors) {
			global $fullname;
			if ($_POST['full_name'] == '') {
				$errors->add('empty_fullname', "<strong>ERROR</strong>: Please enter your full name");
			} else {
				$firstname = $_POST['full_name'];
			}
		}
		
		// Register the nicename within the DB
		function register_first_last_fields($user_id, $password="", $meta=array() )  {
			$userdata = array();
			$userdata['ID'] = $user_id;
			$user_nicename = $_POST['full_name'];
			$userdata['user_nicename'] = $user_nicename;
			wp_update_user($userdata);
		}
		
		// This is what we're here for.
		function send_getresponse_contact($id) 
		{
			global $wpdb,$table_prefix;
			$tp = $wpdb->prefix;
			$wpuser = $wpdb->get_row("SELECT * FROM {$tp}users WHERE ID = $id LIMIT 1");
			
			$user_nicename = (!empty($wpuser->user_nicename)) ? $wpuser->user_nicename : $wpuser->user_login;
			
			get_getresponse( array(
					'email' => $wpuser->user_email,
					'full_name' => $user_nicename )
			);
			if( $this->debug ) error_log( "send_getresponse_contact(): " . print_r( $wpuser, true ) );
		}
		
		// This is what we're here for.
		/*/
		function send_getresponse_contact_after_comment($id) 
		{
			global $wpdb,$table_prefix;
			$tp = $wpdb->prefix;
			$wpuser = $wpdb->get_row("SELECT * FROM {$tp}users WHERE ID = $id LIMIT 1");
			
			$user_nicename = (!empty($wpuser->user_nicename)) ? $wpuser->user_nicename : $wpuser->user_login;
			
			get_getresponse( array(
					'email' => $wpuser->user_email,
					'full_name' => $user_nicename )
			);
			if( $this->debug ) error_log( "send_getresponse_contact(): " . print_r( $wpuser, true ) );
		}
		/**/
		
		// Function that does all the work
		function getresponse( $args = array() )
		{
			/*/
			if ( $this->debug ) {
				$defaults = array(
					'email' => 'dancameron@gmail.com',
					'full_name' => 'Dan Cameron',
					'ip' => '127.00.1',

				);
			}
			/**/
			
			// Create options array
			$options = get_option('gr_options');
			
			$this->api_key = $options['gr_api_key'];
			$this->campaign = $options['gr_campaign'];
			
			$args = wp_parse_args($args, $defaults);

			extract($args);
			
			if( $this->debug ) error_log( "getresponse(): " . $email . print_r( $options, true ));
			
			
			if ( $email && !empty($this->api_key) && !empty($this->campaign) ) {
				// Load up the jsonRPCClient
				require_once 'library/jsonRPCClient.php';
				$client = new jsonRPCClient($this->api_url);
				$result = NULL;

				// Retrive the campaign array
				try {
					$result = $client->get_campaigns(
						$this->api_key,
						array (
							# find by name literally
							'name' => array ( 'EQUALS' => $this->campaign )
						)
					);
				}
				catch (Exception $e) {
					// Let's not kill everything but we can surely email the error to an admin
					// die($e->getMessage());
					$message = sprintf(__('Submission error found on %s because the %s campaign could not be found'), get_bloginfo('name'), $this->campaign) . "\r\n\r\n";
					$message .= __('The registration was made by:') . "\r\n";
					$message .= sprintf(__('Name: %s'), $full_name) . "\r\n";
					$message .= sprintf(__('Email: %s'), $email) . "\r\n\r\n";

					$subject = sprintf(__('GetResponse Submission Error on %s'), get_bloginfo('name'));
					
					wp_mail(get_settings('admin_email'), $subject, $message);
				}

				if( $this->debug ) error_log( "getresponse->campaign: " . print_r( $result, true ) );


				// Var out the campagin for the add_contact below
				$CAMPAIGN_ID = array_pop(array_keys($result));

				// Add the contact to the $compaign
				try {
					$result = $client->add_contact(
						$this->api_key,
						array (
							'campaign'		=>	$CAMPAIGN_ID,
							'name'			=>	$full_name,
							'email'			=>	$email,
							'cycle_day'		=>	'0'
						)
					);
				}
				catch (Exception $e) {
					// Let's not kill everything but we can surely email the error to an admin.
					// die($e->getMessage());
					$message = sprintf(__('Submission error found on %s'), get_bloginfo('name')) . "\r\n\r\n";
					$message .= __('The registration was made by:') . "\r\n";
					$message .= sprintf(__('Name: %s'), $full_name) . "\r\n";
					$message .= sprintf(__('Email: %s'), $email) . "\r\n\r\n";

					$subject = sprintf(__('GetResponse Submission Error on %s'), get_bloginfo('name'));
					wp_mail(get_settings('admin_email'), $subject, $message);
				}

				if( $this->debug ) error_log( "getresponse->add_contact: " . print_r( $result, true ) );
			}
		}
		
		function getresponse_campaigns()
		{
			$options = get_option('gr_options');
			$api_key = $options['gr_api_key'];

			// Load up the jsonRPCClient
			require_once 'library/jsonRPCClient.php';
			$client = new jsonRPCClient($this->api_url);
			$result = NULL;

			// Retrive the campaign array
			try {
				$result = $client->get_campaigns(
					$api_key
				);
			}
			catch (Exception $e) {
				// Let's not kill everything but we can surely email the error to an admin
				if( $debug ) error_log( "getresponse_campaigns(): " . $e->getMessage() );
			}

			return $result;
		}
	}
	global $gr;
	$gr = new GetResponse();
	
	// Load up template tags
	include ('library/template-tags.php');
}