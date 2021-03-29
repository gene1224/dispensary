<?php

add_filter('the_title', 'woo_title_order_received', 10, 2);

function woo_title_order_received($title, $id)
{
    if (function_exists('is_order_received_page') &&
        is_order_received_page() && get_the_ID() === $id) {
        $title = "Thank you for signing up!";
    }
    return $title;
}

add_filter('woocommerce_thankyou_order_received_text', 'text_content', 20, 2);

function text_content($thank_you_title, $order)
{
    return '';
}

function custom_memberships_thank_you()
{

    $thank_you_message = "<p>We are excited to have you onboard. You can see more details about your membership and start editing your website <a href='/my-account'>right here</a>.</p>";

    return $thank_you_message;

}

add_filter('woocommerce_memberships_thank_you_message', 'custom_memberships_thank_you');
