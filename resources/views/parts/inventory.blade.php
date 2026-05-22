<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Magazyn – Inwentaryzacja</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-2xl mx-auto mt-6 px-4 pb-10">

    <h2 class="text-2xl font-bold mb-1 text-gray-800">📋 Inwentaryzacja</h2>
    <p class="text-sm text-gray-500 mb-6">Skanuj kody QR/kreskowe, sprawdzaj stan i wprowadzaj korekty.</p>

    {{-- POLE SKANOWANIA --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5 mb-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Skanuj lub wpisz kod produktu:</label>
        <div class="flex gap-2">
            <input
                type="text"
                id="scan-input"
                autocomplete="off"
                autofocus
                placeholder="Zeskanuj kod lub wpisz nazwę…"
                class="flex-1 px-4 py-3 border-2 border-blue-300 rounded-lg text-base focus:outline-none focus:border-blue-500"
            >
            <button type="button" id="scan-btn"
                class="px-5 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold text-sm whitespace-nowrap">
                🔍 Szukaj
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">Skaner kodu automatycznie zatwierdza wyszukiwanie po zeskanowaniu kodu.</p>
    </div>

    {{-- WYNIK SKANOWANIA --}}
    <div id="product-card" class="hidden bg-white rounded-lg shadow border-2 border-blue-200 p-5 mb-6">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 id="product-name" class="text-xl font-bold text-gray-900"></h3>
                <p id="product-description" class="text-sm text-gray-500 mt-0.5"></p>
            </div>
            <span id="product-category" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded font-medium whitespace-nowrap ml-3"></span>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-5 text-sm">
            <div class="bg-gray-50 rounded p-3">
                <div class="text-xs text-gray-500 mb-1">Stan w systemie</div>
                <div class="text-3xl font-bold text-blue-700" id="product-quantity">0</div>
                <div class="text-xs text-gray-500" id="product-unit">szt.</div>
            </div>
            <div class="bg-gray-50 rounded p-3 space-y-1">
                <div><span class="text-xs text-gray-500">Lokalizacja:</span> <span id="product-location" class="font-medium text-gray-700">—</span></div>
                <div><span class="text-xs text-gray-500">Dostawca:</span> <span id="product-supplier" class="font-medium text-gray-700">—</span></div>
                <div><span class="text-xs text-gray-500">Kod:</span> <span id="product-qr" class="font-mono text-xs text-gray-600">—</span></div>
            </div>
        </div>

        {{-- KOREKTA --}}
        <div id="correction-section">
            <div class="border-t border-gray-200 pt-4">
                <p class="text-sm font-semibold text-gray-700 mb-3">Korekta stanu (jeśli liczba fizyczna różni się od stanu w systemie):</p>

                <div class="flex gap-3 items-end mb-3">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">Rzeczywista ilość (po przeliczeniu):</label>
                        <input type="number" id="correction-qty" min="0" value=""
                            class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-base focus:border-blue-500 focus:outline-none"
                            placeholder="Wpisz faktyczną ilość">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">Uwaga (opcjonalnie):</label>
                        <input type="text" id="correction-note"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="np. uszkodzone, zużyte…">
                    </div>
                </div>

                <div class="flex gap-2 flex-wrap">
                    <button type="button" id="btn-correct"
                        class="px-5 py-2.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-semibold text-sm">
                        ✏️ Zatwierdź korektę
                    </button>
                    <button type="button" id="btn-ok"
                        class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold text-sm">
                        ✅ Stan się zgadza – następny
                    </button>
                    <button type="button" id="btn-skip"
                        class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        ⏭ Pomiń
                    </button>
                </div>

                {{-- Wynik korekty --}}
                <div id="correction-result" class="hidden mt-3 p-3 rounded-lg text-sm font-semibold"></div>
            </div>
        </div>
    </div>

    {{-- BŁĄD SKANOWANIA --}}
    <div id="scan-error" class="hidden bg-red-50 border border-red-300 rounded-lg p-4 mb-6 text-red-700 text-sm font-semibold"></div>

    {{-- HISTORIA SESJI --}}
    <div id="session-history" class="hidden bg-white rounded-lg shadow border border-gray-200 p-5">
        <h4 class="font-semibold text-gray-800 mb-3">📝 Korekty w tej sesji</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="px-2 py-1.5 text-left">Produkt</th>
                        <th class="px-2 py-1.5 text-center">Przed</th>
                        <th class="px-2 py-1.5 text-center">Po</th>
                        <th class="px-2 py-1.5 text-center">Różnica</th>
                        <th class="px-2 py-1.5 text-left">Uwaga</th>
                        <th class="px-2 py-1.5 text-left">Czas</th>
                    </tr>
                </thead>
                <tbody id="history-tbody"></tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    const scanInput      = document.getElementById('scan-input');
    const scanBtn        = document.getElementById('scan-btn');
    const productCard    = document.getElementById('product-card');
    const scanError      = document.getElementById('scan-error');
    const correctionQty  = document.getElementById('correction-qty');
    const correctionNote = document.getElementById('correction-note');
    const btnCorrect     = document.getElementById('btn-correct');
    const btnOk          = document.getElementById('btn-ok');
    const btnSkip        = document.getElementById('btn-skip');
    const corrResult     = document.getElementById('correction-result');
    const sessionHistory = document.getElementById('session-history');
    const historyTbody   = document.getElementById('history-tbody');

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    let currentPart = null;
    let sessionCorrections = [];

    // --- Obsługa skanera: Enter po zebraniu znaków ---
    let scanBuffer = '';
    let scanTimeout = null;
    document.addEventListener('keydown', function (e) {
        // Jeśli focus jest w polu korekty lub uwagi – nie przechwytuj
        if (document.activeElement === correctionQty || document.activeElement === correctionNote) return;

        if (e.key === 'Enter') {
            if (scanBuffer.length > 0) {
                scanInput.value = scanBuffer.trim();
                scanBuffer = '';
                clearTimeout(scanTimeout);
                doScan(scanInput.value);
            }
        } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
            clearTimeout(scanTimeout);
            scanBuffer += e.key;
            scanTimeout = setTimeout(function () { scanBuffer = ''; }, 200);
        }
    });

    scanBtn.addEventListener('click', function () { doScan(scanInput.value); });
    scanInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); doScan(this.value); }
    });

    function doScan(code) {
        code = code.trim();
        if (!code) return;
        hideError();
        productCard.classList.add('hidden');
        corrResult.classList.add('hidden');

        fetch('{{ route("magazyn.inventory.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ code: code })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.found) {
                showError(data.message || 'Nie znaleziono produktu.');
                return;
            }
            currentPart = data;
            renderProductCard(data);
            scanInput.value = '';
            scanInput.focus();
        })
        .catch(function () { showError('Błąd połączenia z serwerem.'); });
    }

    function renderProductCard(p) {
        document.getElementById('product-name').textContent        = p.name;
        document.getElementById('product-description').textContent = p.description || '';
        document.getElementById('product-category').textContent    = p.category || '';
        document.getElementById('product-quantity').textContent    = p.quantity;
        document.getElementById('product-unit').textContent        = p.unit || 'szt.';
        document.getElementById('product-location').textContent    = p.location || '—';
        document.getElementById('product-supplier').textContent    = p.supplier || '—';
        document.getElementById('product-qr').textContent          = p.qr_code || '—';

        correctionQty.value  = p.quantity;
        correctionNote.value = '';
        corrResult.classList.add('hidden');
        productCard.classList.remove('hidden');

        // Przewiń do karty
        productCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        correctionQty.focus();
        correctionQty.select();
    }

    // --- Zatwierdź korektę ---
    btnCorrect.addEventListener('click', function () {
        if (!currentPart) return;
        const realQty = parseInt(correctionQty.value);
        if (isNaN(realQty) || realQty < 0) {
            alert('Podaj poprawną ilość (liczba ≥ 0).');
            correctionQty.focus();
            return;
        }

        fetch('{{ route("magazyn.inventory.correct") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                part_id: currentPart.id,
                real_quantity: realQty,
                note: correctionNote.value.trim()
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) { alert('Błąd podczas zapisywania korekty.'); return; }

            if (!data.changed) {
                showCorrResult('info', '✅ Stan się zgadza – brak zmian (' + data.quantity + ' ' + (currentPart.unit || 'szt.') + ')');
            } else {
                const diff = data.after - data.before;
                const sign = diff > 0 ? '+' : '';
                showCorrResult('ok', '✏️ Korekta zapisana: ' + data.before + ' → ' + data.after + ' ' + (currentPart.unit || 'szt.')
                    + ' (zmiana: ' + sign + diff + ')');

                // Zaktualizuj widok
                document.getElementById('product-quantity').textContent = data.after;
                currentPart.quantity = data.after;

                addHistoryRow(currentPart.name, data.before, data.after, diff, correctionNote.value.trim());
            }
        })
        .catch(function () { alert('Błąd połączenia.'); });
    });

    // --- Stan się zgadza ---
    btnOk.addEventListener('click', function () {
        if (!currentPart) return;
        showCorrResult('ok', '✅ Stan potwierdzony (' + currentPart.quantity + ' ' + (currentPart.unit || 'szt.') + ') – zeskanuj następny produkt.');
        currentPart = null;
        scanInput.value = '';
        scanInput.focus();
    });

    // --- Pomiń ---
    btnSkip.addEventListener('click', function () {
        productCard.classList.add('hidden');
        corrResult.classList.add('hidden');
        currentPart = null;
        scanInput.value = '';
        scanInput.focus();
    });

    function showCorrResult(type, msg) {
        corrResult.textContent = msg;
        corrResult.className = 'mt-3 p-3 rounded-lg text-sm font-semibold';
        if (type === 'ok') corrResult.classList.add('bg-green-50', 'text-green-800', 'border', 'border-green-200');
        else corrResult.classList.add('bg-blue-50', 'text-blue-800', 'border', 'border-blue-200');
        corrResult.classList.remove('hidden');
    }

    function showError(msg) {
        scanError.textContent = '❌ ' + msg;
        scanError.classList.remove('hidden');
    }

    function hideError() {
        scanError.classList.add('hidden');
    }

    function addHistoryRow(name, before, after, diff, note) {
        sessionCorrections.push({ name, before, after, diff, note, time: new Date().toLocaleTimeString('pl-PL') });
        sessionHistory.classList.remove('hidden');
        const sign = diff > 0 ? '+' : '';
        const diffClass = diff > 0 ? 'text-green-700 font-bold' : diff < 0 ? 'text-red-700 font-bold' : 'text-gray-500';
        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100 even:bg-gray-50';
        tr.innerHTML = '<td class="px-2 py-1.5 font-medium">' + escHtml(name) + '</td>'
            + '<td class="px-2 py-1.5 text-center">' + before + '</td>'
            + '<td class="px-2 py-1.5 text-center font-semibold">' + after + '</td>'
            + '<td class="px-2 py-1.5 text-center ' + diffClass + '">' + sign + diff + '</td>'
            + '<td class="px-2 py-1.5 text-gray-500">' + escHtml(note) + '</td>'
            + '<td class="px-2 py-1.5 text-gray-400 whitespace-nowrap">' + new Date().toLocaleTimeString('pl-PL') + '</td>';
        historyTbody.prepend(tr);
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

</body>
</html>
