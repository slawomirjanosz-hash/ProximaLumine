<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CRM - System Zarządzania Relacjami</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
    <style>
        .tab-button { @apply px-4 py-2 text-sm font-medium rounded-t-lg transition-colors; }
        .tab-button.active { @apply bg-white text-blue-600 border-b-2 border-blue-600; }
        .tab-button:not(.active) { @apply bg-gray-200 text-gray-600 hover:bg-gray-300; }
        .tab-content { @apply hidden; }
        .tab-content.active { @apply block; }
        .stage-badge { @apply inline-block px-2 py-1 text-xs rounded font-semibold; }
    </style>
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

@if(session('success'))
    <div class="max-w-7xl mx-auto mt-4 bg-green-100 text-green-800 p-3 rounded">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="max-w-7xl mx-auto mt-4 bg-red-100 text-red-800 p-3 rounded">{{ session('error') }}</div>
@endif

<div class="max-w-7xl mx-auto mt-6 mb-12">
    
    <!-- NAGŁÓWEK -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-800">👥 CRM - System Zarządzania Relacjami z Klientami</h1>
            <a href="{{ route('crm.settings') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                ⚙️ Ustawienia CRM
            </a>
        </div>
    </div>

    <!-- ZAKŁADKI -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200 flex gap-1 p-2">
            <button class="tab-button active" onclick="switchTab('dashboard')">📊 Dashboard</button>
            <button class="tab-button" onclick="switchTab('deals')">💼 Lejek Sprzedażowy</button>
            <button class="tab-button" onclick="switchTab('companies')">🏢 Firmy</button>
            <button class="tab-button" onclick="switchTab('activities')">📝 Historia</button>
            <button class="tab-button" onclick="switchTab('reports')">📈 Raporty</button>
        </div>

        <!-- TAB: DASHBOARD -->
        <div id="tab-dashboard" class="tab-content active p-6">
            <h2 class="text-2xl font-bold mb-4">📊 Przegląd</h2>
            
            <!-- LEJEK SPRZEDAŻOWY - PIPELINE -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-3">💼 Lejek Sprzedażowy</h3>
                @if($stats['deals_by_stage']->isEmpty())
                    <div class="p-6 bg-gray-50 rounded-lg text-center text-gray-500">
                        <p class="text-lg mb-2">Brak aktywnych szans sprzedażowych</p>
                        <p class="text-sm">Dodaj pierwszą szansę w zakładce "Lejek Sprzedażowy"</p>
                    </div>
                @else
                    @php
                        $stageMap = collect($crmStages)->keyBy('slug');
                    @endphp
                    <div class="space-y-4">
                        @foreach($crmStages as $stage)
                            @if($stats['deals_by_stage']->has($stage->slug))
                                @php
                                    $stageDeals = $stats['deals_by_stage'][$stage->slug];
                                @endphp
                                <div class="border rounded-lg p-4" style="background-color: {{ $stage->color }}20; border-color: {{ $stage->color }};">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 text-center min-w-[120px]">
                                            <div class="text-sm font-semibold text-gray-700">{{ $stage->name }}</div>
                                            <div class="text-3xl font-bold text-gray-800 my-1">{{ $stageDeals->count() }}</div>
                                            <div class="text-xs text-gray-600">{{ number_format($stageDeals->sum('value'), 0, ',', ' ') }} zł</div>
                                        </div>
                                        <div class="flex-1 flex flex-wrap gap-3 justify-center">
                                            @foreach($stageDeals as $deal)
                                                <div onclick="editDeal({{ $deal->id }})" class="flex-shrink-0 bg-white border border-gray-300 rounded p-3 shadow-sm min-w-[200px] max-w-[250px] cursor-pointer hover:shadow-md transition">
                                                    <div class="font-semibold text-sm mb-1 truncate" title="{{ $deal->name }}">{{ $deal->name }}</div>
                                                    <div class="text-xs text-gray-600 mb-1 truncate" title="{{ $deal->company->name ?? 'Brak firmy' }}">🏢 {{ $deal->company->name ?? 'Brak firmy' }}</div>
                                                    <div class="text-xs text-gray-500">📅 {{ $deal->expected_close_date ? $deal->expected_close_date->format('d.m.Y') : 'Brak daty' }}</div>
                                                    <div class="text-xs font-bold text-green-600 mt-1">{{ number_format($deal->value, 0, ',', ' ') }} {{ $deal->currency }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- OSTATNIE WYGRANE/PRZEGRANE -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-3">🎯 Ostatnio Wygrane/Przegrane</h3>
                @if($stats['recent_won_deals']->isEmpty())
                    <div class="p-4 bg-gray-50 rounded text-center text-gray-500">
                        <p>Brak zakończonych szans</p>
                    </div>
                @else
                    @php
                        $stageMap = collect($crmStages)->keyBy('slug');
                    @endphp
                    <div class="space-y-2">
                        @foreach($stats['recent_won_deals']->take(3) as $deal)
                            @php $stage = $stageMap[$deal->stage] ?? null; @endphp
                            <div onclick="editDeal({{ $deal->id }})" class="flex justify-between items-center p-3 rounded border cursor-pointer hover:shadow-md transition" style="background-color: {{ $stage?->color ?? '#f3f4f6' }}20; border-color: {{ $stage?->color ?? '#e5e7eb' }};">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">{{ $deal->name }}</span>
                                        @if($stage)
                                            <span class="text-xs px-2 py-0.5 rounded" style="background-color: {{ $stage->color }}20; color: {{ $stage->color }}; border: 1px solid {{ $stage->color }};">
                                                {{ $stage->name }}
                                            </span>
                                        @else
                                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-800">{{ $deal->stage }}</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600">{{ $deal->company->name ?? 'Brak firmy' }} • {{ $deal->actual_close_date->format('d.m.Y') }}</div>
                                </div>
                                <div class="text-lg font-bold" style="color: {{ $stage?->color ?? '#222' }};">{{ number_format($deal->value, 0, ',', ' ') }} zł</div>
                            </div>
                        @endforeach
                        @if($stats['recent_won_deals']->count() > 3)
                            <details class="mt-2">
                                <summary class="cursor-pointer text-blue-600 hover:text-blue-800 font-medium p-3 bg-gray-50 rounded border border-gray-200">
                                    Pokaż starsze ({{ $stats['recent_won_deals']->count() - 3 }})
                                </summary>
                                <div class="space-y-2 mt-2">
                                    @foreach($stats['recent_won_deals']->slice(3) as $deal)
                                        @php $stage = $stageMap[$deal->stage] ?? null; @endphp
                                        <div onclick="editDeal({{ $deal->id }})" class="flex justify-between items-center p-3 rounded border cursor-pointer hover:shadow-md transition" style="background-color: {{ $stage?->color ?? '#f3f4f6' }}20; border-color: {{ $stage?->color ?? '#e5e7eb' }};">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold">{{ $deal->name }}</span>
                                                    @if($stage)
                                                        <span class="text-xs px-2 py-0.5 rounded" style="background-color: {{ $stage->color }}20; color: {{ $stage->color }}; border: 1px solid {{ $stage->color }};">
                                                            {{ $stage->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-800">{{ $deal->stage }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-600">{{ $deal->company->name ?? 'Brak firmy' }} • {{ $deal->actual_close_date->format('d.m.Y') }}</div>
                                            </div>
                                            <div class="text-lg font-bold" style="color: {{ $stage?->color ?? '#222' }};">{{ number_format($deal->value, 0, ',', ' ') }} zł</div>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                @endif
            </div>

            <!-- ZADANIA I PRZYPOMNIENIA -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">✅ Zadania i Przypomnienia</h3>
                    <button onclick="showTaskModal()" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">➕ Dodaj Zadanie</button>
                </div>
                
                <table class="w-full border-collapse text-xs">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">Zadanie</th>
                            <th class="border p-2 text-left">Typ</th>
                            <th class="border p-2 text-left">Priorytet</th>
                            <th class="border p-2 text-left">Status</th>
                            <th class="border p-2 text-left">Termin</th>
                            <th class="border p-2 text-left">Przypisane do</th>
                            <th class="border p-2">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr class="hover:bg-gray-50 {{ $task->isOverdue() ? 'bg-red-50' : '' }}">
                                <td class="border p-2">
                                    <div class="font-semibold">{{ $task->title }}</div>
                                    <div class="text-sm text-gray-600">{{ $task->company->name ?? '' }} {{ $task->deal ? '• ' . $task->deal->name : '' }}</div>
                                </td>
                                <td class="border p-2">{{ ucfirst(str_replace('_', ' ', $task->type)) }}</td>
                                <td class="border p-2">
                                    <span class="stage-badge 
                                        {{ $task->priority === 'pilna' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $task->priority === 'wysoka' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $task->priority === 'normalna' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $task->priority === 'niska' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td class="border p-2">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                                <td class="border p-2 {{ $task->isOverdue() ? 'text-red-600 font-bold' : '' }}">
                                    {{ $task->due_date ? $task->due_date->format('d.m.Y H:i') : '-' }}
                                </td>
                                <td class="border p-2">{{ $task->assignedTo->name ?? 'Nie przypisane' }}</td>
                                <td class="border p-2 text-center">
                                    <button onclick="editTask({{ $task->id }})" class="text-blue-600 hover:underline">✏️</button>
                                    <form action="{{ route('crm.task.delete', $task->id) }}" method="POST" class="inline" onsubmit="return confirm('Usunąć zadanie?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="border p-4 text-center text-gray-500">Brak aktywnych zadań</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: LEJEK SPRZEDAŻOWY -->
        <div id="tab-deals" class="tab-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">💼 Lejek Sprzedażowy - Szanse</h2>
                <button onclick="showDealModal()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">➕ Dodaj Szansę</button>
            </div>
            
            <table class="w-full border-collapse text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left min-w-0 max-w-xs truncate">Nazwa</th>
                        <th class="border p-2 text-left min-w-0 max-w-xs truncate">Firma</th>
                        <th class="border p-2 text-right whitespace-nowrap w-auto">Wartość</th>
                        <th class="border p-2 text-center whitespace-nowrap w-auto">Etap</th>
                        <th class="border p-2 text-center whitespace-nowrap w-auto">Szansa %</th>
                        <th class="border p-2 text-left whitespace-nowrap w-auto" title="Oczekiwanie zamknięcia">Oczek. Zam</th>
                        <th class="border p-2 text-left whitespace-nowrap w-auto">Opiekun</th>
                        <th class="border p-2 text-left whitespace-nowrap w-auto">Przypisani</th>
                        <th class="border p-2 text-center whitespace-nowrap w-auto">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deals as $deal)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 font-semibold min-w-0 max-w-xs truncate">{{ $deal->name }}</td>
                            <td class="border p-2 min-w-0 max-w-xs truncate">{{ $deal->company->name ?? '-' }}</td>
                            <td class="border p-2 text-right font-bold whitespace-nowrap w-auto">{{ number_format($deal->value, 2, ',', ' ') }} {{ $deal->currency }}</td>
                            @php
                                $stage = $stageMap[$deal->stage] ?? null;
                            @endphp
                            @if($stage)
                                <td class="border p-2 text-center whitespace-nowrap w-auto" style="background-color: {{ $stage->color }}20; border-color: #000;">
                                    <span class="stage-badge" style="background: transparent; color: #222; border: none;">{{ $stage->name }}</span>
                                </td>
                            @else
                                <td class="border p-2 text-center whitespace-nowrap w-auto">
                                    <span class="stage-badge bg-gray-100 text-gray-800">{{ $deal->stage }}</span>
                                </td>
                            @endif
                            <td class="border p-2 text-center whitespace-nowrap w-auto">{{ $deal->probability }}%</td>
                            <td class="border p-2 whitespace-nowrap w-auto" title="Oczekiwanie zamknięcia">{{ $deal->expected_close_date ? $deal->expected_close_date->format('d.m.Y') : '-' }}</td>
                            <td class="border p-2 whitespace-nowrap w-auto">{{ $deal->owner->short_name ?? ($deal->owner->name ?? '-') }}</td>
                            <td class="border p-2 w-auto">
                                @if($deal->assignedUsers->count() > 0)
                                    <div class="text-xs flex flex-col gap-1">
                                        @foreach($deal->assignedUsers as $user)
                                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded">{{ $user->short_name ?? $user->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="border p-2 text-center whitespace-nowrap w-auto">
                                <button onclick="editDeal({{ $deal->id }})" class="text-blue-600 hover:underline">✏️</button>
                                <form action="{{ route('crm.deal.delete', $deal->id) }}" method="POST" class="inline" onsubmit="return confirm('Usunąć szansę?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="border p-4 text-center text-gray-500">Brak szans sprzedażowych</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TAB: FIRMY -->
        <div id="tab-companies" class="tab-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">🏢 Firmy / Kontakty</h2>
                <button onclick="showCompanyModal()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">➕ Dodaj Firmę</button>
            </div>
            
            <table class="w-full border-collapse text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Nazwa</th>
                        <th class="border p-2 text-left" title="Skrót z Magazyn → Ustawienia → Dostawcy i klienci">Skrót*</th>
                        <th class="border p-2 text-left">NIP</th>
                        <th class="border p-2 text-left">Kontakt</th>
                        <th class="border p-2 text-left">Typ</th>
                        <th class="border p-2 text-left">Status</th>
                        <th class="border p-2 text-left">Opiekun</th>
                        <th class="border p-2 text-left">Dodana przez</th>
                        <th class="border p-2 text-center">W bazie dostawców</th>
                        <th class="border p-2">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 font-semibold">{{ $company->name }}</td>
                            <td class="border p-2">{{ $company->supplier->short_name ?? '-' }}</td>
                            <td class="border p-2">{{ $company->nip ?? '-' }}</td>
                            <td class="border p-2">
                                <div class="text-sm">{{ $company->email ?? '-' }}</div>
                                <div class="text-sm">{{ $company->phone ?? '-' }}</div>
                            </td>
                            @php
                                $companyType = $customerTypes->firstWhere('slug', $company->type);
                            @endphp
                            @if($companyType)
                                <td class="border p-2 text-center" style="background-color: {{ $companyType->color }}20; border-color: #000;">
                                    <span class="font-semibold" style="color: #222;">{{ $companyType->name }}</span>
                                </td>
                            @else
                                <td class="border p-2 text-center">
                                    <span class="stage-badge bg-gray-100 text-gray-800">{{ ucfirst($company->type) }}</span>
                                </td>
                            @endif
                            <td class="border p-2">{{ ucfirst($company->status) }}</td>
                            <td class="border p-2">{{ $company->owner->name ?? '-' }}</td>
                            <td class="border p-2">
                                <span class="text-sm text-gray-600">{{ $company->addedBy->name ?? '-' }}</span>
                            </td>
                            <td class="border p-2 text-center">
                                @if($company->supplier_id)
                                    <span class="text-green-600 font-bold" title="Firma jest w bazie dostawców/klientów">✓ W bazie</span>
                                @else
                                    <form action="{{ route('crm.company.addToSuppliers', $company->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:underline text-sm" title="Dodaj do bazy dostawców/klientów">➕ Dodaj do bazy</button>
                                    </form>
                                @endif
                            </td>
                            <td class="border p-2 text-center">
                                <button onclick="editCompany({{ $company->id }})" class="text-blue-600 hover:underline">✏️</button>
                                <form action="{{ route('crm.company.delete', $company->id) }}" method="POST" class="inline" onsubmit="return confirm('Usunąć firmę?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="border p-4 text-center text-gray-500">Brak firm w bazie</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TAB: HISTORIA -->
        <div id="tab-activities" class="tab-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">📝 Historia Kontaktów z Klientami</h2>
                <button onclick="showActivityModal()" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">➕ Dodaj Aktywność</button>
            </div>
            
            <div class="space-y-3">
                @forelse($activities as $activity)
                    <div class="border rounded p-4 bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="stage-badge bg-blue-100 text-blue-800">{{ ucfirst($activity->type) }}</span>
                                    @if($activity->outcome)
                                        <span class="stage-badge 
                                            {{ $activity->outcome === 'pozytywny' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $activity->outcome === 'neutralny' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $activity->outcome === 'negatywny' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $activity->outcome === 'brak_odpowiedzi' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $activity->outcome)) }}
                                        </span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-lg">{{ $activity->subject }}</h4>
                                <p class="text-gray-700 mt-1">{{ $activity->description }}</p>
                                <div class="text-sm text-gray-600 mt-2">
                                    {{ $activity->company->name ?? 'Brak firmy' }} 
                                    {{ $activity->deal ? '• ' . $activity->deal->name : '' }}
                                    • {{ $activity->user->name }}
                                    • {{ $activity->activity_date->format('d.m.Y H:i') }}
                                    @if($activity->duration)
                                        • Czas trwania: {{ $activity->duration }} min
                                    @endif
                                </div>
                            </div>
                            <div>
                                <button onclick="editActivity({{ $activity->id }})" class="text-blue-600 hover:underline">✏️</button>
                                <form action="{{ route('crm.activity.delete', $activity->id) }}" method="POST" class="inline" onsubmit="return confirm('Usunąć aktywność?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline ml-2">🗑️</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">Brak aktywności</p>
                @endforelse
            </div>
        </div>

        <!-- TAB: RAPORTY -->
        <div id="tab-reports" class="tab-content p-6">
            <h2 class="text-2xl font-bold mb-4">📈 Raporty i Analityka</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Konwersja leadów -->
                <div class="border rounded p-4">
                    <h3 class="font-bold text-lg mb-3">Konwersja Leadów</h3>
                    <div class="space-y-2">
                        @php
                            $totalDeals = $deals->count();
                            $wonDeals = $deals->where('stage', 'wygrana')->count();
                            $lostDeals = $deals->where('stage', 'przegrana')->count();
                            $conversionRate = $totalDeals > 0 ? round(($wonDeals / $totalDeals) * 100, 1) : 0;
                        @endphp
                        <div>Wszystkich szans: <strong>{{ $totalDeals }}</strong></div>
                        <div>Wygranych: <strong class="text-green-600">{{ $wonDeals }}</strong></div>
                        <div>Przegranych: <strong class="text-red-600">{{ $lostDeals }}</strong></div>
                        <div>Wskaźnik konwersji: <strong class="text-blue-600">{{ $conversionRate }}%</strong></div>
                    </div>
                </div>

                <!-- Wartość według właściciela -->
                <div class="border rounded p-4">
                    <h3 class="font-bold text-lg mb-3">Skuteczność Handlowców</h3>
                    <div class="space-y-2">
                        @php
                            $dealsByOwner = $deals->groupBy('owner_id');
                        @endphp
                        @foreach($dealsByOwner as $ownerId => $ownerDeals)
                            @php
                                $owner = $users->firstWhere('id', $ownerId);
                                $totalValue = $ownerDeals->sum('value');
                                $wonValue = $ownerDeals->where('stage', 'wygrana')->sum('value');
                            @endphp
                            <div class="flex justify-between">
                                <span>{{ $owner->name ?? 'Brak opiekuna' }}:</span>
                                <span><strong>{{ number_format($totalValue, 0, ',', ' ') }} zł</strong> (wygrane: {{ number_format($wonValue, 0, ',', ' ') }} zł)</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Źródła klientów -->
                <div class="border rounded p-4">
                    <h3 class="font-bold text-lg mb-3">Źródła Klientów</h3>
                    <div class="space-y-2">
                        @php
                            $sources = $companies->whereNotNull('source')->groupBy('source');
                        @endphp
                        @forelse($sources as $source => $sourceCompanies)
                            <div class="flex justify-between">
                                <span>{{ $source }}:</span>
                                <strong>{{ $sourceCompanies->count() }}</strong>
                            </div>
                        @empty
                            <p class="text-gray-500">Brak danych o źródłach</p>
                        @endforelse
                    </div>
                </div>

                <!-- Typy firm -->
                <div class="border rounded p-4">
                    <h3 class="font-bold text-lg mb-3">Typy Firm</h3>
                    <div class="space-y-2">
                        @php
                            $types = $companies->groupBy('type');
                        @endphp
                        @foreach($types as $type => $typeCompanies)
                            <div class="flex justify-between">
                                <span>{{ ucfirst($type) }}:</span>
                                <strong>{{ $typeCompanies->count() }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STATYSTYKI NA DOLE STRONY -->
    <div class="bg-white p-6 rounded-lg shadow mt-6 mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">📊 Statystyki</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 rounded-lg shadow">
                <div class="text-4xl font-bold">{{ $stats['total_companies'] }}</div>
                <div class="text-sm opacity-90">Firm w bazie</div>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-4 rounded-lg shadow">
                <div class="text-4xl font-bold">{{ $stats['active_deals'] }}</div>
                <div class="text-sm opacity-90">Aktywnych szans</div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-4 rounded-lg shadow">
                <div class="text-4xl font-bold text-white">{{ number_format($stats['total_pipeline_value'], 0, ',', ' ') }} zł</div>
                <div class="text-sm opacity-90">Wartość pipeline</div>
            </div>
            <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-4 rounded-lg shadow">
                <div class="text-4xl font-bold">{{ $stats['overdue_tasks'] }}</div>
                <div class="text-sm opacity-90">Zadań po terminie</div>
            </div>
        </div>
    </div>
</div>

<!-- Czerwony krzyżyk pod przyciskami -->
<div class="flex justify-center mt-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
    </svg>
</div>

<!-- MODALS -->
<div id="modal-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" onclick="closeModal()">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div id="modal-content"></div>
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

function showCompanyModal() {
    document.getElementById('modal-content').innerHTML = `
        <h3 class="text-xl font-bold mb-4">Dodaj Firmę</h3>
        
        <!-- Sekcja wyszukiwania po NIP -->
        <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
            <h4 class="font-semibold mb-2">🔍 Wyszukaj firmę po NIP</h4>
            <div class="flex gap-2">
                <input type="text" id="search-nip" placeholder="Wpisz NIP (z lub bez myślników)" class="flex-1 border rounded px-3 py-2">
                <button type="button" onclick="searchByNip()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Wyszukaj</button>
            </div>
            <div id="search-result" class="mt-2 text-sm"></div>
        </div>

        <form method="POST" action="{{ route('crm.company.add') }}" id="company-form">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 bg-green-50 border border-green-200 rounded p-3">
                    <label class="block mb-1 font-semibold">🔗 Wybierz istniejącą firmę z bazy dostawców/klientów</label>
                    <select id="supplier-select" onchange="fillCompanyFromSupplier()" class="w-full border rounded px-3 py-2">
                        <option value="">-- Wybierz firmę z bazy lub wypełnij ręcznie --</option>
                        @foreach($availableSuppliers as $supplier)
                            <option value="{{ $supplier->id }}" 
                                data-name="{{ $supplier->name }}" 
                                data-short="{{ $supplier->short_name }}"
                                data-nip="{{ $supplier->nip ?? '' }}"
                                data-email="{{ $supplier->email ?? '' }}"
                                data-phone="{{ $supplier->phone ?? '' }}"
                                data-address="{{ $supplier->address ?? '' }}"
                                data-city="{{ $supplier->city ?? '' }}"
                                data-postal="{{ $supplier->postal_code ?? '' }}">
                                {{ $supplier->short_name }} - {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="supplier_id" id="supplier-id">
                </div>
                <div><label class="block mb-1 font-semibold">Nazwa *</label><input type="text" name="name" id="company-name" required class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">NIP</label><input type="text" name="nip" id="company-nip" class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Email</label><input type="email" name="email" id="company-email" class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Telefon</label><input type="text" name="phone" id="company-phone" class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Miasto</label><input type="text" name="city" id="company-city" class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Kod pocztowy</label><input type="text" name="postal_code" id="company-postal" class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Typ *</label>
                    <select name="type" id="company-type" required class="w-full border rounded px-3 py-2">
                        <option value="potencjalny">Potencjalny</option>
                        <option value="klient">Klient</option>
                        <option value="partner">Partner</option>
                        <option value="konkurencja">Konkurencja</option>
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Status *</label>
                    <select name="status" id="company-status" required class="w-full border rounded px-3 py-2">
                        <option value="aktywny">Aktywny</option>
                        <option value="nieaktywny">Nieaktywny</option>
                        <option value="zawieszony">Zawieszony</option>
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Opiekun</label>
                    <select name="owner_id" id="company-owner" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Źródło</label><input type="text" name="source" id="company-source" placeholder="np. strona www, polecenie" class="w-full border rounded px-3 py-2"></div>
                <div class="col-span-2"><label class="block mb-1 font-semibold">Adres</label><textarea name="address" id="company-address" rows="2" class="w-full border rounded px-3 py-2"></textarea></div>
                <div class="col-span-2"><label class="block mb-1 font-semibold">Notatki</label><textarea name="notes" id="company-notes" rows="3" class="w-full border rounded px-3 py-2"></textarea></div>
            </div>
            <div class="mt-4 flex gap-2 justify-end">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz</button>
            </div>
        </form>
    `;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

function showDealModal() {
    document.getElementById('modal-content').innerHTML = `
        <h3 class="text-xl font-bold mb-4">Dodaj Szansę Sprzedażową</h3>
        <form method="POST" action="{{ route('crm.deal.add') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2"><label class="block mb-1 font-semibold">Nazwa *</label><input type="text" name="name" required class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Firma</label>
                    <select name="company_id" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Wartość (PLN) *</label><input type="number" step="0.01" name="value" required class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Etap *</label>
                    <select name="stage" required class="w-full border rounded px-3 py-2">
                        @foreach($crmStages->where('is_active', 1)->sortBy('order') as $stage)
                            <option value="{{ $stage->slug }}" @if(isset($deal) && $deal->stage === $stage->slug) selected @endif>{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Prawdopodobieństwo (%) *</label><input type="number" min="0" max="100" name="probability" value="10" required class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Oczekiwane zamknięcie</label><input type="date" name="expected_close_date" class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Opiekun</label>
                    <select name="owner_id" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block mb-1 font-semibold">Przypisani użytkownicy (szansa będzie widoczna dla nich)</label>
                    <div class="border rounded px-3 py-2 max-h-32 overflow-y-auto">
                        @foreach($users as $user)
                            <label class="flex items-center py-1 hover:bg-gray-50">
                                <input type="checkbox" name="assigned_users[]" value="{{ $user->id }}" class="mr-2">
                                {{ $user->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="col-span-2"><label class="block mb-1 font-semibold">Opis</label><textarea name="description" rows="3" class="w-full border rounded px-3 py-2"></textarea></div>
            </div>
            <div class="mt-4 flex gap-2 justify-end">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Zapisz</button>
            </div>
        </form>
    `;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

function showTaskModal() {
    document.getElementById('modal-content').innerHTML = `
        <h3 class="text-xl font-bold mb-4">Dodaj Zadanie</h3>
        <form method="POST" action="{{ route('crm.task.add') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2"><label class="block mb-1 font-semibold">Tytuł *</label><input type="text" name="title" required class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Typ *</label>
                    <select name="type" required class="w-full border rounded px-3 py-2">
                        <option value="zadanie">Zadanie</option>
                        <option value="telefon">Telefon</option>
                        <option value="email">Email</option>
                        <option value="spotkanie">Spotkanie</option>
                        <option value="follow_up">Follow-up</option>
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Priorytet *</label>
                    <select name="priority" required class="w-full border rounded px-3 py-2">
                        <option value="normalna">Normalna</option>
                        <option value="niska">Niska</option>
                        <option value="wysoka">Wysoka</option>
                        <option value="pilna">Pilna</option>
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Status *</label>
                    <select name="status" required class="w-full border rounded px-3 py-2">
                        <option value="do_zrobienia">Do zrobienia</option>
                        <option value="w_trakcie">W trakcie</option>
                        <option value="zakonczone">Zakończone</option>
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Termin</label><input type="datetime-local" name="due_date" class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Przypisz do</label>
                    <select name="assigned_to" class="w-full border rounded px-3 py-2">
                        <option value="">Nie przypisane</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Firma</label>
                    <select name="company_id" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Szansa</label>
                    <select name="deal_id" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        @foreach($deals as $deal)
                            <option value="{{ $deal->id }}">{{ $deal->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2"><label class="block mb-1 font-semibold">Opis</label><textarea name="description" rows="3" class="w-full border rounded px-3 py-2"></textarea></div>
            </div>
            <div class="mt-4 flex gap-2 justify-end">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Zapisz</button>
            </div>
        </form>
    `;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

function showActivityModal() {
    document.getElementById('modal-content').innerHTML = `
        <h3 class="text-xl font-bold mb-4">Dodaj Aktywność</h3>
        <form method="POST" action="{{ route('crm.activity.add') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block mb-1 font-semibold">Typ *</label>
                    <select name="type" required class="w-full border rounded px-3 py-2">
                        <option value="telefon">Telefon</option>
                        <option value="email">Email</option>
                        <option value="spotkanie">Spotkanie</option>
                        <option value="notatka">Notatka</option>
                        <option value="sms">SMS</option>
                        <option value="oferta">Oferta</option>
                        <option value="umowa">Umowa</option>
                        <option value="faktura">Faktura</option>
                        <option value="reklamacja">Reklamacja</option>
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Data aktywności *</label><input type="datetime-local" name="activity_date" required value="{{ now()->format('Y-m-d\\TH:i') }}" class="w-full border rounded px-3 py-2"></div>
                <div class="col-span-2"><label class="block mb-1 font-semibold">Temat *</label><input type="text" name="subject" required class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Czas trwania (min)</label><input type="number" name="duration" class="w-full border rounded px-3 py-2"></div>
                <div><label class="block mb-1 font-semibold">Wynik</label>
                    <select name="outcome" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        <option value="pozytywny">Pozytywny</option>
                        <option value="neutralny">Neutralny</option>
                        <option value="negatywny">Negatywny</option>
                        <option value="brak_odpowiedzi">Brak odpowiedzi</option>
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Firma</label>
                    <select name="company_id" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block mb-1 font-semibold">Szansa</label>
                    <select name="deal_id" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        @foreach($deals as $deal)
                            <option value="{{ $deal->id }}">{{ $deal->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2"><label class="block mb-1 font-semibold">Opis</label><textarea name="description" rows="4" class="w-full border rounded px-3 py-2"></textarea></div>
            </div>
            <div class="mt-4 flex gap-2 justify-end">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Zapisz</button>
            </div>
        </form>
    `;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-overlay').classList.add('hidden');
}

async function searchByNip() {
    const nipInput = document.getElementById('search-nip');
    const resultDiv = document.getElementById('search-result');
    const nip = nipInput.value.trim();
    
    if (!nip) {
        resultDiv.innerHTML = '<span class="text-red-600">Proszę wpisać NIP</span>';
        return;
    }
    
    resultDiv.innerHTML = '<span class="text-blue-600">Wyszukiwanie...</span>';
    
    try {
        const response = await fetch(`{{ route('crm.company.searchByNip') }}?nip=${encodeURIComponent(nip)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            // Znaleziono firmę
            let sourceInfo = '';
            if (result.source === 'local') {
                sourceInfo = '(z Twojej bazy CRM)';
            } else if (result.source === 'ceidg') {
                sourceInfo = '(z CEIDG)';
            } else if (result.source === 'vat') {
                sourceInfo = '(z białej listy VAT)';
            }
            
            resultDiv.innerHTML = '<span class="text-green-600">✓ Znaleziono firmę: <strong>' + result.data.name + '</strong> ' + sourceInfo + '</span>';
            
            // Wypełnij formularz danymi
            document.getElementById('company-name').value = result.data.name || '';
            document.getElementById('company-nip').value = result.data.nip || '';
            document.getElementById('company-email').value = result.data.email || '';
            document.getElementById('company-phone').value = result.data.phone || '';
            document.getElementById('company-city').value = result.data.city || '';
            document.getElementById('company-postal').value = result.data.postal_code || '';
            document.getElementById('company-address').value = result.data.address || '';
        } else {
            // Nie znaleziono w żadnej bazie
            resultDiv.innerHTML = '<span class="text-yellow-600">⚠ ' + result.message + '. Wypełnij dane ręcznie.</span>';
        }
    } catch (error) {
        console.error('Error:', error);
        resultDiv.innerHTML = '<span class="text-red-600">Błąd połączenia. Sprawdź konsolę przeglądarki.</span>';
    }
}

function fillCompanyFromSupplier() {
    const select = document.getElementById('supplier-select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        // Fill form with supplier data
        document.getElementById('company-name').value = selectedOption.getAttribute('data-name') || '';
        document.getElementById('company-nip').value = selectedOption.getAttribute('data-nip') || '';
        document.getElementById('company-email').value = selectedOption.getAttribute('data-email') || '';
        document.getElementById('company-phone').value = selectedOption.getAttribute('data-phone') || '';
        document.getElementById('company-address').value = selectedOption.getAttribute('data-address') || '';
        document.getElementById('company-city').value = selectedOption.getAttribute('data-city') || '';
        document.getElementById('company-postal').value = selectedOption.getAttribute('data-postal') || '';
        document.getElementById('supplier-id').value = selectedOption.value;
    } else {
        // Clear form
        document.getElementById('company-name').value = '';
        document.getElementById('company-nip').value = '';
        document.getElementById('company-email').value = '';
        document.getElementById('company-phone').value = '';
        document.getElementById('company-address').value = '';
        document.getElementById('company-city').value = '';
        document.getElementById('company-postal').value = '';
        document.getElementById('supplier-id').value = '';
    }
}

function editCompany(id) { 
    // Pobierz dane firmy
    fetch(`/crm/company/${id}/edit`)
        .then(response => response.json())
        .then(company => {
            document.getElementById('modal-content').innerHTML = `
                <h3 class="text-xl font-bold mb-4">Edytuj Firmę</h3>
                <form method="POST" action="/crm/company/${id}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block mb-1 font-semibold">Nazwa *</label><input type="text" name="name" value="${company.name || ''}" required class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">NIP</label><input type="text" name="nip" value="${company.nip || ''}" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Email</label><input type="email" name="email" value="${company.email || ''}" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Telefon</label><input type="text" name="phone" value="${company.phone || ''}" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Miasto</label><input type="text" name="city" value="${company.city || ''}" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Kod pocztowy</label><input type="text" name="postal_code" value="${company.postal_code || ''}" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Typ *</label>
                            <select name="type" required class="w-full border rounded px-3 py-2">
                                <option value="potencjalny" ${company.type === 'potencjalny' ? 'selected' : ''}>Potencjalny</option>
                                <option value="klient" ${company.type === 'klient' ? 'selected' : ''}>Klient</option>
                                <option value="partner" ${company.type === 'partner' ? 'selected' : ''}>Partner</option>
                                <option value="konkurencja" ${company.type === 'konkurencja' ? 'selected' : ''}>Konkurencja</option>
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Status *</label>
                            <select name="status" required class="w-full border rounded px-3 py-2">
                                <option value="aktywny" ${company.status === 'aktywny' ? 'selected' : ''}>Aktywny</option>
                                <option value="nieaktywny" ${company.status === 'nieaktywny' ? 'selected' : ''}>Nieaktywny</option>
                                <option value="zawieszony" ${company.status === 'zawieszony' ? 'selected' : ''}>Zawieszony</option>
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Opiekun</label>
                            <select name="owner_id" class="w-full border rounded px-3 py-2">
                                <option value="">Brak</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" ${company.owner_id == {{ $user->id }} ? 'selected' : ''}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Źródło</label><input type="text" name="source" value="${company.source || ''}" class="w-full border rounded px-3 py-2"></div>
                        <div class="col-span-2"><label class="block mb-1 font-semibold">Adres</label><textarea name="address" rows="2" class="w-full border rounded px-3 py-2">${company.address || ''}</textarea></div>
                        <div class="col-span-2"><label class="block mb-1 font-semibold">Notatki</label><textarea name="notes" rows="3" class="w-full border rounded px-3 py-2">${company.notes || ''}</textarea></div>
                    </div>
                    <div class="mt-4 flex gap-2 justify-end">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz</button>
                    </div>
                </form>
            `;
            document.getElementById('modal-overlay').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Błąd podczas ładowania danych firmy');
        });
}

function editDeal(id) { 
    fetch(`/crm/deal/${id}/edit`)
        .then(response => response.json())
        .then(deal => {
            const assignedUserIds = deal.assigned_users ? deal.assigned_users.map(u => u.id) : [];
            const hasOffers = deal.offers && deal.offers.length > 0;
            
            // Generuj listę ofert jeśli istnieją
            let offersHtml = '';
            if (hasOffers) {
                offersHtml = `
                    <div class="col-span-2 mt-2 mb-2">
                        <label class="block mb-2 font-semibold text-gray-700">📄 Powiązane oferty</label>
                        <div class="border rounded bg-gray-50 p-3 space-y-2">
                            ${deal.offers.map(offer => `
                                <div class="flex items-center justify-between bg-white p-3 rounded border border-gray-200 hover:border-blue-400 transition">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-800">${offer.offer_number} - ${offer.offer_title}</div>
                                        <div class="text-sm text-gray-600">
                                            Data: ${offer.offer_date} • Wartość: ${parseFloat(offer.total_price).toLocaleString('pl-PL', {minimumFractionDigits: 2, maximumFractionDigits: 2})} zł
                                        </div>
                                    </div>
                                    <a href="/wyceny/${offer.id}/edit" class="ml-3 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        ✏️ Edytuj
                                    </a>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('modal-content').innerHTML = `
                <h3 class="text-xl font-bold mb-4">Edytuj Szansę Sprzedażową</h3>
                <form method="POST" action="/crm/deal/${id}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2"><label class="block mb-1 font-semibold">Nazwa *</label><input type="text" name="name" value="${deal.name || ''}" required class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Firma</label>
                            <select name="company_id" class="w-full border rounded px-3 py-2">
                                <option value="">Brak</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" ${deal.company_id == {{ $company->id }} ? 'selected' : ''}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Wartość (zł) *</label><input type="number" step="0.01" name="value" value="${deal.value || 0}" required class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Prawdopodobieństwo (%) *</label><input type="number" min="0" max="100" name="probability" value="${deal.probability || 50}" required class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Etap *</label>
                            <select name="stage" required class="w-full border rounded px-3 py-2">
                                @foreach($crmStages->where('is_active', 1)->sortBy('order') as $stage)
                                    <option value="{{ $stage->slug }}" ${deal.stage === '{{ $stage->slug }}' ? 'selected' : ''}>{{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Przewidywane zamknięcie</label><input type="date" name="expected_close_date" value="${deal.expected_close_date ? deal.expected_close_date.split('T')[0] : ''}" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Rzeczywiste zamknięcie</label><input type="date" name="actual_close_date" value="${deal.actual_close_date ? deal.actual_close_date.split('T')[0] : ''}" class="w-full border rounded px-3 py-2"></div>
                        ${offersHtml}
                        <div class="col-span-2">
                            <label class="block mb-1 font-semibold">Przypisani użytkownicy (szansa będzie widoczna dla nich)</label>
                            <div class="border rounded px-3 py-2 max-h-32 overflow-y-auto">
                                @foreach($users as $user)
                                    <label class="flex items-center py-1 hover:bg-gray-50">
                                        <input type="checkbox" name="assigned_users[]" value="{{ $user->id }}" ${assignedUserIds.includes({{ $user->id }}) ? 'checked' : ''} class="mr-2">
                                        {{ $user->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-2"><label class="block mb-1 font-semibold">Opis</label><textarea name="description" rows="3" class="w-full border rounded px-3 py-2">${deal.description || ''}</textarea></div>
                    </div>
                    <div class="mt-4 flex gap-2 justify-end">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                        ${deal.stage === 'wycena' && !hasOffers ? '<button type="button" onclick="createOfferFromDeal(' + id + ')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">📄 Stwórz ofertę</button>' : ''}
                        ${deal.stage === 'wycena' && hasOffers ? '<button type="button" onclick="createOfferFromDeal(' + id + ')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">📄 Dodaj kolejną ofertę</button>' : ''}
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Zapisz</button>
                    </div>
                </form>
            `;
            document.getElementById('modal-overlay').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Błąd podczas ładowania danych szansy');
        });
}

function editTask(id) { 
    fetch(`/crm/task/${id}/edit`)
        .then(response => response.json())
        .then(task => {
            // Konwersja daty do formatu datetime-local (YYYY-MM-DDTHH:MM)
            let formattedDueDate = '';
            if (task.due_date) {
                const date = new Date(task.due_date);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                formattedDueDate = `${year}-${month}-${day}T${hours}:${minutes}`;
            }
            
            document.getElementById('modal-content').innerHTML = `
                <h3 class="text-xl font-bold mb-4">Edytuj Zadanie</h3>
                <form method="POST" action="/crm/task/${id}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2"><label class="block mb-1 font-semibold">Tytuł *</label><input type="text" name="title" value="${task.title || ''}" required class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Typ *</label>
                            <select name="type" required class="w-full border rounded px-3 py-2">
                                <option value="zadanie" ${task.type === 'zadanie' ? 'selected' : ''}>Zadanie</option>
                                <option value="telefon" ${task.type === 'telefon' ? 'selected' : ''}>Telefon</option>
                                <option value="email" ${task.type === 'email' ? 'selected' : ''}>Email</option>
                                <option value="spotkanie" ${task.type === 'spotkanie' ? 'selected' : ''}>Spotkanie</option>
                                <option value="follow_up" ${task.type === 'follow_up' ? 'selected' : ''}>Follow-up</option>
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Priorytet *</label>
                            <select name="priority" required class="w-full border rounded px-3 py-2">
                                <option value="niska" ${task.priority === 'niska' ? 'selected' : ''}>Niska</option>
                                <option value="normalna" ${task.priority === 'normalna' ? 'selected' : ''}>Normalna</option>
                                <option value="wysoka" ${task.priority === 'wysoka' ? 'selected' : ''}>Wysoka</option>
                                <option value="pilna" ${task.priority === 'pilna' ? 'selected' : ''}>Pilna</option>
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Status *</label>
                            <select name="status" required class="w-full border rounded px-3 py-2">
                                <option value="do_zrobienia" ${task.status === 'do_zrobienia' ? 'selected' : ''}>Do zrobienia</option>
                                <option value="w_trakcie" ${task.status === 'w_trakcie' ? 'selected' : ''}>W trakcie</option>
                                <option value="zakonczone" ${task.status === 'zakonczone' ? 'selected' : ''}>Zakończone</option>
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Termin</label><input type="datetime-local" name="due_date" value="${formattedDueDate}" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Przypisz do</label>
                            <select name="assigned_to" class="w-full border rounded px-3 py-2">
                                <option value="">Nie przypisane</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" ${task.assigned_to == {{ $user->id }} ? 'selected' : ''}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Firma</label>
                            <select name="company_id" class="w-full border rounded px-3 py-2">
                                <option value="">Brak</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" ${task.company_id == {{ $company->id }} ? 'selected' : ''}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Opis</label><textarea name="description" rows="3" class="w-full border rounded px-3 py-2">${task.description || ''}</textarea></div>
                    </div>
                    <div class="mt-4 flex gap-2 justify-end">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Zapisz</button>
                    </div>
                </form>
            `;
            document.getElementById('modal-overlay').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Błąd podczas ładowania danych zadania');
        });
}

function editActivity(id) { 
    fetch(`/crm/activity/${id}/edit`)
        .then(response => response.json())
        .then(activity => {
            document.getElementById('modal-content').innerHTML = `
                <h3 class="text-xl font-bold mb-4">Edytuj Aktywność</h3>
                <form method="POST" action="/crm/activity/${id}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block mb-1 font-semibold">Typ *</label>
                            <select name="type" required class="w-full border rounded px-3 py-2">
                                <option value="telefon" ${activity.type === 'telefon' ? 'selected' : ''}>Telefon</option>
                                <option value="email" ${activity.type === 'email' ? 'selected' : ''}>Email</option>
                                <option value="spotkanie" ${activity.type === 'spotkanie' ? 'selected' : ''}>Spotkanie</option>
                                <option value="notatka" ${activity.type === 'notatka' ? 'selected' : ''}>Notatka</option>
                                <option value="sms" ${activity.type === 'sms' ? 'selected' : ''}>SMS</option>
                                <option value="oferta" ${activity.type === 'oferta' ? 'selected' : ''}>Oferta</option>
                                <option value="umowa" ${activity.type === 'umowa' ? 'selected' : ''}>Umowa</option>
                                <option value="faktura" ${activity.type === 'faktura' ? 'selected' : ''}>Faktura</option>
                                <option value="reklamacja" ${activity.type === 'reklamacja' ? 'selected' : ''}>Reklamacja</option>
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Data aktywności *</label><input type="datetime-local" name="activity_date" value="${activity.activity_date || ''}" required class="w-full border rounded px-3 py-2"></div>
                        <div class="col-span-2"><label class="block mb-1 font-semibold">Temat *</label><input type="text" name="subject" value="${activity.subject || ''}" required class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Czas trwania (min)</label><input type="number" name="duration" value="${activity.duration || ''}" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block mb-1 font-semibold">Wynik</label>
                            <select name="outcome" class="w-full border rounded px-3 py-2">
                                <option value="">Brak</option>
                                <option value="pozytywny" ${activity.outcome === 'pozytywny' ? 'selected' : ''}>Pozytywny</option>
                                <option value="neutralny" ${activity.outcome === 'neutralny' ? 'selected' : ''}>Neutralny</option>
                                <option value="negatywny" ${activity.outcome === 'negatywny' ? 'selected' : ''}>Negatywny</option>
                                <option value="brak_odpowiedzi" ${activity.outcome === 'brak_odpowiedzi' ? 'selected' : ''}>Brak odpowiedzi</option>
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Firma</label>
                            <select name="company_id" class="w-full border rounded px-3 py-2">
                                <option value="">Brak</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" ${activity.company_id == {{ $company->id }} ? 'selected' : ''}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block mb-1 font-semibold">Szansa</label>
                            <select name="deal_id" class="w-full border rounded px-3 py-2">
                                <option value="">Brak</option>
                                @foreach($deals as $deal)
                                    <option value="{{ $deal->id }}" ${activity.deal_id == {{ $deal->id }} ? 'selected' : ''}>{{ $deal->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2"><label class="block mb-1 font-semibold">Opis</label><textarea name="description" rows="4" class="w-full border rounded px-3 py-2">${activity.description || ''}</textarea></div>
                    </div>
                    <div class="mt-4 flex gap-2 justify-end">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Zapisz</button>
                    </div>
                </form>
            `;
            document.getElementById('modal-overlay').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Błąd podczas ładowania danych aktywności');
        });
}

// Funkcja do tworzenia oferty ze szansy
function createOfferFromDeal(dealId) {
    window.location.href = `/wyceny/nowa?deal_id=${dealId}`;
}

// Automatyczne zamykanie kalendarza po wyborze daty
document.addEventListener('input', function(e) {
    if (e.target.type === 'date') {
        // Dla type="date" zamknij od razu po wyborze daty
        setTimeout(() => e.target.blur(), 100);
    } else if (e.target.type === 'datetime-local') {
        // Dla datetime-local sprawdź czy wybrano już datę
        const value = e.target.value;
        if (value && value.length >= 10) { // Ma datę (YYYY-MM-DD)
            // Zamknij kalendarz po wyborze daty (można jeszcze edytować czas)
            setTimeout(() => {
                // Symuluj kliknięcie poza kalendarzem ale w pole czasu
                e.target.blur();
                // Małe opóźnienie i ponowne focus by móc edytować czas
                setTimeout(() => e.target.focus(), 50);
            }, 100);
        }
    }
});

// Dodatkowy listener dla change jako backup
document.addEventListener('change', function(e) {
    if (e.target.type === 'date' || e.target.type === 'datetime-local') {
        setTimeout(() => e.target.blur(), 100);
    }
});

</script>

</body>
</html>
