

## index.php <a id="index_php"></a>


## uninstall.php <a id="uninstall_php"></a>


## woogst.php <a id="woogst_php"></a>
### Functions
- **activate_woogst**
- **deactivate_woogst**
- **run_woogst**


## class-woogst-admin.php <a id="class-woogst-admin_php"></a>
### Classes
- Woogst_Admin
### Functions
- **__construct**
  - Parameters: $plugin_name, $version
- **enqueue_styles**
- **enqueue_scripts**
- **register_gst_report_post_type**
- **set_wp_admin_notice**
  - Parameters: $message, $type
- **woo_gst_admin_notice_message**
- **set_wp_admin_notice_active_woo**


## index.php <a id="index_php"></a>


## helper.php <a id="helper_php"></a>
### Functions
- **is_hpos_enabled**


## class-gst.php <a id="class-gst_php"></a>
### Classes
- Gst
### Functions
- **get_instance**
- **init**
- **woogst_gst**


## class-woogst-invoice.php <a id="class-woogst-invoice_php"></a>
### Classes
- WoogstInvoice
### Functions
- **get_instance**
- **init**
- **woogst_invoice**
- **add_invoice_action_order_list_table**
  - Parameters: $actions, $order
- **handle_print_invoice**


## class-gst-order-edit.php <a id="class-gst-order-edit_php"></a>
### Classes
- Gst_Order_Edit
### Functions
- **get_instance**
- **init**
- **woogst_admin_gst_single_order_edit**
- **add_gst_to_woocommerce_order_fields**
  - Parameters: $address, $order
- **add_gst_to_woocommerce_admin_billing_fields**
  - Parameters: $billing_fields
- **save_and_validate_custom_gst_billing_fields**
  - Parameters: $order_id, $post_data


## class-gst-order-table.php <a id="class-gst-order-table_php"></a>
### Classes
- Gst_Order_Table
### Functions
- **get_instance**
- **init**
- **woogst_admin_gst_order_table**
- **add_admin_order_list_custom_column**
  - Parameters: $columns
- **display_wc_order_list_custom_column_content**
  - Parameters: $column, $order
- **dropdown_filter_for_gst_orders_list**
- **filtered_data_query_args_for_order_list_table**
  - Parameters: $query_args
- **add_gst_to_woocommerce_order_fields**
  - Parameters: $address, $order
- **add_gst_to_woocommerce_admin_billing_fields**
  - Parameters: $billing_fields
- **save_and_validate_custom_gst_billing_fields**
  - Parameters: $order_id, $post_data


## index.php <a id="index_php"></a>


## class-gst-report-table.php <a id="class-gst-report-table_php"></a>
### Classes
- Gst_Report_Table
### Functions
- **get_instance**
- **init**
- **woogst_gst_reports_columns**
  - Parameters: $columns
- **woogst_gst_reports_custom_column**
  - Parameters: $column, $post_id
- **woogst_gst_reports_row_actions**
  - Parameters: $actions, $post
- **woogst_gst_email_status_column_data**
  - Parameters: $post_id
- **woogst_gst_report_actions_column_data**
  - Parameters: $post_id
- **woogst_send_gst_report_email**
- **woogst_regenerate_report**
- **woogst_admin_gst_report_table**


## class-gst-report.php <a id="class-gst-report_php"></a>
### Classes
- GstReport
### Functions
- **get_instance**
- **init**
- **schedule_report_recurrence**
  - Parameters: $schedules
- **email_report_schedule_notice**
- **schedule_report**
- **woogst_report_handler**
  - Parameters: $generate = null, $send_email = null, $month = null, $year = null
- **generate_save_and_send_report**
  - Parameters: $month = null, $year = null
- **get_monthly_orders**
  - Parameters: $month = null, $year = null
- **prepare_email_content**
  - Parameters: $orders
- **generate_csv**
  - Parameters: $orders, $month = null, $year = null
- **generate_order_csv**
  - Parameters: $order_ids, $output_to_browser = true
- **woogst_report**


## deprecated-class-report-table.php <a id="deprecated-class-report-table_php"></a>
### Classes
- Woo_Gst_Report_Table
### Functions
- **__construct**
- **set_wp_admin_notice**
  - Parameters: $message, $type
- **prepare_items**
- **search_box**
  - Parameters: $text, $input_id
- **fetch_gst_reports_data**
  - Parameters: $per_page, $page_number, $search = ''
- **get_total_items**
- **get_columns**
- **get_sortable_columns**
- **column_default**
  - Parameters: $item, $column_name
