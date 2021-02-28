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
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

require 'order.php';
require 'product.php';
require 'multisite.php';
require 'admin/index.php';
require 'my-account/index.php';
require 'utils.php';

use Automattic\WooCommerce\Client;

define('DISPENSARY_TEMPLATES', WP_PLUGIN_DIR . '/dispensary-template-customizer');
define('DISPENSARY_PAYMENTS', WP_PLUGIN_DIR . '/dispensary-payments');
if (!isset($timber)) {
    $timber = new \Timber\Timber();
    if (is_array($timber::$locations)) {
        $views = $timber::$locations;
    } else {
        $views = array($timber::$locations);
    }
    $views[] = DISPENSARY_PAYMENTS . "/templates";
    $views[] = DISPENSARY_TEMPLATES . "/templates";

    $timber::$locations = $views;
}

function copy_products_function()
{
    $starttime = microtime(true);
    if (!isset($_REQUEST['imported_products'])) {
        die();
    }

    $site_id = 1; //TODO SITE SWITCH

    $imported_products = $_REQUEST['imported_products'];

    // $site = 'http://client.wpms.net';
    // $client_key = 'ck_7dd30741273abf3998abd3b94db24c08e45dddc0';
    // $client_secret = 'cs_681b536a45fdd69ca211d595842fd2716fba8112';

    $site = 'http://allstuff420.com';
    $client_key = 'ck_2eff2c6b9cc435818aad646e1c7676d65af7f168';
    $client_secret = 'cs_2fd13443cf704e5c1ca201cbe786043505b8baaa';

    $wc = new Client($site, $client_key, $client_secret, ['version' => 'wc/v3']);

    $product_ids_to_import = [];

    foreach ($imported_products as $product) {
        $product_ids_to_import[] = $product['product_id'];
    }

    $products = $wc->get('products', array('include' => $product_ids_to_import));

    save_products($products, $imported_products, $site);

    echo microseconds_to_seconds(microtime(true) - $starttime) . "s";

    die();

}

add_action('wp_ajax_copy_products', 'copy_products_function');
add_action('wp_ajax_nopriv_copy_products', 'copy_products_function');

function save_products($imported_products, $price_map, $site_url = '')
{
    foreach ($imported_products as $imported_product) {
        $product_price = -1;

        foreach ($price_map as $price) {
            if ($price['product_id'] == $imported_product->id) {
                $product_price = $price['listing_price'];
            }
        }

        if (wc_get_product_id_by_sku($imported_product->sku) || $product_price == -1) {
            continue;
        }

        $new_product = new WC_Product();
        $new_product->set_name($imported_product->name);
        $new_product->set_slug($imported_product->slug);
        $new_product->set_status($imported_product->status);
        // $new_product->set_type($imported_product->type);
        $new_product->set_sku($imported_product->sku);
        $new_product->set_price($product_price);
        $new_product->set_regular_price($product_price);
        $new_product->set_stock_status($imported_product->stock_status);
        $new_product->set_stock_quantity($imported_product->stock_quantity);

        $new_product_id = $new_product->save();

        $category_ids = array_map(function ($category) {
            $exist = term_exists($category->name, 'product_cat');
            if ($exist) {
                return $exist['term_id'];
            }
            return wp_insert_term(
                $category->name,
                'product_cat',
                array('slug' => $category->slug)
            );
        }, $imported_product->categories);

        $new_product->set_category_ids($category_ids);

        $tags = array_map(function ($tag) {
            return $tag->name;
        }, $imported_product->tags);

        wp_set_object_terms($new_product_id, $tags, 'product_tag');

        $image_ids = [];

        foreach ($imported_product->images as $imported_product_image) {
            $image_ids[] = media_sideload_image($imported_product_image->src, $new_product_id, null, 'id');
        }

        if (count($image_ids)) {
            update_post_meta($new_product_id, '_thumbnail_id', $image_ids[0]);
        }

        $new_product->set_gallery_image_ids($image_ids);

        add_product_attributes($new_product, $imported_product->attributes);

        $new_product->save();

        set_dispensary_connection_metas($imported_product, $new_product_id, 0, $site_url);

    }
}

function set_dispensary_connection_metas($original_product_data, $product_id, $site_id = 0, $site_url = '')
{
    if ($site_id != 0) {
        add_post_meta($product_id, 'source_site_id', $site_id);
    } elseif ($site_url != '') {
        add_post_meta($product_id, 'source_site_url', $site_url);
    } else {
        return false;
    }

    add_post_meta($product_id, 'source_product_id', $original_product_data->id);

    add_post_meta($product_id, 'original_price', $original_product_data->price);
}
