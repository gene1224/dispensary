<?php
add_filter('woocommerce_add_to_cart_redirect', 'redirect_always_to_cheeckout');
function redirect_always_to_cheeckout()
{
    return wc_get_checkout_url();
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

function schedule_site_duplication($order_id)
{
    if (!get_current_user_id()) {
        return;
    }

    if (get_user_meta(get_current_user_id(), 'site_created', true) || get_user_meta(get_current_user_id(), 'site_clone_started', true)) {
        return;
    }

    update_user_meta(get_current_user_id(), 'template_selector', get_post_meta($order_id, 'template_selector', true) ?: 123);

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
                'templatesAvailable' => get_templates(),
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
add_action('wp_ajax_nopriv_check_input_domain', 'check_input_domain');

function check_subdomain_function()
{
    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'checkout_document_upload')) {
        die();
    }

    if (!isset($_REQUEST['subdomain'])) {
        die();
    }

    $response = array('exist' => false);

    $sites = get_sites();

    foreach ($sites as $site) {
        if ($site->domain == $_REQUEST['subdomain']) {
            $response['exist'] = true;
            break;
        }
    }

    echo json_encode($response);

    die();
}

add_action('wp_ajax_check_subdomain', 'check_subdomain_function');
add_action('wp_ajax_nopriv_check_subdomain', 'check_subdomain_function');

