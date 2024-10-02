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
		 * Add gst fields in checkout
		 * Possible hooks
		 * 'woocommerce_after_checkout_billing_form'	- default
		 * 'woocommerce_review_order_before_submit'
		 */
		add_action('woocommerce_after_checkout_billing_form', array($this, 'woogst_checkout_gst_fields'));
            // Remove optional label
            add_filter('woocommerce_form_field', array($this, 'remove_optional_text_from_gst_fields'), 10, 4);
            // Sanitise and Validate
            add_filter('woocommerce_checkout_process', array($this, 'gst_fields_sanitize_and_validate'));
            // Save in order meta during checkout
            add_action('woocommerce_checkout_update_order_meta', array($this, 'gst_fields_save_in_order_meta'));
            // Add GST details (echo) html in email
            add_action('woocommerce_email_order_meta', array($this, 'gst_fields_add_in_email_display'), 10, 3);
            // VAT to GST replacement
            add_filter('gettext', array($this, 'vat_to_gst_replacement'), 20, 3);

	}

	/**
	 * Registers the stylesheets for the public-facing side of the site.
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
	 * Registers the JavaScript for the public-facing side of the site.
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

	/**
	 * Adds GST fields in checkout page
	 * @since    1.0.0
	 * @param mixed $checkout
	 * @return void
	 */
	public function woogst_checkout_gst_fields($checkout)
	{
		if (get_woocommerce_currency() == "INR" && WC()->session->get('customer')['shipping_country'] == "IN") {
			$checkout = WC()->checkout();
			// Add "Claim your GST" checkbox
			woocommerce_form_field(
				'billing_claim_gst',
				array(
					'type' => 'checkbox',
					'label' => __('Use GSTIN for claiming input tax', 'woocommerce'),
					'required' => false,
					'class' => array('form-row-wide'),
					'clear' => true,
				)
			);
			echo '<div id="gst_fields" style="display:none;">';
			// Add Trade Name field (initially hidden)
			woocommerce_form_field(
				'billing_gst_trade_name',
				array(
					'type' => 'text',
					'label' => __('Trade Name', 'woocommerce'),
					'placeholder' => _x('Trade Name as per GSTIN registration', 'placeholder', 'woocommerce'),
					'required' => false,
					'class' => array('form-row-wide', 'gst-field'), // Hidden initially if needed
					'clear' => true,
				),
				$checkout->get_value('billing_gst_trade_name') // Prepopulate with existing value if available
			);
	
			// Add GST Number field
			woocommerce_form_field(
				'billing_gst_number',
				array(
					'type' => 'text',
					'label' => __('GST Number', 'woocommerce'),
					'placeholder' => _x('Enter your GSTIN registration number', 'placeholder', 'woocommerce'),
					'required' => false,
					'class' => array('form-row-wide', 'gst-field'),
					'clear' => true,
				),
				$checkout->get_value('billing_gst_number') // Prepopulate with existing value if available
			);
			echo '</div>';
		}
	}

	/**
	 * Removes (optional) text from label of the checkout fields
	 * @since    1.0.0
	 * @param mixed $field
	 * @param mixed $key
	 * @param mixed $args
	 * @param mixed $value
	 * @return mixed
	 */
	public function remove_optional_text_from_gst_fields($field, $key, $args, $value)
      {
            // Check if the field is either the GST Holder Name or GST Number
            if ($key === 'billing_gst_trade_name' || $key === 'billing_gst_number') {
                  // Remove '(optional)' text if present in the label
                  if (strpos($field, '(optional)') !== false) {
                        $field = str_replace('(optional)', '', $field);
                  }
            }
            return $field;
      }

	/**
	 * Sanitizes and validates GST number during checkout process
	 * @since    1.0.0
	 * @return void
	 */
	public function gst_fields_sanitize_and_validate()
      {
            error_log(print_r($_POST, true));
            // billing_claim_gst checkbox
            if (!isset($_POST['billing_claim_gst']) || intval($_POST['billing_claim_gst']) != 1) {
                  // If the checkbox is not checked, unset GST fields
                  unset($_POST['billing_gst_number']);
                  unset($_POST['billing_gst_trade_name']);
            } else {
                  if (!isset($_POST['billing_gst_number']) || empty($_POST['billing_gst_number'])) {
                        wc_add_notice(__('GSTIN number is required to claim GST.', 'woogst'), 'error');
                  }
                  if (isset($_POST['billing_gst_number']) && !empty($_POST['billing_gst_number'])) {
                        $gst_number = strtoupper(sanitize_text_field($_POST['billing_gst_number']));
                        if (!validate_gst_number($gst_number)) {
                              wc_add_notice(__('GSTIN number is invalid.', 'woogst'), 'error');
                        }
                  }
                  // Check if GST holder name is provided (assuming you have a field for it)
                  if (!isset($_POST['billing_gst_trade_name']) || empty($_POST['billing_gst_trade_name'])) {
                        wc_add_notice(__('GST trade name is required.', 'woogst'), 'error');
                  }
            }
      }

	/**
	 * Saves gst fields into order meta during new checkout order
	 * @since    1.0.0
	 * @param mixed $order_id
	 * @return void
	 */
	public function gst_fields_save_in_order_meta($order_id)
      {
            // _billing_claim_gst
            $gst_claim = isset($_POST['billing_claim_gst']) ? $_POST['billing_claim_gst'] : '';
            $gst_holder_name = isset($_POST['billing_gst_trade_name']) ? sanitize_text_field($_POST['billing_gst_trade_name']) : '';
            $gst_number = isset($_POST['billing_gst_number']) ? sanitize_text_field($_POST['billing_gst_number']) : '';
            if (!empty($gst_claim) && !empty($gst_holder_name) && !empty($gst_number)) {
                  $order = wc_get_order($order_id);
                  $order->update_meta_data('_billing_claim_gst', $gst_claim);
                  $order->update_meta_data('_billing_gst_trade_name', $gst_holder_name);
                  $order->update_meta_data('_billing_gst_number', $gst_number);
                  $order->save();
            }
      }

	/**
	 * Adds GST fields into Email
	 * @since    1.0.0
	 * @param mixed $order
	 * @param mixed $sent_to_admin
	 * @param mixed $plain_text
	 * @return void
	 */
	public function gst_fields_add_in_email_display($order, $sent_to_admin = false, $plain_text = false)
      {
            if (is_a($order, 'WC_Order')) {
                  $gst_holder_name = $order->get_meta('_billing_gst_trade_name');
                  $gst_number = $order->get_meta('_billing_gst_number');

                  if ($gst_number || $gst_holder_name) {
                        echo '<p><strong>' . __('GST Information') . ':</strong><br>';
                        if ($gst_holder_name) {
                              echo __('GST Holder Name: ') . esc_html($gst_holder_name) . '<br>';
                        }
                        if ($gst_number) {
                              echo __('GST Number: ') . esc_html($gst_number) . '</p>';
                        }
                  }
            }
      }

	/**
	 * Replaces VAT to GST
	 * @param mixed $translated_text
	 * @param mixed $text
	 * @return mixed
	 */
	public function vat_to_gst_replacement($translated_text, $text)
      {
            if ($text === 'VAT') {
                  $translated_text = 'GST';
            }
            return $translated_text;
      }

}
