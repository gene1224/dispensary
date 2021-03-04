<?php

function autologin_generator()
{
    if (isset($_REQUEST['auto_login'])) {
        $token = time();
        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'auto_login', $token);
            foreach (get_blogs_of_user(get_current_user_id()) as $site) {
                if ($site->userblog_id != 1) {
                    wp_redirect($site->siteurl . '?auto_login_code=' . $token . '&user_id=' . get_current_user_id());
                }
            }
        }
    }
}

add_action('init', 'autologin_generator');

function auto_login_reciever()
{

    if (!is_user_logged_in() && isset($_REQUEST['auto_login_code'])) {

        if (get_user_meta($_REQUEST['user_id'], 'auto_login', $_REQUEST['auto_login_code']) == $_REQUEST['auto_login_code']) {

            $user = get_user_by('id', $_REQUEST['user_id']);
            if (!$user) {
                return;
            }

            wp_set_current_user($user->ID, $user->user_login);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login);
            wp_redirect(get_site_url() . "/frontend-manager");
        }
    } else if (is_user_logged_in() && isset($_REQUEST['auto_login_code'])) {

        wp_redirect(get_site_url() . "/frontend-manager");
    }
}
add_action('init', 'auto_login_reciever');
