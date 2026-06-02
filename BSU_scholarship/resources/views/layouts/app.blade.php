<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Scholarship Portal | Batangas State University')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/lugo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">

    <div id="app">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
