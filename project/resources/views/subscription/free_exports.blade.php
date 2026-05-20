 
 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
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
                                    <h3 style="font-size: larger;color:black;">Free Exports</h3>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-9">
                                <div class="pt-20">
                                    <form action="{{ route('free_exports') }}" method="GET">
                                        <div class="form-group">
                                            <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                                <label>Search:<input type="text" class="form-control" name="query"
                                                        placeholder="Search here....."
                                                        value="{{ request()->input('query') }}"></label> <button
                                                    type="submit" class="btn btn-primary">Search</button>
                                            </div>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>***</th>
                                        <th>Draft ID</th>
                                        <th>Email / Contact No</th>
                                        <th>Name</th>
                                        <th>Thumb</th>
                                        <th style="width: 60px;">Email Send</th>
                                        <th style="width: 60px;word-break: break-word"> Wp Send</th>
                                        <th>Export Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datas['transcationArray'] as $transcation)
                                        <tr>
                                            <td class="table-plus">{{ $transcation->id }}</td>
                                            <td class="table-plus">{{ $transcation->path }}</td>

                                            <td>
                                                <a target="_blank"href="user_detail/{{ $transcation->uid }}">{{ $transcation->userData->email}} <br> {{ $transcation->getContactNo() }}</a>
                                            </td>

                                            <td>{{ $transcation->userData->name }}</td>

                                            <td>
                                                <img src="{{ $transcation->draft->thumbs[0] ?? '' }}"
                                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px;">
                                            </td>
                                            <td style="font-size: 15px;">
                                                @if ($transcation->email_sent > 0)
                                                <span style="color: green; font-weight: bold;">✔
                                                        {{ $transcation->email_sent }}</span>
                                                @else
                                                <span style="color: red; font-weight: bold;">✗</span>
                                                @endif
                                            </td>
                                            <td style="font-size: 15px;">
                                                @if ($transcation->wp_sent > 0)
                                                <span style="color: green; font-weight: bold;">✔
                                                        {{ $transcation->wp_sent }}</span>
                                                @else
                                                <span style="color: red; font-weight: bold;">✗</span>
                                                @endif
                                            </td>


                                            <td>{{ $transcation->created_at }}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                    aria-live="polite">{{ $datas['count_str'] }}</div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                                    <ul class="pagination">
                                        {{ $datas['transcationArray']->appends(request()->input())->links('pagination::bootstrap-4') }}
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

@include('layouts.masterscript')

</body>

</html>
