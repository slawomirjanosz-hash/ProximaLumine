<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostyka anomalii - ProximaLumine</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 text-gray-900">
<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">Diagnostyka anomalii systemu</h1>
                <p class="text-sm text-gray-600 mt-1">Raport: {{ $timestamp }} | ENV: {{ $environment }} | DB: {{ $dbConnection }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('diagnostics.index') }}" class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm">Powrót do diagnostyki</a>
                <a href="{{ route('diagnostics.anomalies') }}" class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm">Odśwież raport</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="text-xs text-gray-500 uppercase">Krytyczne anomalie</div>
            <div class="text-2xl font-bold text-red-600">{{ $criticalCount }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-500">
            <div class="text-xs text-gray-500 uppercase">Ostrzeżenia</div>
            <div class="text-2xl font-bold text-amber-600">{{ $warningCount }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-xs text-gray-500 uppercase">Pending migracje</div>
            <div class="text-2xl font-bold text-blue-600">{{ $migrationStats['pending_count'] ?? '-' }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="text-xs text-gray-500 uppercase">Orphaned migracje</div>
            <div class="text-2xl font-bold text-purple-600">{{ $migrationStats['orphaned_count'] ?? '-' }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Wykryte anomalie</h2>
        @if(empty($anomalies))
            <div class="p-3 rounded bg-green-50 text-green-800 border border-green-200">Brak wykrytych anomalii krytycznych/ostrzegawczych.</div>
        @else
            <div class="space-y-3">
                @foreach($anomalies as $anomaly)
                    @php
                        $isCritical = ($anomaly['severity'] ?? 'warning') === 'critical';
                    @endphp
                    <div class="p-3 rounded border {{ $isCritical ? 'bg-red-50 border-red-200 text-red-900' : 'bg-amber-50 border-amber-200 text-amber-900' }}">
                        <div class="font-semibold">{{ $isCritical ? 'KRYTYCZNE' : 'OSTRZEŻENIE' }}: {{ $anomaly['title'] }}</div>
                        <div class="text-sm mt-1">{{ $anomaly['details'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Kontrole techniczne</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                <tr class="bg-gray-100">
                    <th class="text-left px-3 py-2 border">Kontrola</th>
                    <th class="text-left px-3 py-2 border">Status</th>
                    <th class="text-left px-3 py-2 border">Szczegóły</th>
                </tr>
                </thead>
                <tbody>
                @foreach($checks as $check)
                    <tr class="{{ $check['ok'] ? 'bg-white' : ($check['critical'] ? 'bg-red-50' : 'bg-amber-50') }}">
                        <td class="px-3 py-2 border font-mono">{{ $check['key'] }}</td>
                        <td class="px-3 py-2 border font-semibold {{ $check['ok'] ? 'text-green-700' : ($check['critical'] ? 'text-red-700' : 'text-amber-700') }}">
                            {{ $check['ok'] ? 'OK' : ($check['critical'] ? 'BŁĄD' : 'BRAK') }}
                        </td>
                        <td class="px-3 py-2 border">{{ $check['message'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if(!empty($queryErrors))
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Błędy zapytań diagnostycznych</h2>
            <ul class="list-disc ml-6 space-y-1 text-sm text-red-700">
                @foreach($queryErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
</body>
</html>
