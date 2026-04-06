<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Portfolio</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    @include('parts.menu')
    <main class="flex-1">
        <div class="relative w-full px-4 p-6">
            <a href="{{ route('offers') }}" class="absolute top-4 left-4 flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 shadow rounded-full text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition z-10">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' /></svg>
                Powrót
            </a>
            
            <h1 class="text-3xl font-bold mb-6 text-center mt-12">Portfolio</h1>
            
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
                                <th class="p-2 text-left text-xs whitespace-nowrap">Nr oferty</th>
                                <th class="p-2 text-left text-sm w-2/5">Nazwa</th>
                                <th class="p-2 text-left text-xs whitespace-nowrap">Data</th>
                                <th class="p-2 text-left text-xs w-1/4">Szansa CRM</th>
                                <th class="p-2 text-right text-sm whitespace-nowrap">Cena końcowa</th>
                                <th class="p-2 text-center text-xs w-20">Akcja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-2 text-xs whitespace-nowrap">{{ $offer->offer_number }}</td>
                                <td class="p-2 text-sm font-medium">{{ $offer->offer_title }}</td>
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
                                <td class="p-2 text-right font-bold text-sm whitespace-nowrap">{{ number_format($offer->total_price, 2, ',', ' ') }} zł</td>
                                <td class="p-2 text-center">
                                    <div class="flex gap-1 justify-center flex-nowrap">
                                        <form action="{{ route('offers.convertToProject', $offer) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Utworzyć projekt z tej oferty?')" title="Wygrana – utwórz projekt" class="p-1.5 bg-yellow-600 text-white rounded hover:bg-yellow-700 inline-flex items-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('offers.generateWord', $offer) }}" title="Generuj Word" class="p-1.5 bg-purple-600 text-white rounded hover:bg-purple-700 inline-flex items-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </a>
                                        <button type="button" onclick="openPdfPreview('{{ route('offers.generatePdf', $offer) }}')" title="Podgląd PDF" class="p-1.5 bg-red-600 text-white rounded hover:bg-red-700 inline-flex items-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </button>
                                        <a href="{{ route('offers.edit', $offer) }}" title="Edytuj ofertę" class="p-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 inline-flex items-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                        <form action="{{ route('offers.copy', $offer) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Kopiuj ofertę" class="p-1.5 bg-green-600 text-white rounded hover:bg-green-700 inline-flex items-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('offers.archive', $offer) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Czy na pewno przenieść ofertę do archiwum?')" title="Przenieś do archiwum" class="p-1.5 bg-gray-600 text-white rounded hover:bg-gray-700 inline-flex items-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
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
                    Brak ofert w portfolio
                </div>
            @endif
        </div>
    </main>

{{-- PDF PREVIEW MODAL --}}
<div id="pdf-preview-modal" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,0.75); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:10px; width:92vw; max-width:960px; height:90vh; display:flex; flex-direction:column; box-shadow:0 24px 64px rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
            <span style="font-weight:700; font-size:15px; color:#1f2937;">&#128196; Podgląd PDF</span>
            <div style="display:flex; gap:8px;">
                <a id="pdf-download-link" href="#" download style="padding:8px 16px; background:#2563eb; color:#fff; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none;">&#8595; Pobierz PDF</a>
                <button onclick="closePdfPreview()" style="padding:8px 14px; background:#ef4444; color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">&times; Zamknij</button>
            </div>
        </div>
        <iframe id="pdf-preview-iframe" src="" style="flex:1; border:none; border-radius:0 0 10px 10px;"></iframe>
    </div>
</div>

</body>
<script>
function openPdfPreview(url) {
    var modal = document.getElementById('pdf-preview-modal');
    var iframe = document.getElementById('pdf-preview-iframe');
    var dlLink = document.getElementById('pdf-download-link');
    iframe.src = url;
    dlLink.href = url + '?download=1';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closePdfPreview() {
    var modal = document.getElementById('pdf-preview-modal');
    var iframe = document.getElementById('pdf-preview-iframe');
    iframe.src = '';
    modal.style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePdfPreview();
});

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
