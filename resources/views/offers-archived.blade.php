<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Oferty zarchiwizowane</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    @include('parts.menu')
    <main class="flex-1">
        <div class="relative max-w-6xl mx-auto p-6">
            <a href="{{ route('offers') }}" class="absolute top-4 left-4 flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 shadow rounded-full text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition z-10">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' /></svg>
                Powrót
            </a>
            
            <h1 class="text-3xl font-bold mb-6 text-center mt-12">Oferty zarchiwizowane</h1>
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($offers->count() > 0)
                <div class="bg-white rounded shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-4 text-left">Nr oferty</th>
                                <th class="p-4 text-left">Nazwa</th>
                                <th class="p-4 text-left">Data</th>
                                <th class="p-4 text-left w-1/4">Szansa CRM</th>
                                <th class="p-4 text-right whitespace-nowrap">Cena końcowa</th>
                                <th class="p-4 text-center w-16">Akcja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-4">{{ $offer->offer_number }}</td>
                                <td class="p-4">{{ $offer->offer_title }}</td>
                                <td class="p-4">{{ $offer->offer_date->format('Y-m-d') }}</td>
                                <td class="p-4">
                                    @if($offer->crmDeal)
                                        <div class="text-sm">
                                            <div class="font-semibold text-blue-600">{{ $offer->crmDeal->name }}</div>
                                            @if($offer->crmDeal->company)
                                                <div class="text-gray-600">{{ $offer->crmDeal->company->name }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right font-semibold">{{ number_format($offer->total_price, 2, ',', ' ') }} zł</td>
                                <td class="p-4 text-center">
                                    <div class="flex gap-1 justify-center">
                                        <a href="{{ route('offers.edit', $offer) }}" title="Edytuj ofertę" class="p-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 inline-flex items-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                        <form action="{{ route('offers.copy', $offer) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Kopiuj ofertę" class="p-1.5 bg-green-600 text-white rounded hover:bg-green-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
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
                    Brak ofert zarchiwizowanych
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
