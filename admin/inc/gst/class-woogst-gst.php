<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
      exit;
}


class Gst
{
      /**
       * Gets an instance of this object.
       * Prevents duplicate instances which avoid artefacts and improves performance.
       *
       * @static
       * @access public
       * @return object
       * @since 1.0.0
       */
      public static function get_instance()
      {
            // Store the instance locally to avoid private static replication.
            static $instance = null;

            // Only run these methods if they haven't been ran previously.
            if (null === $instance) {
                  $instance = new self();
            }

            // Always return the instance.
            return $instance;
      }

      /**
       * Intantiate - WooCommerce > Orders table customizations
       * Intantiate - WooCommerce > Orders > edit single order customizations
       * Intantiate - 'gst-reports' actions and hooks
       * @return void
       */
      public function init()
      {
            /**
             * Intantiate - WooCommerce > Orders table customizations
             */
            $woo_order_table = woogst_order_table();
            $woo_order_table->init();

            /**
             * Intantiate - WooCommerce > Orders > edit single order customizations
             */
            $woo_single_order = woogst_order_edit();
            $woo_single_order->init();

            /**
             * Intantiate - 'gst-reports' actions and hooks
             */
            $woogst_admin_gst_report_table = woogst_gst_report_table();
            $woogst_admin_gst_report_table->init();
      }


}

// The main instance
if (!function_exists('woogst_gst')) {
      /**
       * Return instance of Gst class
       *
       * @since 1.0.0
       *
       * @return Gst
       */
      function woogst_gst()
      {//phpcs:ignore
            return Gst::get_instance();
      }
}
