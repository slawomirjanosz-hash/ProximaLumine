<style>
    html {
        overflow-y: scroll;
        background-color: #f3f4f6;
    }

    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        padding-left: 16rem;
        padding-top: 56px;
        background-color: #f3f4f6;
    }

    #app-topbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 50;
        background: linear-gradient(90deg, #0F295F 0%, #23272F 100%);
    }

    #app-sidebar {
        position: fixed;
        top: 56px;
        bottom: 0;
        left: 0;
        width: 16rem;
        z-index: 40;
        background: linear-gradient(180deg, #0F295F 0%, #23272F 100%);
        overflow-y: auto;
    }

    #app-sidebar .submenu-panel {
        display: none;
    }

    #app-sidebar .submenu-panel.is-open {
        display: block;
    }
</style>

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
                <span class="text-white text-lg tracking-wide">{{ $companyName }}</span>
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
<aside id="app-sidebar" class="fixed left-0 w-64 bg-gradient-to-b from-gray-800 to-gray-900 shadow-2xl flex flex-col justify-between overflow-y-auto z-40 font-[Instrument Sans]" style="top: 56px; bottom: 0;">
    <div>
        <nav class="py-4">
        @php
            $isSuperAdmin = auth()->check() && strtolower(auth()->user()->email) === 'proximalumine@gmail.com';
            $isMagazynMenuActive = request()->routeIs('magazyn.add')
                || request()->routeIs('magazyn.remove')
                || request()->routeIs('magazyn.check')
                || request()->routeIs('magazyn.check.*')
                || request()->routeIs('magazyn.orders')
                || request()->routeIs('magazyn.receive');
            $isProjektyMenuActive = request()->routeIs('magazyn.projects')
                || request()->routeIs('magazyn.projects.*')
                || request()->get('add')
                || request()->get('status');
            $isOfertyMenuActive = request()->routeIs('offers.*');
            $isAudytyMenuActive = request()->routeIs('audits');
            $auditTab = request()->get('tab', 'new-audit');
        @endphp
        <!-- Start -->
        <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white transition-all duration-200 {{ request()->is('/') ? 'text-white border-l-4 border-[#0F295F]' : '' }}">
            <span class="text-lg">🏠</span>
            <span class="menu-main-label {{ request()->is('/') ? 'is-active' : '' }}">Start</span>
        </a>

        @if(auth()->check() && ($isSuperAdmin || auth()->user()->can_view_magazyn))
        <!-- Magazyn (rozwijane) -->
        <div class="menu-group">
            <button onclick="toggleSubmenu('magazyn')" class="w-full flex items-center px-4 py-3 text-gray-300 hover:text-white transition-all duration-200 {{ $isMagazynMenuActive ? 'text-white' : '' }}">
                <div class="flex items-center gap-3 flex-1">
                    <span class="text-lg">📦</span>
                    <span class="menu-main-label {{ $isMagazynMenuActive ? 'is-active' : '' }}">Magazyn</span>
                </div>
                <div class="flex flex-col items-end">
                    <svg class="w-4 h-4 transition-transform duration-200 {{ $isMagazynMenuActive ? 'rotate-180' : '' }}" id="magazyn-arrow" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </button>
            <div id="magazyn-submenu" class="bg-gray-900 submenu-panel {{ $isMagazynMenuActive ? 'is-open' : '' }}">
                @if($isSuperAdmin || auth()->user()->can_view_catalog)
                <a href="{{ route('magazyn.check') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.check') ? 'text-white border-l-4 border-blue-500' : '' }}">
                    <span>🔍</span>
                    <span>Katalog</span>
                </a>
                @endif
                @if($isSuperAdmin || auth()->user()->can_add)
                <a href="{{ route('magazyn.add') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.add') ? 'text-white border-l-4 border-green-500' : '' }}">
                    <span>➕</span>
                    <span>Dodaj</span>
                </a>
                @endif
                @if($isSuperAdmin || auth()->user()->can_receive)
                <a href="{{ route('magazyn.receive') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.receive') ? 'text-white border-l-4 border-green-400' : '' }}">
                    <span>📥</span>
                    <span>Przyjmij na magazyn</span>
                </a>
                @endif
                @if($isSuperAdmin || auth()->user()->can_remove)
                <a href="{{ route('magazyn.remove') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.remove') ? 'text-white border-l-4 border-red-500' : '' }}">
                    <span>➖</span>
                    <span>Pobierz</span>
                </a>
                @endif
                @if($isSuperAdmin || auth()->user()->can_orders)
                <a href="{{ route('magazyn.orders') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.orders') ? 'text-white border-l-4 border-yellow-500' : '' }}">
                    <span>📦</span>
                    <span>Zamówienia</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Projekty (rozwijane) -->
        @if(auth()->check() && ($isSuperAdmin || auth()->user()->can_view_projects))
        <div class="menu-group">
            <button onclick="toggleSubmenu('projekty')" class="w-full flex items-center px-4 py-3 text-gray-300 hover:text-white transition-all duration-200 {{ $isProjektyMenuActive ? 'text-white' : '' }}">
                <div class="flex items-center gap-3 flex-1">
                    <span class="text-lg">🏗️</span>
                    <span class="menu-main-label {{ $isProjektyMenuActive ? 'is-active' : '' }}">Projekty</span>
                </div>
                <div class="flex flex-col items-end">
                    <svg class="w-4 h-4 transition-transform duration-200 {{ $isProjektyMenuActive ? 'rotate-180' : '' }}" id="projekty-arrow" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </button>
            <div id="projekty-submenu" class="bg-gray-900 submenu-panel {{ $isProjektyMenuActive ? 'is-open' : '' }}">
                @if($isSuperAdmin || auth()->user()->can_projects_in_progress)
                <a href="{{ route('magazyn.projects') }}?status=in_progress" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->get('status') == 'in_progress' ? 'text-white border-l-4 border-yellow-500' : '' }}">
                    <span>⏳</span>
                    <span>Projekty w toku</span>
                </a>
                @endif
                @if($isSuperAdmin || auth()->user()->can_projects_warranty)
                <a href="{{ route('magazyn.projects') }}?status=warranty" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->get('status') == 'warranty' ? 'text-white border-l-4 border-blue-500' : '' }}">
                    <span>🛡️</span>
                    <span>Projekty na gwarancji</span>
                </a>
                @endif
                @if($isSuperAdmin || auth()->user()->can_projects_archived)
                <a href="{{ route('magazyn.projects') }}?status=archived" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->get('status') == 'archived' ? 'text-white border-l-4 border-gray-500' : '' }}">
                    <span>📦</span>
                    <span>Projekty archiwalne</span>
                </a>
                @endif
                @if($isSuperAdmin || auth()->user()->can_projects_add)
                <a href="{{ route('magazyn.projects') }}?add=1" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->get('add') == '1' ? 'text-white border-l-4 border-green-500' : '' }}">
                    <span>➕</span>
                    <span>Dodaj projekt</span>
                </a>
                @endif
                @if($isSuperAdmin || auth()->user()->can_projects_settings)
                <a href="{{ route('magazyn.projects.settings') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.projects.settings') ? 'bg-gray-700 text-white border-l-4 border-indigo-500' : '' }}">
                    <span>⚙️</span>
                    <span>Ustawienia projektów</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- CRM -->
        @if(auth()->check() && ($isSuperAdmin || auth()->user()->can_crm))
        <a href="{{ route('crm') }}" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white transition-all duration-200 {{ request()->routeIs('crm*') ? 'bg-gray-700 text-white border-l-4 border-[#0F295F]' : '' }}">
            <span class="text-lg">👥</span>
            <span class="menu-main-label {{ request()->routeIs('crm*') ? 'is-active' : '' }}">CRM</span>
        </a>
        @endif

        <!-- Oferty (rozwijane) -->
        @if(auth()->check() && ($isSuperAdmin || auth()->user()->can_view_offers))
        <div class="menu-group">
            <button onclick="toggleSubmenu('oferty')" class="w-full flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200 {{ $isOfertyMenuActive ? 'bg-gray-700 text-white' : '' }}">
                <div class="flex items-center gap-3 flex-1">
                    <span class="text-lg">📄</span>
                    <span class="menu-main-label {{ $isOfertyMenuActive ? 'is-active' : '' }}">Oferty</span>
                </div>
                <div class="flex flex-col items-end">
                    <svg class="w-4 h-4 transition-transform duration-200 {{ $isOfertyMenuActive ? 'rotate-180' : '' }}" id="oferty-arrow" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </button>
            <div id="oferty-submenu" class="bg-gray-900 submenu-panel {{ $isOfertyMenuActive ? 'is-open' : '' }}">
                <a href="{{ route('offers.new') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.new') ? 'text-white border-l-4 border-green-500' : '' }}">
                    <span>➕</span>
                    <span>Zrób ofertę</span>
                </a>
                <a href="{{ route('offers.portfolio') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.portfolio') ? 'text-white border-l-4 border-blue-500' : '' }}">
                    <span>📂</span>
                    <span>Portfolio</span>
                </a>
                <a href="{{ route('offers.inprogress') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.inprogress') ? 'text-white border-l-4 border-yellow-500' : '' }}">
                    <span>⏳</span>
                    <span>Oferty w toku</span>
                </a>
                <a href="{{ route('offers.archived') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.archived') ? 'text-white border-l-4 border-gray-500' : '' }}">
                    <span>🗄️</span>
                    <span>Oferty zarchiwizowane</span>
                </a>
                @if($isSuperAdmin || auth()->user()->can_settings)
                <a href="{{ route('offers.settings') }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ request()->routeIs('offers.settings') ? 'text-white border-l-4 border-purple-500' : '' }}">
                    <span>⚙️</span>
                    <span>Ustawienia ofert</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Receptury -->
        @if(auth()->check() && ($isSuperAdmin || auth()->user()->can_view_recipes))
        <a href="{{ route('receptury') }}" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white transition-all duration-200 {{ request()->routeIs('receptury') ? 'text-white border-l-4 border-purple-500' : '' }}">
            <span class="text-lg">🧪</span>
            <span class="menu-main-label {{ request()->routeIs('receptury') ? 'is-active' : '' }}">Receptury</span>
        </a>
        @endif

        <!-- Audyty (rozwijane) -->
        @if(auth()->check() && ($isSuperAdmin || auth()->user()->can_audits))
        <div class="menu-group">
            <button onclick="toggleSubmenu('audyty')" class="w-full flex items-center px-4 py-3 text-gray-300 hover:text-white transition-all duration-200 {{ $isAudytyMenuActive ? 'text-white' : '' }}">
                <div class="flex items-center gap-3 flex-1">
                    <span class="text-lg">📝</span>
                    <span class="menu-main-label {{ $isAudytyMenuActive ? 'is-active' : '' }}">Audyty</span>
                </div>
                <div class="flex flex-col items-end">
                    <svg class="w-4 h-4 transition-transform duration-200 {{ $isAudytyMenuActive ? 'rotate-180' : '' }}" id="audyty-arrow" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </button>
            <div id="audyty-submenu" class="bg-gray-900 submenu-panel {{ $isAudytyMenuActive ? 'is-open' : '' }}">
                <a href="{{ route('audits', ['tab' => 'new-audit']) }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ $isAudytyMenuActive && $auditTab === 'new-audit' ? 'text-white border-l-4 border-green-500' : '' }}">
                    <span>➕</span>
                    <span>Nowy audyt</span>
                </a>
                <a href="{{ route('audits', ['tab' => 'in-progress']) }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ $isAudytyMenuActive && $auditTab === 'in-progress' ? 'text-white border-l-4 border-yellow-500' : '' }}">
                    <span>⏳</span>
                    <span>Audyty w toku</span>
                </a>
                <a href="{{ route('audits', ['tab' => 'completed']) }}" class="flex items-center gap-3 px-4 py-2.5 pl-12 text-sm text-gray-400 hover:text-white transition-all duration-200 {{ $isAudytyMenuActive && $auditTab === 'completed' ? 'text-white border-l-4 border-blue-500' : '' }}">
                    <span>✅</span>
                    <span>Audyty zakończone</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Ustawienia -->
        @if(auth()->check() && ($isSuperAdmin || auth()->user()->can_settings))
        <a href="{{ route('magazyn.settings') }}" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white transition-all duration-200 {{ request()->routeIs('magazyn.settings') ? 'text-white border-l-4 border-gray-400' : '' }}">
            <span class="text-lg">⚙️</span>
            <span class="menu-main-label {{ request()->routeIs('magazyn.settings') ? 'is-active' : '' }}">Ustawienia</span>
        </a>
        @endif
        </nav>
    </div>
    
    <!-- Logo ProximaLumine w lewym dolnym rogu -->
    <div class="p-4 border-t border-gray-700 flex flex-col items-center">
        <img src="{{ asset('logo_proxima.png') }}" alt="ProximaLumine" class="h-10 mb-2">
        <span class="text-xs text-gray-400 italic">Powered by ProximaLumine</span>
    </div>
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
    if (!submenu || !arrow) {
        return;
    }

    submenu.classList.toggle('is-open');
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

    html {
        overflow-y: scroll !important;
        background-color: #f3f4f6 !important;
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
        background: linear-gradient(180deg, #0F295F 0%, #23272F 100%) !important;
        position: fixed !important;
        left: 0 !important;
        width: 16rem !important;
        overflow-y: auto !important;
    }

    #app-topbar {
        background: linear-gradient(90deg, #0F295F 0%, #23272F 100%) !important;
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
        font-weight: normal !important;
        line-height: 1.5 !important;
        transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease !important;
        text-decoration: none !important;
        border: 0 !important;
        border-left: 4px solid transparent !important;
        box-sizing: border-box !important;
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
        font-weight: normal !important;
        line-height: 1.5 !important;
    }

    #app-sidebar > div > nav > a span.menu-main-label.is-active,
    #app-sidebar .menu-group > button span.menu-main-label.is-active {
        font-weight: 700 !important;
    }

    #app-sidebar .menu-group > div[id$='-submenu'] a.text-white span:last-child {
        font-weight: 700 !important;
    }
    
    #app-sidebar a:hover,
    #app-sidebar button:hover {
        background-color: transparent !important;
        color: #ffffff !important;
    }
    
    #app-sidebar a.bg-gray-700,
    #app-sidebar a.text-white,
    #app-sidebar button.bg-gray-700 {
        background-color: transparent !important;
        color: #ffffff !important;
    }
    
    /* Rotacja strzałki w menu */
    #app-sidebar svg.rotate-180 {
        transform: rotate(180deg) !important;
    }
    
    /* Wymuszaj kolor tła dla submenu - UNIWERSALNY DLA WSZYSTKICH */
    #app-sidebar .menu-group > div[id$='-submenu'] {
        background-color: #111827 !important; /* gray-900 */
        display: none;
    }

    #app-sidebar .menu-group > div[id$='-submenu'].is-open {
        display: block;
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
        background-color: transparent !important;
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

