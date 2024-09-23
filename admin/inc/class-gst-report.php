<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}


class GstReport
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

    public function __construct()
    {
        // Hook into the 'init' action to ensure scheduling works when the plugin is activated
        add_filter( 'cron_schedules', [ $this,'schedule_report_recurrence'] );
        add_action('init', [ $this, 'schedule_report' ]);

        // Hook into the actual scheduled event
        add_action('woogst_send_monthly_tax_report', [ $this, 'generate_and_send_report' ]);
    }

    /**
     * Schedule the monthly report if not already scheduled.
     */
    // Custom Cron Recurrences
function schedule_report_recurrence( $schedules ) {
	$schedules['monthly'] = array(
		'display' => __( 'Once monthly', 'textdomain' ),
		'interval' => '',
	);
	return $schedules;
}


// Schedule Cron Job Event
function schedule_report() {
	if ( ! wp_next_scheduled( 'woogst_send_monthly_tax_report' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'monthly', 'woogst_send_monthly_tax_report' );
        $this->log_report_status('Scheduled monthly tax report');
	}
}


    // public function schedule_report()
    // {
    //     $this->log_report_status('schedule_report method called.');
    //     if (!wp_next_scheduled('woogst_send_monthly_tax_report')) {
    //         $this->log_report_status('woogst_send_monthly_tax_report not found.');
    //         // Get the WordPress time zone
    //         $timezone = new DateTimeZone(wp_timezone_string());
    //         // Schedule the event for the first day of the next month at 08:00 AM
    //         $first_day_of_next_month = new DateTime('first day of next month 08:00', $timezone);
    //         $timestamp = $first_day_of_next_month->getTimestamp();

    //         // Schedule the event
    //         wp_schedule_event($timestamp, 'monthly', 'woogst_send_monthly_tax_report');
    //     }
    // }

    /**
     * Generate and send the report via email.
     */
    public function generate_and_send_report()
    {
        $order_ids = $this->get_monthly_orders();

        // Prepare email content and generate CSV
        $email_content = $this->prepare_email_content($order_ids);
        $csv_file_path = $this->generate_csv($order_ids);

        // Send the email with CSV attachment
        $to = 'youremail@example.com';
        $subject = 'Monthly WooCommerce Tax Report';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $attachments = array($csv_file_path);
        $sent = wp_mail($to, $subject, $email_content, $headers, $attachments);

        if ($sent) {
            $this->log_report_status('Tax report email sent successfully with CSV.');
        } else {
            $this->log_report_status('Failed to send tax report email.');
        }

        // Cleanup CSV file
        if (file_exists($csv_file_path)) {
            unlink($csv_file_path);
        }
    }



    // Function to fetch WooCommerce orders for the previous month and include custom meta fields
    function get_monthly_orders()
    {
        // Get the previous month's start and end dates
        $first_day_of_last_month = new DateTime('first day of last month 00:00:00');
        $last_day_of_last_month = new DateTime('last day of last month 23:59:59');

        // Query to get orders from the previous month
        $args = array(
            'post_type' => 'shop_order',
            'post_status' => array_keys(wc_get_order_statuses()),
            'date_query' => array(
                'after' => $first_day_of_last_month->format('Y-m-d H:i:s'),
                'before' => $last_day_of_last_month->format('Y-m-d H:i:s'),
                'inclusive' => true,
            ),
            'posts_per_page' => -1, // Retrieve all orders
        );

        $orders = get_posts($args);

        // Array to store the order details with custom meta fields
        $order_data = array();

        foreach ($orders as $order_post) {
            $order = wc_get_order($order_post->ID);

            // Check if HPOS (High-Performance Order Storage) is enabled and use the appropriate functions
            $order_id = $order->get_id();
            $order_total = $order->get_total();
            $tax_total = $order->get_total_tax();
            $order_date = $order->get_date_created()->date('Y-m-d H:i:s');

            // Get custom meta fields (_billing_gst_number, _billing_gst_trade_name)
            $billing_gst_number = $order->get_meta('_billing_gst_number', true);
            $billing_gst_trade_name = $order->get_meta('_billing_gst_trade_name', true);

            // Add the data to the array
            $order_data[] = array(
                'order_id' => $order_id,
                'order_total' => $order_total,
                'tax_total' => $tax_total,
                'billing_gst_number' => $billing_gst_number,
                'billing_gst_trade_name' => $billing_gst_trade_name,
                'order_date' => $order_date,
            );
        }

        return $order_data;
    }

    // Function to prepare the email content
    function prepare_email_content($orders)
    {
        return "Please find attached the monthly tax report for WooCommerce orders, including GST number and trade name.";
    }

    // Function to generate the CSV file with custom meta fields
    function generate_csv($orders)
    {
        // File path for the CSV
        $upload_dir = wp_upload_dir();
        $csv_file_path = $upload_dir['basedir'] . '/woogst-tax-report-' . date('Y-m') . '.csv';

        // Open file for writing
        $file = fopen($csv_file_path, 'w');

        // Add CSV headers
        fputcsv($file, array('Order ID', 'Order Total', 'Tax Total', 'Billing GST Number', 'Billing GST Trade Name', 'Order Date'));

        // Add rows to the CSV
        foreach ($orders as $order) {
            fputcsv($file, array(
                $order['order_id'],
                $order['order_total'],
                $order['tax_total'],
                $order['billing_gst_number'],
                $order['billing_gst_trade_name'],
                $order['order_date']
            ));
        }

        // Close the file
        fclose($file);

        return $csv_file_path;
    }

    // Log the status of the email process
    function log_report_status($message)
    {
        if (!file_exists(plugin_dir_path(__FILE__) . 'logs')) {
            mkdir(plugin_dir_path(__FILE__) . 'logs', 0755, true);
        }

        $log_file = plugin_dir_path(__FILE__) . 'logs/woogst-tax-report-log.txt';
        $time = date('Y-m-d H:i:s');
        $log_message = "[{$time}] - {$message}" . PHP_EOL;

        file_put_contents($log_file, $log_message, FILE_APPEND);
    }

}


// The main instance
if (!function_exists('woogst_report')) {
    /**
     * Return instance of  WooInvoice class
     *
     * @since 1.0.0
     *
     * @return GstReport
     */
    function woogst_report()
    {//phpcs:ignore
        return GstReport::get_instance();
    }
}