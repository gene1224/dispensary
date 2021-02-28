<?php

function product_external_data_meta_box()
{
    $product_id = get_the_ID();

    $original_product_id = get_post_meta(get_the_ID(), 'source_product_id', true);

    $source_site_id = get_post_meta(get_the_ID(), 'source_site_id', true);

    $source_site_url = get_post_meta(get_the_ID(), 'source_site_url', true);

    $original_price = get_post_meta(get_the_ID(), 'original_price', true);

    ob_start();?>

    <p>Soure Website :
        <a href="<?=$source_site_url ? $source_site_url : get_site_url('source_site_id')?>">
            <?=$source_site_url ? $source_site_url . ' - External' : get_site_url('source_site_id') . '/ Site ID ' . $source_site_id?>
        </a>
    </p>
    
    <p>Source Product ID: <?=$original_product_id?>

    <p>Original Price : $<?=$original_price?> </p>

    <?php

    $output = $original_product_id ? ob_get_clean() : 'N/A';

    echo $output;
}

function add_product_external_data_meta_box()
{
    add_meta_box(
        "product-external-data-box",
        "External Information",
        "product_external_data_meta_box",
        "product",
        "side",
        "high",
        null
    );
}

add_action("add_meta_boxes", "add_product_external_data_meta_box");

function add_original_price_field($fields)
{
    $args = array(
        'label' => 'Original Price',
        'placeholder' => 'Original Price',
        'id' => 'original_price',
        'desc_tip' => true,
        'description' => 'Original price before the product is imported',
    );

    woocommerce_wp_text_input($args);
}

function save_original_price_field($post_id)
{

    $custom_field_value = isset($_POST['original_price']) ? $_POST['original_price'] : '';

    $product = wc_get_product($post_id);

    $product->update_meta_data('original_price', $custom_field_value);

    $product->save();

}

add_action('woocommerce_product_options_pricing', 'add_original_price_field');
add_action('woocommerce_process_product_meta', 'save_original_price_field');