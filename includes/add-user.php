<?php
function warranty_claim_register_user() {
    ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="form-wrap">
        <h1>Add New Home Owner</h1>
        <form method="post" action="">
            <?php wp_nonce_field('add_new_user_action', 'add_new_user_nonce'); ?>
            <label for="username">Username <span class="required-mark">*</span></label>
            <input type="text" class="input" id="username" name="username" required />
            
            <label for="email">Email <span class="required-mark">*</span></label>
            <input type="email" class="input" id="email" name="email" required />

            <label for="phone-number">Phone Number</label>
            <input type="tel" class="input" id="phone-number" name="phone_number" />

            <label for="address">Property Address</label>
            <input type="text" class="input" id="address" name="address" />
            
            <label for="date-field">Effective Date</label>
            <input type="date" class="input" id="date-field" name="effective_date" />
            
            <label for="status">Status</label>
            <input type="text" class="input" id="status" name="status" value="Active" readonly />
            
            <input type="submit" class="button button-primary" value="Add Home Owner" />
        </form>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_new_user_nonce'])) {
        custom_add_new_user();
    }
}

function custom_add_new_user() {
    if (!isset($_POST['add_new_user_nonce']) || !wp_verify_nonce($_POST['add_new_user_nonce'], 'add_new_user_action')) {
        wp_die('Security check failed.');
    }

    $username = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $phone_number = sanitize_text_field($_POST['phone_number']);
    $address = sanitize_text_field($_POST['address']);
    $effective_date = sanitize_text_field($_POST['effective_date']);
    $status = 'active';

    if (username_exists($username) || email_exists($email)) {
        // echo '<div class="error"><p>Username or email already exists.</p></div>';
        ?>
        <script type="text/javascript">
            Swal.fire({
                title: "Something went wrong!",
                text: "Username or email already exists.",
                icon: "error"
            });
        </script>
        <?php
        return;
    }
    $generated_password = wp_generate_password();
    $user_id = wp_insert_user([
        'user_login' => $username,
        'user_email' => $email,
        'user_pass'  => $generated_password,
        'role'       => 'home_owner',
    ]);

    if (is_wp_error($user_id)) {
        // echo '<div class="error"><p>Failed to add user: ' . $user_id->get_error_message() . '</p></div>';
        ?>
        <script type="text/javascript">
            Swal.fire({
                title: "Something went wrong!",
                text: "An error occured while adding user, please try again.",
                icon: "error"
            });
        </script>
        <?php
    } else {
        update_user_meta($user_id, 'plain_text_password', $generated_password);
        update_user_meta($user_id, 'custom_user_status', 'active');
        update_user_meta($user_id, 'phone_number', $phone_number);
        update_user_meta($user_id, 'address', $address);
        update_user_meta($user_id, 'effective_date', $effective_date);

        ?>
        <script type="text/javascript">
            Swal.fire({
                title: "User Added",
                text: "The user <?php echo esc_js($username); ?> has been successfully added!",
                icon: "success"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "<?php echo esc_url(admin_url('admin.php?page=warranty-claim-users-list')); ?>";
                }
            });
        </script>
        <?php
    }
}
