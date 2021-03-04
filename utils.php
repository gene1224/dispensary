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

function get_users_imported_products()
{
    $imported_products = [];

    foreach (get_blogs_of_user(get_current_user_id(), true) as $users_site) {

        switch_to_blog($users_site->userblog_id);

        $products = wc_get_products(array(
            'source_product_id' => true,

        ));

        foreach ($products as $key => $product) {
            $attachment_ids = $product->get_gallery_image_ids();
            $first_image_url = 'https://dummyimage.com/180x180/fff/000.png&text=Product';
            if (is_array($attachment_ids) && !empty($attachment_ids)) {
                $first_image_url = wp_get_attachment_url($attachment_ids[0]);
            }

            $imported_products[] = array(
                'source_product_id' => get_post_meta($product->id, 'source_product_id', true),
                'source_site_id' => get_post_meta($product->id, 'source_site_id', true),
                'source_site_url' => get_post_meta($product->id, 'source_site_url', true),
                'original_price' => $product->get_meta('original_price'),
                'categories' => $product->get_categories(', '),
                'tags' => $product->get_tags(', '),
                'index' => $key,
                'sku' => $product->get_sku(),
                'price' => $product->get_price(),
                'name' => $product->get_name(),
                'image' => $first_image_url,
            );
        }

        restore_current_blog();
    }
    return $imported_products;
}

function get_first_dispensary($user_id)
{
    $site_id = 0;
    foreach (get_blogs_of_user($user_id, true) as $blog) {
        if ($blog->userblog_id != 1) {
            $site_id = $blog->userblog_id;
            break;
        }
    }

    return $site_id;
}

function autologin()
{
    // PARAMETER TO CHECK FOR
    if ($_GET['autologin'] == 'demo') {

        // ACCOUNT USERNAME TO LOGIN TO
        $creds['user_login'] = 'demo';

        // ACCOUNT PASSWORD TO USE
        $creds['user_password'] = 'demo';

        $creds['remember'] = true;
        $autologin_user = wp_signon($creds, false);

        if (!is_wp_error($autologin_user)) {
            header('Location: wp-admin');
        }
        // LOCATION TO REDIRECT TO
    }
}
// ADD CODE JUST BEFORE HEADERS AND COOKIES ARE SENT
add_action('after_setup_theme', 'autologin');
