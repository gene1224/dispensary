<?php
require_once 'qrx-template-7.php';

function qrx_custom_styles()
{
    wp_enqueue_style('qrx-custom-template', get_site_url() . '/wp-admin/admin-ajax.php?action=custom_template_css', false, '1.1', 'all');
}
add_action('wp_enqueue_scripts', 'qrx_custom_styles');
