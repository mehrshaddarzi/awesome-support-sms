<?php

namespace AW_SMS\admin;

use AW_SMS\core\SettingAPI;

/**
 * Class Settings
 * @see https://github.com/tareq1988/wordpress-settings-api-class
 */
class Settings {
	/**
	 * Plugin Option name
	 */
	public $setting;

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Admin_Setting_Api constructor.
	 */
	public function __construct() {
		/**
		 * Set Admin Setting
		 */
		add_action( 'admin_init', array( $this, 'init_option' ) );
	}

	/**
	 * Display the plugin settings options page
	 */
	public function setting_page() {

		echo '<div class="wrap">';
		settings_errors();

		$this->setting->show_navigation();
		$this->setting->show_forms();

		echo '</div>';
	}

	/**
	 * Registers settings section and fields
	 */
	public function init_option() {
		$sections = array(
			array(
				'id'    => 'aw_sms_opt',
				'title' => __( 'همگانی', 'wp-reviews-insurance' )
			)
		);

		$help_text = "از موارد ذیل میتوانید استفاده کنید:";
		$help_text .= "<br />";
		$help_text .= "[id] : شناسه تیکت";
		$help_text .= "<br />";
		$help_text .= "[title] : عنوان تیکت";
		$help_text .= "<br />";
		$help_text .= "[sender_name] : نام ارسال کننده";
		$help_text .= "<br />";
		$help_text .= "[receiver_name] : نام دریافت کننده";
		$help_text .= "<br />";
		$help_text .= "[property] : عنوان ویژگی";
		$help_text .= "<br />";
		$help_text .= "[department] : عنوان دپارتمان";
		$help_text .= "<br />";
		$help_text .= "[date] : تاریخ";
		$help_text .= "<br />";

		$fields = array(
			'aw_sms_opt' => array(
				array(
					'name'    => 'send_to_admin',
					'label'   => __( 'ارسال به مدیر', 'wp-reviews-insurance' ),
					'type'    => 'select',
					'desc'    => 'آیا به مدیر پیامک ارسال شود ؟',
					'options' => array(
						'0' => 'خیر',
						'1' => 'آری'
					)
				),
				array(
					'name'    => 'modir_mobile',
					'label'   => __( 'شماره همراه مدیر', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => ''
				),

				array(
					'name'    => 'send_to_sender',
					'label'   => __( 'ارسال پیامک به شخص ارسال کننده', 'wp-reviews-insurance' ),
					'type'    => 'select',
					'desc'    => '',
					'options' => array(
						'0' => 'خیر',
						'1' => 'آری'
					)
				),
				array(
					'name'    => 'send_to_receiver',
					'label'   => __( 'ارسال پیامک به شخص دریافت کننده', 'wp-reviews-insurance' ),
					'type'    => 'select',
					'desc'    => '',
					'options' => array(
						'0' => 'خیر',
						'1' => 'آری'
					)
				),
				array(
					'name'    => 'user_meta_modir',
					'label'   => __( 'متا موبایل کابران در سایت', 'wp-reviews-insurance' ),
					'type'    => 'text',
					'default' => 'sd_wpas_mobile_phone'
				),
				array(
					'name'    => 'text_sms',
					'label'   => __( 'متن پیامک', 'wp-reviews-insurance' ),
					'type'    => 'textarea',
					'default' => 'تیکت [id] با عنوان [title] توسط [sender_name] برای [receiver_name] در تاریخ [date] با فوریت [property] ارسال گردید.',
					'desc'    => $help_text,
				)
			)
		);

		$this->setting = new SettingAPI();

		//set sections and fields
		$this->setting->set_sections( $sections );
		$this->setting->set_fields( $fields );

		//initialize them
		$this->setting->admin_init();
	}

}