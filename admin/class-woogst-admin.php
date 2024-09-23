<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woogst
 * @subpackage Woogst/admin
 * @author     Owlth Tech <owlthtech@gmail.com>
 */

class Woogst_Admin
{


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Add custom columns to order list
		add_filter('manage_edit-shop_order_columns', 'add_admin_order_list_custom_column', 20);
		add_filter('manage_woocommerce_page_wc-orders_columns', 'add_admin_order_list_custom_column', 20);


		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woogst_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woogst_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$has_woocomemrce = class_exists('WooCommerce');
		if (!$has_woocomemrce) {
			return;
		}
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ($screen_id === wc_get_page_screen_id('shop-order')) {
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woogst-admin.css', array(), $this->version, 'all');
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woogst_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woogst_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$has_woocomemrce = class_exists('WooCommerce');
		if (!$has_woocomemrce) {
			return;
		}
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Check screen base and page
		if (class_exists('WooCommerce') && $screen_id === wc_get_page_screen_id('shop-order')) {
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woogst-admin.js', array('jquery'), $this->version, false);
		}

	}

	
}