- **get_bulk_actions**
- **column_cb**
  - Parameters: $item
- **column_name**
  - Parameters: $item
- **column_actions**
  - Parameters: $item
- **process_bulk_action**


## index.php <a id="index_php"></a>


## invoice-template.php <a id="invoice-template_php"></a>


## woogst-admin-display.php <a id="woogst-admin-display_php"></a>


## class-woogst-activator.php <a id="class-woogst-activator_php"></a>
### Classes
- Woogst_Activator
### Functions
- **activate**


## class-woogst-deactivator.php <a id="class-woogst-deactivator_php"></a>
### Classes
- Woogst_Deactivator
### Functions
- **deactivate**


## class-woogst-i18n.php <a id="class-woogst-i18n_php"></a>
### Classes
- Woogst_i18n
### Functions
- **load_plugin_textdomain**


## class-woogst-loader.php <a id="class-woogst-loader_php"></a>
### Classes
- Woogst_Loader
### Functions
- **__construct**
- **add_action**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add_filter**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add**
  - Parameters:  $hooks, $hook, $component, $callback, $priority, $accepted_args 
- **run**


## class-woogst.php <a id="class-woogst_php"></a>
### Classes
- Woogst
### Functions
- **__construct**
- **load_dependencies**
- **set_locale**
- **define_admin_hooks**
- **define_public_hooks**
- **define_gst_report_hooks**
- **define_invoice_hooks**
- **run**
- **get_plugin_name**
- **get_loader**
- **get_version**


## index.php <a id="index_php"></a>


## class-woogst-public.php <a id="class-woogst-public_php"></a>
### Classes
- Woogst_Public
### Functions
- **__construct**
  - Parameters:  $plugin_name, $version 
- **enqueue_styles**
- **enqueue_scripts**


## index.php <a id="index_php"></a>


## woogst-public-functions.php <a id="woogst-public-functions_php"></a>
### Functions
- **gst_fields_add_in_checkout_billing_fields**
  - Parameters: $fields
- **remove_optional_text_from_gst_fields**
  - Parameters: $field, $key, $args, $value
- **gst_fields_sanitize_and_validate**
- **gst_fields_save_in_order_meta**
  - Parameters: $order_id
- **gst_fields_add_in_email_display**
  - Parameters: $order, $sent_to_admin = false, $plain_text = false
- **vat_to_gst_replacement**
  - Parameters: $translated_text, $text


## woogst-public-display.php <a id="woogst-public-display_php"></a>


## class-woogst-validator.php <a id="class-woogst-validator_php"></a>
### Classes
- Woogst_Validator
### Functions
- **__construct**
- **is_woocommerce_installed**
- **is_woocommerce_active**
- **is_woo_tax_active**
- **is_woo_gst_tax_class_slug_exist**
  - Parameters: $slug = null
- **validate_gst_number**
  - Parameters: $gst_number


## functions-logging.php <a id="functions-logging_php"></a>
### Functions
- **log_report_status**
  - Parameters: $message


## index.php <a id="index_php"></a>


## uninstall.php <a id="uninstall_php"></a>


## woogst.php <a id="woogst_php"></a>
### Functions
- **activate_woogst**
- **deactivate_woogst**
- **run_woogst**


## class-woogst-admin.php <a id="class-woogst-admin_php"></a>
### Classes
- Woogst_Admin
### Functions
- **__construct**
  - Parameters: $plugin_name, $version
- **enqueue_styles**
- **enqueue_scripts**
- **register_gst_report_post_type**
- **set_wp_admin_notice**
  - Parameters: $message, $type
- **woo_gst_admin_notice_message**
- **set_wp_admin_notice_active_woo**


## index.php <a id="index_php"></a>


## helper.php <a id="helper_php"></a>
### Functions
- **is_hpos_enabled**


## class-gst.php <a id="class-gst_php"></a>
### Classes
- Gst
### Functions
- **get_instance**
- **init**
- **woogst_gst**


## class-woogst-invoice.php <a id="class-woogst-invoice_php"></a>
### Classes
- WoogstInvoice
### Functions
- **get_instance**
- **init**
- **woogst_invoice**
- **add_invoice_action_order_list_table**
  - Parameters: $actions, $order
- **handle_print_invoice**


## class-gst-order-edit.php <a id="class-gst-order-edit_php"></a>
### Classes
- Gst_Order_Edit
### Functions
- **get_instance**
- **init**
- **woogst_admin_gst_single_order_edit**
- **add_gst_to_woocommerce_order_fields**
  - Parameters: $address, $order
