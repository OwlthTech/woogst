<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}


class Woogst_Report
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
        // Hook into admin_init to handle report scheduling
        add_action('admin_init', array($this, 'handle_scheduled_report_action'));

        // Hook to run the monthly report generation
        add_action('woogst_send_monthly_tax_report', array($this, 'generate_save_and_send_report'));

        // just for testing
        // add_action('wp', array($this, 'generate_save_and_send_report'));

    }

    public function handle_scheduled_report_action()
    {
        // Check if the report generation is enabled in the plugin settings
        $schedule_report_enabled = woogst_get_option('gst-reports', 'schedule_report');

        // If the report is enabled and not already scheduled
        if (isset($schedule_report_enabled) && $schedule_report_enabled) {
            if (!wp_next_scheduled('woogst_send_monthly_tax_report')) {
                // Schedule the report if it's not already scheduled
                log_report_status('Scheduling the monthly report');
                $this->schedule_report();
            }
        } else {
            // Only clear if it's scheduled
            if (wp_next_scheduled('woogst_send_monthly_tax_report')) {
                wp_clear_scheduled_hook('woogst_send_monthly_tax_report');
                log_report_status("Scheduled report generation has been disabled.");
            }
        }
    }

    public function schedule_report()
    {
        if (!wp_next_scheduled('woogst_send_monthly_tax_report')) {
            log_report_status('setting new woogst_send_monthly_tax_report.');

            // Get the WordPress time zone
            $timezone = new DateTimeZone(wp_timezone_string());

            // Set the event to trigger at the first day of next month 08:00 AM in the site’s timezone
            $next_schedule = new DateTime('first day of next month 08:00', $timezone);
            $next_schedule_timestamp = $next_schedule->getTimestamp();

            // Schedule the single event (it will only run once)
            wp_schedule_single_event($next_schedule_timestamp, 'woogst_send_monthly_tax_report');
            log_report_status('schedule_report method called to execute at: ' . wp_date('d-m-Y', $next_schedule_timestamp) . ' @ ' . wp_date('H:i', $next_schedule_timestamp) . ' AM');

            set_wp_admin_notice('Next monthly order tax report is scheduled on: ' . wp_date('d-m-Y', $next_schedule_timestamp) . ' @ ' . wp_date('H:i', $next_schedule_timestamp) . ' AM', 'success');
        }
    }

    public function generate_save_and_send_report(
        $month = null,
        $year = null,
        $report_id = null,
        $schedule = true,
        $send_report_email = true,
        $to_additional_email = true
    ) {
        // If no month and year are passed, use the previous month and current year as default
        if (is_null($month) && is_null($year)) {
            $prev = new DateTime('first day of last month');
            $suffix = $prev->format('m-Y');
        }
        if (!is_null($month) && !is_null($year)) {
            if (is_numeric($month)) {
                $month = DateTime::createFromFormat('!m', $month)->format('F');
            }
            $suffix = "{$month}-{$year}";
        }
        $title = 'Order Tax Report for ' . $suffix;

        $orders = get_monthly_orders($month, $year);

        // Do actions - get orders, generate csv, format email
        $report_csv = generate_csv($orders['order_data'], $month, $year);
        $order_stats = get_order_statistics($orders);
        $email_body = format_statistics_for_email($order_stats);

        // if checked in generation form or scheduled then send email
        if ($send_report_email || $schedule) {
            $email_status = send_report_mail($title, $email_body, $report_csv['file_path'], $to_additional_email);
        }
        // Extract order IDs and other relevant details from order data for storage
        $simplified_order_data = array_map(function ($order) {
            return array(
                'order_id' => $order['order_id'],  // Only store order IDs
                'order_total' => $order['order_total'], // Optionally store total amount
            );
        }, $orders['order_data']);

        // Save gst-reports post
        $report_meta = array(
            'woogst_report' => array(
                'report_csv_url' => esc_url_raw($report_csv['file_url']),
                'from' => $orders['from'],
                'to' => $orders['to'],
                'sent_email' => $send_report_email && isset($email_status) && is_array($email_status) ? $email_status : array(
                    'admin_email_status' => null,
                    'additional_email_status' => null
                ),
                'report_total' => wc_format_decimal($order_stats['total_order_amount'], 2),
                'report_total_tax' => $order_stats['total_tax_amount_by_label'],
                'report_orders' => $simplified_order_data,
                'report_type' => $schedule
            )
        );

        $post_data = array(
            'post_type' => 'gst-reports',
            'post_title' => $title,
            'post_content' => $email_body,
            'post_status' => 'private',
            'meta_input' => $report_meta,
        );

        if (!is_null($report_id)) {
            $post_data['ID'] = $report_id;
        }
        $report_id = wp_insert_post($post_data);

        log_report_status("Order report is created -> " . $report_id);

        // After sending the report, reschedule the next event for the first day of the next month
        $timezone = new DateTimeZone(wp_timezone_string());
        $next_schedule = new DateTime('first day of next month 08:00', $timezone);
        $next_schedule_timestamp = $next_schedule->getTimestamp();
        if ($schedule) {
            wp_schedule_single_event($next_schedule_timestamp, 'woogst_send_monthly_tax_report');
        }
        return array(
            'email_status' => $email_status,
            'is_scheduled' => $schedule,
            'report_id' => $report_id,
            'report_meta' => $report_meta,
            'report_csv' => $report_csv
        );
    }
}