function product_category_filter_changes()
{
    foreach (WC()->cart->get_cart() as $item_key => $values) {
        $product = $values['data'];

        if (
            !stripos(strtolower($product->get_name()), 'pro')
            || !stripos(strtolower($product->get_name()), 'premium')
        ) {
            if (!WC()->cart->subtotal > 0) {
                //remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
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
    echo 'test';
    if (!isset($_REQUEST['user_id'])) {
        die();
    }
    echo 'HELO' . get_user_meta($_REQUEST['user_id'], 'site_clone_started', true) . '|';
    wp_schedule_single_event(time(), 'duplicate_site', array($_REQUEST['user_id']));
    echo "STARTING to create site for " . $_REQUEST['user_id'];
    die();
}

add_action('site_duplicate_finihsed', 'send_site_confirmation_emails', 1, 1);

add_action('wp_ajax_send_confirmation', 'send_site_confirmation_emails');
function send_site_confirmation_emails($blog_id = 0)
{
    if ($blog_id == 0 && !isset($_GET['blog_id'])) {
        return;
    }

    $blog_id = isset($_GET['blog_id']) ? $_GET['blog_id'] : $blog_id;
    error_log("MAILER START TO BLOG" . $blog_id);
    global $timber;

    $user_id = 0;

    $blog_details = get_blog_details($blog_id);

    $args = array(
        'meta_query' => array(
            array(
                'key' => '_wc_memberships_profile_field_subdomain_name',
                'value' => $blog_details->domain,
                'compare' => '=',
            ),
        ),
    );

    $users = get_users($args);

    if ($users) {
        foreach ($users as $user) {
            $user_id = $user->data->ID;
        }
    }

    error_log('SENDING TO' . $user_id);
    if ($user_id == 0) {
        return;
    }

    add_user_to_blog($blog_id, $user_id, 'administrator');

    $user = get_userdata($user_id);

    $context = array(
        "user" => $user,
        "domain" => $blog_details->domain,
    );

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $email_message = $timber->compile('emails/site-created.twig', $context);

    wp_mail($user->user_email, "Site creation complete", $email_message, $headers);

    error_log("EMAIL SENT to " . $user_id);

    update_user_meta($user_id, 'site_created', true);

    update_user_meta($user_id, 'dispensary_blog_id', $blog_id);

    switch_to_blog($blog_id);

    update_user_option($user_id, 'show_admin_bar_front', 'false');

    $blogname = get_user_meta($user_id, '_wc_memberships_profile_field_dispensary_name', true) ?: 'dispensary-' . $user_id;
    // update_option('blogname', $blogname);
    restore_current_blog();

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

    $user_plans = $has_subscription ? wc_memberships_get_user_memberships() : [];

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

function reschedule_duplication()
{
    if (isset($_GET['user_id_manual'])) {
        $user_id = $_GET['user_id_manual'];
        update_user_meta($_GET['user_id_manual'], 'site_created', false);
        update_user_meta($_GET['user_id_manual'], 'site_clone_started', false);
        error_log("MANUAL RESET SITE COPY DONE");
        wp_schedule_single_event(time(), 'duplicate_site', array($_GET['user_id_manual']));
    }

}
add_action('wp_cli_copy_site', 'wp_cli_copy_site');
add_action('wp_ajax_copy_site', 'reschedule_duplication');
add_action('wp_ajax_nopriv_copy_site', 'reschedule_duplication');

function wc_billing_field_strings($translated_text, $text, $domain)
{
    switch ($translated_text) {
        case 'Billing details':
            $translated_text = __('Dispensary Information', 'woocommerce');
            break;
    }
    return $translated_text;
}
add_filter('gettext', 'wc_billing_field_strings', 20, 3);

add_action('woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta');

function custom_checkout_field_update_order_meta($order_id)
{
    if (!empty($_POST['template_selector'])) {
        update_post_meta($order_id, 'template_selector', sanitize_text_field($_POST['template_selector']));
    }
}

//Added
function submit_form_checkout()
{
    echo "<div id='formCheckoutSubmitFormBottom' class='formCheckoutSubmitFormBottom'>";
    echo '<label>Subscription Overview</label>';
    do_action('woocommerce_checkout_order_review');
    echo "</div>";
}
add_filter('woocommerce_after_checkout_billing_form', 'submit_form_checkout', 99, 99);

function add_recurring_postage_fees($cart)
{
    if (!empty($cart->recurring_cart_key)) {
        remove_action('woocommerce_cart_totals_after_order_total', array('WC_Subscriptions_Cart', 'display_recurring_totals'), 10);
        remove_action('woocommerce_review_order_after_order_total', array('WC_Subscriptions_Cart', 'display_recurring_totals'), 10);
    }
}
add_filter('woocommerce_cart_calculate_fees', 'add_recurring_postage_fees', 10, 1);

add_filter('gettext', 'wc_renaming_checkout_total', 20, 3);
function wc_renaming_checkout_total($translated)
{

    if (is_checkout) {
        $text = array(
            'Your order' => 'Subscription Overview',
            'Product' => 'Plan',
        );
        $translated = str_ireplace(array_keys($text), $text, $translated);
    }
    return $translated;
}

add_filter('woocommerce_order_button_html', 'change_checkout_button_text');

function change_checkout_button_text($button_text)
{
    $product = false;
    $subscription = false;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        $subscription = $cart_item["data"];
    }
    $product_name = $product->get_name();

    if ($product_name == "QRx Dispensary Basic Plan") {
        return '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="Select Plan" data-value="Select Plan">Select FREE Plan</button>';
    } elseif ($product_name == "QRx Dispensary Pro Plan") {
        return '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="Select Plan" data-value="Select Plan">Select PRO Plan</button>';
    } elseif ($product_name == "QRx Dispensary Premium Plan") {
        return '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="Select Plan" data-value="Select Plan">Select PREMIUM Plan</button>';
    } else {
        return '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="Select Plan" data-value="Select Plan">Select Plan</button>';
    }

}

add_action('woocommerce_thankyou', 'upgrade_email', 10, 1);
add_action('wp_ajax_mailtest', 'upgrade_email');

function upgrade_email($order_id)
{
    global $timber;
    if (!get_current_user_id()) {
        return;
    }

    $order = wc_get_order($order_id);
    $product_names = [];

    foreach ($order->get_items() as $item_key => $item) {
        $product_names[] = $item->get_name();
    }

    $context = array(
        'display_name' => wp_get_current_user()->data->display_name,
        'product' => join(",", $product_names),
        'total' => $order->get_total(),
    );

    if (!wc_memberships_is_user_active_member($user_id, 3104)) {
        // echo $timber->compile('emails/upgrade.twig',  $context);
        $messageg = $timber->compile('emails/upgrade.twig', $context);
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail(wp_get_current_user()->data->user_email, $subject, $message, $headers);
    }

}

//End Added
