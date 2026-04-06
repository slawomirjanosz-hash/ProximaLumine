<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Zrób nową Ofertę</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        @include('parts.menu')
    </header>
    <main class="flex-1 p-6">
        <div class="w-full bg-white rounded shadow p-6 relative">
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
                <div class="flex flex-wrap items-start gap-3">
                    <div class="flex items-center gap-1.5">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Nr oferty:</label>
                        <div>
                            <input type="text" name="offer_number_base" id="offer_number_base" value="{{ $previewNumber }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-44" oninput="updateFinalOfferNumber()" required>
                            <div><small class="text-gray-500 text-xs">Możesz zmienić domyślny numer</small></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-1 min-w-[16rem]">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Tytuł oferty:</label>
                        <input type="text" name="offer_title" class="flex-1 min-w-0 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Data:</label>
                        <input type="date" name="offer_date" value="{{ date('Y-m-d') }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                @if($element4Enabled)
                <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <label class="font-medium text-gray-700 whitespace-nowrap">Klient (el. 4):</label>
                        <input type="text" id="element4_customer" value="{{ $element4Customer }}" data-separator="{{ $offerSettings->separator3 ?? '_' }}" class="px-2 py-1 border border-gray-300 rounded bg-gray-100 text-sm w-28" readonly>
                    </div>
                    <label class="flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" name="include_element4" id="include_element4" value="1" checked onchange="updateFinalOfferNumber()">
                        <span>Dołącz do nr</span>
                    </label>
                    <span class="text-gray-600 text-xs">Nr: <strong id="full_offer_preview">{{ $previewNumber . ($offerSettings->separator3 ?? '_') . $element4Customer }}</strong></span>
                    <small class="text-gray-500">Skrót z: Magazyn → Ustawienia → Dostawcy i klienci</small>
                    <input type="hidden" name="offer_number" id="offer_number" value="{{ $previewNumber . ($offerSettings->separator3 ?? '_') . $element4Customer }}">
                </div>
                @else
                <input type="hidden" name="offer_number" id="offer_number" value="{{ $previewNumber }}">
                @endif

                <!-- Dane klienta -->
                <div class="border border-blue-300 rounded p-3 bg-blue-50">
                    <div class="flex items-center gap-2 flex-wrap mb-3">
                        <label class="text-sm font-semibold text-blue-900 whitespace-nowrap">👤 Dane klienta — wybierz z bazy lub przypisz ręcznie:</label>
                        <select id="company-select" class="flex-1 min-w-0 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="fillCustomerData(this.value)">
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
                        <button type="button" onclick="clearCustomerData()" class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm whitespace-nowrap">Wyczyść</button>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Nazwa firmy *</label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->name : '' }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-48" required>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">NIP</label>
                            <input type="text" id="customer_nip" name="customer_nip" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->nip : '' }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-32">
                            <button type="button" onclick="fetchFromGUS()" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700 whitespace-nowrap">GUS</button>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Telefon</label>
                            <input type="text" id="customer_phone" name="customer_phone" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->phone : '' }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-32">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Email</label>
                            <input type="email" id="customer_email" name="customer_email" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->email : '' }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-44">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Adres</label>
                            <input type="text" id="customer_address" name="customer_address" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->address : '' }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-44">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Miasto</label>
                            <input type="text" id="customer_city" name="customer_city" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->city : '' }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-32">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Kod pocz.</label>
                            <input type="text" id="customer_postal_code" name="customer_postal_code" value="{{ isset($deal) && $deal && $deal->company ? $deal->company->postal_code : '' }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-24">
                        </div>
                    </div>
                </div>

                <!-- Sekcja Usługi -->
                <div id="section-services" class="border border-gray-300 rounded">
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('services')">
                            <span class="font-semibold text-lg section-name text-left" id="services-name-label" style="min-width:0;">Usługi</span>
                            <span class="flex-1"></span>
                            <span class="mr-3 whitespace-nowrap font-semibold text-lg text-right" style="min-width:120px;">
                                <span id="services-header-sum" class="text-gray-600">0,00 zł</span>
                                <span class="text-gray-400"> / </span>
                                <span id="services-header-profit" class="text-green-600">0,00 zł</span>
                            </span>
                            <svg id="services-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('services')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwę">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('services')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Usuń sekcję">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="services-name-input" name="services_name" value="Usługi">
                        <input type="hidden" id="services-enabled-input" name="services_enabled" value="1">
                    <div id="services-content" class="p-4 overflow-x-auto hidden">
                        <table class="w-full mb-4 text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left">Nazwa</th>
                                    <th class="p-1 text-left w-48">Opis</th>
                                    <th class="p-1 text-left w-16">Ilość</th>
                                    <th class="p-1 text-left">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena</th>
                                    <th class="p-1 text-center w-28">Cena kat.</th>
                                    <th class="p-1 text-left w-24">Wartość</th>
                                    <th class="p-1 w-24"></th>
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
                                    <td class="p-1"><label class="flex items-center gap-1 cursor-pointer select-none mb-1"><input type="checkbox" name="services[0][discounted]" value="1" class="w-4 h-4 accent-orange-500 discount-checkbox" onchange="toggleCatalogPrice(this)"><span class="text-xs font-bold text-orange-600 whitespace-nowrap">kat.</span></label><input type="number" step="0.01" name="services[0][catalog_price]" class="w-full px-1 py-0.5 border border-orange-400 rounded text-xs catalog-price-input bg-amber-50" placeholder="cena kat." style="display:none"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="services[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="services" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'services')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="Usuń"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'services', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
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
                <div id="section-works" class="border border-gray-300 rounded">
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('works')">
                            <span class="font-semibold text-lg section-name text-left" id="works-name-label" style="min-width:0;">Prace własne</span>
                            <span class="flex-1"></span>
                            <span class="mr-3 whitespace-nowrap font-semibold text-lg text-right" style="min-width:120px;">
                                <span id="works-header-sum" class="text-gray-600">0,00 zł</span>
                                <span class="text-gray-400"> / </span>
                                <span id="works-header-profit" class="text-green-600">0,00 zł</span>
                            </span>
                            <svg id="works-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('works')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwę">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('works')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Usuń sekcję">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="works-name-input" name="works_name" value="Prace własne">
                        <input type="hidden" id="works-enabled-input" name="works_enabled" value="1">
                    <div id="works-content" class="p-4 overflow-x-auto hidden">
                        <table class="w-full mb-4 text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left">Nazwa</th>
                                    <th class="p-1 text-left w-48">Opis</th>
                                    <th class="p-1 text-left w-16">Ilość</th>
                                    <th class="p-1 text-left">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena</th>
                                    <th class="p-1 text-center w-28">Cena kat.</th>
                                    <th class="p-1 text-left w-24">Wartość</th>
                                    <th class="p-1 w-24"></th>
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
                                    <td class="p-1"><label class="flex items-center gap-1 cursor-pointer select-none mb-1"><input type="checkbox" name="works[0][discounted]" value="1" class="w-4 h-4 accent-orange-500 discount-checkbox" onchange="toggleCatalogPrice(this)"><span class="text-xs font-bold text-orange-600 whitespace-nowrap">kat.</span></label><input type="number" step="0.01" name="works[0][catalog_price]" class="w-full px-1 py-0.5 border border-orange-400 rounded text-xs catalog-price-input bg-amber-50" placeholder="cena kat." style="display:none"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="works" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'works')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="Usuń"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'works', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
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
                <div id="section-materials" class="border border-gray-300 rounded">
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('materials')">
                            <span class="font-semibold text-lg section-name text-left" id="materials-name-label" style="min-width:0;">Materiały</span>
                            <span class="flex-1"></span>
                            <span class="mr-3 whitespace-nowrap font-semibold text-lg text-right" style="min-width:120px;">
                                <span id="materials-header-sum" class="text-gray-600">0,00 zł</span>
                                <span class="text-gray-400"> / </span>
                                <span id="materials-header-profit" class="text-green-600">0,00 zł</span>
                            </span>
                            <svg id="materials-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('materials')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwę">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('materials')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Usuń sekcję">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="materials-name-input" name="materials_name" value="Materiały">
                        <input type="hidden" id="materials-enabled-input" name="materials_enabled" value="1">
                    <div id="materials-content" class="p-4 overflow-x-auto hidden">
                        <table class="w-full mb-4 text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left">Nazwa</th>
                                    <th class="p-1 text-left w-48">Opis</th>
                                    <th class="p-1 text-left w-16">Ilość</th>
                                    <th class="p-1 text-left">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena</th>
                                    <th class="p-1 text-center w-28">Cena kat.</th>
                                    <th class="p-1 text-left w-24">Wartość</th>
                                    <th class="p-1 w-24"></th>
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
                                    <td class="p-1"><label class="flex items-center gap-1 cursor-pointer select-none mb-1"><input type="checkbox" name="materials[0][discounted]" value="1" class="w-4 h-4 accent-orange-500 discount-checkbox" onchange="toggleCatalogPrice(this)"><span class="text-xs font-bold text-orange-600 whitespace-nowrap">kat.</span></label><input type="number" step="0.01" name="materials[0][catalog_price]" class="w-full px-1 py-0.5 border border-orange-400 rounded text-xs catalog-price-input bg-amber-50" placeholder="cena kat." style="display:none"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="materials" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'materials')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="Usuń"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'materials', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
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

                <!-- Suma końcowa + Zysk -->
                <div class="bg-gray-50 p-4 rounded border border-gray-300 space-y-3">
                    <div class="flex items-center justify-end gap-3">
                        <span class="text-lg font-semibold text-gray-600">Suma netto:</span>
                        <span id="grand-total" class="text-xl font-bold text-blue-600">0,00 zł</span>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-3 border-t pt-3">
                        <span class="text-sm font-medium text-gray-700">Zysk:</span>
                        <div class="flex items-center gap-1">
                            <input type="number" id="profit-percent" name="profit_percent" min="0" max="10000" step="0.01" value="0" class="w-20 px-2 py-1 border rounded text-sm text-right focus:ring-2 focus:ring-green-400" oninput="updateProfitFromPercent()">
                            <span class="text-sm text-gray-600">%</span>
                        </div>
                        <span class="text-gray-400 text-sm">lub</span>
                        <div class="flex items-center gap-1">
                            <input type="number" id="profit-amount-input" name="profit_amount" min="0" step="0.01" value="0" class="w-36 px-2 py-1 border rounded text-sm text-right focus:ring-2 focus:ring-green-400" oninput="updateProfitFromAmount()">
                            <span class="text-sm text-gray-600">zł</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t pt-3">
                        <span class="text-xl font-semibold">Razem z zyskiem:</span>
                        <span id="total-with-profit" class="text-2xl font-bold text-green-700">0,00 zł</span>
                    </div>
                </div>

                <!-- Zysk ukryty w cenie -->
                <div id="profit-row-info" class="p-3 bg-amber-50 border border-amber-300 rounded text-sm text-amber-700 font-semibold text-right" style="display:none">
                    ⚠️ Uwaga: niektóre pozycje mają wbudowany zysk (cena katalogowa &gt; cena po rabacie)
                </div>

                <!-- Harmonogram i Warunki Płatności -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Harmonogram -->
                    <div class="border border-gray-300 rounded bg-white">
                        <div class="p-3 bg-gray-50 border-b flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="schedule-enabled" name="schedule_enabled" value="1" class="w-4 h-4 accent-blue-600" onchange="toggleSchedule(this.checked)">
                                <span class="font-semibold text-gray-800">Harmonogram</span>
                            </label>
                        </div>
                        <div id="schedule-section" class="hidden p-3">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-1 border text-center w-8">Lp.</th>
                                        <th class="p-1 border text-left">Etap / Milestone</th>
                                        <th class="p-1 border text-left w-28">Termin</th>
                                        <th class="p-1 border text-left">Opis</th>
                                        <th class="p-1 border w-6"></th>
                                    </tr>
                                </thead>
                                <tbody id="schedule-table"></tbody>
                            </table>
                            <button type="button" onclick="addScheduleRow()" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">+ Dodaj wiersz</button>
                        </div>
                    </div>

                    <!-- Warunki Płatności -->
                    <div class="border border-gray-300 rounded bg-white">
                        <div class="p-3 bg-gray-50 border-b">
                            <span class="font-semibold text-gray-800">Warunki płatności</span>
                        </div>
                        <div class="p-3">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-1 border text-center w-8">Lp.</th>
                                        <th class="p-1 border text-left">Opis</th>
                                        <th class="p-1 border text-right w-16">%</th>
                                        <th class="p-1 border text-left w-28">Termin / Uwagi</th>
                                        <th class="p-1 border w-6"></th>
                                    </tr>
                                </thead>
                                <tbody id="payment-table"></tbody>
                            </table>
                            <button type="button" onclick="addPaymentRow()" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">+ Dodaj wiersz</button>
                        </div>
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
        let _grandTotalRaw = 0;
        let _sectionTotals = {};

        function toggleSection(section) {
            const content = document.getElementById(section + '-content');
            const icon = document.getElementById(section + '-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
                content.querySelectorAll('textarea').forEach(autoResizeTextarea);
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        function formatPrice(value) {
            return value.toLocaleString('pl-PL', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' zł';
        }

        function autoResizeTextarea(ta) {
            ta.style.overflowY = 'hidden';
            ta.style.height = 'auto';
            ta.style.height = ta.scrollHeight + 'px';
        }
        document.addEventListener('input', function(e) {
            if (e.target.tagName === 'TEXTAREA') autoResizeTextarea(e.target);
        });

        function moveRow(button, direction, section) {
            const row = button.closest('tr');
            const tbody = row.closest('tbody');
            if (direction === 'up') {
                const prev = row.previousElementSibling;
                if (prev) tbody.insertBefore(row, prev);
            } else {
                const next = row.nextElementSibling;
                if (next) tbody.insertBefore(next, row);
            }
            reindexSection(section);
            calculateTotal(section);
        }

        function reindexSection(section) {
            const tbody = document.getElementById(section + '-table');
            if (!tbody) return;
            const rows = tbody.querySelectorAll('tr');
            rows.forEach((row, newIndex) => {
                const numInput = row.querySelector('input[type="number"][readonly]');
                if (numInput) numInput.value = newIndex + 1;
                row.querySelectorAll('[name]').forEach(el => {
                    if (section.startsWith('custom')) {
                        const sNum = section.replace('custom', '');
                        el.name = el.name.replace(
                            /custom_sections\[\d+\]\[items\]\[\d+\]/,
                            'custom_sections[' + sNum + '][items][' + newIndex + ']'
                        );
                    } else {
                        el.name = el.name.replace(
                            new RegExp('^' + section + '\\[\\d+\\]'),
                            section + '[' + newIndex + ']'
                        );
                    }
                });
            });
            rowCounters[section] = rows.length;
        }

        function injectMoveButtons(row, section) {
            const lastTd = row.querySelector('td:last-child');
            if (!lastTd || lastTd.dataset.moveInit) return;
            lastTd.dataset.moveInit = '1';
            let actionDiv = lastTd.querySelector('div.flex');
            if (!actionDiv) {
                lastTd.innerHTML = '<div class="flex gap-1 items-center">' + lastTd.innerHTML + '</div>';
                actionDiv = lastTd.querySelector('div.flex');
            }
            const frag = document.createRange().createContextualFragment(
                '<button type="button" onclick="moveRow(this,\'up\',\'' + section + '\')" class="text-gray-400 hover:text-gray-600 text-xs px-0.5" title="Wyżej">↑</button>' +
                '<button type="button" onclick="moveRow(this,\'down\',\'' + section + '\')" class="text-gray-400 hover:text-gray-600 text-xs px-0.5" title="Niżej">↓</button>'
            );
            actionDiv.insertBefore(frag, actionDiv.firstChild);
        }

        function initMoveButtons() {
            ['services', 'works', 'materials'].forEach(section => {
                const tbody = document.getElementById(section + '-table');
                if (!tbody) return;
                tbody.querySelectorAll('tr').forEach(row => injectMoveButtons(row, section));
            });
            customSections.forEach(num => {
                const sId = 'custom' + num;
                const tbody = document.getElementById(sId + '-table');
                if (!tbody) return;
                tbody.querySelectorAll('tr').forEach(row => injectMoveButtons(row, sId));
            });
        }

        function initCheckboxColumn() {
            ['services', 'works', 'materials'].forEach(section => {
                const tbody = document.getElementById(section + '-table');
                if (!tbody) return;
                const table = tbody.closest('table');
                const thead = table ? table.querySelector('thead tr') : null;
                if (thead && !thead.querySelector('th.cb-th')) {
                    const th = document.createElement('th');
                    th.className = 'p-1 w-5 cb-th';
                    thead.insertBefore(th, thead.firstChild);
                }
                tbody.querySelectorAll('tr').forEach(row => {
                    if (!row.querySelector('.row-checkbox')) {
                        const td = document.createElement('td');
                        td.className = 'p-1 text-center';
                        td.innerHTML = '<input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer">';
                        row.insertBefore(td, row.firstChild);
                    }
                });
            });
            customSections.forEach(num => {
                const sId = 'custom' + num;
                const tbody = document.getElementById(sId + '-table');
                if (!tbody) return;
                const table = tbody.closest('table');
                const thead = table ? table.querySelector('thead tr') : null;
                if (thead && !thead.querySelector('th.cb-th')) {
                    const th = document.createElement('th');
                    th.className = 'p-1 w-5 cb-th';
                    thead.insertBefore(th, thead.firstChild);
                }
                tbody.querySelectorAll('tr').forEach(row => {
                    if (!row.querySelector('.row-checkbox')) {
                        const td = document.createElement('td');
                        td.className = 'p-1 text-center';
                        td.innerHTML = '<input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer">';
                        row.insertBefore(td, row.firstChild);
                    }
                });
            });
        }

        function formatAllValueInputs() {
            document.querySelectorAll('.value-input').forEach(input => {
                if (input.dataset.formattedInit) return;
                input.dataset.formattedInit = '1';
                const raw = parseFloat(input.value) || 0;
                input.dataset.raw = raw.toFixed(2);
                input.type = 'text';
                input.value = formatPrice(raw);
            });
        }

        document.addEventListener('focusin', function(e) {
            if (e.target.classList.contains('row-checkbox')) return;
            const td = e.target.closest('td');
            if (!td) return;
            const row = td.closest('tr');
            if (!row) return;
            const tbody = row.closest('tbody');
            if (!tbody || !tbody.id || !tbody.id.endsWith('-table')) return;
            const cb = row.querySelector('.row-checkbox');
            if (cb) cb.checked = true;
        });

        function calculateRowValue(input) {
            const row = input.closest('tr');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');
            const valueInput = row.querySelector('.value-input');
            
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const value = quantity * price;

            valueInput.dataset.raw = value.toFixed(2);
            valueInput.dataset.formattedInit = '1';
            valueInput.type = 'text';
            valueInput.value = formatPrice(value);
            
            const section = input.dataset.section;
            calculateTotal(section);
        }

        function toggleCatalogPrice(checkbox) {
            const td = checkbox.closest('td');
            const catalogInput = td.querySelector('.catalog-price-input');
            if (checkbox.checked) {
                catalogInput.style.display = '';
                if (!catalogInput.value) {
                    const row = checkbox.closest('tr');
                    const priceInput = row ? row.querySelector('.price-input') : null;
                    if (priceInput) catalogInput.value = priceInput.value;
                }
            } else {
                catalogInput.style.display = 'none';
                catalogInput.value = '';
            }
            updateProfitDisplay();
        }

        function anyRowHasProfit() {
            let found = false;
            document.querySelectorAll('.discount-checkbox:checked').forEach(function(cb) {
                const td = cb.closest('td');
                if (td) {
                    const inp = td.querySelector('.catalog-price-input');
                    if (inp && parseFloat(inp.value) > 0) found = true;
                }
            });
            return found;
        }

        function addRow(section) {
            const table = document.getElementById(section + '-table');
            const rowCount = rowCounters[section];
            
            const row = document.createElement('tr');
            
            if (section === 'materials') {
                row.innerHTML = `
                    <td class="p-1 text-center"><input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer"></td>
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
                    <td class="p-1"><label class="flex items-center gap-1 cursor-pointer select-none mb-1"><input type="checkbox" name="${section}[${rowCount}][discounted]" value="1" class="w-4 h-4 accent-orange-500 discount-checkbox" onchange="toggleCatalogPrice(this)"><span class="text-xs font-bold text-orange-600 whitespace-nowrap">kat.</span></label><input type="number" step="0.01" name="${section}[${rowCount}][catalog_price]" class="w-full px-1 py-0.5 border border-orange-400 rounded text-xs catalog-price-input bg-amber-50" placeholder="cena kat." style="display:none"></td>
                    <td class="p-1"><input type="text" name="${section}[${rowCount}][value]" value="0" data-raw="0" data-formatted-init="1" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${section}" readonly></td>
                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="moveRow(this,'up','${section}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="Wyżej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button><button type="button" onclick="moveRow(this,'down','${section}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="Niżej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button><button type="button" onclick="removeRow(this,'${section}')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="Usuń"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, '${section}', ${rowCount})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                `;
            } else {
                row.innerHTML = `
                    <td class="p-1 text-center"><input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer"></td>
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
                    <td class="p-1"><label class="flex items-center gap-1 cursor-pointer select-none mb-1"><input type="checkbox" name="${section}[${rowCount}][discounted]" value="1" class="w-4 h-4 accent-orange-500 discount-checkbox" onchange="toggleCatalogPrice(this)"><span class="text-xs font-bold text-orange-600 whitespace-nowrap">kat.</span></label><input type="number" step="0.01" name="${section}[${rowCount}][catalog_price]" class="w-full px-1 py-0.5 border border-orange-400 rounded text-xs catalog-price-input bg-amber-50" placeholder="cena kat." style="display:none"></td>
                    <td class="p-1"><input type="text" name="${section}[${rowCount}][value]" value="0" data-raw="0" data-formatted-init="1" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${section}" readonly></td>
                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="moveRow(this,'up','${section}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="Wyżej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button><button type="button" onclick="moveRow(this,'down','${section}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="Niżej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button><button type="button" onclick="removeRow(this,'${section}')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="Usuń"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, '${section}', ${rowCount})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                `;
            }
            
            // Insert after last checked row, or append at end
            const allRows = table.querySelectorAll('tr');
            let lastChecked = null;
            allRows.forEach(r => { if (r.querySelector('.row-checkbox')?.checked) lastChecked = r; });
            if (lastChecked) lastChecked.after(row); else table.appendChild(row);
            rowCounters[section]++;
            reindexSection(section);
            
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
                const numberInput = row.querySelector('input[type="number"][readonly]');
                if (numberInput) {
                    numberInput.value = index + 1;
                }
            });
        }

        function calculateTotal(section) {
            const inputs = document.querySelectorAll(`#${section}-table .value-input`);
            let total = 0;
            
            inputs.forEach(input => {
                const value = parseFloat(input.dataset.raw || input.value) || 0;
                total += value;
            });
            
            _sectionTotals[section] = total;
            document.getElementById(section + '-total').textContent = formatPrice(total);
            const headerSum = document.getElementById(section + '-header-sum');
            if (headerSum) headerSum.textContent = formatPrice(total);
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            const servicesInputs = document.querySelectorAll('#services-table .value-input');
            const worksInputs = document.querySelectorAll('#works-table .value-input');
            const materialsInputs = document.querySelectorAll('#materials-table .value-input');
            
            let grandTotal = 0;
            
            servicesInputs.forEach(input => {
                grandTotal += parseFloat(input.dataset.raw || input.value) || 0;
            });
            worksInputs.forEach(input => {
                grandTotal += parseFloat(input.dataset.raw || input.value) || 0;
            });
            materialsInputs.forEach(input => {
                grandTotal += parseFloat(input.dataset.raw || input.value) || 0;
            });
            
            // Dodaj sumy z niestandardowych sekcji
            customSections.forEach(sectionId => {
                const inputs = document.querySelectorAll(`#custom-${sectionId}-table .value-input`);
                inputs.forEach(input => {
                    grandTotal += parseFloat(input.dataset.raw || input.value) || 0;
                });
            });
            
            _grandTotalRaw = grandTotal;
            document.getElementById('grand-total').textContent = formatPrice(grandTotal);
            updateProfitDisplay();
        }

        function updateProfitFromPercent() {
            const pct = parseFloat(document.getElementById('profit-percent').value) || 0;
            const amount = _grandTotalRaw * pct / 100;
            document.getElementById('profit-amount-input').value = amount.toFixed(2);
            updateProfitDisplay();
        }

        function updateProfitFromAmount() {
            const amount = parseFloat(document.getElementById('profit-amount-input').value) || 0;
            const pct = _grandTotalRaw > 0 ? (amount / _grandTotalRaw * 100) : 0;
            document.getElementById('profit-percent').value = pct.toFixed(2);
            updateProfitDisplay();
        }

        function updateProfitDisplay() {
            const amount = parseFloat(document.getElementById('profit-amount-input').value) || 0;
            document.getElementById('total-with-profit').textContent = formatPrice(_grandTotalRaw + amount);
            const pct = parseFloat(document.getElementById('profit-percent').value) || 0;
            const multiplier = 1 + pct / 100;
            Object.keys(_sectionTotals).forEach(section => {
                const el = document.getElementById(section + '-header-profit');
                if (el) el.textContent = formatPrice(_sectionTotals[section] * multiplier);
            });
            const profitInfo = document.getElementById('profit-row-info');
            if (profitInfo) profitInfo.style.display = anyRowHasProfit() ? '' : 'none';
        }

        // ===========================================
        // HARMONOGRAM I WARUNKI PŁATNOŚCI
        // ===========================================
        let scheduleCount = 0;
        let paymentCount = 0;

        function toggleSchedule(checked) {
            document.getElementById('schedule-section').classList.toggle('hidden', !checked);
        }

        function addScheduleRow(milestone, date, description) {
            const tbody = document.getElementById('schedule-table');
            const idx = scheduleCount++;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="p-1 border text-center text-gray-500 text-xs">${idx + 1}</td>
                <td class="p-1 border"><input type="text" name="schedule[${idx}][milestone]" value="${escapeHtml(milestone||'')}" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                <td class="p-1 border"><input type="date" name="schedule[${idx}][date]" value="${date||''}" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                <td class="p-1 border"><input type="text" name="schedule[${idx}][description]" value="${escapeHtml(description||'')}" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                <td class="p-1 border text-center"><button type="button" onclick="this.closest('tr').remove(); reindexSchedule()" class="text-red-600 hover:text-red-800 text-xs">✕</button></td>
            `;
            tbody.appendChild(tr);
        }

        function reindexSchedule() {
            document.querySelectorAll('#schedule-table tr').forEach((row, i) => {
                row.querySelector('td:first-child').textContent = i + 1;
                row.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(/schedule\[\d+\]/, `schedule[${i}]`);
                });
            });
        }

        function addPaymentRow(description, percent, deadline) {
            const tbody = document.getElementById('payment-table');
            const idx = paymentCount++;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="p-1 border text-center text-gray-500 text-xs">${idx + 1}</td>
                <td class="p-1 border"><input type="text" name="payment_terms[${idx}][description]" value="${escapeHtml(description||'')}" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                <td class="p-1 border"><input type="number" step="0.01" min="0" max="100" name="payment_terms[${idx}][percent]" value="${percent||''}" class="w-full px-1 py-0.5 border rounded text-xs text-right"></td>
                <td class="p-1 border"><input type="text" name="payment_terms[${idx}][deadline]" value="${escapeHtml(deadline||'')}" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                <td class="p-1 border text-center"><button type="button" onclick="this.closest('tr').remove(); reindexPayment()" class="text-red-600 hover:text-red-800 text-xs">✕</button></td>
            `;
            tbody.appendChild(tr);
        }

        function reindexPayment() {
            document.querySelectorAll('#payment-table tr').forEach((row, i) => {
                row.querySelector('td:first-child').textContent = i + 1;
                row.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(/payment_terms\[\d+\]/, `payment_terms[${i}]`);
                });
            });
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
                    <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('${sectionId}')">
                        <span class="font-semibold text-lg section-name text-left" id="${sectionId}-name-label" style="min-width:0;">${escapeHtml(sectionName.trim())}</span>
                        <span class="flex-1"></span>
                        <span class="mr-3 whitespace-nowrap font-semibold text-lg text-right" style="min-width:120px;">
                            <span id="${sectionId}-header-sum" class="text-gray-600">0,00 z&#322;</span>
                            <span class="text-gray-400"> / </span>
                            <span id="${sectionId}-header-profit" class="text-green-600">0,00 z&#322;</span>
                        </span>
                        <svg id="${sectionId}-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <button type="button" onclick="editSectionName('${sectionId}', ${customSectionCounter})" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwę">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                    </button>
                    <button type="button" onclick="removeCustomSection('${sectionId}')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Usuń sekcję">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
                <div id="${sectionId}-content" class="p-4 overflow-x-auto hidden">
                    <input type="hidden" id="${sectionId}-name-input" name="custom_sections[${customSectionCounter}][name]" value="${escapeHtml(sectionName.trim())}">
                    <table class="w-full mb-4 text-xs">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-1 w-5 cb-th"></th>
                                <th class="p-1 text-left w-10">Nr</th>
                                <th class="p-1 text-left">Nazwa</th>
                                <th class="p-1 text-left w-48">Opis</th>
                                <th class="p-1 text-left w-16">Ilość</th>
                                <th class="p-1 text-left">Dostawca</th>
                                <th class="p-1 text-left w-24">Cena (zł)</th>
                                <th class="p-1 text-center w-28">Cena kat.</th>
                                <th class="p-1 text-left w-24">Wartość (zł)</th>
                                <th class="p-1 w-24"></th>
                            </tr>
                        </thead>
                        <tbody id="${sectionId}-table">
                            <tr>
                                <td class="p-1 text-center"><input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer"></td>
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
                                <td class="p-1"><label class="flex items-center gap-1 cursor-pointer select-none mb-1"><input type="checkbox" name="custom_sections[${customSectionCounter}][items][0][discounted]" value="1" class="w-4 h-4 accent-orange-500 discount-checkbox" onchange="toggleCatalogPrice(this)"><span class="text-xs font-bold text-orange-600 whitespace-nowrap">kat.</span></label><input type="number" step="0.01" name="custom_sections[${customSectionCounter}][items][0][catalog_price]" class="w-full px-1 py-0.5 border border-orange-400 rounded text-xs catalog-price-input bg-amber-50" placeholder="cena kat." style="display:none"></td>
                                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${customSectionCounter}][items][0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${sectionId}" readonly></td>
                                <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="addProductToCatalog(this, 'custom_sections', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
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
            const enabledInput = document.getElementById(`${sectionId}-enabled-input`);
            if (enabledInput) {
                enabledInput.value = '0';
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
                <td class="p-1"><label class="flex items-center gap-1 cursor-pointer select-none mb-1"><input type="checkbox" name="custom_sections[${sectionNumber}][items][${rowCount}][discounted]" value="1" class="w-4 h-4 accent-orange-500 discount-checkbox" onchange="toggleCatalogPrice(this)"><span class="text-xs font-bold text-orange-600 whitespace-nowrap">kat.</span></label><input type="number" step="0.01" name="custom_sections[${sectionNumber}][items][${rowCount}][catalog_price]" class="w-full px-1 py-0.5 border border-orange-400 rounded text-xs catalog-price-input bg-amber-50" placeholder="cena kat." style="display:none"></td>
                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${sectionNumber}][items][${rowCount}][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${sectionId}" readonly></td>
                <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="moveRow(this,'up','${sectionId}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="Wyżej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button><button type="button" onclick="moveRow(this,'down','${sectionId}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="Niżej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button><button type="button" onclick="removeRow(this, '${sectionId}')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="Usuń"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'custom_sections', ${rowCount})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
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
            initCheckboxColumn();
            formatAllValueInputs();
            initPartSearch();
            initMoveButtons();
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
                
                let data = await response.json();
                console.log('=== DEBUG: Otrzymane dane z API ===');
                console.log('Pełna odpowiedź:', data);
                console.log('Typ danych:', typeof data);
                console.log('Czy tablica:', Array.isArray(data));
                if (typeof data === 'object' && data !== null) {
                    console.log('Klucze obiektu:', Object.keys(data));
                }
                console.log('Długość (length):', data.length);
                console.log('===================================');
                
                // Sprawdź czy API zwróciło błąd (obiekt z kluczem 'error')
                if (data && typeof data === 'object' && data.error) {
                    console.error('API zwróciło błąd:', data);
                    throw new Error(data.message || data.error || 'Nieznany błąd API');
                }
                
                // WYMUSZENIE TABLICY: Konwertuj obiekt na tablicę jeśli nie jest już tablicą
                if (!Array.isArray(data)) {
                    if (typeof data === 'object' && data !== null) {
                        console.warn('⚠️ API zwróciło obiekt zamiast tablicy - konwertuję automatycznie');
                        console.log('Przed konwersją:', data);
                        // Użyj Object.values() aby wyciągnąć wartości
                        data = Object.values(data);
                        console.log('Po konwersji (is array):', Array.isArray(data), 'length:', data.length);
                    } else {
                        console.error('❌ API zwróciło nieprawidłowy typ:', typeof data);
                        throw new Error('API zwróciło nieprawidłowy format danych (oczekiwano tablicy lub obiektu, otrzymano: ' + typeof data + ')');
                    }
                }
                
                // Dodatkowa walidacja
                if (!Array.isArray(data)) {
                    console.error('❌ Konwersja nie powiodła się, data nadal nie jest tablicą');
                    throw new Error('Nie udało się przekonwertować danych na tablicę');
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
