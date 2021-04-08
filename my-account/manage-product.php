<?php

class ManageProduct
{

    private $user_id;

    private $site_id;

    private $nonce_key = 'qrx_dispensary_import';

    private $max_products = 0;

    private $listing_cart = [];

    private $imported_products;

    private $js_context = [];

    private $context = [];

    public function __construct()
    {
        add_shortcode('product_import_views', [$this, 'views']);
    }

    private function init()
    {
        $this->user_id = get_user_meta(get_current_user_id(), 'created_by_user_id', true) ?: get_current_user_id();

        $this->max_products = get_user_max_products($this->user_id);

        $this->imported_products = get_users_imported_products();

        $this->listing_cart = get_user_meta(get_current_user_id(), 'listing_cart', true) ?: [];

        $this->context = array(
            'sites' => get_source_sites(),
            'gird_url' => explode('?', home_url($_SERVER["REQUEST_URI"]))[0],
            'cart_url' => home_url($_SERVER["REQUEST_URI"]) . "?view=cart",
            'imported_products' => $this->imported_products,
            'max_products' => $this->max_products,
            'view' => $_REQUEST['view'] ?: 'home',
            'membership' => get_user_plan_name($this->user_id),
        );

        try {
            $this->listing_cart = array_values($listing_cart);
        } catch (\Throwable $th) {
            $this->listing_cart = [];
        }

        $this->js_context = array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax-nonce'),
            'default_site' => get_source_sites()[0]['url'],
            'default_api_key' => get_source_sites()[0]['api_key'],
            'imported_products' => $this->imported_products,
            'max_products' => $this->max_products,
            'listing_cart' => $this->listing_cart ?: [],
        );

        $this->init_assets();
    }

    private function init_assets()
    {
        wp_enqueue_script('sweetalert');
        wp_enqueue_style('product_import_css');
    }

    public function views()
    {
        global $timber;

        $this->init();

        if (!get_user_meta($this->user_id, 'site_created', true) && !get_user_meta($this->user_id, 'created_on_my_account', true)) {
            echo $timber->compile('no-site.twig', $this->context);
            return;
        }

        wp_localize_script('product_import_utils', 'wp_ajax', $this->js_context);

        switch ($_REQUEST['view']) {
            case 'imported':
                $this->context['page_heading'] = 'Enlisted Products';
                wp_localize_script('product_import_done_js', 'wp_ajax', $this->js_context);
                wp_enqueue_script('product_import_done_js');
                echo $timber->compile('imported-products.twig', $this->context);
                break;
            case 'cart':
                $this->context['page_heading'] = 'Listed Products';
                $batch = check_imported_products(get_current_user_id());
                $js_objects["import_status"] = count($batch["remaining_skus"]);
                wp_localize_script('product_import_cart_js', 'wp_ajax', $this->js_context);
                wp_enqueue_script('product_import_cart_js');
                echo $timber->compile('import-cart.twig', $this->context);
                break;
            default:
                $this->context['page_heading'] = 'Manage Products';
                wp_localize_script('product_import_js', 'wp_ajax', $this->js_context);
                wp_enqueue_script('product_import_js');
                wp_enqueue_style('lightbox_css_qrx');
                echo $timber->compile('product-import.twig', $this->context);
                break;
        }
    }
}

$manageProduct = new ManageProduct();
