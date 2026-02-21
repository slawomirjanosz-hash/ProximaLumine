<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Oferty w toku</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
    @include('parts.menu')
                @php
                    try {
                        $companySettings = \App\Models\CompanySetting::first();
                        if ($companySettings && $companySettings->logo) {
                            if (str_starts_with($companySettings->logo, 'data:image')) {
                                $logoPath = $companySettings->logo;
                            } else {
                                $logoPath = asset('storage/' . $companySettings->logo);
                            }
                        } else {
                            $logoPath = '/logo.png';
                        }
                        $companyName = $companySettings && $companySettings->name ? $companySettings->name : 'Moja Firma';
                    } catch (\Exception $e) {
                        $logoPath = '/logo.png';
                        $companyName = 'Moja Firma';
                    }
                @endphp
                <img src="{{ $logoPath }}" alt="{{ $companyName }}" class="h-10">
                <span class="text-xl font-bold">{{ $companyName }}</span>
                            <span id="datetime" class="ml-4 px-3 py-2 text-sm bg-white-200 text-gray-400 rounded whitespace-nowrap"></span>
            </div>
        </div>
    </header>
    <main class="flex-1">
        <div class="relative max-w-6xl mx-auto p-6">
            <a href="{{ route('offers') }}" class="absolute top-4 left-4 flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 shadow rounded-full text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition z-10">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' /></svg>
                Powrót
            </a>
            
            <h1 class="text-3xl font-bold mb-6 text-center mt-12">Oferty w toku</h1>
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($offers->count() > 0)
                <div class="bg-white rounded shadow overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left text-xs">Nr oferty</th>
                                <th class="p-2 text-left text-xs">Nazwa</th>
                                <th class="p-2 text-left text-xs">Data</th>
                                <th class="p-2 text-left text-xs">Szansa CRM</th>
                                <th class="p-2 text-right text-xs">Cena końcowa</th>
                                <th class="p-2 text-center text-xs">Akcja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-2 text-xs">{{ $offer->offer_number }}</td>
                                <td class="p-2 text-xs">{{ $offer->offer_title }}</td>
                                <td class="p-2 text-xs whitespace-nowrap">{{ $offer->offer_date->format('Y-m-d') }}</td>
                                <td class="p-2">
                                    @if($offer->crmDeal)
                                        <div class="text-xs">
                                            <div class="font-semibold text-blue-600">{{ $offer->crmDeal->name }}</div>
                                            @if($offer->crmDeal->company)
                                                <div class="text-gray-600">{{ $offer->crmDeal->company->name }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="p-2 text-right font-semibold text-xs">{{ number_format($offer->total_price, 2, ',', ' ') }} zł</td>
                                <td class="p-2 text-center">
                                    <div class="flex gap-1 justify-center flex-nowrap">
                                        <form action="{{ route('offers.convertToProject', $offer) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Utworzyć projekt z tej oferty?')" class="px-2 py-0.5 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-xs font-semibold whitespace-nowrap">⭐ Wygrana</button>
                                        </form>
                                        <a href="{{ route('offers.generateWord', $offer) }}" class="px-2 py-0.5 bg-purple-600 text-white rounded hover:bg-purple-700 text-xs whitespace-nowrap">📄 Word</a>
                                        <a href="{{ route('offers.edit', $offer) }}" class="px-2 py-0.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs whitespace-nowrap">Edytuj</a>
                                        <form action="{{ route('offers.copy', $offer) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs whitespace-nowrap">Kopiuj</button>
                                        </form>
                                        <form action="{{ route('offers.archive', $offer) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Czy na pewno przenieść ofertę do archiwum?')" class="px-2 py-0.5 bg-gray-600 text-white rounded hover:bg-gray-700 text-xs whitespace-nowrap" title="Do archiwum">Do arch.</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-gray-400 text-xl py-12">
                    Brak ofert w toku
                </div>
            @endif
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
