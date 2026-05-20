 
   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
    <div id="main_loading_screen" style="display: none;">
        <div id="loader-wrapper">
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
    </div>

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="bg-white border-radius-4 box-shadow mb-30">
                <div class="row no-gutters">
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <div class="chat-list bg-light-gray">
                            <div class="chat-search">
                                <span class="ti-search"></span>
                                <input type="text" placeholder="Search Contact" />
                            </div>
                            <div id="user_list" class="notification-list chat-notification-list customscroll">
                                <ul>
                                </ul>
                                <div class="spinner"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-8 col-sm-12">
                        <div class="chat-detail">
                            <div class="chat-profile-header clearfix">
                                <div class="left">
                                    <div class="clearfix">
                                        <input type="text" id="user_id" style="display: none;">
                                        <div class="chat-profile-photo">
                                            <img id="user_img" src="vendors/images/profile-photo.jpg" alt="" />
                                        </div>
                                        <div class="chat-profile-name">
                                            <h3 id="user_name">Rachel Curtis</h3>
                                            <span id="user_contact">New York, USA</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="right text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-outline-primary dropdown-toggle" href="#" role="button"
                                            data-toggle="dropdown">
                                            Setting
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#">Export Chat</a>
                                            <a class="dropdown-item" href="#">Search</a>
                                            <a class="dropdown-item text-light-orange" href="#">Delete Chat</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-box">

                                <div class="chat-desc customscroll">
                                    <ul id="chathistory"></ul>
                                </div>
                                <div class="chat-footer">
                                    <div class="file-upload">
                                        <a><label for="file-input">
                                                <i class="fa fa-paperclip" for="file-input"></i>
                                            </label></a>
                                        <input id="file-input" type="file" onchange="previewFile(this);"
                                            style="display: none;" />
                                    </div>
                                    <div class="chat_text_area">
                                        <textarea placeholder="Type your message…" id="admin_msg"></textarea>
                                    </div>
                                    <div class="chat_send">
                                        <button onclick="sendReply()" class="btn btn-link" type="submit">
                                            <i class="icon-copy ion-paper-airplane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="show_contact_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Contact</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <div class="modal-body" id="contact_model">
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')
<script>
    $(document).ready(function() {
        var page = 1;

        function fetchContacts() {
            $('.spinner').css('display', 'block');
            $.ajax({
                url: '{{ route('show_contacts') }}',
                type: 'GET',
                data: {
                    page: page
                },
                success: function(response) {

                    if (response.contacts.length > 0) {
                        $.each(response.contacts, function(index, contactRow) {
                            let userEmailMobileData = '';
                            if (contactRow.user.number == null) {
                                userEmailMobileData = contactRow.user.email;
                            } else {
                                userEmailMobileData = contactRow.user.country_code + " " +
                                    contactRow.user.number;
                            }
                            let row = `<li class="nonactiveclass" id="${contactRow.user.uid}">
                                                    <a onclick="user_history('${contactRow.user.uid}', '${contactRow.user.photo_uri}', '${contactRow.user.name}', '${contactRow.user.country_code}', '${contactRow.user.number}', '${contactRow.user.email}')" href="#">
                                                        <img src="{{ config('filesystems.storage_url') }}${contactRow.user.photo_uri}" alt="" />
                                                        <h3 class="clearfix">${contactRow.user.name}</h3>
                                                        <p>${userEmailMobileData}</p>
                                                    </a>
                                                </li>`;
                            $('#user_list ul').append(row);
                        });
                        $('#user_list ul li.last-item').removeClass('last-item');
                        $('#user_list ul li:last-child').addClass('last-item');

                        setTimeout(() => {
                            console.log("ppppp");
                            $('.spinner').css('display', 'none');
                            $('#user_list').scrollTop(0);
                        }, 800);
                        page++;
                    } else {
                        $('.spinner').css('display', 'none');
                    }
                }
            });
        }
        fetchContacts();

        function checkLastItemVisibility() {
            var lastLi = $('#user_list ul li.last-item');
            var lastLiOffset = lastLi.offset().top;
            var scrollTop = $('#user_list').scrollTop();
            var windowHeight = $(window).height();
            if ((lastLiOffset - scrollTop) < windowHeight) {
                lastLi.removeClass('last-item');

                fetchContacts();
            }
        }
        setInterval(checkLastItemVisibility, 5000);
    });
</script>

<script>
    function user_history($user_id, $photo_uri, $uname, $country_code, $number, $email) {
        $('#user_id').val($user_id);
        document.getElementById('user_name').innerHTML = $uname;
        if (!$number || $number == null || $number === '' || $number === 'null') {
            document.getElementById('user_contact').innerHTML = $email;
        } else {
            document.getElementById('user_contact').innerHTML = $country_code + " " + $number;
        }
        $("#user_img").attr("src", $photo_uri);
        $('.nonactiveclass').removeClass("active");
        $('#' + $user_id).addClass("active");

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{ route('user.getChatData') }}",
            method: "POST",
            data: {
                user_id: $user_id,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                var main_loading_screen = document.getElementById("main_loading_screen");
                main_loading_screen.style.display = "block";
            },
            success: function(result) {
                hideFields();
                if (result.error) {
                    window.alert(result.error);
                } else {
                    $('#chathistory').html(result);
                    setTimeout(() => {
                        var lastLi = $("li.clearfix.admin_chat:last");
                        var lastLiOffset = lastLi.offset().top;
                        $('html, body').animate({
                            scrollTop: lastLiOffset
                        }, 'slow');
                    }, 2000);
                }

            },
            error: function(result) {
                hideFields();
                window.alert(result.responseText);
            }
        })
    }

    function previewFile(input) {
        var file = $("input[type=file]").get(0).files[0];
        if (file) {
            var id = $('#user_id').val();
            if (id === '') {
                $('#file-input').val('');
                window.alert("First select user");
            } else {
                var formData = new FormData();
                formData.append('user_id', id);
                formData.append('is_file', '1');
                formData.append('photo_uri', file);

                $.ajax({
                    url: 'send_reply',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        var main_loading_screen = document.getElementById("main_loading_screen");
                        main_loading_screen.style.display = "block";
                    },
                    success: function(data) {
                        hideFields();
                        if (data.error) {
                            window.alert('error==>' + data.error);
                        } else {
                            $('#chathistory').html(data);
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
            }
        }
    }

    function sendReply() {
        var id = $('#user_id').val();
        if (id === '') {
            window.alert("First select user");
        } else {
            var admin_msg = $('#admin_msg').val();
            admin_msg = admin_msg.trimStart();
            admin_msg = admin_msg.trimEnd();
            if (admin_msg === '') {
                window.alert("Type Something");
            } else {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    }
                });
                var formData = new FormData();
                formData.append('user_id', id);
                formData.append('message', admin_msg);
                formData.append('is_file', '0');
                formData.append('width', '0');
                formData.append('height', '0');

                $.ajax({
                    url: 'send_reply',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        var main_loading_screen = document.getElementById("main_loading_screen");
                        main_loading_screen.style.display = "block";
                    },
                    success: function(data) {
                        hideFields();
                        if (data.error) {
                            window.alert('error==>' + data.error);
                        } else {
                            $('#chathistory').html(data);
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

            }
        }
    }

    function hideFields() {
        $('#admin_msg').val('');
        $('#file-input').val('');
        var main_loading_screen = document.getElementById("main_loading_screen");
        main_loading_screen.style.display = "none";
    }
</script>

</body>

</html>
