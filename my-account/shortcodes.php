<?php

function product_import_shortcode_scripts()
{

    wp_register_script('sweetalert', '//cdn.jsdelivr.net/npm/sweetalert2@10', array('jquery'), 3.3);
    wp_register_script('product_import_js', plugins_url('../assets/js/script.js', __FILE__), array('jquery', 'sweetalert'), '2.5.1');
    wp_register_script('product_import_cart_js', plugins_url('../assets/js/cart.js', __FILE__), array('jquery', 'sweetalert'), '2.5.1');
    wp_enqueue_style('product_import_css', plugins_url('../assets/css/style.css', __FILE__), [], '1.0.0', 'all');

}

add_action('wp_enqueue_scripts', 'product_import_shortcode_scripts');

function product_import_display()
{
    global $timber;

    $sites = array(
        array(
            'url' => 'https://allstuff420.com',
            'api_key' => base64_encode('ck_2eff2c6b9cc435818aad646e1c7676d65af7f168:cs_2fd13443cf704e5c1ca201cbe786043505b8baaa'),
        ),
    );

    $imported_products = get_users_imported_products();

    wp_enqueue_script('sweetalert');
    wp_enqueue_style('product_import_css');

    $listing_cart = get_user_meta(get_current_user_id(), 'listing_cart', true) ?: [];

    $max_product = 6;

    $context = array(
        'sites' => $sites,
        'gird_url' => explode('?', home_url($_SERVER["REQUEST_URI"]))[0],
        'cart_url' => home_url($_SERVER["REQUEST_URI"]) . "?view=cart",
        'cart_count' => count($listing_cart) ?: 0,
        'imported_products_count' => count($imported_products) ?: 0,
        'imported_products' => $imported_products,
        'max_products' => $max_product,
        'view' => $_REQUEST['view'] ?: 'home'
    );

    $js_objects = array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'default_site' => $sites[0]['url'],
        'default_api_key' => $sites[0]['api_key'],
        'imported_products' => $imported_products,
        'max_products' => $max_product,
        'listing_cart' => $listing_cart ?: [],
    );

    switch ($_REQUEST['view']) {
        case 'imported':
            wp_localize_script('product_import_cart_js', 'wp_ajax', $js_objects);
            // wp_enqueue_script('product_import_cart_js');
            echo $timber->compile('imported-products.twig', $context);
            break;
        case 'cart':
            wp_localize_script('product_import_cart_js', 'wp_ajax', $js_objects);
            wp_enqueue_script('product_import_cart_js');
            echo $timber->compile('import-cart.twig', $context);
            break;
        default:
            wp_localize_script('product_import_js', 'wp_ajax', $js_objects);
            wp_enqueue_script('product_import_js');
            echo $timber->compile('product-import.twig', $context);
            break;
    }
}
add_shortcode('product_import_views', 'product_import_display');

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
add_action('wp_ajax_nopriv_add_to_cart_list', 'add_to_cart_list');

function empty_cart_list()
{
    $user_id = get_current_user_id();

    update_user_meta($user_id, 'listing_cart', array());

    echo json_encode(get_user_meta($user_id, 'listing_cart', true));

    die();
};

add_action('wp_ajax_empty_cart_list', 'empty_cart_list');
add_action('wp_ajax_nopriv_empty_cart_list', 'empty_cart_list');

function enlist_products()
{
    if (!isset($_REQUEST['products'])) {
        die();
    }

    $user_id = get_current_user_id();

    $enlisted_products = get_user_meta($user_id, 'product_enlisted', true) ?: [];

    echo json_encode(get_user_meta($user_id, 'listing_cart', true));

    die();
};

add_action('wp_ajax_enlist_products', 'enlist_products');
add_action('wp_ajax_nopriv_enlist_products', 'enlist_products');

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
add_action('wp_ajax_nopriv_remove_product_in_cart', 'remove_product_in_cart');

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
add_action('wp_ajax_nopriv_start_import', 'start_import');

function import_pulse()
{

    $user_id = get_current_user_id();

    $import_data = check_imported_products($user_id);

    $import_data['next'] = wp_next_scheduled('product_import_batch', array($user_id, 2));

    if (count($import_data['remaining_skus']) == 0) {
        update_user_meta($user_id, 'current_import', []);
        update_user_meta($user_id, 'listing_cart', []);
    }

    echo json_encode($import_data);

    die();
}

add_action('product_import_batch', 'import_batch', 1, 2);
add_action('wp_ajax_import_pulse', 'import_pulse');
add_action('wp_ajax_nopriv_import_pulse', 'import_pulse');

function import_batch($user_id, $site_id)
{
    $per_batch = 2;

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
add_action('wp_ajax_nopriv_import_batch', 'import_batch');

function check_imported_products($user_id)
{
    $site_id = 0;
    foreach (get_blogs_of_user($user_id, true) as $blog) {
        if ($blog->userblog_id != 1) {
            $site_id = $blog->userblog_id;
            break;
        }
    }

    $current_import = get_user_meta($user_id, 'current_import', true) ?: [];

    if (!is_array($current_import)) {
        update_user_meta($user_id, 'listing_cart', []);
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
    );

    restore_current_blog();

    return $context;
}
