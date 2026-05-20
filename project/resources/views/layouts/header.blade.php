@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@guest
@else
<style>
    /* Custom styles for the sidebar */
    .selectSubMenu {
        color: #fff;
        background-color: rgb(169 169 169 / 40%);
    }

    .left-side-bar.open {
        left: -281px;
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
<div class="header">
    <div class="header-left">
        <div class="menu-icon dw dw-menu" style="font-size: 20px;"></div>
    </div>
    <div class="header-right">
        <div class="user-info">
            {{ Auth::user()->name }}
            &nbsp;&nbsp;&nbsp;
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="dw dw-logout"></i> {{ __('Log Out') }}
            </a>
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div>

@endguest
<div class="left-side-bar">
    <div class="brand-logo">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/vendors/images/deskapp-logo.svg') }}" alt="" class="dark-logo">
            <img src="{{ asset('assets/vendors/images/deskapp-logo-white.svg') }}" alt="" class="light-logo">
        </a>
        <div class="close-sidebar" data-toggle="left-sidebar-close">
            <i class="ion-close-round"></i>
        </div>
    </div>
    <div class="menu-block customscroll">
        <div class="sidebar-menu">
            <ul id="accordion-menu">
                @if (!$roleManager::isSalesManager(Auth::user()->user_type) && !$roleManager::isSalesEmployee(Auth::user()->user_type))
                    @if (!$roleManager::isAdmin(Auth::user()->user_type))
                    <li class="dropdown {{ Route::currentRouteName() == 'dashboard' ? 'show' : '' }}">
                        <a href="javascript:;" class="dropdown-toggle">
                                <span class="micon"><img src="{{ asset('assets/vendors/images/menu_icon/Dashboard.svg') }}"
                                                         alt=""></span><span class="mtext">Dashboard</span>
                        </a>
                        <ul class="submenu"
                            style="display: {{ Route::currentRouteName() == 'dashboard' ? 'block' : 'none' }};">
                            @if (Route::currentRouteName() == 'dashboard' && Request::segment(2) == '1')
                            <li>
                                <a href="{{ route('dashboard', 1) }}" class="selectSubMenu">All Data</a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard') }}">Your Data</a>
                            </li>
                            @else
                            <li>
                                <a href="{{ route('dashboard', 1) }}">All Data</a>
                            </li>
                            <li>
                                <a href="{{ route('dashboard') }}"
                                   class="{{ Route::currentRouteName() == 'dashboard' ? 'selectSubMenu' : '' }}">Your
                                    Data</a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @else
                    <li class="dropdown">
                        <a href="{{ route('dashboard') }}"
                           class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'dashboard' ? 'selectSubMenu' : '' }}">
                                <span class="micon"><img
                                            src="{{ asset('assets/vendors/images/menu_icon/Dashboard.svg') }}"
                                            alt=""></span><span class="mtext">Dashboard</span>
                        </a>
                    </li>
                    @endif


                <li
                        class="dropdown {{ in_array(Route::currentRouteName(), [
                        'show_style',
                        'show_theme',
                        'show_keyword',
                        'create_keyword',
                        'edit_keyword',
                        'show_search_tag',
                        'new_search_tags.index',
                        'show_lang',
                        'sizes.index',
                        'sizes.create',
                        'sizes.edit',
                        'colors.index',
                        'religions.index',
                        'show_interest',

                        // SPECIAL PAGE
                        'special_page',
                        'create_pages',

                        // CATEGORIES
                        'show_cat',
                        'create_cat',
                        'edit_cat',
                        'show_new_cat',
                        'create_new_cat',
                        'edit_new_cat',
                        'show_virtual_cat',
                        'create_virtual_cat',
                        'edit_virtual_cat',

                        // REVIEWS
                        'p_reviews.index',
                        'reviews.index',
                    ])
                        ? 'show'
                        : '' }}">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon">
                            <img src="{{ asset('assets/vendors/images/menu_icon/Filter.svg') }}" alt="">
                        </span>
                        <span class="mtext">SEO</span>
                    </a>

                    <ul class="submenu"
                        style="display:{{ in_array(Route::currentRouteName(), [
                            'show_style',
                            'show_theme',
                            'show_keyword',
                            'create_keyword',
                            'edit_keyword',
                            'show_search_tag',
                            'new_search_tags.index',
                            'show_lang',
                            'sizes.index',
                            'sizes.create',
                            'sizes.edit',
                            'colors.index',
                            'religions.index',
                            'show_interest',
                            'special_page',
                            'create_pages',
                            'show_cat',
                            'create_cat',
                            'edit_cat',
                            'show_new_cat',
                            'create_new_cat',
                            'edit_new_cat',
                            'show_virtual_cat',
                            'create_virtual_cat',
                            'edit_virtual_cat',
                            'p_reviews.index',
                            'reviews.index',
                        ])
                            ? 'block'
                            : 'none' }};">

                        {{-- 🔹 Filters --}}
                        <li
                                class="dropdown {{ in_array(Route::currentRouteName(), [
                                'show_style',
                                'show_theme',
                                'show_keyword',
                                'create_keyword',
                                'edit_keyword',
                                'show_search_tag',
                                'new_search_tags.index',
                                'show_lang',
                                'sizes.index',
                                'sizes.create',
                                'sizes.edit',
                                'colors.index',
                                'religions.index',
                                'show_interest',
                            ])
                                ? 'show'
                                : '' }}">
                            <a href="javascript:;" class="dropdown-toggle">
                                <span class="micon">
                                    <img src="{{ asset('assets/vendors/images/menu_icon/Filter.svg') }}"
                                         alt="">
                                </span>
                                <span class="mtext">Filters</span>
                            </a>
                            <ul class="submenu"
                                style="display:{{ in_array(Route::currentRouteName(), [
                                    'show_style',
                                    'show_theme',
                                    'show_keyword',
                                    'create_keyword',
                                    'edit_keyword',
                                    'show_search_tag',
                                    'new_search_tags.index',
                                    'show_lang',
                                    'sizes.index',
                                    'sizes.create',
                                    'sizes.edit',
                                    'colors.index',
                                    'religions.index',
                                    'show_interest',
                                ])
                                    ? 'block'
                                    : 'none' }};">
                                <li><a href="{{ route('show_style') }}"
                                       class="{{ Route::currentRouteName() == 'show_style' ? 'selectSubMenu' : '' }}">Style</a>
                                </li>
                                <li><a href="{{ route('show_theme') }}"
                                       class="{{ Route::currentRouteName() == 'show_theme' ? 'selectSubMenu' : '' }}">Theme</a>
                                </li>
                                <li><a href="{{ route('show_keyword') }}"
                                       class="{{ in_array(Route::currentRouteName(), ['show_keyword', 'create_keyword', 'edit_keyword']) ? 'selectSubMenu' : '' }}">Special
                                        Keywords</a></li>
                                <li><a href="{{ route('show_search_tag') }}"
                                       class="{{ Route::currentRouteName() == 'show_search_tag' ? 'selectSubMenu' : '' }}">Search
                                        Tags</a></li>
                                <li><a href="{{ route('new_search_tags.index') }}"
                                       class="{{ Route::currentRouteName() == 'new_search_tags.index' ? 'selectSubMenu' : '' }}">Sub
                                        Category Tags</a></li>
                                <li><a href="{{ route('show_interest') }}"
                                       class="{{ Route::currentRouteName() == 'show_interest' ? 'selectSubMenu' : '' }}">Interest</a>
                                </li>
                                <li><a href="{{ route('show_lang') }}"
                                       class="{{ Route::currentRouteName() == 'show_lang' ? 'selectSubMenu' : '' }}">Languages</a>
                                </li>
                                <li><a href="{{ route('sizes.index') }}"
                                       class="{{ in_array(Route::currentRouteName(), ['sizes.index', 'sizes.create', 'sizes.edit']) ? 'selectSubMenu' : '' }}">Size</a>
                                </li>
                                <li><a href="{{ route('religions.index') }}"
                                       class="{{ Route::currentRouteName() == 'religions.index' ? 'selectSubMenu' : '' }}">Religion</a>
                                </li>
                            </ul>
                        </li>

                        {{-- 🔹 Categories --}}
                        <li
                                class="dropdown {{ in_array(Route::currentRouteName(), [
                                'show_cat',
                                'create_cat',
                                'edit_cat',
                                'show_new_cat',
                                'create_new_cat',
                                'edit_new_cat',
                                'show_virtual_cat',
                                'create_virtual_cat',
                                'edit_virtual_cat',
                            ])
                                ? 'show'
                                : '' }}">
                            <a href="javascript:;" class="dropdown-toggle">
                                <span class="micon">
                                    <img src="{{ asset('assets/vendors/images/menu_icon/Filter.svg') }}"
                                         alt="">
                                </span>
                                <span class="mtext">Categories</span>
                            </a>
                            <ul class="submenu"
                                style="display:{{ in_array(Route::currentRouteName(), [
                                    'show_cat',
                                    'create_cat',
                                    'edit_cat',
                                    'show_new_cat',
                                    'create_new_cat',
                                    'edit_new_cat',
                                    'show_virtual_cat',
                                    'create_virtual_cat',
                                    'edit_virtual_cat',
                                ])
                                    ? 'block'
                                    : 'none' }};">
                                <li><a href="{{ route('show_cat') }}"
                                       class="{{ in_array(Route::currentRouteName(), ['show_cat', 'create_cat', 'edit_cat']) ? 'selectSubMenu' : '' }}">Old
                                        Categories</a></li>
                                <li><a href="{{ route('show_new_cat') }}"
                                       class="{{ in_array(Route::currentRouteName(), ['show_new_cat', 'create_new_cat', 'edit_new_cat']) ? 'selectSubMenu' : '' }}">New
                                        Categories</a></li>
                                <li><a href="{{ route('show_virtual_cat') }}"
                                       class="{{ in_array(Route::currentRouteName(), ['show_virtual_cat', 'create_virtual_cat', 'edit_virtual_cat']) ? 'selectSubMenu' : '' }}">Virtual
                                        Categories</a></li>
                            </ul>
                        </li>

                        {{-- 🔹 Special Page --}}
                        <li>
                            <a href="{{ route('special_page.index') }}"
                               class="{{ in_array(Route::currentRouteName(), ['special_page', 'create_pages']) ? 'selectSubMenu' : '' }}">
                                <span class="mtext">Special Page</span>
                            </a>
                        </li>

                        {{-- 🔹 Page Reviews --}}
                        @if ($roleManager::onlySeoAccess(Auth::user()->user_type) || $roleManager::isAdmin(Auth::user()->user_type))
                        <li>
                            <a href="{{ route('p_reviews.index') }}"
                               class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'p_reviews.index' ? 'selectSubMenu' : '' }}">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/Review.svg') }}"
                                                alt=""></span>
                                <span class="mtext">Page Reviews</span>
                            </a>
                        </li>

                        {{-- 🔹 Reviews --}}
                        <li>
                            <a href="{{ route('reviews.index') }}"
                               class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'reviews.index' ? 'selectSubMenu' : '' }}">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/Review.svg') }}"
                                                alt=""></span>
                                <span class="mtext">Reviews</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>


                <li
                        class="dropdown {{ in_array(Route::currentRouteName(), ['show_item', 'create_item', 'edit_item']) ? 'show' : '' }}">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon">
                            <img src="{{ asset('assets/vendors/images/menu_icon/Template.svg') }}" alt="">
                        </span>
                        <span class="mtext">Templates</span>
                    </a>

                    <ul class="submenu"
                        style="display:{{ in_array(Route::currentRouteName(), ['show_item', 'create_item', 'edit_item']) ? 'block' : 'none' }};">

                        <li>
                            <a href="{{ route('show_item') }}"
                               class="{{ in_array(Route::currentRouteName(), ['show_item', 'create_item', 'edit_item']) ? 'selectSubMenu' : '' }}">
                                Items
                            </a>
                        </li>

                    </ul>
                </li>

                <li
                        class="dropdown {{ in_array(Route::currentRouteName(), ['show_cari_cat', 'create_cari_cat', 'edit_cari_cat', 'show_attire_item', 'create_attire', 'edit_seo_attire']) ? 'show' : '' }}">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon">
                            <img src="{{ asset('assets/vendors/images/menu_icon/Template.svg') }}" alt="">
                        </span>
                        <span class="mtext">Caricature</span>
                    </a>

                    <ul class="submenu"
                        style="display:{{ in_array(Route::currentRouteName(), ['show_item', 'create_item', 'edit_item']) ? 'block' : 'none' }};">

                        <li>
                            <a href="{{ route('show_cari_cat') }}"
                               class="{{ in_array(Route::currentRouteName(), ['show_cari_cat', 'create_cari_cat', 'edit_cari_cat']) ? 'selectSubMenu' : '' }}">
                                Category
                            </a>
                        </li>

                    </ul>
                    <ul class="submenu"
                        style="display:{{ in_array(Route::currentRouteName(), ['show_item', 'create_item', 'edit_item']) ? 'block' : 'none' }};">

                        <li>
                            <a href="{{ route('show_attire_item') }}"
                               class="{{ in_array(Route::currentRouteName(), ['show_attire_item', 'create_attire', 'edit_seo_attire']) ? 'selectSubMenu' : '' }}">
                                Attire
                            </a>
                        </li>

                    </ul>
                </li>

                @if (!$roleManager::isSeoExecutiveOrIntern(Auth::user()->user_type))
                    <li
                        class="dropdown {{ in_array(Route::currentRouteName(), [
                            'frame_categories.index',
                            'frame_items.index',
                            'show_sticker_cat.index',
                            'sticker_item.index',
                            'vector_categories.index',
                            'vector_items.index',
                            'audio_cat.index',
                            'audio_items.index',
                            'show_bg_cat.index',
                            'show_bg_item.index',
                            'video_cat.index',
                            'video_item.index',
                            'gif_categories.index',
                            'gif_items.index',
                            'raw_datas.index',
                            'import_json',
                            'show_fonts',
                            'create_font',
                            'edit_font',
                            'font_families',
                            'font_list',
                        ])
                            ? 'show'
                            : '' }}">
                    <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon"><img
                                        src="{{ asset('assets/vendors/images/menu_icon/Elements.svg') }}"
                                        alt=""></span>
                        <span class="mtext">Designer</span>
                    </a>
                    <ul class="submenu"
                        style="display: {{ in_array(Route::currentRouteName(), [
                                'frame_categories.index',
                                'frame_items.index',
                                'show_sticker_cat.index',
                                'sticker_item.index',
                                'vector_categories.index',
                                'vector_items.index',
                                'audio_cat.index',
                                'audio_items.index',
                                'show_bg_cat.index',
                                'show_bg_item.index',
                                'video_cat.index',
                                'video_item.index',
                                'gif_categories.index',
                                'gif_items.index',
                                'raw_datas.index',
                                'import_json',
                                'show_fonts',
                                'create_font',
                                'edit_font',
                                'font_families',
                                'font_list',
                            ])
                                ? 'block'
                                : 'none' }};">

                        {{-- Templates Section --}}
                        <li
                                class="dropdown {{ in_array(Route::currentRouteName(), [
                                    'frame_categories.index',
                                    'frame_items.index',
                                    'show_sticker_cat.index',
                                    'sticker_item.index',
                                    'vector_categories.index',
                                    'vector_items.index',
                                    'audio_cat.index',
                                    'audio_items.index',
                                    'show_bg_cat.index',
                                    'show_bg_item.index',
                                    'video_cat.index',
                                    'video_item.index',
                                    'gif_categories.index',
                                    'gif_items.index',
                                ])
                                    ? 'show'
                                    : '' }}">
                            <a href="javascript:;" class="dropdown-toggle">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/Template.svg') }}"
                                                alt=""></span>
                                <span class="mtext">Element</span>
                            </a>
                            <ul class="submenu child">
                                {{-- Frame --}}
                                <li
                                        class="dropdown {{ in_array(Route::currentRouteName(), ['frame_categories.index', 'frame_items.index']) ? 'show' : '' }}">
                                    <a href="javascript:;" class="dropdown-toggle">
                                            <span class="micon"><img
                                                        src="{{ asset('assets/vendors/images/menu_icon/Shape.svg') }}"
                                                        alt=""></span>
                                        <span class="mtext">Frame</span>
                                    </a>
                                    <ul class="submenu child">
                                        <li><a href="{{ route('frame_categories.index') }}"
                                               class="{{ Route::currentRouteName() == 'frame_categories.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Category.svg') }}"
                                                                alt=""></span>
                                                Category</a></li>
                                        <li><a href="{{ route('frame_items.index') }}"
                                               class="{{ Route::currentRouteName() == 'frame_items.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Item.svg') }}"
                                                                alt=""></span>
                                                Item</a></li>
                                    </ul>
                                </li>

                                {{-- Sticker --}}
                                <li
                                        class="dropdown {{ in_array(Route::currentRouteName(), ['show_sticker_cat.index', 'sticker_item.index']) ? 'show' : '' }}">
                                    <a href="javascript:;" class="dropdown-toggle">
                                            <span class="micon"><img
                                                        src="{{ asset('assets/vendors/images/menu_icon/Sticker.svg') }}"
                                                        alt=""></span>
                                        <span class="mtext">Sticker</span>
                                    </a>
                                    <ul class="submenu child">
                                        <li><a href="{{ route('show_sticker_cat.index') }}"
                                               class="{{ Route::currentRouteName() == 'show_sticker_cat.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Category.svg') }}"
                                                                alt=""></span>
                                                Category</a></li>
                                        <li><a href="{{ route('sticker_item.index') }}"
                                               class="{{ Route::currentRouteName() == 'sticker_item.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Item.svg') }}"
                                                                alt=""></span>
                                                Item</a></li>
                                    </ul>
                                </li>

                                {{-- Vector --}}
                                <li
                                        class="dropdown {{ in_array(Route::currentRouteName(), ['vector_categories.index', 'vector_items.index']) ? 'show' : '' }}">
                                    <a href="javascript:;" class="dropdown-toggle">
                                            <span class="micon"><img
                                                        src="{{ asset('assets/vendors/images/menu_icon/Shape.svg') }}"
                                                        alt=""></span>
                                        <span class="mtext">Vector</span>
                                    </a>
                                    <ul class="submenu child">
                                        <li><a href="{{ route('vector_categories.index') }}"
                                               class="{{ Route::currentRouteName() == 'vector_categories.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Category.svg') }}"
                                                                alt=""></span>
                                                Category</a></li>
                                        <li><a href="{{ route('vector_items.index') }}"
                                               class="{{ Route::currentRouteName() == 'vector_items.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Item.svg') }}"
                                                                alt=""></span>
                                                Item</a></li>
                                    </ul>
                                </li>

                                {{-- Audio --}}
                                <li
                                        class="dropdown {{ in_array(Route::currentRouteName(), ['audio_cat.index', 'audio_items.index']) ? 'show' : '' }}">
                                    <a href="javascript:;" class="dropdown-toggle">
                                            <span class="micon"><img
                                                        src="{{ asset('assets/vendors/images/menu_icon/Shape.svg') }}"
                                                        alt=""></span>
                                        <span class="mtext">Audio</span>
                                    </a>
                                    <ul class="submenu child">
                                        <li><a href="{{ route('audio_cat.index') }}"
                                               class="{{ Route::currentRouteName() == 'audio_cat.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Category.svg') }}"
                                                                alt=""></span>
                                                Category</a></li>
                                        <li><a href="{{ route('audio_items.index') }}"
                                               class="{{ Route::currentRouteName() == 'audio_items.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Item.svg') }}"
                                                                alt=""></span>
                                                Item</a></li>
                                    </ul>
                                </li>

                                {{-- Background --}}
                                <li
                                        class="dropdown {{ in_array(Route::currentRouteName(), ['show_bg_cat.index', 'show_bg_item.index']) ? 'show' : '' }}">
                                    <a href="javascript:;" class="dropdown-toggle">
                                            <span class="micon"><img
                                                        src="{{ asset('assets/vendors/images/menu_icon/Background.svg') }}"
                                                        alt=""></span>
                                        <span class="mtext">Background</span>
                                    </a>
                                    <ul class="submenu child">
                                        <li><a href="{{ route('show_bg_cat.index') }}"
                                               class="{{ Route::currentRouteName() == 'show_bg_cat.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Category.svg') }}"
                                                                alt=""></span>
                                                Category</a></li>
                                        <li><a href="{{ route('show_bg_item.index') }}"
                                               class="{{ Route::currentRouteName() == 'show_bg_item.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Item.svg') }}"
                                                                alt=""></span>
                                                Item</a></li>
                                    </ul>
                                </li>

                                {{-- Gif --}}
                                <li
                                        class="dropdown {{ in_array(Route::currentRouteName(), ['gif_categories.index', 'gif_items.index']) ? 'show' : '' }}">
                                    <a href="javascript:;" class="dropdown-toggle">
                                            <span class="micon"><img
                                                        src="{{ asset('assets/vendors/images/menu_icon/Shape.svg') }}"
                                                        alt=""></span>
                                        <span class="mtext">Gif</span>
                                    </a>
                                    <ul class="submenu child">
                                        <li><a href="{{ route('gif_categories.index') }}"
                                               class="{{ Route::currentRouteName() == 'gif_categories.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Category.svg') }}"
                                                                alt=""></span>
                                                Category</a></li>
                                        <li><a href="{{ route('gif_items.index') }}"
                                               class="{{ Route::currentRouteName() == 'gif_items.index' ? 'selectSubMenu' : '' }}">
                                                    <span class="micon"><img
                                                                src="{{ asset('assets/vendors/images/menu_icon/Item.svg') }}"
                                                                alt=""></span>
                                                Item</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a href="{{ route('raw_datas.index') }}"
                               class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'raw_datas.index' ? 'selectSubMenu' : '' }}">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/RawData.svg') }}"
                                                alt=""></span>
                                <span class="mtext">Raw Datas</span>
                            </a>
                        </li>

                        {{--
                        <li>
                            <a href="{{ route('import_json') }}"
                               class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'import_json' ? 'selectSubMenu' : '' }}">
                                <span class="micon"><img
                                            src="{{ asset('assets/vendors/images/menu_icon/JsonImport.svg') }}"
                                            alt=""></span>
                                <span class="mtext">Import Json</span>
                            </a>
                        </li>
                        --}}

                        <li
                                class="dropdown {{ Route::currentRouteName() == 'show_fonts' || Route::currentRouteName() == 'font_families' || Route::currentRouteName() == 'font_list' || Route::currentRouteName() == 'create_font' || Route::currentRouteName() == 'edit_font' ? 'show' : '' }}">
                            <a href="javascript:;" class="dropdown-toggle">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/Fonts.svg') }}"
                                                alt=""></span><span class="mtext">Fonts</span>
                            </a>
                            <ul class="submenu"
                                style="display: {{ Route::currentRouteName() == 'show_fonts' || Route::currentRouteName() == 'font_families' || Route::currentRouteName() == 'font_list' || Route::currentRouteName() == 'create_font' || Route::currentRouteName() == 'edit_font' ? 'block' : 'none' }};">
                                <li><a href="{{ route('show_fonts') }}"
                                       class="{{ Route::currentRouteName() == 'show_fonts' || Route::currentRouteName() == 'create_font' || Route::currentRouteName() == 'edit_font' ? 'selectSubMenu' : '' }}">Mobile
                                        Fonts</a></li>
                                <li>
                                    <a href="{{ route('font_families') }}"
                                       class="{{ Route::currentRouteName() == 'font_families' ? 'selectSubMenu' : '' }}">Web
                                        Font Familes</a>
                                </li>
                                <li><a href="{{ route('font_list') }}"
                                       class="{{ Route::currentRouteName() == 'font_list' ? 'selectSubMenu' : '' }}">Web
                                        Font List</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                @endif
                @endif

                {{-- SEO Executive and SEO Manager Video Access --}}
                @if (!$roleManager::isSalesManager(Auth::user()->user_type) && !$roleManager::isSalesEmployee(Auth::user()->user_type))
                <li class="dropdown {{ in_array(Route::currentRouteName(), [
        'show_v_cat',
        'show_v_item',
        'create_v_cat',
        'edit_v_cat',
        'edit_seo_v_item',
        'video_reviews.index',
        'video_page_reviews.index',
        'show_video_virtual_cat',
        'create_video_virtual_cat',
        'edit_video_virtual_cat',
        'show_video_style',
        'show_video_theme',
        'show_video_search_tag',
        'show_video_interest',
        'show_video_lang',
        'video_religions.index',
        'video_sizes.index',
        'video_sizes.create',
        'video_sizes.edit',
    ])
        ? 'show'
        : '' }}">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon"><img src="{{ asset('assets/vendors/images/menu_icon/Video.svg') }}"
                                alt=""></span>
                        <span class="mtext">Video</span>
                    </a>
                    <ul class="submenu" style="display: {{ in_array(Route::currentRouteName(), [
        'show_v_cat',
        'show_v_item',
        'create_v_cat',
        'edit_v_cat',
        'edit_seo_v_item',
        'video_reviews.index',
        'video_page_reviews.index',
        'show_video_virtual_cat',
        'create_video_virtual_cat',
        'edit_video_virtual_cat',
        'show_video_style',
        'show_video_theme',
        'show_video_search_tag',
        'show_video_interest',
        'show_video_lang',
        'video_religions.index',
        'video_sizes.index',
        'video_sizes.create',
        'video_sizes.edit',
    ])
        ? 'block'
        : 'none' }};">
                        <li><a href="{{ route('show_v_cat') }}"
                                class="{{ Route::currentRouteName() == 'show_v_cat' || Route::currentRouteName() == 'create_v_cat' || Route::currentRouteName() == 'edit_v_cat' ? 'selectSubMenu' : '' }}">Categories</a>
                        </li>
                        <li><a href="{{ route('show_video_virtual_cat') }}"
                                class="{{ Route::currentRouteName() == 'show_video_virtual_cat' || Route::currentRouteName() == 'create_video_virtual_cat' || Route::currentRouteName() == 'edit_video_virtual_cat' ? 'selectSubMenu' : '' }}">Virtual
                                Categories</a>
                        </li>
                        <li><a href="{{ route('show_v_item') }}"
                                class="{{ Route::currentRouteName() == 'show_v_item' || Route::currentRouteName() == 'edit_seo_v_item' ? 'selectSubMenu' : '' }}">Templates</a>
                        </li>

                        {{-- 🔹 Video Filters --}}
                        <li class="dropdown {{ in_array(Route::currentRouteName(), [
        'show_video_style',
        'show_video_theme',
        'show_video_search_tag',
        'show_video_interest',
        'show_video_lang',
        'video_religions.index',
        'video_sizes.index',
        'video_sizes.create',
        'video_sizes.edit',
    ])
        ? 'show'
        : '' }}">
                            <a href="javascript:;" class="dropdown-toggle">
                                <span class="micon">
                                    <img src="{{ asset('assets/vendors/images/menu_icon/Filter.svg') }}" alt="">
                                </span>
                                <span class="mtext">Filters</span>
                            </a>
                            <ul class="submenu" style="display:{{ in_array(Route::currentRouteName(), [
        'show_video_style',
        'show_video_theme',
        'show_video_search_tag',
        'show_video_interest',
        'show_video_lang',
        'video_religions.index',
        'video_sizes.index',
        'video_sizes.create',
        'video_sizes.edit',
    ])
        ? 'block'
        : 'none' }};">
                                {{-- <li><a href="{{ route('show_video_style') }}"
                                        class="{{ Route::currentRouteName() == 'show_video_style' ? 'selectSubMenu' : '' }}">Style</a>
                                </li> --}}
                                <li><a href="{{ route('show_video_theme') }}"
                                        class="{{ Route::currentRouteName() == 'show_video_theme' ? 'selectSubMenu' : '' }}">Theme</a>
                                </li>
                                <li><a href="{{ route('show_video_search_tag') }}"
                                        class="{{ Route::currentRouteName() == 'show_video_search_tag' ? 'selectSubMenu' : '' }}">Search
                                        Tags</a>
                                </li>
                                {{-- <li><a href="{{ route('show_video_interest') }}"
                                        class="{{ Route::currentRouteName() == 'show_video_interest' ? 'selectSubMenu' : '' }}">Interest</a>
                                </li> --}}
                                <li><a href="{{ route('show_video_lang') }}"
                                        class="{{ Route::currentRouteName() == 'show_video_lang' ? 'selectSubMenu' : '' }}">Languages</a>
                                </li>
                                <li><a href="{{ route('video_religions.index') }}"
                                        class="{{ Route::currentRouteName() == 'video_religions.index' ? 'selectSubMenu' : '' }}">Religion</a>
                                </li>
                                <li><a href="{{ route('video_sizes.index') }}"
                                        class="{{ in_array(Route::currentRouteName(), ['video_sizes.index', 'video_sizes.create', 'video_sizes.edit']) ? 'selectSubMenu' : '' }}">Size</a>
                                </li>
                            </ul>
                        </li>

                        @if ($roleManager::isAdmin(Auth::user()->user_type) || $roleManager::isSeoManager(Auth::user()->user_type) || $roleManager::isManager(Auth::user()->user_type))
                            <li><a href="{{ route('video_page_reviews.index') }}"
                                    class="{{ Route::currentRouteName() == 'video_page_reviews.index' ? 'selectSubMenu' : '' }}">Page
                                    Review</a>
                            </li>
                            <li><a href="{{ route('video_reviews.index') }}"
                                    class="{{ Route::currentRouteName() == 'video_reviews.index' ? 'selectSubMenu' : '' }}">Reviews</a>
                            </li>
                        @endif
                    </ul>
                </li>
