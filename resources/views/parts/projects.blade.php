<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Projekty</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">


@include('parts.menu')

<div class="w-full px-4 mt-4">
    <a href="/" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow transition-all text-gray-700 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Powrót
    </a>
</div>

{{-- KOMUNIKATY --}}
@if(session('success'))
    <div class="w-full px-4 mt-3">
        <div class="bg-green-100 text-green-800 p-2 rounded">{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="w-full px-4 mt-3">
        <div class="bg-red-100 text-red-800 p-2 rounded">{{ session('error') }}</div>
    </div>
@endif

<div class="w-full px-4 pb-6 mt-4">
    
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Projekty</h2>
        <a href="{{ route('magazyn.projects.settings') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Ustawienia projektów
        </a>
    </div>
    
    {{-- PRZYCISKI WIDOKÓW --}}
    <div class="flex gap-2 mb-6">
        <button type="button" id="btn-in-progress" class="px-4 py-2 bg-blue-500 text-white rounded active-tab">
            Projekty w toku
        </button>
        <button type="button" id="btn-warranty" class="px-4 py-2 bg-gray-300 text-gray-800 rounded">
            Projekty na gwarancji
        </button>
        <button type="button" id="btn-archived" class="px-4 py-2 bg-gray-300 text-gray-800 rounded">
            Projekty Archiwalne
        </button>
    </div>
    
    {{-- SEKCJA: DODAJ PROJEKT --}}
    <div class="bg-white rounded shadow mb-6 border">
        <button type="button" class="collapsible-btn w-full flex items-center gap-2 p-6 cursor-pointer hover:bg-gray-50" data-target="add-project-content">
            <span class="toggle-arrow text-lg">▶</span>
            <h3 class="text-lg font-semibold">Dodaj Projekt</h3>
        </button>
        <div id="add-project-content" class="collapsible-content hidden p-6 border-t">
            <form method="POST" action="{{ route('magazyn.projects.store') }}" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Nr projektu *</label>
                        <input type="text" name="project_number" required class="w-full px-3 py-2 border rounded" placeholder="PR-2024-001">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Nazwa projektu *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border rounded" placeholder="Projekt X">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Budżet projektu (PLN)</label>
                        <input type="number" name="budget" step="0.01" min="0" class="w-full px-3 py-2 border rounded" placeholder="10000.00">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Osoba odpowiedzialna</label>
                        <select name="responsible_user_id" class="w-full px-3 py-2 border rounded">
                            <option value="">- Wybierz -</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Okres gwarancji (miesiące)</label>
                        <input type="number" name="warranty_period" min="0" class="w-full px-3 py-2 border rounded" placeholder="12">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Data zakończenia projektu</label>
                        <input type="date" name="finished_at" class="w-full px-3 py-2 border rounded">
                    </div>
                </div>
                
                @php
                    $qrSettings = \DB::table('qr_settings')->first();
                    $qrEnabled = $qrSettings->qr_enabled ?? true;
                    $canChangeAuthorization = auth()->check() && auth()->user()->is_admin;
                @endphp
                
                <div class="flex items-center gap-2 p-4 bg-purple-50 border border-purple-200 rounded">
                    <input 
                        type="checkbox" 
                        name="requires_authorization" 
                        id="requires_authorization"
                        value="1"
                        class="w-5 h-5"
                        {{ (!$qrEnabled || !$canChangeAuthorization) ? 'disabled' : '' }}
                    >
                    <label for="requires_authorization" class="text-sm font-medium {{ (!$qrEnabled || !$canChangeAuthorization) ? 'text-gray-400' : '' }}">
                        🔐 Pobranie produktów wymaga autoryzacji (skanowanie kodów QR)
                    </label>
                </div>
                @if(!$qrEnabled)
                <p class="text-xs text-gray-500 -mt-2">💡 Obsługa kodów QR jest wyłączona w ustawieniach</p>
                @elseif(!$canChangeAuthorization)
                <p class="text-xs text-gray-500 -mt-2">🔒 Tylko administrator może zmienić to ustawienie</p>
                @endif

                @php
                    $sectionLabels = [
                        'pickup' => 'Pobieranie produktów',
                        'changes' => 'Zmiany w magazynie',
                        'summary' => 'Lista produktów w projekcie',
                        'frappe' => 'Gantt Frappe',
                        'finance' => 'Harmonogram finansowy',
                    ];
                    $selectedSections = old('visible_sections', $availableProjectSections ?? []);
                @endphp
                <div class="p-4 bg-blue-50 border border-blue-200 rounded">
                    <p class="text-sm font-semibold text-blue-900 mb-2">Widoczne sekcje w szczegółach projektu</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach(($availableProjectSections ?? []) as $sectionKey)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="visible_sections[]"
                                    value="{{ $sectionKey }}"
                                    class="w-4 h-4"
                                    {{ in_array($sectionKey, $selectedSections, true) ? 'checked' : '' }}
                                >
                                <span>{{ $sectionLabels[$sectionKey] ?? $sectionKey }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-600 mt-2">Wybierz sekcje, które mają być dostępne w widoku szczegółów projektu.</p>
                </div>

                @error('visible_sections')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Utwórz Projekt
                </button>
            </form>
        </div>
    </div>
    
    {{-- SEKCJA: PROJEKTY W TOKU --}}
    <div id="section-in-progress" class="project-section">
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="flex justify-between items-center px-4 py-3 border-b">
                <h3 class="text-lg font-semibold">Projekty w toku</h3>
                @if(auth()->user()->email === 'proximalumine@gmail.com' && $inProgressProjects->count() > 0)
                    <button type="button" id="delete-in-progress-btn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm hidden">
                        🗑️ Usuń zaznaczone (0)
                    </button>
                @endif
            </div>
            <form id="delete-in-progress-form" method="POST" action="{{ route('magazyn.projects.bulkDelete') }}">
                @csrf
                @method('DELETE')

                @if($inProgressProjects->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            @if(auth()->user()->email === 'proximalumine@gmail.com')
                                <th class="p-2 text-center w-10">
                                    <input type="checkbox" class="select-all-in-progress w-4 h-4 cursor-pointer" title="Zaznacz wszystkie">
                                </th>
                            @endif
                            <th class="p-2 text-left text-xs whitespace-nowrap">Nr projektu</th>
                            <th class="p-2 text-left text-sm w-2/5">Nazwa</th>
                            <th class="p-2 text-left text-xs whitespace-nowrap">Oferta</th>
                            <th class="p-2 text-left text-xs whitespace-nowrap">Data rozpoczęcia</th>
                            <th class="p-2 text-left text-xs whitespace-nowrap">Data zakończenia</th>
                            <th class="p-2 text-right text-sm whitespace-nowrap">Cena końcowa</th>
                            <th class="p-2 text-left text-xs">Osoba odpowiedzialna</th>
                            <th class="p-2 text-center text-xs w-28">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inProgressProjects as $project)
                        <tr class="border-t hover:bg-gray-50">
                            @if(auth()->user()->email === 'proximalumine@gmail.com')
                                <td class="p-2 text-center">
                                    <input type="checkbox" name="project_ids[]" value="{{ $project->id }}" class="project-checkbox-in-progress w-4 h-4 cursor-pointer">
                                </td>
                            @endif
                            <td class="p-2 text-xs whitespace-nowrap font-mono">{{ $project->project_number }}</td>
                            <td class="p-2 text-sm font-medium">{{ $project->name }}</td>
                            <td class="p-2 text-xs whitespace-nowrap">
                                @if($project->sourceOffer)
                                    <div class="text-xs">
                                        <div class="font-semibold text-blue-600">{{ $project->sourceOffer->offer_number }}</div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="p-2 text-xs whitespace-nowrap">{{ $project->started_at ? $project->started_at->format('Y-m-d') : '-' }}</td>
                            <td class="p-2 text-xs whitespace-nowrap">{{ $project->finished_at ? $project->finished_at->format('Y-m-d') : '-' }}</td>
                            <td class="p-2 text-right font-bold text-sm whitespace-nowrap">{{ $project->budget ? number_format($project->budget, 2, ',', ' ') . ' zł' : '-' }}</td>
                            <td class="p-2 text-xs">{{ $project->responsibleUser->name ?? '-' }}</td>
                            <td class="p-2 text-center">
                                <div class="flex gap-1 justify-center flex-nowrap">
                                    <a href="{{ route('magazyn.projects.show', $project->id) }}" title="Szczegóły projektu" class="p-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 inline-flex items-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('magazyn.editProject', $project->id) }}" title="Edytuj projekt" class="p-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 inline-flex items-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <button type="button" onclick="openPdfPreview('{{ route('magazyn.projects.generatePdf', $project->id) }}')" title="Podgląd PDF" class="p-1.5 bg-red-600 text-white rounded hover:bg-red-700 inline-flex items-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </button>
                                    <form action="{{ route('magazyn.projects.copy', $project->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="Kopiuj projekt" class="p-1.5 bg-green-600 text-white rounded hover:bg-green-700 inline-flex items-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </form>
                                    @if(auth()->user()->email === 'proximalumine@gmail.com' || (auth()->user()->is_admin && !in_array($project->status, ['warranty','archived'])))
                                    <form action="{{ route('magazyn.deleteProject', $project->id) }}" method="POST" class="inline delete-project-form" data-project-name="{{ $project->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Usuń projekt" class="p-1.5 bg-gray-600 text-white rounded hover:bg-gray-700 inline-flex items-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center text-gray-400 text-base py-12">
                    Brak projektów w toku
                </div>
                @endif
            </form>
        </div>
    </div>
    {{-- KONIEC SEKCJI: PROJEKTY W TOKU --}}
    
    {{-- SEKCJA: PROJEKTY NA GWARANCJI --}}
    <div id="section-warranty" class="project-section hidden">
        <div class="bg-white rounded shadow border">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Projekty na gwarancji</h3>
                    @if(auth()->user()->email === 'proximalumine@gmail.com' && $warrantyProjects->count() > 0)
                        <button type="button" id="delete-warranty-btn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm hidden">
                            🗑️ Usuń zaznaczone (0)
                        </button>
                    @endif
                </div>
                <form id="delete-warranty-form" method="POST" action="{{ route('magazyn.projects.bulkDelete') }}">
                    @csrf
                    @method('DELETE')
                    <table class="w-full border border-collapse text-xs">
                        <thead class="bg-gray-100">
                            <tr>
                                @if(auth()->user()->email === 'proximalumine@gmail.com')
                                    <th class="border p-2 w-10">
                                        <input type="checkbox" class="select-all-warranty w-4 h-4 cursor-pointer" title="Zaznacz wszystkie">
                                    </th>
                                @endif
                                <th class="border p-2">Nr projektu</th>
                                <th class="border p-2">Nazwa</th>
                                <th class="border p-2">Budżet</th>
                                <th class="border p-2">Osoba odpowiedzialna</th>
                                <th class="border p-2">Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warrantyProjects as $project)
                                <tr>
                                    @if(auth()->user()->email === 'proximalumine@gmail.com')
                                        <td class="border p-2 text-center">
                                            <input type="checkbox" name="project_ids[]" value="{{ $project->id }}" class="project-checkbox-warranty w-4 h-4 cursor-pointer">
                                        </td>
                                    @endif
                                    <td class="border p-2">{{ $project->project_number }}</td>
                                    <td class="border p-2">{{ $project->name }}</td>
                                    <td class="border p-2 text-right">{{ $project->budget ? number_format($project->budget, 2) . ' PLN' : '-' }}</td>
                                    <td class="border p-2">{{ $project->responsibleUser->name ?? '-' }}</td>
                                    <td class="border p-2 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('magazyn.projects.show', $project->id) }}" class="text-blue-600 hover:underline text-sm">Szczegóły</a>
                                            @if(auth()->user()->is_admin)
                                            <form action="{{ route('magazyn.deleteProject', $project->id) }}" method="POST" class="inline delete-project-form" data-project-name="{{ $project->name }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold" title="Usuń projekt">🗑️</button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->email === 'proximalumine@gmail.com' ? '6' : '5' }}" class="border p-4 text-center text-gray-500">Brak projektów na gwarancji</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
    
    {{-- SEKCJA: PROJEKTY ARCHIWALNE --}}
    <div id="section-archived" class="project-section hidden">
        <div class="bg-white rounded shadow border">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Projekty Archiwalne</h3>
                    @if(auth()->user()->email === 'proximalumine@gmail.com' && $archivedProjects->count() > 0)
                        <button type="button" id="delete-archived-btn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm hidden">
                            🗑️ Usuń zaznaczone (0)
                        </button>
                    @endif
                </div>
                <form id="delete-archived-form" method="POST" action="{{ route('magazyn.projects.bulkDelete') }}">
                    @csrf
                    @method('DELETE')
                    <table class="w-full border border-collapse text-xs">
                        <thead class="bg-gray-100">
                            <tr>
                                @if(auth()->user()->email === 'proximalumine@gmail.com')
                                    <th class="border p-2 w-10">
                                        <input type="checkbox" class="select-all-archived w-4 h-4 cursor-pointer" title="Zaznacz wszystkie">
                                    </th>
                                @endif
                                <th class="border p-2">Nr projektu</th>
                                <th class="border p-2">Nazwa</th>
                                <th class="border p-2">Budżet</th>
                                <th class="border p-2">Osoba odpowiedzialna</th>
                                <th class="border p-2">Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($archivedProjects as $project)
                                <tr>
                                    @if(auth()->user()->email === 'proximalumine@gmail.com')
                                        <td class="border p-2 text-center">
                                            <input type="checkbox" name="project_ids[]" value="{{ $project->id }}" class="project-checkbox-archived w-4 h-4 cursor-pointer">
                                        </td>
                                    @endif
                                    <td class="border p-2">{{ $project->project_number }}</td>
                                    <td class="border p-2">{{ $project->name }}</td>
                                    <td class="border p-2 text-right">{{ $project->budget ? number_format($project->budget, 2) . ' PLN' : '-' }}</td>
                                    <td class="border p-2">{{ $project->responsibleUser->name ?? '-' }}</td>
                                    <td class="border p-2 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('magazyn.projects.show', $project->id) }}" class="text-blue-600 hover:underline text-sm">Szczegóły</a>
                                            @if(auth()->user()->is_admin)
                                            <form action="{{ route('magazyn.deleteProject', $project->id) }}" method="POST" class="inline delete-project-form" data-project-name="{{ $project->name }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold" title="Usuń projekt">🗑️</button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->email === 'proximalumine@gmail.com' ? '6' : '5' }}" class="border p-4 text-center text-gray-500">Brak projektów archiwalnych</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
    