/**
 * Main actions of schedule reporting
 */

if (!function_exists('get_monthly_orders')) {
    function get_monthly_orders($month = null, $year = null)
    {
        // Ensure $month is a valid string (e.g., "October") or number (e.g., "10")
        if (empty($month) || is_null($month) || !is_string($month) && !is_numeric($month)) {
            $month = date('m');  // Default to current month as numeric (e.g., '10')
        }

        // Convert numeric month to full textual format (e.g., 'October')
        if (is_numeric($month)) {
            $month = DateTime::createFromFormat('!m', $month)->format('F');
        }

        // Ensure $year is a valid string or number (e.g., "2024")
        if (empty($year) || is_null($year) || !is_numeric($year)) {
            $year = date('Y');  // Default to current year
        }

        // Now safely use sprintf for date creation
        try {
            // Convert to a valid date string
            $date_string = sprintf('first day of %s %s', $month, $year);
            $first_day_of_month = new DateTime($date_string);
            $last_day_of_month = new DateTime($first_day_of_month->format('Y-m-t'));
        } catch (Exception $e) {
            // Handle any errors in date creation
            return null;
        }

        // Set the time to the start and end of the day for inclusivity
        $first_day_of_month->setTime(0, 0, 0);
        $last_day_of_month->setTime(23, 59, 59);

        $args = array(
            'date_created' => "{$first_day_of_month->getTimestamp()}...{$last_day_of_month->getTimestamp()}",
            'limit' => -1
        );

        // $orders = get_posts($args);
        $orders = wc_get_orders($args);

        // Array to store the order details with custom meta fields
        $order_data = array();

        foreach ($orders as $order) {

            // Check if HPOS (High-Performance Order Storage) is enabled and use the appropriate functions
            $order_id = $order->get_id();
            $order_total = $order->get_total();
            $tax_total = $order->get_total_tax();
            $order_date = $order->get_date_created()->date('d-m-Y H:i:s');

            // Get custom meta fields (_billing_gst_number, _billing_gst_trade_name)
            $billing_gst_number = $order->get_meta('_billing_gst_number', true);
            $billing_gst_trade_name = $order->get_meta('_billing_gst_trade_name', true);

            // Initialize variables for tax percentage and total tax amount
            $tax_details = [];

            // Loop through the tax totals to get the tax rate and total amount
            foreach ($order->get_tax_totals() as $code => $tax) {
                $tax_rate_id = $tax->rate_id;  // The rate ID
                $tax_label = $tax->label;      // The tax label (e.g., "VAT", "GST", etc.)
                $tax_amount = $tax->amount;    // The total tax amount for this rate

                // Get the tax rate object using the rate ID
                $rates = WC_Tax::_get_tax_rate($tax_rate_id);
                $tax_percentage = isset($rates['tax_rate']) ? $rates['tax_rate'] : $tax_label;

                // Add tax details for this rate to the array
                $tax_details[] = array(
                    'label' => $tax_label,
                    'rate' => $tax_percentage,
                    'amount' => $tax_amount,
                    'rate_id' => $tax_rate_id
                );
            }

            // Add the data to the array
            $order_data[] = array(
                'order_id' => $order_id,
                'order_total' => $order_total,
                'tax_total' => $tax_total,
                'billing_gst_number' => $billing_gst_number,
                'billing_gst_trade_name' => $billing_gst_trade_name,
                'order_date' => $order_date,
                'tax_classes' => $tax_details,
            );
        }

        return array(
            'order_data' => $order_data,
            'from' => $first_day_of_month->format('d-m-Y H:i:s'),
            'to' => $last_day_of_month->format('d-m-Y H:i:s')
        );
    }
}

