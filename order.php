<?php

/**
 * Initiate Order - Function that will split the amount.
 *
 * @param int $order_id
 * @return void
 */
function initiate_order($order_id)
{
    $sales = [];

    $sales_to_source_dispensary = 0;

    $order = wc_get_order($order_id);

    foreach ($order->get_items() as $item_id => $item) {

        $product_id = $item['variation_id'] > 0 ? $item['variation_id'] : $item['product_id'];

        $product = wc_get_product($product_id);

        $original_price = $product->get_meta('original_price');

        $quantity = $item->get_quantity();

        if (isset($original_price)) {
            $sales_to_source_dispensary = $sales_to_source_dispensary + ($quantity * $original_price);

            $source_site_id = get_post_meta($product_id, 'source_site_id', true);

            //TODO FLAT OUT THE ARRAY
            $sales[] = array(
                "source_site_id" => $source_site_id,
                "item" => $product_id,
                "quantity" => $quantity,
                "total" => $quantity * $original_price,
            );
        }

    }

    $new_order_total = $order->get_total() - $sales_to_source_dispensary;

    add_post_meta($order_id, 'sales_to_source_dispensary', $sales_to_source_dispensary, true);

    $order->set_total($new_order_total);

    // $order->save();
}

/**
 * We need to update back the original sales amount
 * Since the sales amount is splitted between product_source and the seller
 * @param [type] $order_id
 * @return void
 */
function payment_complete($order_id)
{

    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order->is_paid()) {
        return;
    }

    $sales_to_source_dispensary = get_post_meta($order_id, 'sales_to_source_dispensary', true);

    $stripe = new \Stripe\StripeClient(
        'sk_test_51IHOEsGt2600zxIgroP7sf0roJx4XaOoQ4eJVAPMPqrCpEzcglIdGtQPqkE8xiZ9dGqsg3oEgWiYPR20mG9FfJz200jSVbP2QH'
      );
    $stripe->transfers->create([
        'amount' => $sales_to_source_dispensary * 100,
        'currency' => 'usd',
        'destination' => 'acct_1IHPfSKhUsdmrRlw',
        'transfer_group' => 'ORDER_95',
    ]);

    create_orders_on_other_sites($order_id);
}

add_action('woocommerce_checkout_order_processed', 'initiate_order', 10, 1);
add_action('woocommerce_payment_complete', 'payment_complete', 10, 1);


function create_orders_on_other_sites($order_id)
{
    $order = wc_get_order($order_id);

    $site_orders = array();

    $order_address = array(
        'billing_address' => array(
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'company' => $order->get_billing_company(),
            'address_1' => $order->get_billing_address_1(),
            'address_2' => $order->get_billing_address_2(),
            'city' => $order->get_billing_city(),
            'state' => $order->get_billing_state(),
            'postcode' => $order->get_billing_postcode(),
            'country' => $order->get_billing_country(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
        ),
        'shipping_address' => array(
            'first_name' => $order->get_shipping_first_name(),
            'last_name' => $order->get_shipping_last_name(),
            'company' => $order->get_shipping_company(),
            'address_1' => $order->get_shipping_address_1(),
            'address_2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'state' => $order->get_shipping_state(),
            'postcode' => $order->get_shipping_postcode(),
            'country' => $order->get_shipping_country(),
        ),
    );

    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();

        $source_product_id = get_post_meta($product_id, 'source_product_id', true);

        $source_site_id = get_post_meta($product_id, 'source_site_id', true);

        if ($source_product_id && $source_site_id) {
            $site_orders[$source_site_id][] = array(
                'product_id' => $source_product_id,
                'quantity' => $item->get_quantity(),
            );
        }

    }

    foreach ($site_orders as $key => $site_order) {
        create_order_from_external($site_order, $key, $order_address);
    }
}

function create_order_from_external($items, $source_site_id, $order_address)
{
    switch_to_blog($source_site_id);

    $order = wc_create_order();

    $billing_address = $order_address['billing_address'];

    $shipping_address = $order_address['shipping_address'];

    $order->set_address($billing_address, 'billing');

    $order->set_address($shipping_address, 'shipping');

    foreach ($items as $key => $item) {
        $product = wc_get_product($item['product_id']);

        $order->add_product($product , $item['quantity']);
    }

    $order->update_status('on-hold');

    $order->save();

    restore_current_blog();
}