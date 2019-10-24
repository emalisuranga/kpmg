<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Oswald:100,500" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto:100,500" rel="stylesheet">
        @yield('style')
    </head>
 <body style="font-family: 'Roboto', sans-serif;">
    @yield('content')
    @stack('scripts')
</body>
</html>
