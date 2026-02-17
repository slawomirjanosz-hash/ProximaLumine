<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wyceny i Oferty</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    @include('parts.menu')
    <main class="flex-1">
        <div class="max-w-6xl mx-auto mt-6">
            <a href="/" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow transition-all text-gray-700 font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Powrót
            </a>
        </div>
        <div class="max-w-3xl mx-auto mt-6 p-6 bg-white rounded shadow text-center">
            <h1 class="text-3xl font-bold mb-8">Wyceny i Oferty</h1>
            <div class="flex flex-col sm:flex-row flex-wrap gap-4 justify-center items-center">
                <a href="{{ route('offers.portfolio') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded text-base hover:bg-blue-700 min-w-[180px]">Portfolio</a>
                <a href="{{ route('offers.new') }}" class="inline-block px-6 py-2 bg-green-600 text-white rounded text-base hover:bg-green-700 min-w-[180px]">Zrób nową Ofertę</a>
                <a href="{{ route('offers.inprogress') }}" class="inline-block px-6 py-2 bg-yellow-600 text-white rounded text-base hover:bg-yellow-700 min-w-[180px]">Oferty w toku</a>
                <a href="{{ route('offers.archived') }}" class="inline-block px-6 py-2 bg-gray-500 text-white rounded text-base hover:bg-gray-600 min-w-[180px]">Oferty zarchiwizowane</a>
                @if(auth()->check() && (auth()->user()->email === 'proximalumine@gmail.com' || auth()->user()->can_settings))
                    <a href="{{ route('offers.settings') }}" class="inline-block px-6 py-2 bg-purple-600 text-white rounded text-base hover:bg-purple-700 min-w-[180px]">⚙️ Ustawienia Ofert</a>
                @endif
                <a href="{{ route('magazyn.check') }}" class="inline-block px-6 py-2 bg-teal-600 text-white rounded text-base hover:bg-teal-700 min-w-[180px]">📦 Wejdź do magazynu</a>
            </div>
        </div>
    </main>
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
</html>
