<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Zrób nową Ofertę</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
    @include('parts.menu')
                @php
                    try {
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
                        $companyName = $companySettings && $companySettings->name ? $companySettings->name : 'Moja Firma';
                    } catch (\Exception $e) {
                        $logoPath = '/logo.png';
                        $companyName = 'Moja Firma';
                    }
                @endphp
                <img src="{{ $logoPath }}" alt="{{ $companyName }}" class="h-10">
                <span class="text-xl font-bold">{{ $companyName }}</span>
                            <span id="datetime" class="ml-4 px-3 py-2 text-sm bg-white-200 text-gray-400 rounded whitespace-nowrap"></span>
            </div>
            </div>
        </div>
    </header>
    <main class="flex-1 p-6">
        <div class="max-w-5xl mx-auto bg-white rounded shadow p-6 relative">
            <a href="{{ route('offers') }}" class="absolute top-4 left-4 flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 shadow rounded-full text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition z-10">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' /></svg>
                Powrót
            </a>
            
            <h1 class="text-3xl font-bold mb-6 text-center mt-12">Tworzenie nowej oferty</h1>
            
            <form action="#" method="POST" class="space-y-6" onkeydown="return event.key != 'Enter';">
                @csrf
                
                <!-- Przypisanie do szansy CRM -->
                <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded">
                    <h3 class="text-lg font-semibold mb-3 text-green-900">🎯 Przypisanie do szansy CRM (opcjonalnie)</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Wybierz szansę CRM</label>
                        <select id="crm-deal-select" name="crm_deal_id" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500" onchange="updateDealInfo(this.value)">
                            <option value="">-- Brak przypisania --</option>
                            @foreach($deals as $d)
                                <option value="{{ $d->id }}" 
                                    data-name="{{ $d->name }}"
                                    data-company="{{ $d->company ? $d->company->name : '' }}"
                                    data-value="{{ number_format($d->value, 2, ',', ' ') }}"
                                    data-currency="{{ $d->currency }}"
                                    @if(isset($deal) && $deal && $deal->id == $d->id) selected @endif>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="deal-info" class="mt-3 p-3 bg-blue-50 border-l-4 border-blue-500 rounded @if(!isset($deal) || !$deal) hidden @endif">
                        <p class="text-sm font-medium text-blue-800">Szczegóły szansy:</p>
                        <p class="mt-1 text-sm text-blue-700">
                            <strong id="deal-name">{{ isset($deal) && $deal ? $deal->name : '' }}</strong>
                            <span id="deal-company" class="ml-2">{{ isset($deal) && $deal && $deal->company ? '• Firma: ' . $deal->company->name : '' }}</span>
                            <span id="deal-value" class="ml-2">{{ isset($deal) && $deal ? '• Wartość: ' . number_format($deal->value, 2, ',', ' ') . ' ' . $deal->currency : '' }}</span>
                        </p>
                    </div>
                </div>
                
                <!-- Podstawowe informacje -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nr oferty</label>
                        @if($errors->has('offer_number'))
                            <div class="text-red-600 text-sm mb-1">{{ $errors->first('offer_number') }}</div>
                        @endif
                        @php
                            $offerSettings = null;
                            try {
                                $offerSettings = \DB::table('offer_settings')->first();
                            } catch (\Exception $e) {
                                // Table doesn't exist yet
                            }
                            
                            // Use preview number from controller if available
                            if (!isset($previewNumber)) {
                                $previewNumber = 'OFF_' . date('Ymd') . '_0001';
                            }
                            $element4Customer = '';
                            $element4Enabled = false;
                            
                            if ($offerSettings) {
                                // Przygotuj element 4 (klient) osobno
                                $element4Customer = '';
                                $element4Enabled = false;
                                if (($offerSettings->element4_type ?? 'empty') === 'customer') {
                                    $element4Enabled = true;
                                    if (isset($deal) && $deal && $deal->company && $deal->company->supplier && $deal->company->supplier->short_name) {
                                        $element4Customer = $deal->company->supplier->short_name;
                                    } elseif (isset($deal) && $deal && $deal->company && $deal->company->name) {
                                        // Fallback: użyj pierwszych 5 znaków nazwy jako skrótu
                                        $element4Customer = strtoupper(substr($deal->company->name, 0, 5));
                                    }
                                }
                            }
                        @endphp
                        <input type="text" name="offer_number_base" id="offer_number_base" value="{{ $previewNumber }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="updateFinalOfferNumber()" required>
                            <small class="text-gray-500 text-xs">Możesz zmienić domyślny numer</small>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                            <input type="date" name="offer_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    @if($element4Enabled)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Element 4 - Klient</label>
                        <input type="text" id="element4_customer" value="{{ $element4Customer }}" data-separator="{{ $offerSettings->separator3 ?? '_' }}" class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100" readonly>
                        <small class="text-gray-500 text-xs">Skrót z: Magazyn → Ustawienia → Dostawcy i klienci</small>
                        <div class="mt-2 space-y-2">
                            <label class="flex items-center text-sm">
                                <input type="checkbox" name="include_element4" id="include_element4" value="1" checked class="mr-2" onchange="updateFinalOfferNumber()">
                                <span>Dołącz do numeru oferty</span>
                            </label>
                            <div class="text-xs text-gray-600 bg-blue-50 p-2 rounded">
                                <strong>Pełny numer:</strong> <span id="full_offer_preview">{{ $previewNumber . ($offerSettings->separator3 ?? '_') . $element4Customer }}</span>
                            </div>
                        </div>
                        <input type="hidden" name="offer_number" id="offer_number" value="{{ $previewNumber . ($offerSettings->separator3 ?? '_') . $element4Customer }}">
                    </div>
                    @else
                    <input type="hidden" name="offer_number" id="offer_number" value="{{ $previewNumber }}">
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tytuł oferty</label>
                        <input type="text" name="offer_title" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>

                <!-- Dane klienta -->
                <div class="border border-blue-300 rounded p-4 bg-blue-50">
                    <h3 class="text-lg font-semibold mb-4 text-blue-900">👤 Dane klienta</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Wybierz z bazy lub wpisz ręcznie</label>
                        <div class="flex gap-2">
                            <select id="company-select" class="flex-1 px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="fillCustomerData(this.value)">
                                <option value="">-- Wybierz firmę z CRM --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" 
                                        data-name="{{ $company->name }}"
                                        data-short-name="{{ $company->supplier->short_name ?? '' }}"
                                        data-nip="{{ $company->nip ?? '' }}"
                                        data-address="{{ $company->address ?? '' }}"
                                        data-city="{{ $company->city ?? '' }}"
                                        data-postal="{{ $company->postal_code ?? '' }}"
                                        data-phone="{{ $company->phone ?? '' }}"
                                        data-email="{{ $company->email ?? '' }}"
                                        @if(isset($deal) && $deal && $deal->company_id == $company->id) selected @endif>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" onclick="clearCustomerData()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Wyczyść</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nazwa firmy *</label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->name : '' }}" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">NIP</label>
                            <div class="flex gap-1">
                                <input type="text" id="customer_nip" name="customer_nip" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->nip : '' }}" class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" onclick="fetchFromGUS()" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700 whitespace-nowrap">GUS</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Telefon</label>
                            <input type="text" id="customer_phone" name="customer_phone" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->phone : '' }}" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Adres</label>
                            <input type="text" id="customer_address" name="customer_address" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->address : '' }}" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="customer_email" name="customer_email" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->email : '' }}" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Miasto</label>
                            <input type="text" id="customer_city" name="customer_city" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->city : '' }}" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Kod pocztowy</label>
                            <input type="text" id="customer_postal_code" name="customer_postal_code" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->postal_code : '' }}" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Sekcja Usługi -->
                <div class="border border-gray-300 rounded">
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center justify-between hover:bg-gray-100 transition" onclick="toggleSection('services')">
                            <span class="font-semibold text-lg section-name" id="services-name-label">Usługi</span>
                            <svg id="services-icon" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('services')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwę">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('services')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Usuń sekcję">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="services-name-input" name="services_name" value="Usługi">
                    <div id="services-content" class="p-4 hidden">
                        <table class="w-full mb-4 text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left">Nazwa</th>
                                    <th class="p-1 text-left w-48">Opis</th>
                                    <th class="p-1 text-left w-16">Ilość</th>
                                    <th class="p-1 text-left">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena</th>
                                    <th class="p-1 text-left w-24">Wartość</th>
                                    <th class="p-1 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="services-table">
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                    <td class="p-1"><input type="text" name="services[0][name]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                                    <td class="p-1"><input type="text" name="services[0][type]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                                    <td class="p-1"><input type="number" min="1" value="1" name="services[0][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="services" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1">
                                        <select name="services[0][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                            <option value="">-- brak --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->name }}">{{ $supplier->short_name ?: $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-1"><input type="number" step="0.01" name="services[0][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="services" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="services[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="services" readonly></td>
                                    <td class="p-1"><div class="flex gap-1 items-center"><button type="button" onclick="removeRow(this, 'services')" class="text-red-600 hover:text-red-800 text-xs">✕</button>
                                    <button type="button" onclick="addProductToCatalog(this, 'services', 0)" class="px-1 py-0.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs whitespace-nowrap">Dod. do kat.</button></div></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex gap-2">
                            <button type="button" onclick="addRow('services')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                            <button type="button" onclick="openPartsCatalog('services')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">📂 Wybierz z katalogu</button>
                        </div>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="services-total" class="font-bold text-lg">0.00 zł</span>
                        </div>
                    </div>
                </div>

                <!-- Sekcja Prace własne -->
                <div class="border border-gray-300 rounded">
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center justify-between hover:bg-gray-100 transition" onclick="toggleSection('works')">
                            <span class="font-semibold text-lg section-name" id="works-name-label">Prace własne</span>
                            <svg id="works-icon" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('works')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwę">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('works')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Usuń sekcję">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="works-name-input" name="works_name" value="Prace własne">
                    <div id="works-content" class="p-4 hidden">
                        <table class="w-full mb-4 text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left">Nazwa</th>
                                    <th class="p-1 text-left w-48">Opis</th>
                                    <th class="p-1 text-left w-16">Ilość</th>
                                    <th class="p-1 text-left">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena</th>
                                    <th class="p-1 text-left w-24">Wartość</th>
                                    <th class="p-1 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="works-table">
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                    <td class="p-1"><input type="text" name="works[0][name]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                                    <td class="p-1"><input type="text" name="works[0][type]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                                    <td class="p-1"><input type="number" min="1" value="1" name="works[0][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="works" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1">
                                        <select name="works[0][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                            <option value="">-- brak --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->name }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[0][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="works" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="works" readonly></td>
                                    <td class="p-1"><div class="flex gap-1 items-center"><button type="button" onclick="removeRow(this, 'works')" class="text-red-600 hover:text-red-800 text-xs">✕</button>
                                    <button type="button" onclick="addProductToCatalog(this, 'works', 0)" class="px-1 py-0.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs whitespace-nowrap">Dod. do kat.</button></div></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex gap-2">
                            <button type="button" onclick="addRow('works')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                            <button type="button" onclick="openPartsCatalog('works')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">📂 Wybierz z katalogu</button>
                        </div>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="works-total" class="font-bold text-lg">0.00 zł</span>
                        </div>
                    </div>
                </div>

                <!-- Sekcja Materiały -->
                <div class="border border-gray-300 rounded">
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center justify-between hover:bg-gray-100 transition" onclick="toggleSection('materials')">
                            <span class="font-semibold text-lg section-name" id="materials-name-label">Materiały</span>
                            <svg id="materials-icon" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('materials')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwę">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('materials')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Usuń sekcję">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="materials-name-input" name="materials_name" value="Materiały">
                    <div id="materials-content" class="p-4 hidden">
                        <table class="w-full mb-4 text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left">Nazwa</th>
                                    <th class="p-1 text-left w-48">Opis</th>
                                    <th class="p-1 text-left w-16">Ilość</th>
                                    <th class="p-1 text-left">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena</th>
                                    <th class="p-1 text-left w-24">Wartość</th>
                                    <th class="p-1 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="materials-table">
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                    <td class="p-1">
                                        <div class="relative">
                                            <input type="text" 
                                                name="materials[0][name]" 
                                                class="w-full px-1 py-0.5 border rounded text-xs part-search-input" 
                                                data-index="0"
                                                placeholder="Nazwa lub wyszukaj..."
                                                autocomplete="off">
                                            <div class="part-search-results absolute z-10 w-full bg-white border border-gray-300 rounded mt-1 shadow-lg hidden max-h-60 overflow-y-auto"></div>
                                        </div>
                                    </td>
                                    <td class="p-1"><input type="text" name="materials[0][type]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                                    <td class="p-1"><input type="number" min="1" value="1" name="materials[0][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="materials" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1">
                                        <select name="materials[0][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                            <option value="">-- brak --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->name }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[0][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="materials" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="materials" readonly></td>
                                    <td class="p-1"><div class="flex gap-1 items-center"><button type="button" onclick="removeRow(this, 'materials')" class="text-red-600 hover:text-red-800 text-xs">✕</button>
                                    <button type="button" onclick="addProductToCatalog(this, 'materials', 0)" class="px-1 py-0.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs whitespace-nowrap">Dod. do kat.</button></div></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex gap-2">
                            <button type="button" onclick="addRow('materials')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                            <button type="button" onclick="openPartsCatalog('materials')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">📂 Wybierz z katalogu</button>
                        </div>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="materials-total" class="font-bold text-lg">0.00 zł</span>
                        </div>
                    </div>
                </div>

                <!-- Dynamiczne sekcje niestandardowe -->
                <div id="custom-sections-container"></div>

                <!-- Przycisk dodawania nowej sekcji -->
                <div class="text-center">
                    <button type="button" onclick="addCustomSection()" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2 mx-auto">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Dodaj nową sekcję
                    </button>
                </div>

                <!-- Suma końcowa -->
                <div class="bg-gray-50 p-4 rounded border border-gray-300">
                    <div class="text-right">
                        <span class="text-xl font-semibold">Suma końcowa: </span>
                        <span id="grand-total" class="text-2xl font-bold text-blue-600">0.00 zł</span>
                    </div>
                </div>

                <!-- Opis oferty -->
                <div class="mt-8">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opis oferty</label>
                    <div id="offer_description_editor" style="min-height: 150px; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem;"></div>
                    <textarea id="offer_description" name="offer_description" style="display: none;"></textarea>
                </div>

                <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
                <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const quill = new Quill('#offer_description_editor', {
                        theme: 'snow',
                        placeholder: 'Dodaj opis oferty...',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'font': [] }],
                                [{ 'align': [] }],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['link'],
                                ['clean']
                            ]
                        }
                    });
                    
                    // Synchronizuj z hidden textarea
                    quill.on('text-change', function() {
                        document.getElementById('offer_description').value = quill.root.innerHTML;
                    });
                });
                </script>

                <!-- Miejsce docelowe oferty -->
                <div class="border-t pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gdzie ma wylądować oferta?</label>
                    <select name="destination" class="w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="portfolio">Portfolio</option>
                        <option value="inprogress">Oferty w toku</option>
                    </select>
                </div>

                <!-- Przycisk Zapisz -->
                <div class="text-center">
                    <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg text-lg font-semibold hover:bg-green-700 transition">
                        Zapisz ofertę
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Modal katalogu części -->
    <div id="parts-catalog-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-xl font-bold">Katalog części z magazynu</h3>
                <button type="button" onclick="closePartsCatalog()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            
            <div class="p-4 border-b">
                <input type="text" 
                    id="catalog-search" 
                    placeholder="Szukaj w katalogu..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="flex-1 overflow-y-auto p-4">
                <div id="catalog-loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-600">Wczytywanie katalogu...</p>
                </div>
                <div id="catalog-content" class="hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="p-2 text-left w-10">
                                    <input type="checkbox" id="select-all-parts" onchange="toggleSelectAll()">
                                </th>
                                <th class="p-2 text-left">Nazwa</th>
                                <th class="p-2 text-left">Opis</th>
                                <th class="p-2 text-left">Dostawca</th>
                                <th class="p-2 text-left">Ilość</th>
                                <th class="p-2 text-left">Cena</th>
                            </tr>
                        </thead>
                        <tbody id="catalog-parts-list">
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="p-4 border-t flex justify-between items-center">
                <span id="selected-count" class="text-gray-600">Wybrano: 0</span>
                <div class="flex gap-2">
                    <button type="button" onclick="closePartsCatalog()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Anuluj</button>
                    <button type="button" onclick="addSelectedParts()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Dodaj wybrane</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function addProductToCatalog(button, section, index) {
            const row = button.closest('tr');
            let nameInput = row.querySelector(`[name^="${section}[${index}][name]"]`);
            let typeInput = row.querySelector(`[name^="${section}[${index}][type]"]`);
            let quantityInput = row.querySelector(`[name^="${section}[${index}][quantity]"]`);
            let supplierInput = row.querySelector(`[name^="${section}[${index}][supplier]"]`);
            let priceInput = row.querySelector(`[name^="${section}[${index}][price]"]`);
            if (!nameInput) nameInput = row.querySelector('input[name*="[name]"]');
            if (!typeInput) typeInput = row.querySelector('input[name*="[type]"]');
            if (!quantityInput) quantityInput = row.querySelector('input[name*="[quantity]"]');
            if (!supplierInput) supplierInput = row.querySelector('select[name*="[supplier]"]');
            if (!priceInput) priceInput = row.querySelector('input[name*="[price]"]');
            const data = {
                name: nameInput ? nameInput.value : '',
                type: typeInput ? typeInput.value : '',
                quantity: quantityInput ? quantityInput.value : '',
                supplier: supplierInput ? supplierInput.value : '',
                price: priceInput ? priceInput.value : ''
            };
            try {
                const response = await fetch('/api/parts/catalog/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                if (response.ok) {
                    alert('Produkt dodany do katalogu!');
                } else {
                    alert('Błąd dodawania produktu do katalogu.');
                }
            } catch (e) {
                alert('Błąd sieci podczas dodawania produktu.');
            }
        }

        let rowCounters = {
            services: 1,
            works: 1,
            materials: 1
        };
        
        let customSectionCounter = 0;
        let customSections = [];

        function toggleSection(section) {
            const content = document.getElementById(section + '-content');
            const icon = document.getElementById(section + '-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        function calculateRowValue(input) {
            const row = input.closest('tr');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');
            const valueInput = row.querySelector('.value-input');
            
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const value = quantity * price;
            
            valueInput.value = value.toFixed(2);
            
            const section = input.dataset.section;
            calculateTotal(section);
        }

        function addRow(section) {
            const table = document.getElementById(section + '-table');
            const rowCount = rowCounters[section];
            
            const row = document.createElement('tr');
            
            if (section === 'materials') {
                row.innerHTML = `
                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="${rowCount + 1}" readonly></td>
                    <td class="p-1">
                        <div class="relative">
                            <input type="text" 
                                name="${section}[${rowCount}][name]" 
                                class="w-full px-1 py-0.5 border rounded text-xs part-search-input" 
                                data-index="${rowCount}"
                                placeholder="Nazwa lub wyszukaj..."
                                autocomplete="off">
                            <div class="part-search-results absolute z-10 w-full bg-white border border-gray-300 rounded mt-1 shadow-lg hidden max-h-60 overflow-y-auto"></div>
                        </div>
                    </td>
                    <td class="p-1"><input type="text" name="${section}[${rowCount}][type]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                    <td class="p-1"><input type="number" min="1" value="1" name="${section}[${rowCount}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                    <td class="p-1">
                        <select name="${section}[${rowCount}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                            <option value="">-- brak --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->name }}">{{ $supplier->short_name ?: $supplier->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-1"><input type="number" step="0.01" name="${section}[${rowCount}][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                    <td class="p-1"><input type="number" step="0.01" name="${section}[${rowCount}][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${section}" readonly></td>
                    <td class="p-1"><button type="button" onclick="addProductToCatalog(this, '${section}', ${rowCount})" class="px-1 py-0.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs whitespace-nowrap">Dod. do kat.</button></td>
                `;
            } else {
                row.innerHTML = `
                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="${rowCount + 1}" readonly></td>
                    <td class="p-1"><input type="text" name="${section}[${rowCount}][name]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                    <td class="p-1"><input type="text" name="${section}[${rowCount}][type]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                    <td class="p-1"><input type="number" min="1" value="1" name="${section}[${rowCount}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                    <td class="p-1">
                        <select name="${section}[${rowCount}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                            <option value="">-- brak --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->name }}">{{ $supplier->short_name ?: $supplier->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-1"><input type="number" step="0.01" name="${section}[${rowCount}][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                    <td class="p-1"><input type="number" step="0.01" name="${section}[${rowCount}][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${section}" readonly></td>
                    <td class="p-1"><button type="button" onclick="addProductToCatalog(this, '${section}', ${rowCount})" class="px-1 py-0.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs whitespace-nowrap">Dod. do kat.</button></td>
                `;
            }
            
            table.appendChild(row);
            rowCounters[section]++;
            updateRowNumbers(section);
            
            if (section === 'materials') {
                initPartSearch();
            }
        }

        function removeRow(button, section) {
            button.closest('tr').remove();
            updateRowNumbers(section);
            calculateTotal(section);
        }

        function updateRowNumbers(section) {
            const rows = document.querySelectorAll(`#${section}-table tr`);
            rows.forEach((row, index) => {
                const numberInput = row.querySelector('td:first-child input[type="number"][readonly]');
                if (numberInput) {
                    numberInput.value = index + 1;
                }
            });
        }

        function calculateTotal(section) {
            const inputs = document.querySelectorAll(`#${section}-table .value-input`);
            let total = 0;
            
            inputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });
            
            document.getElementById(section + '-total').textContent = total.toFixed(2) + ' zł';
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            const servicesInputs = document.querySelectorAll('#services-table .value-input');
            const worksInputs = document.querySelectorAll('#works-table .value-input');
            const materialsInputs = document.querySelectorAll('#materials-table .value-input');
            
            let grandTotal = 0;
            
            servicesInputs.forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });
            worksInputs.forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });
            materialsInputs.forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });
            
            // Dodaj sumy z niestandardowych sekcji
            customSections.forEach(sectionId => {
                const inputs = document.querySelectorAll(`#custom-${sectionId}-table .value-input`);
                inputs.forEach(input => {
                    grandTotal += parseFloat(input.value) || 0;
                });
            });
            
            document.getElementById('grand-total').textContent = grandTotal.toFixed(2) + ' zł';
        }

        // ===========================================
        // OBSŁUGA DYNAMICZNYCH SEKCJI
        // ===========================================
        function addCustomSection() {
            const sectionName = prompt('Podaj nazwę nowej sekcji:');
            if (!sectionName || sectionName.trim() === '') {
                return;
            }
            customSectionCounter++;
            const sectionId = `custom${customSectionCounter}`;
            customSections.push(customSectionCounter);
            rowCounters[sectionId] = 1;
            const container = document.getElementById('custom-sections-container');
            const sectionDiv = document.createElement('div');
            sectionDiv.className = 'border border-gray-300 rounded';
            sectionDiv.id = `section-${sectionId}`;
            sectionDiv.innerHTML = `
                <div class="flex items-center justify-between p-4 bg-gray-50">
                    <button type="button" class="flex-1 flex items-center justify-between hover:bg-gray-100 transition" onclick="toggleSection('${sectionId}')">
                        <span class="font-semibold text-lg section-name" id="${sectionId}-name-label">${escapeHtml(sectionName.trim())}</span>
                    </button>
                    <button type="button" onclick="editSectionName('${sectionId}', ${customSectionCounter})" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwę">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                    </button>
                    <button type="button" onclick="removeCustomSection('${sectionId}')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Usuń sekcję">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
                <div id="${sectionId}-content" class="p-4 hidden">
                    <input type="hidden" id="${sectionId}-name-input" name="custom_sections[${customSectionCounter}][name]" value="${escapeHtml(sectionName.trim())}">
                    <table class="w-full mb-4 text-xs">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-1 text-left w-10">Nr</th>
                                <th class="p-1 text-left">Nazwa</th>
                                <th class="p-1 text-left w-48">Opis</th>
                                <th class="p-1 text-left w-16">Ilość</th>
                                <th class="p-1 text-left">Dostawca</th>
                                <th class="p-1 text-left w-24">Cena (zł)</th>
                                <th class="p-1 text-left w-24">Wartość (zł)</th>
                                <th class="p-1 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="${sectionId}-table">
                            <tr>
                                <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                <td class="p-1"><input type="text" name="custom_sections[${customSectionCounter}][items][0][name]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                                <td class="p-1"><input type="text" name="custom_sections[${customSectionCounter}][items][0][type]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                                <td class="p-1"><input type="number" min="1" value="1" name="custom_sections[${customSectionCounter}][items][0][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${sectionId}" onchange="calculateRowValue(this)"></td>
                                <td class="p-1">
                                    <select name="custom_sections[${customSectionCounter}][items][0][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                        <option value="">-- brak --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->name }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${customSectionCounter}][items][0][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${sectionId}" onchange="calculateRowValue(this)"></td>
                                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${customSectionCounter}][items][0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${sectionId}" readonly></td>
                                <td class="p-1"><button type="button" onclick="addProductToCatalog(this, 'custom_sections', 0)" class="px-1 py-0.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs whitespace-nowrap">Dod. do kat.</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="flex gap-2">
                        <button type="button" onclick="addCustomRow('${sectionId}', ${customSectionCounter})" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                        <button type="button" onclick="openPartsCatalog('${sectionId}')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">📂 Wybierz z katalogu</button>
                    </div>
                    <div class="mt-4 text-right">
                        <span class="font-semibold">Suma: </span>
                        <span id="${sectionId}-total" class="font-bold text-lg">0.00 zł</span>
                    </div>
                </div>
            `;
            container.appendChild(sectionDiv);
            // Automatycznie rozwiń nową sekcję
            toggleSection(sectionId);
        }

        function editSectionName(sectionId, sectionNumber) {
            const label = document.getElementById(`${sectionId}-name-label`);
            if (!label) return;
            const current = label.textContent;
            const newName = prompt('Edytuj nazwę sekcji:', current);
            if (newName && newName.trim() !== '') {
                label.textContent = newName.trim();
                // For custom sections, update hidden input
                const inputId = `${sectionId}-name-input`;
                const input = document.getElementById(inputId);
                if (input) input.value = newName.trim();
            }
        }

        function removeMainSection(sectionId) {
            if (!confirm('Czy na pewno chcesz usunąć tę sekcję?')) {
                return;
            }
            // Hide the section and clear its rows
            const sectionDiv = document.getElementById(`section-${sectionId}`) || document.querySelector(`[onclick*="toggleSection('${sectionId}')"]`).closest('.border');
            if (sectionDiv) {
                sectionDiv.style.display = 'none';
            }
            // Clear table rows
            const table = document.getElementById(`${sectionId}-table`);
            if (table) {
                table.innerHTML = '';
            }
            // Reset total
            const total = document.getElementById(`${sectionId}-total`);
            if (total) {
                total.textContent = '0.00 zł';
            }
            calculateGrandTotal();
        }
        
        function removeCustomSection(sectionId) {
            if (!confirm('Czy na pewno chcesz usunąć tę sekcję?')) {
                return;
            }
            
            const sectionDiv = document.getElementById(`section-${sectionId}`);
            if (sectionDiv) {
                sectionDiv.remove();
                const sectionNumber = parseInt(sectionId.replace('custom', ''));
                const index = customSections.indexOf(sectionNumber);
                if (index > -1) {
                    customSections.splice(index, 1);
                }
                delete rowCounters[sectionId];
                calculateGrandTotal();
            }
        }
        
        function addCustomRow(sectionId, sectionNumber) {
            const table = document.getElementById(`${sectionId}-table`);
            const rowCount = rowCounters[sectionId];
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="${rowCount + 1}" readonly></td>
                <td class="p-1"><input type="text" name="custom_sections[${sectionNumber}][items][${rowCount}][name]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                <td class="p-1"><input type="text" name="custom_sections[${sectionNumber}][items][${rowCount}][type]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                <td class="p-1"><input type="number" min="1" value="1" name="custom_sections[${sectionNumber}][items][${rowCount}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${sectionId}" onchange="calculateRowValue(this)"></td>
                <td class="p-1">
                    <select name="custom_sections[${sectionNumber}][items][${rowCount}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                        <option value="">-- brak --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->name }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${sectionNumber}][items][${rowCount}][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${sectionId}" onchange="calculateRowValue(this)"></td>
                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${sectionNumber}][items][${rowCount}][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${sectionId}" readonly></td>
                <td class="p-1"><button type="button" onclick="addProductToCatalog(this, 'custom_sections', ${rowCount})" class="px-1 py-0.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs whitespace-nowrap">Dod. do kat.</button></td>
            `;
            
            table.appendChild(row);
            rowCounters[sectionId]++;
            updateRowNumbers(sectionId);
        }

        // ===========================================
        // OBSŁUGA WYSZUKIWANIA CZĘŚCI
        // ===========================================
        let searchTimeout;
        
        function initPartSearch() {
            document.querySelectorAll('.part-search-input').forEach(input => {
                if (input.dataset.initialized) return;
                input.dataset.initialized = 'true';
                
                input.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    const query = e.target.value;
                    const resultsDiv = e.target.closest('.relative').querySelector('.part-search-results');
                    
                    if (query.length < 2) {
                        resultsDiv.classList.add('hidden');
                        return;
                    }
                    
                    searchTimeout = setTimeout(() => {
                        fetch(`/api/parts/search?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(parts => {
                                if (parts.length === 0) {
                                    resultsDiv.innerHTML = '<div class="p-2 text-gray-500 text-sm">Nie znaleziono części</div>';
                                    resultsDiv.classList.remove('hidden');
                                    return;
                                }
                                
                                resultsDiv.innerHTML = parts.map(part => `
                                    <div class="p-2 hover:bg-gray-100 cursor-pointer border-b part-search-item" 
                                         data-name="${part.name}"
                                         data-supplier="${part.supplier || ''}"
                                         data-price="${part.net_price || ''}">
                                        <div class="font-medium text-sm">${part.name}</div>
                                        <div class="text-xs text-gray-600">
                                            ${part.description || ''} | 
                                            Dostępne: ${part.quantity || 0} szt. | 
                                            Cena: ${part.net_price || '0.00'} zł
                                        </div>
                                    </div>
                                `).join('');
                                
                                resultsDiv.classList.remove('hidden');
                                
                                // Obsługa kliknięcia na wynik
                                resultsDiv.querySelectorAll('.part-search-item').forEach(item => {
                                    item.addEventListener('click', function() {
                                        const row = e.target.closest('tr');
                                        row.querySelector('[name*="[name]"]').value = this.dataset.name;
                                        row.querySelector('[name*="[supplier]"]').value = this.dataset.supplier;
                                        const priceInput = row.querySelector('[name*="[price]"]');
                                        priceInput.value = this.dataset.price;
                                        resultsDiv.classList.add('hidden');
                                        calculateRowValue(priceInput);
                                    });
                                });
                            })
                            .catch(error => {
                                console.error('Błąd wyszukiwania:', error);
                                resultsDiv.classList.add('hidden');
                            });
                    }, 300);
                });
                
                // Ukryj wyniki po kliknięciu poza polem
                document.addEventListener('click', function(event) {
                    if (!input.contains(event.target)) {
                        const resultsDiv = input.closest('.relative').querySelector('.part-search-results');
                        resultsDiv.classList.add('hidden');
                    }
                });
            });
        }
        
        // Inicjalizuj wyszukiwanie dla pierwszego wiersza
        document.addEventListener('DOMContentLoaded', function() {
            initPartSearch();
        });

        // ===========================================
        // OBSŁUGA KATALOGU CZĘŚCI
        // ===========================================
        let allParts = [];
        let filteredParts = [];
        let currentCatalogSection = 'materials';
        
        async function openPartsCatalog(section = 'materials') {
            currentCatalogSection = section;
            const modal = document.getElementById('parts-catalog-modal');
            modal.classList.remove('hidden');
            
            if (allParts.length === 0) {
                await loadPartsCatalog();
            }
        }
        
        function closePartsCatalog() {
            document.getElementById('parts-catalog-modal').classList.add('hidden');
            document.getElementById('catalog-search').value = '';
            document.querySelectorAll('.part-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('select-all-parts').checked = false;
            updateSelectedCount();
        }
        
        async function loadPartsCatalog() {
            try {
                const response = await fetch('/api/parts/catalog', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Błąd HTTP:', response.status, errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Otrzymane dane z API:', data);
                
                // Sprawdź czy odpowiedź jest tablicą
                if (!Array.isArray(data)) {
                    console.error('API nie zwróciło tablicy:', data);
                    throw new Error('API zwróciło nieprawidłowy format danych');
                }
                
                allParts = data;
                filteredParts = [...allParts];
                
                document.getElementById('catalog-loading').classList.add('hidden');
                document.getElementById('catalog-content').classList.remove('hidden');
                
                renderCatalog();
                setupCatalogSearch();
            } catch (error) {
                console.error('Błąd ładowania katalogu:', error);
                
                // Ukryj loading, pokaż treść (która wyświetli komunikat o błędzie)
                document.getElementById('catalog-loading').classList.add('hidden');
                document.getElementById('catalog-content').classList.remove('hidden');
                
                // Wyświetl komunikat o błędzie w tabeli
                const tbody = document.getElementById('catalog-parts-list');
                tbody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-red-600">
                    Nie udało się załadować katalogu: ${error.message}<br>
                    <small>Sprawdz konsolę przeglądarki (F12) aby zobaczyć szczegóły.</small>
                </td></tr>`;
                
                alert('Nie udało się załadować katalogu części. Błąd: ' + error.message);
            }
        }
        
        function renderCatalog() {
            const tbody = document.getElementById('catalog-parts-list');
            
            if (filteredParts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-gray-500">Nie znaleziono części</td></tr>';
                return;
            }
            
            tbody.innerHTML = filteredParts.map(part => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">
                        <input type="checkbox" 
                            class="part-checkbox" 
                            data-id="${part.id}"
                            data-name="${escapeHtml(part.name)}"
                            data-supplier="${escapeHtml(part.supplier || '')}"
                            data-price="${part.net_price || 0}"
                            onchange="updateSelectedCount()">
                    </td>
                    <td class="p-2 font-medium">${escapeHtml(part.name)}</td>
                    <td class="p-2 text-gray-600">${escapeHtml(part.description || '-')}</td>
                    <td class="p-2">${escapeHtml(part.supplier || '-')}</td>
                    <td class="p-2">${part.quantity || 0}</td>
                    <td class="p-2 font-medium">${parseFloat(part.net_price || 0).toFixed(2)} zł</td>
                </tr>
            `).join('');
        }
        
        function setupCatalogSearch() {
            const searchInput = document.getElementById('catalog-search');
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const query = this.value.toLowerCase();
                    
                    if (query === '') {
                        filteredParts = [...allParts];
                    } else {
                        filteredParts = allParts.filter(part => 
                            part.name.toLowerCase().includes(query) ||
                            (part.description && part.description.toLowerCase().includes(query)) ||
                            (part.supplier && part.supplier.toLowerCase().includes(query))
                        );
                    }
                    
                    renderCatalog();
                    document.getElementById('select-all-parts').checked = false;
                    updateSelectedCount();
                }, 300);
            });
        }
        
        function toggleSelectAll() {
            const selectAll = document.getElementById('select-all-parts');
            const checkboxes = document.querySelectorAll('.part-checkbox');
            
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            
            updateSelectedCount();
        }
        
        function updateSelectedCount() {
            const count = document.querySelectorAll('.part-checkbox:checked').length;
            document.getElementById('selected-count').textContent = `Wybrano: ${count}`;
        }
        
        function addSelectedParts() {
            const selected = document.querySelectorAll('.part-checkbox:checked');
            
            if (selected.length === 0) {
                alert('Nie wybrano żadnych części');
                return;
            }
            
            const section = currentCatalogSection;
            const isCustomSection = section.startsWith('custom');
            
            selected.forEach(checkbox => {
                const name = checkbox.dataset.name;
                const supplier = checkbox.dataset.supplier;
                const price = checkbox.dataset.price;
                
                // Pobierz tabelę dla odpowiedniej sekcji
                const table = document.getElementById(`${section}-table`);
                const rowCount = rowCounters[section];
                
                // Ustal odpowiednią nazwę pola w formularzu
                let fieldPrefix;
                if (isCustomSection) {
                    const sectionNumber = section.replace('custom', '');
                    fieldPrefix = `custom_sections[${sectionNumber}][items][${rowCount}]`;
                } else {
                    fieldPrefix = `${section}[${rowCount}]`;
                }
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="${rowCount + 1}" readonly></td>
                    <td class="p-1"><input type="text" name="${fieldPrefix}[name]" value="${name}" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                    <td class="p-1"><input type="text" name="${fieldPrefix}[type]" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                    <td class="p-1"><input type="number" min="1" value="1" name="${fieldPrefix}[quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                    <td class="p-1"><input type="text" name="${fieldPrefix}[supplier]" value="${supplier}" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                    <td class="p-1"><input type="number" step="0.01" name="${fieldPrefix}[price]" value="${price}" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                    <td class="p-1"><input type="number" step="0.01" name="${fieldPrefix}[value]" value="${price}" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${section}" readonly></td>
                    <td class="p-1"><button type="button" onclick="removeRow(this, '${section}')" class="text-red-600 hover:text-red-800">✕</button></td>
                `;
                
                table.appendChild(row);
                rowCounters[section]++;
            });
            
            updateRowNumbers(section);
            calculateTotal(section);
            initPartSearch();
            closePartsCatalog();
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Auto-fill customer data if company is pre-selected (from CRM deal)
        document.addEventListener('DOMContentLoaded', function() {
            const companySelect = document.getElementById('company-select');
            if (companySelect && companySelect.value) {
                fillCustomerData(companySelect.value);
            }
        });
        
        // Funkcje obsługi danych klienta
        function fillCustomerData(companyId) {
            if (!companyId) return;
            
            const select = document.getElementById('company-select');
            const option = select.options[select.selectedIndex];
            
            document.getElementById('customer_name').value = option.dataset.name || '';
            document.getElementById('customer_nip').value = option.dataset.nip || '';
            document.getElementById('customer_address').value = option.dataset.address || '';
            document.getElementById('customer_city').value = option.dataset.city || '';
            document.getElementById('customer_postal_code').value = option.dataset.postal || '';
            document.getElementById('customer_phone').value = option.dataset.phone || '';
            document.getElementById('customer_email').value = option.dataset.email || '';
            
            // Update element 4 customer field
            const customerShortName = option.dataset.shortName || option.dataset.name?.substring(0, 5).toUpperCase() || 'KLIENT';
            console.log('Selected company ID:', companyId);
            console.log('Short name from dataset:', option.dataset.shortName);
            console.log('Using customer short name:', customerShortName);
            
            const element4Field = document.getElementById('element4_customer');
            if (element4Field) {
                element4Field.value = customerShortName;
                console.log('Updated element4_customer to:', customerShortName);
                updateFinalOfferNumber();
            } else {
                console.log('element4_customer field not found');
            }
        }
        
        function updateFinalOfferNumber() {
            const baseNumber = document.getElementById('offer_number_base');
            const element4Field = document.getElementById('element4_customer');
            const checkbox = document.getElementById('include_element4');
            const finalNumber = document.getElementById('offer_number');
            const preview = document.getElementById('full_offer_preview');
            
            if (!baseNumber) return;
            
            let final = baseNumber.value;
            
            if (element4Field && checkbox && checkbox.checked && element4Field.value) {
                const separator = element4Field.dataset.separator || '_';
                final = baseNumber.value + separator + element4Field.value;
            }
            
            if (finalNumber) {
                finalNumber.value = final;
            }
            
            if (preview) {
                preview.textContent = final;
            }
        }
        
        function clearCustomerData() {
            document.getElementById('company-select').value = '';
            document.getElementById('customer_name').value = '';
            document.getElementById('customer_nip').value = '';
            document.getElementById('customer_address').value = '';
            document.getElementById('customer_city').value = '';
            document.getElementById('customer_postal_code').value = '';
            document.getElementById('customer_phone').value = '';
            document.getElementById('customer_email').value = '';
            
            // Reset element 4 to default
            const element4Field = document.getElementById('element4_customer');
            if (element4Field) {
                element4Field.value = 'KLIENT';
                console.log('Reset element4_customer to: KLIENT');
                updateFinalOfferNumber();
            }
        }
        
        async function fetchFromGUS() {
            const nip = document.getElementById('customer_nip').value.replace(/[^0-9]/g, '');
            
            if (!nip || nip.length !== 10) {
                alert('Podaj prawidłowy 10-cyfrowy NIP');
                return;
            }
            
            try {
                const response = await fetch(`https://wl-api.mf.gov.pl/api/search/nip/${nip}?date=${new Date().toISOString().split('T')[0]}`);
                
                if (!response.ok) {
                    throw new Error('Nie znaleziono danych w GUS');
                }
                
                const data = await response.json();
                
                if (data.result && data.result.subject) {
                    const subject = data.result.subject;
                    document.getElementById('customer_name').value = subject.name || '';
                    document.getElementById('customer_nip').value = subject.nip || '';
                    
                    if (subject.workingAddress) {
                        const addr = subject.workingAddress.split(',');
                        if (addr.length >= 2) {
                            document.getElementById('customer_address').value = addr[0].trim();
                            const cityPostal = addr[1].trim().split(' ');
                            if (cityPostal.length >= 2) {
                                document.getElementById('customer_postal_code').value = cityPostal[0];
                                document.getElementById('customer_city').value = cityPostal.slice(1).join(' ');
                            }
                        }
                    }
                    
                    alert('Dane pobrane z GUS!');
                } else {
                    alert('Nie znaleziono firmy o podanym NIP');
                }
            } catch (error) {
                console.error('Błąd pobierania danych z GUS:', error);
                alert('Błąd podczas pobierania danych z GUS: ' + error.message);
            }
        }
        
        // Update deal info based on selection
        function updateDealInfo(dealId) {
            const dealInfo = document.getElementById('deal-info');
            const select = document.getElementById('crm-deal-select');
            
            if (!dealId) {
                dealInfo.classList.add('hidden');
                return;
            }
            
            const selectedOption = select.options[select.selectedIndex];
            const name = selectedOption.getAttribute('data-name');
            const company = selectedOption.getAttribute('data-company');
            const value = selectedOption.getAttribute('data-value');
            const currency = selectedOption.getAttribute('data-currency');
            
            document.getElementById('deal-name').textContent = name;
            document.getElementById('deal-company').textContent = company ? `• Firma: ${company}` : '';
            document.getElementById('deal-value').textContent = `• Wartość: ${value} ${currency}`;
            
            dealInfo.classList.remove('hidden');
        }
    </script>
</body>
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
</script>
</html>
