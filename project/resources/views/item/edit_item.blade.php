 @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
 <div class="main-container designer-employee-container">
     <div id="main_loading_screen" style="display: none;">
         <div id="loader-wrapper">
             <div id="loader"></div>
             <div class="loader-section section-left"></div>
             <div class="loader-section section-right"></div>
         </div>
     </div>
     <div class="pd-ltr-20 xs-pd-20-10">
         <div class="row pd-20 card-box mb-30">
             <div class="col-md-10 col-sm-12 text-right">
                 <ul class="nav nav-tabs customtab" id="page_list" role="tablist">
                     @for ($i = 0; $i < count($dataArray['thumbs']); $i++)
                         @if ($i == 0)
                             <li class="nav-item li_page_class" id="li_page_{{ $i }}"
                                 data-page="{{ $i }}"> <a class="nav-link active"
                                     id="a_link_{{ $i }}" data-toggle="tab" href="#page_{{ $i }}"
                                     role="tab">Page {{ $i + 1 }}</a> </li>
                         @else
                             <li class="nav-item li_page_class" id="li_page_{{ $i }}"
                                 data-page="{{ $i }}"> <a class="nav-link" id="a_link_{{ $i }}"
                                     data-toggle="tab" href="#page_{{ $i }}" role="tab">Page
                                     {{ $i + 1 }}</a>
                             </li>
                         @endif
                     @endfor

                 </ul>
             </div>
             <div class="col-md-2 col-sm-12 text-right">
                 <button type="button" name="add_new_page" id="add_new_page"
                     class="btn btn-primary form-control-file">Add New Page</button>
             </div>
         </div>
     </div>

     <div class="pd-ltr-20 xs-pd-20-10">
         <div class="row pd-20 card-box mb-30">
             <form method="post" id="import_json_form" enctype="multipart/form-data">
                 @csrf
                 <div class="row">
                     <div class="col-md-5 col-sm-12">
                         <div class="form-group">
                             <h6>Json File</h6>
                             <input type="file" id="json_file" class="form-control-file form-control"
                                 name="json_file">
                         </div>
                     </div>

                     <div class="col-md-5 col-sm-12">
                         <div class="form-group">
                             <h6>Images</h6>
                             <input type="file" class="form-control" id="st_image" name="st_image[]" multiple
                                 required>
                         </div>
                     </div>

                     <div class="col-md-2 col-sm-12">
                         <div class="form-group">
                             <h6 style="opacity: 0">Images</h6>
                             <button type="button" name="import_json" id="import_json"
                                 class="btn btn-primary form-control-file">Import Json</button>
                         </div>
                     </div>
                 </div>
             </form>
         </div>
     </div>
     <div class="min-height-200px">
         <div class="pd-20 card-box mb-30" style="background-color: #eaeaea;">
             <form method="post" id="dynamic_form" enctype="multipart/form-data">
                 <span id="result"></span>
                 @csrf
                 <div id="page_number_container" style="display: none;">
                     @for ($i = 0; $i < count($dataArray['thumbs']); $i++)
                         <input class="form-control" type="textname" id="page_number_{{ $i }}"
                             name="design_page_number[]" value="{{ $i }}" style="display: none;">
                     @endfor
                 </div>
                 <div class="tab-content" id="page_container">

                     @for ($i = 0; $i < count($dataArray['thumbs']); $i++)

                         @php
                             $thumb = $dataArray['thumbs'][$i];
                             $design = $dataArray['designs'][$i] ?? [];
                         @endphp
                         @if ($i == 0)
                             <div class="tab-pane fade show active" id="page_{{ $i }}" role="tabpanel">
                             @else
                                 <div class="tab-pane fade" id="page_{{ $i }}" role="tabpanel">
                         @endif

                         <div class="row">
                             <div class="col-md-2 col-sm-12">
                                 <button type="button" onclick="remove_page('{{ $i }}');"
                                     class="btn btn-danger form-control-file">Remove Page</button>
                             </div>
                         </div>
                         <br />

                         <div class="row">

                             <div class="col-md-2 col-sm-12">
                                 <div class="form-group">
                                     <h6>Post Thumb</h6>
                                     <input type="file" class="form-control-file form-control"
                                         name="post_thumb_{{ $i }}"><br>
                                     <img src="{{ config('filesystems.storage_url') }}{{ $thumb }}"
                                         style="max-width: 100px; max-height: 100px; width: auto; height: auto" />

                                     <input class="form-control" type="textname"
                                         name="post_thumb_path_{{ $i }}" value="{{ $thumb }}"
                                         style="display: none">
                                 </div>
                             </div>

                             <div class="col-md-2 col-sm-12">
                                 <div class="form-group">
                                     <h6>Select BG Type</h6>
                                     <div class="col-sm-20">
                                         <select id="bg_type_id_{{ $i }}"
                                             class="selectpicker form-control" data-style="btn-outline-primary"
                                             onchange="selectChangeFunc('{{ $i }}');"
                                             name="bg_type_id_{{ $i }}" required>
                                             @foreach ($dataArray['bg_mode'] as $bg)
                                                 @if (($design['type'] ?? '2') == $bg->value)
                                                     <option value="{{ $bg->value }}" selected="">
                                                         {{ $bg->type }}
                                                     </option>
                                                 @else
                                                     <option value="{{ $bg->value }}">{{ $bg->type }}</option>
                                                 @endif
                                             @endforeach
                                         </select>
                                     </div>
                                 </div>
                             </div>


                             <div class="col-md-2 col-sm-12">
                                 @if (($design['type'] ?? '') == 0 || ($design['type'] ?? '') == 1)
                                     <div class="form-group" id="back_image_field_{{ $i }}"
                                         style="display: block;">
                                         <h6>Back Image</h6>
                                         <input type="file" id="back_image_{{ $i }}"
                                             class="form-control-file form-control"
                                             name="back_image_{{ $i }}"><br>
                                         <img src="{{ config('filesystems.storage_url') }}{{ $design['image'] }}"
                                             style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                         <input class="form-control" type="textname"
                                             id="back_image_path_{{ $i }}"
                                             name="back_image_path_{{ $i }}"
                                             value="{{ $design['image'] }}" style="display: none">
                                     </div>
                                     <div class="form-group" id="color_code_field_{{ $i }}"
                                         style="display: none;">
                                         <h6>Color Code</h6>
                                         <input class="form-control" type="textname"
                                             id="color_code_{{ $i }}"
                                             name="color_code_{{ $i }}">
                                     </div>
                                 @else
                                     <div class="form-group" id="back_image_field_{{ $i }}"
                                         style="display: none;">
                                         <h6>Back Image</h6>
                                         <input type="file" id="back_image_{{ $i }}"
                                             class="form-control-file form-control"
                                             name="back_image_{{ $i }}"><br>
                                     </div>

                                     <div class="form-group" id="color_code_field_{{ $i }}"
                                         style="display: block;">
                                         <h6>Color Code</h6>
                                         <input class="form-control" type="textname"
                                             id="color_code_{{ $i }}"
                                             name="color_code_{{ $i }}"
                                             value="{{ $design['color'] ?? '' }}">
                                     </div>
                                 @endif

                             </div>

                             <div class="col-md-2 col-sm-12">
                                 <div class="form-group">
                                     <h6>Gradient Angle</h6>
                                     <input class="form-control" type="textname"
                                         name="grad_angle_{{ $i }}"
                                         value="{{ $design['gradAngle'] ?? 0 }}" required>
                                 </div>
                             </div>
                             <div class="col-md-2 col-sm-12">
                                 <div class="form-group">
                                     <h6>Gradient Ratio</h6>
                                     <input class="form-control" type="textname"
                                         name="grad_ratio_{{ $i }}"
                                         value="{{ $design['gradRatio'] ?? 0 }}" required>
                                 </div>
                             </div>

                         </div>

                         <div class="form-group">
                             <br />
                             <h6>Layers</h6>
                         </div>

                         <hr size="8" width="100%" color="black">
                         <div id="layers_container_{{ $i }}">

                             @if ($design && isset($design['layers']))
                                 @for ($j = 0; $j < count($design['layers']); $j++)

                                     @php
                                         $layer = $design['layers'][$j];
                                     @endphp
                                     @if ($layer['layerType'] == 1)

                                         <div class="row">
                                             <input class="form-control" type="textname"
                                                 name="layerType_{{ $i }}[]" value="1"
                                                 style="display: none">
                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6 style="opacity: 0;">.</h6>
                                                     <button type="button" id="remove_layer"
                                                         class="btn btn-danger form-control-file">Remove
                                                     </button>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Width</h6>
                                                     <input class="form-control" type="textname"
                                                         name="st_width_{{ $i }}[]"
                                                         value="{{ $layer['width'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Height</h6>
                                                     <input class="form-control" type="textname"
                                                         name="st_height_{{ $i }}[]"
                                                         value="{{ $layer['height'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Scale X</h6>
                                                     @if (isset($layer['scaleX']))
                                                         <input class="form-control" type="textname"
                                                             name="st_scale_x_{{ $i }}[]"
                                                             value="{{ $layer['scaleX'] }}" required>
                                                     @else
                                                         <input class="form-control" type="textname"
                                                             name="st_scale_x_{{ $i }}[]" value="1"
                                                             required>
                                                     @endif
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Scale Y</h6>
                                                     @if (isset($layer['scaleY']))
                                                         <input class="form-control" type="textname"
                                                             name="st_scale_y_{{ $i }}[]"
                                                             value="{{ $layer['scaleY'] }}" required>
                                                     @else
                                                         <input class="form-control" type="textname"
                                                             name="st_scale_y_{{ $i }}[]" value="1"
                                                             required>
                                                     @endif

                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>X Pos</h6>
                                                     <input class="form-control" type="textname"
                                                         name="st_x_pos_{{ $i }}[]"
                                                         value="{{ $layer['left'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Y Pos</h6>
                                                     <input class="form-control" type="textname"
                                                         name="st_y_pos_{{ $i }}[]"
                                                         value="{{ $layer['top'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Rotation</h6>
                                                     <input class="form-control" type="textname"
                                                         name="st_rotation_{{ $i }}[]"
                                                         value="{{ $layer['rotation'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Opacity</h6>
                                                     <input class="form-control" type="textname"
                                                         name="st_opacity_{{ $i }}[]"
                                                         value="{{ $layer['opacity'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Type</h6>
                                                     <select class="selectpicker form-control"
                                                         data-style="btn-outline-primary"
                                                         name="st_type_{{ $i }}[]" required>
                                                         @foreach ($dataArray['sticker_mode'] as $sticker)
                                                             @if ($layer['type'] == $sticker->value)
                                                                 <option value="{{ $sticker->value }}"
                                                                     selected="">
                                                                     {{ $sticker->type }}
                                                                 </option>
                                                             @else
                                                                 <option value="{{ $sticker->value }}">
                                                                     {{ $sticker->type }}</option>
                                                             @endif
                                                         @endforeach
                                                     </select>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Color</h6>

                                                     @if (isset($layer['color']))
                                                         <input class="form-control" type="textname"
                                                             name="st_color_{{ $i }}[]"
                                                             value="{{ $layer['color'] }}">
                                                     @else
                                                         <input class="form-control" type="textname"
                                                             name="st_color_{{ $i }}[]">
                                                     @endif

                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Resize</h6>
                                                     <select class="selectpicker form-control"
                                                         data-style="btn-outline-primary"
                                                         name="st_resize_{{ $i }}[]" required>
                                                         @foreach ($dataArray['resize_mode'] as $resize)
                                                             @if ($layer['resizeType'] == $resize->value)
                                                                 <option value="{{ $resize->value }}" selected="">
                                                                     {{ $resize->type }}
                                                                 </option>
                                                             @else
                                                                 <option value="{{ $resize->value }}">
                                                                     {{ $resize->type }}</option>
                                                             @endif
                                                         @endforeach
                                                     </select>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Lock Type</h6>
                                                     <select class="selectpicker form-control"
                                                         data-style="btn-outline-primary"
                                                         name="st_lock_type_{{ $i }}[]" required>

                                                         @if (!isset($layer['lockType']))
                                                             @foreach ($dataArray['lock_type'] as $type)
                                                                 <option value="{{ $type->value }}">
                                                                     {{ $type->type }}</option>
                                                             @endforeach
                                                         @else
                                                             @foreach ($dataArray['lock_type'] as $type)
                                                                 @if ($layer['lockType'] == $type->value)
                                                                     <option value="{{ $type->value }}"
                                                                         selected="">{{ $type->type }}
                                                                     </option>
                                                                 @else
                                                                     <option value="{{ $type->value }}">
                                                                         {{ $type->type }}</option>
                                                                 @endif
                                                             @endforeach
                                                         @endif
                                                     </select>
                                                 </div>
                                             </div>

                                             <div class="col-md-2 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Sticker Image</h6>
                                                     <input type="file" class="form-control"
                                                         name="st_image_{{ $i }}[]">
                                                     <br>
                                                     <img src="{{ config('filesystems.storage_url') }}{{ $layer['image'] }}"
                                                         style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                                     <input class="form-control" type="text"
                                                         name="st_image_path_{{ $i }}[]"
                                                         value="{{ $layer['image'] }}" style="display: none">
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Editable</h6>
                                                     <div class="col-sm-20">
                                                         <select class="selectpicker form-control"
                                                             data-style="btn-outline-primary"
                                                             name="st_is_editable_{{ $i }}[]" required>

                                                             @if (isset($layer['isEditable']))
                                                                 @if ($layer['isEditable'] == '1')
                                                                     <option value="1" selected>TRUE</option>
                                                                     <option value="0">FALSE</option>
                                                                 @else
                                                                     <option value="1">TRUE</option>
                                                                     <option value="0" selected>FALSE</option>
                                                                 @endif
                                                             @else
                                                                 <option value="0" selected>FALSE</option>
                                                                 <option value="1">TRUE</option>
                                                             @endif
                                                         </select>
                                                     </div>
                                                 </div>
                                             </div>

                                             <div class="col-md-2 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Editable Title</h6>
                                                     <select class="custom-select2 form-control"
                                                         data-style="btn-outline-primary"
                                                         name="st_editable_title_{{ $i }}[]">
                                                         <option value="null" selected="">=Select Editable Title=
                                                         </option>
                                                         @foreach ($dataArray['editable_mode'] as $editable_mode)
                                                             @if (isset($layer['editableTitle']))
                                                                 @if ($layer['editableTitle'] == $editable_mode->name)
                                                                     <option value="{{ $editable_mode->name }}"
                                                                         selected="">
                                                                         {{ $editable_mode->name }}
                                                                         ({{ $editable_mode->brand_id }})
                                                                     </option>
                                                                 @else
                                                                     <option value="{{ $editable_mode->name }}">
                                                                         {{ $editable_mode->name }}
                                                                         ({{ $editable_mode->brand_id }})
                                                                     </option>
                                                                 @endif
                                                             @else
                                                                 <option value="{{ $editable_mode->name }}">
                                                                     {{ $editable_mode->name }}
                                                                     ({{ $editable_mode->brand_id }})
                                                                 </option>
                                                             @endif
                                                         @endforeach
                                                     </select>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Url</h6>
                                                     <div class="col-sm-20">
                                                         <select class="selectpicker form-control"
                                                             data-style="btn-outline-primary"
                                                             name="st_is_url_{{ $i }}[]" required>

                                                             @if (isset($layer['isUrl']))
                                                                 @if ($layer['isUrl'] == '1')
                                                                     <option value="1" selected>TRUE</option>
                                                                     <option value="0">FALSE</option>
                                                                 @else
                                                                     <option value="1">TRUE</option>
                                                                     <option value="0" selected>FALSE</option>
                                                                 @endif
                                                             @else
                                                                 <option value="0" selected>FALSE</option>
                                                                 <option value="1">TRUE</option>
                                                             @endif
                                                         </select>
                                                     </div>
                                                 </div>
                                             </div>

                                             <hr size="8" width="100%" color="black">
                                         </div>
                                     @else
                                         <div class="row">
                                             <input class="form-control" type="textname"
                                                 name="layerType_{{ $i }}[]" value="2"
                                                 style="display: none">
                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6 style="opacity: 0;">.</h6>
                                                     <button type="button" id="remove_layer"
                                                         class="btn btn-danger form-control-file">Remove
                                                     </button>
                                                 </div>
                                             </div>
                                             <div class="col-md-3 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Text</h6>
                                                     <textarea style="height: 80px" class="form-control" name="text_{{ $i }}[]">{{ $layer['text'] }}</textarea>
                                                 </div>
                                             </div>

                                             <div class="col-md-3 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Effects</h6>
                                                     @if (isset($layer['Effects']))
                                                         <textarea style="height: 80px" class="form-control" name="txt_effect_{{ $i }}[]">{{ $layer['Effects'] }}</textarea>
                                                     @elseif (isset($layer['effects']))
                                                         <textarea style="height: 80px" class="form-control" name="txt_effect_{{ $i }}[]">{{ $layer['effects'] }}</textarea>
                                                     @else
                                                         <textarea style="height: 80px" class="form-control" name="txt_effect_{{ $i }}[]"></textarea>
                                                     @endif
                                                 </div>
                                             </div>

                                             <div class="col-md-3 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Font Family</h6>
                                                     <input class="form-control" list="font_list" type="text"
                                                         name="font_family_{{ $i }}[]" autocomplete="on"
                                                         style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000"
                                                         value="{{ $layer['font'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Alignment</h6>
                                                     <select class="selectpicker form-control"
                                                         data-style="btn-outline-primary"
                                                         name="txt_align_{{ $i }}[]" required>

                                                         @foreach ($dataArray['txt_align'] as $txt)
                                                             @if ($layer['format']['alignment'] == $txt->value)
                                                                 <option value="{{ $txt->value }}" selected="">
                                                                     {{ $txt->type }}
                                                                 </option>
                                                             @else
                                                                 <option value="{{ $txt->value }}">
                                                                     {{ $txt->type }}</option>
                                                             @endif
                                                         @endforeach

                                                     </select>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Size</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_size_{{ $i }}[]"
                                                         value="{{ $layer['size'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Color</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_color_{{ $i }}[]"
                                                         value="{{ $layer['color'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Width</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_width_{{ $i }}[]"
                                                         value="{{ $layer['width'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Height</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_height_{{ $i }}[]"
                                                         value="{{ $layer['height'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Scale X</h6>
                                                     @if (isset($layer['scaleX']))
                                                         <input class="form-control" type="textname"
                                                             name="txt_scale_x_{{ $i }}[]"
                                                             value="{{ $layer['scaleX'] }}" required>
                                                     @else
                                                         <input class="form-control" type="textname"
                                                             name="txt_scale_x_{{ $i }}[]" value="1"
                                                             required>
                                                     @endif
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Scale Y</h6>
                                                     @if (isset($layer['scaleY']))
                                                         <input class="form-control" type="textname"
                                                             name="txt_scale_y_{{ $i }}[]"
                                                             value="{{ $layer['scaleY'] }}" required>
                                                     @else
                                                         <input class="form-control" type="textname"
                                                             name="txt_scale_y_{{ $i }}[]" value="1"
                                                             required>
                                                     @endif

                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>X Pos</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_x_pos_{{ $i }}[]"
                                                         value="{{ $layer['left'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Y Pos</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_y_pos_{{ $i }}[]"
                                                         value="{{ $layer['top'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Line Space</h6>
                                                     <input class="form-control" type="textname"
                                                         name="line_spacing_{{ $i }}[]"
                                                         value="{{ $layer['spacing']['line'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-2 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Line Space Multiplier</h6>
                                                     @if (isset($layer['spacing']))
                                                         <input class="form-control" type="textname"
                                                             name="lineSpaceMultiplier_{{ $i }}[]"
                                                             value="{{ $layer['spacing']['lineMultiplier'] }}"
                                                             required>
                                                     @else
                                                         <input class="form-control" type="textname"
                                                             name="lineSpaceMultiplier_{{ $i }}[]"
                                                             value="1" required>
                                                     @endif
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Word Space</h6>
                                                     <input class="form-control" type="textname"
                                                         name="word_spacing_{{ $i }}[]"
                                                         value="{{ $layer['spacing']['letter'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Curve</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_curve_{{ $i }}[]"
                                                         value="{{ $layer['curve'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Rotation</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_rotation_{{ $i }}[]"
                                                         value="{{ $layer['rotation'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Opacity</h6>
                                                     <input class="form-control" type="textname"
                                                         name="txt_opacity_{{ $i }}[]"
                                                         value="{{ $layer['opacity'] }}" required>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Editable</h6>
                                                     <div class="col-sm-20">
                                                         <select class="selectpicker form-control"
                                                             data-style="btn-outline-primary"
                                                             name="is_editable_{{ $i }}[]" required>

                                                             @if (isset($layer['isEditable']))
                                                                 @if ($layer['isEditable'] == '1')
                                                                     <option value="1" selected>TRUE</option>
                                                                     <option value="0">FALSE</option>
                                                                 @else
                                                                     <option value="1">TRUE</option>
                                                                     <option value="0" selected>FALSE</option>
                                                                 @endif
                                                             @else
                                                                 <option value="0" selected>FALSE</option>
                                                                 <option value="1">TRUE</option>
                                                             @endif
                                                         </select>
                                                     </div>
                                                 </div>
                                             </div>

                                             <div class="col-md-2 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Editable Title</h6>
                                                     <select class="custom-select2 form-control"
                                                         data-style="btn-outline-primary"
                                                         name="editable_title_{{ $i }}[]">
                                                         <option value="null" selected="">=Select Editable Title=
                                                         </option>
                                                         @foreach ($dataArray['editable_mode'] as $editable_mode)
                                                             @if (isset($layer['editableTitle']))
                                                                 @if ($layer['editableTitle'] == $editable_mode->name)
                                                                     <option value="{{ $editable_mode->name }}"
                                                                         selected="">
                                                                         {{ $editable_mode->name }}
                                                                         ({{ $editable_mode->brand_id }})
                                                                     </option>
                                                                 @else
                                                                     <option value="{{ $editable_mode->name }}">
                                                                         {{ $editable_mode->name }}
                                                                         ({{ $editable_mode->brand_id }})
                                                                     </option>
                                                                 @endif
                                                             @else
                                                                 <option value="{{ $editable_mode->name }}">
                                                                     {{ $editable_mode->name }}
                                                                     ({{ $editable_mode->brand_id }})
                                                                 </option>
                                                             @endif
                                                         @endforeach
                                                     </select>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Url</h6>
                                                     <div class="col-sm-20">
                                                         <select class="selectpicker form-control"
                                                             data-style="btn-outline-primary"
                                                             name="is_url_{{ $i }}[]" required>

                                                             @if (isset($layer['isUrl']))
                                                                 @if ($layer['isUrl'] == '1')
                                                                     <option value="1" selected>TRUE</option>
                                                                     <option value="0">FALSE</option>
                                                                 @else
                                                                     <option value="1">TRUE</option>
                                                                     <option value="0" selected>FALSE</option>
                                                                 @endif
                                                             @else
                                                                 <option value="0" selected>FALSE</option>
                                                                 <option value="1">TRUE</option>
                                                             @endif
                                                         </select>
                                                     </div>
                                                 </div>
                                             </div>

                                             <div class="col-md-1 col-sm-12">
                                                 <div class="form-group">
                                                     <h6>Update Text</h6>
                                                     <div class="col-sm-20">
                                                         <select class="selectpicker form-control txt_update_class"
                                                             data-style="btn-outline-primary"
                                                             name="txt_update_{{ $i }}[]"
                                                             disabled="true">

                                                             @if (isset($layer['update']))
                                                                 @if ($layer['update'] == true)
                                                                     <option value="1" selected>TRUE</option>
                                                                     <option value="0">FALSE</option>
                                                                 @else
                                                                     <option value="1">TRUE</option>
                                                                     <option value="0" selected>FALSE</option>
                                                                 @endif
                                                             @else
                                                                 <option value="1" selected>TRUE</option>
                                                                 <option value="0">FALSE</option>
                                                             @endif
                                                         </select>
                                                     </div>
                                                 </div>
                                             </div>

                                             <hr size="8" width="100%" color="black">
                                         </div>

                                     @endif
                                 @endfor
                             @endif

                         </div>

                         <div class="col-md-1 col-sm-12">
                             <div class="form-group">
                                 <h6 style="opacity: 0;">.</h6>
                                 <div class="dropdown">
                                     <a class="btn btn-primary dropdown-toggle" href="#" role="button"
                                         data-toggle="dropdown">
                                         Add
                                     </a>
                                     <div class="dropdown-menu dropdown-menu-right">
                                         <button type="button" class="dropdown-item"
                                             onclick="add_sticker_layer('{{ $i }}');">Sticker
                                             Layer</button>
                                         <button type="button" class="dropdown-item"
                                             onclick="add_text_layer('{{ $i }}');">Text Layer</button>
                                     </div>
                                 </div>
                             </div>
                         </div>
                 </div>
                 @endfor
         </div>
         <br />

         <div class="row">
             <div class="col-md-12 col-sm-12">
                 <img src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->post_thumb }}"
                     style="max-width: 300px" />
             </div>
         </div>

         <br>
         <hr>

         <div>
             <input class="btn btn-primary" type="submit" name="submit">
         </div>
         </form>
     </div>
 </div>
 </div>
 <datalist id="font_list">
     @foreach ($dataArray['fonts'] as $font)
         <option value="{{ $font->name }}.{{ $font->extension }}"></option>
     @endforeach
 </datalist>

 <datalist id="related_tag_list">
     @foreach ($dataArray['searchTagArray'] as $searchTag)
         <option value="{{ $searchTag->name }}"></option>
     @endforeach
 </datalist>
 </div>
 @include('layouts.masterscript')
 <script>
     window.addEventListener("load", function() {
         var tagsInputContainer = document.querySelector('.bootstrap-tagsinput');
         var tagsInput = tagsInputContainer.querySelector('input[type="text"]');

         if (tagsInput) {
             tagsInput.setAttribute('list', 'related_tag_list');
             tagsInput.setAttribute('autocomplete', 'on');
             tagsInput.style.width = '100%';
             tagsInput.style.height = '45px';
             tagsInput.style.border = '1px solid #000000';
             tagsInput.style.borderRadius = '5px';
             tagsInput.style.marginTop = '5px';
         }

         // $('#colorTags').tagsinput('remove', $(currentTag).text());
         // $('#colorTags').tagsinput('add', colorHex);
     });

     total_pages = {{ count($dataArray['thumbs']) }};
     current_page = 0;

     var sizeArray;

     $('#page_list').on('click', 'a[data-toggle="tab"]', function(e) {
         var activeTab = $(e.target);
         var pageId = activeTab.attr('href');
         current_page = pageId.replaceAll("#page_", "");
     });

     $(document).on('click', '#import_json', function(event) {

         event.preventDefault();
         count = $("#page_list").children().length;
         if (count > 15) {
             alert("Can't add new page, Max 15 pages allowed");
             return
         }

         var json_file = $("#json_file")[0];
         var st_image = $("#st_image")[0];

         if (json_file.files.length === 0) {
             alert('Please select a json file');
             return;
         }

         if (st_image.files.length === 0) {
             alert('Please select images');
             return;
         }

         var validExtensions = ["json"];
         var fileExtension = json_file.files[0].name.split('.').pop();

         if (validExtensions.indexOf(fileExtension.toLowerCase()) === -1) {
             alert("Invalid file extension. Allowed extensions: " + validExtensions.join(", "));
             return;
         }
         validExtensions = ["jpg", "jpeg", "png"];
         var invalidFiles = [];
         for (var i = 0; i < st_image.files.length; i++) {
             var fileName = st_image.files[i].name;
             var fileExtension = fileName.split('.').pop();
             if (validExtensions.indexOf(fileExtension.toLowerCase()) === -1) {
                 invalidFiles.push(fileName);
             }
         }

         if (invalidFiles.length > 0) {
             alert("Invalid file extensions for: " + invalidFiles.join(", ") + ". Allowed extensions: " +
                 validExtensions.join(", "));
             return;
         }

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         var formData = new FormData(document.getElementById("import_json_form"));
         $.ajax({
             url: "{{ route('import_page') }}",
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 hideFields();
                 if (data.error) {
                     window.alert(data.error);
                 } else {
                     $('#json_file').val('');
                     $.each(data.success.layers, function(index, layer) {
                         if (layer.layerType === 1) {
                             add_json_sticker_layer(current_page, index, layer, st_image);
                         } else if (layer.layerType === 2) {
                             add_json_text_layer(current_page, index, layer);
                         }
                     });
                     $('#st_image').val('');
                 }
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

     function selectChangeFunc(id) {
         if ($('#bg_type_id_' + id).val() === '0' || $('#bg_type_id_' + id).val() === '1') {
             $('#back_image_' + id).attr('required', '');
             var x = document.getElementById("back_image_field_" + id);
             x.style.display = "block";

             $('#color_code_' + id).removeAttr('required');
             var x1 = document.getElementById("color_code_field_" + id);
             x1.style.display = "none";

         } else {
             $('#color_code_' + id).attr('required', '');
             var x = document.getElementById("color_code_field_" + id);
             x.style.display = "block";

             $('#back_image_' + id).removeAttr('required');
             var x1 = document.getElementById("back_image_field_" + id);
             x1.style.display = "none";
         }
     }

     $(document).on('click', '#add_new_page', function() {
         count = $("#page_list").children().length;
         if (count >= 15) {
             alert('Max 15 pages allowed');
         } else {
             total_pages++;
             count = count + 1;
             $page_list = '<li class="nav-item li_page_class" id="li_page_' + total_pages +
                 '"> <a class="nav-link" id="a_link_' + total_pages + '" data-toggle="tab" href="#page_' +
                 total_pages + '" role="tab">Page ' + count + '</a> </li>';
             $('#page_list').append($page_list);
             add_page(total_pages);
             $('#page_number_container').append('<input class="form-control" id="page_number_' + total_pages +
                 '" type="textname" name="design_page_number[]" value="' + total_pages +
                 '" style="display: none;">');

             $('#a_link_' + total_pages).click();
             current_page = total_pages;
         }

     });

     /* Filter and selected reordering options and value are set on inputed field when the form submit */

     const initialSetFilteredData = () => {
         var keywords = "";
         $("#relatedKeyword span.tag.label.label-info").each(function(index, value) {
             var keywordVal = $(this).text();
             if (keywords == "") {
                 keywords = keywordVal;
             } else {
                 keywords = keywords + ',' + keywordVal;
             }
         });

         $("#keywords").val(keywords);

         var selectedValues = "";
         $("select[name='religion_id[]']").next().find(
             ".select2-selection--multiple .select2-selection__rendered li").each(function(index, value) {
             var text = $(this).text().trim();
             text = text.replace('×', '');

             var optionValue = $("select[name='religion_id[]'] option").filter(function() {
                 return $(this).text().trim() === text;
             }).val();

             if (selectedValues == "") {
                 selectedValues = optionValue;
             } else {
                 selectedValues = selectedValues + ',' + optionValue;
             }
         });

         selectedValues = removeUndefineValue(selectedValues);
         $("#selectedReligions").val(selectedValues);

         var selectedColorIds = "";
         $("select[name='color_id[]']").next().find(".select2-selection--multiple .select2-selection__rendered li")
             .each(function(index, value) {
                 var text = $(this).text().trim();
                 text = text.replace('×', '');

                 var optionValue = $("select[name='color_id[]'] option").filter(function() {
                     return $(this).text().trim() === text;
                 }).val();

                 if (selectedColorIds == "") {
                     selectedColorIds = optionValue;
                 } else {
                     selectedColorIds = selectedColorIds + ',' + optionValue;
                 }
             });
         selectedColorIds = removeUndefineValue(selectedColorIds);
         $("#selectedColors").val(selectedColorIds);

         var selectedThemes = "";
         $("select[name='theme_id[]']").next().find(".select2-selection--multiple .select2-selection__rendered li")
             .each(function(index, value) {
                 var val = $(this).text().trim();
                 val = val.replace('×', '');

                 if (selectedThemes == "") {
                     selectedThemes = val;
                 } else {
                     selectedThemes = selectedThemes + ',' + val;
                 }
             });
         $("#selectedThemes").val(selectedThemes);

         var selectedIntrests = "";
         $("select[name='interest_id[]']").next().find(
             ".select2-selection--multiple .select2-selection__rendered li").each(function(index, value) {
             var text = $(this).text().trim();
             text = text.replace('×', '');

             var optionValue = $("select[name='interest_id[]'] option").filter(function() {
                 return $(this).text().trim() === text;
             }).val();

             if (selectedIntrests == "") {
                 selectedIntrests = optionValue;
             } else {
                 selectedIntrests = selectedIntrests + ',' + optionValue;
             }
         });
         selectedIntrests = removeUndefineValue(selectedIntrests);
         $("#selectedInterests").val(selectedIntrests);


         var selectedLanguageIds = "";
         $("select[name='lang_id[]']").next().find(".select2-selection--multiple .select2-selection__rendered li")
             .each(function(index, value) {
                 var text = $(this).text().trim();
                 text = text.replace('×', '');
                 var optionValue = $("select[name='lang_id[]'] option").filter(function() {
                     return $(this).text().trim() === text;
                 }).val();

                 if (selectedLanguageIds == "") {
                     selectedLanguageIds = optionValue;
                 } else {
                     selectedLanguageIds = selectedLanguageIds + ',' + optionValue;
                 }
             });
         selectedLanguageIds = removeUndefineValue(selectedLanguageIds);
         $("#selectedLanguages").val(selectedLanguageIds);
     }

     const removeUndefineValue = (string) => {
         var values = string.split(',');
         values = values.filter(function(value) {
             return value !== 'undefined';
         });
         string = values.join(',');

         return string;
     }

     $('#dynamic_form').on('submit', function(event) {
         event.preventDefault();
         count = $("#page_list").children().length;

         if ($("input[name='new_category_id']").val() == "0" || $("input[name='new_category_id']").val() == "") {
             $("#newCategoryRequiredPopup").show();
             event.preventDefault();
             return false;
         }

         if (count == 0) {
             alert('Add atleast 1 page.');
             return;
         }

         initialSetFilteredData()

         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         document.querySelectorAll('.txt_update_class').forEach(function(select) {
             select.disabled = false;
         });

         var formData = new FormData(this);
         var url = "{{ route('item.update', [$dataArray['item']->id]) }}";
         $.ajax({
             url: url,
             type: 'POST',
             data: formData,
             beforeSend: function() {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 document.querySelectorAll('.txt_update_class').forEach(function(select) {
                     select.disabled = true;
                 });

                 hideFields();
                 if (data.error) {
                     window.alert(data.error);
                     $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                 } else {
                     window.alert(data.success);
                     // window.location.href = "{{ route('show_item') }}";
                 }

                 setTimeout(function() {
                     $('#result').html('');
                 }, 3000);

             },
             error: function(error) {
                 document.querySelectorAll('.txt_update_class').forEach(function(select) {
                     select.disabled = true;
                 });
                 hideFields();
                 window.alert(error.responseText);
             },
             cache: false,
             contentType: false,
             processData: false
         })
     });

     $(document).on('click', '#remove_layer', function() {
         $(this).closest(".row").remove();
     });

     function remove_page(id) {
         count = $("#page_list").children().length;
         if (count <= 1) {
             alert('Need at least 1 page');
             return;
         }

         $('#li_page_' + id).remove();
         $('#page_' + id).remove();
         $('#page_number_' + id).remove();
         count = 1;
         pageId = 0;
         $('#page_list li a').each(function(e) {
             var li = $(this);
             if (count === 1) {
                 pageId = li.attr('href');
                 pageId = pageId.replaceAll("#page_", "");
             }
             li.text('Page ' + count);
             count++;
         });
         current_page = pageId;
         $('#a_link_' + pageId).click();
     }

     //@formatter:off
     function add_page(id) {
         html = '<div class="tab-pane fade" id="page_' + id +
             '" role="tabpanel"> <div class="row"> <div class="col-md-2 col-sm-12"> <button type="button" onclick="remove_page(' +
             id +
             ');" class="btn btn-danger form-control-file">Remove Page</button> </div> </div> <br/><div class="row"> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Post Thumb</h6> <input type="file" class="form-control-file form-control" name="post_thumb_' +
             id +
             '"><br> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Select BG Type</h6> <div class="col-sm-20"> <select id="bg_type_id_' +
             id + '" class="selectpicker form-control" data-style="btn-outline-primary" onchange="selectChangeFunc(' +
             id + ');" name="bg_type_id_' + id +
             '" required> @foreach ($dataArray['bg_mode'] as $bg) <option value="{{ $bg->value }}">{{ $bg->type }}</option> @endforeach </select> </div> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group" id="back_image_field_' +
             id + '" style="display: block;"> <h6>Back Image</h6> <input type="file" id="back_image_' + id +
             '" class="form-control-file form-control" name="back_image_' + id +
             '"><br> </div> <div class="form-group" id="color_code_field_' + id +
             '" style="display: none;"> <h6>Color Code</h6> <input class="form-control" type="textname" id="color_code_' +
             id + '" name="color_code_' + id +
             '"> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Gradient Angle</h6> <input class="form-control" type="textname" name="grad_angle_' +
             id +
             '" required> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Gradient Ratio</h6> <input class="form-control" type="textname" name="grad_ratio_' +
             id +
             '" required> </div> </div> </div> <div class="form-group"> <br/> <h6>Layers</h6> </div> <hr size="8" width="100%" color="black"> <div id="layers_container_' +
             id +
             '"></div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6 style="opacity: 0;">.</h6> <div class="dropdown"> <a class="btn btn-primary dropdown-toggle" href="#" role="button" data-toggle="dropdown"> Add </a> <div class="dropdown-menu dropdown-menu-right"> <button type="button" class="dropdown-item" onclick="add_sticker_layer(' +
             id + ');">Sticker Layer</button> <button type="button" class="dropdown-item" onclick="add_text_layer(' +
             id + ');">Text Layer</button> </div> </div> </div> </div> </div>';
         $('#page_container').append(html);
     }

     function add_text_layer(id) {
         html = '<div class="row"> <input class="form-control" type="textname" name="layerType_' + id +
             '[]" value="2" style="display: none"> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6 style="opacity: 0;">.</h6> <button type="button" id="remove_layer" class="btn btn-danger form-control-file">Remove </button> </div> </div> <div class="col-md-3 col-sm-12"> <div class="form-group"> <h6>Text</h6> <textarea style="height: 80px" class="form-control" name="text_' +
             id +
             '[]"></textarea> </div> </div> <div class="col-md-3 col-sm-12"> <div class="form-group"> <h6>Effects</h6> <textarea style="height: 80px" class="form-control" name="txt_effect_' +
             id +
             '[]"></textarea> </div> </div> <div class="col-md-3 col-sm-12"> <div class="form-group"> <h6>Font Family</h6> <input class="form-control" list="font_list" type="text" name="font_family_' +
             id +
             '[]" autocomplete="on" style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Alignment</h6> <select class="selectpicker form-control" data-style="btn-outline-primary" name="txt_align_' +
             id +
             '[]" required> @foreach ($dataArray['txt_align'] as $txt) <option value="{{ $txt->value }}">{{ $txt->type }}</option> @endforeach </select> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Size</h6> <input class="form-control" type="textname" name="txt_size_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Color</h6> <input class="form-control" type="textname" name="txt_color_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Width</h6> <input class="form-control" type="textname" name="txt_width_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Height</h6> <input class="form-control" type="textname" name="txt_height_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Scale X</h6> <input class="form-control" type="textname" name="txt_scale_x_' +
             id +
             '[]" value="1" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Scale Y</h6> <input class="form-control" type="textname" name="txt_scale_y_' +
             id +
             '[]" value="1" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>X Pos</h6> <input class="form-control" type="textname" name="txt_x_pos_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Y Pos</h6> <input class="form-control" type="textname" name="txt_y_pos_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Line Space</h6> <input class="form-control" type="textname" name="line_spacing_' +
             id +
             '[]" required> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Line Space Multiplier</h6> <input class="form-control" type="textname" name="lineSpaceMultiplier_' +
             id +
             '[]" value="1" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Word Space</h6> <input class="form-control" type="textname" name="word_spacing_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Curve</h6> <input class="form-control" type="textname" name="txt_curve_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Rotation</h6> <input class="form-control" type="textname" name="txt_rotation_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Opacity</h6> <input class="form-control" type="textname" name="txt_opacity_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Editable</h6> <div class="col-sm-20"> <select class="selectpicker form-control" data-style="btn-outline-primary" name="is_editable_' +
             id +
             '[]" required> <option value="0" selected>FALSE</option> <option value="1">TRUE</option> </select> </div> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Editable Title</h6> <select class="custom-select2 form-control" data-style="btn-outline-primary" name="editable_title_' +
             id +
             '[]"> <option value="null" selected="">=Select Editable Title=</option> @foreach ($dataArray['editable_mode'] as $editable_mode) <option value="{{ $editable_mode->name }}">{{ $editable_mode->name }} ({{ $editable_mode->brand_id }})</option> @endforeach </select> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Url</h6> <div class="col-sm-20"> <select class="selectpicker form-control" data-style="btn-outline-primary" name="is_url_' +
             id +
             '[]" required> <option value="0" selected>FALSE</option> <option value="1">TRUE</option> </select> </div> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Update Text</h6> <div class="col-sm-20"> <select class="selectpicker form-control txt_update_class" data-style="btn-outline-primary" name="txt_update_' +
             id +
             '[]" disabled="true"> <option value="0">FALSE</option> <option value="1" selected>TRUE</option> </select> </div> </div> </div> <hr size="8" width="100%" color="black"> </div>';
         $('#layers_container_' + id).append(html);
     }

     function add_sticker_layer(id) {
         html = '<div class="row"> <input class="form-control" type="textname" name="layerType_' + id +
             '[]" value="1" style="display: none"> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6 style="opacity: 0;">.</h6> <button type="button" id="remove_layer" class="btn btn-danger form-control-file">Remove </button> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Width</h6> <input class="form-control" type="textname" name="st_width_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Height</h6> <input class="form-control" type="textname" name="st_height_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Scale X</h6> <input class="form-control" type="textname" name="st_scale_x_' +
             id +
             '[]" value="1" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Scale Y</h6> <input class="form-control" type="textname" name="st_scale_y_' +
             id +
             '[]" value="1" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>X Pos</h6> <input class="form-control" type="textname" name="st_x_pos_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Y Pos</h6> <input class="form-control" type="textname" name="st_y_pos_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Rotation</h6> <input class="form-control" type="textname" name="st_rotation_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Opacity</h6> <input class="form-control" type="textname" name="st_opacity_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Type</h6> <select class="selectpicker form-control" data-style="btn-outline-primary" name="st_type_' +
             id +
             '[]" required> @foreach ($dataArray['sticker_mode'] as $sticker) <option value="{{ $sticker->value }}">{{ $sticker->type }}</option> @endforeach </select> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Color</h6> <input class="form-control" type="textname" name="st_color_' +
             id +
             '[]"> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Resize</h6> <select class="selectpicker form-control" data-style="btn-outline-primary" name="st_resize_' +
             id +
             '[]" required> @foreach ($dataArray['resize_mode'] as $resize) <option value="{{ $resize->value }}">{{ $resize->type }}</option> @endforeach </select> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Lock Type</h6> <select class="selectpicker form-control" data-style="btn-outline-primary" name="st_lock_type_' +
             id +
             '[]" required> @foreach ($dataArray['lock_type'] as $type) <option value="{{ $type->value }}">{{ $type->type }}</option> @endforeach </select> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Sticker Image</h6> <input type="file" class="form-control" name="st_image_' +
             id +
             '[]" required> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Editable</h6> <div class="col-sm-20"> <select class="selectpicker form-control" data-style="btn-outline-primary" name="st_is_editable_' +
             id +
             '[]" required> <option value="0" selected>FALSE</option> <option value="1">TRUE</option> </select> </div> </div> </div> <div class="col-md-2 col-sm-12"> <div class="form-group"> <h6>Editable Title</h6> <select class="custom-select2 form-control" data-style="btn-outline-primary" name="st_editable_title_' +
             id +
             '[]"> <option value="null" selected="">=Select Editable Title=</option> @foreach ($dataArray['editable_mode'] as $editable_mode) <option value="{{ $editable_mode->name }}">{{ $editable_mode->name }} ({{ $editable_mode->brand_id }})</option> @endforeach </select> </div> </div> <div class="col-md-1 col-sm-12"> <div class="form-group"> <h6>Url</h6> <div class="col-sm-20"> <select class="selectpicker form-control" data-style="btn-outline-primary" name="st_is_url_' +
             id +
             '[]" required> <option value="0" selected>FALSE</option> <option value="1">TRUE</option> </select> </div> </div> </div> <hr size="8" width="100%" color="black"> </div>';
         $('#layers_container_' + id).append(html);
     }

     function add_json_text_layer(id, index, layer) {
         html = '<div class="row">';

         html += '<input class="form-control" type="textname" name="layerType_' + id +
             '[]" value="2" style="display: none">';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6 style="opacity: 0;">.</h6>' +
             '<button type="button" id="remove_layer" class="btn btn-danger form-control-file">Remove</button>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-3 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Text</h6>' +
             '<textarea style="height: 80px" class="form-control" name="text_' + id + '[]">' + layer.text +
             '</textarea>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-3 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Effects</h6>' +
             '<textarea style="height: 80px" class="form-control" name="txt_effect_' + id + '[]">' + layer.Effects +
             '</textarea>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-3 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Font Family</h6>' +
             '<input class="form-control" list="font_list" type="text" name="font_family_' + id +
             '[]" autocomplete="on" style="color: #00000000; -webkit-text-fill-color: #000000; caret-color: #000000" value="' +
             layer.font + '" required>' +
             '</div>' +
             '</div>';



         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Alignment</h6>' +
             '<select class="selectpicker form-control" data-style="btn-outline-primary" id="txt_align_' + index + '_' +
             id + '" name="txt_align_' + id + '[]" required>' +
             '@foreach ($dataArray['txt_align'] as $txt)' +
             '   <option value="{{ $txt->value }}" selected="">{{ $txt->type }}</option>' +
             '@endforeach' +
             '</select>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Size</h6>' +
             '<input class="form-control" type="textname" name="txt_size_' + id + '[]" value="' + layer.size +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Color</h6>' +
             '<input class="form-control" type="textname" name="txt_color_' + id + '[]" value="' + layer.color +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Width</h6>' +
             '<input class="form-control" type="textname" name="txt_width_' + id + '[]" value="' + layer.width +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Height</h6>' +
             '<input class="form-control" type="textname" name="txt_height_' + id + '[]" value="' + layer.height +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Scale X</h6>' +
             '<input class="form-control" type="textname" name="txt_scale_x_' + id + '[]" value="1" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Scale Y</h6>' +
             '<input class="form-control" type="textname" name="txt_scale_y_' + id + '[]" value="1" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>X Pos</h6>' +
             '<input class="form-control" type="textname" name="txt_x_pos_' + id + '[]" value="' + layer.left +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Y Pos</h6>' +
             '<input class="form-control" type="textname" name="txt_y_pos_' + id + '[]" value="' + layer.top +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Line Space</h6>' +
             '<input class="form-control" type="textname" name="line_spacing_' + id + '[]" value="' + layer.spacing
             .line + '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-2 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Line Space Multiplier</h6>' +
             '<input class="form-control" type="textname" name="lineSpaceMultiplier_' + id + '[]" value="' + layer
             .spacing.lineMultiplier + '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Word Space</h6>' +
             '<input class="form-control" type="textname" name="word_spacing_' + id + '[]" value="' + layer.spacing
             .letter + '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Curve</h6>' +
             '<input class="form-control" type="textname" name="txt_curve_' + id + '[]" value="' + layer.curve +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Rotation</h6>' +
             '<input class="form-control" type="textname" name="txt_rotation_' + id + '[]" value="' + layer.rotation +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Opacity</h6>' +
             '<input class="form-control" type="textname" name="txt_opacity_' + id + '[]" value="' + layer.opacity +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Editable</h6>' +
             '<div class="col-sm-20">' +
             '<select class="selectpicker form-control" data-style="btn-outline-primary" name="is_editable_' + id +
             '[]" required>' +
             '<option value="0" selected>FALSE</option>' +
             '<option value="1">TRUE</option>' +
             '</select>' +
             '</div>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-2 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Editable Title</h6>' +
             '<select class="custom-select2 form-control" data-style="btn-outline-primary" name="editable_title_' + id +
             '[]">' +
             '<option value="null" selected="">=Select Editable Title=</option>' +
             '@foreach ($dataArray['editable_mode'] as $editable_mode)' +
             '<option value="{{ $editable_mode->name }}">{{ $editable_mode->name }} ({{ $editable_mode->brand_id }})</option>' +
             '@endforeach' +
             '</select>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Url</h6>' +
             '<div class="col-sm-20">' +
             '<select class="selectpicker form-control" data-style="btn-outline-primary" name="is_url_' + id +
             '[]" required>' +
             '<option value="0" selected>FALSE</option>' +
             '<option value="1">TRUE</option>' +
             '</select>' +
             '</div>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Update Text</h6>' +
             '<div class="col-sm-20">' +
             '<select class="selectpicker form-control txt_update_class" data-style="btn-outline-primary" name="txt_update_' +
             id + '[]" disabled="true">' +
             '<option value="0">FALSE</option>' +
             '<option value="1" selected>TRUE</option>' +
             '</select>' +
             '</div>' +
             '</div>' +
             '</div>';


         $('#layers_container_' + id).append(html);


         $('#txt_align_' + index + '_' + id).val(layer.format.alignment);
     }

     function add_json_sticker_layer(id, index, layer, st_images) {

         var imgFile;
         for (var i = 0; i < st_images.files.length; i++) {
             if (st_images.files[i].name === layer.image) {
                 imgFile = st_images.files[i];
             }
         }

         html = '<div class="row">';

         html += '<input class="form-control" type="textname" name="layerType_' + id +
             '[]" value="1" style="display: none">';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6 style="opacity: 0;">.</h6>' +
             '<button type="button" id="remove_layer" class="btn btn-danger form-control-file">Remove</button>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Width</h6>' +
             '<input class="form-control" type="textname" name="st_width_' + id + '[]" value="' + layer.width +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Height</h6>' +
             '<input class="form-control" type="textname" name="st_height_' + id + '[]" value="' + layer.height +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Scale X</h6>' +
             '<input class="form-control" type="textname" name="st_scale_x_' + id + '[]" value="1" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Scale Y</h6>' +
             '<input class="form-control" type="textname" name="st_scale_y_' + id + '[]" value="1" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>X Pos</h6>' +
             '<input class="form-control" type="textname" name="st_x_pos_' + id + '[]" value="' + layer.left +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Y Pos</h6>' +
             '<input class="form-control" type="textname" name="st_y_pos_' + id + '[]" value="' + layer.top +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Rotation</h6>' +
             '<input class="form-control" type="textname" name="st_rotation_' + id + '[]" value="' + layer.rotation +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Opacity</h6>' +
             '<input class="form-control" type="textname" name="st_opacity_' + id + '[]" value="' + layer.opacity +
             '" required>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Type</h6>' +
             '<select class="selectpicker form-control" data-style="btn-outline-primary" id="st_type_' + index + '_' +
             id + '" name="st_type_' + id + '[]" required>' +
             '@foreach ($dataArray['sticker_mode'] as $sticker)' +
             '<option value="{{ $sticker->value }}">{{ $sticker->type }}</option>' +
             '@endforeach' +
             '</select>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Color</h6>' +
             '<input class="form-control" type="textname" name="st_color_' + id + '[]" value="' + layer.color + '">' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Resize</h6>' +
             '<select class="selectpicker form-control" data-style="btn-outline-primary" id="st_resize_' + index + '_' +
             id + '" name="st_resize_' + id + '[]" required>' +
             '@foreach ($dataArray['resize_mode'] as $resize)' +
             '<option value="{{ $resize->value }}">{{ $resize->type }}</option>' +
             '@endforeach' +
             '</select>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Lock Type</h6>' +
             '<select class="selectpicker form-control" data-style="btn-outline-primary" id="st_lock_type_' + index +
             '_' + id + '" name="st_lock_type_' + id + '[]" required>' +
             '@foreach ($dataArray['lock_type'] as $type)' +
             '<option value="{{ $type->value }}">{{ $type->type }}</option>' +
             '@endforeach' +
             '</select>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-2 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Sticker Image</h6>' +
             '<input type="file" class="form-control" id="st_image0_' + index + '_' + id + '" name="st_image_' + id +
             '[]" required>' +
             '<br>' +
             '<img id="st_image_' + index + '_' + id +
             '" style="max-width: 100px; max-height: 100px; width: auto; height: auto"/>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Editable</h6>' +
             '<div class="col-sm-20">' +
             '<select class="selectpicker form-control" data-style="btn-outline-primary" name="st_is_editable_' + id +
             '[]" required>' +
             '<option value="0" selected>FALSE</option>' +
             '<option value="1">TRUE</option>' +
             '</select>' +
             '</div>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-2 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Editable Title</h6>' +
             '<select class="custom-select2 form-control" data-style="btn-outline-primary" name="st_editable_title_' +
             id + '[]">' +
             '<option value="null" selected="">=Select Editable Title=</option>' +
             '@foreach ($dataArray['editable_mode'] as $editable_mode)' +
             '<option value="{{ $editable_mode->name }}">{{ $editable_mode->name }} ({{ $editable_mode->brand_id }})</option>' +
             '@endforeach' +
             '</select>' +
             '</div>' +
             '</div>';

         html += '<div class="col-md-1 col-sm-12">' +
             '<div class="form-group">' +
             '<h6>Url</h6>' +
             '<div class="col-sm-20">' +
             '<select class="selectpicker form-control" data-style="btn-outline-primary" name="st_is_url_' + id +
             '[]" required>' +
             '<option value="0" selected>FALSE</option>' +
             '<option value="1">TRUE</option>' +
             '</select>' +
             '</div>' +
             '</div>' +
             '</div>';

         html += '<hr size="8" width="100%" color="black">';
         html += '</div>';
         $('#layers_container_' + id).append(html);

         let fileList = new DataTransfer();
         fileList.items.add(imgFile);
         let fileInputElement = document.getElementById('st_image0_' + index + '_' + id);
         fileInputElement.files = fileList.files;

         $('#st_image_' + index + '_' + id).attr('src', URL.createObjectURL(imgFile));
         $('#st_type_' + index + '_' + id).val(layer.type);
         $('#st_resize_' + index + '_' + id).val(layer.resizeType);
         $('#st_lock_type_' + index + '_' + id).val(layer.lockType);
     }

     //@formatter:on
     function hideFields() {
         var main_loading_screen = document.getElementById("main_loading_screen");
         main_loading_screen.style.display = "none";
     }

     $(document).ready(function() {
         $('#newKeywordsCols .bootstrap-tagsinput input[type="text"]').attr("list", "keywordsList");
         $('#newKeywordsCols .bootstrap-tagsinput input[type="text"]').attr("style",
             "width: 100%; height: 45px; border: 1px solid rgb(0, 0, 0); border-radius: 5px; margin-top: 5px;"
             );

         if ($("input[name='new_category_id']").val() != "" && $("input[name='new_category_id']").val() != "0") {
             loadNewSearchKeywords($("input[name='new_category_id']").val())
         }
     });

     const loadNewSearchKeywords = (newCatId) => {
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         $.ajax({
             url: "{{ route('getNewSearchTag') }}",
             type: 'POST',
             data: {
                 cateId: newCatId
             },
             beforeSend: function() {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 if (data.error) {
                     window.alert('error==>' + data.error);
                 } else {
                     if (data.success) {
                         $("#related_new_tag_list").html("");
                         var newSearchTags = data.success;
                         $("#keywordsList").empty();
                         newSearchTags.forEach(tag => {
                             $("#keywordsList").append(`<option value="${tag.name}"></option>`);
                         });

                     }
                 }
             },
             error: function(error) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 window.alert(error.responseText);
             },
         })
     }

     document.getElementById('orientation').addEventListener('change', function() {
         var orientation = this.value;

         if (sizeArray) {
             $("#sizeInput").html("");
             sizeArray.forEach(size => {
                 var currentOrientation = "portrait";
                 if (size.width_ration == size.height_ration) {
                     currentOrientation = "square";
                 } else if (size.width_ration > size.height_ration) {
                     currentOrientation = "landscape";
                 }
                 // $("#sizeInput").append(`<option value="${size.id}" ${orientation != currentOrientation && 'disabled'}>${size.size_name}</option>`);
                 $("#sizeInput").append(`<option value="${size.id}">${size.size_name}</option>`);
             });
         } else {
             var id = $("input[name='new_category_id']").val();

             if (id != "0") {
                 loadSize(id).val();
             }

         }

     });

     const loadSize = (newCatId) => {
         $("#sizeInput").html("");
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         $.ajax({
             url: "{{ route('getSizeList') }}",
             type: 'POST',
             data: {
                 cateId: newCatId
             },
             beforeSend: function() {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 if (data.error) {
                     window.alert('error==>' + data.error);
                 } else {
                     if (data.success) {
                         sizeArray = data.data;
                         var orientation = $("#orientation").val();
                         sizeArray.forEach(size => {
                             var currentOrientation = "portrait";
                             if (size.width_ration == size.height_ration) {
                                 currentOrientation = "square";
                             } else if (size.width_ration > size.height_ration) {
                                 currentOrientation = "landscape";
                             }
                             // $("#sizeInput").append(`<option value="${size.id}" ${orientation != currentOrientation && 'disabled'}>${size.size_name}</option>`);
                             $("#sizeInput").append(
                                 `<option value="${size.id}">${size.size_name}</option>`);
                         });

                     }
                 }
             },
             error: function(error) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 window.alert(error.responseText);
             },
         })
     }

     const loadTheme = (newCatId) => {
         $("#themeIds").html("");
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         $.ajax({
             url: "{{ route('getThemeList') }}",
             type: 'POST',
             data: {
                 cateId: newCatId
             },
             beforeSend: function() {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 if (data.error) {
                     window.alert('error==>' + data.error);
                 } else {
                     console.log("data -> data :::: ");
                     console.log(data.data);
                     if (data.success) {
                         var themeArray = data.data;
                         console.log("themeArray :::: ");
                         console.log(themeArray);
                         themeArray.forEach(theme => {
                             $("#themeIds").append(
                                 `<option value="${theme.id}">${theme.name}</option>`);
                         });

                     }
                 }
             },
             error: function(error) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 window.alert(error.responseText);
             },
         })
     }

     const loadInterest = (newCatId) => {
         $("#interestIds").html("");
         $.ajaxSetup({
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
             }
         });

         $.ajax({
             url: "{{ route('getInterestList') }}",
             type: 'POST',
             data: {
                 cateId: newCatId
             },
             beforeSend: function() {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "block";
             },
             success: function(data) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 if (data.error) {
                     window.alert('error==>' + data.error);
                 } else {
                     if (data.success) {
                         var interestArray = data.data;
                         interestArray.forEach(interest => {
                             $("#interestIds").append(
                                 `<option value="${interest.id}">${interest.name}</option>`);
                         });

                     }
                 }
             },
             error: function(error) {
                 var main_loading_screen = document.getElementById("main_loading_screen");
                 main_loading_screen.style.display = "none";
                 window.alert(error.responseText);
             },
         })
     }

     $(document).on('click', '#newKeywordsCols input[type="text"]', function() {
         $(".error-message").remove();
         if ($("input[name='new_category_id']").val() == "0") {
             $('#newKeywords').after(
                 '<span class="error-message" style="color:red;">Please select a new category.</span>');
         }
     });

     $(document).on('click', '#parentCategoryInput', function() {
         $("#newCategoryRequiredPopup").hide();
         if ($('.parent-category-input').hasClass('show')) {
             $('.parent-category-input').removeClass('show');
         } else {
             $(".parent-category-input").addClass('show');
         }
     });

     $(document).on("click", ".category", function(event) {
         $(".category").removeClass("selected");
         $(".subcategory").removeClass("selected");
         var id = $(this).data('id');
         $("input[name='new_category_id']").val(id);
         $("#parentCategoryInput span").html($(this).data('catname'));
         $('.parent-category-input').removeClass('show');
         $(this).addClass("selected");

         $("#keywordsList").empty();
         $("#sizeInput").html("");
         $("#themeIds").html("");
         $("#interestIds").html("");
         sizeArray = null;

         if (id) {
             loadNewSearchKeywords(id);
             loadSize(id);
             loadTheme(id);
             loadInterest(id);
         }


     });

     $(document).on("click", ".subcategory", function(event) {
         event.stopPropagation();
         $(".category").removeClass("selected");
         $(".subcategory").removeClass("selected");
         var id = $(this).data('id');
         var parentId = $(this).data('pid');
         $("input[name='new_category_id']").val(id);
         $('.parent-category-input').removeClass('show');
         $("#parentCategoryInput span").html($(this).data('catname'));
         $(this).addClass("selected");

         $("#keywordsList").empty();
         $("#sizeInput").html("");
         $("#themeIds").html("");
         $("#interestIds").html("");

         sizeArray = null;

         if (id) {
             loadNewSearchKeywords(id);
             loadSize(id);
             loadTheme(id);
             loadInterest(id);
         }
     });

     $(document).on('click', function(e) {
         if (!$(e.target).closest('.form-group.category-dropbox-wrap').length) {
             $('.custom-dropdown.parent-category-input.show').removeClass('show');
         }
     });

     $(document).on("click", "li.category.none-option", function() {
         $("input[name='new_category_id']").val("0");
         $('.parent-category-input').removeClass('show');
         $("#parentCategoryInput span").html('== none ==');
     });

     $('#categoryFilter').on('input', function() {
         var filterValue = $(this).val().toLowerCase();
         $('.category, .subcategory').each(function() {
             var text = $(this).text().toLowerCase();
             if (text.indexOf(filterValue) > -1) {
                 $(this).show();
             } else {
                 $(this).hide();
             }
         });
     });
     jQuery.noConflict();

     jQuery(document).ready(function($) {
         $('.col-sm-20.color_tags input[type="text"]').prop('readonly', true);
         $('.col-sm-20.color_tags input[type="text"]').css('min-width', '417px !important');
         $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
         $('.col-sm-20.color_tags input[type="text"]').on('keydown', function(event) {
             event.preventDefault();
             $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
         });
         $('.col-sm-20.color_tags input[type="text"]').on('keyup', function(event) {
             event.preventDefault();
             $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
         });
         $('.col-sm-20.color_tags input[type="text"]').on('keypress', function(event) {
             event.preventDefault();
             $('.col-sm-20.color_tags input[type="text"]').attr("size", "40");
         });



         var colorData = @json($dataArray['item']->color_id);
         const colorIds = colorData ? colorData.split(",") : "";

         for (const colorId of colorIds) {
             $('#colorTags').tagsinput('add', colorId);
             setTagBackgroundColor(colorId);
         }

         var currentTag = null; // To keep track of the current tag being edited
         var currentColor = null;
         // Initialize Spectrum color picker
         $("#colorPicker").spectrum({
             color: "#f00",
             showInput: true,
             showPalette: false,
             showAlpha: true,
             change: function(color) {
                 var colorHex = color.toHexString();
                 // if( currentColor != null){
                 //     var currentColorHex = rgbToHex(currentColor);
                 // }
                 $(currentTag).css('background-color', colorHex);
                 $(currentTag).text(colorHex);

                 if (currentTag == null) {
                     $('#colorTags').tagsinput('remove', $(currentTag).text());
                     $('#colorTags').tagsinput('add', colorHex);
                 } else {
                     var currentColorHex = rgbToHex(currentColor);
                     updateColorCodeValue(currentColorHex)
                 }
                 currentTag = null;
             }
         });

         function rgbToHex(rgb) {

             var result = rgb.match(/\d+/g);
             if (result) {
                 var r = parseInt(result[0]).toString(16).padStart(2, '0');
                 var g = parseInt(result[1]).toString(16).padStart(2, '0');
                 var b = parseInt(result[2]).toString(16).padStart(2, '0');
                 return '#' + r + g + b;
             }
             return null;
         }

         function updateColorCodeValue(currentColorHex) {
             var colorsCode = [];
             $(".color_tags .bootstrap-tagsinput.ui-sortable span.tag.label").each(function(index, val) {
                 console.log($(this).text());
                 colorsCode.push($(this).text())
             });
             // Mapping Color Codes
             var existColorCodeVal = $("input[name='color_ids']").val();
             var colorsCodeString = colorsCode.join(',');
             $("input[name='color_ids']").val(colorsCodeString);

         }
         // Function to set the background color of the last tag
         function setTagBackgroundColor(color) {
             var tagElements = $('.bootstrap-tagsinput .tag');
             var lastTagElement = tagElements[tagElements.length - 1];
             $(lastTagElement).css('background-color', color);
         }

         // Initialize tags input
         $('#colorTags').tagsinput({
             confirmKeys: [13, 32, 188]
         });

         // Set background color when a new tag is added
         $('#colorTags').on('itemAdded', function(event) {
             setTagBackgroundColor(event.item);
         });

         // Set initial background colors for tags
         $(".color_tags .bootstrap-tagsinput.ui-sortable span.tag.label").each(function(index, val) {
             $(this).css("background-color", $(this).text());
         });

         // Event handler for tag click to open color picker
         $(document).on('click', '.color_tags .bootstrap-tagsinput .tag', function() {
             currentTag = this;
             currentColor = $(this).css('background-color');
             $("#colorPicker").spectrum("set", currentColor);
             $("#colorPicker").spectrum("show");
         });

         // Restore page index from session storage
         // let pageIndex = sessionStorage.getItem("pageIndex");
         // if (pageIndex) {
         //     $(".li_page_class .nav-link").removeClass("active");
         //     $("#a_link_" + pageIndex).addClass("active");
         // }

         // Make tags sortable
         // $(".bootstrap-tagsinput").sortable({
         //     items: "> .tag",
         //     axis: "x",
         //     containment: "parent",
         //     tolerance: "pointer",
         //     cursor: "move",
         //     start: function(event, ui) {
         //         $(this).find('input').prop('disabled', true);
         //     },
         //     stop: function(event, ui) {
         //         $(this).find('input').prop('disabled', false);
         //     }
         // });


         $(".bootstrap-tagsinput").sortable({
             items: "> .tag",
             axis: "x",
             containment: "parent",
             tolerance: "pointer",
             cursor: "move",
             distance: 5,
             helper: "clone",
             start: function(event, ui) {
                 $(this).find('input').prop('disabled', true);
                 ui.helper.addClass('sorting');
             },
             stop: function(event, ui) {
                 $(this).find('input').prop('disabled', false);
                 $(ui.item).removeClass('sorting');
                 $(this).sortable("refreshPositions");
             },
             sort: function(event, ui) {
                 ui.helper.css('transform', 'scale(1.1)'); // Optional: scale up while dragging
             }
         });

         $(".select2-selection__rendered").sortable({
             placeholder: "ui-state-highlight",
             stop: function(event, ui) {
                 var selectionContainer = $(this);
                 var selectedOptions = selectionContainer.find(".select2-selection__choice");
                 var newOrder = [];
                 selectedOptions.each(function() {
                     var optionValue = $(this).attr("title");
                     newOrder.push(optionValue);
                 });
                 var selectElement = selectionContainer.closest(".custom-select2").find("select");
                 selectElement.val(newOrder).trigger("change");
             }
         }).disableSelection();

         const toTitleCase = str => str.replace(/\b\w+/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1)
             .toLowerCase());
         $("#post_name").on("input", function() {
             const strId = $(this).data("strid");
             const postNameString = toTitleCase($(this).val());
             $("#id_name").val(`${strId}-${postNameString.toLowerCase().replace(/\s+/g, '-')}`);
             $(this).val(postNameString);
         });
     });

     // $(document).on("click",".li_page_class",function(){
     //     let page = $(this).data("page");
     //     sessionStorage.setItem("pageIndex", page);
     // });
     $('#arrowIcon').on('click', function() {
         if ($("#suggestionsNewContainer").css('display') == "none") {
             $("#suggestionsNewContainer").show();
         } else {
             $("#suggestionsNewContainer").hide();
         }
     });

     $(document).on("click", "div#suggestionsNewContainer ul li", function() {
         var $input = $('#newKeywordsCols input[type="text"]');
         var newWord = $(this).text().trim();
         var currentKeywords = $("#newKeywords").val().trim();
         var keywordsArray = currentKeywords ? currentKeywords.split(',').map(function(keyword) {
             return keyword.trim();
         }) : [];

         if (!keywordsArray.includes(newWord)) {
             keywordsArray.push(newWord);
         }
         var updatedKeywords = keywordsArray.join(', ');
         $("#newKeywords").val(updatedKeywords);
         $input.val(updatedKeywords);
         $("div#suggestionsNewContainer").hide();

     });
 </script>

 </body>

 </html>
