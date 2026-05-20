   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container designer-access-container">
  
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" class="designer-access-container" enctype="multipart/form-data">

                    <span id="result"></span>

                    @csrf

                    <div class="form-group">
                        <h6>Font Name</h6>
                        <input class="form-control" type="textname" id="font_name" name="font_name"
                            value="{{ $appArray->name }}" required>
                    </div>
                    <div class="form-group">
                        <h6>Font Thumb</h6>
                        <input type="file" class="form-control-file form-control height-auto" id="font_thumb"
                            name="font_thumb"><br />
                        <img src="{{ config('filesystems.storage_url') }}{{ $appArray->thumb }}" width="100" />
                        <input class="form-control" type="textname" id="thumb_path" name="thumb_path"
                            value="{{ $appArray->thumb }}" style="display: none">
                    </div>

                    <div class="form-group">
                        <h6>Font File</h6>
                        <input type="file" class="form-control-file form-control height-auto" id="font_file"
                            name="font_file" <label> {{ $appArray->path }} </label>
                    </div>

                    <div class="form-group">
                        <h6>Uniname</h6>
                        <div class="col-sm-20">
                            <select class="custom-select2 form-control" data-style="btn-outline-primary" name="uniname"
                                style="width: 100%;" value="{{ $appArray->uniname }}">
                                @php
                                    $first = $appArray->uniname == 'none' ? 'selected' : '';
                                    $second = $appArray->uniname == 'kap' ? 'selected' : '';
                                    $third = $appArray->uniname == 'krutidev' ? 'selected' : '';
                                @endphp
                                <option value="none" {{ $first }}>None</option>
                                <option value="kap" {{ $second }}>Kap</option>
                                <option value="krutidev" {{ $third }}>Krutidev</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <h6>Status</h6>
                        <div class="col-sm-20">
                            <select class="selectpicker form-control" data-style="btn-outline-primary" id="status"
                                name="status">
                                @if ($appArray->status == '1')
                                    <option value="1" selected>LIVE</option>
                                    <option value="0">NOT LIVE</option>
                                @else
                                    <option value="1">LIVE</option>
                                    <option value="0" selected>NOT LIVE</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div>
                        <input class="btn btn-primary" type="submit" name="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
<script>
    $('#dynamic_form').on('submit', function(event) {
        event.preventDefault();


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);

        var url = "{{ route('font.update', [$appArray->id]) }}";
        $.ajax({
            url: url,
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
                    $('#result').html('<div class="alert alert-danger">' + data.error + '</div>');
                } else {
                    window.location.replace("../show_fonts");
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
