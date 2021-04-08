<?php
function get_user_max_products($user_id)
{
    $max_products = 0;
    foreach (wc_memberships_get_user_memberships($user_id) as $membership) {
        $product_limit = get_post_meta($membership->plan_id, 'dispensary_product_limit', true) ?: 0;
        
        if ($max_products < $product_limit) {
            $max_products = $product_limit;
        }
    }
    return $max_products;
}


function get_user_plan_name($user_id) {
    $plan_name = 'Not Subscribed';
    $max_products = 0;
    foreach (wc_memberships_get_user_memberships($user_id) as $membership) {
        $product_limit = get_post_meta($membership->plan_id, 'dispensary_product_limit', true) ?: 0;
        
        if ($max_products < $product_limit) {
            $max_products = $product_limit;
            $plan_name = $membership->plan->name;
        }
    }
    return $plan_name;
}
