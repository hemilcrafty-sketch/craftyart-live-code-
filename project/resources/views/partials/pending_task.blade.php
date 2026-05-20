<div class="iconclass">
    <button type="button" class="btn btn-success accept-reject-btn" onclick="approveChange({{ $id }})">
        <i class="bi bi-check2"></i>
    </button>
</div>

<div class="iconclass ml-2">
    <button type="button" class="btn btn-danger accept-reject-btn" onclick="rejectTask({{ $id }})">
        <i class="bi bi-x-circle"></i>
    </button>
</div>


<div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog" aria-labelledby="rejectionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectionModalLabel">Reject Task</h5>
                    <button class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="reject_task_id" name="id">
                    <div class="form-group">
                        <label for="reason">Why do you want to reject this task?</label>
                        <textarea class="form-control accept-reject-btn" id="reason" name="reason" rows="4"
                            placeholder="Enter rejection reason..." required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary accept-reject-btn"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="rejectionForm" class="btn btn-danger accept-reject-btn">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmApproval(id) {
        if (confirm('Are you sure you want to approve this change?')) {
            approveChange(id);
        }
    }

    function approveChange(id) {
        $.ajax({
            url: "{{ route('pending-task.approve') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            success: function(res) {
                alert(res.success || 'Approved successfully.');
                window.location.href = "{{ route('show_pending_task') }}";
            },
            error: function(err) {
                alert('Approval failed.');
            }
        });
    }


    // When reject button is clicked
    function rejectTask(id) {
        $('#reject_task_id').val(id); // set task id
        $('#reason').val(''); // clear previous value
        $('#rejectionModal').modal('show'); // open modal
    }

    // When form is submitted
    $('#rejectionForm').click(function(e) {
        e.preventDefault();

        let id = $('#reject_task_id').val();
        let reason = $('#reason').val();

        if (!reason.trim()) {
            alert('Please provide a reason for rejection.');
            return;
        }

        $.ajax({
            url: "{{ route('pending-task.reject') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                reason: reason
            },
            success: function(res) {
                //    alert(res.success || 'Rejected successfully.');
                $('#rejectionModal').modal('hide');
                window.location.href = "{{ route('show_pending_task') }}";
                // location.reload();
            },
            error: function(err) {
                alert('Rejection failed.');
            }
        });
    });
</script>
