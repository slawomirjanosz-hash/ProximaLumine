<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostyka systemu - ProximaLumine</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
<div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Diagnostyka systemu</h1>
    
    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500">
        <h2 class="font-bold mb-2">🔍 Dostępne narzędzia diagnostyczne:</h2>
        <ul class="space-y-2">
            <li>
                <a href="/diagnostics-projects.html" class="text-blue-600 hover:underline font-semibold text-lg">
                    🔧 Diagnostyka projektów (błędy 500) →
                </a>
                <p class="text-xs text-gray-600">Sprawdź, które projekty mają problemy z usuniętymi produktami</p>
            </li>
        </ul>
    </div>
    
    <div id="diagnostics-output" class="text-sm"></div>
    <button id="refresh-btn" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Odśwież diagnostykę</button>
</div>
<script>
function fetchDiagnostics() {
    fetch('/diagnostics/project-check')
        .then(r => r.json())
        .then(data => {
            document.getElementById('diagnostics-output').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(err => {
            document.getElementById('diagnostics-output').innerHTML = '<p class="text-red-600">Błąd: ' + err.message + '</p>';
        });
}
fetchDiagnostics();
document.getElementById('refresh-btn').onclick = fetchDiagnostics;
</script>
</body>
</html>

