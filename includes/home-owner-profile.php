<?php
function home_owner_profile_shortcode() {

    if (is_user_logged_in()) {

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $status = get_user_meta($user_id, 'custom_user_status', true);

        if ((in_array('home_owner', (array) $current_user->roles) && $status == 'active') || in_array('administrator', (array) $current_user->roles)) {

            // Fetch user meta fields (e.g., phone number)
            $phone_number = get_user_meta($user_id, 'phone_number', true);
            $address = get_user_meta($user_id, 'address', true);

            ob_start();

            ?>
            <style>
                .c-container-profile {
                    max-width: 600px;
                    margin: 150px auto;
                    padding: 20px;
                    background-color: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 10px;
                    font-family: Arial, sans-serif;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }

                .c-container-profile h1 {
                    font-size: 24px;
                    margin-bottom: 20px;
                    color: #333;
                }

                .c-container-profile label {
                    font-weight: bold;
                    color: #555;
                    margin-top: 10px;
                    display: block;
                }

                .c-container-profile input {
                    width: 100%;
                    padding: 8px;
                    margin: 5px 0 15px 0;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    background-color: #f1f1f1;
                    color: #333;
                }

                .c-container-profile input[readonly] {
                    cursor: not-allowed;
                }
            </style>

            <div class="home-owner-dashboard c-container-profile">
                <h1>Welcome, <?php echo esc_html($current_user->display_name); ?></h1>

                <label for="user_name">Name:</label>
                <input type="text" id="user_name" value="<?php echo esc_attr($current_user->display_name); ?>" readonly>

                <label for="user_email">Email Address:</label>
                <input type="email" id="user_email" value="<?php echo esc_attr($current_user->user_email); ?>" readonly>

                <label for="user_phone">Phone Number:</label>
                <input type="text" id="user_phone" value="<?php echo esc_attr($phone_number ? $phone_number : 'Not Provided'); ?>" readonly>

                <label for="user_address">Property Address:</label>
                <input type="text" id="user_address" value="<?php echo esc_attr($address ? $address : 'Not Provided'); ?>" readonly>
            </div>

            <?php

            return ob_get_clean();

        } else {
            logoutAndRedirectToLogin();
        }

    } else {

        $login_page_id = get_option('home_owner_login_page_id');

        if ($login_page_id) {
            wp_redirect(get_permalink($login_page_id));
        } else {
            wp_redirect(home_url());
        }

        exit;

    }

}

add_shortcode('home_owner_profile', 'home_owner_profile_shortcode');
?>