- **add_gst_to_woocommerce_admin_billing_fields**
  - Parameters: $billing_fields
- **save_and_validate_custom_gst_billing_fields**
  - Parameters: $order_id, $post_data


## class-gst-order-table.php <a id="class-gst-order-table_php"></a>
### Classes
- Gst_Order_Table
### Functions
- **get_instance**
- **init**
- **woogst_admin_gst_order_table**
- **add_admin_order_list_custom_column**
  - Parameters: $columns
- **display_wc_order_list_custom_column_content**
  - Parameters: $column, $order
- **dropdown_filter_for_gst_orders_list**
- **filtered_data_query_args_for_order_list_table**
  - Parameters: $query_args
- **add_gst_to_woocommerce_order_fields**
  - Parameters: $address, $order
- **add_gst_to_woocommerce_admin_billing_fields**
  - Parameters: $billing_fields
- **save_and_validate_custom_gst_billing_fields**
  - Parameters: $order_id, $post_data


## index.php <a id="index_php"></a>


## class-gst-report-table.php <a id="class-gst-report-table_php"></a>
### Classes
- Gst_Report_Table
### Functions
- **get_instance**
- **init**
- **woogst_gst_reports_columns**
  - Parameters: $columns
- **woogst_gst_reports_custom_column**
  - Parameters: $column, $post_id
- **woogst_gst_reports_row_actions**
  - Parameters: $actions, $post
- **woogst_gst_email_status_column_data**
  - Parameters: $post_id
- **woogst_gst_report_actions_column_data**
  - Parameters: $post_id
- **woogst_send_gst_report_email**
- **woogst_regenerate_report**
- **woogst_admin_gst_report_table**


## class-gst-report.php <a id="class-gst-report_php"></a>
### Classes
- GstReport
### Functions
- **get_instance**
- **init**
- **schedule_report_recurrence**
  - Parameters: $schedules
- **email_report_schedule_notice**
- **schedule_report**
- **woogst_report_handler**
  - Parameters: $generate = null, $send_email = null, $month = null, $year = null
- **generate_save_and_send_report**
  - Parameters: $month = null, $year = null
- **get_monthly_orders**
  - Parameters: $month = null, $year = null
- **prepare_email_content**
  - Parameters: $orders
- **generate_csv**
  - Parameters: $orders, $month = null, $year = null
- **generate_order_csv**
  - Parameters: $order_ids, $output_to_browser = true
- **woogst_report**


## deprecated-class-report-table.php <a id="deprecated-class-report-table_php"></a>
### Classes
- Woo_Gst_Report_Table
### Functions
- **__construct**
- **set_wp_admin_notice**
  - Parameters: $message, $type
- **prepare_items**
- **search_box**
  - Parameters: $text, $input_id
- **fetch_gst_reports_data**
  - Parameters: $per_page, $page_number, $search = ''
- **get_total_items**
- **get_columns**
- **get_sortable_columns**
- **column_default**
  - Parameters: $item, $column_name
- **get_bulk_actions**
- **column_cb**
  - Parameters: $item
- **column_name**
  - Parameters: $item
- **column_actions**
  - Parameters: $item
- **process_bulk_action**


## index.php <a id="index_php"></a>


## invoice-template.php <a id="invoice-template_php"></a>


## woogst-admin-display.php <a id="woogst-admin-display_php"></a>


## class-woogst-activator.php <a id="class-woogst-activator_php"></a>
### Classes
- Woogst_Activator
### Functions
- **activate**


## class-woogst-deactivator.php <a id="class-woogst-deactivator_php"></a>
### Classes
- Woogst_Deactivator
### Functions
- **deactivate**


## class-woogst-i18n.php <a id="class-woogst-i18n_php"></a>
### Classes
- Woogst_i18n
### Functions
- **load_plugin_textdomain**


## class-woogst-loader.php <a id="class-woogst-loader_php"></a>
### Classes
- Woogst_Loader
### Functions
- **__construct**
- **add_action**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add_filter**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add**
  - Parameters:  $hooks, $hook, $component, $callback, $priority, $accepted_args 
- **run**


## class-woogst.php <a id="class-woogst_php"></a>
### Classes
- Woogst
### Functions
- **__construct**
- **load_dependencies**
- **set_locale**
- **define_admin_hooks**
- **define_public_hooks**
- **define_gst_report_hooks**
- **define_invoice_hooks**
- **run**
- **get_plugin_name**
- **get_loader**
- **get_version**


## index.php <a id="index_php"></a>


