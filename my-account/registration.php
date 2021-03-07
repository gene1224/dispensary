<?php
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
    return wcs_user_has_subscription() ? "Signup and Subscribe" : "Update Plan";
}

add_filter('woocommerce_create_account_default_checked', function ($checked) {
    return true;
});

add_action('woocommerce_thankyou', 'schedule_site_duplication', 10, 1);

function schedule_site_duplication($args)
{
    if (!get_current_user_id()) {
        return;
    }

    if (get_user_meta(get_current_user_id(), 'site_created', true) || get_user_meta(get_current_user_id(), 'site_clone_started', true)) {
        return;
    }

    wp_schedule_single_event(time(), 'duplicate_site', array(get_current_user_id()));
}


function checkout_custom_script()
{
    wp_register_script('custom_checkout_scripts', plugins_url('../assets/js/checkout.js', __FILE__), array('jquery', 'sweetalert'), '2.5.1');
    if (function_exists('is_woocommerce')) {

        if (is_checkout()) {
            $js_objects = array(
                'url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('checkout_document_upload'),
                'base_domain' => $_SERVER['HTTP_HOST'] ?: 'qrxdispensary.com',
            );
            wp_localize_script('custom_checkout_scripts', 'wp_ajax', $js_objects);
            wp_enqueue_script('custom_checkout_scripts');
        }
    }
}
add_action('wp_enqueue_scripts', 'checkout_custom_script', 99);

function checkout_document_upload()
{
    // if (!isset_($_REQUEST['nonce']) || wp_verify_nonce($_REQUEST['nonce'], 'checkout_document_upload')) {
    //     die();
    // }
    $upload_overrides = array('test_form' => false);
    if (isset($_FILES['business_information'])) {
        $message = wp_handle_upload($_FILES['business_information'], $upload_overrides);
        echo json_encode($message);
        die();
    }
    die();
}
add_action('wp_ajax_checkout_document_upload', 'checkout_document_upload');

add_filter('woocommerce_add_to_cart_validation', 'bbloomer_only_one_in_cart', 99, 2);
function bbloomer_only_one_in_cart($passed, $added_product_id)
{
    wc_empty_cart();
    return $passed;
}

function check_input_domain()
{

    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'checkout_document_upload')) {
        die();
    }

    if (!isset($_REQUEST['domain'])) {
        die();
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.ote-godaddy.com/v1/domains/available?checkType=FAST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "[  \"" . $_REQUEST['domain'] . "\"]");

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: sso-key UzQxLikm_46KxDFnbjN7cQjmw6wocia:46L26ydpkwMaKZV6uVdDWe';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        echo $result;
    }
    curl_close($ch);
    die();

}
add_action('wp_ajax_check_input_domain', 'check_input_domain');

function check_subdomain_function()
{
    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'checkout_document_upload')) {
        die();
    }

    if (!isset($_REQUEST['subdomain'])) {
        die();
    }

    $response = array('exist' => false);

    $sites = wp_get_sites();

    foreach ($sites as $site) {
        if ($site['domain'] == $_REQUEST['subdomain']) {
            $response['exist'] = true;
            break;
        }
    }

    echo json_encode($response);

    die();
}

add_action('wp_ajax_check_subdomain', 'check_subdomain_function');

function product_category_filter_changes()
{
    foreach (WC()->cart->get_cart() as $item_key => $values) {
        $product = $values['data'];

        if (
            !stripos(strtolower($product->get_name()), 'pro')
            || !stripos(strtolower($product->get_name()), 'premium')
        ) {
            if (!WC()->cart->subtotal > 0) {
                remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
            }
        }
    }

}
add_action('template_redirect', 'product_category_filter_changes');

//COMMENT OUT WHEN PAYMENT IS AVAILABLE
add_filter('woocommerce_cart_needs_payment', 'filter_cart_needs_payment_callback', 100, 2);
function filter_cart_needs_payment_callback($needs_payment, $cart)
{
    $payment_required = false; //$cart->subtotal > 0 ? $needs_payment : false
    // if($payment_required) {
    wp_register_style('checkout_custom_css', plugins_url('../assets/css/checkout.css', __FILE__), [], '1.0.0', 'all');
    wp_enqueue_style('checkout_custom_css');
    // }

    return $payment_required;
}

add_action('wp_ajax_create_site', 'create_site_function');
function create_site_function()
{
    if (!isset($_REQUEST['user_id'])) {
        die();
    }

    wp_schedule_single_event(time(), 'duplicate_site', array($_REQUEST['user_id']));
    echo "STARTING to create site for " . $_REQUEST['user_id'];
    die();
}

add_action('site_duplicate_finihsed', 'send_site_confirmation_emails', 1, 2);
function send_site_confirmation_emails($user_id, $site_creatation_data)
{

    global $timber;

    $blog_id = 0;
    foreach (get_sites() as $site) {
        if ($site->domain == $site_creatation_data['newdomain']) {
            $blog_id = $site->blog_id;
            break;
        }
    }
    add_user_to_blog($blog_id, $user_id, 'administrator');

    $user = get_userdata($user_id);

    $context = array(
        "user" => $user,
        "domain" => $site_creatation_data['newdomain'],
    );

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $email_message = $timber->compile('emails/site-created.twig', $context);

    wp_mail($user->user_email, "Site creation complete", $email_message, $headers);

}

add_filter('wc_add_to_cart_message_html', '__return_false');

add_action('woocommerce_before_checkout_form', 'pre_checkout_information');
function pre_checkout_information()
{
    global $timber;

    $product = false;
    $subscription = false;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        $subscription = $cart_item["data"];
    }

    $has_subscription = wcs_user_has_subscription();

    $user_plans = $has_subscription ?  wc_memberships_get_user_memberships() : [];
    
    $user_plan_names = array_map(function ($plan) {
        return $plan->plan->name;
    }, $user_plans);
    
    $context = array(
        'product_name' => $product->get_name(),
        'product_price' => $product->get_price(),
        'has_subscription' => $has_subscription,
        'user_plan_names' => join(', ', $user_plan_names),
        
    );
    $timber->render('template-parts/pre-checkout.twig', $context);
}
