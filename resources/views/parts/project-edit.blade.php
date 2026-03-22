<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edycja projektu - {{ $project->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow mt-6">
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Edycja projektu</h2>
        <a href="{{ route('magazyn.projects', ['status' => 'in_progress']) }}" class="text-blue-600 hover:underline">← Powrót do projektów w toku</a>
    </div>
    
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('magazyn.updateProject', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Nr projektu</label>
                <input type="text" name="project_number" value="{{ old('project_number', $project->project_number) }}" 
                       class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Nazwa projektu</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" 
                       class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Budżet (PLN)</label>
                <input type="number" name="budget" step="0.01" value="{{ old('budget', $project->budget) }}" 
                       class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Osoba odpowiedzialna</label>
                <select name="responsible_user_id" class="w-full border p-2 rounded">
                    <option value="">-- Nie przypisano --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('responsible_user_id', $project->responsible_user_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Okres gwarancji (miesiące)</label>
                <input type="number" name="warranty_period" value="{{ old('warranty_period', $project->warranty_period) }}" 
                       class="w-full border p-2 rounded" min="0">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Data rozpoczęcia</label>
                <input type="date" name="started_at" value="{{ old('started_at', $project->started_at ? $project->started_at->format('Y-m-d') : '') }}" 
                       class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Data zakończenia</label>
                <input type="date" name="finished_at" value="{{ old('finished_at', $project->finished_at ? $project->finished_at->format('Y-m-d') : '') }}" 
                       class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Status</label>
                <select name="status" class="w-full border p-2 rounded" required>
                    <option value="in_progress" {{ old('status', $project->status) === 'in_progress' ? 'selected' : '' }}>W toku</option>
                    <option value="warranty" {{ old('status', $project->status) === 'warranty' ? 'selected' : '' }}>Na gwarancji</option>
                    <option value="archived" {{ old('status', $project->status) === 'archived' ? 'selected' : '' }}>Archiwalny</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Autoryzacja pobrań</label>
                @php
                    $canChangeAuthorization = auth()->check() && auth()->user()->is_admin;
                @endphp
                <div class="flex items-center gap-2 mt-2">
                    <input 
                        type="checkbox" 
                        name="requires_authorization" 
                        id="requires_authorization" 
                        value="1"
                        {{ old('requires_authorization', $project->requires_authorization) ? 'checked' : '' }}
                        class="w-4 h-4 {{ $canChangeAuthorization ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                        {{ $canChangeAuthorization ? '' : 'disabled' }}
                    >
                    <label for="requires_authorization" class="text-sm font-medium {{ $canChangeAuthorization ? 'cursor-pointer' : 'text-gray-400 cursor-not-allowed' }}">
                        Pobranie produktów wymaga autoryzacji przez skanowanie
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Jeśli zaznaczone, produkty pobrane do projektu nie zostaną odjęte ze stanu magazynu dopóki nie zostaną zeskanowane</p>
                @if(!$canChangeAuthorization)
                    <p class="text-xs text-gray-500 mt-1">🔒 Tylko administrator może zmienić to ustawienie</p>
                @endif
            </div>

            @php
                $sectionLabels = [
                    'pickup' => 'Pobieranie produktów',
                    'changes' => 'Zmiany w magazynie',
                    'summary' => 'Lista produktów w projekcie',
                    'frappe' => 'Gantt Frappe',
                    'finance' => 'Harmonogram finansowy',
                ];
                $selectedSections = old('visible_sections', $projectVisibleSections ?? []);
            @endphp
            <div>
                <label class="block text-sm font-semibold mb-2">Widoczne sekcje w szczegółach projektu</label>
                <div class="p-4 bg-blue-50 border border-blue-200 rounded space-y-2">
                    @foreach(($availableProjectSections ?? []) as $sectionKey)
                        @php
                            $hasData = (bool) (($sectionsWithData[$sectionKey] ?? false));
                        @endphp
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input
                                type="checkbox"
                                name="visible_sections[]"
                                value="{{ $sectionKey }}"
                                class="project-section-checkbox w-4 h-4"
                                data-section-key="{{ $sectionKey }}"
                                data-has-content="{{ $hasData ? '1' : '0' }}"
                                data-section-label="{{ $sectionLabels[$sectionKey] ?? $sectionKey }}"
                                {{ in_array($sectionKey, $selectedSections, true) ? 'checked' : '' }}
                            >
                            <span>{{ $sectionLabels[$sectionKey] ?? $sectionKey }}</span>
                            @if($hasData)
                                <span class="text-xs text-amber-700">(sekcja zawiera dane)</span>
                            @endif
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-1">Po odznaczeniu sekcja zostanie ukryta w szczegółach projektu.</p>
                @error('visible_sections')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Zapisz zmiany
            </button>
            <a href="{{ route('magazyn.projects', ['status' => 'in_progress']) }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                Anuluj
            </a>
        </div>
    </form>

</div>

<script>
    // Kontrola checkboxa autoryzacji w zależności od stanu QR
    document.addEventListener('DOMContentLoaded', function() {
        const qrEnabled = {{ $qrEnabled ? 'true' : 'false' }};
        const canChangeAuthorization = {{ (auth()->check() && auth()->user()->is_admin) ? 'true' : 'false' }};
        const authCheckbox = document.getElementById('requires_authorization');
        
        if ((!qrEnabled || !canChangeAuthorization) && authCheckbox) {
            authCheckbox.disabled = true;
            authCheckbox.closest('div').classList.add('opacity-50', 'cursor-not-allowed');
        }

        const sectionCheckboxes = document.querySelectorAll('.project-section-checkbox');
        sectionCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const isUnchecking = !this.checked;
                const hasContent = this.dataset.hasContent === '1';

                if (isUnchecking && hasContent) {
                    const sectionLabel = this.dataset.sectionLabel || 'Ta sekcja';
                    const confirmed = confirm(`${sectionLabel} zawiera już dane. Czy na pewno chcesz ukryć tę sekcję?`);
                    if (!confirmed) {
                        this.checked = true;
                    }
                }
            });
        });

        const editForm = document.querySelector('form[action="{{ route('magazyn.updateProject', $project->id) }}"]');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const checked = Array.from(sectionCheckboxes).filter(cb => cb.checked).length;
                if (checked === 0) {
                    e.preventDefault();
                    alert('Wybierz co najmniej jedną widoczną sekcję projektu.');
                }
            });
        }
    });
</script>

</body>
</html>
