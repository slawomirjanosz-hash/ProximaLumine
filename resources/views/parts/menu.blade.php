<header class="bg-white shadow">
    <div class="mx-auto px-6 py-4 flex items-center justify-between">

        <!-- LEWA STRONA: LOGO + NAZWA -->
        <div class="flex items-center gap-4">
            @php
                $companySettings = \App\Models\CompanySetting::first();
                $logoPath = $companySettings && $companySettings->logo ? asset('storage/' . $companySettings->logo) : '/logo.png';
                $companyName = $companySettings && $companySettings->name ? $companySettings->name : 'Magazyn 3C Automation';
            @endphp
            <img src="{{ $logoPath }}" alt="{{ $companyName }}" class="h-10">
            <span class="text-xl font-bold">
                {{ $companyName }}
            </span>
        </div>

        <!-- PRAWA STRONA: MENU -->
        <nav class="flex gap-2 items-center flex-wrap justify-end">
            <a href="{{ url('/') }}"
               class="px-3 py-2 text-sm bg-gray-200 rounded whitespace-nowrap">
                Start
            </a>

            <a href="{{ route('magazyn.check') }}"
               class="px-3 py-2 text-sm bg-gray-200 text-black rounded whitespace-nowrap">
                🔍Katalog
            </a>

            <a href="{{ route('magazyn.remove') }}"
               class="px-3 py-2 text-sm bg-gray-200 text-black rounded whitespace-nowrap">
                ➖Pobierz
            </a>

            <a href="{{ route('magazyn.add') }}"
               class="px-3 py-2 text-sm bg-gray-200 text-black rounded whitespace-nowrap">
                ➕Dodaj
            </a>

            <a href="{{ route('magazyn.orders') }}"
               class="px-3 py-2 text-sm bg-gray-200 text-black rounded whitespace-nowrap">
                📦Zamówienia
            </a>

            <a href="{{ route('magazyn.settings') }}"
               class="px-3 py-2 text-sm bg-gray-200 text-black rounded whitespace-nowrap">
                ⚙️Ustawienia
            </a>

            @auth
                <div class="border-l border-gray-300 pl-2 flex items-center gap-2">
                    <span class="text-gray-700 text-sm whitespace-nowrap">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded transition whitespace-nowrap">
                            Wyloguj
                        </button>
                    </form>
                </div>
            @endauth
        </nav>

    </div>
</header>

