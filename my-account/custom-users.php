<?php

class DispensaryCustomUser
{
    private $field_errors = array();

    private $nonce_key = 'qrx_custom_user';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'load_assets']);
        add_shortcode('custom_user_manager', [$this, 'load_template']);
        add_action('wp_ajax_create_dispensary_user', [$this, 'create_user']);

    }

    public function load_assets()
    {
        wp_register_script('custom_user_js', plugins_url('../assets/js/custom-user.js', __FILE__), array('jquery', 'sweetalert'), '1.0.0');
        wp_register_style('custom_user_css', plugins_url('../assets/css/custom-user.css', __FILE__), [], '1.0.0', 'all');
    }

    public function load_template()
    {
        global $timber;

        wp_enqueue_script('sweetalert');

        wp_enqueue_style('custom_user_css');

        $actions = array('create' => 'create_dispensary_user');

        $js_objects = array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce($nonce_key),
            'actions' => $actions,
            'first_name' => '',
            'last_name' => '',
            'user_email' => '',
            'edit' => isset($_REQUEST['edit']),
        );

        $users = array_map(function ($user) {
            return array(
                'id' => $user->data->ID,
                'user_login' => $user->data->user_login,
                'user_email' => $user->data->user_email,
                'first_name' => get_user_meta($user->data->ID, 'first_name', true),
                'last_name' => get_user_meta($user->data->ID, 'last_name', true),

            );
        }, get_customers_store_managers());

        wp_localize_script('custom_user_js', 'wp_ajax', $js_objects);
        wp_enqueue_script('custom_user_js');

        $context = array(
            'actions' => $actions,
            'nonce' => wp_create_nonce($nonce_key),
            'user_list' => $users,
        );
        $edit_user = isset($_REQUEST['edit']) ? get_userdata($_REQUEST['edit']) : false;
        if ($edit_user) {
            $context['user_login'] = $edit_user->user_login;
            $context['first_name'] = get_user_meta($edit_user->ID, 'first_name', true);
            $context['last_name'] = get_user_meta($edit_user->ID, 'last_name', true);
            $context['user_id'] = $edit_user->ID;
            $context['user_email'] = $edit_user->user_email;

            echo $timber->compile('custom-user/form.twig', $context);
        } else if (isset($_REQUEST['add'])) {
            echo $timber->compile('custom-user/form.twig', $context);
        } else {
            echo $timber->compile('custom-user/index.twig', $context);
        }

    }

    public function create_user()
    {
        
        if (isset($_REQUEST['user_id'])) {
            return $this->update_user($_REQUEST['user_id']);
        }

        $user_id = get_current_user_id();
        
        $user_blog_id = get_user_meta($user_id, 'dispensary_blog_id', true);

        if (!$user_blog_id) {
            die();
        }

        $email = $_POST['email'];

        $password = $_POST['password'];

        $first_name = $_POST['first_name'];

        $last_name = $_POST['last_name'];

        $username = $_POST['username'];

        $role = 'store_manager';

        if (!validate_username($username) || username_exists($username)) {
            $this->field_errors['username'] = username_exists($username) ? 'exist' : 'invalid';
        }
        if ($_POST['confirm_password'] != $_POST['password'] || !$password) {
            $this->field_errors['password'] = !$password ? 'required' : 'not_matched';
        }
        if (email_exists($email) || !$email) {
            $this->field_errors['email'] = !$email ? 'required' : 'exist';
        }
        if (!$last_name || strlen($last_name) <= 0) {
            $this->field_errors['last_name'] = 'required';
        }
        if (!$first_name || strlen($first_name) <= 0) {
            $this->field_errors['first_name'] = 'required';
        }

        if (count($this->field_errors) != 0) {
            echo json_encode(array('errors' => $this->field_errors));
            die();
        }

        if (email_exists($email) == false) {
            $new_user_id = wp_insert_user(
                array(
                    'user_pass' => $password,
                    'user_login' => $username,
                    'user_email' => $email,
                    'last_name' => $last_name,
                    'first_name' => $first_name,
                )
            );
            if ($new_user_id) {
                add_user_to_blog($user_blog_id, $new_user_id, 'shop_manager');
                update_user_meta($new_user_id, 'created_on_my_account', true);
                update_user_meta($new_user_id, 'created_by_user_id', $user_id);
                
                
            }

        } else {
            $random_password = __('User already exists.  Password inherited.');
        }
        $response = array('success' => true);

        echo json_encode($response);

        die();
    }

    private function update_user($user_id)
    {

        $email = $_POST['email'];

        $password = $_POST['password'];

        $first_name = $_POST['first_name'];

        $last_name = $_POST['last_name'];

        $username = $_POST['username'];

        $role = 'store_manager';

        if ($_POST['confirm_password'] != $_POST['password']) {
            $this->field_errors['password'] = !$password ? 'required' : 'not_matched';
        }
        if (email_exists($email) || !$email) {
            if (get_userdata($user_id)->user_email != $email) {
                $this->field_errors['email'] = !$email ? 'required' : 'exist';
            }

        }
        if (!$last_name || strlen($last_name) <= 0) {
            $this->field_errors['last_name'] = 'required';
        }
        if (!$first_name || strlen($first_name) <= 0) {
            $this->field_errors['first_name'] = 'required';
        }

        if (count($this->field_errors) != 0) {
            echo json_encode(array('errors' => $this->field_errors));
            die();
        }
        $data = array(
            'user_login' => get_userdata($user_id)->user_login,
            'ID' => $user_id,
            'user_email' => $email,
            'last_name' => $last_name,
            'first_name' => $first_name,
        );
        if ($password) {
            $data['user_pass'] = $password;
        }
        $new_user_id = wp_update_user(
            $data
        );
        $response = array('success' => true);

        echo json_encode($response);
        die();

    }
}

$DispensaryCustomUser = new DispensaryCustomUser();
