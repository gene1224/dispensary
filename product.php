<?php
// define the woocommerce_product_is_in_stock callback
function filter_woocommerce_product_is_in_stock($instock_this_get_stock_status, $instance)
{
    $product_id = $instance->get_id();

    $source_site_id = get_post_meta($product_id, 'source_site_id', true);

    $source_product_id = get_post_meta($product_id, 'source_product_id', true);

    if ($source_site_id && $source_product_id) {
        return get_stocks_status($source_product_id, $source_site_id);
    } else {
        return $instock_this_get_stock_status;
    }

};


function get_stocks_status($source_product_id, $source_site_id, $site_url = '')
{
    switch_to_blog($source_site_id);

    $product = wc_get_product($source_product_id);

    $product->is_in_stock();

    $stock_status = 'outofstock' !== $product->get_stock_status();

    restore_current_blog();

    return $stock_status;
}


function get_stocks_status_ajax()
{
    if (!isset($_REQUEST['product_id'])) {
        return;
    }

    $product_id = $_REQUEST['product_id'];

    $product = wc_get_product($product_id);

    $stock_status = $product ? 'outofstock' !== $product->get_stock_status() : false;

    $response = array(
        'in_stock' => $stock_status,
    );

    echo json_encode($response, true);

    die();
}


add_action("wp_ajax_get_stocks_status_ajax", "get_stocks_status_ajax");
add_action("wp_ajax_nopriv_get_stocks_status_ajax", "get_stocks_status_ajax");
add_filter('woocommerce_product_is_in_stock', 'filter_woocommerce_product_is_in_stock', 10, 2);