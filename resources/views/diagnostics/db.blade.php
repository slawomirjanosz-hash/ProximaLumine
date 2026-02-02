@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-red-700 mb-4">Diagnostyka bazy danych</h1>
        <ul class="list-disc pl-6 text-lg">
            <li>Status połączenia: <span id="db-status" class="font-semibold text-gray-800">Sprawdzanie...</span></li>
            <li>Typ bazy: <span id="db-type" class="font-semibold text-gray-800">Sprawdzanie...</span></li>
            <li>Aktualna tabela: <span id="db-table" class="font-semibold text-gray-800">Sprawdzanie...</span></li>
            <li>Liczba rekordów w users: <span id="db-users" class="font-semibold text-gray-800">Sprawdzanie...</span></li>
        </ul>
        <div class="mt-6">
            <a href="/" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">← Powrót</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
fetch('/diagnostics/db-status-json')
    .then(r => r.json())
    .then(data => {
        document.getElementById('db-status').textContent = data.status;
        document.getElementById('db-type').textContent = data.type;
        document.getElementById('db-table').textContent = data.table;
        document.getElementById('db-users').textContent = data.users;
    })
    .catch(() => {
        document.getElementById('db-status').textContent = 'Błąd!';
        document.getElementById('db-type').textContent = '-';
        document.getElementById('db-table').textContent = '-';
        document.getElementById('db-users').textContent = '-';
    });
</script>
@endpush
