@include('layouts.masterhead')
<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">

                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">

                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <div class="pd-20">
                                    <h3 style="font-size: larger;color:black;">Credits Transaction Logs</h3>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-9">
                                <div class="pt-20">
                                    <form action="{{ route(Route::currentRouteName()) }}" method="GET">
                                        <div class="form-group">
                                            <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                                <label>Search:<input type="text" class="form-control" name="query" placeholder="Search here....." value="{{ request()->input('query') }}"></label> <button type="submit" class="btn btn-primary">Search</button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="pd-20">
                            <a href="#" class="btn btn-primary" data-backdrop="static" data-toggle="modal"
                                data-target="#add_transaction_model" type="button">
                                Add Bonus Credits </a>
                        </div>

                        <div class="col-sm-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>***</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Ref ID</th>
                                    <th>Txn ID</th>
                                    <th>Type</th>
                                    <th>Reason</th>
                                    <th>Debited</th>
                                    <th>Credited</th>
                                    <th>Created At</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($transactions as $transaction)
                                <tr>
                                    <td class="table-plus">{{ $transaction->id }}</td>

                                    <td>
                                        <a target="_blank"href="user_detail/{{ $transaction->user_id }}">{{ $transaction->user->name }}</a>
                                    </td>


                                    <td>{{ $transaction->user->email }} <br> {{ $transaction->user->contact_no ?? '' }}</td>

                                    <td>{{ $transaction->ref_id ?? "-" }}</td>

                                    <td>{{ $transaction->txn_id ?? "-" }}</td>

                                    <td>{{ $transaction->type ?? "-" }}</td>

                                    <td>{{ $transaction->reason ?? "-" }}</td>

                                    <td>{{ $transaction->debited }} </td>

                                    <td>{{ $transaction->credited }}</td>

                                    <td>{{ $transaction->created_at }}</td>

                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                     aria-live="polite">{{$str_count}}</div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                                    <ul class="pagination">
                                        {{ $transactions->appends(request()->input())->links('pagination::bootstrap-4') }}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_transaction_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Add Bonus Credits</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body">
                <form method="post" id="add_transaction_form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h6>Email ID</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" name="email" required="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Credits</h6>
                        <div class="input-group custom">
                            <input type="text" class="form-control" name="credits" required="" />
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

@include('layouts.masterscript')

<script>
    $('#add_transaction_form').on('submit', function(event) {
        event.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        $.ajax({
            url: "{{ route('add_credit_bonus') }}",
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                alert(data.success || 'Subscription added successfully!');
                location.reload();

            },
            error: function(error) {
                alert(data.success || 'Subscription added successfully!');
                location.reload();
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });
</script>

</body>
</html>