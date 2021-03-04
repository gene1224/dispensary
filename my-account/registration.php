<?php
function custom_checkout_fields()
{
    return array(
        'account_username' => array(
            'type' => 'text',
            'label' => __('Account username', 'woocommerce'),
            'placeholder' => _x('Username', 'placeholder', 'woocommerce'),
            'required' => true,
        ),
        'account_password' => array(
            'type' => 'password',
            'label' => __('Account password', 'woocommerce'),
            'placeholder' => _x('Password', 'placeholder', 'woocommerce'),
            'required' => true,
        ),
        'site_name' => array(
            'type' => 'text',
            'label' => "Dispensary Name",
            'placeholder' => "Website Name",
            'required' => true,
        ),
        'domain' => array(
            'type' => 'text',
            'label' => "Domain Name",
            'placeholder' => "Domain Name",
            'required' => true,
        ),
        'subdomain' => array(
            'type' => 'text',
            'label' => "Domain Name",
            'placeholder' => "Domain Name",
            'required' => true,
        ),
        'file_attachment_id' => array(
            'type' => 'hidden',
        ),
    );
}

add_filter('add_to_cart_redirect', 'redirect_always_to_cheeckout');
function redirect_always_to_cheeckout()
{
    global $woocommerce;
    $cw_redirect_url_checkout = $woocommerce->cart->get_checkout_url();
    return $cw_redirect_url_checkout;
}

add_filter('woocommerce_product_single_add_to_cart_text', 'select_plan_button');
add_filter('woocommerce_product_add_to_cart_text', 'select_plan_button');
function select_plan_button()
{
    return __('Select Plan', 'woocommerce');
}

add_filter('woocommerce_enable_order_notes_field', '__return_false', 9999);

add_filter('woocommerce_checkout_fields', 'remove_order_notes');

function remove_order_notes($fields)
{
    $fields['account'] = custom_checkout_fields();

    foreach (WC()->cart->get_cart() as $item_key => $values) {
        $product = $values['data'];
        if (!stripos($product->get_name(), 'pro') || !stripos($product->get_name(), 'premium')) {
            unset($fields['account']['domain']);
        } else {
            unset($fields['account']['subdomain']);
        }
    }

    $fields['billing']['file_attachment_id'] = array(
        'type' => 'hidden',
    );

    return $fields;
}

function custom_fields_saving($customer_id, $posted)
{
    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);

    foreach (custom_checkout_fields() as $key => $custom_fields) {
        if ($key == 'account_password' || $key == 'account_username') {
            continue;
        }
        if (isset($posted[$key]) && $posted[$key] != '') {
            $data = sanitize_text_field($posted[$key]);
            update_user_meta($customer_id, $key, $data);
        }
    }
}
add_action('woocommerce_checkout_update_user_meta', 'custom_fields_saving', 10, 2);

add_filter('woocommerce_create_account_default_checked', function ($checked) {
    return true;
});

add_action('woocommerce_thankyou', 'duplicate_site', 10, 1);

add_action('woocommerce_after_checkout_billing_form', 'some_custom_checkout_field', 11);
function some_custom_checkout_field($checkout)
{
    global $timber;

    echo $timber->compile('checkout/file-input.twig', $context);
}
