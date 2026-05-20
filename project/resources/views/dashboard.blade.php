@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container">
    <div class="pd-ltr-20">
        <div class="row">

            @if ($roleManager::isAdmin(Auth::user()->user_type))
                <div class="col-xl-3 mb-30">
                    <a href="{{ route('show_app') }}">
                        <div class="card-box height-200-p widget-style2">
                            <div class="d-flex flex-wrap align-items-center">
                                <div class="widget-data">
                                    <div class="weight-600 font-13">Applications</div>
                                    <div class="h5 mb-0" style="opacity: 0;">Applications</div>
                                    <div class="h5 mb-0">Total - {{ $datas['app'] }}</div>
                                    <div class="h5 mb-0">Live - {{ $datas['app_live'] }}</div>
                                    <div class="h5 mb-0">Unlive - {{ $datas['app_unlive'] }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @else
                <div class="col-xl-3 mb-30">
                    <a href="{{ route('show_fonts') }}">
                        <div class="card-box height-200-p widget-style2">
                            <div class="d-flex flex-wrap align-items-center">
                                <div class="widget-data">
                                    <div class="weight-600 font-13">Fonts</div>
                                    <div class="h5 mb-0" style="opacity: 0;">Fonts</div>
                                    <div class="h5 mb-0">Total - {{ $datas['fonts'] }}</div>
                                    <div class="h5 mb-0">Live - {{ $datas['fonts_live'] }}</div>
                                    <div class="h5 mb-0">Unlive - {{ $datas['fonts_unlive'] }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            <div class="col-xl-3 mb-30">
                <a href="{{ route('show_cat') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Categories</div>
                                <div class="h5 mb-0" style="opacity: 0;">Categories</div>
                                <div class="h5 mb-0">Total - {{ $datas['cat'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['cat_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['cat_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 mb-30">
                <a href="{{ route('show_new_cat') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">New Categories</div>
                                <div class="h5 mb-0" style="opacity: 0;">New Categories</div>
                                <div class="h5 mb-0">Total - {{ $datas['new_categories_item'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['new_categories_item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['new_categories_item_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 mb-30">
                <a href="{{ route('show_item') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Templates</div>
                                <div class="h5 mb-0" style="opacity: 0;">Templates</div>
                                <div class="h5 mb-0">Total - {{ $datas['item'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['item_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 mb-30">
                <a href="{{ route('show_sticker_cat.index') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Sticker Categories</div>
                                <div class="h5 mb-0" style="opacity: 0;">Sticker Categories</div>
                                <div class="h5 mb-0">Total - {{ $datas['stk_cat'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['stk_cat_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['stk_cat_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 mb-30">
                <a href="{{ route('sticker_item.index') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Sticker Items</div>
                                <div class="h5 mb-0" style="opacity: 0;">Sticker Items</div>
                                <div class="h5 mb-0">Total - {{ $datas['stk_item'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['stk_item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['stk_item_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 mb-30">
                <a href="{{ route('show_bg_cat.index') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Background Categories</div>
                                <div class="h5 mb-0" style="opacity: 0;">Background Categories</div>
                                <div class="h5 mb-0">Total - {{ $datas['bg_cat'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['bg_cat_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['bg_cat_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 mb-30">
                <a href="{{ route('show_bg_item.index') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Background Items</div>
                                <div class="h5 mb-0" style="opacity: 0;">Background Items</div>
                                <div class="h5 mb-0">Total - {{ $datas['bg_item'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['bg_item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['bg_item_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>



        {{-- <div class="row">
                    <div class="col-xl-4 mb-30">
                        <a href="{{route('religions.index')}}">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <div class="weight-600 font-13">Religion</div>
                                        <div class="h5 mb-0" style="opacity: 0;">Religion</div>
                                        <div class="h5 mb-0">Total   - {{$datas['religion_item']}}</div>
                                        <div class="h5 mb-0">Live    - {{$datas['religion_item_live']}}</div>
                                        <div class="h5 mb-0">Unlive  - {{$datas['religion_item_unlive']}}</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-4 mb-30">
                        <a href="{{route('show_lang')}}">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <div class="weight-600 font-13">Language</div>
                                        <div class="h5 mb-0" style="opacity: 0;">Language</div>
                                        <div class="h5 mb-0">Total   - {{$datas['language_item']}}</div>
                                        <div class="h5 mb-0">Live    - {{$datas['language_item_live']}}</div>
                                        <div class="h5 mb-0">Unlive  - {{$datas['language_item_unlive']}}</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 mb-30">
                        <a href="{{route('show_app')}}">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <div class="weight-600 font-13">Colors</div>
                                        <div class="h5 mb-0" style="opacity: 0;">Colors</div>
                                        <div class="h5 mb-0">Total   - {{$datas['color_item']}}</div>
                                        <div class="h5 mb-0">Live    - {{$datas['color_item_live']}}</div>
                                        <div class="h5 mb-0">Unlive  - {{$datas['color_item_unlive']}}</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-4 mb-30">
                        <a href="{{route('show_keyword')}}">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <div class="weight-600 font-13">Special Keyword</div>
                                        <div class="h5 mb-0" style="opacity: 0;">Special Keyword</div>
                                        <div class="h5 mb-0">Total   - {{$datas['keyword_item']}}</div>
                                        <div class="h5 mb-0">Live    - {{$datas['keyword_item_live']}}</div>
                                        <div class="h5 mb-0">Unlive  - {{$datas['keyword_item_unlive']}}</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-4 mb-30">
                        <a href="{{route('show_search_tag')}}">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <div class="weight-600 font-13">Search Tag</div>
                                        <div class="h5 mb-0" style="opacity: 0;">Search Tag</div>
                                        <div class="h5 mb-0">Total   - {{$datas['search_tag_item']}}</div>
                                        <div class="h5 mb-0">Live    - {{$datas['search_tag_item_live']}}</div>
                                        <div class="h5 mb-0">Unlive  - {{$datas['search_tag_item_unlive']}}</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 mb-30">
                        <a href="{{route('show_app')}}">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <div class="weight-600 font-13">Interest</div>
                                        <div class="h5 mb-0" style="opacity: 0;">Interest</div>
                                        <div class="h5 mb-0">Total   - {{$datas['interest_item']}}</div>
                                        <div class="h5 mb-0">Live    - {{$datas['interest_item_live']}}</div>
                                        <div class="h5 mb-0">Unlive  - {{$datas['interest_item_unlive']}}</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div> --}}


        <div class="row">
            <div class="col-xl-3 mb-30">
                <a href="{{ route('frame_categories.index') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Shape Categories</div>
                                <div class="h5 mb-0" style="opacity: 0;">Shape Categories</div>
                                <div class="h5 mb-0">Total - {{ $datas['frame_cat_item'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['frame_cat_item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['frame_cat_item_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 mb-30">
                <a href="{{ route('frame_items.index') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Shape Item</div>
                                <div class="h5 mb-0" style="opacity: 0;">Shape Item</div>
                                <div class="h5 mb-0">Total - {{ $datas['frame_item'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['frame_item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['frame_item_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 mb-30">
                <a href="{{ route('show_v_cat') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Video Templates Category</div>
                                <div class="h5 mb-0" style="opacity: 0;">Video Templates Category</div>
                                <div class="h5 mb-0">Total - {{ $datas['video_cat_item'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['video_cat_item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['video_cat_item_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 mb-30">
                <a href="{{ route('show_v_item') }}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Video Templates Item</div>
                                <div class="h5 mb-0" style="opacity: 0;">Video Templates Item</div>
                                <div class="h5 mb-0">Total - {{ $datas['video_template_item'] }}</div>
                                <div class="h5 mb-0">Live - {{ $datas['video_template_item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['video_template_item_unlive'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- rejected   --}}
        @if ($roleManager::isSeoExecutive(Auth::user()->user_type))
            <div class="row">
                <div class="col-xl-3 mb-30">
                    <a href="{{ route('rejecte_task') }}">
                        <div class="card-box height-200-p widget-style2">
                            <div class="d-flex flex-wrap align-items-center">
                                <div class="widget-data">
                                    <div class="weight-600 font-13">Rejected Task</div>
                                    <div class="h5 mb-0" style="opacity: 0;">Shape Categories</div>
                                    <div class="h5 mb-0">Total - {{ $datas['pending_task'] }}</div>
                                    {{-- <div class="h5 mb-0">Live - {{ $datas['frame_cat_item_live'] }}</div>
                                <div class="h5 mb-0">Unlive - {{ $datas['frame_cat_item_unlive'] }}</div> --}}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @endif

        @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type))
        <div class="row">
            <div class="col-xl-3 mb-30">
                <div class="card-box h-200-p widget-style2" style="padding: 15px;">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="widget-data">
                            <div class="weight-600 mb-3 font-13">Index Page Count</div>
                            <div class="h5 mb-0">Category Page - {{ $datas['index_category_page'] }}</div>
                            <div class="h5 mb-0">Special Page - {{ $datas['index_special_page'] }}</div>
                            <div class="h5 mb-0">Keyword Page - {{ $datas['index_keyword_page'] }}</div>
                            <div class="h5 mb-0">Product Page - {{ $datas['index_product_page'] }}</div>
                            <div class="h5 mb-0">Orphan Page - {{ $datas['index_orphan_page'] }}</div>
                            <div class="h5 mb-0">Caricature Category - {{ $datas['index_caricature_cat'] }}</div>
                            <div class="h5 mb-0">Caricature Attire - {{ $datas['index_caricature_attire'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if ($roleManager::isAdmin(Auth::user()->user_type))

                    <div class="row" style="margin-top: 400px;">


                        @foreach($datas['revenue_data'] as $period => $rows)


                            <div class="col-xl-2 mb-30">
                                <div class="card-box height-200-p widget-style2">
                                    <div class="d-flex flex-wrap align-items-center">
                                        <div class="widget-data" style="display: grid; row-gap: 20px;">

                                            @foreach($rows as $row)
                                                
                                                <div style="color: {{ ($row['ignore'] ?? false) ? '#ff0000' : '#031e23' }}">
                                                    @if(isset($row['link']))
                                                        <a href="{{ $row['link'] }}" style="color: {{ ($row['ignore'] ?? false) ? '#ff0000' : '#555555' }}"> <div class="weight-600 font-13">{{ $row['title'] }}</div> </a>
                                                    @else
                                                        <div class="weight-600 font-13">{{ $row['title'] }}</div>
                                                    @endif

                                                    @if(isset($row['inr']))
                                                        <div class="h7 mb-0">{{ $row['inr'] ?? '-' }}</div>
                                                    @endif

                                                    @if(isset($row['usd']))
                                                        <div class="h7 mb-0">{{ $row['usd'] ?? '-' }}</div>
                                                    @endif

                                                    @if(isset($row['final']))
                                                        <div class="h7 mb-0">{{ $row['final'] ?? '-' }}</div>
                                                    @endif
                                                </div>
    
                                            @endforeach
                                    
                                        </div>
                                    </div>
                                </div>
                            </div>
                          
                        @endforeach

                        @foreach($datas['extras'] as $rows)

                            <div class="col-xl-2 mb-30">
                                <div class="card-box height-200-p widget-style2">
                                    <div class="d-flex flex-wrap align-items-center">
                                        <div class="widget-data" style="display: grid; row-gap: 20px;">

                                            @foreach($rows as $row)
                                                
                                                <div>
                                                    @if(isset($row['link']))
                                                        <a href="{{ $row['link'] }}" target="{{ $row['target'] ?? '_self' }}"> <div class="weight-600 font-13">{{ $row['title'] }}</div> </a>
                                                    @else
                                                        <div class="weight-600 font-13">{{ $row['title'] }}</div>
                                                    @endif

                                                    @if(isset($row['value']))
                                                        <div class="h7 mb-0">{{ $row['value'] }}</div>
                                                    @endif
                                                </div>
        
                                            @endforeach
                                    
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endforeach

                    </div>
                @endif
        <div style="display: none;">
            <form method="post" id="dynamic_form" enctype="multipart/form-data">
                <span id="result"></span>
                @csrf
                <div class="row">
                    <div class="col-md-2 col-sm-12">
                        <div class="form-group">
                            <h6>Cache Version</h6>
                            <input class="form-control-file form-control" type="number" name="cache_ver"
                                value="{{ $datas['cache'] }}" required>
                        </div>
                    </div>
                    <div class="col-md-1 col-sm-12">
                        <div class="form-group">
                            <h6 style="opacity: 0;">.</h6>
                            <input class="btn btn-primary" type="submit" name="submit" value="update">
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();
        count = 0;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        $.ajax({
            url: 'update_cache_ver',
            type: 'POST',
            data: formData,
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(data) {
                hideFields();
                if (data.error) {
                    var error_html = '';
                    for (var count = 0; count < data.error.length; count++) {
                        error_html += '<p>' + data.error[count] + '</p>';
                    }
                    $('#result').html('<div class="alert alert-danger">' + error_html + '</div>');
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success +
                        '</div>');
                }
                setTimeout(function() {
                    $('#result').html('');
                }, 3000);
            },
            error: function(error) {
                hideFields();
                window.alert(error.responseText);
            },
            cache: false,
            contentType: false,
            processData: false
        })
    });

    function hideFields() {
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }
</script>
</body>

</html>
