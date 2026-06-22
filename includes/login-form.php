<?php
// Prevent direct access to the file.
if (!defined('ABSPATH')) {
    exit;
}

function warranty_claim_login_form_shortcode() {
    ob_start();

    if (is_user_logged_in()) {
        $dashboard_page_id = get_option('warranty_list_page_id');
        if ($dashboard_page_id) {
            wp_redirect(get_permalink($dashboard_page_id));
            exit;
        }
    }

    if (isset($_GET['login']) && $_GET['login'] === 'failed') {
        echo '<div class="error-message">Invalid username or password. Please try again.</div>';
    }
    if (isset($_GET['status']) && $_GET['status'] === 'inactive') {
        echo '<div class="error-message">Your account is inactive. Please contact support for more information.</div>';
    }
    ?>
    <div class="custom-login-container c-container">
        <h2>Home Owner Login</h2>
        <form method="post" action="">
            <label for="username">Username or Email:</label>
            <input type="text" id="username" name="log" placeholder="Enter your username or email" required>
            
            <label for="password">Password:</label>
            <input type="text" id="password" name="pwd" placeholder="Enter your password" required>
            <?php do_action('my_custom_login_form'); ?>
            <input type="hidden" name="home_owner_login_form" value="1">
            
            <button type="submit" class="login-button">Login</button>
        </form>
    </div>
    <style>
        .custom-login-container {
            max-width: 400px;
            margin: 200px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
padding-top:130px;
        }
        .custom-login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .custom-login-container label {
            display: block;
            margin-bottom: 5px;
        }
        .custom-login-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .custom-login-container .login-button {
            width: 100%;
            padding: 10px;
            background-color: #8abd33;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .custom-login-container .login-button:hover {
            background-color: #005c8a;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('login_form', 'warranty_claim_login_form_shortcode');

function warranty_claim_handle_login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'], $_POST['pwd'], $_POST['home_owner_login_form'])) {

        $username = sanitize_text_field($_POST['log']);
        $password = sanitize_text_field($_POST['pwd']);
        $user = wp_signon([
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        ]);

        if (is_wp_error($user)) {
            wp_redirect(add_query_arg('login', 'failed', site_url('/login-page')));
            exit;
        } else {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $status = get_user_meta($user_id, 'custom_user_status', true);
            if(in_array('home_owner', (array) $current_user->roles) && $status == 'active'){
                $redirectUrl = site_url('/warranty-list');
            } else {
                $redirectUrl = site_url('/login-page?status=inactive');
            }
            wp_redirect($redirectUrl);
            exit;
        }
    }
}
add_action('template_redirect', 'warranty_claim_handle_login');
