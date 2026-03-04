<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Audyty</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
    <style>
        .audit-tab-button { @apply px-4 py-2 text-sm font-medium rounded-t-lg transition-colors; }
        .audit-tab-button.active { @apply bg-white text-blue-600 border-b-2 border-blue-600; }
        .audit-tab-button:not(.active) { @apply bg-gray-200 text-gray-600 hover:bg-gray-300; }
        .audit-tab-content { @apply hidden; }
        .audit-tab-content.active { @apply block; }
    </style>
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-7xl mx-auto mt-6 mb-12">
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-6 border-b border-gray-200 flex gap-2">
            <button class="audit-tab-button active" data-tab="new-audit" onclick="switchAuditTab('new-audit', this)">➕ Nowy audyt</button>
            <button class="audit-tab-button" data-tab="in-progress" onclick="switchAuditTab('in-progress', this)">⏳ Audyty w toku</button>
            <button class="audit-tab-button" data-tab="completed" onclick="switchAuditTab('completed', this)">✅ Audyty zakończone</button>
        </div>

        <div id="new-audit" class="audit-tab-content active">
            <h2 class="text-xl font-bold mb-4">Nowy audyt</h2>
            <form method="POST" action="{{ route('audits.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                <div>
                    <label class="block mb-1 font-semibold">Nazwa *</label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block mb-1 font-semibold">Data rozpoczęcia *</label>
                    <input type="date" name="start_date" required value="{{ old('start_date') }}" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block mb-1 font-semibold">Firma</label>
                    <select name="company_id" class="w-full border rounded px-3 py-2">
                        <option value="">Brak</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-semibold">Osoba odpowiedzialna *</label>
                    <select name="responsible_user_id" required class="w-full border rounded px-3 py-2">
                        <option value="">Wybierz</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('responsible_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold">Osoby zaangażowane</label>
                    <div class="border rounded p-3 grid grid-cols-1 md:grid-cols-2 gap-2 max-h-56 overflow-y-auto">
                        @foreach($users as $user)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="involved_user_ids[]" value="{{ $user->id }}" class="w-4 h-4" {{ in_array($user->id, old('involved_user_ids', [])) ? 'checked' : '' }}>
                                <span>{{ $user->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Zapisz audyt</button>
                </div>
            </form>
        </div>

        <div id="in-progress" class="audit-tab-content">
            <h2 class="text-xl font-bold mb-4">Audyty w toku</h2>

            @if($auditsInProgress->isEmpty())
                <p class="text-gray-600">Brak audytów w toku.</p>
            @else
                <div class="space-y-3">
                    @foreach($auditsInProgress as $audit)
                        <div class="border rounded-lg p-4 bg-white">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-lg">{{ $audit->name }}</p>
                                    <p class="text-sm text-gray-600">Start: {{ optional($audit->start_date)->format('Y-m-d') }}</p>
                                    <p class="text-sm text-gray-600">Firma: {{ $audit->company->name ?? 'Brak' }}</p>
                                    <p class="text-sm text-gray-600">Odpowiedzialny: {{ $audit->responsibleUser->name ?? 'Brak' }}</p>
                                    <p class="text-sm text-gray-600">Zaangażowani:
                                        {{ $audit->involvedUsers->pluck('name')->join(', ') ?: 'Brak' }}
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('audits.status', $audit) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="zakonczony">
                                    <button type="submit" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm">Oznacz jako zakończony</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div id="completed" class="audit-tab-content">
            <h2 class="text-xl font-bold mb-4">Audyty zakończone</h2>

            @if($auditsCompleted->isEmpty())
                <p class="text-gray-600">Brak zakończonych audytów.</p>
            @else
                <div class="space-y-3">
                    @foreach($auditsCompleted as $audit)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <p class="font-semibold text-lg">{{ $audit->name }}</p>
                            <p class="text-sm text-gray-600">Start: {{ optional($audit->start_date)->format('Y-m-d') }}</p>
                            <p class="text-sm text-gray-600">Firma: {{ $audit->company->name ?? 'Brak' }}</p>
                            <p class="text-sm text-gray-600">Odpowiedzialny: {{ $audit->responsibleUser->name ?? 'Brak' }}</p>
                            <p class="text-sm text-gray-600">Zaangażowani: {{ $audit->involvedUsers->pluck('name')->join(', ') ?: 'Brak' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function switchAuditTab(tabId, buttonEl) {
    document.querySelectorAll('.audit-tab-content').forEach(content => content.classList.remove('active'));
    document.querySelectorAll('.audit-tab-button').forEach(btn => btn.classList.remove('active'));

    const tab = document.getElementById(tabId);
    if (tab) {
        tab.classList.add('active');
    }
    buttonEl.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function () {
    const allowedTabs = ['new-audit', 'in-progress', 'completed'];
    const queryTab = new URLSearchParams(window.location.search).get('tab');
    const targetTab = allowedTabs.includes(queryTab) ? queryTab : 'new-audit';
    const targetButton = document.querySelector(`.audit-tab-button[data-tab="${targetTab}"]`);

    if (targetButton) {
        switchAuditTab(targetTab, targetButton);
    }
});
</script>

</body>
</html>
