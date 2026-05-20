@php
$classBold = (!empty($subcategory['subcategories']->toArray()) && count($subcategory['subcategories']->toArray()) > 0 && isset($subcategory['subcategories']->toArray()[0])) ? "has-children" : "has-parent";
$selected = (isset($frameCategory->parent_category_id) && $frameCategory->parent_category_id == $sub_category_id) ? "selected" : "";
$selected = (isset($frameCategory->frame_category_id) && $frameCategory->frame_category_id == $sub_category_id) ? "selected" : $selected;
@endphp

<li class="subcategory {{$classBold}} {{$selected}}" data-pid="{{$category['id']}}"  data-id="{{ (isset($sub_category_id) && $sub_category_id != '') ? $sub_category_id : '' }}" data-catname="{{ (isset($sub_category_name) && $sub_category_name != '') ? $sub_category_name : ''}}"> <span>{{ $subcategory['name'] }}</span>
    @if (!empty($subcategory['subcategories']))
        <ul class="subcategories">
            @foreach ($subcategory['subcategories'] as $subsubcategory)
                @include('partials.subframecategory-optgroup', ['subcategory' => $subsubcategory,'sub_category_id' => $subsubcategory['id'],'sub_category_name' => $subsubcategory['name']])
            @endforeach
        </ul>
    @endif
</li>


