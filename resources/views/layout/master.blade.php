<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('img/favicon/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('img/favicon/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('img/favicon/favicon-16x16.png')}}">
    <link rel="manifest" href="{{asset('img/favicon/site.webmanifest')}}">
    <link rel="mask-icon" href="{{asset('img/favicon/safari-pinned-tab.svg')}}" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#292929">

    <title>
        @yield('title', 'ProScholy.cz - chytrý křesťanský zpěvník')
    </title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

    <!-- Import Google Icon Font -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Fonts awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/solid.css"
          integrity="sha384-wnAC7ln+XN0UKdcPvJvtqIH3jOjs9pnKnq9qX68ImXvOGz2JuFoEiCjT8jyZQX2z" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/regular.css"
          integrity="sha384-zkhEzh7td0PG30vxQk1D9liRKeizzot4eqkJ8gB3/I+mZ1rjgQk+BSt2F6rT2c+I" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/fontawesome.css"
          integrity="sha384-HbmWTHay9psM8qyzEKPc8odH4DsOuzdejtnr+OFtDmOcIVnhgReQ4GZBH7uwcjf6" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/brands.css"
          integrity="sha384-nT8r1Kzllf71iZl81CdFzObMsaLOhqBU1JD2+XoAALbdtWaXDOlWOZTR4v1ktjPE" crossorigin="anonymous">

    {{-- CSS --}}
    @yield('app-css')

    @yield('google-analytics')

    @stack('head_links')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark static-top justify-content-between">
        <div>
            <a class="navbar-brand" href="@yield('navbar-brand_href', '/')"><img src="{{asset('img/logo_v2.png')}}" width="60" style="padding: 0 10px 0 0;">
             Zpěvník pro scholy</a>
        </div>

        @if (Auth::check())
                <a class="navbar-text" href="{{route('admin.dashboard')}}">
                Přihlášený uživatel: {{ Auth::user()->name }}
                @if (Auth::user()->roles()->count() > 0)
                    ({{Auth::user()->roles()->first()->name}})
                @endif
            </a>
        @endif

        <button class="navbar-toggler" type="button"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" onclick="toggleNavbar()">
            <span class="navbar-toggler-icon"></span>
        </button>

    </nav>

    <div class="container-fluid" id="app">
        <div class="row">
            {{-- Side navbar --}}
            <div class="sidebar bg-dark material-shadow" id="navbarNav">
                @yield('navbar')
            </div>

            {{-- Content --}}
            <div class="content">
                @yield('content')
            </div>
        </div>
    </div>

    {{-- Main JS built with Laravel's mix --}}
    @yield('app-js')
    
    <script>
        // Mobile viewport soft keyboard fix
        setTimeout(function () {
            var viewheight = $(window).height();
            var viewwidth = $(window).width();
            var viewport = $("meta[name=viewport]");
            viewport.attr("content", "height=" + viewheight + "px, width=" +
                viewwidth + "px, initial-scale=1.0");
        }, 300);


        // Navbar toggling
        let navbarState = false;

        function toggleNavbar() {
            console.log(navbarState);

            if (navbarState === false) {
                showNavbar();
            }
            else {
                hideNavbar();
            }
        }

        function showNavbar() {
            navbarState = true;

            $('.sidebar')
                .show()
                .css({position: 'absolute'});
        }

        function hideNavbar() {
            navbarState = false;

            $('.sidebar').hide();
        }
    </script>

    @stack('scripts')
</body>
</html>