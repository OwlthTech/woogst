<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/public
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/woogst-public-functions.php';

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woogst
 * @subpackage Woogst/public
 * @author     Owlth Tech <owlthtech@gmail.com>
 */
class Woogst_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		/**
             * Checkout - frontend
             */
            // Add in checkout billing fields
            add_filter('woocommerce_billing_fields', 'gst_fields_add_in_checkout_billing_fields');
            // Remove optional label
            add_filter('woocommerce_form_field', 'remove_optional_text_from_gst_fields', 10, 4);
            // Sanitise and Validate
            add_filter('woocommerce_checkout_process', 'gst_fields_sanitize_and_validate');
            // Save in order meta during checkout
            add_action('woocommerce_checkout_update_order_meta', 'gst_fields_save_in_order_meta');
            // Add GST details (echo) html in email
            add_action('woocommerce_email_order_meta', 'gst_fields_add_in_email_display', 10, 3);
            // VAT to GST replacement
            add_filter('gettext', 'vat_to_gst_replacement', 20, 3);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woogst-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woogst-public.js', array( 'jquery' ), $this->version, false );
		if (is_checkout()) {
			wp_enqueue_script('gst-validation', plugin_dir_url( __FILE__ ) . '/js/checkout-validations.js', array('jquery'), $this->version, true);
	  	}
	}

}
