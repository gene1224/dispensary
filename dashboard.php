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

/**
 * Function to create custom meta box on external order post type editor
 *
 * @return void
 */
function external_order_add_custom_box()
{
    add_meta_box(
        'wporg_box_id',
        'Order Details',
        'external_order_custom_meta_box',
        'external_orders'
    );
}
add_action('add_meta_boxes', 'external_order_add_custom_box');

/**
 * External order custom box html function used to display order details
 *
 * @param [type] $post
 * @return void
 */
function external_order_custom_meta_box($post)
{
    $billing_full_name = get_post_meta($post->ID, 'billing_full_name', true);

    $billing_address = get_post_meta($post->ID, 'billing_address', true);

    $shipping_full_name = get_post_meta($post->ID, 'shipping_full_name', true);

    $shiiping_address = get_post_meta($post->ID, 'shiiping_address', true);

    $date_paid = get_post_meta($post->ID, 'date_paid', true);

    $date_to_display = date_format(date_create($date_paid->date), "F d Y @ H:i:s");

    $date_paid = get_post_meta($post->ID, 'date_paid', true);

    $items = get_post_meta($post->ID, 'items', true);

    $itemHTML = "";

    $total = 0;
    foreach ($items as $key => $item) {
        $product = wc_get_product($item['product_id']);
        $subTotal = $product->get_price() * $item['quantity'];
        $itemHTML .= '<p><a href="http://wpms.net/first/wp-admin/post.php?post=' . $item['product_id'] . '&action=edit">' . $product->get_name() . '</a> - ';
        $itemHTML .= 'Price - $' . number_format($product->get_price(), 2, '.', ',') . ' * Quantity - ' . $item['quantity'] . ' = Subtotal - $' . number_format($subTotal, 2, '.', ',') . '</p>';
        $total += $subTotal;
    }

    ?>
    <p>Name: <?=$billing_full_name?></p>
    <p>Total: $<?=number_format($total, 2, '.', ',')?></p>
    <p>Date Paid: <?=$date_to_display?></p>
    <p>
    Items
    </p>
    <?=$itemHTML?>
    <br>
    <p>Billing Details: <?=strlen($billing_address) != 1 ? $billing_address : $shipping_full_name?></p>
    <p>Shipping Details: <?=strlen($billing_address) != 1 ? $shiiping_address : $shipping_full_name?></p>

    <?php
}