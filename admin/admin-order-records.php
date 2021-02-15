<?php
function qrx_dispensary_orders_type()
{
    $supports = array(
        'custom-fields',
    );
    $labels = array(
        'name' => _x('Dispensary Orders', 'plural'),
        'singular_name' => _x('Dispensary Order', 'singular'),
        'menu_name' => _x('Dispensary Orders', 'admin menu'),
        'name_admin_bar' => _x('Dispensary Orders', 'admin bar'),
        'add_new' => _x('Add New', 'add new'),
        'add_new_item' => __('Add New Order'),
        'new_item' => __('New Order'),
        'edit_item' => __('Edit Order'),
        'view_item' => __('View Order'),
        'all_items' => __('All Order'),
        'search_items' => __('Search Order'),
        'not_found' => __('No Order found.'),
    );
    $args = array(
        'supports' => $supports,
        'labels' => $labels,
        'public' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'custom-order'),
        'has_archive' => true,
        'hierarchical' => false,
    );

    register_post_type('dispensary_orders', $args);
}

add_action('init', 'qrx_dispensary_orders_type');

function qrx_add_custom_order_meta_box()
{
    add_meta_box(
        "product-external-data-box",
        "Order Details",
        "qrx_custom_order_meta_box_HTML",
        "dispensary_orders",
        "normal",
        "high",
        null
    );
}

add_action("add_meta_boxes", "qrx_add_custom_order_meta_box");

function qrx_custom_order_meta_box_HTML()
{
    $post_id = get_the_ID();

    $order_meta = get_post_meta($post_id);
    echo "<pre>";

    $items = get_post_meta($post_id, 'order_items', true);

    $subtotal = get_post_meta($post_id, 'subtotal', true);

    $shipping_total = get_post_meta($post_id, 'shipping_total', true);

    $total_tax = get_post_meta($post_id, 'total_tax', true);

    $order_id = get_post_meta($post_id, 'order_id', true);

    $sales_siteurl = get_post_meta($post_id, 'sales_siteurl', true);
    

    $total = get_post_meta($post_id, 'total', true);

    $date_created = get_post_meta($post_id, 'date_created', true);

    $date_paid = get_post_meta($post_id, 'date_paid', true);

    $sales_siteurl = get_post_meta($post_id, 'sales_siteurl', true);
    
    echo "</pre>";
    ?>
        <div class="order-details">
            <div class="order-info">
                <p>Order created from <a href="<?= $sales_siteurl ?>"><?= $sales_siteurl ?></a> with Order ID: <?= $order_id ?></p>
                <p>Date Paid: <?= date_format($date_paid, 'F, d Y @ h:i:sA') ?> </p>
                <p>Date Created: <?= date_format($date_created, 'F, d Y @ h:i:sA') ?> </p>
                <p><a target="_blank" href="<?= $sales_siteurl."/wp-admin/post.php?post=".$order_id."&action=edit" ?>">View Actual Order</a></p>
                

            </div>
            <p><strong>Items</strong></p>
            <table class="order-items table"  cellspacing="0">
            <thead>
                <tr>
                    <td>Image</td>
                    <td>Item Name</td>
                    <td>Total Line Sales</td>
                    <td>Sales to Source Dispensary</td>
                    <td>Sales to Seller Dispensary</td>
                    <td>10% Fee</td>
                    <td>Net Sales <br>to Seller</td>
                    <td>Sales to Source <br>Dispensary</td>
                    <td>Subtotal</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item) {
                    $total_line_sales = $item['price'] * $item['quantity'];
                    $total_original_price_sales = $item['original_price'] * $item['quantity'];
                    $sales_to_seller = $total_line_sales - $total_original_price_sales;
                    $listing_fee = $sales_to_seller * 0.10;
                    $net_sales_to_seller = $sales_to_seller - $listing_fee;
                 ?>
       
                      <tr class="item">
                        <td class="item-image">
                           <img src=<?=$item['image_url']?> width="50px;">
                        </td>
                        <td class="item-name">
                            <?=$item['name']?>
                        </td>
                        <td class="item-seller-sales">
                            $<?=number_format($item['price'], 2, '.', ',')?> x <?=$item['quantity']?>pcs = $<?=$total_line_sales?>
                        </td>
                        <td class="item-original-sales">
                            Sales to <?=$item['source_site_url']?><br>
                            $<?=number_format($item['original_price'], 2, '.', ',')?> x <?=$item['quantity']?> =
                            $<?=$total_original_price_sales?>
                        </td>
                        <td class="item-sales">
                            Sales to <?=$sales_siteurl?><br>
                            $<?=number_format($total_line_sales, 2, '.', ',')?> - $<?=number_format($total_original_price_sales, 2, '.', ',')?>
                             = $<?=number_format($sales_to_seller, 2, '.', ',')?>
                        </td>
                        <td class="item-fee">

                            $<?=number_format($listing_fee, 2, '.', ',')?>
                        </td>
                        <td class="items-transfer-to-seller">
                          $<?=number_format($net_sales_to_seller, 2, '.', ',')?>
                        </td>
                        <td class="items-transfer-to-source">
                          $<?=number_format($total_original_price_sales, 2, '.', ',')?>
                        </td>
                        <td class="sub-total">
                        $<?=number_format($total_line_sales, 2, '.', ',')?>
                        </td>

                    </tr>
                <?php }?>
                
                </tbody>
            </table>
            <p><strong>Totals</strong></p>
            <table class="totals" cellspacing="0">
                <tr>
                    <td>Sub Total</td>
                    <td>$<?= number_format($subtotal, 2, '.', ',') ?></td>
                </tr>
                <tr>
                    <td>Shipping Total</td>
                    <td>$<?= number_format($shipping_total, 2, '.', ',') ?></td>
                </tr>
                <tr>
                    <td>Tax</td>
                    <td>$<?= number_format($total_tax, 2, '.', ',') ?></td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td>$<?= number_format($total, 2, '.', ',') ?></td>
                </tr>
            </table>
        </div>
    <?php
    
}

