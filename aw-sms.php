<?php
/**
 * Plugin Name: Awesome Support SMS system
 * Description: Awesome Support SMS system
 * Plugin URI:  https://realwp.net
 * Version:     1.0.0
 * Author:      Mehrshad Darzi
 * Author URI:  https://realwp.net
 * License:     MIT
 * Text Domain: aw-sms
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AW_SMS {

	/*
	|--------------------------------------------------------------------------
	| Plugin ENVIRONMENT
	|--------------------------------------------------------------------------
	|
	| This array of class aliases will be registered when this application
	|
	*/
	public static $ENVIRONMENT = 'production';

	/**
	 * Use Template Engine
	 * if you want use template Engine Please add dir name
	 *
	 * @var string / dir name
	 * @status Core
	 */
	public static $Template_Engine = 'aw-sms';

	/**
	 * Minimum PHP version required
	 *
	 * @var string
	 */
	private $min_php = '5.4.0';

	/**
	 * Use plugin's translated strings
	 *
	 * @var string
	 * @default true
	 */
	public static $use_i18n = true;

	/**
	 * Plugin Option Store
	 * if Plugin Used Option API set list of option key to store this variable.
	 *
	 * @see \AW_SMS\admin\Settings
	 * @var array
	 * @status Optional
	 */
	public static $option = array( 'aw_sms_opt' );

	/*
	|--------------------------------------------------------------------------
	| Autoloaded Service Providers
	|--------------------------------------------------------------------------
	|
	| The service providers listed here will be automatically loaded on the request to your Plugin.
	*/
	public static $providers = array(
		//'admin\Settings',
		//'admin\Admin',
		//'Front',
		//'Ajax',
		//'core\\Utility'
	);

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_url;

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_path;

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 * @status Core
	 */
	public static $plugin_version;

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @status Core
	 */
	protected static $_instance = null;

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.13
	 * @return  object of this class
	 */
	public static function instance() {
		null === self::$_instance and self::$_instance = new self;
		return self::$_instance;
	}

	/**
	 * AW_SMS constructor.
	 */
	public function __construct() {

		/*
		 * Check Require Php Version
		 */
		if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
			return;
		}

		/*
		 * Define Variable
		 */
		$this->define_constants();

		/*
		 * include files
		 */
		$this->includes();

		/*
		 * init Wordpress hook
		 */
		$this->init_hooks();

		/*
		 * Plugin Loaded Action
		 */
		do_action( 'aw_sms_loaded' );
	}

	/**
	 * Define Constant
	 */
	public function define_constants() {

		/*
		 * Get Plugin Data
		 */
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( __FILE__ );

		/*
		 * Set Plugin Version
		 */
		self::$plugin_version = $plugin_data['Version'];

		/*
		 * Set Plugin Url
		 */
		self::$plugin_url = plugins_url( '', __FILE__ );

		/*
		 * Set Plugin Path
		 */
		self::$plugin_path = plugin_dir_path( __FILE__ );

		/*
		 * Set Plugin Option
		 */
		if ( is_array( self::$option ) and count( self::$option ) > 0 ) {
			$option = array();
			foreach ( self::$option as $opt ) {
				$option_value = get_option( $opt );
				if ( isset( $option_value ) and ! empty( $option_value ) ) {
					$option[ $opt ] = $option_value;
				}
			}
			self::$option = $option;
		}
	}

	/**
	 * include Plugin Require File
	 */
	public function includes() {

		/*
		 * autoload plugin files
		 */
		include_once dirname( __FILE__ ) . '/inc/core/settingapi.php';
		include_once dirname( __FILE__ ) . '/inc/admin/admin.php';
		include_once dirname( __FILE__ ) . '/inc/admin/settings.php';

		/*
		 * Load List Of classes
		 */
		new AW_SMS\admin\Settings();
		new AW_SMS\admin\Admin();

	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook init Hook
	 * @return  void
	 */
	public function init_hooks() {}

	/**
	 * Show notice about PHP version
	 *
	 * @return void
	 */
	function php_version_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$error = __( 'Your installed PHP Version is: ', 'aw-sms' ) . PHP_VERSION . '. ';
		$error .= __( 'The <strong>AW SMS</strong> plugin requires PHP version <strong>', 'aw-sms' ) . $this->min_php . __( '</strong> or greater.', 'aw-sms' );
		?>
        <div class="error">
            <p><?php printf( $error ); ?></p>
        </div>
		<?php
	}

}

/**
 * Main instance of WP_Plugin.
 *
 * @since  1.1.0
 */
function aw_sms_plugin() {
	return AW_SMS::instance();
}

// Global for backwards compatibility.
$GLOBALS['aw-sms'] = aw_sms_plugin();
