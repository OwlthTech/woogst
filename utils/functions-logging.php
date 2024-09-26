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