add_action("wp_ajax_testing_1", "testing_1");
add_action("wp_ajax_nopriv_testing_1", "testing_1");

function testing_1()
{
    qrx_create_custom_order(20);
}

add_action('woocommerce_payment_complete', 'qrx_create_custom_order', 10, 1);
function qrx_create_custom_order($order_id)
{

    $source_site_details = get_blog_details();

    $wc_order = wc_get_order($order_id);

    $wc_order_items = array();

    foreach ($wc_order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();

        $variation_id = $item->get_variation_id();

        $product = $item->get_product();

        $image_id = $product->get_image_id();

        $image_url = wp_get_attachment_image_url($image_id, 'full');

        $source_product_id = get_post_meta($product_id, 'source_product_id', true);

        $source_site_id = get_post_meta($product_id, 'source_site_id', true);

        $source_site_url = get_post_meta($product_id, 'source_site_url', true);

        $original_price = get_post_meta($product_id, 'original_price', true);

        $image = wp_get_attachment_image_src(get_post_thumbnail_id($loop->post->ID), 'single-post-thumbnail');

        $wc_order_items[] = array(
            'name' => $item->get_name(),
            'image_url' => $image_url,
            'quantity' => $item->get_quantity(),
            'subtotal' => $item->get_subtotal(),
            'total' => $item->get_total(),
            'price' => $product->get_price(),
            'tax' => $item->get_subtotal_tax(),
            'product_id' => $source_product_id ?: $product_id,
            'source_site_id' => $source_site_id ?: '',
            'source_site_url' => $source_site_url ?: '',
            'original_price' => $original_price ?: '',
        );
    }

    switch_to_blog(1);

    $custom_order = array(
        'post_title' => 'Custom Order',
        'post_content' => '',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'dispensary_orders',
    );

    $custom_post_id = wp_insert_post($custom_order);

    add_post_meta($custom_post_id, 'order_items', $wc_order_items);

    add_post_meta($custom_post_id, 'order_id', $order_id);

    add_post_meta($custom_post_id, 'total', $wc_order->get_total());

    add_post_meta($custom_post_id, 'total_tax', $wc_order->get_total_tax());

    add_post_meta($custom_post_id, 'date_created', $wc_order->get_date_created());

    add_post_meta($custom_post_id, 'date_paid', $wc_order->get_date_paid());

    add_post_meta($custom_post_id, 'subtotal', $wc_order->get_subtotal());

    add_post_meta($custom_post_id, 'shipping_total', $wc_order->get_shipping_total());

    add_post_meta($custom_post_id, 'sales_siteurl', $source_site_details->siteurl);

    add_post_meta($custom_post_id, 'sales_blog_id', $source_site_details->blog_id);

    add_post_meta($custom_post_id, 'blogname', $source_site_details->blogname);

    restore_current_blog();
}
