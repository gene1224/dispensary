<?php

class WebAnalytics
{
    private $site_id;

    private $user_id;

    private $nonce_key = 'qrx_dispensary_dashboard';

    private $imported_products = [];

    private $membership_plan_name = '';

    public function __construct()
    {
        add_shortcode('website_analytics_views', [$this, 'website_analytics_display']);
        add_action('wp_ajax_get_custom_date', [$this, 'website_analytics_display']);
        add_action('wp_ajax_get_notify', [$this, 'website_analytics_display']);

    }

    private function init()
    {
        $this->user_id = get_dispensary_user_id();

        $this->site_id = get_user_site_id($this->user_id);

        $this->imported_products = get_users_imported_products();

        $this->membership_plan_name = get_user_plan_name($this->user_id);
    }

    public function website_analytics_display()
    {
        global $timber;

        global $wpdb;

        $ordered_products = []; //get_users_ordered_products();

        $ordered_total_sales = get_users_total_sales();

        $memberships = wc_memberships_get_user_memberships($parent_id == 0 ? get_current_user_id() : $parent_id);

        $site_url = '';

        $sites = get_blogs_of_user(get_current_user_id(), true);

        $notifySent = "";

        $context = array(
            'imported_products' => $this->imported_products,
            'ordered_products' => $ordered_products,
            'notifySent' => $notifySent,
            'membership' => $this->membership_plan_name,
            'ordered_total_sales' => $ordered_total_sales,
        );

        $js_objects = array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax-nonce'),

            'imported_products' => $this->imported_products,
            'ordered_products' => $ordered_products,
            'notifySent' => $notifySent,
            'membership' => $membership_plan_name,
            'ordered_total_sales' => $ordered_total_sales,
        );

        wp_enqueue_script('website_analytics_js');
        wp_enqueue_script('product_report_js');
        wp_enqueue_script('jquery_ui_js');
        wp_enqueue_script('chart_js');
        wp_enqueue_style('website_analytics_css');
        wp_enqueue_style('jquery_ui_css');

        wp_localize_script('product_report_js', 'wp_ajax', $js_objects);

        echo $timber->compile('website-analytics/website-analytics.twig', $context);

    }

}

$webAnalytics = new WebAnalytics();
