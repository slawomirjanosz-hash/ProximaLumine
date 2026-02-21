<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Magazyn – Przyjmij</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

{{-- Skopiowany kod z check.blade.php z modyfikacjami --}}
@php
	// Domyślne ustawienia jeśli nie ma w bazie
	if (!isset($catalogSettings) || !$catalogSettings) {
		$catalogSettings = (object)[
			'show_product' => true,
			'show_description' => true,
			'show_supplier' => true,
			'show_price' => true,
			'show_category' => true,
			'show_quantity' => true,
			'show_unit' => true,
			'show_minimum' => true,
			'show_location' => true,
			'show_user' => true,
			'show_actions' => true,
			'show_qr_code' => false,
			'show_qr_description' => true,
		];
	}
@endphp

@include('parts.menu')



@if(session('success'))
	<div class="max-w-6xl mx-auto mt-4 bg-green-100 text-green-800 p-2 rounded">
		{{ session('success') }}
	</div>
@endif

@if(session('error'))
	<div class="max-w-6xl mx-auto mt-4 bg-red-100 text-red-800 p-2 rounded">
		{{ session('error') }}
	</div>
@endif

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow mt-6">
	{{-- PRZYCISK TRYBU SKANOWANIA --}}
	<div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
		<button id="start-scanner-mode" class="px-4 py-2 bg-blue-600 text-white rounded text-lg font-semibold hover:bg-blue-700">
			📱 Przyjmij na magazyn skanerem
		</button>
		<span id="scanner-status" class="text-green-700 font-bold hidden">✓ Tryb skanowania aktywny - skanuj kody</span>
	</div>

	{{-- TABELA ZESKANOWANYCH PRODUKTÓW --}}
	<div id="scanner-table-container" class="mb-8"></div>

	{{-- PRZYCISK ZAAKCEPTUJ --}}
	<button id="accept-receive-btn" class="px-4 py-2 bg-green-600 text-white rounded text-lg font-semibold hidden hover:bg-green-700 mb-6">
		✓ Zaakceptuj przyjęcie
	</button>

	{{-- KATALOG PRODUKTÓW (ukryty w trybie skanowania) --}}
	<div id="catalog-container">
		@include('parts.check', ['bulkActions' => false, 'showExport' => false, 'isPartial' => true])
	</div>
</div>

<audio id="error-sound" preload="auto">
	<source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSyBzvLXijcIGWi77eeeTRAMUKfj8LZjHAY4ktfyzHksBSh+zPLckTsIEVyz6O2rVxMGQ5zd8sFmIgQpfs3y25A6CA9csujtq1cTBkOc3fLBZiIEKX7N8tuQOggPXLPo7atXEwZDnN3ywWYiBCl+zfLbkDoID1yz6O2rVxMGQ5zd8sFmIgQpfs3y25A6CA9csujtq1cTBkOc3fLBZiIEKX7N8tuQOggPXLLo7atXEwZDnN3ywWYiBCl+zfLbkDoID1yy6O2rVxMGQ5zd8sFmIgQpfs3y25A6CA9csujtq1cTBkOc3fLBZiIEKX7N8tuQOggPXLLo7atXEwZDnN3ywWYiBCl+zfLbkDoID1yy6O2rVxMGQ5zd8sFmIgQpfs3y25A6CA9csujtq1cTBkOc3fLBZiIEKX7N8tuQOggPXLLo7atXEwZDnN3ywWYiBCl+zfLbkDoID1yy6O2rVxMGQ5zd8sFmIgQpfs3y25A6CA9csujtq1cTBkOc3fLBZiIEKX7N8tuQOggPXLLo7atXEwZDnN3ywWYiBCl+zfLbkDoID1yy6O2rVxMGQ5zd8sFmIgQpfs3y25A6CA9csujtq1cTBkOc3fLBZiIEKX7N8tuQOggPXLLo7atXEwZDnN3ywWYiBCl+zfLbkDoID1yy6O2rVxMGQ5zd8sFmIgQpfs3y25A6CA9csujtq1cTBkOc3fLBZiIEKX7N8tuQOggPXLLo7atXEwZDnN3ywWYiBCl+zfLbkDoID1yy6O2rVxMGQ5zd8sFmIgQpfs3y25A6CA9csujtq1cTBkOc3fLBZiIEKX7N8tuQOggPXLLo7atXEwYAAAAA=" type="audio/wav">
</audio>

