<?php

function warranty_list_user() {

    if (is_user_logged_in()) {

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $status = get_user_meta($user_id, 'custom_user_status', true);
        if ((in_array('home_owner', (array) $current_user->roles) && $status == 'active') || in_array('administrator', (array) $current_user->roles)) {

        ob_start();

        ?>
        <style>
            .c-container{
                margin:150px auto;
            }
            .form_link_div{
                text-align:center;
            }
            .form_link{
                background: #8abd33;
                text-decoration: none !important;
                color: white;
                padding: 10px;
            }
            .form_link:hover{
                color: white !important;
            }
        </style>
        <div class="home-owner-dashboard c-container">

            <h1>Welcome, <?php echo esc_html($current_user->display_name); ?></h1>

            <p>Here you can manage your claims, <a href="<?php echo get_permalink(get_option('home_owner_profile_page_id')) ?>">view your profile</a>, and more.</p>

            <h2>Your Submitted Claims</h2>

            <?php
            $form_url = get_permalink(get_option('warranty_form_page_id'));
            $claims_args = array(

                'post_type'      => 'claim_entry',

                'post_status'    => 'publish',

                'posts_per_page' => -1,

                'author'         => $user_id,

            );

            $claims_query = new WP_Query($claims_args);

            if ($claims_query->have_posts()) {

                ?>

                <table class="claim-table" style="width:100%; border-collapse: collapse;">

                    <thead>

                        <tr>

                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Title</th>

                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Description</th>

                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        while ($claims_query->have_posts()) {

                            $claims_query->the_post();

                            $claim_status = get_post_meta(get_the_ID(), 'claim_status', true);

                            ?>

                            <tr>

                                <td style="border: 1px solid #ddd; padding: 8px;"><?php the_title(); ?></td>

                                <td style="border: 1px solid #ddd; padding: 8px;"><?php the_content(); ?></td>

                                <td style="border: 1px solid #ddd; padding: 8px; color: <?php echo $claim_status == 'completed' ? 'green' : '' ?>"><?php echo ucwords(str_replace('_', ' ', esc_html($claim_status))); ?></td>

                            </tr>

                            <?php

                        }

                        ?>

                    </tbody>

                </table>

                <?php

            } else {

                echo '<p>No claims submitted yet.</p>';

            }

            wp_reset_postdata();

            ?>
            <div class="form_link_div">
                <a href="<?php echo $form_url ?>" class="form_link">Submit New</a>
            </div>
        </div>

        <?php

        return ob_get_clean();
    } else {
        wp_logout();
        wp_redirect(site_url('login-page?status=inactive'));
        exit;
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

add_shortcode('warranty_list_user', 'warranty_list_user');