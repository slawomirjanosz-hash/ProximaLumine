@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-red-700 mb-4">Diagnostyka migracji</h1>
        
        @if(count($orphaned) > 0)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <h2 class="font-bold mb-2">⚠️ Osierocone migracje (w bazie, ale brak pliku):</h2>
            <ul class="list-disc pl-6">
                @foreach($orphaned as $migration)
                    <li>{{ $migration }}</li>
                @endforeach
            </ul>
            <button onclick="cleanOrphaned()" class="mt-3 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                🗑️ Usuń osierocone migracje z bazy
            </button>
            <p id="clean-result" class="mt-2 font-semibold"></p>
        </div>
        @else
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            ✅ Brak osieroconych migracji
        </div>
        @endif
        
        @if(count($pending) > 0)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            <h2 class="font-bold mb-2">⏳ Migracje do wykonania (plik istnieje, brak w bazie):</h2>
            <ul class="list-disc pl-6">
                @foreach($pending as $migration)
                    <li>{{ $migration }}</li>
                @endforeach
            </ul>
        </div>
        @else
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            ✅ Wszystkie migracje wykonane
        </div>
        @endif
        
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h2 class="text-lg font-bold mb-2">Statystyki:</h2>
            <ul class="list-disc pl-6">
                <li>Plików migracji: {{ count($fileNames) }}</li>
                <li>Migracji w bazie: {{ count($dbMigrations) }}</li>
                <li>Osieroconych: {{ count($orphaned) }}</li>
                <li>Do wykonania: {{ count($pending) }}</li>
            </ul>
        </div>
        
        <a href="/" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">← Powrót</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cleanOrphaned() {
    if (!confirm('Czy na pewno usunąć osierocone migracje z bazy danych?')) return;
    
    fetch('/diagnostics/migrations/clean', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('clean-result').textContent = '✅ Usunięto ' + data.deleted + ' osieroconych migracji';
            document.getElementById('clean-result').className = 'mt-2 font-semibold text-green-700';
            setTimeout(() => location.reload(), 2000);
        } else {
            document.getElementById('clean-result').textContent = '❌ ' + data.message;
            document.getElementById('clean-result').className = 'mt-2 font-semibold text-red-700';
        }
    })
    .catch(() => {
        document.getElementById('clean-result').textContent = '❌ Błąd podczas usuwania';
        document.getElementById('clean-result').className = 'mt-2 font-semibold text-red-700';
    });
}
</script>
@endpush