@endif

                @if ($roleManager::isAdmin(Auth::user()->user_type))
                <li>
                    <a href="{{ route('templateRate.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'templateRate.index' ? 'selectSubMenu' : '' }}">
                            <span class="micon"><img
                                        src="{{ asset('assets/vendors/images/menu_icon/Review.svg') }}"
                                        alt=""></span><span class="mtext">Template Rate</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('caricatureRate.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'caricatureRate.index' ? 'selectSubMenu' : '' }}">
                            <span class="micon"><img
                                        src="{{ asset('assets/vendors/images/menu_icon/Review.svg') }}"
                                        alt=""></span><span class="mtext">Caricature Rate</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('promocode.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'promocode.index' ? 'selectSubMenu' : '' }}">
                        <span class="micon bi bi-calendar4-week"></span><span class="mtext">Promo Code</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('offer-popup.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'offer-popup.index' ? 'selectSubMenu' : '' }}">
                        <span class="micon bi bi-calendar4-week"></span><span class="mtext">Offer PopUp </span>
                    </a>
                </li>

                {{-- Designer System Menu --}}
                                <li class="dropdown {{ in_array(Route::currentRouteName(), [
                        'designer_system.applications',
                        'designer_system.designers',
                        'designer_system.design_submissions',
                        'designer_system.types',
                        'designer_system.goals',
                    ]) ? 'show' : '' }}">
                                    <a href="javascript:;" class="dropdown-toggle">
                                        <span class="micon">
                                            <img src="{{ asset('assets/vendors/images/menu_icon/Users.svg') }}" alt="">
                                        </span>
                                        <span class="mtext">Designer System</span>
                                    </a>
                                    <ul class="submenu" style="display:{{ in_array(Route::currentRouteName(), [
                        'designer_system.applications',
                        'designer_system.designers',
                        'designer_system.design_submissions',
                        'designer_system.types',
                        'designer_system.goals',
                    ]) ? 'block' : 'none' }};">

                                        {{-- Designer Applications --}}
                                        <li>
                                            <a href="{{ route('designer_system.applications') }}"
                                                class="{{ Route::currentRouteName() == 'designer_system.applications' ? 'selectSubMenu' : '' }}">
                                                <i class="fa fa-file-text"></i> Applications
                                            </a>
                                        </li>

                                        {{-- Approved Designers --}}
                                        <li>
                                            <a href="{{ route('designer_system.designers') }}"
                                                class="{{ Route::currentRouteName() == 'designer_system.designers' ? 'selectSubMenu' : '' }}">
                                                <i class="fa fa-users"></i> Designers
                                            </a>
                                        </li>

                                        {{-- Design Submissions (Designer Head) --}}
                                        @if ($roleManager::isAdmin(Auth::user()->user_type) || $roleManager::isDesignerManager(Auth::user()->user_type))
                                            <li>
                                                <a href="{{ route('designer_system.design_submissions') }}"
                                                    class="{{ Route::currentRouteName() == 'designer_system.design_submissions' ? 'selectSubMenu' : '' }}">
                                                    <i class="fa fa-paint-brush"></i> Design Submissions
                                                </a>
                                            </li>
                                        @endif

                                        {{-- System Settings Dropdown --}}
                                        <li class="dropdown {{ in_array(Route::currentRouteName(), [
                        'designer_system.types',
                        'designer_system.goals',
                    ]) ? 'show' : '' }}">
                                            <a href="javascript:;" class="dropdown-toggle">
                                                <i class="fa fa-cog"></i> System Settings
                                            </a>
                                            <ul class="submenu" style="display:{{ in_array(Route::currentRouteName(), [
                        'designer_system.types',
                        'designer_system.goals',
                    ]) ? 'block' : 'none' }};">
                                                <li>
                                                    <a href="{{ route('designer_system.types') }}"
                                                        class="{{ Route::currentRouteName() == 'designer_system.types' ? 'selectSubMenu' : '' }}">
                                                        <i class="fa fa-tags"></i> Designer Types
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('designer_system.goals') }}"
                                                        class="{{ Route::currentRouteName() == 'designer_system.goals' ? 'selectSubMenu' : '' }}">
                                                        <i class="fa fa-bullseye"></i> Goals
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>


                <li>
                    <a href="{{ route('ai_credits.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'ai_credits.index' ? 'selectSubMenu' : '' }}">
                        <span class="micon bi bi-calendar4-week"></span><span class="mtext">Ai Credit</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('payment_configuration.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'payment_configuration.index' ? 'selectSubMenu' : '' }}">
                        <span class="micon bi bi-calendar4-week"></span><span class="mtext">Paymeny Configuration</span>
                    </a>
                </li>

                <!--  -->


                <li
                        class="dropdown {{ in_array(Route::currentRouteName(), ['bonus-package.index', 'offer-package.index']) ? 'show' : '' }}">
                    <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon">
                                <img src="{{ asset('assets/vendors/images/menu_icon/Template.svg') }}"
                                     alt="">
                            </span>
                        <span class="mtext">Bonus & Offer</span>
                    </a>

                    <ul class="submenu"
                        style="display:{{ in_array(Route::currentRouteName(), ['bonus-package.index', 'offer-package.index']) ? 'block' : 'none' }};">

                        <li>
                            <a href="{{ route('bonus-package.index') }}"
                               class="{{ in_array(Route::currentRouteName(), ['bonus-package.index']) ? 'selectSubMenu' : '' }}">
                                Bonus Package
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('offer-package.index') }}"
                               class="{{ in_array(Route::currentRouteName(), ['offer-package.index']) ? 'selectSubMenu' : '' }}">
                                Offer Package
                            </a>
                        </li>

                    </ul>
                </li>

                <li
                        class="dropdown {{ Route::currentRouteName() == 'show_packages' || Route::currentRouteName() == 'payment_setting' || Route::currentRouteName() == 'transcation_logs' || Route::currentRouteName() == 'template_transcation_logs' || Route::currentRouteName() == 'video_transcation_logs' ? 'show' : '' }}">
                    <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon"><img
                                        src="{{ asset('assets/vendors/images/menu_icon/SubscribeOld.svg') }}"
                                        alt=""></span><span class="mtext">Old Plan Subscription</span>
                    </a>
                    <ul class="submenu"
                        style="display:{{ Route::currentRouteName() == 'show_packages' || Route::currentRouteName() == 'payment_setting' || Route::currentRouteName() == 'transcation_logs' || Route::currentRouteName() == 'template_transcation_logs' || Route::currentRouteName() == 'video_transcation_logs' ? 'block' : 'none' }};">
                        <li><a href="{{ route('show_packages') }}"
                               class="{{ Route::currentRouteName() == 'show_packages' ? 'selectSubMenu' : '' }}">Subscription
                                Package</a></li>
                        <li><a href="{{ route('payment_setting') }}"
                               class="{{ Route::currentRouteName() == 'payment_setting' ? 'selectSubMenu' : '' }}">Payment
                                Setting</a></li>
                        <li><a href="{{ route('transcation_logs') }}"
                               class="{{ Route::currentRouteName() == 'transcation_logs' ? 'selectSubMenu' : '' }}">Subscription
                                Transaction Logs</a></li>
                        <li><a href="{{ route('template_transcation_logs') }}"
                               class="{{ Route::currentRouteName() == 'template_transcation_logs' ? 'selectSubMenu' : '' }}">Template
                                Transaction Logs</a></li>
                        <li><a href="{{ route('video_transcation_logs') }}"
                               class="{{ Route::currentRouteName() == 'video_transcation_logs' ? 'selectSubMenu' : '' }}">Video
                                Transaction Logs</a></li>
                    </ul>
                </li>

                <li
                        class="dropdown
                                {{ in_array(Route::currentRouteName(), [
                                    'categoryFeatures.index',
                                    'features.index',
                                    'planduration.index',
                                    'plans.index',
                                    'plans.create',
                                    'plans.edit',
                                ])
                                    ? 'show'
                                    : '' }}">
                    <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon">
                                <img src="{{ asset('assets/vendors/images/menu_icon/SubscribeNew1.svg') }}"
                                     alt="">
                            </span>
                        <span class="mtext">New Plan Subscription</span>
                    </a>

                    <ul class="submenu child"
                        style="display: {{ in_array(Route::currentRouteName(), [
                                'categoryFeatures.index',
                                'features.index',
                                'planduration.index',
                                'plans.index',
                                'plans.create',
                                'plans.edit',
                            ])
                                ? 'block'
                                : 'none' }}">

                        <li>
                            <a href="{{ route('categoryFeatures.index') }}"
                               class="{{ Route::currentRouteName() == 'categoryFeatures.index' ? 'selectSubMenu' : '' }}">
                                Category Feature
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('features.index') }}"
                               class="{{ Route::currentRouteName() == 'features.index' ? 'selectSubMenu' : '' }}">
                                Plan Feature
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('planduration.index') }}"
                               class="{{ Route::currentRouteName() == 'planduration.index' ? 'selectSubMenu' : '' }}">
                                Additional User Discount & <br> Plan Duration
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('plans.index') }}"
                               class="{{ in_array(Route::currentRouteName(), ['plans.index', 'plans.create', 'plans.edit']) ? 'selectSubMenu' : '' }}">
                                Plans
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                @if ($roleManager::isAdmin(Auth::user()->user_type))

                    <li class="dropdown {{ in_array(Route::currentRouteName(), [
                    'create_email_template',
                    'whatsapp_template.index',
                    'campaign.index',
                    'campaign.report',
                    'automation_config.index',
                    'automation_report.index'
                ]) ? 'show' : '' }}">

                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon">
                            <img src="{{ asset('assets/vendors/images/menu_icon/SubscribeNew1.svg') }}" alt="">
                        </span>
                        <span class="mtext">Campaign</span>
                    </a>

                    <ul class="submenu"
                        style="display: {{ in_array(Route::currentRouteName(), [
                            'create_email_template',
                            'whatsapp_template.index',
                            'campaign.index',
                            'campaign.report',
                            'automation_config.index',
                            'automation_report.index'
                        ]) ? 'block' : 'none' }};">
                        <!-- Email Template -->
                        <li>
                            <a href="{{ route('create_email_template') }}"
                               class="{{ Route::currentRouteName() == 'create_email_template' ? 'selectSubMenu' : '' }}">
                                Email Template
                            </a>
                        </li>

                        <!-- WhatsApp Template -->
                        <li>
                            <a href="{{ route('whatsapp_template.index') }}"
                               class="{{ Route::currentRouteName() == 'whatsapp_template.index' ? 'selectSubMenu' : '' }}">
                                WhatsApp Template
                            </a>
                        </li>


                        <!-- Campaign (with 2 sub-items) -->
                        <li class="dropdown {{ in_array(Route::currentRouteName(), ['campaign.index', 'campaign.report']) ? 'show' : '' }}">
                            <a href="javascript:;" class="dropdown-toggle">Campaign</a>
                            <ul class="submenu" style="display: {{ in_array(Route::currentRouteName(), ['campaign.index', 'campaign.report']) ? 'block' : 'none' }};">

                                <li>
                                    <a href="{{ route('campaign.index') }}"
                                       class="{{ Route::currentRouteName() == 'campaign.index' ? 'selectSubMenu' : '' }}">
                                        Campaign Config
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('campaign.report') }}"
                                       class="{{ Route::currentRouteName() == 'campaign.report' ? 'selectSubMenu' : '' }}">
                                        Campaign Report
                                    </a>
                                </li>

                            </ul>
                        </li>


                        <!-- Automation (with 2 sub-items) -->
                        <li class="dropdown {{ in_array(Route::currentRouteName(), ['automation_config.index', 'automation_report.index']) ? 'show' : '' }}">
                            <a href="javascript:;" class="dropdown-toggle">Automation</a>

                            <ul class="submenu"
                                style="display: {{ in_array(Route::currentRouteName(), ['automation_config.index', 'automation_report.index']) ? 'block' : 'none' }};">

                                <li>
                                    <a href="{{ route('automation_config.index') }}"
                                       class="{{ Route::currentRouteName() == 'automation_config.index' ? 'selectSubMenu' : '' }}">
                                        Automation Config
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('automation_report.index') }}"
                                       class="{{ Route::currentRouteName() == 'automation_report.index' ? 'selectSubMenu' : '' }}">
                                        Automation Report
                                    </a>
                                </li>

                            </ul>
                        </li>

                    </ul>
                </li>
                @endif


                @if ($roleManager::salesHierarchyAccess(Auth::user()->user_type))
                <li>
                        <a href="{{ route('unified_leads.index') }}"
                            class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'unified_leads.index' ? 'selectSubMenu' : '' }}">
                            <span class="micon"><img src="{{ asset('assets/vendors/images/menu_icon/Notifications.svg') }}"
                                    alt=""></span><span class="mtext">Unified Leads</span>
                        </a>
                    </li>
                <li>
                    <a href="{{ route('order_user.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'order_user.index' ? 'selectSubMenu' : '' }}">
                                <span class="micon"><img
                                            src="{{ asset('assets/vendors/images/menu_icon/Notifications.svg') }}"
                                            alt=""></span><span class="mtext">Drop User</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('recent_expire.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'recent_expire.index' ? 'selectSubMenu' : '' }}">
                                <span class="micon"><img
                                            src="{{ asset('assets/vendors/images/menu_icon/Notifications.svg') }}"
                                            alt=""></span><span class="mtext">Recent Expire</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('recent_expire.new_index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'recent_expire.new_index' ? 'selectSubMenu' : '' }}">
                                <span class="micon"><img
                                            src="{{ asset('assets/vendors/images/menu_icon/Notifications.svg') }}"
                                            alt=""></span><span class="mtext">New Recent Expire</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('wp_feedback.index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'wp_feedback.index' ? 'selectSubMenu' : '' }}">
                       <span class="micon"><img src="{{ asset('assets/vendors/images/menu_icon/Feedback.svg') }}"
                             alt=""></span><span class="mtext">Whatsapp Feedback</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('wp_feedback.new_index') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'wp_feedback.new_index' ? 'selectSubMenu' : '' }}">
                       <span class="micon"><img src="{{ asset('assets/vendors/images/menu_icon/Feedback.svg') }}"
                             alt=""></span><span class="mtext">New Whatsapp Feedback</span>
                    </a>
                </li>

                @if($roleManager::isAdminSalesManagerOrNisha(Auth::user()->id))
                    <li>
                        <a href="{{ route('custom_leads.index') }}"
                           class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'custom_leads.index' ? 'selectSubMenu' : '' }}">
                                <span class="micon"><img src="{{ asset('assets/vendors/images/menu_icon/Notifications.svg') }}"
                                                         alt=""></span><span class="mtext">Custom Leads</span>
                        </a>
                    </li>
                @endif

                    @if($roleManager::isAdminOrSalesManager(Auth::user()->user_type))
                        <li>
                            <a href="{{ route('show_messages') }}"
                               class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'show_messages' ? 'selectSubMenu' : '' }}">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/AppMessage.svg') }}"
                                                alt=""></span><span class="mtext">In App Message</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('show_feedbacks') }}"
                               class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'show_feedbacks' ? 'selectSubMenu' : '' }}">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/Feedback.svg') }}"
                                                alt=""></span><span class="mtext">Feedbacks</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('contact_us_web') }}"
                               class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'contact_us_web' ? 'selectSubMenu' : '' }}">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/Feedback.svg') }}"
                                                alt=""></span><span class="mtext">Contect Us Web</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('show_contacts') }}"
                               class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'show_contacts' ? 'selectSubMenu' : '' }}">
                                    <span class="micon"><img
                                                src="{{ asset('assets/vendors/images/menu_icon/Contacts.svg') }}"
                                                alt=""></span><span class="mtext">Contacts</span>
                            </a>
                        </li>
                    @endif
                @endif

                @if ($roleManager::isAdmin(Auth::user()->user_type))
                
                <li
                        class="dropdown {{ in_array(Route::currentRouteName(), ['vendor.withdrawals', 'vendor.withdrawal.show', 'vendor.wallet_settings', 'vendor.wallet_settings.create', 'vendor.wallet_settings.edit']) ? 'show' : '' }}">
                        <a href="javascript:;" class="dropdown-toggle">
                            <span class="micon"><i class="fa fa-money-bill-wave" aria-hidden="true"></i></span><span
                                class="mtext">Vendor Management</span>
                        </a>
                        <ul class="submenu"
                            style="display: {{ in_array(Route::currentRouteName(), ['vendor.withdrawals', 'vendor.withdrawal.show', 'referral.dashboard', 'vendor.wallet_settings', 'vendor.wallet_settings.create', 'vendor.wallet_settings.edit']) ? 'block' : 'none' }};">
                            <li>
                                <a href="{{ route('referral.dashboard') }}"
                                    class="{{ in_array(Route::currentRouteName(), ['referral.dashboard', 'referral.show']) ? 'selectSubMenu' : '' }}">
                                    <i class="fa fa-users"></i> Referral Users
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('vendor.withdrawals') }}"
                                    class="{{ in_array(Route::currentRouteName(), ['vendor.withdrawals', 'vendor.withdrawal.show']) ? 'selectSubMenu' : '' }}">
                                    <i class="fa fa-money-bill"></i> Withdrawal Requests
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('vendor.wallet_settings') }}"
                                    class="{{ in_array(Route::currentRouteName(), ['vendor.wallet_settings', 'vendor.wallet_settings.create', 'vendor.wallet_settings.edit']) ? 'selectSubMenu' : '' }}">
                                    <i class="fa fa-cog"></i> Wallet Settings
                                </a>
                            </li>
                        </ul>
                    </li>

                <li>
                    <a href="{{ route('show_users') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'show_users' || Route::currentRouteName() == 'manage_subscription.show' || Route::currentRouteName() == 'manage_template_product.show' || Route::currentRouteName() == 'manage_video_product.show' ? 'selectSubMenu' : '' }}">
                            <span class="micon"><img src="{{ asset('assets/vendors/images/menu_icon/Users.svg') }}"
                                                     alt=""></span><span class="mtext">Users</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('show_employee') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'show_employee' ? 'selectSubMenu' : '' }}">
                            <span class="micon"><img
                                        src="{{ asset('assets/vendors/images/menu_icon/Employee.svg') }}"
                                        alt=""></span><span class="mtext">Employees</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('notification_setting') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'notification_setting' ? 'selectSubMenu' : '' }}">
                            <span class="micon"><img
                                        src="{{ asset('assets/vendors/images/menu_icon/Notifications.svg') }}"
                                        alt=""></span><span class="mtext">Notification Setting</span>
                    </a>
                </li>
                @endif

                @if ($roleManager::onlySeoAccess(Auth::user()->user_type))
                <li>
                    <a href="{{ route('seo_error_list') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'data_list' ? 'selectSubMenu' : '' }}">
                            <span class="micon"><img
                                        src="{{ asset('assets/vendors/images/menu_icon/Contacts.svg') }}"
                                        alt=""></span><span class="mtext">Seo Audit</span>
                    </a>
                </li>
                @endif
                @if ($roleManager::isAdminOrSeoManager(Auth::user()->user_type) || $roleManager::isSeoExecutive(Auth::user()->user_type))
                <li>
                    <a href="{{ route('show_pending_task') }}"
                       class="dropdown-toggle no-arrow {{ Route::currentRouteName() == 'show_pending_task' ? 'selectSubMenu' : '' }}">
                            <span class="micon"><img
                                        src="{{ asset('assets/vendors/images/menu_icon/Contacts.svg') }}"
                                        alt=""></span><span class="mtext">Pending Task</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<div class="mobile-menu-overlay"></div>
<script>
    let roleKey = @json($roleManager::getUserType(Auth::user()->user_type));
    let isPreviewMode = @json($previewMode ?? false);
    let storageUrl = @json(config('filesystems.storage_url'));
    let STORAGE_URL = @json(env('STORAGE_URL'));
    let APP_BASE_URL = @json(env('APP_URL'));
</script>