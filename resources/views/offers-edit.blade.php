<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edytuj OfertÄ™</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        @include('parts.menu')
    </header>
    <main class="w-full flex-1 p-6">
        <div class="w-full bg-white rounded shadow p-6 relative">
                        @if(auth()->user() && (auth()->user()->is_admin || strtolower(auth()->user()->email) === 'admin@admin.com'))
                        <form method="POST" action="{{ route('offers.destroy', $offer) }}" onsubmit="return confirm('Czy na pewno chcesz usunÄ…Ä‡ tÄ™ ofertÄ™?');" class="absolute top-4 right-4">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 border border-red-300 shadow rounded-full hover:bg-red-200 hover:text-red-800 transition">
                                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' /></svg>
                                UsuĹ„ ofertÄ™
                            </button>
                        </form>
                        @endif
            <a href="javascript:void(0)" onclick="handleBack()" class="absolute top-4 left-4 flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 shadow rounded-full text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition z-10">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' /></svg>
                PowrĂłt
            </a>
            
            <h1 class="text-3xl font-bold mb-6 text-center mt-12">Edycja oferty</h1>
            
            <form id="offer-form" action="{{ route('offers.update', $offer) }}" method="POST" class="space-y-6" onkeydown="return event.key != 'Enter';" onsubmit="prepareSaveAndStay()">
                @csrf
                @method('PUT')
                
                <!-- Przypisanie do szansy CRM -->
                <div class="mb-4 p-3 bg-green-50 border border-green-300 rounded">
                    <div class="flex items-center gap-2 flex-wrap">
                        <label class="text-sm font-semibold text-green-900 whitespace-nowrap">đźŽŻ Przypisz do szansy:</label>
                        <select id="crm-deal-select" name="crm_deal_id" class="flex-1 min-w-0 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500" onchange="updateDealInfo(this.value)">
                            <option value="">-- Brak przypisania --</option>
                            @foreach($deals as $d)
                                <option value="{{ $d->id }}"
                                    data-name="{{ $d->name }}"
                                    data-company="{{ $d->company ? $d->company->name : '' }}"
                                    data-value="{{ number_format($d->value, 2, ',', ' ') }}"
                                    data-currency="{{ $d->currency }}"
                                    @if($offer->crm_deal_id == $d->id) selected @endif>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($offer->crm_deal_id)
                        <button type="button" onclick="detachDeal()" class="px-2 py-1 bg-red-100 text-red-700 border border-red-300 rounded hover:bg-red-200 transition text-sm font-semibold whitespace-nowrap" title="Odepnij szansÄ™ od tej oferty">
                            âś‚ď¸Ź Odepnij szansÄ™
                        </button>
                        @endif
                    </div>
                    <div id="deal-info" class="mt-1 flex items-baseline gap-2 text-sm @if(!$offer->crmDeal) hidden @endif">
                        <span class="font-medium text-blue-800 whitespace-nowrap">SzczegĂłĹ‚y szansy:</span>
                        <p class="text-blue-700">
                            <strong id="deal-name">{{ $offer->crmDeal ? $offer->crmDeal->name : '' }}</strong>
                            <span id="deal-company" class="ml-2">{{ $offer->crmDeal && $offer->crmDeal->company ? 'â€˘ Firma: ' . $offer->crmDeal->company->name : '' }}</span>
                            <span id="deal-value" class="ml-2">{{ $offer->crmDeal ? 'â€˘ WartoĹ›Ä‡: ' . number_format($offer->crmDeal->value, 2, ',', ' ') . ' ' . $offer->crmDeal->currency : '' }}</span>
                        </p>
                    </div>
                </div>
                
                <!-- Podstawowe informacje -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Nr oferty:</label>
                        <input type="text" name="offer_number" value="{{ $offer->offer_number }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-36" required>
                    </div>
                    <div class="flex items-center gap-1.5 flex-1 min-w-[16rem]">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">TytuĹ‚ oferty:</label>
                        <input type="text" name="offer_title" value="{{ $offer->offer_title }}" class="flex-1 min-w-0 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Data:</label>
                        <input type="date" name="offer_date" value="{{ $offer->offer_date->format('Y-m-d') }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>

                <!-- Dane klienta -->
                <div class="border border-blue-300 rounded p-3 bg-blue-50">
                    <div class="flex items-center gap-2 flex-wrap mb-3">
                        <label class="text-sm font-semibold text-blue-900 whitespace-nowrap">đź‘¤ Dane klienta â€” wybierz z bazy lub przypisz rÄ™cznie:</label>
                        <select id="company-select" class="flex-1 min-w-0 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="fillCustomerData(this.value)">
                            <option value="">-- Wybierz firmÄ™ z CRM --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    data-name="{{ $company->name }}"
                                    data-nip="{{ $company->nip ?? '' }}"
                                    data-address="{{ $company->address ?? '' }}"
                                    data-city="{{ $company->city ?? '' }}"
                                    data-postal="{{ $company->postal_code ?? '' }}"
                                    data-phone="{{ $company->phone ?? '' }}"
                                    data-email="{{ $company->email ?? '' }}">
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" onclick="clearCustomerData()" class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm whitespace-nowrap">WyczyĹ›Ä‡</button>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Nazwa firmy *</label>
                            <input type="text" id="customer_name" name="customer_name" value="{{ $offer->customer_name }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-48" required>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">NIP</label>
                            <input type="text" id="customer_nip" name="customer_nip" value="{{ $offer->customer_nip }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-32">
                            <button type="button" onclick="fetchFromGUS()" class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs whitespace-nowrap">GUS</button>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Telefon</label>
                            <input type="text" id="customer_phone" name="customer_phone" value="{{ $offer->customer_phone }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-32">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Email</label>
                            <input type="email" id="customer_email" name="customer_email" value="{{ $offer->customer_email }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-44">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Adres</label>
                            <input type="text" id="customer_address" name="customer_address" value="{{ $offer->customer_address }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-44">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Miasto</label>
                            <input type="text" id="customer_city" name="customer_city" value="{{ $offer->customer_city }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-32">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-medium text-gray-700 whitespace-nowrap">Kod pocz.</label>
                            <input type="text" id="customer_postal_code" name="customer_postal_code" value="{{ $offer->customer_postal_code }}" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-24">
                        </div>
                    </div>
                </div>

                <!-- Ustawienia dokumentĂłw (PDF / Word) -->
                <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="show-unit-prices-checkbox"
                            onchange="document.getElementById('show-unit-prices-input').value = this.checked ? '1' : '0'"
                            @if($showUnitPrices) checked @endif
                            class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">PokaĹĽ ceny jednostkowe w PDF / Word</span>
                    </label>
                    <input type="hidden" id="show-unit-prices-input" name="show_unit_prices" value="{{ $showUnitPrices ? '1' : '0' }}">
                    <span class="text-xs text-gray-400">(gdy odznaczone â€” w dokumencie widoczna jest tylko cena koĹ„cowa)</span>
                </div>

                <!-- Sekcja UsĹ‚ugi -->
                <div id="section-services" class="border border-gray-300 rounded" @if(!$servicesEnabled) style="display:none;" @endif>
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('services')">
                            <span class="font-semibold text-lg section-name text-left" id="services-name-label" style="min-width:0;">{{ $servicesName }}</span>
                            <span class="flex-1"></span>
                            <span class="mr-3 whitespace-nowrap font-semibold text-lg text-right" style="min-width:120px;">
                                <span id="services-header-sum" class="text-gray-600">0,00 zĹ‚</span>
                                <span class="text-gray-400"> / </span>
                                <span id="services-header-profit" class="text-green-600">0,00 zĹ‚</span>
                            </span>
                            <svg id="services-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('services')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwÄ™">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('services')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="UsuĹ„ sekcjÄ™">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="services-name-input" name="services_name" value="{{ $servicesName }}">
                        <input type="hidden" id="services-enabled-input" name="services_enabled" value="{{ $servicesEnabled ? '1' : '0' }}">
                    <div id="services-content" class="p-4 overflow-x-auto hidden">
                        <table class="w-full mb-4 text-xs table-fixed">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left w-[28%]">Nazwa</th>
                                    <th class="p-1 text-left w-[28%]">Opis</th>
                                    <th class="p-1 text-left w-16">IloĹ›Ä‡</th>
                                    <th class="p-1 text-left w-[14%]">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena (zĹ‚)</th>
                                    <th class="p-1 text-center w-24">Cena kat.</th>
                                    <th class="p-1 text-left w-24">WartoĹ›Ä‡ (zĹ‚)</th>
                                    <th class="p-1 w-24"></th>
                                </tr>
                            </thead>
                            <tbody id="services-table">
                                @forelse($offer->services ?? [] as $index => $service)
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="{{ $index + 1 }}" readonly></td>
                                    <td class="p-1"><textarea name="services[{{ $index }}][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">{{ $service['name'] ?? '' }}</textarea></td>
                                    <td class="p-1"><textarea name="services[{{ $index }}][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">{{ $service['type'] ?? '' }}</textarea></td>
                                    <td class="p-1"><input type="number" min="1" value="{{ $service['quantity'] ?? 1 }}" name="services[{{ $index }}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="services" onchange="calculateRowValue(this)"></td>
                                                                <td class="p-1">
                                                                    <select name="services[{{ $index }}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                                                        <option value="">-- brak --</option>
                                                                        @foreach($suppliers as $supplier)
                                                                            <option value="{{ $supplier->name }}" @if(($service['supplier'] ?? '') == $supplier->name) selected @endif>{{ $supplier->short_name ?: $supplier->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                    <td class="p-1"><input type="number" step="0.01" name="services[{{ $index }}][price]" value="{{ $service['price'] ?? '' }}" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="services" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="services[{{ $index }}][catalog_price]" value="{{ $service['catalog_price'] ?? '' }}" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="services[{{ $index }}][value]" value="{{ ($service['quantity'] ?? 1) * ($service['price'] ?? 0) }}" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="services" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'services')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'services', {{ $index }})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                    <td class="p-1"><textarea name="services[0][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                    <td class="p-1"><textarea name="services[0][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
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
                                    <td class="p-1"><input type="number" step="0.01" name="services[0][catalog_price]" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="services[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="services" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'services')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'services', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="flex gap-2">
                            <button type="button" onclick="addRow('services')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                            <button type="button" onclick="openPartsCatalog('services')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">đź“‚ Wybierz z katalogu</button>
                        </div>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="services-total" class="font-bold text-lg">0.00 zĹ‚</span>
                        </div>
                    </div>
                </div>

                <!-- Sekcja Prace wĹ‚asne -->
                <div id="section-works" class="border border-gray-300 rounded" @if(!$worksEnabled) style="display:none;" @endif>
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('works')">
                            <span class="font-semibold text-lg section-name text-left" id="works-name-label" style="min-width:0;">{{ $worksName }}</span>
                            <span class="flex-1"></span>
                            <span class="mr-3 whitespace-nowrap font-semibold text-lg text-right" style="min-width:120px;">
                                <span id="works-header-sum" class="text-gray-600">0,00 zĹ‚</span>
                                <span class="text-gray-400"> / </span>
                                <span id="works-header-profit" class="text-green-600">0,00 zĹ‚</span>
                            </span>
                            <svg id="works-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('works')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwÄ™">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('works')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="UsuĹ„ sekcjÄ™">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="works-name-input" name="works_name" value="{{ $worksName }}">
                        <input type="hidden" id="works-enabled-input" name="works_enabled" value="{{ $worksEnabled ? '1' : '0' }}">
                    <div id="works-content" class="p-4 overflow-x-auto hidden">
                        <table class="w-full mb-4 text-xs table-fixed">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left w-[28%]">Nazwa</th>
                                    <th class="p-1 text-left w-[28%]">Opis</th>
                                    <th class="p-1 text-left w-16">IloĹ›Ä‡</th>
                                    <th class="p-1 text-left w-[14%]">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena (zĹ‚)</th>
                                    <th class="p-1 text-center w-24">Cena kat.</th>
                                    <th class="p-1 text-left w-24">WartoĹ›Ä‡ (zĹ‚)</th>
                                    <th class="p-1 w-24"></th>
                                </tr>
                            </thead>
                            <tbody id="works-table">
                                @forelse($offer->works ?? [] as $index => $work)
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="{{ $index + 1 }}" readonly></td>
                                    <td class="p-1"><textarea name="works[{{ $index }}][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">{{ $work['name'] ?? '' }}</textarea></td>
                                    <td class="p-1"><textarea name="works[{{ $index }}][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">{{ $work['type'] ?? '' }}</textarea></td>
                                    <td class="p-1"><input type="number" min="1" value="{{ $work['quantity'] ?? 1 }}" name="works[{{ $index }}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="works" onchange="calculateRowValue(this)"></td>
                                                                <td class="p-1">
                                                                    <select name="works[{{ $index }}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                                                        <option value="">-- brak --</option>
                                                                        @foreach($suppliers as $supplier)
                                                                            <option value="{{ $supplier->name }}" @if(($work['supplier'] ?? '') == $supplier->name) selected @endif>{{ $supplier->short_name ?: $supplier->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[{{ $index }}][price]" value="{{ $work['price'] ?? '' }}" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="works" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[{{ $index }}][catalog_price]" value="{{ $work['catalog_price'] ?? '' }}" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[{{ $index }}][value]" value="{{ ($work['quantity'] ?? 1) * ($work['price'] ?? 0) }}" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="works" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'works')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'works', {{ $index }})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                    <td class="p-1"><textarea name="works[0][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                    <td class="p-1"><textarea name="works[0][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                    <td class="p-1"><input type="number" min="1" value="1" name="works[0][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="works" onchange="calculateRowValue(this)"></td>
                                                                <td class="p-1">
                                                                    <select name="works[0][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                                                        <option value="">-- brak --</option>
                                                                        @foreach($suppliers as $supplier)
                                                                            <option value="{{ $supplier->name }}">{{ $supplier->short_name ?: $supplier->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[0][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="works" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[0][catalog_price]" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="works[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="works" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'works')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'works', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="flex gap-2">
                            <button type="button" onclick="addRow('works')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                            <button type="button" onclick="openPartsCatalog('works')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">đź“‚ Wybierz z katalogu</button>
                        </div>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="works-total" class="font-bold text-lg">0.00 zĹ‚</span>
                        </div>
                    </div>
                </div>

                <!-- Sekcja MateriaĹ‚y -->
                <div id="section-materials" class="border border-gray-300 rounded" @if(!$materialsEnabled) style="display:none;" @endif>
                    <div class="flex items-center justify-between p-4 bg-gray-50">
                        <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('materials')">
                            <span class="font-semibold text-lg section-name text-left" id="materials-name-label" style="min-width:0;">{{ $materialsName }}</span>
                            <span class="flex-1"></span>
                            <span class="mr-3 whitespace-nowrap font-semibold text-lg text-right" style="min-width:120px;">
                                <span id="materials-header-sum" class="text-gray-600">0,00 zĹ‚</span>
                                <span class="text-gray-400"> / </span>
                                <span id="materials-header-profit" class="text-green-600">0,00 zĹ‚</span>
                            </span>
                            <svg id="materials-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button type="button" onclick="editSectionName('materials')" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwÄ™">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                        </button>
                        <button type="button" onclick="removeMainSection('materials')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="UsuĹ„ sekcjÄ™">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                        <input type="hidden" id="materials-name-input" name="materials_name" value="{{ $materialsName }}">
                        <input type="hidden" id="materials-enabled-input" name="materials_enabled" value="{{ $materialsEnabled ? '1' : '0' }}">
                    <div id="materials-content" class="p-4 overflow-x-auto hidden">
                        <table class="w-full mb-4 text-xs table-fixed">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-1 text-left w-10">Nr</th>
                                    <th class="p-1 text-left w-[28%]">Nazwa</th>
                                    <th class="p-1 text-left w-[28%]">Opis</th>
                                    <th class="p-1 text-left w-16">IloĹ›Ä‡</th>
                                    <th class="p-1 text-left w-[14%]">Dostawca</th>
                                    <th class="p-1 text-left w-24">Cena (zĹ‚)</th>
                                    <th class="p-1 text-center w-24">Cena kat.</th>
                                    <th class="p-1 text-left w-24">WartoĹ›Ä‡ (zĹ‚)</th>
                                    <th class="p-1 w-24"></th>
                                </tr>
                            </thead>
                            <tbody id="materials-table">
                                @forelse($offer->materials ?? [] as $index => $material)
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="{{ $index + 1 }}" readonly></td>
                                    <td class="p-1"><textarea name="materials[{{ $index }}][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">{{ $material['name'] ?? '' }}</textarea></td>
                                    <td class="p-1"><textarea name="materials[{{ $index }}][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">{{ $material['type'] ?? '' }}</textarea></td>
                                    <td class="p-1"><input type="number" min="1" value="{{ $material['quantity'] ?? 1 }}" name="materials[{{ $index }}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="materials" onchange="calculateRowValue(this)"></td>
                                                                <td class="p-1">
                                                                    <select name="materials[{{ $index }}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                                                        <option value="">-- brak --</option>
                                                                        @foreach($suppliers as $supplier)
                                                                            <option value="{{ $supplier->name }}" @if(($material['supplier'] ?? '') == $supplier->name) selected @endif>{{ $supplier->short_name ?: $supplier->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[{{ $index }}][price]" value="{{ $material['price'] ?? '' }}" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="materials" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[{{ $index }}][catalog_price]" value="{{ $material['catalog_price'] ?? '' }}" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[{{ $index }}][value]" value="{{ ($material['quantity'] ?? 1) * ($material['price'] ?? 0) }}" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="materials" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'materials')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'materials', {{ $index }})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                    <td class="p-1"><textarea name="materials[0][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                    <td class="p-1"><textarea name="materials[0][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                    <td class="p-1"><input type="number" min="1" value="1" name="materials[0][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="materials" onchange="calculateRowValue(this)"></td>
                                                                <td class="p-1">
                                                                    <select name="materials[0][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                                                        <option value="">-- brak --</option>
                                                                        @foreach($suppliers as $supplier)
                                                                            <option value="{{ $supplier->name }}">{{ $supplier->short_name ?: $supplier->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[0][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="materials" onchange="calculateRowValue(this)"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[0][catalog_price]" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                                    <td class="p-1"><input type="number" step="0.01" name="materials[0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="materials" readonly></td>
                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="removeRow(this, 'materials')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'materials', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="flex gap-2">
                            <button type="button" onclick="addRow('materials')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                            <button type="button" onclick="openPartsCatalog('materials')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">đź“‚ Wybierz z katalogu</button>
                        </div>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="materials-total" class="font-bold text-lg">0.00 zĹ‚</span>
                        </div>
                    </div>
                </div>

                <!-- Dynamiczne sekcje niestandardowe -->
                <div id="custom-sections-container">
                    @if(isset($offer->custom_sections) && is_array($offer->custom_sections))
                        @php
                            $customSectionsList = [];
                            foreach($offer->custom_sections as $key => $value) {
                                if(is_array($value)) {
                                    $customSectionsList[] = $value;
                                }
                            }
                        @endphp
                        @foreach($customSectionsList as $sectionIndex => $customSection)
                            <div class="border border-gray-300 rounded mb-4" id="section-custom{{ $sectionIndex + 1 }}">
                                <div class="flex items-center justify-between p-4 bg-gray-50">
                                    <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('custom{{ $sectionIndex + 1 }}')">
                                        <span class="font-semibold text-lg section-name text-left" id="custom{{ $sectionIndex + 1 }}-name-label" style="min-width:0;">{{ $customSection['name'] ?? 'Sekcja ' . ($sectionIndex + 1) }}</span>
                                        <span class="flex-1"></span>
                                        <span class="mr-3 whitespace-nowrap font-semibold text-lg text-right" style="min-width:120px;">
                                            <span id="custom{{ $sectionIndex + 1 }}-header-sum" class="text-gray-600">0,00 zĹ‚</span>
                                            <span class="text-gray-400"> / </span>
                                            <span id="custom{{ $sectionIndex + 1 }}-header-profit" class="text-green-600">0,00 zĹ‚</span>
                                        </span>
                                        <svg id="custom{{ $sectionIndex + 1 }}-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>
                                    <button type="button" onclick="editSectionName('custom{{ $sectionIndex + 1 }}', {{ $sectionIndex + 1 }})" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwÄ™">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                                    </button>
                                    <button type="button" onclick="removeCustomSection('custom{{ $sectionIndex + 1 }}')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="UsuĹ„ sekcjÄ™">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                                <div id="custom{{ $sectionIndex + 1 }}-content" class="p-4 overflow-x-auto hidden">
                                    <input type="hidden" id="custom{{ $sectionIndex + 1 }}-name-input" name="custom_sections[{{ $sectionIndex + 1 }}][name]" value="{{ $customSection['name'] ?? '' }}">
                                    <table class="w-full mb-4 text-xs table-fixed">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="p-1 text-left w-10">Nr</th>
                                                <th class="p-1 text-left w-[28%]">Nazwa</th>
                                                <th class="p-1 text-left w-[28%]">Opis</th>
                                                <th class="p-1 text-left w-16">IloĹ›Ä‡</th>
                                                <th class="p-1 text-left w-[14%]">Dostawca</th>
                                                <th class="p-1 text-left w-24">Cena (zĹ‚)</th>
                                                <th class="p-1 text-center w-24">Cena kat.</th>
                                                <th class="p-1 text-left w-24">WartoĹ›Ä‡ (zĹ‚)</th>
                                                <th class="p-1 w-24"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="custom{{ $sectionIndex + 1 }}-table">
                                            @forelse($customSection['items'] ?? [] as $itemIndex => $item)
                                                <tr>
                                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="{{ $itemIndex + 1 }}" readonly></td>
                                                    <td class="p-1"><textarea name="custom_sections[{{ $sectionIndex + 1 }}][items][{{ $itemIndex }}][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">{{ $item['name'] ?? '' }}</textarea></td>
                                                    <td class="p-1"><textarea name="custom_sections[{{ $sectionIndex + 1 }}][items][{{ $itemIndex }}][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">{{ $item['type'] ?? '' }}</textarea></td>
                                                    <td class="p-1"><input type="number" min="1" value="{{ $item['quantity'] ?? 1 }}" name="custom_sections[{{ $sectionIndex + 1 }}][items][{{ $itemIndex }}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="custom{{ $sectionIndex + 1 }}" onchange="calculateRowValue(this)"></td>
                                                                                                        <td class="p-1">
                                                                                                            <select name="custom_sections[{{ $sectionIndex + 1 }}][items][{{ $itemIndex }}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                                                                                                <option value="">-- brak --</option>
                                                                                                                @foreach($suppliers as $supplier)
                                                                                                                    <option value="{{ $supplier->name }}" @if(($item['supplier'] ?? '') == $supplier->name) selected @endif>{{ $supplier->short_name ?: $supplier->name }}</option>
                                                                                                                @endforeach
                                                                                                            </select>
                                                                                                        </td>
                                                    <td class="p-1"><input type="number" step="0.01" name="custom_sections[{{ $sectionIndex + 1 }}][items][{{ $itemIndex }}][price]" value="{{ $item['price'] ?? '' }}" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="custom{{ $sectionIndex + 1 }}" onchange="calculateRowValue(this)"></td>
                                                    <td class="p-1"><input type="number" step="0.01" name="custom_sections[{{ $sectionIndex + 1 }}][items][{{ $itemIndex }}][catalog_price]" value="{{ $item['catalog_price'] ?? '' }}" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                                                    <td class="p-1"><input type="number" step="0.01" name="custom_sections[{{ $sectionIndex + 1 }}][items][{{ $itemIndex }}][value]" value="{{ ($item['quantity'] ?? 1) * ($item['price'] ?? 0) }}" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="custom{{ $sectionIndex + 1 }}" readonly></td>
                                                    <td class="p-1"><div class="flex items-center gap-0.5">@if($itemIndex > 0)<button type="button" onclick="removeRow(this, 'custom{{ $sectionIndex + 1 }}')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>@endif<button type="button" onclick="addProductToCatalog(this, 'custom_sections[{{ $sectionIndex + 1 }}][items]', {{ $itemIndex }})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                                    <td class="p-1"><textarea name="custom_sections[{{ $sectionIndex + 1 }}][items][0][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                                    <td class="p-1"><textarea name="custom_sections[{{ $sectionIndex + 1 }}][items][0][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                                    <td class="p-1"><input type="number" min="1" value="1" name="custom_sections[{{ $sectionIndex + 1 }}][items][0][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="custom{{ $sectionIndex + 1 }}" onchange="calculateRowValue(this)"></td>
                                                                                                        <td class="p-1">
                                                                                                            <select name="custom_sections[{{ $sectionIndex + 1 }}][items][0][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">
                                                                                                                <option value="">-- brak --</option>
                                                                                                                @foreach($suppliers as $supplier)
                                                                                                                    <option value="{{ $supplier->name }}">{{ $supplier->short_name ?: $supplier->name }}</option>
                                                                                                                @endforeach
                                                                                                            </select>
                                                                                                        </td>
                                                    <td class="p-1"><input type="number" step="0.01" name="custom_sections[{{ $sectionIndex + 1 }}][items][0][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="custom{{ $sectionIndex + 1 }}" onchange="calculateRowValue(this)"></td>
                                                    <td class="p-1"><input type="number" step="0.01" name="custom_sections[{{ $sectionIndex + 1 }}][items][0][catalog_price]" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                                                    <td class="p-1"><input type="number" step="0.01" name="custom_sections[{{ $sectionIndex + 1 }}][items][0][value]" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="custom{{ $sectionIndex + 1 }}" readonly></td>
                                                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="addProductToCatalog(this, 'custom_sections[{{ $sectionIndex + 1 }}][items]', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="addCustomRow('custom{{ $sectionIndex + 1 }}', {{ $sectionIndex + 1 }})" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                                        <button type="button" onclick="openPartsCatalog('custom{{ $sectionIndex + 1 }}')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">đź“‚ Wybierz z katalogu</button>
                                    </div>
                                    <div class="mt-4 text-right">
                                        <span class="font-semibold">Suma: </span>
                                        <span id="custom{{ $sectionIndex + 1 }}-total" class="font-bold text-lg">0.00 zĹ‚</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Przycisk dodawania nowej sekcji -->
                <div class="text-center">
                    <button type="button" onclick="addCustomSection()" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2 mx-auto">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Dodaj nowÄ… sekcjÄ™
                    </button>
                </div>

                <!-- Suma koĹ„cowa + Zysk -->
                <div class="bg-gray-50 p-4 rounded border border-gray-300 text-sm text-gray-600">
                    <div class="flex items-center justify-end gap-6 mb-2">
                        <span>Koszty: <b class="font-semibold text-gray-800" id="costs-display">0,00 zĹ‚</b></span>
                        <span>Oferta: <b class="font-semibold text-gray-800" id="offer-display">0,00 zĹ‚</b></span>
                        <span id="grand-total" class="hidden"></span>
                    </div>
                    <div class="flex items-center justify-end gap-3 mb-2">
                        <span>Zysk:</span>
                        <span id="built-in-profit-display" class="w-44 text-right">0,00 zĹ‚ (0,0%)</span>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-3 mb-2">
                        <span>Zysk dodatkowy:</span>
                        <div class="flex items-center gap-1">
                            <input type="number" id="profit-percent" name="profit_percent" min="0" max="10000" step="0.01" value="{{ $offer->profit_percent ?? 0 }}" class="w-20 px-2 py-1 border rounded text-sm text-right focus:ring-2 focus:ring-blue-300" oninput="updateProfitFromPercent()">
                            <span>%</span>
                        </div>
                        <span class="text-gray-400">lub</span>
                        <div class="flex items-center gap-1">
                            <input type="number" id="profit-amount-input" name="profit_amount" min="0" step="0.01" value="{{ $offer->profit_amount ?? 0 }}" class="w-32 px-2 py-1 border rounded text-sm text-right focus:ring-2 focus:ring-blue-300" oninput="updateProfitFromAmount()">
                            <span>zĹ‚</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mb-2">
                        <span class="font-medium text-gray-700">ĹÄ…czny zysk:</span>
                        <span id="total-profit-display" class="font-semibold text-gray-700 w-44 text-right">0,00 zĹ‚ (0,0%)</span>
                    </div>
                    <div class="flex items-center justify-end gap-3 font-semibold text-gray-800">
                        <span>Razem z zyskiem:</span>
                        <span id="total-with-profit" class="text-lg w-44 text-right text-green-700">0,00 zĹ‚</span>
                    </div>
                </div>

                <!-- Harmonogram i Warunki PĹ‚atnoĹ›ci -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Harmonogram -->
                    <div class="border border-gray-300 rounded bg-white">
                        <div class="p-3 bg-gray-50 border-b flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="schedule-enabled" name="schedule_enabled" value="1" class="w-4 h-4 accent-blue-600" onchange="toggleSchedule(this.checked)" {{ ($offer->schedule_enabled ?? false) ? 'checked' : '' }}>
                                <span class="font-semibold text-gray-800">Harmonogram</span>
                            </label>
                        </div>
                        <div id="schedule-section" class="{{ ($offer->schedule_enabled ?? false) ? '' : 'hidden' }} p-3">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-1 border text-center w-8">Lp.</th>
                                        <th class="p-1 border text-left">Etap / Milestone</th>
                                        <th class="p-1 border text-left">Opis</th>
                                        <th class="p-1 border w-6"></th>
                                    </tr>
                                </thead>
                                <tbody id="schedule-table"></tbody>
                            </table>
                            <button type="button" onclick="addScheduleRow()" class="mt-2 px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">+ Dodaj wiersz</button>
                        </div>
                    </div>

                    <!-- Warunki PĹ‚atnoĹ›ci -->
                    <div class="border border-gray-300 rounded bg-white">
                        <div class="p-3 bg-gray-50 border-b">
                            <span class="font-semibold text-gray-800">Warunki pĹ‚atnoĹ›ci</span>
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
                    <textarea id="offer_description" name="offer_description" style="display: none;">{{ $offer->offer_description ?? '' }}</textarea>
                </div>

                <!-- Miejsce docelowe oferty -->
                <div class="border-t pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gdzie ma wylÄ…dowaÄ‡ oferta?</label>
                    <select name="destination" class="w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="portfolio" {{ $offer->status === 'portfolio' ? 'selected' : '' }}>Portfolio</option>
                        <option value="inprogress" {{ $offer->status === 'inprogress' ? 'selected' : '' }}>Oferty w toku</option>
                    </select>
                </div>

                <!-- Przycisk Zapisz -->
                <!-- Podsumowanie dostawcĂłw -->
                <div class="bg-blue-50 border border-blue-300 rounded p-4 mb-6">
                    <h3 class="text-lg font-semibold mb-2 text-blue-900">Podsumowanie dostawcĂłw</h3>
                    <div id="suppliers-summary">
                        <!-- Tu pojawi siÄ™ podsumowanie JS -->
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg text-lg font-semibold hover:bg-green-700 transition">
                        Zapisz zmiany
                    </button>
                </div>
                <!-- PĹ‚ywajÄ…cy przycisk submit -->
                <button id="float-save-btn" type="submit" style="position:fixed; bottom:28px; right:28px; z-index:99999; padding:16px 28px; border:none; border-radius:50px; font-size:17px; font-weight:bold; color:#fff; background:#16a34a; box-shadow:0 6px 20px rgba(0,0,0,0.3); cursor:pointer; transition:background 0.2s;">
                    đź’ľ Zapisz zmiany
                </button>
            </form>
        </div>
<script>
function getSupplierSummary() {
    const supplierTotals = {};
    const supplierFields = document.querySelectorAll('#offer-form select[name$="[supplier]"]');

    supplierFields.forEach((supplierField) => {
        const row = supplierField.closest('tr');
        if (!row) return;

        const valueField = row.querySelector('input[name$="[value]"]');
        const supplierName = (supplierField.value || '').trim() || 'Inne';
        const value = parseFloat((valueField?.value || '0').replace(/[\s\u00a0]/g, '').replace(',', '.')) || 0;

        if (!supplierTotals[supplierName]) {
            supplierTotals[supplierName] = 0;
        }

        supplierTotals[supplierName] += value;
    });

    return supplierTotals;
}

function renderSupplierSummary() {
    const summary = getSupplierSummary();
    const container = document.getElementById('suppliers-summary');
    container.innerHTML = '';
    const keys = Object.keys(summary);
    if (keys.length === 0) {
        container.innerHTML = '<span class="text-gray-500">Brak pozycji do podsumowania.</span>';
        return;
    }
    const table = document.createElement('table');
    table.className = 'w-full text-sm';
    table.innerHTML = '<thead><tr><th class="text-left p-1">Dostawca</th><th class="text-right p-1">Suma (zĹ‚)</th></tr></thead>';
    const tbody = document.createElement('tbody');
    keys.forEach(supplier => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="p-1">${supplier}</td><td class="p-1 text-right font-semibold">${formatPrice(summary[supplier])}</td>`;
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);
}

// OdĹ›wieĹĽ podsumowanie po kaĹĽdej zmianie
document.addEventListener('input', function(e) {
    if (e.target.matches('select[name*="[supplier]"]') || e.target.matches('input[name*="[value]"]')) {
        renderSupplierSummary();
    }
});
document.addEventListener('change', function(e) {
    if (e.target.matches('select[name*="[supplier]"]') || e.target.matches('input[name*="[value]"]') || e.target.matches('input[name*="[price]"]') || e.target.matches('input[name*="[quantity]"]')) {
        renderSupplierSummary();
    }
});
document.addEventListener('DOMContentLoaded', renderSupplierSummary);
</script>
    </main>

    <!-- Modal katalogu czÄ™Ĺ›ci -->
    <div id="parts-catalog-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] flex flex-col">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="text-xl font-bold">Katalog czÄ™Ĺ›ci z magazynu</h3>
                <button type="button" onclick="closePartsCatalog()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <div class="p-4 border-b">
                <input type="text" 
                    id="catalog-search" 
                    placeholder="Szukaj w katalogu..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex-1 overflow-auto p-4">
                <div id="catalog-loading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-gray-600">Wczytywanie katalogu...</p>
                </div>
                <div id="catalog-content" class="hidden">
                    <div class="mb-2 flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="select-all-parts" onchange="toggleSelectAll()" class="mr-2">
                            Zaznacz wszystkie
                        </label>
                        <span id="selected-count" class="text-gray-600">Wybrano: 0</span>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="p-2 text-left w-10"></th>
                                <th class="p-2 text-left w-[34%]">Nazwa</th>
                                <th class="p-2 text-left w-[34%]">Opis</th>
                                <th class="p-2 text-left w-[17%]">Dostawca</th>
                                <th class="p-2 text-left w-20">IloĹ›Ä‡</th>
                                <th class="p-2 text-left w-24">Cena netto</th>
                                <th class="p-2 text-left w-24">Cena kat.</th>
                            </tr>
                        </thead>
                        <tbody id="catalog-parts-list"></tbody>
                    </table>
                </div>
            </div>
            <div class="p-4 border-t flex justify-end gap-2">
                <button type="button" onclick="closePartsCatalog()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Anuluj</button>
                <button type="button" onclick="addSelectedParts()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Dodaj wybrane</button>
            </div>
        </div>
    </div>

    <script>
        // Bezpieczna lista dostawcĂłw jako dane JSON
        const _supplierOptionsHtml = (function() {
            var d = @json(collect($suppliers ?? [])->map(fn($s) => ['v' => $s->name, 'l' => $s->short_name ?: $s->name])->values());
            function esc(t){return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
            return '<option value="">-- brak --<\/option>' + d.map(function(s){return '<option value="'+esc(s.v)+'">'+esc(s.l)+'<\/option>';}).join('');
        })();

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
                    alert('BĹ‚Ä…d dodawania produktu do katalogu.');
                }
            } catch (e) {
                alert('BĹ‚Ä…d sieci podczas dodawania produktu.');
            }
        }

        let rowCounters = {
            services: {{ count($offer->services ?? []) }},
            works: {{ count($offer->works ?? []) }},
            materials: {{ count($offer->materials ?? []) }}
        };
        
        let customSectionCounter = {{ count($offer->custom_sections ?? []) }};
        let customSections = [];
        let _grandTotalRaw = 0;
        let _sectionTotals = {};
        
        // Zmienne dla katalogu czÄ™Ĺ›ci
        let allParts = [];
        let filteredParts = [];
        let currentCatalogSection = 'materials';
        
        // Inicjalizuj istniejÄ…ce sekcje niestandardowe
        @if(isset($offer->custom_sections) && is_array($offer->custom_sections))
            @php
                $customSectionsList = [];
                foreach($offer->custom_sections as $key => $value) {
                    if(is_array($value)) {
                        $customSectionsList[] = $value;
                    }
                }
            @endphp
            @foreach($customSectionsList as $sectionIndex => $customSection)
                customSections.push({{ $sectionIndex + 1 }});
                rowCounters['custom{{ $sectionIndex + 1 }}'] = {{ count($customSection['items'] ?? []) }};
            @endforeach
        @endif

        // Oblicz sumy przy Ĺ‚adowaniu
        function autoResizeTextarea(ta) {
            ta.style.overflowY = 'hidden';
            ta.style.height = 'auto';
            ta.style.height = ta.scrollHeight + 'px';
        }

        document.addEventListener('input', function(e) {
            if (e.target.tagName === 'TEXTAREA') autoResizeTextarea(e.target);
        });

        document.addEventListener('DOMContentLoaded', function() {
            initCheckboxColumn();
            formatAllValueInputs();
            calculateTotal('services');
            calculateTotal('works');
            calculateTotal('materials');
            
            // Oblicz sumy dla niestandardowych sekcji
            customSections.forEach(sectionNum => {
                calculateTotal(`custom${sectionNum}`);
            });

            initMoveButtons();

            // Auto-resize visible textareas only (hidden sections will be resized on expand)
            document.querySelectorAll('textarea').forEach(ta => {
                if (ta.offsetParent !== null) autoResizeTextarea(ta);
            });

            // Inicjalizuj istniejÄ…ce wiersze harmonogramu
            @foreach($offer->schedule ?? [] as $row)
            addScheduleRow('{{ addslashes($row['milestone'] ?? '') }}', '{{ $row['date'] ?? '' }}', '{{ addslashes($row['description'] ?? '') }}');
            @endforeach

            // Inicjalizuj istniejÄ…ce warunki pĹ‚atnoĹ›ci
            @foreach($offer->payment_terms ?? [] as $term)
            addPaymentRow('{{ addslashes($term['description'] ?? '') }}', '{{ $term['percent'] ?? '' }}', '{{ addslashes($term['deadline'] ?? '') }}');
            @endforeach
        });

        // ===========================================
        // OBSĹUGA KATALOGU CZÄĹšCI
        // ===========================================
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
                    console.error('BĹ‚Ä…d HTTP:', response.status, errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                let data = await response.json();
                console.log('=== DEBUG: Otrzymane dane z API ===');
                console.log('PeĹ‚na odpowiedĹş:', data);
                console.log('Typ danych:', typeof data);
                console.log('Czy tablica:', Array.isArray(data));
                if (typeof data === 'object' && data !== null) {
                    console.log('Klucze obiektu:', Object.keys(data));
                }
                console.log('DĹ‚ugoĹ›Ä‡ (length):', data.length);
                console.log('===================================');
                
                // SprawdĹş czy API zwrĂłciĹ‚o bĹ‚Ä…d (obiekt z kluczem 'error')
                if (data && typeof data === 'object' && data.error) {
                    console.error('API zwrĂłciĹ‚o bĹ‚Ä…d:', data);
                    throw new Error(data.message || data.error || 'Nieznany bĹ‚Ä…d API');
                }
                
                // WYMUSZENIE TABLICY: Konwertuj obiekt na tablicÄ™ jeĹ›li nie jest juĹĽ tablicÄ…
                if (!Array.isArray(data)) {
                    if (typeof data === 'object' && data !== null) {
                        console.warn('âš ď¸Ź API zwrĂłciĹ‚o obiekt zamiast tablicy - konwertujÄ™ automatycznie');
                        console.log('Przed konwersjÄ…:', data);
                        // UĹĽyj Object.values() aby wyciÄ…gnÄ…Ä‡ wartoĹ›ci
                        data = Object.values(data);
                        console.log('Po konwersji (is array):', Array.isArray(data), 'length:', data.length);
                    } else {
                        console.error('âťŚ API zwrĂłciĹ‚o nieprawidĹ‚owy typ:', typeof data);
                        throw new Error('API zwrĂłciĹ‚o nieprawidĹ‚owy format danych (oczekiwano tablicy lub obiektu, otrzymano: ' + typeof data + ')');
                    }
                }
                
                // Dodatkowa walidacja
                if (!Array.isArray(data)) {
                    console.error('âťŚ Konwersja nie powiodĹ‚a siÄ™, data nadal nie jest tablicÄ…');
                    throw new Error('Nie udaĹ‚o siÄ™ przekonwertowaÄ‡ danych na tablicÄ™');
                }
                
                allParts = data;
                filteredParts = [...allParts];
                
                document.getElementById('catalog-loading').classList.add('hidden');
                document.getElementById('catalog-content').classList.remove('hidden');
                
                renderCatalog();
                setupCatalogSearch();
            } catch (error) {
                console.error('BĹ‚Ä…d Ĺ‚adowania katalogu:', error);
                
                // Ukryj loading, pokaĹĽ treĹ›Ä‡ (ktĂłra wyĹ›wietli komunikat o bĹ‚Ä™dzie)
                document.getElementById('catalog-loading').classList.add('hidden');
                document.getElementById('catalog-content').classList.remove('hidden');
                
                // WyĹ›wietl komunikat o bĹ‚Ä™dzie w tabeli
                const tbody = document.getElementById('catalog-parts-list');
                tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-red-600">
                    Nie udaĹ‚o siÄ™ zaĹ‚adowaÄ‡ katalogu: ${error.message}<br>
                    <small>Sprawdz konsolÄ™ przeglÄ…darki (F12) aby zobaczyÄ‡ szczegĂłĹ‚y.</small>
                </td></tr>`;
                
                alert('Nie udaĹ‚o siÄ™ zaĹ‚adowaÄ‡ katalogu czÄ™Ĺ›ci. BĹ‚Ä…d: ' + error.message);
            }
        }
        
        function renderCatalog() {
            const tbody = document.getElementById('catalog-parts-list');
            
            if (filteredParts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="p-4 text-center text-gray-500">Nie znaleziono czÄ™Ĺ›ci</td></tr>';
                return;
            }
            
            tbody.innerHTML = filteredParts.map(part => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">
                        <input type="checkbox" 
                            class="part-checkbox" 
                            data-id="${part.id}"
                            data-name="${escapeHtml(part.name)}"
                            data-description="${escapeHtml(part.description || '')}"
                            data-supplier="${escapeHtml(part.supplier || '')}"
                            data-price="${part.net_price || 0}"
                            data-catalog-price="${part.catalog_price != null ? part.catalog_price : (part.net_price || 0)}"
                            onchange="updateSelectedCount()">
                    </td>
                    <td class="p-2 font-medium"><div class="break-words" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">${escapeHtml(part.name)}</div></td>
                    <td class="p-2 text-gray-600"><div class="break-words" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">${escapeHtml(part.description || '-')}</div></td>
                    <td class="p-2"><div class="break-words" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">${escapeHtml(part.supplier || '-')}</div></td>
                    <td class="p-2">${part.quantity || 0}</td>
                    <td class="p-2 font-medium">${formatPrice(parseFloat(part.net_price || 0))}</td>
                    <td class="p-2 text-gray-500">${formatPrice(parseFloat(part.catalog_price != null ? part.catalog_price : (part.net_price || 0)))}</td>
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
                alert('Nie wybrano ĹĽadnych czÄ™Ĺ›ci');
                return;
            }
            
            const section = currentCatalogSection;
            const isCustomSection = section.startsWith('custom');
            
            selected.forEach(checkbox => {
                const name = checkbox.dataset.name;
                const description = checkbox.dataset.description || '';
                const supplier = checkbox.dataset.supplier;
                const price = checkbox.dataset.price;
                const catalogPrice = checkbox.dataset.catalogPrice != null ? checkbox.dataset.catalogPrice : price;
                const safeName = escapeHtml(name || '');
                const safeDescription = escapeHtml(description || '');
                const safeSupplier = escapeHtml(supplier || '');
                
                // Pobierz tabelÄ™ dla odpowiedniej sekcji
                const table = document.getElementById(`${section}-table`);
                const rowCount = rowCounters[section];
                
                // Ustal odpowiedniÄ… nazwÄ™ pola w formularzu
                let fieldPrefix;
                if (isCustomSection) {
                    const sectionNumber = section.replace('custom', '');
                    fieldPrefix = `custom_sections[${sectionNumber}][items][${rowCount}]`;
                } else {
                    fieldPrefix = `${section}[${rowCount}]`;
                }
                
                let supplierOptions = _supplierOptionsHtml;
                // (pre-computed from PHP to avoid backtick issues in template literals)

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="p-1 text-center"><input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer"></td>
                    <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="${rowCount + 1}" readonly></td>
                    <td class="p-1"><textarea name="${fieldPrefix}[name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">${safeName}</textarea></td>
                    <td class="p-1"><textarea name="${fieldPrefix}[type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden">${safeDescription}</textarea></td>
                    <td class="p-1"><input type="number" min="1" value="1" name="${fieldPrefix}[quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                    <td class="p-1"><select name="${fieldPrefix}[supplier]" class="w-full px-1 py-0.5 border rounded text-xs">${supplierOptions}</select></td>
                    <td class="p-1"><input type="number" step="0.01" name="${fieldPrefix}[price]" value="${price}" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                    <td class="p-1"><input type="number" step="0.01" name="${fieldPrefix}[catalog_price]" value="${catalogPrice}" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                    <td class="p-1"><input type="text" name="${fieldPrefix}[value]" value="0" data-raw="0" data-formatted-init="1" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${section}" readonly></td>
                    <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="moveRow(this,'up','${section}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="WyĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button><button type="button" onclick="moveRow(this,'down','${section}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="NiĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button><button type="button" onclick="removeRow(this, '${section}')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, '${section}', ${rowCount})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                `;

                // Select the matching supplier
                if (supplier) {
                    const supSelect = row.querySelector('select');
                    if (supSelect) supSelect.value = supplier;
                }

                table.appendChild(row);
                rowCounters[section]++;

                // Calculate row value immediately
                const priceInput = row.querySelector('.price-input');
                if (priceInput) calculateRowValue(priceInput);
            });

            reindexSection(section);
            calculateTotal(section);
            closePartsCatalog();
        }

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

        function updateBuiltInProfit() {
            let builtIn = 0, offerTotal = 0;
            document.querySelectorAll('.catalog-price-input').forEach(function(catalogInput) {
                const row = catalogInput.closest('tr');
                if (!row) return;
                const priceInput = row.querySelector('.price-input');
                const qtyInput = row.querySelector('.quantity-input');
                const price = parseFloat(priceInput ? priceInput.value : '') || 0;
                const catalog = parseFloat(catalogInput.value) || price;
                const qty = parseFloat(qtyInput ? qtyInput.value : '1') || 1;
                if (catalog > price) builtIn += (catalog - price) * qty;
                offerTotal += catalog * qty;
            });
            const pct = _grandTotalRaw > 0 ? (builtIn / _grandTotalRaw * 100) : 0;
            const builtEl = document.getElementById('built-in-profit-display');
            if (builtEl) builtEl.textContent = formatPrice(builtIn) + ' (' + pct.toFixed(1) + '%)';
            const costsEl = document.getElementById('costs-display');
            if (costsEl) costsEl.textContent = formatPrice(_grandTotalRaw);
            const offerEl = document.getElementById('offer-display');
            if (offerEl) offerEl.textContent = formatPrice(offerTotal);
            const additionalAmount = parseFloat((document.getElementById('profit-amount-input') || {}).value || '0') || 0;
            const totalProfit = builtIn + additionalAmount;
            const totalPct = _grandTotalRaw > 0 ? (totalProfit / _grandTotalRaw * 100) : 0;
            const totalEl = document.getElementById('total-profit-display');
            if (totalEl) totalEl.textContent = formatPrice(totalProfit) + ' (' + totalPct.toFixed(1) + '%)';
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('price-input')) {
                const row = e.target.closest('tr');
                if (row) {
                    const catalogInput = row.querySelector('.catalog-price-input');
                    if (catalogInput) catalogInput.value = e.target.value;
                }
                updateBuiltInProfit();
            }
        });

        function addRow(section) {
            const table = document.getElementById(section + '-table');
            const rowCount = rowCounters[section];
            const row = document.createElement('tr');
            const supplierOptions = _supplierOptionsHtml;
            row.innerHTML = `
                <td class="p-1 text-center"><input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer"></td>
                <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="${rowCount + 1}" readonly></td>
                <td class="p-1"><textarea name="${section}[${rowCount}][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                <td class="p-1"><textarea name="${section}[${rowCount}][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                <td class="p-1"><input type="number" min="1" value="1" name="${section}[${rowCount}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                <td class="p-1"><select name="${section}[${rowCount}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">${supplierOptions}</select></td>
                <td class="p-1"><input type="number" step="0.01" name="${section}[${rowCount}][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${section}" onchange="calculateRowValue(this)"></td>
                <td class="p-1"><input type="number" step="0.01" name="${section}[${rowCount}][catalog_price]" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                <td class="p-1"><input type="text" name="${section}[${rowCount}][value]" value="0" data-raw="0" data-formatted-init="1" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${section}" readonly></td>
                <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="moveRow(this,'up','${section}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="WyĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button><button type="button" onclick="moveRow(this,'down','${section}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="NiĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button><button type="button" onclick="removeRow(this, '${section}')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, '${section}', ${rowCount})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
            `;
            // Insert after last checked row, or append at end
            const allRows = table.querySelectorAll('tr');
            let lastChecked = null;
            allRows.forEach(r => { if (r.querySelector('.row-checkbox')?.checked) lastChecked = r; });
            if (lastChecked) lastChecked.after(row); else table.appendChild(row);
            rowCounters[section]++;
            reindexSection(section);
        }

        function removeRow(button, section) {
            button.closest('tr').remove();
            updateRowNumbers(section);
            calculateTotal(section);
            if (typeof renderSupplierSummary === 'function') {
                renderSupplierSummary();
            }
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

        function formatPrice(value) {
            return value.toLocaleString('pl-PL', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' zĹ‚';
        }

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
                '<button type="button" onclick="moveRow(this,\'up\',\'' + section + '\')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="WyĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button>' +
                '<button type="button" onclick="moveRow(this,\'down\',\'' + section + '\')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="NiĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>'
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
            if (typeof renderSupplierSummary === 'function') {
                renderSupplierSummary();
            }
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
            customSections.forEach(sectionNum => {
                const inputs = document.querySelectorAll(`#custom${sectionNum}-table .value-input`);
                inputs.forEach(input => {
                    grandTotal += parseFloat(input.dataset.raw || input.value) || 0;
                });
            });
            
            _grandTotalRaw = grandTotal;
            document.getElementById('grand-total').textContent = formatPrice(grandTotal);
            updateProfitFromPercent();
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
            updateBuiltInProfit();
        }

        // ===========================================
        // HARMONOGRAM I WARUNKI PĹATNOĹšCI
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
                <td class="p-1 border"><input type="text" name="schedule[${idx}][description]" value="${escapeHtml(description||'')}" class="w-full px-1 py-0.5 border rounded text-xs"></td>
                <td class="p-1 border text-center"><button type="button" onclick="this.closest('tr').remove(); reindexSchedule()" class="text-red-600 hover:text-red-800 text-xs">âś•</button></td>
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
                <td class="p-1 border text-center"><button type="button" onclick="this.closest('tr').remove(); reindexPayment()" class="text-red-600 hover:text-red-800 text-xs">âś•</button></td>
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
        // OBSĹUGA DYNAMICZNYCH SEKCJI
        // ===========================================
        function addCustomSection() {
            const sectionName = prompt('Podaj nazwÄ™ nowej sekcji:');
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
            const supplierOptions = _supplierOptionsHtml;
            sectionDiv.innerHTML = `
                <div class="flex items-center justify-between p-4 bg-gray-50">
                    <button type="button" class="flex-1 flex items-center hover:bg-gray-100 transition" onclick="toggleSection('${sectionId}')">
                        <span class="font-semibold text-lg section-name text-left" id="${sectionId}-name-label" style="min-width:0;">${escapeHtml(sectionName.trim())}</span>
                        <span class="mr-3 whitespace-nowrap font-semibold text-lg">
                            <span id="${sectionId}-header-sum" class="text-gray-600">0,00 z&#322;</span>
                            <span class="text-gray-400"> / </span>
                            <span id="${sectionId}-header-profit" class="text-green-600">0,00 z&#322;</span>
                        </span>
                        <svg id="${sectionId}-icon" class="h-5 w-5 transform transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <button type="button" onclick="editSectionName('${sectionId}', ${customSectionCounter})" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edytuj nazwÄ™">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3z" /></svg>
                    </button>
                    <button type="button" onclick="removeCustomSection('${sectionId}')" class="ml-2 px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="UsuĹ„ sekcjÄ™">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
                <div id="${sectionId}-content" class="p-4 overflow-x-auto hidden">
                    <input type="hidden" id="${sectionId}-name-input" name="custom_sections[${customSectionCounter}][name]" value="${escapeHtml(sectionName.trim())}">
                    <table class="w-full mb-4 text-xs table-fixed">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-1 w-5 cb-th"></th>
                                <th class="p-1 text-left w-10">Nr</th>
                                <th class="p-1 text-left w-[28%]">Nazwa</th>
                                <th class="p-1 text-left w-[28%]">Opis</th>
                                <th class="p-1 text-left w-16">IloĹ›Ä‡</th>
                                <th class="p-1 text-left w-[14%]">Dostawca</th>
                                <th class="p-1 text-left w-24">Cena (zĹ‚)</th>
                                <th class="p-1 text-left w-24">WartoĹ›Ä‡ (zĹ‚)</th>
                                <th class="p-1 w-24"></th>
                            </tr>
                        </thead>
                        <tbody id="${sectionId}-table">
                            <tr>
                                <td class="p-1 text-center"><input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer"></td>
                                <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="1" readonly></td>
                                <td class="p-1"><textarea name="custom_sections[${customSectionCounter}][items][0][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                <td class="p-1"><textarea name="custom_sections[${customSectionCounter}][items][0][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                                <td class="p-1"><input type="number" min="1" value="1" name="custom_sections[${customSectionCounter}][items][0][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${sectionId}" onchange="calculateRowValue(this)"></td>
                                <td class="p-1"><select name="custom_sections[${customSectionCounter}][items][0][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">${supplierOptions}</select></td>
                                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${customSectionCounter}][items][0][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${sectionId}" onchange="calculateRowValue(this)"></td>
                                <td class="p-1"><input type="text" name="custom_sections[${customSectionCounter}][items][0][value]" value="0" data-raw="0" data-formatted-init="1" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${sectionId}" readonly></td>
                                <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="moveRow(this,'up','${sectionId}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="WyĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button><button type="button" onclick="moveRow(this,'down','${sectionId}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="NiĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button><button type="button" onclick="removeRow(this,'${sectionId}')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'custom_sections[${customSectionCounter}][items]', 0)" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="flex gap-2">
                        <button type="button" onclick="addCustomRow('${sectionId}', ${customSectionCounter})" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                        <button type="button" onclick="openPartsCatalog('${sectionId}')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">đź“‚ Wybierz z katalogu</button>
                    </div>
                    <div class="mt-4 text-right">
                        <span class="font-semibold">Suma: </span>
                        <span id="${sectionId}-total" class="font-bold text-lg">0.00 zĹ‚</span>
                    </div>
                </div>
            `;
            container.appendChild(sectionDiv);
            // Ensure spacing for collapsed custom sections matches static ones
            const allCustomSections = container.querySelectorAll('.border.border-gray-300.rounded');
            allCustomSections.forEach(sec => {
                sec.style.marginBottom = '1rem';
            });
            // Automatycznie rozwiĹ„ nowÄ… sekcjÄ™
            toggleSection(sectionId);
        }

        function editSectionName(sectionId, sectionNumber) {
            const label = document.getElementById(`${sectionId}-name-label`);
            if (!label) return;
            const current = label.textContent;
            const newName = prompt('Edytuj nazwÄ™ sekcji:', current);
            if (newName && newName.trim() !== '') {
                label.textContent = newName.trim();
                // For custom sections, update hidden input
                const inputId = `${sectionId}-name-input`;
                const input = document.getElementById(inputId);
                if (input) input.value = newName.trim();
                _formChanged = true;
                _updateSaveBtn();
            }
        }

        function removeMainSection(sectionId) {
            if (!confirm('Czy na pewno chcesz usunÄ…Ä‡ tÄ™ sekcjÄ™?')) {
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
                total.textContent = '0.00 zĹ‚';
            }
            const enabledInput = document.getElementById(`${sectionId}-enabled-input`);
            if (enabledInput) {
                enabledInput.value = '0';
            }
            calculateGrandTotal();
            if (typeof renderSupplierSummary === 'function') {
                renderSupplierSummary();
            }
            _formChanged = true;
            _updateSaveBtn();
        }
        
        function removeCustomSection(sectionId) {
            if (!confirm('Czy na pewno chcesz usunÄ…Ä‡ tÄ™ sekcjÄ™?')) {
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
                if (typeof renderSupplierSummary === 'function') {
                    renderSupplierSummary();
                }
                _formChanged = true;
                _updateSaveBtn();
            }
        }
        
        function addCustomRow(sectionId, sectionNumber) {
            const table = document.getElementById(`${sectionId}-table`);
            const rowCount = rowCounters[sectionId];
            const supplierOptions = _supplierOptionsHtml;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="p-1 text-center"><input type="checkbox" class="row-checkbox accent-blue-600 cursor-pointer"></td>
                <td class="p-1"><input type="number" class="w-full px-1 py-0.5 border rounded text-xs" value="${rowCount + 1}" readonly></td>
                <td class="p-1"><textarea name="custom_sections[${sectionNumber}][items][${rowCount}][name]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                <td class="p-1"><textarea name="custom_sections[${sectionNumber}][items][${rowCount}][type]" rows="1" class="w-full px-1 py-0.5 border rounded text-xs resize-none leading-tight min-h-[1.6rem] overflow-hidden"></textarea></td>
                <td class="p-1"><input type="number" min="1" value="1" name="custom_sections[${sectionNumber}][items][${rowCount}][quantity]" class="w-full px-1 py-0.5 border rounded text-xs quantity-input" data-section="${sectionId}" onchange="calculateRowValue(this)"></td>
                <td class="p-1"><select name="custom_sections[${sectionNumber}][items][${rowCount}][supplier]" class="w-full px-1 py-0.5 border rounded text-xs">${supplierOptions}</select></td>
                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${sectionNumber}][items][${rowCount}][price]" class="w-full px-1 py-0.5 border rounded text-xs price-input" data-section="${sectionId}" onchange="calculateRowValue(this)"></td>
                <td class="p-1"><input type="number" step="0.01" name="custom_sections[${sectionNumber}][items][${rowCount}][catalog_price]" class="w-full px-1 py-0.5 border rounded text-xs catalog-price-input" placeholder="kat." oninput="updateBuiltInProfit()"></td>
                <td class="p-1"><input type="text" name="custom_sections[${sectionNumber}][items][${rowCount}][value]" value="0" data-raw="0" data-formatted-init="1" class="w-full px-1 py-0.5 border rounded text-xs bg-gray-100 value-input" data-section="${sectionId}" readonly></td>
                <td class="p-1"><div class="flex items-center gap-0.5"><button type="button" onclick="moveRow(this,'up','${sectionId}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="WyĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button><button type="button" onclick="moveRow(this,'down','${sectionId}')" class="p-0.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50" title="NiĹĽej"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button><button type="button" onclick="removeRow(this, '${sectionId}')" class="p-0.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50" title="UsuĹ„"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button><button type="button" onclick="addProductToCatalog(this, 'custom_sections[${sectionNumber}][items]', ${rowCount})" class="p-0.5 rounded text-amber-500 hover:text-amber-700 hover:bg-amber-50" title="Dodaj do katalogu"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button></div></td>
            `;
            const allRows = table.querySelectorAll('tr');
            let lastChecked = null;
            allRows.forEach(r => { if (r.querySelector('.row-checkbox')?.checked) lastChecked = r; });
            if (lastChecked) lastChecked.after(row); else table.appendChild(row);
            rowCounters[sectionId]++;
            reindexSection(sectionId);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Funkcje obsĹ‚ugi danych klienta
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
        }
        
        async function fetchFromGUS() {
            const nip = document.getElementById('customer_nip').value.replace(/[^0-9]/g, '');
            
            if (!nip || nip.length !== 10) {
                alert('Podaj prawidĹ‚owy 10-cyfrowy NIP');
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
                console.error('BĹ‚Ä…d pobierania danych z GUS:', error);
                alert('BĹ‚Ä…d podczas pobierania danych z GUS: ' + error.message);
            }
        }
    </script>

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
        
        const existingDescription = document.getElementById('offer_description').value;
        if (existingDescription) {
            quill.root.innerHTML = existingDescription;
        }
        
        quill.on('text-change', function() {
            document.getElementById('offer_description').value = quill.root.innerHTML;
        });
    });
    
    // Odepnij szansÄ™ od oferty
    function detachDeal() {
        if (!confirm('Czy na pewno odpiÄ…Ä‡ szansÄ™ CRM od tej oferty?')) return;
        const select = document.getElementById('crm-deal-select');
        select.value = '';
        updateDealInfo('');
        // Ukryj przycisk odepniÄ™cia
        const detachBtn = document.querySelector('[onclick="detachDeal()"]');
        if (detachBtn) detachBtn.remove();
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
        document.getElementById('deal-company').textContent = company ? `â€˘ Firma: ${company}` : '';
        document.getElementById('deal-value').textContent = `â€˘ WartoĹ›Ä‡: ${value} ${currency}`;
        
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
<script>
var _formChanged = false;
var _isSaving = false;
function _updateSaveBtn() {
    var btn = document.getElementById('float-save-btn');
    if (!btn) return;
    btn.style.background = _formChanged ? '#dc2626' : '#16a34a';
}
function prepareSaveAndStay() {
    _isSaving = true;
    _formChanged = false;
    _updateSaveBtn();
}
document.addEventListener('input',  function(e) { if (e.target.closest('form')) { _formChanged = true; _updateSaveBtn(); } });
document.addEventListener('change', function(e) { if (e.target.closest('form')) { _formChanged = true; _updateSaveBtn(); } });
function handleBack() {
    if (_formChanged) {
        if (!confirm('Czy na pewno chcesz wyjĹ›Ä‡ bez zapisywania?')) {
            return;
        }
        _formChanged = false;
    }
    window.location.href = '/wyceny';
}
window.addEventListener('beforeunload', function(e) {
    if (_formChanged && !_isSaving) { e.preventDefault(); e.returnValue = ''; }
});
</script>
</html>