<script>
// TRYB SKANOWANIA
document.addEventListener('DOMContentLoaded', function() {
	const scannerBtn = document.getElementById('start-scanner-mode');
	const scannerStatus = document.getElementById('scanner-status');
	const scannerTableContainer = document.getElementById('scanner-table-container');
	const acceptBtn = document.getElementById('accept-receive-btn');
	const errorSound = document.getElementById('error-sound');
	const catalogContainer = document.getElementById('catalog-container');
	let scannerMode = false;
	let scannedProducts = {};
	let scanBuffer = '';
	let scanTimeout = null;

	scannerBtn.addEventListener('click', function() {
		scannerMode = true;
		scannerStatus.classList.remove('hidden');
		scannerBtn.disabled = true;
		scannerBtn.classList.add('opacity-50', 'cursor-not-allowed');
		catalogContainer.classList.add('hidden');
		scannerTableContainer.innerHTML = `
			<h3 class="text-lg font-bold mb-3">Zeskanowane produkty:</h3>
			<table class='w-full border border-collapse text-sm' id='scanner-table'>
				<thead class="bg-gray-100">
					<tr>
						<th class="border p-2">Kod</th>
						<th class="border p-2">Nazwa produktu</th>
						<th class="border p-2">Ilość</th>
						<th class="border p-2">Akcja</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>`;
		acceptBtn.classList.remove('hidden');
		document.body.focus();
	});

	// Obsługa skanowania kodów (nasłuchiwanie na klawiaturę)
	document.addEventListener('keypress', function(e) {
		if (!scannerMode) return;
		
		// Zbieraj znaki z klawiatury
		clearTimeout(scanTimeout);
		
		if (e.key === 'Enter') {
			// Koniec skanowania
			if (scanBuffer.length > 0) {
				processScannedCode(scanBuffer.trim());
				scanBuffer = '';
			}
		} else {
			// Dodaj znak do bufora
			scanBuffer += e.key;
			
			// Reset bufora po 100ms bezczynności (na wypadek ręcznego wpisywania)
			scanTimeout = setTimeout(() => {
				scanBuffer = '';
			}, 100);
		}
	});

	function processScannedCode(code) {
		if (!code) return;
		
		// Szukaj produktu po kodzie w danych z tabeli
		const allRows = document.querySelectorAll('#catalog-container tbody tr[data-qr-code]');
		const row = Array.from(allRows).find(tr => {
			const qrCode = (tr.getAttribute('data-qr-code') || '').toLowerCase();
			return qrCode === code.toLowerCase();
		});

		if (row) {
			const prodName = row.getAttribute('data-name') || 'Nieznany produkt';
			const checkbox = row.querySelector('.part-checkbox');
			const prodId = checkbox ? checkbox.value : null;
			
			if (!scannedProducts[code]) {
				scannedProducts[code] = { 
					id: prodId,
					name: prodName, 
					qty: 1 
				};
			} else {
				scannedProducts[code].qty++;
			}
			renderScannerTable();
			
			// Dźwięk sukcesu (opcjonalnie)
			playBeep(800, 100);
		} else {
			// Nie znaleziono produktu
			errorSound.play();
			showAlert('error', `Nie znaleziono produktu o kodzie: ${code}`);
			playBeep(200, 500);
		}
	}

	function renderScannerTable() {
		const tbody = document.querySelector('#scanner-table tbody');
		if (!tbody) return;
		
		tbody.innerHTML = '';
		Object.entries(scannedProducts).forEach(([code, prod]) => {
			const tr = document.createElement('tr');
			tr.innerHTML = `
				<td class='border p-2 font-mono text-xs'>${code}</td>
				<td class='border p-2'>${prod.name}</td>
				<td class='border p-2 text-center font-bold'>${prod.qty}</td>
				<td class='border p-2 text-center'>
					<button class='bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600' onclick='removeScannerProduct("${code}")'>
						Usuń
					</button>
				</td>
			`;
			tbody.appendChild(tr);
		});
	}

	// Funkcja globalna do usuwania produktu
	window.removeScannerProduct = function(code) {
		delete scannedProducts[code];
		renderScannerTable();
	};

	acceptBtn.addEventListener('click', function() {
		if (Object.keys(scannedProducts).length === 0) {
			showAlert('error', 'Brak zeskanowanych produktów!');
			return;
		}

		// Wyślij dane do backendu
		const productsArray = Object.entries(scannedProducts).map(([code, prod]) => ({
			id: prod.id,
			name: prod.name,
			code: code,
			quantity: prod.qty
		}));

		fetch('{{ route("magazyn.parts.bulkAdd") }}', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
			},
			body: JSON.stringify({ products: productsArray })
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				showAlert('success', `Przyjęto ${Object.keys(scannedProducts).length} produktów na magazyn!`);
				scannedProducts = {};
				renderScannerTable();
				scannerMode = false;
				scannerStatus.classList.add('hidden');
				scannerBtn.disabled = false;
				scannerBtn.classList.remove('opacity-50', 'cursor-not-allowed');
				acceptBtn.classList.add('hidden');
				scannerTableContainer.innerHTML = '';
				catalogContainer.classList.remove('hidden');
				
				// Odśwież stronę po 2s
				setTimeout(() => location.reload(), 2000);
			} else {
				showAlert('error', data.message || 'Błąd podczas przyjmowania produktów');
			}
		})
		.catch(error => {
			showAlert('error', 'Błąd połączenia: ' + error.message);
		});
	});

	function showAlert(type, message) {
		const alertDiv = document.createElement('div');
		alertDiv.className = `max-w-6xl mx-auto mt-4 p-3 rounded ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
		alertDiv.textContent = message;
		document.body.insertBefore(alertDiv, document.body.firstChild);
		setTimeout(() => alertDiv.remove(), 5000);
	}

	function playBeep(frequency, duration) {
		try {
			const audioContext = new (window.AudioContext || window.webkitAudioContext)();
			const oscillator = audioContext.createOscillator();
			const gainNode = audioContext.createGain();
			
			oscillator.connect(gainNode);
			gainNode.connect(audioContext.destination);
			
			oscillator.frequency.value = frequency;
			oscillator.type = 'sine';
			
			gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
			gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration / 1000);
			
			oscillator.start(audioContext.currentTime);
			oscillator.stop(audioContext.currentTime + duration / 1000);
		} catch(e) {
			console.log('Audio API nie jest dostępne');
		}
	}

	// Ukryj przyciski eksportu
	document.getElementById('btn-download-xlsx')?.remove();
	document.getElementById('btn-download-word')?.remove();
	document.getElementById('csv-export-link')?.remove();
	document.getElementById('view-selected-btn')?.remove();
	document.getElementById('bulk-delete-form')?.remove();
});
</script>

</body>
</html>