if (!function_exists('generate_csv')) {
    function generate_csv($orders_data, $month = null, $year = null)
    {
        // If no month and year are passed, use the previous month and current year as default
        if (is_null($month) && is_null($year)) {
            $prev = new DateTime('first day of last month');
            $suffix = $prev->format('m-Y');
        }
        if (!is_null($month) && !is_null($year)) {
            $suffix = "{$month}-{$year}";
        }

        // Set the file name based on the passed or default month and year
        $file_name = "woogst-tax-report-{$suffix}.csv";
        $reports_directory = get_report_directory();
        $csv_file_path = "{$reports_directory}{$file_name}";
        // Open file for writing
        $file = fopen($csv_file_path, 'w');
        if ($file === false) {
            return false; // Handle error if file opening fails
        }

        // Add CSV headers
        fputcsv($file, array('Order ID', 'Billing GST Number', 'Billing GST Trade Name', 'Order Total', 'Tax Total', 'Order Date', 'Tax Label', 'Tax rate', 'Tax amount'));
        // Add rows to the CSV
        foreach ($orders_data as $order) {
            // Check if the order has any tax classes
            if (!empty($order['tax_classes'])) {
                // Loop through each tax class for the order
                foreach ($order['tax_classes'] as $tax_class) {
                    fputcsv($file, array(
                        $order['order_id'],
                        $order['billing_gst_number'],
                        $order['billing_gst_trade_name'],
                        $order['order_total'],
                        $order['tax_total'],
                        $order['order_date'],
                        $tax_class['label'],  // Tax label (e.g., GST, VAT)
                        $tax_class['rate'],   // Tax percentage
                        $tax_class['amount']  // Tax amount
                    ));
                }
            } else {
                // If no tax classes, add a row with empty tax details
                fputcsv($file, array(
                    $order['order_id'],
                    $order['billing_gst_number'],
                    $order['billing_gst_trade_name'],
                    $order['order_total'],
                    $order['tax_total'],
                    $order['order_date'],
                    '',  // Empty tax label
                    '',  // Empty tax rate
                    ''   // Empty tax amount
                ));
            }
        }


        // Close the file
        fclose($file);

        // Create the public URL for the CSV file
        $upload_dir = wp_upload_dir();
        $csv_file_url = $upload_dir['baseurl'] . '/order-reports/' . $file_name;

        log_report_status("CSV generated and saved. Path -> $csv_file_url");
        return array(
            'file_path' => $csv_file_path,  // Local file path for attachment
            'file_url' => $csv_file_url     // Public URL for download
        );
    }
}

