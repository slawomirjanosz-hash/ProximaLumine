<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zrób nową Ofertę</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="h-10">
                <span class="text-xl font-bold">Nowa Oferta</span>
            </div>
        </div>
    </header>
    <main class="flex-1 p-6">
        <div class="max-w-5xl mx-auto bg-white rounded shadow p-6 relative">
            <a href="{{ route('offers') }}" class="absolute top-4 left-4 flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 shadow rounded-full text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition z-10">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' /></svg>
                Powrót
            </a>
            
            <h1 class="text-3xl font-bold mb-6 text-center mt-12">Tworzenie nowej oferty</h1>
            
            <form action="#" method="POST" class="space-y-6" onkeydown="return event.key != 'Enter';">
                @csrf
                
                <!-- Podstawowe informacje -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nr oferty</label>
                        <input type="text" name="offer_number" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tytuł oferty</label>
                        <input type="text" name="offer_title" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                        <input type="date" name="offer_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>

                <!-- Sekcja Usługi -->
                <div class="border border-gray-300 rounded">
                    <button type="button" class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition" onclick="toggleSection('services')">
                        <span class="font-semibold text-lg">Usługi</span>
                        <svg id="services-icon" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="services-content" class="p-4 hidden">
                        <table class="w-full mb-4">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left w-16">Nr</th>
                                    <th class="p-2 text-left">Nazwa</th>
                                    <th class="p-2 text-left">Dostawca</th>
                                    <th class="p-2 text-left w-32">Cena (zł)</th>
                                    <th class="p-2 w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="services-table">
                                <tr>
                                    <td class="p-2"><input type="number" class="w-full px-2 py-1 border rounded text-sm" value="1" readonly></td>
                                    <td class="p-2"><input type="text" name="services[0][name]" class="w-full px-2 py-1 border rounded text-sm"></td>
                                    <td class="p-2"><input type="text" name="services[0][supplier]" class="w-full px-2 py-1 border rounded text-sm"></td>
                                    <td class="p-2"><input type="number" step="0.01" name="services[0][price]" class="w-full px-2 py-1 border rounded text-sm price-input" data-section="services" onchange="calculateTotal('services')"></td>
                                    <td class="p-2"></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" onclick="addRow('services')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="services-total" class="font-bold text-lg">0.00 zł</span>
                        </div>
                    </div>
                </div>

                <!-- Sekcja Prace własne -->
                <div class="border border-gray-300 rounded">
                    <button type="button" class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition" onclick="toggleSection('works')">
                        <span class="font-semibold text-lg">Prace własne</span>
                        <svg id="works-icon" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="works-content" class="p-4 hidden">
                        <table class="w-full mb-4">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left w-16">Nr</th>
                                    <th class="p-2 text-left">Nazwa</th>
                                    <th class="p-2 text-left">Dostawca</th>
                                    <th class="p-2 text-left w-32">Cena (zł)</th>
                                    <th class="p-2 w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="works-table">
                                <tr>
                                    <td class="p-2"><input type="number" class="w-full px-2 py-1 border rounded text-sm" value="1" readonly></td>
                                    <td class="p-2"><input type="text" name="works[0][name]" class="w-full px-2 py-1 border rounded text-sm"></td>
                                    <td class="p-2"><input type="text" name="works[0][supplier]" class="w-full px-2 py-1 border rounded text-sm"></td>
                                    <td class="p-2"><input type="number" step="0.01" name="works[0][price]" class="w-full px-2 py-1 border rounded text-sm price-input" data-section="works" onchange="calculateTotal('works')"></td>
                                    <td class="p-2"></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" onclick="addRow('works')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="works-total" class="font-bold text-lg">0.00 zł</span>
                        </div>
                    </div>
                </div>

                <!-- Sekcja Materiały -->
                <div class="border border-gray-300 rounded">
                    <button type="button" class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition" onclick="toggleSection('materials')">
                        <span class="font-semibold text-lg">Materiały</span>
                        <svg id="materials-icon" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="materials-content" class="p-4 hidden">
                        <table class="w-full mb-4">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left w-16">Nr</th>
                                    <th class="p-2 text-left">Nazwa</th>
                                    <th class="p-2 text-left">Dostawca</th>
                                    <th class="p-2 text-left w-32">Cena (zł)</th>
                                    <th class="p-2 w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="materials-table">
                                <tr>
                                    <td class="p-2"><input type="number" class="w-full px-2 py-1 border rounded text-sm" value="1" readonly></td>
                                    <td class="p-2"><input type="text" name="materials[0][name]" class="w-full px-2 py-1 border rounded text-sm"></td>
                                    <td class="p-2"><input type="text" name="materials[0][supplier]" class="w-full px-2 py-1 border rounded text-sm"></td>
                                    <td class="p-2"><input type="number" step="0.01" name="materials[0][price]" class="w-full px-2 py-1 border rounded text-sm price-input" data-section="materials" onchange="calculateTotal('materials')"></td>
                                    <td class="p-2"></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" onclick="addRow('materials')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Dodaj wiersz</button>
                        <div class="mt-4 text-right">
                            <span class="font-semibold">Suma: </span>
                            <span id="materials-total" class="font-bold text-lg">0.00 zł</span>
                        </div>
                    </div>
                </div>

                <!-- Suma końcowa -->
                <div class="bg-gray-50 p-4 rounded border border-gray-300">
                    <div class="text-right">
                        <span class="text-xl font-semibold">Suma końcowa: </span>
                        <span id="grand-total" class="text-2xl font-bold text-blue-600">0.00 zł</span>
                    </div>
                </div>

                <!-- Miejsce docelowe oferty -->
                <div class="border-t pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gdzie ma wylądować oferta?</label>
                    <select name="destination" class="w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="portfolio">Portfolio</option>
                        <option value="inprogress">Oferty w toku</option>
                    </select>
                </div>

                <!-- Przycisk Zapisz -->
                <div class="text-center">
                    <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg text-lg font-semibold hover:bg-green-700 transition">
                        Zapisz ofertę
                    </button>
                </div>
            </form>
        </div>
    </main>
    <footer class="bg-white text-center py-4 mt-8 border-t text-gray-400 text-sm">
        Powered by ProximaLumine
    </footer>

    <script>
        let rowCounters = {
            services: 1,
            works: 1,
            materials: 1
        };

        function toggleSection(section) {
            const content = document.getElementById(section + '-content');
            const icon = document.getElementById(section + '-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        function addRow(section) {
            const table = document.getElementById(section + '-table');
            const rowCount = rowCounters[section];
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="p-2"><input type="number" class="w-full px-2 py-1 border rounded text-sm" value="${rowCount + 1}" readonly></td>
                <td class="p-2"><input type="text" name="${section}[${rowCount}][name]" class="w-full px-2 py-1 border rounded text-sm"></td>
                <td class="p-2"><input type="text" name="${section}[${rowCount}][supplier]" class="w-full px-2 py-1 border rounded text-sm"></td>
                <td class="p-2"><input type="number" step="0.01" name="${section}[${rowCount}][price]" class="w-full px-2 py-1 border rounded text-sm price-input" data-section="${section}" onchange="calculateTotal('${section}')"></td>
                <td class="p-2"><button type="button" onclick="removeRow(this, '${section}')" class="text-red-600 hover:text-red-800">✕</button></td>
            `;
            
            table.appendChild(row);
            rowCounters[section]++;
            updateRowNumbers(section);
        }

        function removeRow(button, section) {
            button.closest('tr').remove();
            updateRowNumbers(section);
            calculateTotal(section);
        }

        function updateRowNumbers(section) {
            const rows = document.querySelectorAll(`#${section}-table tr`);
            rows.forEach((row, index) => {
                row.querySelector('input[type="number"][readonly]').value = index + 1;
            });
        }

        function calculateTotal(section) {
            const inputs = document.querySelectorAll(`#${section}-table .price-input`);
            let total = 0;
            
            inputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });
            
            document.getElementById(section + '-total').textContent = total.toFixed(2) + ' zł';
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            const servicesInputs = document.querySelectorAll('#services-table .price-input');
            const worksInputs = document.querySelectorAll('#works-table .price-input');
            const materialsInputs = document.querySelectorAll('#materials-table .price-input');
            
            let grandTotal = 0;
            
            servicesInputs.forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });
            worksInputs.forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });
            materialsInputs.forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });
            
            document.getElementById('grand-total').textContent = grandTotal.toFixed(2) + ' zł';
        }
    </script>
</body>
</html>
