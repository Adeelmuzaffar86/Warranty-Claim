<?php
function warranty_claim_users_list() {
    $args = array(
        'role' => 'home_owner',
        'orderby' => 'ID',
        'order' => 'ASC'
    );
    $users = get_users($args);
    ?>
    <div class="wrap">
        <h1>Home Owners</h1>
        <p>Below is a list of Home Owners:</p>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th class="manage-column column-title" scope="col">#</th>
                    <th class="manage-column column-title" scope="col">Username</th>
                    <th class="manage-column column-title" scope="col">Email</th>
                    <th class="manage-column column-title" scope="col">Phone Number</th>
                    <th class="manage-column column-title" scope="col">Address</th>
                    <th class="manage-column column-title" scope="col">Password</th>
                    <th class="manage-column column-title" scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $index => $user): ?>
                        <?php
                            $plain_password = get_user_meta($user->ID, 'plain_text_password', true);
                            $phone_number = get_user_meta($user->ID, 'phone_number', true);
                            $address = get_user_meta($user->ID, 'address', true);
                            $status = get_user_meta($user->ID, 'custom_user_status', true);
                            $status_text = ($status === 'active') ? 'Active' : 'Deactivated';
                            $status_color = ($status === 'active') ? 'green' : 'red';
                            $edit_user_url = get_edit_user_link($user->ID);
                        ?>
                        <tr class="user-row" data-user-id="<?php echo esc_attr($user->ID); ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <a href="<?php echo esc_url($edit_user_url); ?>" target="_blank">
                                    <?php echo esc_html($user->user_login); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html($phone_number) ? esc_html($phone_number) : '-' ?></td>
                            <td><?php echo esc_html($address) ? esc_html($address) : '-' ?></td>
                            <td><?php echo esc_html($plain_password); ?></td>
                            <td style="color:<?php echo esc_attr($status_color); ?>;display: flex;align-items: center;gap: 20px;">
                            <span class="status-text"><?php echo esc_html($status_text); ?></span>
                                <div class="toggler">
                                    <input id="toggler-<?php echo $index ?>" name="toggler-<?php echo $index ?>" type="checkbox" class="toggle-status" <?php echo ($status === 'active') ? 'checked' : ''; ?>>
                                    <label for="toggler-<?php echo $index ?>">
                                        <svg class="toggler-on" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                                            <polyline class="path check" points="100.2,40.2 51.5,88.8 29.8,67.5"></polyline>
                                        </svg>
                                        <svg class="toggler-off" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                                            <line class="path line" x1="34.4" y1="34.4" x2="95.8" y2="95.8"></line>
                                            <line class="path line" x1="95.8" y1="34.4" x2="34.4" y2="95.8"></line>
                                        </svg>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">No home owner found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
