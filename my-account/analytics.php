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

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts()
    {
        wp_register_script('graph_js', plugins_url('../assets/js/graphs.js', __FILE__), array('jquery', 'chart_js', 'sweetalert'), '2.5.1');
    }

    public function website_analytics_display()
    {
        global $timber;

        global $wpdb;

        $this->user_id = get_dispensary_user_id();

        $this->site_id = get_user_site_id($this->user_id);

        $this->imported_products = get_users_imported_products();

        $this->membership_plan_name = get_user_plan_name($this->user_id);

        echo $timber->compile('website-analytics/index.twig', $context);

    }

}

$webAnalytics = new WebAnalytics();
