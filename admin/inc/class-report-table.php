<?php

if (!class_exists('WP_List_Table')) {
      require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Woo_Gst_Report_Table extends WP_List_Table
{

      /**
       * Constructor for the list table.
       */
      public function __construct()
      {
            parent::__construct(
                  array(
                        'singular' => __('GST Report', 'woogst'),  // Singular label
                        'plural' => __('GST Reports', 'woogst'),  // Plural label
                        'ajax' => false  // Does this table support ajax?
                  ),
            );
      }

      // Admin notice
      function set_wp_admin_notice($message, $type)
      {
            wp_admin_notice($message, ['type' => $type, 'dismissible' => true]);
      }

      /**
       * Prepare the list of items for displaying.
       */
      public function prepare_items()
      {
            $columns = $this->get_columns();
            $hidden = get_hidden_columns(get_current_screen());
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = [$columns, $hidden, $sortable];

            $per_page = $this->get_items_per_page('reports_per_page', 3);
            $current_page = $this->get_pagenum();
            $total_items = $this->get_total_items();

            // Handle the search query if it exists
            $search = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';
            
            // Process bulk actions
            $this->process_bulk_action();
            
            $this->items = $this->fetch_gst_reports_data($per_page, $current_page, $search);

            // Set pagination arguments
            $this->set_pagination_args([
                  'total_items' => $total_items,
                  'per_page' => $per_page,
                  'total_pages' => ceil($total_items / $per_page)
            ]);
      }

      /**
       * Display the search box.
       */
      public function search_box($text, $input_id)
      {
            if (empty($_REQUEST['s']) && !$this->has_items()) {
                  return;
            }

            foreach ($_REQUEST as $key => $value) {
                  if (!$key === 's' || !is_array($value)) {
                        echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
                  }
            }

            $input_id = $input_id . '-search-input';

            if (!empty($_REQUEST['orderby'])) {
                  if (is_array($_REQUEST['orderby'])) {
                        foreach ($_REQUEST['orderby'] as $key => $value) {
                              echo '<input type="hidden" name="orderby[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" />';
                        }
                  } else {
                        echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
                  }
            }
            if (!empty($_REQUEST['order'])) {
                  echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
            }
            if (!empty($_REQUEST['post_mime_type'])) {
                  echo '<input type="hidden" name="post_mime_type" value="' . esc_attr($_REQUEST['post_mime_type']) . '" />';
            }
            if (!empty($_REQUEST['detached'])) {
                  echo '<input type="hidden" name="detached" value="' . esc_attr($_REQUEST['detached']) . '" />';
            }
            ?>
            <p class="search-box">
                  <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php echo $text; ?>:</label>
                  <input type="search" id="<?php echo esc_attr($input_id); ?>" name="s" value="<?php _admin_search_query(); ?>" />
                  <?php submit_button($text, '', '', false, array('id' => 'search-submit')); ?>
            </p>
            <?php
      }


      /**
       * Fetch data for the table.
       *
       * @param int $per_page Number of items per page.
       * @param int $page_number Current page number.
       * @return array Data for the table.
       */
      public function fetch_gst_reports_data($per_page, $page_number, $search = '')
      {
            $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
            $current_paged = isset($_REQUEST['paged']) ? $_REQUEST['paged'] : $page_number;
            
            
            $args = [
                  'post_type' => 'gst-reports',
                  'posts_per_page' => $per_page,
            ];

            if(!empty($current_paged)) {
                  $args['paged'] = $current_paged;
            }

            // If searching, add search query
            if (!empty($search)) {
                  $args['s'] = $search;
            }

            $query = new WP_Query($args);
            return $query->posts;
      }

      /**
       * Get total number of gst_reports in the database.
       *
       * @return int Total number of items.
       */
      public function get_total_items()
      {
            $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

            $args = [
                  'post_type' => 'gst-reports',
                  'posts_per_page' => -1,
            ];

            // If searching, add search query
            if (!empty($search)) {
                  $args['s'] = $search;
            }
            // ob_start();
            $query = new WP_Query($args);
            // ob_end_clean();
            return $query->found_posts;
      }

      /**
       * Define the columns of the table.
       *
       * @return array Columns for the table.
       */
      public function get_columns()
      {
            $columns = array(
                  'cb' => '<input type="checkbox" />',
                  'name' => __('Name', 'woogst'),
                  'email_status' => __('Email', 'woogst'),
                  'date_generated' => __('Reporting Time', 'woogst'),
                  'actions' => __('Actions', 'woogst')
            );

            return $columns;
      }

      /**
       * Define which columns are sortable.
       *
       * @return array Sortable columns.
       */
      protected function get_sortable_columns()
      {
            return [
                  'name' => ['name', true],
                  'email_status' => ['email_status', false],
                  'date_generated' => ['date_generated', false],
            ];
      }

      /**
       * Render a column when no specific method exists.
       */
      public function column_default($item, $column_name)
      {
            switch ($column_name) {
                  case 'name':
                        return $item->post_title;
                  case 'email_status':
                        return get_post_meta($item->ID, 'email_status', true) ? 'Sent' : 'Pending';
                  case 'date_generated':
                        return esc_html(get_the_date('Y-m-d H:i:s', $item->ID));
                  case 'actions':
                        return $this->get_row_actions($item);
                  default:
                        return print_r($item, true);
            }
      }


      public function get_bulk_actions()
      {
            $actions = array(
                  'bulk-delete' => __('Delete', 'woogst')
            );
            return $actions;
      }

      /**
       * Render the checkbox column.
       */
      public function column_cb($item)
      {
            return sprintf(
                  '<input type="checkbox" name="post[]" value="%s" />',
                  $item->ID
            );
      }

      /**
       * Render the name column.
       */
      public function column_name($item)
      {
            $title = '<strong>' . esc_html(get_the_title($item->ID)) . '</strong>';

            $view_link = sprintf(
                  '<a href="%s">%s</a>',
                  esc_url(add_query_arg(['post' => $item->ID, 'action' => 'view'], admin_url('admin.php?page=woo-gst-reports'))),
                  __('View more', 'woogst')
            );

            $actions = [
                  'view' => $view_link
            ];

            return $title . $this->row_actions($actions);
      }


      /**
       * Render the actions column.
       */
      public function column_actions($item)
      {
            $regenerate = sprintf(
                  '<a href="%s">%s</a>',
                  esc_url(add_query_arg(['post' => $item->ID, 'action' => 'view'], $_SERVER['REQUEST_URI'])),
                  __('Regenrate Report', 'woogst')
            );

            $email_status = get_post_meta($item->ID, 'email_status', true);
            $email_status = $email_status ? 'Email sent' : 'unknown';
            $send_email = sprintf(
                  '<a href="%s">%s</a>',
                  esc_url(add_query_arg(['post' => $item->ID, 'action' => 'toggle_status'], $_SERVER['REQUEST_URI'])),
                  ucfirst($email_status)
            );

            $download_url = get_post_meta($item->ID, 'csv_file_path', true);
            $download_report = sprintf(
                  '<a href="%s" target="_blank">%s</a>',
                  esc_url($download_url),
                  __('Download Report CSV', 'woogst')
            );
            // Combine both actions
            $actions = $regenerate . ' <br/> ' . $send_email . '<br/>' . $download_report;
            return $actions;
      }



      /**
       * Process bulk actions.
       */
      public function process_bulk_action()
      {
            $action = $this->current_action();

            $request_ids = isset($_REQUEST['post']) ? wp_parse_id_list(wp_unslash($_REQUEST['post'])) : array();

            if (empty($request_ids)) {
                  return;
            }

            $count = 0;
            $failures = 0;

            check_admin_referer('bulk-' . $this->_args['plural']);

            switch ($action) {

                  case 'bulk-delete':
                        foreach ($request_ids as $request_id) {
                              if (wp_delete_post($request_id, true)) {
                                    ++$count;
                              } else {
                                    ++$failures;
                              }
                        }

                        if ($failures) {
                              $this->set_wp_admin_notice(
                                    sprintf(
                                          /* translators: %d: Number of requests. */
                                          _n(
                                                '%d request failed to delete.',
                                                '%d requests failed to delete.',
                                                $failures
                                          ),
                                          $failures
                                    ),
                                    'error'
                              );
                        }

                        if ($count) {
                              $this->set_wp_admin_notice(
                                    sprintf(
                                          _n(
                                                '%d item deleted successfully.',
                                                '%d items deleted successfully.',
                                                $count
                                          ),
                                          $count
                                    ),
                                    'success'
                              );

                        }

                        break;
            }
      }
}