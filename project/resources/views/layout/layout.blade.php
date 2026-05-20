<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @yield('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .h3, h3 {
            font-size: calc(1.3rem + .6vw) !important;
        }
        @media (min-width: 1200px){
            .h3, h3 {
                font-size: 1.75rem !important;
            }
        }

        select#selectPageStatus {
            width: auto;
            max-width: 320px;
            position: absolute;
            right: 90px;
        }
        select#selectPageType {
            width: auto;
            max-width: 320px;
            position: absolute;
            right: 207px;
        }
    </style>
</head>
<body>
    @auth
    <div class="py-3 border-bottom">
        <div class="row justify-content-between mx-4 align-items-center">
            <div class="col-6">
                <a href="{{ route('pages.list') }}"><img src="{{ asset('assets/logo.png') }}" width="150" class="img-fluid" height="100" alt=""></a>
            </div>
            <div class="col-6 text-end dropdown">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <form method="POST" action="{{ route('logout.user') }}">
                            @csrf
                            <input type="submit" value="Logout" class="btn w-100">
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endauth
    @yield('content')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('js')
    <script type='text/javascript'>
        window.addEventListener("pageshow", function (event) {
            var perfEntries = performance.getEntriesByType("navigation");
            if (perfEntries[0].type === "back_forward") {
                location.reload(true);
            }
        });
    </script>
</body>
</html>
