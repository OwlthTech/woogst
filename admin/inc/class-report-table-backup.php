<?php

if (!class_exists('WP_List_Table')) {
      require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Woogst_Order_Reports_List_Table extends WP_List_Table
{

      public function __construct()
      {
            parent::__construct([
                  'singular' => __('Order Report', 'woogst'),
                  'plural' => __('Order Reports', 'woogst'),
                  'ajax' => false
            ]);
      }

      /**
       * Prepare the list of items for displaying.
       */
      public function prepare_items()
      {

            $per_page = $this->get_items_per_page('submissions_per_page', 10);
            $current_page = $this->get_pagenum();
            $total_items = $this->get_total_items();

            // Handle the search query if it exists
            $search = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';

            // Set column headers
            $hidden = get_hidden_columns(get_current_screen());
            $this->_column_headers = [$this->get_columns(), $hidden, $this->get_sortable_columns()];

            // Process bulk actions
            $this->process_bulk_action();
            $data = $this->get_reports($per_page, $current_page, $search);
            $this->items = $data;

            // Set pagination arguments
            $this->set_pagination_args([
                  'total_items' => $total_items,
                  'per_page' => $per_page,
                  'total_pages' => ceil($total_items / $per_page)
            ]);

      }

      /**
       * Get a list of reports.
       */
      public function get_reports($per_page = 10, $page_number = 1, $search = '')
      {
            $args = [
                  'post_type' => 'order-reports',
                  'posts_per_page' => $per_page,
                  'paged' => $page_number,
                  's' => $search,
            ];

            $query = new WP_Query($args);
            return $query->posts;
      }

      /**
       * Get total number of reports.
       */
      public function get_total_items()
      {
            $args = [
                  'post_type' => 'order-reports',
                  'posts_per_page' => -1,
            ];

            $query = new WP_Query($args);
            return $query->found_posts;
      }

      /**
       * Define columns.
       */
      public function get_columns()
      {
            $columns = [
                  'cb' => '<input type="checkbox" />',
                  'title' => __('Report Title', 'woogst'),
                  'email_status' => __('Email Status', 'woogst'),
                  'actions' => __('Actions', 'woogst'),
            ];
            return $columns;
      }

      /**
       * Render a column when no specific method exists.
       */
      public function column_default($item, $column_name)
      {
            switch ($column_name) {
                  case 'email_status':
                        return get_post_meta($item->ID, 'email_status', true) ? 'Sent' : 'Pending';
                  case 'title':
                        return $item->post_title;
                  case 'actions':
                        return $this->get_row_actions($item);
                  default:
                        return print_r($item, true);
            }
      }

      protected function get_row_actions($item)
      {
            // Create a nonce for the delete action
            $delete_nonce = wp_create_nonce('delete-report');

            // Create a form with a button for the delete action
            $delete_form = sprintf(
                  '<form method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="%s">
                <input type="hidden" name="_wpnonce" value="%s">
                <button type="submit" class="button-link delete" onclick="return confirm(\'%s\');">%s</button>
            </form>',
                  esc_attr($item->ID), // report ID
                  esc_attr($delete_nonce), // Nonce value
                  esc_js(__('Are you sure you want to delete this report?', 'woogst')), // Confirmation message
                  __('Delete', 'woogst') // Button label
            );

            return $delete_form;
      }

      /**
       * Render the checkbox column.
       */
      public function column_cb($item)
      {
            return sprintf(
                  '<input type="checkbox" name="bulk-delete[]" value="%s" />',
                  $item->ID
            );
      }

      /**
       * Define sortable columns.
       */
      public function get_sortable_columns()
      {
            return [
                  'title' => ['title', true],
            ];
      }

      /**
       * Bulk actions available.
       */
      public function get_bulk_actions()
      {
            $actions = [
                  'bulk-delete' => 'Delete',
            ];
            return $actions;
      }

      /**
       * Process bulk actions.
       */
      public function process_bulk_action()
      {
            if ('delete' === $this->current_action()) {
                  $nonce = esc_attr($_REQUEST['_wpnonce']);

                  if (!wp_verify_nonce($nonce, 'woogst_delete_report')) {
                        die('Security check failed');
                  } else {
                        wp_delete_post(absint($_GET['report']));
                        wp_redirect(esc_url(add_query_arg()));
                        exit;
                  }
            }

            if (isset($_POST['action']) && $_POST['action'] == 'bulk-delete') {
                  $delete_ids = esc_sql($_POST['bulk-delete']);

                  foreach ($delete_ids as $id) {
                        wp_delete_post($id);
                  }

                  wp_redirect(esc_url(add_query_arg()));
                  exit;
            }
      }
}
