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
        wp_register_script('graph_js', plugins_url('../assets/js/analytics.js', __FILE__), array('jquery', 'chart_js', 'sweetalert'), '2.5.1');
        wp_register_style('analytics_styles', plugins_url('../assets/css/analytics.css', __FILE__), [], '1.0.1', 'all');
    }

    public function website_analytics_display()
    {
        global $timber;

        global $wpdb;

        $this->user_id = get_dispensary_user_id();

        $this->site_id = get_user_site_id($this->user_id);

        $this->imported_products = get_users_imported_products();

        $this->membership_plan_name = get_user_plan_name($this->user_id);
        
        $date_args = array(
            'start'=>date('Y-m-d', strtotime('-7 days')),
            'end'=>date('Y-m-d', strtotime('today'))
        );
        
        $context = array(
            'url' => admin_url('admin-ajax.php') . "?action=fetch_data",
            'visit_data' => get_visitor_data('daily', $date_args),
            'page_data' => get_page_data('daily', $date_args),
        );
    
        wp_enqueue_style('analytics_styles');
        
        wp_localize_script('graph_js', 'wp_ajax', $context);

        wp_enqueue_script('graph_js');

        

        echo $timber->compile('website-analytics/index.twig', $context);

    }

}

$webAnalytics = new WebAnalytics();
