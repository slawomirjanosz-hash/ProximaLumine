<!-- Top Bar -->
<div id="app-topbar" class="bg-gradient-to-r from-gray-800 to-gray-900 shadow-lg fixed top-0 left-0 right-0 z-50">
    <div class="px-6 py-3 flex items-center justify-between">
        <!-- Logo i Nazwa Firmy -->
        <div class="flex items-center gap-3">
            @php
                $companySettings = \App\Models\CompanySetting::first();
                if ($companySettings && $companySettings->logo) {
                    if (str_starts_with($companySettings->logo, 'data:image')) {
                        $logoPath = $companySettings->logo;
                    } else {
                        $logoPath = asset('storage/' . $companySettings->logo);
                    }
                } else {
                    $logoPath = '/logo.png';
                }
                $companyName = $companySettings && $companySettings->name ? $companySettings->name : 'ProximaLumine';
            @endphp
            <img src="{{ $logoPath }}" alt="{{ $companyName }}" class="h-8">
            <div>
                <span class="text-white text-lg font-semibold tracking-wide">{{ $companyName }}</span>
                @if(!$companySettings || !$companySettings->name)
                    <span class="block text-xs text-gray-400">(Ustaw dane firmy w Ustawieniach)</span>
                @endif
            </div>
        </div>

        <!-- Czas i Użytkownik -->
        <div class="flex items-center gap-4">
            <span id="datetime" class="text-sm text-gray-300 font-mono"></span>
            @auth
                <div class="flex items-center gap-3 border-l border-gray-600 pl-4">
                    <div class="text-right">
                        <span class="text-white text-sm block">{{ Auth::user()->name }}</span>
                        <span class="text-gray-400 text-xs">{{ Auth::user()->is_admin ? 'Administrator' : 'Użytkownik' }}</span>
                    </div>
                    <span class="px-2 py-1 text-xs bg-green-600 text-white rounded shadow" title="Aktualnie zalogowani">
                        👤 {{ \App\Helpers\UserHelper::getOnlineUsersCount() }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition">
                            Wyloguj
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</div>

<!-- Sidebar -->
<aside id="app-sidebar" class="fixed left-0 w-64 bg-gradient-to-b from-gray-800 to-gray-900 shadow-2xl overflow-y-auto z-40" style="top: 56px; bottom: 0;">
    <nav class="py-4">
        <!-- Start -->
        <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->is('/') ? 'bg-gray-700 text-white border-l-4 border-blue-500' : '' }}">
            <span class="text-lg">🏠</span>
            <span class="font-medium">Start</span>
        </a>

        @if(auth()->check() && auth()->user()->can_view_magazyn)
        <!-- Magazyn (rozwijane) -->
        <div class="menu-group">
            <button onclick="toggleSubmenu('magazyn')" class="w-full flex items-center justify-between px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ (request()->routeIs('magazyn.add') || request()->routeIs('magazyn.remove') || request()->routeIs('magazyn.check') || request()->routeIs('magazyn.orders')) ? 'bg-gray-700 text-white' : '' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">📦</span>
                    <span class="font-medium">Magazyn</span>
                </div>
                <svg class="w-4 h-4 transition-transform duration-200 {{ (request()->routeIs('magazyn.add') || request()->routeIs('magazyn.remove') || request()->routeIs('magazyn.check') || request()->routeIs('magazyn.orders')) ? 'rotate-180' : '' }}" id="magazyn-arrow" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div id="magazyn-submenu" class="bg-gray-900 {{ (request()->routeIs('magazyn.add') || request()->routeIs('magazyn.remove') || request()->routeIs('magazyn.check') || request()->routeIs('magazyn.orders')) ? '' : 'hidden' }}">
                @if(auth()->user()->can_add)
                <a href="{{ route('magazyn.add') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.add') ? 'bg-gray-700 text-white border-l-4 border-green-500' : '' }}">
                    <span>➕</span>
                    <span>Dodaj</span>
                </a>
                @endif
                @if(auth()->user()->can_remove)
                <a href="{{ route('magazyn.remove') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.remove') ? 'bg-gray-700 text-white border-l-4 border-red-500' : '' }}">
                    <span>➖</span>
                    <span>Pobierz</span>
                </a>
                @endif
                @if(auth()->user()->can_view_catalog)
                <a href="{{ route('magazyn.check') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.check') ? 'bg-gray-700 text-white border-l-4 border-blue-500' : '' }}">
                    <span>🔍</span>
                    <span>Katalog</span>
                </a>
                @endif
                @if(auth()->user()->can_orders)
                <a href="{{ route('magazyn.orders') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.orders') ? 'bg-gray-700 text-white border-l-4 border-yellow-500' : '' }}">
                    <span>📦</span>
                    <span>Zamówienia</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Projekty (rozwijane) -->
        @if(auth()->check() && auth()->user()->can_view_projects)
        <div class="menu-group">
            <button onclick="toggleSubmenu('projekty')" class="w-full flex items-center justify-between px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.projects*') || request()->routeIs('magazyn.projects.settings') ? 'bg-gray-700 text-white' : '' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">🏗️</span>
                    <span class="font-medium">Projekty</span>
                </div>
                <svg class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('magazyn.projects*') || request()->routeIs('magazyn.projects.settings') ? 'rotate-180' : '' }}" id="projekty-arrow" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div id="projekty-submenu" class="bg-gray-900 {{ request()->routeIs('magazyn.projects*') || request()->routeIs('magazyn.projects.settings') ? '' : 'hidden' }}">
                @if(auth()->user()->can_projects_add)
                <a href="{{ route('magazyn.projects') }}?add=1" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->get('add') == '1' ? 'bg-gray-700 text-white border-l-4 border-green-500' : '' }}">
                    <span>➕</span>
                    <span>Dodaj projekt</span>
                </a>
                @endif
                @if(auth()->user()->can_projects_in_progress)
                <a href="{{ route('magazyn.projects') }}?status=in_progress" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->get('status') == 'in_progress' ? 'bg-gray-700 text-white border-l-4 border-yellow-500' : '' }}">
                    <span>⏳</span>
                    <span>Projekty w toku</span>
                </a>
                @endif
                @if(auth()->user()->can_projects_warranty)
                <a href="{{ route('magazyn.projects') }}?status=warranty" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->get('status') == 'warranty' ? 'bg-gray-700 text-white border-l-4 border-blue-500' : '' }}">
                    <span>🛡️</span>
                    <span>Projekty na gwarancji</span>
                </a>
                @endif
                @if(auth()->user()->can_projects_archived)
                <a href="{{ route('magazyn.projects') }}?status=archived" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->get('status') == 'archived' ? 'bg-gray-700 text-white border-l-4 border-gray-500' : '' }}">
                    <span>📦</span>
                    <span>Projekty archiwalne</span>
                </a>
                @endif
                @if(auth()->user()->can_projects_settings)
                <!-- Ustawienia projektów rozwijane -->
                <div class="pl-8">
                    <a href="{{ route('magazyn.projects.settings') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.projects.settings') ? 'bg-gray-700 text-white border-l-4 border-indigo-500' : '' }}">
                        <span>⚙️</span>
                        <span>Ustawienia projektów</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- CRM -->
        @if(auth()->check() && auth()->user()->can_crm)
        <a href="{{ route('crm') }}" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('crm*') ? 'bg-gray-700 text-white border-l-4 border-teal-500' : '' }}">
            <span class="text-lg">👥</span>
            <span class="font-medium">CRM</span>
        </a>
        @endif

        <!-- Oferty (rozwijane) -->
        @if(auth()->check() && auth()->user()->can_view_offers)
        <div class="menu-group">
            <button onclick="toggleSubmenu('oferty')" class="w-full flex items-center justify-between px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ (request()->routeIs('offers.new') || request()->routeIs('offers.portfolio') || request()->routeIs('offers.inprogress') || request()->routeIs('offers.archived') || request()->routeIs('offers.settings')) ? 'bg-gray-700 text-white' : '' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">📄</span>
                    <span class="font-medium">Oferty</span>
                </div>
                <svg class="w-4 h-4 transition-transform duration-200 {{ (request()->routeIs('offers.new') || request()->routeIs('offers.portfolio') || request()->routeIs('offers.inprogress') || request()->routeIs('offers.archived') || request()->routeIs('offers.settings')) ? 'rotate-180' : '' }}" id="oferty-arrow" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div id="oferty-submenu" class="bg-gray-900 {{ (request()->routeIs('offers.new') || request()->routeIs('offers.portfolio') || request()->routeIs('offers.inprogress') || request()->routeIs('offers.archived') || request()->routeIs('offers.settings')) ? '' : 'hidden' }}">
                <a href="{{ route('offers.new') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.new') ? 'bg-gray-700 text-white border-l-4 border-green-500' : '' }}">
                    <span>➕</span>
                    <span>Zrób ofertę</span>
                </a>
                <a href="{{ route('offers.portfolio') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.portfolio') ? 'bg-gray-700 text-white border-l-4 border-blue-500' : '' }}">
                    <span>📂</span>
                    <span>Portfolio</span>
                </a>
                <a href="{{ route('offers.inprogress') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.inprogress') ? 'bg-gray-700 text-white border-l-4 border-yellow-500' : '' }}">
                    <span>⏳</span>
                    <span>Oferty w toku</span>
                </a>
                <a href="{{ route('offers.archived') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.archived') ? 'bg-gray-700 text-white border-l-4 border-gray-500' : '' }}">
                    <span>🗄️</span>
                    <span>Oferty zarchiwizowane</span>
                </a>
                @if(auth()->user()->email === 'proximalumine@gmail.com' || auth()->user()->can_settings)
                <a href="{{ route('offers.settings') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.settings') ? 'bg-gray-700 text-white border-l-4 border-purple-500' : '' }}">
                    <span>⚙️</span>
                    <span>Ustawienia ofert</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Receptury -->
        @if(auth()->check() && (auth()->user()->email === 'proximalumine@gmail.com' || auth()->user()->can_view_recipes))
        <a href="{{ route('receptury') }}" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('receptury') ? 'bg-gray-700 text-white border-l-4 border-purple-500' : '' }}">
            <span class="text-lg">🧪</span>
            <span class="font-medium">Receptury</span>
        </a>
        @endif

        <!-- Ustawienia -->
        @if(auth()->check() && (auth()->user()->email === 'proximalumine@gmail.com' || auth()->user()->can_settings))
        <a href="{{ route('magazyn.settings') }}" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.settings') ? 'bg-gray-700 text-white border-l-4 border-gray-400' : '' }}">
            <span class="text-lg">⚙️</span>
            <span class="font-medium">Ustawienia</span>
        </a>
        @endif
    </nav>
