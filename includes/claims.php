<?php

function warranty_claim_claims() {

    $claims_args = array(

        'post_type' => 'claim_entry',

        'post_status' => 'publish',

        'posts_per_page' => -1

    );

    $claims = get_posts($claims_args);

    ?>

    <div class="wrap">

        <h1>Claims</h1>

        <p>Below is a list of Claims made by Home Owners:</p>

        <table class="widefat fixed" cellspacing="0">

    <thead>

        <tr>

            <th class="manage-column column-title" scope="col">#</th>

            <th class="manage-column column-title" scope="col">Property Address</th>

            <th class="manage-column column-title" scope="col">Home Owner Name</th>

            <th class="manage-column column-title" scope="col">Claim</th>

            <th class="manage-column column-title" scope="col">Description</th>

            <th class="manage-column column-title" scope="col">Status</th>

            <th class="manage-column column-title" scope="col">Effective Date</th>

            <th class="manage-column column-title" scope="col">Date of Submission</th>

            <th class="manage-column column-title" scope="col">Last Updated</th>

            <th class="manage-column column-title" scope="col">View Details</th>

        </tr>

    </thead>

    <tbody>

        <?php if (!empty($claims)): ?>

            <?php foreach ($claims as $index => $claim): ?>

                <?php
                    $owner_id = $claim->post_author;
                    $claim_author = get_userdata($owner_id);
                    $property_address = get_user_meta($owner_id,'address',true);
                    $home_owner_name = $claim_author->user_login;
                    $claim_name = $claim->post_title;
                    $claim_description = wp_trim_words(wp_kses_post(get_the_excerpt($claim->ID)), 10, '...');
                    $stored_date = get_user_meta($owner_id, 'effective_date', true);
                    if($stored_date){
                        $effective_date = DateTime::createFromFormat('Y-m-d', $stored_date)->format('d-M-Y');
                    } else {
                        $effective_date = '';
                    }
                    // $effective_date = get_user_meta($owner_id,'effective_date',true);
                    $user_email = $claim_author->user_email;

                    $claim_status = get_post_meta($claim->ID, 'claim_status', true);

                    $status_color = ($claim_status === 'completed') ? 'green' : '';

                    $claim_status_text = ucwords(str_replace('_', ' ', $claim_status));



                    $post_date = get_the_date('d-M-Y g:i A', $claim->ID);

                    $post_modified = get_post_modified_time('d-M-Y g:i A', false, $claim->ID);

                ?>

                <tr class="user-row" data-claim-id="<?php echo esc_attr($claim->ID); ?>">

                    <td><?php echo $index + 1; ?></td>

                    <td><?php echo esc_html($property_address); ?></td>
                    <td><?php echo esc_html($home_owner_name); ?></td>
                    <td><?php echo esc_html($claim_name); ?></td>

                    <td><?php echo esc_html($claim_description); ?></td>

                    <td style="color:<?php echo esc_attr($status_color); ?>;">

                        <span class="status-text"><?php echo esc_html($claim_status_text); ?></span>

                        <select id="status-dropdown-<?php echo $index; ?>" name="status-dropdown-<?php echo $index; ?>" class="status-dropdown">

                            <option value="submitted" <?php selected($claim_status, 'submitted'); ?>>Submitted</option>

                            <option value="excluded" <?php selected($claim_status, 'excluded'); ?>>Excluded</option>

                            <option value="scheduled" <?php selected($claim_status, 'scheduled'); ?>>Scheduled</option>

                            <option value="completed" <?php selected($claim_status, 'completed'); ?>>Completed</option>

                        </select>

                    </td>

                    <td><?php echo $effective_date ?></td>
                    <td><?php echo esc_html($post_date); ?></td>

                    <td class="updated-time"><?php echo esc_html($post_modified); ?></td>

                    <td>

                        <button class="detail-button btn btn-dark" data-claim-id="<?php echo esc_attr($claim->ID); ?>">View Details</button>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="9" style="text-align:center;">No claims found</td>

            </tr>

        <?php endif; ?>

    </tbody>

</table>
<style>
    .modal-body p {
        font-size: 18px;
    }
</style>
    <div class="modal fade" id="claimDetailsPopup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Claim Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
        </div>
</div>

    <?php

}

