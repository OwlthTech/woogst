<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woogst
 * @subpackage Woogst/includes
 * @author     Owlth Tech <owlthtech@gmail.com>
 */
class Woogst {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woogst_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOOGST_VERSION' ) ) {
			$this->version = WOOGST_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		
		$this->plugin_name = 'woogst';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->validate_woo_gst();
		$this->define_invoice_hooks();
		$this->define_gst_report_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woogst_Loader. Orchestrates the hooks of the plugin.
	 * - Woogst_i18n. Defines internationalization functionality.
	 * - Woogst_Admin. Defines all hooks for the admin area.
	 * - Woogst_Public. Defines all hooks for the public side of the site.
	 * - Gst. Defines all gst related function to perform.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woogst-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woogst-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woogst-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woogst-public.php';

		/**
		 * Admin inc files
		 */
		// GST
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/gst/class-woogst-gst.php';
		// Invoice
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/invoice/class-woogst-invoice.php';
		// Orders
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/orders/class-woogst-order-edit.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/orders/class-woogst-order-table.php';
		// Reports
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/reports/class-woogst-report.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/inc/reports/class-woogst-report-table.php';

		/**
		 * Utils
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'utils/class-woogst-validator.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'utils/functions-logging.php';

		$this->loader = new Woogst_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woogst_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Woogst_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woogst_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woogst_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Defines reports
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_gst_report_hooks() {
		$woo_gst_report = woogst_report();
		$this->loader->add_action('init', $woo_gst_report, 'init');
	}

	/**
	 * Defines invoice
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_invoice_hooks() {
		$woo_invoice = woogst_invoice();
		$this->loader->add_action('init', $woo_invoice, 'init');
	}

	/**
	 * Adds HPOS Compatibility
	 * @since    1.0.0
	 * @access   private
	 */
	private function validate_woo_gst() {
		$woogst_validator = woogst_validator();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woogst_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
