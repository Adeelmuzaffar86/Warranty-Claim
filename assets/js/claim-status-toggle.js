jQuery(document).ready(function($) {
    $('.status-dropdown').on('change', function () {
        showLoader();
        var claimId = $(this).closest('tr').data('claim-id');
        const status = $(this).val();
        $.ajax({
            url: claimStatusAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'update_claim_status',
                claim_id: claimId,
                status: status,
                nonce: claimStatusAjax.nonce 
            },
            success: function (response) {
                if (response.success) {
                    console.log(response);
                    var statusText = response.data.status;
                    var statusColor = statusText === 'Completed' ? 'green' : '#50575e';
                    var updateTime = response.data.update_time;
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Status updated!",
                        showConfirmButton: false,
                        timer: 1000
                      });
                    $('tr[data-claim-id="' + claimId + '"] .status-text').text(statusText).css('color', statusColor);
                    $('tr[data-claim-id="' + claimId + '"] .updated-time').text(updateTime);
                } else {
                    Swal.fire({
                        position: "top-end",
                        icon: "error",
                        title: "There was an error updating the status.",
                        showConfirmButton: false,
                        timer: 1000
                      });
                }
                hideLoader();
            },
            error: function () {
                hideLoader();
                Swal.fire({
                    position: "top-end",
                    icon: "error",
                    title: "Request failed. Please try again.",
                    showConfirmButton: false,
                    timer: 1000
                  });
            }
        });
    });
    $('.detail-button').on('click', function () {
        const $button = $(this);
        const claimId = $button.data('claim-id');
        $button.prop('disabled', true).text('Loading...');
        $.ajax({
            url: claimStatusAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_claim_detail',
                claim_id: claimId,
                nonce: claimStatusAjax.nonce,
            },
            success: function (response) {
                if (response.success) {
                    $('#claimDetailsPopup .modal-body').html(response.data.html);
                    $('#claimDetailsPopup').modal('show');
                } else {
                    alert('Failed to load details.');
                }
                $button.prop('disabled', false).text('View Details');
            },
            error: function () {
                alert('Error fetching claim details.');
                $button.prop('disabled', false).text('View Details');
            },
        });
    });

    // $('#close-modal').on('click', function () {
    //     $('#claim-detail-modal').hide();
    // });
});