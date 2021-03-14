<?php

function product_import_shortcode_scripts()
{
    wp_register_script('sweetalert', '//cdn.jsdelivr.net/npm/sweetalert2@10', array('jquery'), 3.3);
    wp_register_script('product_import_utils', plugins_url('../assets/js/utils.js', __FILE__), array('jquery', 'sweetalert'), '2.5.1');
    wp_register_script('lightbox_js_qrx', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', array('jquery'), '2.5.1');
    wp_register_style('lightbox_css_qrx', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.css', [], '1.0.0', 'all');
    wp_register_script('product_import_js', plugins_url('../assets/js/script.js', __FILE__), array('jquery', 'sweetalert', 'product_import_utils', 'lightbox_js_qrx'), '2.5.1');
    wp_register_script('product_import_done_js', plugins_url('../assets/js/product.js', __FILE__), array('jquery', 'sweetalert', 'product_import_utils'), '2.5.1');
    wp_register_script('product_import_cart_js', plugins_url('../assets/js/cart.js', __FILE__), array('jquery', 'sweetalert', 'product_import_utils'), '2.5.1');
    wp_register_style('product_import_css', plugins_url('../assets/css/style.css', __FILE__), [], '1.0.0', 'all');
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

    if(!get_user_meta(get_current_user_id(), 'site_created', true)) {
        echo $timber->compile('no-site.twig', $context);
        return;
    }

    $listing_cart = get_user_meta(get_current_user_id(), 'listing_cart', true) ?: [];

    $max_product;
    $memberships = wc_memberships_get_user_memberships(get_current_user_id());
    $membership_plan = trim(preg_replace('/\s+/', ' ', $memberships[0]->plan->name));
    $testing = $membership_plan;
    $value_output;
    
    if ($testing == "QRX Dispensary Basic Plan") {
        $max_product = 20;
    } elseif ($testing == 'QRX Dispensary Pro Plan') {
        $max_product = 50;
    } elseif ($testing == 'QRX Dispensary Premium Plan') {
        $max_product = 100;
    } else {
        $max_product = 0;
    }
    

    $max_product = apply_filters('max_products_to_import', $max_product);

    $context = array(
        'sites' => $sites,
        'gird_url' => explode('?', home_url($_SERVER["REQUEST_URI"]))[0],
        'cart_url' => home_url($_SERVER["REQUEST_URI"]) . "?view=cart",
        'cart_count' => count($listing_cart) ?: 0,
        'imported_products_count' => count($imported_products) ?: 0,
        'imported_products' => $imported_products,
        'max_products' => $max_product,
        'view' => $_REQUEST['view'] ?: 'home',
        'membership' => $membership_plan, //Added
        'testing' => $testing, //Added
        'value_output' => $value_output, //Added
    );

    try {
        $listing_cart = array_values($listing_cart);
    } catch (\Throwable $th) {
        $listing_cart = [];
    }

    $js_objects = array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'default_site' => $sites[0]['url'],
        'default_api_key' => $sites[0]['api_key'],
        'imported_products' => $imported_products,
        'max_products' => $max_product,
        'listing_cart' => $listing_cart ?: [],
    );

    wp_localize_script('product_import_utils', 'wp_ajax', $js_objects);

    switch ($_REQUEST['view']) {
        case 'imported':
            wp_localize_script('product_import_done_js', 'wp_ajax', $js_objects);
            wp_enqueue_script('product_import_done_js');
            echo $timber->compile('imported-products.twig', $context);
            break;
        case 'cart':
            $batch = check_imported_products(get_current_user_id());
            $js_objects["import_status"] = count($batch["remaining_skus"]);
            wp_localize_script('product_import_cart_js', 'wp_ajax', $js_objects);
            wp_enqueue_script('product_import_cart_js');
            echo $timber->compile('import-cart.twig', $context);
            break;
        default:
            wp_localize_script('product_import_js', 'wp_ajax', $js_objects);
            wp_enqueue_script('product_import_js');
            wp_enqueue_style('lightbox_css_qrx');
            echo $timber->compile('product-import.twig', $context);
            break;
    }
}
add_shortcode('product_import_views', 'product_import_display');