if (!function_exists('get_order_statistics')) {
    function get_order_statistics($order_data)
    {
        // Variables to hold statistics
        $total_order_amount = 0;
        $total_tax_amount_by_label = array();

        // Loop through the orders to gather statistics
        foreach ($order_data['order_data'] as $order) {
            // Add up total order amount
            $total_order_amount += $order['order_total'];

            // Loop through tax classes for each order to gather tax details
            foreach ($order['tax_classes'] as $tax_class) {
                $label = $tax_class['label'];   // Tax label (e.g., VAT, GST)
                $rate = $tax_class['rate'];     // Tax rate (e.g., 5%, 18%)
                $tax_amount = $tax_class['amount']; // Tax amount for this rate

                // Group tax by label and rate
                if (!isset($total_tax_amount_by_label[$label])) {
                    $total_tax_amount_by_label[$label] = array();
                }

                if (!isset($total_tax_amount_by_label[$label][$rate])) {
                    $total_tax_amount_by_label[$label][$rate] = 0;
                }

                // Add tax amount to the total for this label and rate
                $total_tax_amount_by_label[$label][$rate] += $tax_amount;
            }
        }

        // Return statistics as an array
        return array(
            'total_order_amount' => $total_order_amount,
            'total_tax_amount_by_label' => $total_tax_amount_by_label
        );
    }
}

if (!function_exists('format_statistics_for_email')) {
    function format_statistics_for_email($statistics)
    {
        // Extract statistics from the array
        $total_order_amount = wc_price($statistics['total_order_amount']); // Format as currency
        $total_tax_amount_by_label = $statistics['total_tax_amount_by_label'];

        // Start building the email content
        $email_body = "<h2>Monthly Order & Tax Statistics</h2>";
        $email_body .= "<p>Here are the statistics of the order and taxes:</p>";

        // Start table
        $email_body .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 80%;'>
                        <thead style='background-color: #dfdfdf'>
                            <tr>
                                <th>Statistic</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>";

        // Add total order amount
        $email_body .= "<tr>
                        <td><strong>Total Order Amount</strong></td>
                        <td>$total_order_amount</td>
                    </tr>";

        // Add tax summary by label and rate
        foreach ($total_tax_amount_by_label as $label => $rates) {
            foreach ($rates as $rate => $tax_amount) {
                $tax_amount_formatted = wc_price($tax_amount); // Format tax amount
                $email_body .= "<tr>
                                <td><strong>$label ($rate%)</strong></td>
                                <td>$tax_amount_formatted</td>
                            </tr>";
            }
        }

        // Close the table
        $email_body .= "</tbody></table>";

        return $email_body;
    }

}

if (!function_exists('send_report_mail')) {
    function send_report_mail($title, $email_body, $report_csv, $to_additional_email)
    {
        // Prepare
        $subject = get_bloginfo('name') . ' - ' . $title;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $attachments = array($report_csv);
        $admin_email = get_bloginfo('admin_email');
        // Send
        $sent_admin = wp_mail($admin_email, $subject, $email_body, $headers, $attachments);
        // Log
        log_report_status('Report email status for admin: ' . $sent_admin);

        // Additional email
        $additional_email = woogst_get_option('gst-reports', 'schedule_report_email_id');
        if (is_email($additional_email) && !empty($additional_email) && $to_additional_email) {
            $sent_additional = wp_mail($additional_email, $subject, $email_body, $headers, $attachments);
            log_report_status('Report email status for additional email id (' . $additional_email . ') :' . $sent_additional);
        }

        return array(
            'admin_email_status' => $sent_admin,
            'additional_email_status' => $sent_additional
        );

    }
}

/**
 * Helper functions
 */


if (!function_exists('get_report_directory')) {
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

if (!function_exists('woogst_schedule_report_notice')) {
    function woogst_schedule_report_notice()
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
            // echo '<div class="notice notice-success is-dismissible" id="woogst-cron-notice"><p>Cron job is scheduled to run at: ' . $scheduled_time->format('Y-m-d H:i:s') . '</p></div>';
        }
    }
}


// The main instance
if (!function_exists('woogst_report')) {
    /**
     * Return instance of  Woogst_Report class
     *
     * @since 1.0.0
     *
     * @return Woogst_Report
     */
    function woogst_report()
    {//phpcs:ignore
        return Woogst_Report::get_instance();
    }
}
