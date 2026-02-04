<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Magazyn</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">


<div class="max-w-6xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <div class="flex items-center justify-between mb-6 relative">
        <h1 class="text-3xl font-bold mb-6">Magazyn</h1>
        <div class="bg-white rounded shadow p-1 w-96 ml-auto" style="word-break:break-word;">
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-sm font-semibold whitespace-nowrap">Statystyki magazynu:</p>
                    @php [$warehouseValue, $eurPln] = \App\Helpers\WarehouseHelper::getWarehouseValuePln(); @endphp
                    <span class="text-[10px] text-gray-400 block mt-0.5">Kurs Euro = {{ number_format($eurPln, 4, ',', ' ') }} PLN</span>
                </div>
                <div class="flex gap-4">
                    <div class="text-center">
                        <p class="text-lg font-bold text-blue-600">{{ \App\Models\Part::count() }}</p>
                        <p class="text-gray-600 text-xs">Produktów</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-green-600">{{ \App\Models\Category::count() }}</p>
                        <p class="text-gray-600 text-xs">Kategorii</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-purple-600">{{ \App\Models\Part::sum('quantity') }}</p>
                        <p class="text-gray-600 text-xs">Sztuk łącznie</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-bold text-amber-600" style="font-size:0.75rem;">{{ number_format($warehouseValue, 2, ',', ' ') }} PLN</p>
                        <p class="text-gray-600 text-xs">Wartość magazynu</p>
                        <span class="text-[10px] text-gray-400 block mt-0.5">Kurs Euro = {{ number_format($eurPln, 4, ',', ' ') }} PLN</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ZAKŁADKI -->
    <div class="flex gap-4 mb-6">
        <button onclick="showTab('add')" class="tab-btn">Dodaj część</button>
        <button onclick="showTab('remove')" class="tab-btn">Pobierz część</button>
        <button onclick="showTab('check')" class="tab-btn">Sprawdź część</button>
    </div>

    <!-- ZAKŁADKI -->
    <div class="flex gap-4 mb-6">
        <button onclick="showTab('add')" class="tab-btn">Dodaj część</button>
        <button onclick="showTab('remove')" class="tab-btn">Pobierz część</button>
        <button onclick="showTab('check')" class="tab-btn">Sprawdź część</button>
    </div>

    <!-- ================= DODAJ ================= -->
    <div id="tab-add">

        <h2 class="text-xl font-bold mb-2">Dodaj część</h2>

        <form method="POST" action="/parts/add" class="grid grid-cols-4 gap-2 mb-4">
            @csrf
            <input name="name" placeholder="Nazwa" class="border p-2">
            <input name="quantity" type="number" min="1" value="1" class="border p-2">
            <select name="category_id" class="border p-2">
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <button class="bg-green-500 text-white rounded">➕</button>
        </form>

        @if(count($sessionAdds))
            <table class="w-full border border-collapse mt-4">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">Część</th>
                    <th class="border p-2">Kategoria</th>
                    <th class="border p-2">Dodano</th>
                    <th class="border p-2">Stan po</th>
                    <th class="border p-2">Data</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sessionAdds as $r)
                    <tr>
                        <td class="border p-2">{{ $r['name'] }}</td>
                        <td class="border p-2">{{ $r['category'] ?? '-' }}</td>
                        <td class="border p-2 text-center text-green-600 font-bold">
                            +{{ $r['changed'] ?? ($r['quantity'] ?? 0) }}
                        </td>
                        <td class="border p-2 text-center font-bold">
                            {{ $r['after'] ?? '-' }}
                        </td>
                        <td class="border p-2">{{ $r['date'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

    </div>

    <!-- ================= POBIERZ ================= -->
    <div id="tab-remove" class="hidden">

        <h2 class="text-xl font-bold mb-2">Pobierz część</h2>

        <form method="POST" action="/parts/remove" class="grid grid-cols-3 gap-2 mb-4">
            @csrf
            <input name="name" placeholder="Nazwa" class="border p-2">
            <input name="quantity" type="number" min="1" value="1" class="border p-2">
            <button class="bg-amber-400 text-white rounded">➖</button>
        </form>

        @if(count($sessionRemoves))
            <table class="w-full border border-collapse mt-4">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">Część</th>
                    <th class="border p-2">Pobrano</th>
                    <th class="border p-2">Stan po</th>
                    <th class="border p-2">Data</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sessionRemoves as $r)
                    <tr>
                        <td class="border p-2">{{ $r['name'] }}</td>
                        <td class="border p-2 text-center text-red-600 font-bold">
                            {{ $r['changed'] ?? '-' }}
                        </td>
                        <td class="border p-2 text-center font-bold">
                            {{ $r['after'] ?? '-' }}
                        </td>
                        <td class="border p-2">{{ $r['date'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

    </div>

    <!-- ================= SPRAWDŹ ================= -->
    <div id="tab-check" class="hidden">
        <h2 class="text-xl font-bold mb-2">Sprawdź część</h2>
        <p class="text-gray-500">Ten moduł zrobimy w kolejnym kroku.</p>
    </div>

</div>

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
function showTab(tab) {
    ['add','remove','check'].forEach(t =>
        document.getElementById('tab-'+t).classList.add('hidden')
    );
    document.getElementById('tab-'+tab).classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const tab = new URLSearchParams(window.location.search).get('tab') || 'add';
    showTab(tab);
});
</script>

<style>
.tab-btn {
    padding: 8px 12px;
    background: #e5e7eb;
    border-radius: 6px;
}
</style>
</body>
</html>
