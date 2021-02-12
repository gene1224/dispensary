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

    //TODO ADD SHIPPING

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

        $source_site_url = get_post_meta($product_id, 'source_site_url', true);

        if ($source_product_id && $source_site_id) {
            $site_orders[$source_site_id][] = array(
                'product_id' => $source_product_id,
                'quantity' => $item->get_quantity(),
            );
        } else if ($source_site_url && $source_product_id) {
            $site_orders[$source_site_url][] = array(
                'product_id' => $source_product_id,
                'quantity' => $item->get_quantity(),
            );
        }
    }

    $site_information = array(
        'order_id' => $order_id,
        'site_id' => get_current_blog_id(),
        'site_url' => get_site_url(),
    );

    $total = $order->get_total();

    $amount_paid_to_other_site = 0;

    foreach ($site_orders as $key => $site_order) {

        if (strpos($key, 'http') !== false) {
            $result = ajax(
                $key . 'wp-admin/admin-ajax.php',
                array(
                    'action' => 'create_order_from_external_ajax',
                    'items' => $site_order,
                    'order_address' => $order_address,
                    'site_information' => $site_information,
                ),
                'POST'
            );
            $amount_paid_to_other_site += isset($result['amount']) ? $result['amount'] : 0;
        } else {
            $amount_paid_to_other_site += create_order_from_external($site_order, $key, $order_address, $site_information);
        }

    }

    $percentage_to_transfer = ($total - $amount_paid_to_other_site) * 0.10;

    $amount_to_client_stripe = $total - $amount_paid_to_other_site - $percentage_to_transfer;

    create_payments('acct_1IHPfSKhUsdmrRlw', $percentage_to_transfer, $site_information);

    $stripe_account_id = get_option('stripe_account_id', '');

    if (isset($stripe_account_id) && $stripe_account_id != '') {
        create_payments($stripe_account_id, $amount_to_client_stripe, $site_information);
    }

}

function create_order_from_external_ajax()
{
    if ($_REQUEST['action'] == 'create_order_from_external_ajax') {
        $data = json_decode(urldecode($_POST['data']), true);

        $amount = create_order_from_external($data['items'], 0, $data['order_address'], $data['site_information'], true);

        echo json_encode(array('amount' => $amount));
    }
    die();
}
add_action("wp_ajax_create_order_from_external_ajax", "create_order_from_external_ajax");
add_action("wp_ajax_nopriv_create_order_from_external_ajax", "create_order_from_external_ajax");

function create_order_from_external($items, $source_site_id, $order_address, $site_information, $external = false)
{
    if (!$external) {
        switch_to_blog($source_site_id);
    }

    $order = wc_create_order();

    $billing_address = $order_address['billing_address'];

    $shipping_address = $order_address['shipping_address'];

    $order->set_address($billing_address, 'billing');

    $order->set_address($shipping_address, 'shipping');

    foreach ($items as $key => $item) {
        $product = wc_get_product($item['product_id']);

        $order->add_product($product, $item['quantity']);
    }

    $order->add_order_note('Order created from : ' . $site_information['site_url']);

    $order->add_order_note('External Order ID : ' . $site_information['order_id']);

    $order->calculate_taxes();

    $order->calculate_totals();

    $order->update_status('processing');

    $order->save();

    $stripe_account_id = get_option('stripe_account_id', '');

    if (isset($stripe_account_id) && $stripe_account_id != '') {
        create_payments(
            $stripe_account_id,
            $order->get_total(),
            $site_information
        );
    }
    if (!$external) {
        restore_current_blog();
    }
    return $order->get_total();
}

function create_payments($account_id, $amount, $site_information)
{
    // TODO - ADD IT ON THE SETTINGS
    $stripe = new \Stripe\StripeClient(
        'sk_test_51IHOEsGt2600zxIgroP7sf0roJx4XaOoQ4eJVAPMPqrCpEzcglIdGtQPqkE8xiZ9dGqsg3oEgWiYPR20mG9FfJz200jSVbP2QH'
    );

    $stripe->transfers->create([
        'amount' => $amount * 100,
        'currency' => 'usd',
        'destination' => $account_id,
        'transfer_group' => 'ORDER_ID_' . $site_information['site_id'] . '_' . $site_information['order_id'],
        'description' => 'Payments for order ' . $site_information['order_id'] . ' - ' . get_blog_details($site_information['site_id'])->domain,
    ]);
}
