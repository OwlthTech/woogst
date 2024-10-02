<?php

class Woogst_Report_Table
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
       *
       * Adds actions and filter for 'gst-reports' table
       * - Adds custom columns
       * - Unsets default columns
       * - Adds actions in default post_row_actions
       * - Adds header & data to the custom columns
       * - Handles custom row actions
       * @return void
       */
      public function init()
      {
            add_filter('manage_edit-gst-reports_columns', [$this, 'woogst_gst_reports_columns']);
            add_action('manage_gst-reports_posts_custom_column', [$this, 'woogst_gst_reports_custom_column'], 10, 2);

            // Add actions in post_row_actions
            add_filter('post_row_actions', [$this, 'woogst_gst_reports_row_actions'], 10, 2);

            // post actions' trigger
            add_action('admin_post_send_gst_report_email', [$this, 'woogst_send_gst_report_email']);
            add_action('admin_post_regenerate_report', [$this, 'woogst_regenerate_report']);
      }




      /**
       * Adds custom columns & unset default columns of 'gst-reports' post type table
       * @param mixed $columns
       * @return mixed
       */
      public function woogst_gst_reports_columns($columns)
      {
            // Remove some default columns if not needed
            unset($columns['date']);
            unset($columns['comments']);

            // Add custom columns
            $columns['report_stats'] = __('Report Stats', 'woogst');
            $columns['email_status'] = __('Email Status', 'woogst');
            $columns['report_duration'] = __('Report Duration', 'woogst');
            $columns['report_actions'] = __('Report Actions', 'woogst');

            return $columns;
      }


      /**
       * Adds data in 'gst-reports' post type table
       * @param mixed $column
       * @param mixed $post_id
       * @return void
       */
      public function woogst_gst_reports_custom_column($column, $post_id)
      {
            $woogst_option = get_post_meta($post_id, 'woogst_report', true);
            switch ($column) {
                  case 'email_status':
                        // Fetch the email_status from post meta
                        $email_status = isset($woogst_option['sent_email']) ? "✔️" : "❌";
                        echo $email_status;
                        break;

                  case 'report_duration':
                        // Assuming you have a custom field 'report_date'
                        echo '<b>From:</b> ' . esc_html($woogst_option['from'] ?? __('No Date', 'woogst')) . '<br/>';
                        echo '<b>To:</b> ' . esc_html($woogst_option['to'] ?? __('No Date', 'woogst')) . '<br/>';
                        break;

                  case 'report_actions':
                        echo $this->woogst_gst_report_actions_column_data($post_id, $woogst_option);
                        break;

                  case 'report_stats':
                        echo '<b>Order total:</b> ' . esc_html($woogst_option['report_total'] ?? __('No data', 'woogst')) . '<br/>';
                        echo '<b>Total taxes:</b><br>';

                        // Unserialize the report_total_tax meta value
                        $report_total_tax = isset($woogst_option['report_total_tax']) ? maybe_unserialize($woogst_option['report_total_tax']) : [];

                        // Check if there is valid data in the report_total_tax
                        if (!empty($report_total_tax) && is_array($report_total_tax)) {
                              // Use array_map to loop over the array and format the output
                              array_map(function ($tax_amounts, $tax_type) {
                                    // Get the tax rate and amount (e.g., 2.5000% => 5000)
                                    foreach ($tax_amounts as $rate => $amount) {
                                          // Format and print each tax type and its total
                                          echo '<b>Total ' . esc_html($tax_type) . ' (' . esc_html($rate) . '%):</b> ' . esc_html(number_format($amount, 2)) . '<br/>';
                                    }
                              }, $report_total_tax, array_keys($report_total_tax));
                        } else {
                              echo esc_html(__('No data', 'woogst'));
                        }
                        break;
            }
      }

      /**
       * Adds row actions in title column
       * @param mixed $actions
       * @param mixed $post
       * @return mixed
       */
      public function woogst_gst_reports_row_actions($actions, $post)
      {
            if ($post->post_type == 'gst-reports') {
                  // Add custom action links
                  $actions['send_email'] = '<a href="' . admin_url('admin-post.php?action=send_gst_report_email&post_id=' . $post->ID) . '">' . __('Send Email', 'woogst') . '</a>';
            }
            return $actions;
      }

      /**
       * Email status columns data
       * @param mixed $post_id
       * @return string
       */

      /**
       * Action columns data
       * @param mixed $post_id
       * @return string
       */
      public function woogst_gst_report_actions_column_data($post_id, $woogst_option)
      {

            $regenerate = woogst_validator()->is_woocommerce_active() ? sprintf(
                  '<a href="%s">%s</a><br/>',
                  esc_url(add_query_arg(['post_id' => $post_id, 'action' => 'regenerate_report'], admin_url('admin-post.php'))),
                  __('Regenerate Report', 'woogst')
            ) : '';

            // Get email status and set action link
            $email_status = isset($woogst_option['sent_email']) ? $woogst_option['sent_email'] : 0;
            $email_status_text = $email_status ? __('Resend Email', 'woogst') : __('Send Email', 'woogst');

            $send_email = sprintf(
                  '<a href="%s">%s</a><br/>',
                  esc_url(add_query_arg(['post_id' => $post_id, 'action' => 'send_gst_report_email'], admin_url('admin-post.php'))),
                  $email_status_text
            );

            // Download Report Link
            $download_url = isset($woogst_option['report_csv_url']) ? $woogst_option['report_csv_url'] : '';
            $download_report = $download_url ? sprintf(
                  '<a href="%s" target="_blank">%s</a>',
                  esc_url($download_url),
                  __('Download Report CSV', 'woogst')
            ) : __('No CSV available', 'woogst');

            // Combine actions
            $actions = "{$regenerate}{$send_email}{$download_report}";
            return $actions;
      }


      /**
       * Handles the "Send Email" action
       * @return void
       */
      public function woogst_send_gst_report_email()
      {
            // Verify post ID is set
            if (isset($_GET['post_id'])) {
                  $post_id = intval($_GET['post_id']);

                  // Get the email address and email content (adjust to your needs)
                  $email_status = get_post_meta($post_id, 'email_status', true);
                  $report_title = get_the_title($post_id);

                  // For example purposes, let's assume we're sending to a hardcoded email
                  $to = 'owlthtech@gmail.com';
                  $subject = 'GST Report: ' . $report_title;
                  $message = 'Here is the GST report: ' . $report_title . '. Status: ' . $email_status;

                  // Send the email
                  $email = wp_mail($to, $subject, $message);

                  if ($email) {
                        set_wp_admin_notice(__('Email sent successfully.'), 'success');
                  } else {
                        set_wp_admin_notice(__('Unable to send email!'), 'error');
                  }
                  // Redirect back to the admin page
                  wp_redirect(remove_query_arg(['send_gst_report_email'], admin_url('edit.php?post_type=gst-reports')));
            }
      }

      /**
       * Handle the "Regenerate Report" action
       * @return void
       */
      public function woogst_regenerate_report()
      {
            if (isset($_GET['post_id'])) {
                  $post_id = intval($_GET['post_id']);
                  $report_title = get_the_title($post_id);
                  $regenerate = false;
                  if ($regenerate) {
                        set_wp_admin_notice(sprintf('Report "%s" regenerated successfully.', $report_title), 'success');
                  } else {
                        set_wp_admin_notice(sprintf('Error occured during regenerating report "%s", pleae try again!', $report_title), 'error');
                  }
                  // Redirect back to the admin page
                  wp_redirect(remove_query_arg(['regenerate_report'], admin_url('edit.php?post_type=gst-reports')));
            }
      }
}

// The main instance
if (!function_exists('woogst_gst_report_table')) {
      /**
       * Return instance of Woogst_Report_Table class
       *
       * @since 1.0.0
       *
       * @return Woogst_Report_Table
       */
      function woogst_gst_report_table()
      {//phpcs:ignore
            return Woogst_Report_Table::get_instance();
      }
}