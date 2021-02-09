<?php


function add_original_price_field($fields)
{
    $args = array(
        'label' => 'Original Price',
        'placeholder' => 'Original Price',
        'id' => 'original_price',
        'desc_tip' => true,
        'description' => 'Original price before the product is imported',
    );

    woocommerce_wp_text_input($args);
}

function save_original_price_field($post_id)
{

    $custom_field_value = isset($_POST['original_price']) ? $_POST['original_price'] : '';

    $product = wc_get_product($post_id);

    $product->update_meta_data('original_price', $custom_field_value);

    $product->save();

}

add_action('woocommerce_product_options_pricing', 'add_original_price_field');
add_action('woocommerce_process_product_meta', 'save_original_price_field');

function external_order_post_type_function()
{
    $labels = array(
        'name' => 'External Orders',
        'singular_name' => 'External Order',
        'menu_name' => 'Dispensary External Orders',
        'all_items' => 'All External Orders',
        'view_item' => 'View External Order',
        'add_new_item' => 'Add New External Order',
        'add_new' => 'Add New',
        'edit_item' => 'Edit External Order',
        'update_item' => 'Update External Order',
        'search_items' => 'Search External Order',

    );

// Set other options for Custom Post Type

    $args = array(
        'label' => 'External Orders',
        'description' => 'External Orders from other dispensary',
        'labels' => $labels,
        'supports' => array('title', 'custom-fields'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,

    );
    register_post_type('external_orders', $args);
}
add_action('init', 'external_order_post_type_function');

add_filter('admin_init', 'my_general_settings_register_fields');
function my_general_settings_register_fields()
{
    register_setting('general', 'stripe_account_id', 'esc_attr');
    add_settings_field('stripe_account_id', '<label for="stripe_account_id">' . __('Copyright Message', 'stripe_account_id') . '</label>', 'stripe_account_id_HTML', 'general');
}

function stripe_account_id_HTML()
{
    $copyright_message = get_option('stripe_account_id', '');
    echo '<input id="copyright_message" style="width: 35%;" type="text" name="stripe_account_id" value="' . $copyright_message . '" />';
}