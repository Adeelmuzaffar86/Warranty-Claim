jQuery(document).ready(function($) {
    $('.toggle-status').on('change', function () {
        showLoader();
        var userId = $(this).closest('tr').data('user-id');
        var status = $(this).prop('checked') ? 'active' : 'inactive';
        
        $.ajax({
            url: userStatusAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'update_user_status',
                user_id: userId,
                status: status,
                nonce: userStatusAjax.nonce 
            },
            success: function (response) {
                if (response.success) {
                    var statusText = response.data.status === 'active' ? 'Active' : 'Deactivated';
                    var statusColor = response.data.status === 'active' ? 'green' : 'red';
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "User status updated!",
                        showConfirmButton: false,
                        timer: 1000
                      });
                    $('tr[data-user-id="' + userId + '"] .status-text').text(statusText).css('color', statusColor);
                } else {
                    Swal.fire({
                        position: "top-end",
                        icon: "error",
                        title: "There was an error updating the user status.",
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
});
