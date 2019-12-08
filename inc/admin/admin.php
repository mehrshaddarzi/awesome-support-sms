<?php

namespace AW_SMS\admin;

use AW_SMS;

class Admin {

	/**
	 * Admin Page slug
	 */
	public static $admin_page_slug;

	/**
	 * Admin_Page constructor.
	 */
	public function __construct() {
		/*
		 * Set Page slug Admin
		 */
		self::$admin_page_slug = 'aw-sms';
		/*
		 * Setup Admin Menu
		 */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		/*
		 * Register Script in Admin Area
		 */
		//add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		/*
		 * Add SMS after Create Post Type
		 */
		add_action( 'save_post', array( $this, 'sms_send' ), 10, 3 );
	}

	function sms_send( $post_ID, $post, $update ) {
		global $sms;

		// Check Sms has Activate
		if ( ! isset( $GLOBALS['sms'] ) ) {
			return;
		}

		if ( $post->post_type == 'ticket' && $post->post_status == 'processing' and ! get_post_meta( $post_ID, 'check_if_run_once_send_sms', true ) and isset( $_POST['wpas_ticket_priority'] ) ) {

			// Option
			$options = get_option( 'aw_sms_opt' );

			// Get Sender name
			$sender_name = '';
			$sender_user = get_userdata( $post->post_author );
			if ( ! empty( $sender_user ) ) {
				$sender_name = $sender_user->first_name . ' ' . $sender_user->last_name;
			}

			// Get Receiver Name
			$receiver_name = '';
			if ( isset( $_POST['wpas_assignee'] ) and ! empty( $_POST['wpas_assignee'] ) ) {
				$_user = get_userdata( $_POST['wpas_assignee'] );
				if ( ! empty( $_user ) ) {
					$receiver_name = $_user->first_name . ' ' . $_user->last_name;
				}
			}

			// Get Property name
			$property = '';
			if ( isset( $_POST['wpas_ticket_priority'] ) and ! empty( $_POST['wpas_ticket_priority'] ) ) {
				$_term    = get_term( $_POST['wpas_ticket_priority'] );
				$property = $_term->name;
			}

			// Get Department Name
			$department = '';
			if ( isset( $_POST['wpas_department'] ) and ! empty( $_POST['wpas_department'] ) ) {
				$_term      = get_term( $_POST['wpas_department'] );
				$department = $_term->name;
			}

			// Get Id
			$id = $post->ID;
			if ( isset( $_POST['ticket_id'] ) and ! empty( $_POST['ticket_id'] ) ) {
				$id = $_POST['ticket_id'];
			}

			// Convert SMS Text
			$sms_text = str_ireplace(
				array(
					'[id]',
					'[title]',
					'[sender_name]',
					'[receiver_name]',
					'[property]',
					'[department]',
					'[date]',
					PHP_EOL
				),
				array(
					$id,
					$post->post_title,
					$sender_name,
					$receiver_name,
					$property,
					$department,
					date_i18n( "Y-m-d H:i", strtotime( $post->post_date ) ),
					"\n"
				),
				$options['text_sms']
			);


			// Check Sms To
			$sms->to = array();

			// Check Administrator
			if ( $options['send_to_admin'] == 1 and ! empty( $options['modir_mobile'] ) ) {
				$sms->to[] = $options['modir_mobile'];
			}

			// Check Sender
			if ( $options['send_to_sender'] == 1 ) {

				// Check User has Mobile
				$mobile_number = get_user_meta( $post->post_author, $options['user_meta_modir'], true );
				if ( ! empty( $mobile_number ) ) {
					$sms->to[] = $mobile_number;
				}
			}

			// Check Receiver
			if ( $options['send_to_receiver'] == 1 ) {

				// Check User has Mobile
				if ( isset( $_POST['wpas_assignee'] ) and ! empty( $_POST['wpas_assignee'] ) ) {
					$mobile_number = get_user_meta( $_POST['wpas_assignee'], $options['user_meta_modir'], true );
					if ( ! empty( $mobile_number ) ) {
						$sms->to[] = $mobile_number;
					}
				}
			}

			// Send SMS
			$sms->msg = $sms_text;
			$sms->SendSMS();

			# And update the meta so it won't run again
			update_post_meta( $post_ID, 'check_if_run_once_send_sms', true );
		}

	}

	/**
	 * Admin Link
	 *
	 * @param $page
	 * @param array $args
	 * @return string
	 */
	public static function admin_link( $page, $args = array() ) {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . $page ) );
	}

	/**
	 * If in Page in Admin
	 *
	 * @param $page_slug
	 * @return bool
	 */
	public static function in_page( $page_slug ) {
		global $pagenow;
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and $_GET['page'] == $page_slug ) {
			return true;
		}

		return false;
	}

	/**
	 * Load assets file in admin
	 */
	public function admin_assets() {
		global $pagenow;

		//List Allow This Script
		if ( $pagenow == "admin.php" ) {

			//wp_enqueue_style( 'aw-sms', AW_SMS::$plugin_url . '/asset/admin/css/style.css', array(), AW_SMS::$plugin_version, 'all' );
			//wp_enqueue_script( 'aw-sms', AW_SMS::$plugin_url . '/asset/admin/js/script.js', array( 'jquery' ), AW_SMS::$plugin_version, false );

		}

	}

	/**
	 * Set Admin Menu
	 */
	public function admin_menu() {
		add_menu_page( __( 'پیامک تیکت', 'aw-sms' ), __( 'پیامک تیکت', 'aw-sms' ), 'manage_options', self::$admin_page_slug, array( Settings::instance(), 'setting_page' ), 'dashicons-email', 10 );
		//add_submenu_page( self::$admin_page_slug, __( 'order', 'aw-sms' ), __( 'order', 'aw-sms' ), 'manage_options', self::$admin_page_slug, array( $this, 'admin_page' ) );
		//add_submenu_page( self::$admin_page_slug, __( 'setting', 'aw-sms' ), __( 'setting', 'aw-sms' ), 'manage_options', 'aw_sms_option', array( Settings::instance(), 'setting_page' ) );
	}

	/*
	 * Admin Page
	 */
	public function admin_page() {
		$simple_text = 'Hi';
		require_once AW_SMS::$plugin_path . '/inc/admin/views/default.php';
	}

}