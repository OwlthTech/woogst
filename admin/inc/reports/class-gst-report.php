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

    protected $reports_directory;

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
        add_action('wp', array($this, 'schedule_report'));

        // Hook to run the monthly report generation
        add_action('woogst_send_monthly_tax_report', array($this, 'generate_save_and_send_report'));

        // Test action
        add_action('wp_loaded', array($this, 'generate_save_and_send_report'));

    }

    
    /**
     * Schedule the monthly report if not already scheduled.
     */
    public function schedule_report_recurrence($schedules)
    {
        $schedules['monthly'] = array(
            'display' => __('Once monthly', 'textdomain'),
            'interval' => '',
        );
        return $schedules;
    }

    // Display a dismissible admin notice after cron is scheduled for the first time
    public function email_report_schedule_notice()
    {
        // Check if the cron job has been scheduled and if we need to display the notice
        $timestamp = wp_next_scheduled('woogst_send_monthly_tax_report');
        $cron_scheduled_notice_dismissed = get_option('woogst_cron_scheduled_notice_dismissed');

        if ($timestamp && !$cron_scheduled_notice_dismissed) {
            // Get the WordPress timezone
            $wp_timezone = wp_timezone();

            // Convert the timestamp to the correct timezone
            $scheduled_time = new DateTime('@' . $timestamp);
            $scheduled_time->setTimezone($wp_timezone);

            // Display the notice with the correct time in WordPress timezone
            echo '<div class="notice notice-success is-dismissible" id="woogst-cron-notice"><p>Cron job is scheduled to run at: ' . $scheduled_time->format('Y-m-d H:i:s') . '</p></div>';
        }
    }

    public function schedule_report()
    {
        // Clear any existing schedule for this event (optional but useful for testing)
        // wp_clear_scheduled_hook('woogst_send_monthly_tax_report');

        log_report_status('schedule_report method called.');
        if (!wp_next_scheduled('woogst_send_monthly_tax_report')) {
            log_report_status('setting new woogst_send_monthly_tax_report.');

            // Get the WordPress time zone - [production]
            $timezone = new DateTimeZone(wp_timezone_string());
            // Schedule the event for the first day of the next month at 08:00 AM
            // $first_day_of_next_month = new DateTime('first day of next month 08:00', $timezone);
            // $timestamp = $first_day_of_next_month->getTimestamp();


            // Get the current time in the WordPress timezone
            $now = new DateTime('now', $timezone);

            // Set the event to trigger at the next minute mark for testing
            $timestamp = $now->modify('+1 minute')->getTimestamp();
            // Schedule the event
            $create_event = wp_schedule_event($timestamp, 'monthly', 'woogst_send_monthly_tax_report');

            if ($create_event) {
                add_action('admin_notices', array($this, 'email_report_schedule_notice'));
            }
        }

    }

    /**
     * Generate, save and send the report via email.
     */
    public function woogst_report_handler($generate = null, $send_email = null, $month = null, $year = null)
    {
        if (is_null($generate)) {
            $generate = true;
        }
        if (is_null($send_email)) {
            $send_email = true;
        }

        $order_data = $this->get_monthly_orders($month, $year);
        $simplified_order_data = array_map(function ($order) {
            return array(
                'order_id' => $order['order_id'],  // Only store order IDs
                'order_total' => $order['order_total'], // Optionally store total amount
            );
        }, $order_data);

        if ($generate) {
            $csv_file_path = $this->generate_csv($order_data, $month, $year);
        }

        if ($send_email) {
            // Send the email with CSV attachment
            $to = 'owlthtech@gmail.com';
            $subject = 'Orgotel Organic - Previous Month Online Order Invoices';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $attachments = array($csv_file_path);
            $body_content = $this->prepare_email_content($order_data);
            // $sent = wp_mail($to, $subject, $body_content, $headers, $attachments);
            $sent = true;
            // if($sent) {
            //     log_report_status('email sent');
            // } else {
            //     log_report_status('email failed');
            // }
        }

        $report_id = wp_insert_post(array(
            'post_type' => 'gst-reports',
            'post_title' => 'Order Report for ' . date('F Y'),  // Set the title as the current month
            'post_content' => $body_content,  // Save the email content in the post
            'post_status' => 'publish',
            'meta_input' => array(
                'csv_file_path' => esc_url_raw($csv_file_path),  // Save the path of the CSV file as meta data
                'email_status' => sanitize_text_field($sent),   // Save the email status as meta data
                'order_data' => $simplified_order_data,  // Save the order IDs as meta data
            ),
        ));

    }
    public function generate_save_and_send_report($month = null, $year = null)
    {
        if (isset($_GET['test_report'])) {
            echo "triggered -> generate_save_and_send_report()";
            $order_data = $this->get_monthly_orders($month, $year);

            // Extract order IDs and other relevant details from order data for storage
            $simplified_order_data = array_map(function ($order) {
                return array(
                    'order_id' => $order['order_id'],  // Only store order IDs
                    'order_total' => $order['order_total'], // Optionally store total amount
                );
            }, $order_data);

            // Prepare email content and generate CSV
            $email_content = $this->prepare_email_content($order_data);
            $csv_file_path = $this->generate_csv($order_data, $month, $year);

            // Send the email with CSV attachment
            $to = 'owlthtech@gmail.com';
            $subject = 'Orgotel Organic - Previous Month Online Order Invoices';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $attachments = array($csv_file_path);
            // $sent = wp_mail($to, $subject, $email_content, $headers, $attachments);
            $sent = true;
            // // Log email status
            $email_status = $sent ? '1' : '0';

            // if($sent) {
            //     log_report_status('email sent');
            // } else {
            //     log_report_status('email failed');
            // }

            // Log the report details in 'gst-reports' CPT
            $report_id = wp_insert_post(array(
                'post_type' => 'gst-reports',
                'post_title' => 'Order Report for ' . date('F Y'),
                'post_content' => $email_content,
                'post_status' => 'private',
                'meta_input' => array(
                    'csv_file_path' => esc_url_raw($csv_file_path),
                    'email_status' => sanitize_text_field($email_status),
                    'order_data' => $simplified_order_data,
                ),
            ));

            log_report_status("Order report post is created -> " . $report_id);

            // Cleanup CSV file
            if (file_exists($csv_file_path)) {
                unlink($csv_file_path);
            }
        }
    }

    // Function to fetch WooCommerce orders for the previous month and include custom meta fields
    function get_monthly_orders($month = null, $year = null)
    {
        // Use previous month and current year as default if no values are passed
        if (empty($month) || $month < 1 || $month > 12) {
            $month = date('m', strtotime('first day of last month'));
        }

        if (empty($year) || $year < 1970 || $year > date('Y')) {
            $year = date('Y', strtotime('first day of last month'));
        }

        // Create valid DateTime objects for the first and last day of the month
        try {
            $first_day_of_month = new DateTime("$year-$month-01 00:00:00");
            $last_day_of_month = new DateTime($first_day_of_month->format('Y-m-t 23:59:59'));
        } catch (Exception $e) {
            return new WP_Error('invalid_date', __('Invalid date provided', 'woogst'));
        }

        // Query to get orders from the specified month
        $args = array(
            'post_type' => 'shop_order',
            'post_status' => array_keys(wc_get_order_statuses()),
            'date_query' => array(
                'after' => $first_day_of_month->format('Y-m-d H:i:s'),
                'before' => $last_day_of_month->format('Y-m-d H:i:s'),
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
        return "Please find attached monthly tax report for orgotel.com orders. Please contact us for any further query.";
    }

    // Function to generate the CSV file with custom meta fields
    function generate_csv($orders, $month = null, $year = null)
    {
        // If no month and year are passed, use the previous month and current year as default
        if (is_null($month)) {
            $month = date('m', strtotime('first day of last month'));
        }

        if (is_null($year)) {
            $year = date('Y', strtotime('first day of last month'));
        }

        // Set the file name based on the passed or default month and year
        $file_name = "woogst-tax-report-{$year}-{$month}.csv";
        $reports_directory = get_report_directory();
        $csv_file_path = "{$reports_directory}{$file_name}";
        // Open file for writing
        $file = fopen($csv_file_path, 'w');
        if ($file === false) {
            return false; // Handle error if file opening fails
        }

        // Add CSV headers
        fputcsv($file, array('Order ID', 'Billing GST Number', 'Billing GST Trade Name', 'Order Total', 'Tax Total', 'Order Date'));

        // Add rows to the CSV
        foreach ($orders as $order) {
            fputcsv($file, array(
                $order['order_id'],
                $order['billing_gst_number'],
                $order['billing_gst_trade_name'],
                $order['order_total'],
                $order['tax_total'],
                $order['order_date']
            ));
        }

        // Close the file
        fclose($file);

        // Create the public URL for the CSV file
        $upload_dir = wp_upload_dir();
        $csv_file_url = $upload_dir['baseurl'] . '/order-reports/' . $file_name;

        log_report_status("CSV generated and saved. Path -> $csv_file_url");
        return $csv_file_url;
    }

}

if(!function_exists('get_report_directory')) {
    function get_report_directory()
    {
        // File path for the CSV
        $upload_dir = wp_upload_dir();
        // Check if the order_reports directory exists; if not, create it
        $reports_directory = $upload_dir['basedir'] . '/order-reports/';
        // Ensure that the custom order_reports directory exists
        if (!file_exists($reports_directory)) {
            if (!mkdir($reports_directory, 0755, true)) {
                error_log("Failed to create the reports directory: {$reports_directory}");
            } else {
                error_log("Reports directory created successfully: {$reports_directory}");
            }
        }
        return $reports_directory;
    }
}

if (!function_exists("generate_order_csv")) {
    function generate_order_csv($order_ids, $output_to_browser = true)
    {
        // Prepare CSV headers
        $header = ['Date', 'OrderId', 'Customer', 'SKU', 'Product', 'Quantity', 'Price', 'Total Tax', 'GST Number', 'GST Trade Name'];
        $data = [$header];

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            $order_data = $order->get_data();
            $order_items = $order->get_items();
            $billing_gst_number = $order->get_meta('_billing_gst_number', true);
            $billing_gst_trade_name = $order->get_meta('_billing_gst_trade_name', true);

            foreach ($order_items as $order_item) {
                $product_sku = '';
                $product_id = $order_item->get_product_id();

                if ($product_id) {
                    $product = wc_get_product($product_id);
                    $product_sku = $product->get_sku();
                }

                $data[] = [
                    $order->get_date_created()->format('m/d/Y'),
                    $order_id,
                    sprintf('%s %s', $order_data['shipping']['first_name'], $order_data['shipping']['last_name']),
                    $product_sku,
                    $order_item->get_name(),
                    $order_item->get_quantity(),
                    $order_item->get_total(),
                    $order->get_total_tax(),
                    $billing_gst_number,
                    $billing_gst_trade_name
                ];
            }
        }

        if ($output_to_browser) {
            // Output CSV to browser for download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=orders.csv');
            $out = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
            exit;
        } else {
            // Save CSV to file (used in scheduled email attachment)
            $upload_dir = wp_upload_dir();
            $csv_file_path = $upload_dir['basedir'] . '/woogst-tax-report-' . date('Y-m') . '.csv';
            $out = fopen($csv_file_path, 'w');
            foreach ($data as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
            return $csv_file_path;
        }
    }
}



// The main instance
if (!function_exists('woogst_report')) {
    /**
     * Return instance of  GstReport class
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


