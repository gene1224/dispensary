<?php

function product_import_shortcode_scripts()
{
    wp_register_script('sweetalert', '//cdn.jsdelivr.net/npm/sweetalert2@10', array('jquery'), 3.3);
    wp_register_script('product_import_utils', plugins_url('../assets/js/utils.js', __FILE__), array('jquery', 'sweetalert'), '2.5.1');
    wp_register_script('website_analytics_js', plugins_url('../assets/js/website-analytics.js', __FILE__), array('jquery'), '1.0.1');
    wp_register_script('product_report_js', plugins_url('../assets/js/product-report.js', __FILE__), array('jquery'), '1.0.1');
    wp_register_script('jquery_ui_js', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), '1.0.1');
    wp_register_style('website_analytics_css', plugins_url('../assets/css/website-analytics.css', __FILE__), [], '1.0.1', 'all');
    wp_register_style('jquery_ui_css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', [], '1.0.1', 'all');
    wp_register_script('chart_js', 'https://www.jsdelivr.com/package/npm/chart.js?path=dist', array('jquery'), '1.0.1');

}
add_action('wp_enqueue_scripts', 'product_import_shortcode_scripts');

