<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="alertModalLabel">Error</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>

            </div>
            <div class="modal-body" id="alertModalBody">
                <!-- Message will be inserted here -->
            </div>
        </div>
    </div>
</div>

<script>
    function showAlertModal(message, callback = null) {
        $('#alertModalBody').html(message);
        $('#alertModal').modal('show');

        // Allow close on button click manually (fallback)
        $('#alertModal .close').off('click').on('click', function() {
            $('#alertModal').modal('hide');
        });

        if (callback) {
            $('#alertModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
                callback();
            });
        }
    }
</script>
