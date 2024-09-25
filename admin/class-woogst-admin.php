<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woogst
 * @subpackage Woogst/admin
 * @author     Owlth Tech <owlthtech@gmail.com>
 */

class Woogst_Admin
{


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Add custom columns to order list
		add_filter('manage_edit-shop_order_columns', 'add_admin_order_list_custom_column', 20);
		add_filter('manage_woocommerce_page_wc-orders_columns', 'add_admin_order_list_custom_column', 20);

		add_action( 'init', 'register_order_reports_cpt' );
		
		add_filter('manage_order-reports_posts_columns', 'set_custom_order_reports_columns');
		add_action('manage_order-reports_posts_custom_column', 'custom_order_reports_column', 10, 2);
		
		add_filter('post_row_actions', 'custom_order_reports_row_actions', 10, 2);

		add_filter('bulk_actions-edit-order-reports', 'register_order_reports_bulk_actions');
		add_filter('handle_bulk_actions-edit-order-reports', 'handle_order_reports_bulk_actions', 10, 3);

		add_action('add_meta_boxes', 'order_reports_add_meta_boxes');

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woogst_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woogst_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$has_woocomemrce = class_exists('WooCommerce');
		if (!$has_woocomemrce) {
			return;
		}
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ($screen_id === wc_get_page_screen_id('shop-order')) {
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woogst-admin.css', array(), $this->version, 'all');
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woogst_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woogst_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$has_woocomemrce = class_exists('WooCommerce');
		if (!$has_woocomemrce) {
			return;
		}
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Check screen base and page
		if (class_exists('WooCommerce') && $screen_id === wc_get_page_screen_id('shop-order')) {
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woogst-admin.js', array('jquery'), $this->version, false);
		}

	}

	
}

function register_order_reports_cpt() {
    $labels = array(
        'name'               => _x( 'Order Reports', 'post type general name' ),
        'singular_name'      => _x( 'Order Report', 'post type singular name' ),
        'menu_name'          => _x( 'Order Reports', 'admin menu' ),
        'name_admin_bar'     => _x( 'Order Report', 'add new on admin bar' ),
        'add_new'            => _x( 'Add New', 'report' ),
        'add_new_item'       => __( 'Add New Report' ),
        'new_item'           => __( 'New Report' ),
        'edit_item'          => __( 'Edit Report' ),
        'view_item'          => __( 'View Report' ),
        'all_items'          => __( 'All Reports' ),
        'search_items'       => __( 'Search Reports' ),
        'not_found'          => __( 'No reports found.' ),
        'not_found_in_trash' => __( 'No reports found in Trash.' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,  // Not publicly accessible
        'exclude_from_search'=> true,
        'publicly_queryable' => false,
        'show_ui'            => true,   // Visible in admin interface
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'order-reports' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'supports'           => array( 'title', 'editor', 'custom-fields' ), // You can add more if needed
        'capabilities'       => array(
            'edit_posts'          => 'manage_options', // Only admins can edit and view
            'edit_others_posts'   => 'manage_options',
            'delete_posts'        => 'manage_options',
            'delete_others_posts' => 'manage_options',
            'read_private_posts'  => 'manage_options',
            'publish_posts'       => 'manage_options',
        ),
    );

    register_post_type( 'order-reports', $args );
}

// Add custom columns to 'order-reports' table in the admin
function set_custom_order_reports_columns($columns) {
    $columns = array(
        'cb'              => '<input type="checkbox" />', // Checkbox for bulk actions
        'title'           => __('Report Title'),
        'report_date'     => __('Report Date'),
        'email_status'    => __('Email Status'),
        'csv_file'        => __('CSV File'),
        'date'            => __('Date'), // Use default date column
    );
    return $columns;
}

// Populate the custom columns with data
function custom_order_reports_column($column, $post_id) {
	
    switch ($column) {
        case 'report_date':
            // Retrieve and display report date (custom meta field)
            $report_date = get_post_meta($post_id, 'report_date', true);
            echo $report_date ? esc_html($report_date) : 'N/A';
            break;

        case 'email_status':
            // Retrieve and display email status (custom meta field)
            $email_status = get_post_meta($post_id, 'email_status', true);
            echo $email_status ? esc_html($email_status) : 'N/A';
            break;

        case 'csv_file':
            // Retrieve and display link to CSV file
            $csv_file_path = get_post_meta($post_id, 'csv_file_path', true);
            if ($csv_file_path) {
                echo '<a href="' . $csv_file_path . '">Download CSV</a>';
            } else {
                echo 'N/A';
            }
            break;
    }
}



// Add custom row actions to the 'order-reports' table
function custom_order_reports_row_actions($actions, $post) {
    if ($post->post_type == 'order-reports') {
        $csv_file_path = get_post_meta($post->ID, 'csv_file_path', true);
		error_log($post->ID);

        // Add a custom action to view the CSV file
        if ($csv_file_path) {
            $actions['view_csv'] = '<a href="' . esc_url($csv_file_path) . '" target="_blank">View CSV</a>';
        }
    }

    return $actions;
}


// Register custom bulk actions for 'order-reports'
function register_order_reports_bulk_actions($bulk_actions) {
    $bulk_actions['download_csv'] = __('Download CSV');
    return $bulk_actions;
}

// Handle the custom bulk action
function handle_order_reports_bulk_actions($redirect_to, $doaction, $post_ids) {
    if ($doaction !== 'download_csv') {
        return $redirect_to;
    }

    // Loop through the selected reports and download their CSVs
    foreach ($post_ids as $post_id) {
        $csv_file_path = get_post_meta($post_id, 'csv_file_path', true);
        if ($csv_file_path) {
            // Add your logic to handle CSV download (or zip multiple files)
        }
    }

    return $redirect_to;
}



// Add a meta box to the 'order-reports' CPT
function order_reports_add_meta_boxes() {
    add_meta_box(
        'order_reports_meta_box',      // ID of the meta box
        __('Order Report Details'),    // Title of the meta box
        'order_reports_meta_box_html', // Callback to render the meta box
        'order-reports',               // Post type where the box will appear
        'normal',                      // Position
        'high'                         // Priority
    );
}

// Callback function to display the meta box HTML
function order_reports_meta_box_html($post) {
    // Retrieve current meta values
    $csv_file_path = get_post_meta($post->ID, 'csv_file_path', true);
    $email_status = get_post_meta($post->ID, 'email_status', true);
    $order_ids = get_post_meta($post->ID, 'order_ids', true);

    ?>
    <p><strong>CSV File Path:</strong> <a href="<?php echo esc_url(wp_get_attachment_url($csv_file_path)); ?>" target="_blank"><?php echo esc_html($csv_file_path); ?></a></p>
    <p><strong>Email Status:</strong> <?php echo esc_html($email_status); ?></p>
    <p><strong>Order IDs:</strong> <?php echo esc_html($order_ids); ?></p>
    <?php
}
