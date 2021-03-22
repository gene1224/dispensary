<?php
/**
 * DispesnaryAdminLoadAssets Custom Post Type
 */
class DispesnaryAdminLoadAssets
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'load_js']);
        add_action('admin_enqueue_scripts', [$this, 'load_css']);
    }

    public function load_css()
    {
        wp_register_style('qrx-admin-base-style', plugins_url('../assets/admin/css/style.css', __FILE__), [], '1.0.0', 'all');
        wp_enqueue_style('qrx-admin-base-style');
    }

    public function load_js()
    {
        wp_register_script('qrx-template-script', plugins_url('../assets/admin/js/script-admin-templates.js.js', __FILE__), array('jquery'), '2.5.1');
        wp_enqueue_style('qrx-template-script');
    }
}
$adminLoadAssets = new DispesnaryAdminLoadAssets();
