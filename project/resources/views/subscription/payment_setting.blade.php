   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" enctype="multipart/form-data">

                    <span id="result"></span>

                    @csrf
                    <div class="row">

                    	<div class="col-md-4 col-sm-12">
	                    	<div class="form-group">
                                <h6>RazorPay</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary" name="razorpay_status">
                                    @if(isset($payment->razorpay_status) && $payment->razorpay_status =='0')
                                        <option value="0" selected>Disabled</option>
                                        <option value="1">Enabled</option>
                                    @else
                                        <option value="0">Disabled</option>
                                        <option value="1" selected>Enabled</option>
                                    @endif
                                </select>
                            </div>
		                </div>

		                <div class="col-md-4 col-sm-12">
	                    	<div class="form-group">
                                <h6>Stripe</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary" name="stripe_status">
                                    @if( isset($payment->stripe_status) && $payment->stripe_status =='0')
                                        <option value="0" selected>Disabled</option>
                                        <option value="1">Enabled</option>
                                    @else
                                        <option value="0">Disabled</option>
                                        <option value="1" selected>Enabled</option>
                                    @endif
                                </select>
                            </div>
		                </div>

		                <div class="col-md-4 col-sm-12">
	                    	<div class="form-group">
                                <h6>Paypal</h6>
                                <select class="selectpicker form-control" data-style="btn-outline-primary" name="paypal_status">
                                    @if( isset($payment->paypal_status) && $payment->paypal_status =='0')
                                        <option value="0" selected>Disabled</option>
                                        <option value="1">Enabled</option>
                                    @else
                                        <option value="0">Disabled</option>
                                        <option value="1" selected>Enabled</option>
                                    @endif
                                </select>
                            </div>
		                </div>
                    </div>
                    <br/>

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
	                    	<div class="form-group">
	                        	<h6>Razorpay Key ID</h6>
		                        <input class="form-control" type="textname" name="razorpay_ki" value="{{(isset($payment->razorpay_ki)) ? $payment->razorpay_ki : ''}}" required>
		                    </div>
		                </div>

		                <div class="col-md-6 col-sm-12">
		                    <div class="form-group">
		                        <h6>Razorpay Secret Key</h6>
		                        <input class="form-control" type="textname" name="razorpay_ck" value="{{(isset($payment->razorpay_ck)) ? $payment->razorpay_ck : ''}}"
		                               required>
		                    </div>
		                </div>

		            </div>

		            <br/>

		            <div class="row">

		                <div class="col-md-5 col-sm-12">
		                    <div class="form-group">
		                        <h6>Stripe Secret Key</h6>
		                        <input class="form-control" type="textname" name="stripe_sk" value="{{(isset($payment->stripe_sk)) ? $payment->stripe_sk : ''}}"
		                               required>
		                    </div>
		                </div>

		                <div class="col-md-5 col-sm-12">
		                    <div class="form-group">
		                        <h6>Stripe Pulish Key</h6>
		                        <input class="form-control" type="textname" name="stripe_pk" value="{{(isset($payment->stripe_pk)) ? $payment->stripe_pk : ''}}"
		                               required>
		                    </div>
		                </div>

		                <div class="col-md-2 col-sm-12">
		                    <div class="form-group">
		                        <h6>Stripe Version</h6>
		                        <input class="form-control" type="textname" name="stripe_ver" value="{{( isset($payment->stripe_ver) ) ? $payment->stripe_ver : ''}}"
		                               required>
		                    </div>
		                </div>

		            </div>

		            <br/>

		            <div class="row">

		                <div class="col-md-6 col-sm-12">
		                    <div class="form-group">
		                        <h6>Paypal Client ID</h6>
		                        <input class="form-control" type="textname" name="paypal_ci" value="{{(isset($payment->paypal_ci)) ? $payment->paypal_ci : ''}}"
		                               required>
		                    </div>
		                </div>

		                <div class="col-md-6 col-sm-12">
		                    <div class="form-group">
		                        <h6>Paypal Secret Key</h6>
		                        <input class="form-control" type="textname" name="paypal_sk" value="{{(isset($payment->paypal_sk)) ? $payment->paypal_sk :  ''}}"
		                               required>
		                    </div>
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
    $('#dynamic_form').on('submit', function (event) {
        event.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var formData = new FormData(this);
        var url = "{{route('payment.update',[$payment->id])}}";
        $.ajax({
            url: url,
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
