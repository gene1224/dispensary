<?php

function handle_custom_query_var($query, $query_vars)
{
    if (!empty($query_vars['source_product_id'])) {
        $query['meta_query'][] = array(
            'key' => 'source_product_id',
            'value' => '',
            'compare' => '!=',
        );
    }
    if (!empty($query_vars['skus'])) {
        $query['meta_query'][] = array(
            'key' => '_sku',
            'value' => $query_vars['skus'],
            'compare' => 'IN',
        );
    }

    return $query;
}
add_filter('woocommerce_product_data_store_cpt_get_products_query', 'handle_custom_query_var', 10, 2);

function add_to_cart_list()
{

    if (!isset($_POST['source_product_id']) || !isset($_POST['sku'])) {
        die();
    }

    $user_id = get_current_user_id();

    $listing_cart = get_user_meta($user_id, 'listing_cart', true) ?: array();

    $found = false;

    foreach ($listing_cart as $item) {
        if ($item['source_product_id'] == $_POST['source_product_id'] && $item['sku'] == $_POST['sku']) {
            $found = true;
            break;
        }
    }

    if ($found) {
        echo json_encode(get_user_meta($user_id, 'listing_cart', true));
        die();
    }

    $listing_cart[] = array(
        'source_site_url' => 'https://allstuff420.com',
        'source_product_id' => $_POST['source_product_id'],
        'listing_price' => $_POST['listing_price'] ?: 0,
        'sku' => $_POST['sku'],
    );

    update_user_meta($user_id, 'listing_cart', $listing_cart);

    echo json_encode(get_user_meta($user_id, 'listing_cart', true));

    die();
};
add_action('wp_ajax_add_to_cart_list', 'add_to_cart_list');

function empty_cart_list()
{
    $user_id = isset($_REQUEST["uid"]) ? $_REQUEST["uid"] : get_current_user_id();

    update_user_meta($user_id, 'listing_cart', array());

    echo json_encode(get_user_meta($user_id, 'listing_cart', true));

    die();
};
add_action('wp_ajax_empty_cart_list', 'empty_cart_list');


//FUTURE ADD SITE ID SELECTION
function remove_product_in_cart()
{
    if (!isset($_POST['source_product_id']) || !isset($_POST['sku'])) {
        die();
    }

    $user_id = get_current_user_id();

    $listing_cart = get_user_meta($user_id, 'listing_cart', true) ?: array();

    $listing_cart = array_filter($listing_cart, function ($item) {
        return $item['source_product_id'] != $_POST['source_product_id'] || $item["sku"] != $_POST["sku"];
    });

    update_user_meta($user_id, 'listing_cart', $listing_cart);

    echo json_encode(get_user_meta($user_id, 'listing_cart', true));

    die();
}

add_action('wp_ajax_remove_product_in_cart', 'remove_product_in_cart');

//FUTURE ADD SITE ID SELECTION
function start_import()
{
    $user_id = get_current_user_id();

    $site_id = 0;
    foreach (get_blogs_of_user($user_id, true) as $blog) {
        if ($blog->userblog_id != 1) {
            $site_id = $blog->userblog_id;
            break;
        }
    }

    update_user_meta($user_id, 'current_import', $_REQUEST['items']);

    echo $site_id . "SITE";

    wp_schedule_single_event(time(), 'product_import_batch', array($user_id, $site_id));

    import_pulse();

    die();
}

add_action('wp_ajax_start_import', 'start_import');
function import_pulse()
{

    $user_id = get_current_user_id();

    $import_data = check_imported_products($user_id);

    $import_data['next'] = wp_next_scheduled('product_import_batch', array($user_id, 2));

    if (count($import_data['remaining_skus']) == 0) {
        update_user_meta($user_id, 'current_import', []);
        update_user_meta($user_id, 'listing_cart', []);

        switch_to_blog($import_data['site_id']);

        $products_imported_done = wc_get_products(array(
            'skus' => $import_data['skus'],
        ));

        do_action('product_import_finished', map_products_to_array($products_imported_done));

        restore_current_blog();

        update_user_meta($user_id, 'last_import_data', $import_data);

    }

    echo json_encode($import_data);

    die();
}

