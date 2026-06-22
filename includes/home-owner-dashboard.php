<?php
function home_owner_dashboard_shortcode() {

    if (is_user_logged_in()) {

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $status = get_user_meta($user_id, 'custom_user_status', true);

        if ((in_array('home_owner', (array) $current_user->roles) && $status == 'active') || in_array('administrator', (array) $current_user->roles)) {

            ob_start();

            ?>
            <style>
                .c-container-warranty-form {
                    max-width: 800px;
                    /* margin: 0 auto; */
                    margin: 100px auto;
                }
                .c-container-warranty-form button {
                    background: #8abd33;
                }
            </style>
            <div class="home-owner-dashboard c-container-warranty-form">

                <h1>Welcome, <?php echo esc_html($current_user->display_name); ?></h1>

                <p>Here you can manage your claims, view your profile, and more.</p>

                <?php echo do_shortcode('[custom_user_form]'); ?>

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

add_shortcode('warranty_form', 'home_owner_dashboard_shortcode');
