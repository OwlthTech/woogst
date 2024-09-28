<?php

// Log the status of the email process
if (!function_exists('log_report_status')) {
    function log_report_status($message)
    {
        // Check if the logs directory exists, if not, create it
        if (!file_exists(plugin_dir_path(dirname(__FILE__)) . 'logs')) {
            mkdir(plugin_dir_path(dirname(__FILE__)) . 'logs', 0755, true);
        }

        // Define the log file path
        $log_file = plugin_dir_path(dirname(__FILE__)) . 'logs/woogst-tax-report-log.txt';

        // Get the WordPress timezone setting
        $timezone = new DateTimeZone(wp_timezone_string());

        // Create a new DateTime object and apply the timezone
        $time = new DateTime('now', $timezone);

        // Format the date and time
        $formatted_time = $time->format('Y-m-d H:i:s');

        // Create the log message with the formatted date/time and the custom message
        $log_message = "[{$formatted_time}] - {$message}" . PHP_EOL;

        // Append the log message to the log file
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}





/**
 * Admin notice and transients for notice
 * 
 */

function set_wp_admin_notice($message, $type)
{
    set_transient('woogst_admin_notice', ['message' => $message, 'type' => $type], 30);
}

// Checks transient 'woogst_admin_notice' and adds admin notice and deletes transient
function woo_gst_admin_notice_message()
{
    // Retrieve the transient
    $notice = get_transient('woogst_admin_notice');

    if ($notice) {
        $class = $notice['type'] === 'success' ? 'notice-success' : 'notice-error';
        ?>
        <div class="notice <?php echo $class; ?> is-dismissible">
            <p><?php echo esc_html($notice['message']); ?></p>
        </div>
        <?php
        // Delete the transient so it doesn't persist
        delete_transient('woogst_admin_notice');
    }
}

// Checks WooCommerce in installed & active plugins and sets admin notice
function set_wp_admin_notice_active_woo()
{
    if (!Woogst_Validator::is_woocommerce_installed() && !Woogst_Validator::is_woocommerce_active()) {
        set_wp_admin_notice("Please install & activate woocommerce plugin", 'error');
    }
}