<?php
function sku_test()
{
    $product_id = wc_get_product_id_by_sku($_REQUEST['sku']);
    if (isset($_REQUEST['priint'])) {
        print_r($product_id);
        die();
    }

    return $product_id;
}
add_action('wp_ajax_sku_test', 'sku_test');

function create_attribute($attribute_name, $attribute_slug, $public = 0)
{
    if (taxonomy_exists($attribute_name)) {
        return false;
    }
    delete_transient('wc_attribute_taxonomies');

    \WC_Cache_Helper::invalidate_cache_group('woocommerce-attributes');

    $attribute_labels = wp_list_pluck(wc_get_attribute_taxonomies(), 'attribute_label', 'attribute_name');

    $attributeWCName = array_search($attribute_slug, $attribute_labels, true);

    if (!$attributeWCName) {
        $attributeWCName = wc_sanitize_taxonomy_name($attribute_slug);
    }

    $attribute_id = wc_attribute_taxonomy_id_by_name($attributeWCName);

    if (!$attribute_id) {
        $taxonomy_name = wc_attribute_taxonomy_name($attributeWCName);
        unregister_taxonomy($taxonomy_name);
        $attribute_id = wc_create_attribute(array(
            'name' => $attribute_name,
            'slug' => $attribute_slug,
            'type' => 'select',
            'order_by' => 'menu_order',
            'has_archives' => 0,
        ));

        register_taxonomy($taxonomy_name, apply_filters('woocommerce_taxonomy_objects_' . $taxonomy_name, array(
            'product',
        )), apply_filters('woocommerce_taxonomy_args_' . $taxonomy_name, array(
            'labels' => array(
                'name' => $attribute_slug,
            ),
            'hierarchical' => false,
            'show_ui' => false,
            'query_var' => true,
            'rewrite' => false,
        )));
    }

    return wc_get_attribute($attribute_id);
}

function create_term(string $term_name, string $term_slug, string $taxonomy, int $order = 0)
{
    $taxonomy = wc_attribute_taxonomy_name($taxonomy);

    if (!$term = get_term_by('slug', $term_slug, $taxonomy)) {
        $term = wp_insert_term($term_name, $taxonomy, array(
            'slug' => $term_slug,
        ));
        $term = get_term_by('id', $term['term_id'], $taxonomy);
        if ($term) {
            update_term_meta($term->term_id, 'order', $order);
        }
    }

    return $term;
}

function add_product_attributes($product, $old_attributes)
{
    $attributes = (array) $product->get_attributes();

    foreach ($old_attributes as $attribute) {
        $attribute_slug = sanitize_title($attribute->name);

        create_attribute($attribute->name, $attribute_slug);

        $term_ids = [];

        foreach ($attribute->options as $option) {
            $option_slug = sanitize_title($option);
            $term_ids[] = create_term($option, $option_slug, $attribute_slug);
        }

        $new_attribute = new WC_Product_Attribute();
        $new_attribute->set_id(sizeof($attributes) + 1);
        $new_attribute->set_name($attribute->name);
        $new_attribute->set_options($term_ids);
        $new_attribute->set_position(sizeof($attributes) + 1);
        $new_attribute->set_visible(true);
        $new_attribute->set_variation(false);

        $attributes[] = $new_attribute;
    }

    $product->set_attributes($attributes); // -> NOT WORKING

    $product->save();

    return $product;
}

function microseconds_to_seconds($duration)
{
    $hours = (int) ($duration / 60 / 60);

    $minutes = (int) ($duration / 60) - $hours * 60;

    return (int) $duration - $hours * 60 * 60 - $minutes * 60;
}
