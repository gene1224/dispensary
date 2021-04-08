<?php

function autologin_generator()
{
    if (isset($_REQUEST['auto_login'])) {
        $token = time();
        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'auto_login', $token);
            foreach (get_blogs_of_user(get_current_user_id()) as $site) {
                if ($site->userblog_id != 1) {
                    wp_redirect($site->siteurl . '?auto_login_code=' . $token . '&user_id=' . get_current_user_id(). isset($_REQUEST['auto_login_code']) ? '&new_product=1' : '');
                }
            }
        }
    }
}

add_action('init', 'autologin_generator');

