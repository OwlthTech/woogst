<?php

class Gst_Report_Table
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
            switch ($column) {
                  case 'email_status':
                        echo esc_html($this->woogst_gst_email_status_column_data($post_id));
                        break;

                  case 'report_duration':
                        // Assuming you have a custom field 'report_date'
                        $report_date = get_post_meta($post_id, 'report_duration', true);
                        echo esc_html($report_date ? $report_date : __('No Date', 'woogst'));
                        break;

                  case 'report_actions':
                        echo $this->woogst_gst_report_actions_column_data($post_id);
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
      public function woogst_gst_email_status_column_data($post_id)
      {
            // Fetch the email_status from post meta
            $email_status = get_post_meta($post_id, 'email_status', true);
            $email_status = $email_status ? "✔️" : "❌";

            return $email_status;
      }

      /**
       * Action columns data
       * @param mixed $post_id
       * @return string
       */
      public function woogst_gst_report_actions_column_data($post_id)
      {
            $regenerate = sprintf(
                  '<a href="%s">%s</a>',
                  esc_url(add_query_arg(['post_id' => $post_id, 'action' => 'regenerate_report'], admin_url('admin-post.php'))),
                  __('Regenerate Report', 'woogst')
            );

            // Get email status and set action link
            $email_status = get_post_meta($post_id, 'email_status', true);
            $email_status_text = $email_status ? __('Resend Email', 'woogst') : __('Send Email', 'woogst');

            $send_email = sprintf(
                  '<a href="%s">%s</a>',
                  esc_url(add_query_arg(['post_id' => $post_id, 'action' => 'send_gst_report_email'], admin_url('admin-post.php'))),
                  $email_status_text
            );

            // Download Report Link
            $download_url = get_post_meta($post_id, 'csv_file_path', true);
            $download_report = $download_url ? sprintf(
                  '<a href="%s" target="_blank">%s</a>',
                  esc_url($download_url),
                  __('Download Report CSV', 'woogst')
            ) : __('No CSV available', 'woogst');

            // Combine actions
            $actions = $regenerate . '<br/>' . $send_email . '<br/>' . $download_report;
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
if (!function_exists('woogst_admin_gst_report_table')) {
      /**
       * Return instance of Gst_Report_Table class
       *
       * @since 1.0.0
       *
       * @return Gst_Report_Table
       */
      function woogst_admin_gst_report_table()
      {//phpcs:ignore
            return Gst_Report_Table::get_instance();
      }
}