<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Not Found</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/styles/core.css')}}">
        <link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/styles/style.css')}}">
    </head>
    <body class="antialiased">

        <div class="error-page d-flex align-items-center flex-wrap justify-content-center pd-20">
            <div class="pd-10">
                <div class="error-page-wrap text-center">
                    <h1>404</h1>
                    <h3>Error: 404 Page Not Found</h3>
                    <p>Sorry, the page you’re looking for cannot be accessed.<br>Either check the URL</p>
                    <div class="pt-20 mx-auto max-width-200">
                        <a href="{{route('dashboard')}}" class="btn btn-primary btn-block btn-lg">Back To Home</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
