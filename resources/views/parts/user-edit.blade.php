<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Magazyn – Edycja użytkownika</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow mt-6">
    <a href="{{ route('magazyn.settings') }}" class="text-blue-600 hover:underline mb-4 inline-block">← Wróć do ustawień</a>

    <h2 class="text-2xl font-bold mb-6">Edycja użytkownika: {{ $user->name }}</h2>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('magazyn.user.update', $user->id) }}" method="POST" class="flex flex-col gap-4">
        @csrf
        @method('PUT')

        @php
            $isSuperAdmin = auth()->user()->email === 'proximalumine@gmail.com';
            $isAdmin = auth()->user()->is_admin;
            $canManageUsers = auth()->user()->can_settings_users;
        @endphp

        <!-- Informacje o użytkowniku -->
        <div class="border-b pb-4">
            <h3 class="font-semibold mb-3">Dane użytkownika</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Imię</label>
                    <input 
                        type="text" 
                        name="first_name" 
                        id="edit_first_name"
                        value="{{ $user->first_name ?? explode(' ', $user->name)[0] }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded @error('first_name') border-red-500 @enderror"
                        required
                    >
                    @error('first_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nazwisko</label>
                    <input 
                        type="text" 
                        name="last_name" 
                        id="edit_last_name"
                        value="{{ $user->last_name ?? (count(explode(' ', $user->name)) > 1 ? explode(' ', $user->name)[1] : '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded @error('last_name') border-red-500 @enderror"
                        required
                    >
                    @error('last_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ $user->email }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded @error('email') border-red-500 @enderror"
                        required
                    >
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numer telefonu</label>
                    <input 
                        type="text" 
                        name="phone" 
                        value="{{ $user->phone }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded @error('phone') border-red-500 @enderror"
                    >
                    @error('phone')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Skrócona nazwa użytkownika</label>
                    <input 
                        type="text" 
                        name="short_name" 
                        id="edit_short_name"
                        value="{{ $user->short_name ?? '' }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded @error('short_name') border-red-500 @enderror"
                        placeholder="np. MicKow"
                    >
                    @error('short_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Zmiana hasła -->
        <div class="border-b pb-4">
            <h3 class="font-semibold mb-3">Zmiana hasła (opcjonalne)</h3>
            <input 
                type="password" 
                name="password" 
                placeholder="Nowe hasło" 
                class="w-full px-3 py-2 border border-gray-300 rounded @error('password') border-red-500 @enderror"
                autocomplete="new-password"
            >
            <p class="text-xs text-gray-500 mt-1">Pozostaw puste, jeśli nie chcesz zmieniać hasła</p>
        </div>

        <!-- Utworzony przez (tylko dla superadmin/admin) -->
        @if($isSuperAdmin || $isAdmin)
        <div class="border-b pb-4">
            <h3 class="font-semibold mb-3">Zarządzanie właścicielem</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Utworzony przez
                    <span class="text-xs text-gray-500 font-normal">(tylko ten użytkownik będzie mógł edytować tego użytkownika)</span>
                </label>
                <select 
                    name="created_by" 
                    class="w-full px-3 py-2 border border-gray-300 rounded @error('created_by') border-red-500 @enderror"
                >
                    <option value="">-- Brak (tylko admin/superadmin) --</option>
                    @foreach(\App\Models\User::orderBy('name')->get() as $potentialCreator)
                        <option value="{{ $potentialCreator->id }}" {{ $user->created_by == $potentialCreator->id ? 'selected' : '' }}>
                            {{ $potentialCreator->name }} ({{ $potentialCreator->email }})
                        </option>
                    @endforeach
                </select>
                @error('created_by')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                @if($user->creator)
                    <p class="text-xs text-gray-600 mt-1">
                        Obecny właściciel: <strong>{{ $user->creator->name }}</strong>
                    </p>
                @else
                    <p class="text-xs text-gray-600 mt-1">
                        Brak właściciela - może edytować tylko admin/superadmin
                    </p>
                @endif
            </div>
        </div>
        @endif

        <!-- Uprawnienia -->
        <div class="border-b pb-4">
            <h3 class="font-semibold mb-4">Dostęp do zakładek</h3>
            @php
                // Funkcja sprawdzająca czy można nadać dane uprawnienie
                $canGrant = function($permission) use ($isSuperAdmin, $isAdmin, $canManageUsers) {
                    if ($isSuperAdmin) return true;
                    if (!$isAdmin && !$canManageUsers) return false;
                    return (bool) auth()->user()->$permission;
                };
            @endphp
            <div class="space-y-2">
                {{-- MAGAZYN --}}
                @if($canGrant('can_view_magazyn') || $canGrant('can_view_catalog') || $canGrant('can_add') || $canGrant('can_receive') || $canGrant('can_remove') || $canGrant('can_orders') || $canGrant('can_delete_orders') || $canGrant('show_action_column') || $canGrant('show_action_column_receive'))
                <div class="border rounded">
                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="can_view_magazyn" 
                            id="can_view_magazyn_checkbox"
                            class="w-4 h-4 parent-checkbox"
                            data-target="magazyn_tree"
                            {{ $user->can_view_magazyn ? 'checked' : '' }}
                        >
                        <span class="toggle-arrow text-sm">{{ $user->can_view_magazyn ? '▼' : '▶' }}</span>
                        <span class="text-sm flex-1">
                            <strong>📦 Magazyn</strong>
                        </span>
                    </label>
                    
                    <div id="magazyn_tree" class="ml-8 mr-3 mb-3 space-y-2 {{ $user->can_view_magazyn ? '' : 'hidden' }}">
                        {{-- Katalog z poddrzewem --}}
                        @if($canGrant('can_view_catalog'))
                        <div class="border rounded">
                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="can_view_catalog" 
                                    id="can_view_catalog_checkbox"
                                    class="w-4 h-4 child-checkbox parent-checkbox"
                                    data-parent="can_view_magazyn_checkbox"
                                    data-target="catalog_tree"
                                    {{ $user->can_view_catalog ? 'checked' : '' }}
                                >
                                <span class="toggle-arrow text-xs">{{ $user->can_view_catalog ? '▼' : '▶' }}</span>
                                <span class="text-sm flex-1"><strong>🔍 Katalog</strong></span>
                            </label>
                            
                            <div id="catalog_tree" class="ml-8 mr-2 mb-2 space-y-1 {{ $user->can_view_catalog ? '' : 'hidden' }}">
                                @if($canGrant('show_action_column'))
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="show_action_column" 
                                        class="w-4 h-4 child-checkbox"
                                        data-parent="can_view_catalog_checkbox"
                                        {{ $user->show_action_column ? 'checked' : '' }}
                                    >
                                    <span class="text-xs">👁️ Pokaż kolumnę akcja w Magazyn/Sprawdź</span>
                                </label>
                                @elseif($user->show_action_column)
                                <div class="flex items-center gap-2 p-2 bg-gray-50">
                                    <input type="checkbox" class="w-4 h-4" checked disabled>
                                    <span class="text-xs text-gray-500">👁️ Pokaż kolumnę akcja w Magazyn/Sprawdź (tylko do odczytu)</span>
                                </div>
                                <input type="hidden" name="show_action_column" value="1">
                                @endif
                            </div>
                        </div>
                        @elseif($user->can_view_catalog)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">🔍 Katalog (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_view_catalog" value="1">
                        @endif
                        
                        {{-- Dodaj --}}
                        @if($canGrant('can_add'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_add" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_view_magazyn_checkbox"
                                {{ $user->can_add ? 'checked' : '' }}
                            >
                            <span class="text-sm">➕ Dodaj</span>
                        </label>
                        @elseif($user->can_add)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">➕ Dodaj (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_add" value="1">
                        @endif
                        
                        {{-- Przyjmij na magazyn z poddrzewem --}}
                        @if($canGrant('can_receive'))
                        <div class="border rounded">
                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="can_receive" 
                                    id="can_receive_checkbox"
                                    class="w-4 h-4 child-checkbox parent-checkbox"
                                    data-parent="can_view_magazyn_checkbox"
                                    data-target="receive_tree"
                                    {{ $user->can_receive ? 'checked' : '' }}
                                >
                                <span class="toggle-arrow text-xs">{{ $user->can_receive ? '▼' : '▶' }}</span>
                                <span class="text-sm flex-1"><strong>📥 Przyjmij na magazyn</strong></span>
                            </label>
                            
                            <div id="receive_tree" class="ml-8 mr-2 mb-2 space-y-1 {{ $user->can_receive ? '' : 'hidden' }}">
                                @if($canGrant('show_action_column_receive'))
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="show_action_column_receive" 
                                        class="w-4 h-4 child-checkbox"
                                        data-parent="can_receive_checkbox"
                                        {{ $user->show_action_column_receive ? 'checked' : '' }}
                                    >
                                    <span class="text-xs">👁️ Pokaż kolumnę akcja w Przyjmij na magazyn</span>
                                </label>
                                @elseif($user->show_action_column_receive)
                                <div class="flex items-center gap-2 p-2 bg-gray-50">
                                    <input type="checkbox" class="w-4 h-4" checked disabled>
                                    <span class="text-xs text-gray-500">👁️ Pokaż kolumnę akcja w Przyjmij na magazyn (tylko do odczytu)</span>
                                </div>
                                <input type="hidden" name="show_action_column_receive" value="1">
                                @endif
                            </div>
                        </div>
                        @elseif($user->can_receive)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">📥 Przyjmij na magazyn (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_receive" value="1">
                        @endif
                        
                        {{-- Pobierz --}}
                        @if($canGrant('can_remove'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_remove" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_view_magazyn_checkbox"
                                {{ $user->can_remove ? 'checked' : '' }}
                            >
                            <span class="text-sm">➖ Pobierz</span>
                        </label>
                        @elseif($user->can_remove)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">➖ Pobierz (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_remove" value="1">
                        @endif
                        
                        {{-- Zamówienia z poddrzewem --}}
                        @if($canGrant('can_orders'))
                        <div class="border rounded">
                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="can_orders" 
                                    id="can_orders_checkbox"
                                    class="w-4 h-4 child-checkbox parent-checkbox"
                                    data-parent="can_view_magazyn_checkbox"
                                    data-target="orders_tree"
                                    {{ $user->can_orders ? 'checked' : '' }}
                                >
                                <span class="toggle-arrow text-xs">{{ $user->can_orders ? '▼' : '▶' }}</span>
                                <span class="text-sm flex-1"><strong>📦 Zamówienia</strong></span>
                            </label>
                            
                            <div id="orders_tree" class="ml-8 mr-2 mb-2 space-y-1 {{ $user->can_orders ? '' : 'hidden' }}">
                                @if($canGrant('can_delete_orders'))
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="can_delete_orders" 
                                        class="w-4 h-4 child-checkbox"
                                        data-parent="can_orders_checkbox"
                                        {{ $user->can_delete_orders ? 'checked' : '' }}
                                    >
                                    <span class="text-xs">🗑️ Usuwanie zamówień</span>
                                </label>
                                @elseif($user->can_delete_orders)
                                <div class="flex items-center gap-2 p-2 bg-gray-50">
                                    <input type="checkbox" class="w-4 h-4" checked disabled>
                                    <span class="text-xs text-gray-500">🗑️ Usuwanie zamówień (tylko do odczytu)</span>
                                </div>
                                <input type="hidden" name="can_delete_orders" value="1">
                                @endif
                            </div>
                        </div>
                        @elseif($user->can_orders)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">📦 Zamówienia (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_orders" value="1">
                        @endif
                    </div>
                </div>
                @else
                    @if($user->can_view_magazyn)
                    <div class="flex items-center gap-3 p-3 border rounded bg-gray-50">
                        <input type="checkbox" class="w-4 h-4" checked disabled>
                        <span class="text-sm text-gray-500">
                            <strong>📦 Magazyn</strong> (tylko do odczytu)
                        </span>
                    </div>
                    <input type="hidden" name="can_view_magazyn" value="1">
                    @endif
                @endif
                
                {{-- PROJEKTY --}}
                @if($canGrant('can_view_projects') || $canGrant('can_projects_add') || $canGrant('can_projects_in_progress') || $canGrant('can_projects_warranty') || $canGrant('can_projects_archived') || $canGrant('can_projects_settings') || $canGrant('can_import_project_costs_excel'))
                <div class="border rounded">
                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="can_view_projects" 
                            id="can_view_projects_checkbox"
                            class="w-4 h-4 parent-checkbox"
                            data-target="projects_tree"
                            {{ $user->can_view_projects ? 'checked' : '' }}
                        >
                        <span class="toggle-arrow text-sm">{{ $user->can_view_projects ? '▼' : '▶' }}</span>
                        <span class="text-sm flex-1">
                            <strong>📋 Projekty</strong>
                        </span>
                    </label>
                    
                    <div id="projects_tree" class="ml-8 mr-3 mb-3 space-y-2 {{ $user->can_view_projects ? '' : 'hidden' }}">
                        {{-- Dodawanie nowych projektów --}}
                        @if($canGrant('can_projects_add'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_projects_add" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_view_projects_checkbox"
                                {{ $user->can_projects_add ? 'checked' : '' }}
                            >
                            <span class="text-sm">➕ Dodawanie nowych projektów</span>
                        </label>
                        @elseif($user->can_projects_add)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">➕ Dodawanie nowych projektów (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_projects_add" value="1">
                        @endif
                        
                        {{-- Przeglądanie projektów w toku --}}
                        @if($canGrant('can_projects_in_progress'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_projects_in_progress" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_view_projects_checkbox"
                                {{ $user->can_projects_in_progress ? 'checked' : '' }}
                            >
                            <span class="text-sm">🔄 Przeglądanie projektów w toku</span>
                        </label>
                        @elseif($user->can_projects_in_progress)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">🔄 Przeglądanie projektów w toku (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_projects_in_progress" value="1">
                        @endif
                        
                        {{-- Przeglądanie projektów na gwarancji --}}
                        @if($canGrant('can_projects_warranty'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_projects_warranty" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_view_projects_checkbox"
                                {{ $user->can_projects_warranty ? 'checked' : '' }}
                            >
                            <span class="text-sm">🛡️ Przeglądanie projektów na gwarancji</span>
                        </label>
                        @elseif($user->can_projects_warranty)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">🛡️ Przeglądanie projektów na gwarancji (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_projects_warranty" value="1">
                        @endif
                        
                        {{-- Przeglądanie projektów archiwalnych --}}
                        @if($canGrant('can_projects_archived'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_projects_archived" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_view_projects_checkbox"
                                {{ $user->can_projects_archived ? 'checked' : '' }}
                            >
                            <span class="text-sm">📦 Przeglądanie projektów archiwalnych</span>
                        </label>
                        @elseif($user->can_projects_archived)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">📦 Przeglądanie projektów archiwalnych (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_projects_archived" value="1">
                        @endif
                        
                        {{-- Dostęp do ustawień projektów i list projektowych --}}
                        @if($canGrant('can_projects_settings'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_projects_settings" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_view_projects_checkbox"
                                {{ $user->can_projects_settings ? 'checked' : '' }}
                            >
                            <span class="text-sm">⚙️ Dostęp do ustawień projektów i list projektowych</span>
                        </label>
                        @elseif($user->can_projects_settings)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">⚙️ Dostęp do ustawień projektów i list projektowych (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_projects_settings" value="1">
                        @endif

                        {{-- Import kosztów z Excela w harmonogramie finansowym --}}
                        @if($canGrant('can_import_project_costs_excel'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_import_project_costs_excel" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_view_projects_checkbox"
                                {{ $user->can_import_project_costs_excel ? 'checked' : '' }}
                            >
                            <span class="text-sm">💸 Import kosztów z Excela (harmonogram finansowy)</span>
                        </label>
                        @elseif($user->can_import_project_costs_excel)
                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50">
                            <input type="checkbox" class="w-4 h-4" checked disabled>
                            <span class="text-sm text-gray-500">💸 Import kosztów z Excela (harmonogram finansowy) (tylko do odczytu)</span>
                        </div>
                        <input type="hidden" name="can_import_project_costs_excel" value="1">
                        @endif
                    </div>
                </div>
                @else
                    @if($user->can_view_projects)
                    <div class="flex items-center gap-3 p-3 border rounded bg-gray-50">
                        <input type="checkbox" class="w-4 h-4" checked disabled>
                        <span class="text-sm text-gray-500">
                            <strong>📋 Projekty</strong> (tylko do odczytu)
                        </span>
                    </div>
                    <input type="hidden" name="can_view_projects" value="1">
                    @endif
                @endif
                
                {{-- CRM --}}
                @if($canGrant('can_crm'))
                <label class="flex items-center gap-3 p-3 border rounded hover:bg-gray-50 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="can_crm" 
                        class="w-4 h-4"
                        {{ $user->can_crm ? 'checked' : '' }}
                    >
                    <span class="text-sm">
                        <strong>👥 CRM</strong>
                    </span>
                </label>
                @else
                    @if($user->can_crm)
                    <div class="flex items-center gap-3 p-3 border rounded bg-gray-50">
                        <input type="checkbox" class="w-4 h-4" checked disabled>
                        <span class="text-sm text-gray-500">
                            <strong>👥 CRM</strong> (tylko do odczytu)
                        </span>
                    </div>
                    <input type="hidden" name="can_crm" value="1">
                    @endif
                @endif
                
                {{-- OFERTY --}}
                @if($canGrant('can_view_offers'))
                <label class="flex items-center gap-3 p-3 border rounded hover:bg-gray-50 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="can_view_offers" 
                        class="w-4 h-4"
                        {{ $user->can_view_offers ? 'checked' : '' }}
                    >
                    <span class="text-sm">
                        <strong>💼 Oferty</strong>
                    </span>
                </label>
                @else
                    @if($user->can_view_offers)
                    <div class="flex items-center gap-3 p-3 border rounded bg-gray-50">
                        <input type="checkbox" class="w-4 h-4" checked disabled>
                        <span class="text-sm text-gray-500">
                            <strong>💼 Oferty</strong> (tylko do odczytu)
                        </span>
                    </div>
                    <input type="hidden" name="can_view_offers" value="1">
                    @endif
                @endif
                
                {{-- RECEPTURY --}}
                @if($canGrant('can_view_recipes'))
                <label class="flex items-center gap-3 p-3 border rounded hover:bg-gray-50 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="can_view_recipes" 
                        class="w-4 h-4"
                        {{ $user->can_view_recipes ? 'checked' : '' }}
                    >
                    <span class="text-sm">
                        <strong>🧪 Receptury</strong>
                    </span>
                </label>
                @else
                    @if($user->can_view_recipes)
                    <div class="flex items-center gap-3 p-3 border rounded bg-gray-50">
                        <input type="checkbox" class="w-4 h-4" checked disabled>
                        <span class="text-sm text-gray-500">
                            <strong>🧪 Receptury</strong> (tylko do odczytu)
                        </span>
                    </div>
                    <input type="hidden" name="can_view_recipes" value="1">
                    @endif
                @endif

                {{-- AUDYTY --}}
                @if($canGrant('can_audits'))
                <label class="flex items-center gap-3 p-3 border rounded hover:bg-gray-50 cursor-pointer">
                    <input
                        type="checkbox"
                        name="can_audits"
                        class="w-4 h-4"
                        {{ $user->can_audits ? 'checked' : '' }}
                    >
                    <span class="text-sm">
                        <strong>📝 Audyty</strong>
                    </span>
                </label>
                @else
                    @if($user->can_audits)
                    <div class="flex items-center gap-3 p-3 border rounded bg-gray-50">
                        <input type="checkbox" class="w-4 h-4" checked disabled>
                        <span class="text-sm text-gray-500">
                            <strong>📝 Audyty</strong> (tylko do odczytu)
                        </span>
                    </div>
                    <input type="hidden" name="can_audits" value="1">
                    @endif
                @endif

                {{-- USTAWIENIA --}}
                @if($canGrant('can_settings') || $canGrant('can_settings_categories') || $canGrant('can_settings_suppliers') || $canGrant('can_settings_company') || $canGrant('can_settings_users') || $canGrant('can_settings_export') || $canGrant('can_settings_other'))
                <div class="border rounded">
                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="can_settings" 
                            id="can_settings_checkbox"
                            class="w-4 h-4 parent-checkbox"
                            data-target="settings_tree"
                            {{ $user->can_settings ? 'checked' : '' }}
                        >
                        <span class="toggle-arrow text-sm">{{ $user->can_settings ? '▼' : '▶' }}</span>
                        <span class="text-sm flex-1">
                            <strong>⚙️ Ustawienia</strong>
                        </span>
                    </label>
                    
                    <div id="settings_tree" class="ml-8 mr-3 mb-3 space-y-2 {{ $user->can_settings ? '' : 'hidden' }}">
                        @if($canGrant('can_settings_categories'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_settings_categories" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_settings_checkbox"
                                {{ $user->can_settings_categories ? 'checked' : '' }}
                            >
                            <span class="text-sm">📁 Kategorie</span>
                        </label>
                        @endif

                        @if($canGrant('can_settings_suppliers'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_settings_suppliers" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_settings_checkbox"
                                {{ $user->can_settings_suppliers ? 'checked' : '' }}
                            >
                            <span class="text-sm">🏢 Dostawcy i klienci</span>
                        </label>
                        @endif

                        @if($canGrant('can_settings_company'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_settings_company" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_settings_checkbox"
                                {{ $user->can_settings_company ? 'checked' : '' }}
                            >
                            <span class="text-sm">🏭 Dane mojej firmy</span>
                        </label>
                        @endif

                        @if($canGrant('can_settings_users'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_settings_users" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_settings_checkbox"
                                {{ $user->can_settings_users ? 'checked' : '' }}
                            >
                            <span class="text-sm">👥 Użytkownicy</span>
                        </label>
                        @endif

                        @if($canGrant('can_settings_export'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_settings_export" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_settings_checkbox"
                                {{ $user->can_settings_export ? 'checked' : '' }}
                            >
                            <span class="text-sm">📤 Ustawienia eksportu</span>
                        </label>
                        @endif

                        @if($canGrant('can_settings_other'))
                        <label class="flex items-center gap-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="can_settings_other" 
                                class="w-4 h-4 child-checkbox"
                                data-parent="can_settings_checkbox"
                                {{ $user->can_settings_other ? 'checked' : '' }}
                            >
                            <span class="text-sm">⚡ Inne ustawienia</span>
                        </label>
                        @endif
                    </div>
                </div>
                @else
                    @if($user->can_settings)
                    <div class="flex items-center gap-3 p-3 border rounded bg-gray-50">
                        <input type="checkbox" class="w-4 h-4" checked disabled>
                        <span class="text-sm text-gray-500">
                            <strong>⚙️ Ustawienia</strong> (tylko do odczytu)
                        </span>
                    </div>
                    <input type="hidden" name="can_settings" value="1">
                    @endif
                @endif
            </div>
        </div>

        <!-- Przyciski -->
        <div class="flex gap-2 pt-4 border-t">
            <button 
                type="submit" 
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
                Zapisz zmiany
            </button>
            <a 
                href="{{ route('magazyn.settings') }}" 
                class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500"
            >
                Anuluj
            </a>
        </div>
    </form>
</div>

<script>
    // Auto-generowanie skróconej nazwy użytkownika w formularzu edycji
    var editFirstNameInput = document.getElementById('edit_first_name');
    var editLastNameInput = document.getElementById('edit_last_name');
    var editShortNameInput = document.getElementById('edit_short_name');
    
    if (editFirstNameInput && editLastNameInput && editShortNameInput) {
        function generateEditShortName() {
            var firstName = editFirstNameInput.value.trim();
            var lastName = editLastNameInput.value.trim();
            
            if (firstName.length >= 3 && lastName.length >= 3) {
                var firstPart = firstName.charAt(0).toUpperCase() + firstName.substring(1, 3).toLowerCase();
                var lastPart = lastName.charAt(0).toUpperCase() + lastName.substring(1, 3).toLowerCase();
                editShortNameInput.value = firstPart + lastPart;
            }
        }
        
        editFirstNameInput.addEventListener('input', generateEditShortName);
        editLastNameInput.addEventListener('input', generateEditShortName);
    }

    // === NOWY SYSTEM HIERARCHICZNYCH UPRAWNIEŃ ===
    
    // Obsługa rozwijania/zwijania drzewek i strzałek
    document.querySelectorAll('.parent-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const targetId = this.getAttribute('data-target');
            const target = document.getElementById(targetId);
            const arrow = this.closest('label').querySelector('.toggle-arrow');
            
            if (target) {
                if (this.checked) {
                    target.classList.remove('hidden');
                    if (arrow) arrow.textContent = '▼';
                } else {
                    target.classList.add('hidden');
                    if (arrow) arrow.textContent = '▶';
                    
                    // Odznacz wszystkie podrzędne checkboxy
                    uncheckChildren(targetId);
                }
            }
        });
        
        // Kliknięcie w strzałkę tylko rozwija/zwija bez zmiany checkboxa
        const arrow = checkbox.closest('label').querySelector('.toggle-arrow');
        if (arrow) {
            arrow.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const targetId = checkbox.getAttribute('data-target');
                const target = document.getElementById(targetId);
                
                if (target && checkbox.checked) {
                    if (target.classList.contains('hidden')) {
                        target.classList.remove('hidden');
                        this.textContent = '▼';
                    } else {
                        target.classList.add('hidden');
                        this.textContent = '▶';
                    }
                }
            });
        }
    });
    
    // Funkcja rekurencyjnego odznaczania dzieci
    function uncheckChildren(parentId) {
        const parent = document.getElementById(parentId);
        if (!parent) return;
        
        const childCheckboxes = parent.querySelectorAll('.child-checkbox');
        childCheckboxes.forEach(function(child) {
            child.checked = false;
            
            // Jeśli dziecko też jest rodzicem (nested), odznacz jego dzieci
            if (child.classList.contains('parent-checkbox')) {
                const childTargetId = child.getAttribute('data-target');
                const childTarget = document.getElementById(childTargetId);
                if (childTarget) {
                    childTarget.classList.add('hidden');
                    const childArrow = child.closest('label').querySelector('.toggle-arrow');
                    if (childArrow) childArrow.textContent = '▶';
                    uncheckChildren(childTargetId);
                }
            }
        });
    }
</script>

</body>
</html>
