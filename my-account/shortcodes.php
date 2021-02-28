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

    $imported_products = [];

    foreach (get_blogs_of_user(get_current_user_id(), true) as $users_site) {

        switch_to_blog($users_site->userblog_id);

        $products = wc_get_products(array(
            'source_product_id' => true,

        ));

        foreach ($products as $product) {
            $imported_products[] = array(
                'source_product_id' => get_post_meta($product->id, 'source_product_id', true),
                'source_site_id' => get_post_meta($product->id, 'source_site_id', true),
                'source_site_url' => get_post_meta($product->id, 'source_site_url', true),
            );
        }

        restore_current_blog();
    }

    wp_enqueue_script('sweetalert');
    wp_enqueue_style('product_import_css');

    $context = array(
        'sites' => $sites,
    );
    $js_objects = array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'default_site' => $sites[0]['url'],
        'default_api_key' => $sites[0]['api_key'],
        'imported_products' => $imported_products,
        'listing_cart' => get_user_meta(get_current_user_id(), 'listing_cart', true),
    );
    
    if ($_REQUEST['view'] == 'cart') {
        wp_localize_script('product_import_cart_js', 'wp_ajax', $js_objects);
        wp_enqueue_script('product_import_cart_js');
        echo $timber->compile('import-cart.twig', $context);
    } else {
        wp_localize_script('product_import_js', 'wp_ajax', $js_objects);
        wp_enqueue_script('product_import_js');
        echo $timber->compile('product-import.twig', $context);
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
