<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>ProximaLumine</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

@include('parts.menu')

<!-- TREŚĆ GŁÓWNA -->
<main class="max-w-6xl mx-auto mt-20 text-center">
    <!-- KOMUNIKATY -->
    @if(session('success'))
        <div class="max-w-md mx-auto mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @php
        try {
            $companySettings = \App\Models\CompanySetting::first();
            $companyName = $companySettings && $companySettings->name ? $companySettings->name : 'Moja Firma';
        } catch (\Exception $e) {
            $companyName = 'Moja Firma';
        }
    @endphp

    <h1 class="text-4xl font-bold mb-4">
        Magazyn {{ $companyName }}
    </h1>

    <p class="text-gray-600 mb-8">
        System zarządzania
    </p>

    @auth
        <div class="flex flex-col gap-4 justify-center items-center">
            @if(Auth::user()->email === 'proximalumine@gmail.com' || Auth::user()->can_view_magazyn)
                <a href="{{ route('magazyn.check') }}"
                   class="inline-block px-6 py-3 bg-blue-600 text-white rounded text-lg hover:bg-blue-700 min-w-[220px]">
                    Wejdź do magazynu
                </a>
            @endif
            @if(Auth::user()->email === 'proximalumine@gmail.com' || Auth::user()->can_view_magazyn)
                <a href="{{ route('magazyn.projects') }}"
                   class="inline-block px-6 py-3 bg-indigo-600 text-white rounded text-lg hover:bg-indigo-700 min-w-[220px]">
                    🗂️ Projekty
                </a>
            @endif
            @if(Auth::user()->email === 'proximalumine@gmail.com' || Auth::user()->can_view_offers)
                <a href="{{ route('offers') }}" class="inline-block px-6 py-3 bg-green-600 text-white rounded text-lg hover:bg-green-700 min-w-[220px]">
                    Wyceny i Oferty
                </a>
            @endif
            @if(Auth::user()->email === 'proximalumine@gmail.com' || Auth::user()->can_view_recipes)
                <a href="{{ route('receptury') }}" class="inline-block px-6 py-3 bg-purple-700 text-white rounded text-lg hover:bg-purple-800 min-w-[220px]">
                    Receptury
                </a>
            @endif
            @if(Auth::user()->email === 'proximalumine@gmail.com' || Auth::user()->can_crm)
                <a href="{{ route('crm') }}" class="inline-block px-6 py-3 bg-purple-600 text-white rounded text-lg hover:bg-purple-700 min-w-[220px]">
                    👥 CRM
                </a>
            @endif
        </div>
    @else
        <a href="{{ route('login') }}"
           class="inline-block px-6 py-3 bg-green-600 text-white rounded text-lg hover:bg-green-700">
            Zaloguj się
        </a>
    @endauth
</main>
</body>
</html>
