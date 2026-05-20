{{-- @php
$classBold = (!empty($subcategory['subcategories']->toArray()) && count($subcategory['subcategories']->toArray()) > 0 && isset($subcategory['subcategories']->toArray()[0])) ? "has-children" : "has-parent";
$selected = (isset($dataArray['item']['new_category_id']) && $dataArray['item']['new_category_id'] == $sub_category_id) ? "selected" : "";
$selected = ( $selected == "" && isset($datas['cat']->parent_category_id ) && $datas['cat']->parent_category_id == $sub_category_id ) ? "selected" : $selected;
@endphp

<li class="subcategory {{$classBold}} {{$selected}}" data-pid="{{$category['id']}}"  data-id="{{ (isset($sub_category_id) && $sub_category_id != '') ? $sub_category_id : '' }}" data-catname="{{ (isset($sub_category_name) && $sub_category_name != '') ? $sub_category_name : ''}}"> <span>{{ $subcategory['category_name'] }}</span>
    @if (!empty($subcategory['subcategories']))
        <ul class="subcategories">
            @foreach ($subcategory['subcategories'] as $subsubcategory)
                @include('partials.subcategory-optgroup', ['subcategory' => $subsubcategory,'sub_category_id' => $subsubcategory['id'],'sub_category_name' => $subsubcategory['category_name']])
            @endforeach
        </ul>
    @endif
</li> --}}


@php
$classBold = (!empty($subcategory['subcategories']->toArray()) && count($subcategory['subcategories']->toArray()) > 0 && isset($subcategory['subcategories']->toArray()[0])) ? "has-children" : "has-parent";
$selected = (isset($dataArray['item']['new_category_id']) && in_array($sub_category_id, $dataArray['item']['new_category_id'])) ? "selected" : "";
$selected = ($selected == "" && isset($datas['cat']->parent_category_id) && $datas['cat']->parent_category_id == $sub_category_id) ? "selected" : $selected;
@endphp

<li class="subcategory {{$classBold}} {{$selected}}" data-pid="{{$category['id']}}" data-id="{{ (isset($sub_category_id) && $sub_category_id != '') ? $sub_category_id : '' }}" data-catname="{{ (isset($sub_category_name) && $sub_category_name != '') ? $sub_category_name : '' }}">
    <span>{{ $subcategory['category_name'] }}</span>
    @if (!empty($subcategory['subcategories']))
        <ul class="subcategories">
            @foreach ($subcategory['subcategories'] as $subsubcategory)
                @include('partials.subcategory-optgroup', ['subcategory' => $subsubcategory,'sub_category_id' => $subsubcategory['id'],'sub_category_name' => $subsubcategory['category_name']])
            @endforeach
        </ul>
    @endif
</li>