## class-woogst-public.php <a id="class-woogst-public_php"></a>
### Classes
- Woogst_Public
### Functions
- **__construct**
  - Parameters:  $plugin_name, $version 
- **enqueue_styles**
- **enqueue_scripts**


## index.php <a id="index_php"></a>


## woogst-public-functions.php <a id="woogst-public-functions_php"></a>
### Functions
- **gst_fields_add_in_checkout_billing_fields**
  - Parameters: $fields
- **remove_optional_text_from_gst_fields**
  - Parameters: $field, $key, $args, $value
- **gst_fields_sanitize_and_validate**
- **gst_fields_save_in_order_meta**
  - Parameters: $order_id
- **gst_fields_add_in_email_display**
  - Parameters: $order, $sent_to_admin = false, $plain_text = false
- **vat_to_gst_replacement**
  - Parameters: $translated_text, $text


## woogst-public-display.php <a id="woogst-public-display_php"></a>


## class-woogst-validator.php <a id="class-woogst-validator_php"></a>
### Classes
- Woogst_Validator
### Functions
- **__construct**
- **is_woocommerce_installed**
- **is_woocommerce_active**
- **is_woo_tax_active**
- **is_woo_gst_tax_class_slug_exist**
  - Parameters: $slug = null
- **validate_gst_number**
  - Parameters: $gst_number


## functions-logging.php <a id="functions-logging_php"></a>
### Functions
- **log_report_status**
  - Parameters: $message


## index.php <a id="index_php"></a>


## uninstall.php <a id="uninstall_php"></a>


## woogst.php <a id="woogst_php"></a>
### Functions
- **activate_woogst**
- **deactivate_woogst**
- **run_woogst**


## class-woogst-admin.php <a id="class-woogst-admin_php"></a>
### Classes
- Woogst_Admin
### Functions
- **__construct**
  - Parameters: $plugin_name, $version
- **enqueue_styles**
- **enqueue_scripts**
- **register_gst_report_post_type**
- **set_wp_admin_notice**
  - Parameters: $message, $type
- **woo_gst_admin_notice_message**
- **set_wp_admin_notice_active_woo**


## index.php <a id="index_php"></a>


## helper.php <a id="helper_php"></a>
### Functions
- **is_hpos_enabled**


## class-gst.php <a id="class-gst_php"></a>
### Classes
- Gst
### Functions
- **get_instance**
- **init**
- **woogst_gst**


## class-woogst-invoice.php <a id="class-woogst-invoice_php"></a>
### Classes
- WoogstInvoice
### Functions
- **get_instance**
- **init**
- **woogst_invoice**
- **add_invoice_action_order_list_table**
  - Parameters: $actions, $order
- **handle_print_invoice**


## class-gst-order-edit.php <a id="class-gst-order-edit_php"></a>
### Classes
- Gst_Order_Edit
### Functions
- **get_instance**
- **init**
- **woogst_admin_gst_single_order_edit**
- **add_gst_to_woocommerce_order_fields**
  - Parameters: $address, $order
- **add_gst_to_woocommerce_admin_billing_fields**
  - Parameters: $billing_fields
- **save_and_validate_custom_gst_billing_fields**
  - Parameters: $order_id, $post_data


## class-gst-order-table.php <a id="class-gst-order-table_php"></a>
### Classes
- Gst_Order_Table
### Functions
- **get_instance**
- **init**
- **woogst_admin_gst_order_table**
- **add_admin_order_list_custom_column**
  - Parameters: $columns
- **display_wc_order_list_custom_column_content**
  - Parameters: $column, $order
- **dropdown_filter_for_gst_orders_list**
- **filtered_data_query_args_for_order_list_table**
  - Parameters: $query_args
- **add_gst_to_woocommerce_order_fields**
  - Parameters: $address, $order
- **add_gst_to_woocommerce_admin_billing_fields**
  - Parameters: $billing_fields
- **save_and_validate_custom_gst_billing_fields**
  - Parameters: $order_id, $post_data


## index.php <a id="index_php"></a>


## class-gst-report-table.php <a id="class-gst-report-table_php"></a>
### Classes
- Gst_Report_Table
### Functions
- **get_instance**
- **init**
- **woogst_gst_reports_columns**
  - Parameters: $columns
- **woogst_gst_reports_custom_column**
  - Parameters: $column, $post_id
- **woogst_gst_reports_row_actions**
  - Parameters: $actions, $post
- **woogst_gst_email_status_column_data**
  - Parameters: $post_id
- **woogst_gst_report_actions_column_data**
  - Parameters: $post_id
