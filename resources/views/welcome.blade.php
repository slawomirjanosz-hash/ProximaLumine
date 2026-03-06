<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>ProximaLumine</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
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
            $companyLogo = $companySettings && !empty($companySettings->logo) ? $companySettings->logo : '/logo.png';
        } catch (\Exception $e) {
            $companyName = 'Moja Firma';
            $companyLogo = '/logo.png';
        }
    @endphp

    <div class="flex flex-col items-center mb-4">
        <img src="{{ $companyLogo }}" alt="Logo firmy" class="mb-2" style="width: auto !important; height: auto !important; max-width: 220px !important; max-height: 110px !important; object-fit: contain; display: block;">
        <p class="text-gray-600 mb-8">System zarządzania</p>
    </div>

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
            @if(Auth::user()->email === 'proximalumine@gmail.com' || Auth::user()->is_admin || Auth::user()->can_audits)
                <a href="{{ route('audits') }}" class="inline-block px-6 py-3 text-white rounded text-lg min-w-[220px]" style="background-color: #38bdf8; color: #ffffff;" onmouseover="this.style.backgroundColor='#0ea5e9'" onmouseout="this.style.backgroundColor='#38bdf8'">
                    📝 Audyty
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
