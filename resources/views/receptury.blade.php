<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Receptury - System Zarządzania</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-6xl mx-auto mt-6">
    <a href="/" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow transition-all text-gray-700 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Powrót
    </a>
</div>

<!-- GŁÓWNA TREŚĆ -->
<main class="max-w-7xl mx-auto mt-8 px-6">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="text-4xl font-bold mb-8">System Zarządzania Recepturami</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Katalog składników -->
        <a href="{{ route('recipes.ingredients') }}" class="block p-8 bg-white rounded-lg shadow hover:shadow-lg transition">
            <div class="text-5xl mb-4">📦</div>
            <h2 class="text-2xl font-bold mb-2">Katalog Składników</h2>
            <p class="text-gray-600">Zarządzaj składnikami dostępnymi do receptur</p>
        </a>

        <!-- Lista receptur -->
        <a href="{{ route('recipes.index') }}" class="block p-8 bg-white rounded-lg shadow hover:shadow-lg transition">
            <div class="text-5xl mb-4">📋</div>
            <h2 class="text-2xl font-bold mb-2">Lista Receptur</h2>
            <p class="text-gray-600">Przeglądaj i zarządzaj recepturami</p>
        </a>

        <!-- Lista procesów -->
        <a href="{{ route('processes.index') }}" class="block p-8 bg-white rounded-lg shadow hover:shadow-lg transition">
            <div class="text-5xl mb-4">⚙️</div>
            <h2 class="text-2xl font-bold mb-2">Lista Procesów</h2>
            <p class="text-gray-600">Przeglądaj procesy produkcyjne</p>
        </a>
    </div>
</main>

</body>
</html>