</div>

<script>
    // Collapsible sections
    document.querySelectorAll('.collapsible-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const content = document.getElementById(targetId);
            const arrow = this.querySelector('.toggle-arrow');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                arrow.textContent = '▼';
            } else {
                content.classList.add('hidden');
                arrow.textContent = '▶';
            }
        });
    });
    
    // Tab switching
    const tabs = {
        'btn-in-progress': 'section-in-progress',
        'btn-warranty': 'section-warranty',
        'btn-archived': 'section-archived'
    };
    
    Object.keys(tabs).forEach(btnId => {
        document.getElementById(btnId).addEventListener('click', function() {
            // Ukryj wszystkie sekcje
            document.querySelectorAll('.project-section').forEach(s => s.classList.add('hidden'));
            
            // Usuń active z wszystkich przycisków
            Object.keys(tabs).forEach(id => {
                const btn = document.getElementById(id);
                btn.classList.remove('bg-blue-500', 'text-white', 'active-tab');
                btn.classList.add('bg-gray-300', 'text-gray-800');
            });
            
            // Pokaż wybraną sekcję
            document.getElementById(tabs[btnId]).classList.remove('hidden');
            
            // Oznacz aktywny przycisk
            this.classList.remove('bg-gray-300', 'text-gray-800');
            this.classList.add('bg-blue-500', 'text-white', 'active-tab');
        });
    });
    
    // Obsługa parametrów GET z menu
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const addParam = urlParams.get('add');
    
    // Jeśli parametr add=1, rozwiń sekcję "Dodaj Projekt"
    if (addParam === '1') {
        const addProjectBtn = document.querySelector('[data-target="add-project-content"]');
        const addProjectContent = document.getElementById('add-project-content');
        const addProjectArrow = addProjectBtn.querySelector('.toggle-arrow');
        
        if (addProjectContent && addProjectContent.classList.contains('hidden')) {
            addProjectContent.classList.remove('hidden');
            addProjectArrow.textContent = '▼';
        }
    }
    
    // Jeśli parametr status, przełącz na odpowiednią zakładkę
    if (status === 'in_progress') {
        document.getElementById('btn-in-progress').click();
    } else if (status === 'warranty') {
        document.getElementById('btn-warranty').click();
    } else if (status === 'archived') {
        document.getElementById('btn-archived').click();
    }

    // Funkcja do aktualizacji przycisku usuwania
    function setupDeleteSection(section) {
        const checkboxes = document.querySelectorAll(`.project-checkbox-${section}`);
        const selectAll = document.querySelector(`.select-all-${section}`);
        const deleteBtn = document.getElementById(`delete-${section}-btn`);
        const form = document.getElementById(`delete-${section}-form`);
        
        if (!checkboxes.length || !deleteBtn) return;
        
        function updateDeleteButton() {
            const checkedCount = document.querySelectorAll(`.project-checkbox-${section}:checked`).length;
            if (checkedCount > 0) {
                deleteBtn.classList.remove('hidden');
                deleteBtn.textContent = `🗑️ Usuń zaznaczone (${checkedCount})`;
            } else {
                deleteBtn.classList.add('hidden');
            }
        }
        
        // Zaznacz/odznacz wszystkie
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateDeleteButton();
            });
        }
        
        // Aktualizuj stan przy zmianie pojedynczego checkboxa
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (selectAll) {
                    selectAll.checked = Array.from(checkboxes).every(c => c.checked);
                }
                updateDeleteButton();
            });
        });
        
        // Obsługa przycisku usuwania
        deleteBtn.addEventListener('click', function() {
            const checkedCount = document.querySelectorAll(`.project-checkbox-${section}:checked`).length;
            if (checkedCount === 0) return;
            
            const confirmed = confirm(`Czy na pewno chcesz TRWALE usunąć ${checkedCount} projekt(ów)?\n\nTa operacja jest nieodwracalna i usunie również wszystkie powiązane dane (pobrania, autoryzacje, itp.).`);
            if (confirmed) {
                form.submit();
            }
        });
    }
    
    // Inicjalizacja dla wszystkich sekcji
    setupDeleteSection('in-progress');
    setupDeleteSection('warranty');
    setupDeleteSection('archived');

    // Obsługa pojedynczych przycisków usuń (dla adminów)
    document.querySelectorAll('.delete-project-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const projectName = this.dataset.projectName;
            const confirmed = confirm(`Czy na pewno chcesz TRWALE usunąć projekt "${projectName}"?\n\nUWAGA! Ta operacja jest nieodwracalna i usunie:\n- Wszystkie pobrania produktów\n- Wszystkie zadania i harmonogramy\n- Wszystkie załadowane listy\n- Cały projekt\n\nCzy kontynuować?`);
            if (confirmed) {
                this.submit();
            }
        });
    });

    // PDF Preview Modal
    function openPdfPreview(url) {
        var modal = document.getElementById('pdf-preview-modal');
        var iframe = document.getElementById('pdf-preview-iframe');
        var dlLink = document.getElementById('pdf-download-link');
        iframe.src = url;
        dlLink.href = url + '?download=1';
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closePdfPreview() {
        var modal = document.getElementById('pdf-preview-modal');
        var iframe = document.getElementById('pdf-preview-iframe');
        iframe.src = '';
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePdfPreview();
    });
</script>

{{-- PDF PREVIEW MODAL --}}
<div id="pdf-preview-modal" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,0.75); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:10px; width:92vw; max-width:960px; height:90vh; display:flex; flex-direction:column; box-shadow:0 24px 64px rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
            <span style="font-weight:700; font-size:15px; color:#1f2937;">&#128196; Podgląd PDF</span>
            <div style="display:flex; gap:8px;">
                <a id="pdf-download-link" href="#" download style="padding:8px 16px; background:#2563eb; color:#fff; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none;">&#8595; Pobierz PDF</a>
                <button onclick="closePdfPreview()" style="padding:8px 14px; background:#ef4444; color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">&times; Zamknij</button>
            </div>
        </div>
        <iframe id="pdf-preview-iframe" src="" style="flex:1; border:none; border-radius:0 0 10px 10px;"></iframe>
    </div>
</div>

</body>
</html>
