 

   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">

  

    <div class="pd-ltr-20">
        <div class="row">

            {{-- @if ($helperController::isAdmin(Auth::user()->user_type))
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_app')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Applications</div> <br>
                                <table class="main-row-tb single">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Total</td>
                                            <td>{{$datas['app']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Live</td>
                                            <td>{{$datas['app_live']}}</td>
                                        </tr>
                                        <tr>
                                            <td>UnLive</td>
                                            <td>{{$datas['app_unlive']}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endif --}}
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_cat')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Categories</div><br>
                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['cat']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['cat_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['cat_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_cat']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_cat_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_cat_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_item')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Templates</div>
                                <div class="h5 mb-0" style="opacity: 0;">Templates</div>

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['all_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['all_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['all_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_theme')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Theme</div>
                                <div class="h5 mb-0" style="opacity: 0;">Themes</div>
                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['theme_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['theme_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['theme_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_theme_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_theme_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_theme_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="row">
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_bg_cat')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Background Categories</div>
                                <div class="h5 mb-0" style="opacity: 0;">Background Categories</div>

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['bg_cat']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['bg_cat_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['bg_cat_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_bg_cat']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_bg_cat_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_bg_cat_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_bg_item')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Background Items</div>
                                <div class="h5 mb-0" style="opacity: 0;">Background Items</div>

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['bg_cat']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['bg_cat_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['bg_cat_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_bg_cat']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_bg_cat_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_bg_cat_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>

                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_sticker_cat')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Sticker Categories</div>
                                <div class="h5 mb-0" style="opacity: 0;">Sticker Categories</div>

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['stk_cat']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['stk_cat_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['stk_cat_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_stk_cat']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_stk_cat_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_stk_cat_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_sticker_item')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Sticker Items</div>
                                <div class="h5 mb-0" style="opacity: 0;">Sticker Items</div>

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['stk_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['stk_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['stk_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_stk_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_stk_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_stk_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_fonts')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Fonts</div><br />

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['fonts']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['fonts_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['fonts_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_fonts']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_fonts_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_fonts_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-6 mb-30">
                <a href="{{route('colors.index')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Color Items</div>
                                <div class="h5 mb-0" style="opacity: 0;">Color Items</div>

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['color_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['color_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['color_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_color_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_color_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_color_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 mb-30">
                <a href="{{route('show_fonts')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Fonts</div><br />

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['fonts']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['fonts_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['fonts_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_fonts']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_fonts_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_fonts_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-6 mb-30">
                <a href="{{route('colors.index')}}">
                    <div class="card-box height-200-p widget-style2">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="widget-data">
                                <div class="weight-600 font-13">Color Items</div>
                                <div class="h5 mb-0" style="opacity: 0;">Color Items</div>

                                <table class="main-row-tb">
                                    <thead>
                                        <tr>
                                            <th>ALL RECORD</th>
                                            <th>USER RECORD</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['color_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['color_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['color_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="row-col-tb">
                                                    <tr>
                                                        <td>Total</td>
                                                        <td>{{$datas['user_color_item']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Live</td>
                                                        <td>{{$datas['user_color_item_live']}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UnLive</td>
                                                        <td>{{$datas['user_color_item_unlive']}}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </a>
            </div>
        </div>

        @if ($roleManager::isAdmin(Auth::user()->user_type))

        <hr>
        <div class="row">
            <div class="col-xl-12 mb-30">
                <h5>Subscription</h5> <br />
                <div class="card-container">
                    <a class="card" href="{{route('transcation_logs')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Today</h3>
                            <p><strong>{{$datas['today_subs_inr']}}</strong></p>
                            <p><strong>{{$datas['today_subs_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="{{route('transcation_logs')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Yesterday</h3>
                            <p><strong>{{$datas['yesterday_subs_inr']}}</strong></p>
                            <p><strong>{{$datas['yesterday_subs_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="{{route('transcation_logs')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Current Month</h3>
                            <p><strong>{{$datas['this_month_subs_inr']}}</strong></p>
                            <p><strong>{{$datas['this_month_subs_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="{{route('transcation_logs')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Last Month</h3>
                            <p><strong>{{$datas['last_month_subs_inr']}}</strong></p>
                            <p><strong>{{$datas['last_month_subs_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="{{route('transcation_logs')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Total</h3>
                            <p><strong>{{$datas['total_subs_inr']}}</strong></p>
                            <p><strong>{{$datas['total_subs_usd']}}</strong></p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-xl-12 mb-30">
                <h5>Templates</h5> <br />
                <div class="card-container">
                    <a class="card" href="{{route('purchases')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Today</h3>
                            <p><strong>{{$datas['today_templates_inr']}}</strong></p>
                            <p><strong>{{$datas['today_templates_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="{{route('purchases')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Yesterday</h3>
                            <p><strong>{{$datas['yesterday_templates_inr']}}</strong></p>
                            <p><strong>{{$datas['yesterday_templates_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="{{route('purchases')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Current Month</h3>
                            <p><strong>{{$datas['this_month_templates_inr']}}</strong></p>
                            <p><strong>{{$datas['this_month_templates_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="{{route('purchases')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Last Month</h3>
                            <p><strong>{{$datas['last_month_templates_inr']}}</strong></p>
                            <p><strong>{{$datas['last_month_templates_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="{{route('purchases')}}">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Total</h3>
                            <p><strong>{{$datas['total_templates_inr']}}</strong></p>
                            <p><strong>{{$datas['total_templates_usd']}}</strong></p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-xl-12 mb-30">
                <h5>Videos</h5> <br />
                <div class="card-container">
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Today</h3>
                            <p><strong>{{$datas['today_video_inr']}}</strong></p>
                            <p><strong>{{$datas['today_video_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Yesterday</h3>
                            <p><strong>{{$datas['yesterday_video_inr']}}</strong></p>
                            <p><strong>{{$datas['yesterday_video_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Current Month</h3>
                            <p><strong>{{$datas['this_month_video_inr']}}</strong></p>
                            <p><strong>{{$datas['this_month_video_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Last Month</h3>
                            <p><strong>{{$datas['last_month_video_inr']}}</strong></p>
                            <p><strong>{{$datas['last_month_video_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Total</h3>
                            <p><strong>{{$datas['total_video_inr']}}</strong></p>
                            <p><strong>{{$datas['total_video_usd']}}</strong></p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-xl-12 mb-30">
                <h5>Total</h5> <br />
                <div class="card-container">
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Today</h3>
                            <p><strong>{{$datas['today_inr']}}</strong></p>
                            <p><strong>{{$datas['today_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Yesterday</h3>
                            <p><strong>{{$datas['yesterday_inr']}}</strong></p>
                            <p><strong>{{$datas['yesterday_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Current Month</h3>
                            <p><strong>{{$datas['this_month_inr']}}</strong></p>
                            <p><strong>{{$datas['this_month_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Last Month</h3>
                            <p><strong>{{$datas['last_month_inr']}}</strong></p>
                            <p><strong>{{$datas['last_month_usd']}}</strong></p>
                        </div>
                    </a>
                    <a class="card" href="javascript:void(0);">
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="content">
                            <h3>Total</h3>
                            <p><strong>{{$datas['total_inr']}}</strong></p>
                            <p><strong>{{$datas['total_usd']}}</strong></p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        {{-- *******************************************************************************************************
        --}}

        {{-- <div class="row">
            <div class="col-xl-2 mb-30">
                <div class="card-box height-200-p widget-style2">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="widget-data">
                            <a href="{{route('transcation_logs')}}">
                                <div class="weight-600 font-13">Today's Subs</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['today_subs_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['today_subs_usd']}}</div>

                            <a href="{{route('purchases')}}">
                                <div class="weight-600 font-13" style="margin-top: 20px;">Today's Templates</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['today_templates_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['today_templates_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">Today's Videos</div>
                            <div class="h7 mb-0">{{$datas['today_video_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['today_video_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">Today's Total</div>
                            <div class="h7 mb-0">{{$datas['today_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['today_usd']}}</div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xl-2 mb-30">
                <div class="card-box height-200-p widget-style2">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="widget-data">
                            <a href="{{route('transcation_logs')}}">
                                <div class="weight-600 font-13">Yesterday's Subs</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['yesterday_subs_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['yesterday_subs_usd']}}</div>

                            <a href="{{route('purchases')}}">
                                <div class="weight-600 font-13" style="margin-top: 20px;">Yesterday's Templates</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['yesterday_templates_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['yesterday_templates_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">Yesterday's Videos</div>
                            <div class="h7 mb-0">{{$datas['yesterday_video_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['yesterday_video_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">Yesterday's Total</div>
                            <div class="h7 mb-0">{{$datas['yesterday_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['yesterday_usd']}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 mb-30">
                <div class="card-box height-200-p widget-style2">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="widget-data">
                            <a href="{{route('transcation_logs')}}">
                                <div class="weight-600 font-13">This Month's Subs</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['this_month_subs_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['this_month_subs_usd']}}</div>

                            <a href="{{route('purchases')}}">
                                <div class="weight-600 font-13" style="margin-top: 20px;">This Month's Templates</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['this_month_templates_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['this_month_templates_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">This Month's Videos</div>
                            <div class="h7 mb-0">{{$datas['this_month_video_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['this_month_video_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">This Month's Total</div>
                            <div class="h7 mb-0">{{$datas['this_month_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['this_month_usd']}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 mb-30">
                <div class="card-box height-200-p widget-style2">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="widget-data">
                            <a href="{{route('transcation_logs')}}">
                                <div class="weight-600 font-13">Last Month's Subs</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['last_month_subs_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['last_month_subs_usd']}}</div>

                            <a href="{{route('purchases')}}">
                                <div class="weight-600 font-13" style="margin-top: 20px;">Last Month's Templates</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['last_month_templates_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['last_month_templates_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">Last Month's Videos</div>
                            <div class="h7 mb-0">{{$datas['last_month_video_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['last_month_video_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">Last Month's Total</div>
                            <div class="h7 mb-0">{{$datas['last_month_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['last_month_usd']}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 mb-30">
                <div class="card-box height-200-p widget-style2">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="widget-data">
                            <a href="{{route('transcation_logs')}}">
                                <div class="weight-600 font-13">Total Subs</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['total_subs_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['total_subs_usd']}}</div>

                            <a href="{{route('purchases')}}">
                                <div class="weight-600 font-13" style="margin-top: 20px;">Total Templates</div>
                            </a>
                            <div class="h7 mb-0">{{$datas['total_templates_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['total_templates_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">Total Videos</div>
                            <div class="h7 mb-0">{{$datas['total_video_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['total_video_usd']}}</div>

                            <div class="weight-600 font-13" style="margin-top: 20px;">Total</div>
                            <div class="h7 mb-0">{{$datas['total_inr']}}</div>
                            <div class="h7 mb-0">{{$datas['total_usd']}}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div> --}}

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
                                value="{{$datas['cache']}}" required>
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
    $('#dynamic_form').on('submit', function (event) {
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
            beforeSend: function () {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function (data) {
                hideFields();
                if (data.error) {
                    var error_html = '';
                    for (var count = 0; count < data.error.length; count++) {
                        error_html += '<p>' + data.error[count] + '</p>';
                    }
                    $('#result').html('<div class="alert alert-danger">' + error_html + '</div>');
                } else {
                    $('#result').html('<div class="alert alert-success">' + data.success + '</div>');
                }
                setTimeout(function () {
                    $('#result').html('');
                }, 3000);
            },
            error: function (error) {
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