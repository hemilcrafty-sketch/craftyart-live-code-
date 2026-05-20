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



        @if ($roleManager::isAdmin(Auth::user()->user_type))

                    <div class="row" style="margin-top: 400px;">
                        <div class="col-xl-2 mb-30">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <a href="{{route('transcation_logs')}}"> <div class="weight-600 font-13">Today's Subs</div> </a>
                                        <div class="h7 mb-0">{{$datas['today_subs_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['today_subs_usd']}}</div>

                                        <a href="{{route('purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Today's Templates</div> </a>
                                        <div class="h7 mb-0">{{$datas['today_templates_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['today_templates_usd']}}</div>

                                        <a href="{{route('cari_purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Today's Caricatures</div> </a>
                                        <div class="h7 mb-0">{{$datas['today_caricature_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['today_caricature_usd']}}</div>

                                        <a href="{{route('video_transcation_logs')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Today's Videos</div> </a>
                                        <div class="h7 mb-0">{{$datas['today_video_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['today_video_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">Today's Total</div>
                                        <div class="h7 mb-0">{{$datas['today_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['today_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;"></div>
                                        <div class="h7 mb-0">{{$datas['today_final']}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 mb-30">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <a href="{{route('transcation_logs')}}"> <div class="weight-600 font-13">Yesterday's Subs</div> </a>
                                        <div class="h7 mb-0">{{$datas['yesterday_subs_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['yesterday_subs_usd']}}</div>

                                        <a href="{{route('purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Yesterday's Templates</div> </a>
                                        <div class="h7 mb-0">{{$datas['yesterday_templates_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['yesterday_templates_usd']}}</div>

                                        <a href="{{route('cari_purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Yesterday's Caricatures</div> </a>
                                        <div class="h7 mb-0">{{$datas['yesterday_caricature_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['yesterday_caricature_usd']}}</div>

                                        <a href="{{route('video_transcation_logs')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Yesterday's Videos</div> </a>
                                        <div class="h7 mb-0">{{$datas['yesterday_video_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['yesterday_video_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">Yesterday's Total</div>
                                        <div class="h7 mb-0">{{$datas['yesterday_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['yesterday_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;"></div>
                                        <div class="h7 mb-0">{{$datas['yesterday_final']}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 mb-30">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <a href="{{route('transcation_logs')}}"> <div class="weight-600 font-13">This Month's Subs</div> </a>
                                        <div class="h7 mb-0">{{$datas['this_month_subs_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_month_subs_usd']}}</div>

                                        <a href="{{route('purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">This Month's Templates</div> </a>
                                        <div class="h7 mb-0">{{$datas['this_month_templates_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_month_templates_usd']}}</div>

                                        <a href="{{route('cari_purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">This Month's Caricatures</div> </a>
                                        <div class="h7 mb-0">{{$datas['this_month_caricature_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_month_caricature_usd']}}</div>

                                        <a href="{{route('video_transcation_logs')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">This Month's Videos</div> </a>
                                        <div class="h7 mb-0">{{$datas['this_month_video_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_month_video_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">This Month's Total</div>
                                        <div class="h7 mb-0">{{$datas['this_month_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_month_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;"></div>
                                        <div class="h7 mb-0">{{$datas['this_month_final']}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 mb-30">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <a href="{{route('transcation_logs')}}"> <div class="weight-600 font-13">Last Month's Subs</div> </a>
                                        <div class="h7 mb-0">{{$datas['last_month_subs_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_month_subs_usd']}}</div>

                                        <a href="{{route('purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Last Month's Templates</div> </a>
                                        <div class="h7 mb-0">{{$datas['last_month_templates_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_month_templates_usd']}}</div>

                                        <a href="{{route('cari_purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Last Month's Caricatures</div> </a>
                                        <div class="h7 mb-0">{{$datas['last_month_caricature_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_month_caricature_usd']}}</div>

                                        <a href="{{route('video_transcation_logs')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Last Month's Videos</div> </a>
                                        <div class="h7 mb-0">{{$datas['last_month_video_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_month_video_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">Last Month's Total</div>
                                        <div class="h7 mb-0">{{$datas['last_month_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_month_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;"></div>
                                        <div class="h7 mb-0">{{$datas['last_month_final']}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 mb-30">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <a href="{{route('transcation_logs')}}"> <div class="weight-600 font-13">This Year's Subs</div> </a>
                                        <div class="h7 mb-0">{{$datas['this_year_subs_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_year_subs_usd']}}</div>

                                        <a href="{{route('purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">This Year's Templates</div> </a>
                                        <div class="h7 mb-0">{{$datas['this_year_templates_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_year_templates_usd']}}</div>

                                        <a href="{{route('cari_purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">This Year's Caricatures</div> </a>
                                        <div class="h7 mb-0">{{$datas['this_year_caricature_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_year_caricature_usd']}}</div>

                                        <a href="{{route('video_transcation_logs')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">This Year's Videos</div> </a>
                                        <div class="h7 mb-0">{{$datas['this_year_video_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_year_video_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">This Year's Total</div>
                                        <div class="h7 mb-0">{{$datas['this_year_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['this_year_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;"></div>
                                        <div class="h7 mb-0">{{$datas['this_year_final']}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 mb-30">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <a href="{{route('transcation_logs')}}"> <div class="weight-600 font-13">Last Year's Subs</div> </a>
                                        <div class="h7 mb-0">{{$datas['last_year_subs_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_year_subs_usd']}}</div>

                                        <a href="{{route('purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Last Year's Templates</div> </a>
                                        <div class="h7 mb-0">{{$datas['last_year_templates_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_year_templates_usd']}}</div>

                                         <a href="{{route('cari_purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Last Year's Caricatures</div> </a>
                                        <div class="h7 mb-0">{{$datas['last_year_caricature_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_year_caricature_usd']}}</div>

                                        <a href="{{route('video_transcation_logs')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Last Year's Videos</div> </a>
                                        <div class="h7 mb-0">{{$datas['last_year_video_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_year_video_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">Last Year's Total</div>
                                        <div class="h7 mb-0">{{$datas['last_year_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['last_year_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;"></div>
                                        <div class="h7 mb-0">{{$datas['last_year_final']}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 mb-30">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <a href="{{route('transcation_logs')}}"> <div class="weight-600 font-13">Total Subs</div> </a>
                                        <div class="h7 mb-0">{{$datas['total_subs_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['total_subs_usd']}}</div>

                                        <a href="{{route('purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Total Templates</div> </a>
                                        <div class="h7 mb-0">{{$datas['total_templates_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['total_templates_usd']}}</div>

                                        <a href="{{route('cari_purchases')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Total Caricatures</div> </a>
                                        <div class="h7 mb-0">{{$datas['total_caricature_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['total_caricature_usd']}}</div>

                                        <a href="{{route('video_transcation_logs')}}"> <div class="weight-600 font-13" style="margin-top: 20px;">Total Videos</div> </a>
                                        <div class="h7 mb-0">{{$datas['total_video_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['total_video_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">Total</div>
                                        <div class="h7 mb-0">{{$datas['total_inr']}}</div>
                                        <div class="h7 mb-0">{{$datas['total_usd']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;"></div>
                                        <div class="h7 mb-0">{{$datas['total_final']}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-2 mb-30">
                            <div class="card-box height-200-p widget-style2">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="widget-data">
                                        <div class="weight-600 font-13">Total Razorpay Inr</div>
                                        <div class="h7 mb-0">{{$datas['today_razorpay_inr']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">Yesterday Razorpay Inr</div>
                                        <div class="h7 mb-0">{{$datas['yesterday_razorpay_inr']}}</div>

                                        <div class="weight-600 font-13" style="margin-top: 20px;">{{$datas['editor_history']}}</div>

                                        <a href="https://panel.craftyartapp.com/templates/refreshTanscation" target="_blank"> <div class="weight-600 font-13" style="margin-top: 20px;">Refresh Transcation</div> </a>

                                        <a href="https://panel.craftyartapp.com/templates/export-users" target="_blank"> <div class="weight-600 font-13" style="margin-top: 20px;">Get Users Excel</div> </a>
                                        <a href="https://panel.craftyartapp.com/templates/export-datas" target="_blank"> <div class="weight-600 font-13" style="margin-top: 20px;">Get Purchase Excel</div> </a>
                                        <a href="https://panel.craftyartapp.com/templates/export-sub-datas" target="_blank"> <div class="weight-600 font-13" style="margin-top: 20px;">Get Subs Excel</div> </a>
                                        <a href="https://panel.craftyartapp.com/templates/export-meta-sub-datas" target="_blank"> <div class="weight-600 font-13" style="margin-top: 20px;">Get Meta Subs Excel</div> </a>

                                    </div>
                                </div>
                            </div>
                        </div>
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
