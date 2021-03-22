<?php

class AdminMembershipMetaBox
{

    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'qrx_add_custom_order_meta_box'], 40);
        add_action('save_post', [$this, 'save_product_limit']);
    }

    public function qrx_add_custom_order_meta_box()
    {
        add_meta_box(
            'memberships-external-data-box',
            'Dispensary product limit',
            [$this, 'max_products_input_field'],
            'wc_membership_plan',
            'normal',
            'high',
            null
        );
    }

    public function max_products_input_field()
    {
        global $timber;

        $post_id = get_the_ID();

        $context = array(
            'dispensary_product_limit' => get_post_meta($post_id, 'dispensary_product_limit', true) ?: 0,
        );

        echo $timber->compile('admin/admin-membership-meta-box.twig', $context);

    }

    public function save_product_limit($post_id)
    {

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['dispensary_product_limit'])) {
            update_post_meta($post_id, 'dispensary_product_limit', sanitize_text_field($_POST['dispensary_product_limit']));
        } else {
            update_post_meta($post_id, 'dispensary_product_limit', 0);
        }

    }

}

$adminMembershipMetaBox = new AdminMembershipMetaBox();