</aside>

<script>
function updateDateTime() {
    const now = new Date();
    const day = String(now.getDate()).padStart(2, '0');
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const year = now.getFullYear();
    const hour = String(now.getHours()).padStart(2, '0');
    const minute = String(now.getMinutes()).padStart(2, '0');
    const formatted = `${day}.${month}.${year} ${hour}:${minute}`;
    document.getElementById('datetime').textContent = formatted;
}
setInterval(updateDateTime, 1000);
updateDateTime();

function toggleSubmenu(id) {
    const submenu = document.getElementById(id + '-submenu');
    const arrow = document.getElementById(id + '-arrow');
    submenu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}
</script>

<!-- Style dla layoutu z sidebar -->
<style>
    /* Resetuj domyślne style body */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100vh;
    }
    
    /* Dodaj padding dla contentu */
    body {
        padding-left: 16rem !important; /* szerokość sidebara (w-64 = 16rem) */
        padding-top: 56px !important; /* wysokość topbara */
        background-color: #f3f4f6 !important; /* bg-gray-100 */
    }
    
    /* Zapewnij, że topbar i sidebar mają stałe tło i są na wierzchu */
    #app-topbar {
        z-index: 50 !important;
        background: linear-gradient(to right, #1f2937, #111827) !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
    }
    
    /* Wymuszaj biały tekst w topbar */
    #app-topbar,
    #app-topbar * {
        color: #ffffff !important;
    }
    
    #app-sidebar {
        z-index: 40 !important;
        background: linear-gradient(to bottom, #1f2937, #111827) !important;
        position: fixed !important;
        left: 0 !important;
        width: 16rem !important;
    }
    
    /* Wymuszaj dokładne style dla wszystkich linków i przycisków w sidebar */
    #app-sidebar a,
    #app-sidebar button {
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important; /* gap-3 */
        padding: 0.75rem 1rem !important; /* py-3 px-4 */
        color: #d1d5db !important; /* text-gray-300 */
        font-size: 1rem !important;
        font-weight: 500 !important;
        line-height: 1.5 !important;
        transition: all 0.2s !important;
        text-decoration: none !important;
        border: none !important;
        background-color: transparent !important;
        width: 100% !important;
        text-align: left !important;
        justify-content: flex-start !important;
    }
    
    /* Submenu linki - mniejszy padding, mniejsza czcionka - UNIWERSALNY DLA WSZYSTKICH SUBMENU */
    #app-sidebar .menu-group > div[id$='-submenu'] a {
        padding: 0.625rem 1rem !important; /* py-2.5 px-4 */
        padding-left: 3rem !important; /* pl-12 */
        font-size: 0.875rem !important; /* text-sm */
        color: #9ca3af !important; /* text-gray-400 */
        background-color: transparent !important;
    }
    
    /* Wszystkie spany w submenu - mniejsza czcionka */
    #app-sidebar .menu-group > div[id$='-submenu'] a span {
        font-size: 0.875rem !important; /* text-sm */
        font-family: inherit !important;
    }
    
    /* Emoji w submenu - emoji font */
    #app-sidebar .menu-group > div[id$='-submenu'] a span:first-child {
        font-family: "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji" !important;
    }
    
    /* Wymuszaj emoji font dla ikon */
    #app-sidebar .text-lg,
    #app-sidebar a span:first-child,
    #app-sidebar button span:first-child {
        font-family: "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji" !important;
        font-size: 1.125rem !important; /* text-lg */
        line-height: 1 !important;
        display: inline-block !important;
    }
    
    /* Tekst w linkach głównych (nie w submenu) */
    #app-sidebar > nav > a span:not(:first-child),
    #app-sidebar .menu-group > button span:not(:first-child) {
        font-size: 1rem !important;
        font-weight: 500 !important;
        line-height: 1.5 !important;
    }
    
    #app-sidebar a:hover,
    #app-sidebar button:hover {
        background-color: #374151 !important; /* gray-700 */
        color: #ffffff !important;
    }
    
    #app-sidebar a.bg-gray-700,
    #app-sidebar a.text-white,
    #app-sidebar button.bg-gray-700 {
        background-color: #374151 !important;
        color: #ffffff !important;
    }
    
    /* Rotacja strzałki w menu */
    #app-sidebar svg.rotate-180 {
        transform: rotate(180deg) !important;
    }
    
    /* Wymuszaj kolor tła dla submenu - UNIWERSALNY DLA WSZYSTKICH */
    #app-sidebar .menu-group > div[id$='-submenu'] {
        background-color: #111827 !important; /* gray-900 */
        transition: none !important; /* usuń animację dla natychmiastowego wyświetlania */
    }
    
    /* Zapobiegaj FOUC (Flash of Unstyled Content) - UNIWERSALNY */
    #app-sidebar .menu-group > div[id$='-submenu'].hidden {
        display: none !important;
    }
    
    #app-sidebar .menu-group > div[id$='-submenu']:not(.hidden) {
        display: block !important;
    }
    
    #app-sidebar .menu-group > div[id$='-submenu'] a {
        background-color: transparent !important;
        color: #9ca3af !important; /* gray-400 */
    }
    
    #app-sidebar .menu-group > div[id$='-submenu'] a span:first-child {
        font-family: "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji" !important;
        color: inherit !important;
    }
    
    #app-sidebar .menu-group > div[id$='-submenu'] a:hover {
        background-color: #374151 !important; /* gray-700 */
        color: #ffffff !important;
    }
    
    #app-sidebar .menu-group > div[id$='-submenu'] a.bg-gray-700,
    #app-sidebar .menu-group > div[id$='-submenu'] a.text-white {
        background-color: #374151 !important;
        color: #ffffff !important;
    }
    
    /* Media query dla responsywności */
    @media (max-width: 768px) {
        body {
            padding-left: 0 !important;
        }
        #app-sidebar {
            transform: translateX(-100%);
        }
        #app-sidebar.mobile-open {
            transform: translateX(0);
        }
    }
</style>

<!-- Stopka z logo i napisem Powered by ProximaLumine -->
<div style="position: fixed; right: 20px; bottom: 10px; z-index: 50; color: #888; font-style: italic; font-size: 1rem; pointer-events: none; display: flex; align-items: center; gap: 8px;">
    <img src="{{ asset('logo_proxima.png') }}" alt="ProximaLumine" style="height:44px;vertical-align:middle;">
    <span>Powered by ProximaLumine</span>
</div>