- **woogst_send_gst_report_email**
- **woogst_regenerate_report**
- **woogst_admin_gst_report_table**


## class-gst-report.php <a id="class-gst-report_php"></a>
### Classes
- GstReport
### Functions
- **get_instance**
- **init**
- **schedule_report_recurrence**
  - Parameters: $schedules
- **email_report_schedule_notice**
- **schedule_report**
- **woogst_report_handler**
  - Parameters: $generate = null, $send_email = null, $month = null, $year = null
- **generate_save_and_send_report**
  - Parameters: $month = null, $year = null
- **get_monthly_orders**
  - Parameters: $month = null, $year = null
- **prepare_email_content**
  - Parameters: $orders
- **generate_csv**
  - Parameters: $orders, $month = null, $year = null
- **generate_order_csv**
  - Parameters: $order_ids, $output_to_browser = true
- **woogst_report**


## deprecated-class-report-table.php <a id="deprecated-class-report-table_php"></a>
### Classes
- Woo_Gst_Report_Table
### Functions
- **__construct**
- **set_wp_admin_notice**
  - Parameters: $message, $type
- **prepare_items**
- **search_box**
  - Parameters: $text, $input_id
- **fetch_gst_reports_data**
  - Parameters: $per_page, $page_number, $search = ''
- **get_total_items**
- **get_columns**
- **get_sortable_columns**
- **column_default**
  - Parameters: $item, $column_name
- **get_bulk_actions**
- **column_cb**
  - Parameters: $item
- **column_name**
  - Parameters: $item
- **column_actions**
  - Parameters: $item
- **process_bulk_action**


## index.php <a id="index_php"></a>


## invoice-template.php <a id="invoice-template_php"></a>


## woogst-admin-display.php <a id="woogst-admin-display_php"></a>


## class-woogst-activator.php <a id="class-woogst-activator_php"></a>
### Classes
- Woogst_Activator
### Functions
- **activate**


## class-woogst-deactivator.php <a id="class-woogst-deactivator_php"></a>
### Classes
- Woogst_Deactivator
### Functions
- **deactivate**


## class-woogst-i18n.php <a id="class-woogst-i18n_php"></a>
### Classes
- Woogst_i18n
### Functions
- **load_plugin_textdomain**


## class-woogst-loader.php <a id="class-woogst-loader_php"></a>
### Classes
- Woogst_Loader
### Functions
- **__construct**
- **add_action**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add_filter**
  - Parameters:  $hook, $component, $callback, $priority = 10, $accepted_args = 1 
- **add**
  - Parameters:  $hooks, $hook, $component, $callback, $priority, $accepted_args 
- **run**


## class-woogst.php <a id="class-woogst_php"></a>
### Classes
- Woogst
### Functions
- **__construct**
- **load_dependencies**
- **set_locale**
- **define_admin_hooks**
- **define_public_hooks**
- **define_gst_report_hooks**
- **define_invoice_hooks**
- **run**
- **get_plugin_name**
- **get_loader**
- **get_version**


## index.php <a id="index_php"></a>


## class-woogst-public.php <a id="class-woogst-public_php"></a>
### Classes
- Woogst_Public
### Functions
- **__construct**
  - Parameters:  $plugin_name, $version 
- **enqueue_styles**
- **enqueue_scripts**


## index.php <a id="index_php"></a>


## woogst-public-functions.php <a id="woogst-public-functions_php"></a>
### Functions
- **gst_fields_add_in_checkout_billing_fields**
  - Parameters: $fields
- **remove_optional_text_from_gst_fields**
  - Parameters: $field, $key, $args, $value
- **gst_fields_sanitize_and_validate**
- **gst_fields_save_in_order_meta**
  - Parameters: $order_id
- **gst_fields_add_in_email_display**
  - Parameters: $order, $sent_to_admin = false, $plain_text = false
- **vat_to_gst_replacement**
  - Parameters: $translated_text, $text


## woogst-public-display.php <a id="woogst-public-display_php"></a>


## class-woogst-validator.php <a id="class-woogst-validator_php"></a>
### Classes
- Woogst_Validator
### Functions
- **__construct**
- **is_woocommerce_installed**
- **is_woocommerce_active**
- **is_woo_tax_active**
- **is_woo_gst_tax_class_slug_exist**
  - Parameters: $slug = null
- **validate_gst_number**
  - Parameters: $gst_number


## functions-logging.php <a id="functions-logging_php"></a>
### Functions
- **log_report_status**
  - Parameters: $message
