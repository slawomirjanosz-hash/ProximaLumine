<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Magazyn')</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="h-10">
                <span class="text-xl font-bold">Magazyn</span>
            </div>
        </div>
    </header>
    <main class="flex-1">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
