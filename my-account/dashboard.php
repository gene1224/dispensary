<?php
/**
 * Dispensary Dashboard Shortcode
 */
class DispensaryDashboard
{
    /**
     * WP_User ID
     *
     * @var [int]
     */
    private $user_id;

    private $site_id;

    private $nonce_key = 'qrx_dispensary_dashboard';

    private $context = array();

    private $js_context = array();

    private $max_product = 10;

    private $membership_plan_name = '';

    private $listing_cart = [];

    private $imported_products = [];

    private $ordered_total_sales = 0;

    private $website_visitors_total = 0;

    public function __construct()
    {
        add_shortcode('dashboard_views', [$this, 'views']);
    }

    /**
     * INIT initialize context and load the shortcode data.
     *
     * @return void
     */
    private function init()
    {
        $this->user_id = get_user_meta(get_current_user_id(), 'created_by_user_id', true) ?: get_current_user_id();

        $this->site_id = get_user_site_id($this->user_id);

        $this->listing_cart = get_user_meta(get_current_user_id(), 'listing_cart', true) ?: [];

        $this->imported_products = get_users_imported_products();

        $this->ordered_total_sales = get_users_total_sales();

        $this->website_visitors_total = calculate_visitor_total($this->site_id);

        $this->context = array(
            'website_visitors_total' => $this->website_visitors_total,
            'imported_products' => $this->imported_products,
            'ordered_products' => get_dispensary_orders($this->site_id),
            'ordered_total_sales' => $this->ordered_total_sales,
            'site_product' => get_source_sites(),
            'gird_url' => explode('?', home_url($_SERVER["REQUEST_URI"]))[0],
            'cart_url' => home_url($_SERVER["REQUEST_URI"]) . "?view=cart",
            'max_products' => $this->max_product,
            'membership' => $this->membership_plan_name,
        );

        try {
            $this->$listing_cart = array_values($this->$listing_cart);
        } catch (\Throwable $th) {
            $this->$listing_cart = [];
        }

        $this->js_context = array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax-nonce'),
            'website_visitors_total' => $this->website_visitors_total,
            'site_product' => get_source_sites(),
            'imported_products' => $this->imported_products,
            'ordered_products' => get_dispensary_orders($this->site_id),
            'ordered_total_sales' => $this->ordered_total_sales,
            'default_site' => get_source_sites()[0]['url'],
            'default_api_key' => get_source_sites()[0]['api_key'],
            'max_products' => $this->max_product,
            'last_weeks_orders' => get_recent_orders($this->user_id),
            'listing_cart' => $this->$listing_cart ?: [],
        );

    }

    /**
     * Assets loader
     *
     * @return void
     */
    public function load_assets()
    {
        wp_enqueue_script('sweetalert');
        wp_enqueue_script('jquery_ui_js');
        wp_enqueue_script('dashboard_js');
        wp_enqueue_style('jquery_ui_css');
        wp_enqueue_style('dashboard_css');
        wp_enqueue_style('product_import_css');
    }

    /**
     * Shortcode Display Function
     *
     * @return void
     */
    public function views()
    {
        global $timber;

        $this->init(); // POPULATES THE CONTEXTS ALWAYS ON TOP

        foreach (wc_memberships_get_user_memberships($this->user_id) as $membership) {
            $product_limit = get_post_meta($membership->plan_id, 'dispensary_product_limit', true) ?: 0;
            if ($max_product < $product_limit) {
                $this->max_product = $product_limit;
                $this->membership_plan_name = $membership->plan->name;
            }
        }

        $this->load_assets();

        $this->js_context['visitor_data'] = get_visitor_counts($this->site_id);

        $this->js_context['pageview_data'] = get_page_view_count($this->site_id);

        wp_localize_script('dashboard_js', 'wp_ajax', $this->js_context);

        echo $timber->compile('dashboard.twig', $this->context);
    }

}
$dispensaryDashboard = new DispensaryDashboard();
