@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Diagnostyka systemu</h1>
    <div id="diagnostics-output" class="text-sm"></div>
    <button id="refresh-btn" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Odśwież diagnostykę</button>
</div>
<script>
function fetchDiagnostics() {
    fetch('/diagnostics/project-check')
        .then(r => r.json())
        .then(data => {
            document.getElementById('diagnostics-output').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        });
}
fetchDiagnostics();
document.getElementById('refresh-btn').onclick = fetchDiagnostics;
</script>
@endsection
