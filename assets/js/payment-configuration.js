$(document).ready(function () {
  // Payment type checkbox styling
  $('.payment-type-checkbox-item input[type="checkbox"]').on('change', function () {
    if ($(this).is(':checked')) {
      $(this).closest('.payment-type-checkbox-item').addClass('checked');
    } else {
      $(this).closest('.payment-type-checkbox-item').removeClass('checked');
    }
  });

  // Add new credential field in add modal
  $('#addNewCredentialField').on('click', function () {
    const fieldHtml = `
            <div class="credential-row-box">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="small">Field Key *</label>
                            <input type="text"
                                   class="form-control credential-key"
                                   name="credential_keys[]"
                                   placeholder="e.g., api_key, secret_key"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label class="small">Field Value *</label>
                            <input type="text"
                                   class="form-control credential-value"
                                   name="credential_values[]"
                                   placeholder="Enter value"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-danger remove-add-credential-field"
                                style="margin-bottom: 8px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
    $('#addCredentialsContainer').append(fieldHtml);

    // Enable remove button for first field if there are more than 1 fields
    if ($('#addCredentialsContainer .credential-row-box').length > 1) {
      $('#addCredentialsContainer .credential-row-box:first .remove-add-credential-field').prop('disabled', false);
    }
  });

  // Remove credential field in add modal
  $(document).on('click', '.remove-add-credential-field', function () {
    if ($('#addCredentialsContainer .credential-row-box').length > 1) {
      $(this).closest('.credential-row-box').remove();

      // Disable remove button for first field if only 1 field remains
      if ($('#addCredentialsContainer .credential-row-box').length === 1) {
        $('#addCredentialsContainer .credential-row-box:first .remove-add-credential-field').prop('disabled', true);
      }
    }
  });

  // Add new gateway with credentials and payment types
  $('#addGatewayForm').on('submit', function (e) {
    e.preventDefault();

    // Validate payment types
    const selectedTypes = $('input[name="payment_types[]"]:checked').length;
    if (selectedTypes === 0) {
      showNotification('error', 'Please select at least one payment type');
      return;
    }

    const formData = new FormData(this);
    const saveBtn = $(this).find('button[type="submit"]');
    const originalText = saveBtn.html();

    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

    $.ajax({
      url: window.location.origin + '/payment_configuration/add-gateway',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        saveBtn.prop('disabled', false).html(originalText);
        if (response.success) {
          showNotification('success', response.message);
          $('#addGatewayModal').modal('hide');
          setTimeout(() => location.reload(), 1500);
        }
      },
      error: function (xhr) {
        saveBtn.prop('disabled', false).html(originalText);
        let message = 'Error adding gateway';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        showNotification('error', message);
      }
    });
  });
