<?php

class Woogst_Order_Table
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

      public function init()
      {
            /**
             * Woocommerce > Orders
             */
            $is_hpos = is_hpos_enabled();
            error_log($is_hpos);
            if ($is_hpos) {
                  add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'woogst_custom_order_column'), 20);
            } else {
                  add_filter('manage_edit-shop_order_columns', array($this, 'woogst_custom_order_column'), 20);
            }

            if ($is_hpos) {
                  add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'woogst_custom_order_column_content'), 10, 2);
            } else {
                  add_action('manage_shop_order_posts_custom_column', array($this, 'woogst_custom_order_column_content'), 20, 2);
            }

            // Filer in Order list for B2B orders
            add_action('woocommerce_order_list_table_restrict_manage_orders', array($this, 'woogst_order_table_filter'), 5);
            add_filter('woocommerce_order_query_args', array($this, 'woogst_order_table_filter_query_args'));
      }

      /**
       * Order list - column header
       * @since 1.0.0
       * @param mixed $columns
       * @return array
       */
      public function woogst_custom_order_column($columns)
      {
            $reordered_columns = array();

            // Inserting columns to a specific location
            foreach ($columns as $key => $column) {
                  $reordered_columns[$key] = $column;
                  if ($key == 'order_status') {
                        // Inserting after "Status" column
                        $reordered_columns['gst_claim'] = __('GST Claimed', 'theme_domain');
                        $reordered_columns['gst_details'] = __('GST Details', 'theme_domain');
                  }
            }
            return $reordered_columns;
      }

      /**
       * Order list - column data
       * @since 1.0.0
       * @param mixed $column
       * @param mixed $order
       * @return void
       */
      public function woogst_custom_order_column_content($column, $order)
      {
            $claim_gst = $order->get_meta('_billing_claim_gst', true);
            switch ($column) {
                  case 'gst_claim':
                        echo $claim_gst ? "✔️" : "❌";
                        break;

                  case 'gst_details':
                        if ($claim_gst) {
                              $gst_holder_name = $order->get_meta('_billing_gst_trade_name', true);

                              $gst_number = $order->get_meta('_billing_gst_number', true);
                        }

                        if (!empty($gst_holder_name)) {
                              echo "<strong>Trade name:</strong> " . $gst_holder_name . "<br/>";
                        }
                        if (!empty($gst_number)) {
                              echo "<strong>GST No:</strong> " . $gst_number;
                        } else {
                              echo '<small>-</small>';
                        }
                        break;

            }
      }

      /**
       * Adds filter for order-type b2b/b2c orders
       * @since 1.0.0
       * @return void
       */
      public function woogst_order_table_filter()
      {
            $selected = isset($_GET['order-type']) ? esc_attr($_GET['order-type']) : '';
            $options = array(
                  '' => __('All Types', 'woocommerce'),
                  'b2c_orders' => __('B2C Orders', 'woocommerce'),
                  'b2b_orders' => __('B2B Orders', 'woocommerce'),
            );

            echo '<select name="order-type" id="dropdown_shop_order_type">';
            foreach ($options as $value => $label_name) {
                  printf(
                        '<option value="%s" %s>%s</option>',
                        $value,
                        selected($selected, $value, false),
                        $label_name
                  );
            }
            echo '</select>';
      }

      /**
       * Prepares query argument for order-type (b2b/b2c) orders 
       * @param mixed $query
       * @return mixed
       */
      public function woogst_order_table_filter_query_args($query)
      {
            if (isset($_GET['order-type']) && $_GET['order-type'] === 'b2b_orders') {
                  $query['meta_key'] = '_billing_claim_gst';
                  $query['meta_value'] = '1';
            }
            if (isset($_GET['order-type']) && $_GET['order-type'] === 'b2c_orders') {
                  // Filter orders where '_billing_claim_gst' is '0' or does not exist
                  $query['meta_query'] = array(
                        'relation' => 'OR',
                        array(
                              'key' => '_billing_claim_gst',
                              'value' => '0',
                              'compare' => '='
                        ),
                        array(
                              'key' => '_billing_claim_gst',
                              'compare' => 'NOT EXISTS'
                        ),
                  );
            }
            return $query;
      }

}

// The main instance
if (!function_exists('woogst_order_table')) {
      /**
       * Return instance of Woogst_Order_Table class
       *
       * @since 1.0.0
       *
       * @return Woogst_Order_Table
       */
      function woogst_order_table()
      {//phpcs:ignore
            return Woogst_Order_Table::get_instance();
      }
}
