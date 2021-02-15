<?php

function my_general_settings_register_fields()
{
    register_setting('general', 'stripe_account_id', 'esc_attr');
    add_settings_field('stripe_account_id', '<label for="stripe_account_id">' . __('Copyright Message', 'stripe_account_id') . '</label>', 'stripe_account_id_HTML', 'general');
}

function stripe_account_id_HTML()
{
    $copyright_message = get_option('stripe_account_id', '');
    if($copyright_message != ''){
        echo "<p>Stripe Account : ******************************</p>";
        echo '<input id="copyright_message" type="text" style="width: 35%;" type="text" name="stripe_account_id" />';
    } else {
        echo '<input id="copyright_message" style="width: 35%;" type="text" name="stripe_account_id" value="' . $copyright_message . '" />';
    }
    
}

add_filter('admin_init', 'my_general_settings_register_fields');
