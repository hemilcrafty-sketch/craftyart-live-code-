<div class="modal fade" id="add_color_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Color</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="addColorForm" action="{{route('colors.store')}}">
                    @csrf
                    <div class="form-group">
                        <h7>Code</h7>
                        <div class="input-group custom">
                            <input type="text" class="form-control" placeholder="Enter Code" name="code" required="" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <input class="btn btn-primary btn-block" type="submit" name="submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- <script src="{{asset('assets/vendors/scripts/core.js')}}"></script>
<script src="{{asset('assets/vendors/scripts/script.min.js')}}"></script> --}}


