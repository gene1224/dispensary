<?php
/**
 * @package QRXDispensary Payments
 */
/*
Plugin Name: QRXDispensary Payments
Plugin URI: https://qrxdispensary.com/
Description: Payment process of QRXDispensary
Version: 1.0.0
Author: Gene Sescon
Author URI: https://example.com/
License: GPLv2 or later
Text Domain: QRXDispensary
 */

require_once 'vendor/autoload.php';

require 'order.php';

require 'product.php';

require 'multisite.php';

require 'admin/index.php';

function copy_products_function()
{
    $array_of_product = array(
        array(
            'product_id' => '',
            'listing_price' => '',
        ),
    );
    die();
}
add_action('wp_ajax_copy_products', 'copy_products_function');