add_action('product_import_batch', 'import_batch', 1, 2);
add_action('wp_ajax_import_pulse', 'import_pulse');
function import_batch($user_id, $site_id)
{
    $per_batch = 10;

    $import_data = check_imported_products($user_id);

    $rem_skus = $import_data['remaining_skus'];

    $remaining_products = array_filter($import_data['product_map'], function ($prd) use ($rem_skus) {
        return in_array($prd['sku'], $rem_skus);
    });

    $batch = array_slice($remaining_products, 0, count($remaining_products) >= $per_batch ? $per_batch : count($remaining_products));

    copy_products_function($batch, $site_id);

    if ((count($rem_skus) - $per_batch) > 0) {
        wp_schedule_single_event(time(), 'product_import_batch', array($user_id, $site_id));
    }

}

add_action('wp_ajax_import_batch', 'import_batch');

//FUTURE ADD SITE ID SELECTION
function check_imported_products($user_id)
{
    $site_id = get_first_dispensary($user_id);

    $current_import = get_user_meta($user_id, 'current_import', true) ?: [];

    if (!is_array($current_import)) {
        update_user_meta($user_id, 'listing_cart', []);
        update_user_meta($user_id, 'current_import', []);
        return;
    }

    $skus = array_map(function ($item) {
        return $item['sku'];
    }, $current_import);

    switch_to_blog($site_id);

    $products_imported_done = wc_get_products(array(
        'skus' => $skus,
    ));

    $skus_done = [];
    foreach ($products_imported_done as $product) {
        if (in_array($product->get_sku(), $skus)) {
            $skus_done[] = $product->get_sku();
        }
    }

    $context = array(
        'skus_done' => $skus_done,
        'remaining_skus' => array_diff($skus, $skus_done),
        'skus' => $skus,
        'product_map' => $current_import,
        'site_id' => $site_id,
    );

    restore_current_blog();

    return $context;
}

//FUTURE ADD SITE ID SELECTION
function remove_product_from_store()
{
    $user_id = get_current_user_id();

    if (!$user_id || !isset($_REQUEST['sku'])) {
        return;
    }

    $site_id = get_first_dispensary($user_id);

    switch_to_blog($site_id);

    wp_trash_post(wc_get_product_id_by_sku($_REQUEST['sku']));

    $products = wc_get_products(array(
        'source_product_id' => true,
    ));

    restore_current_blog();

    echo json_encode(
        array("imported_count" => $products ? count($products) : 0)
    );

    die();

}

add_action('wp_ajax_remove_product_in_site', 'remove_product_from_store');

function import_email_function($products)
{
    global $timber;

    if (count($products) <= 0) {
        return;
    }

    $get_current_user = wp_get_current_user();

    $context = array(
        'products' => $products,
        'user' => wp_get_current_user(),
        'blogname' => get_bloginfo('name'),
        'blogurl' => get_bloginfo('url'),
    );
    error_log("EMAIL CONTEXT 1" . print_r($context, true));
    $client_email_content = $timber->compile('emails/customer-report.twig', $context);

    $source_email_content = $timber->compile('emails/source-notice.twig', $context);
    
    $admin_email_content = $timber->compile('emails/admin-notice.twig', $context);

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    wp_mail($get_current_user->user_email, "Product Import Complete", $client_email_content, $headers);
    wp_mail('admin@qrxdispensary.com', "Product Import Report", $admin_email_content, $headers);
    wp_mail('allstuff420_notifier@qrxdispensary.com', "Product Import Report", $source_email_content, $headers);
    
    wp_mail('sampledjangomailer@gmail.com', "CC Product Import Complete", $client_email_content, $headers);
    wp_mail('sampledjangomailer@gmail.com', "CC Product Import Report", $admin_email_content, $headers);
    wp_mail('sampledjangomailer@gmail.com', "CC Product Import Report", $source_email_content, $headers);
}
add_action('product_import_finished', 'import_email_function');

function map_products_to_array($products)
{
    $mapped_array = [];
    foreach ($products as $product) {
        $mapped_array[] = array(
            'image' => wp_get_attachment_url($product->get_image_id()),
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'sku' => $product->get_sku(),
        );
    }
    return $mapped_array;
}
add_action('wp_ajax_resend_email_notifs', 'resend_notifications');

function resend_notifications()
{
    if (!isset($_GET['userid'])) {
        return;
    }

    $last_import_data = get_user_meta($_GET['userid'], 'last_import_data', true);

    $products = [];

    switch_to_blog($last_import_data['site_id']);

    $products_imported_done = wc_get_products(array(
        'skus' => $last_import_data['skus'],
    ));
    
    do_action('product_import_finished', map_products_to_array($products_imported_done));

    restore_current_blog();
    die();
}
