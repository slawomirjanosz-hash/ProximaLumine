<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ustawienia CRM</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

@if(session('success'))
    <div class="max-w-7xl mx-auto mt-4 bg-green-100 text-green-800 p-3 rounded">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="max-w-7xl mx-auto mt-4 bg-red-100 text-red-800 p-3 rounded">{{ session('error') }}</div>
@endif

<div class="max-w-7xl mx-auto mt-6 mb-12">
    
    <!-- NAGŁÓWEK -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('crm') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 shadow rounded-full text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition mr-4">
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' /></svg>
                    Powrót do CRM
                </a>
                <h1 class="text-3xl font-bold text-gray-800">⚙️ Ustawienia CRM</h1>
            </div>
        </div>
    </div>

    <!-- ZARZĄDZANIE TYPAMI KLIENTÓW -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Typy Klientów</h2>
        <p class="text-gray-600 mb-4">Zarządzaj typami klientów, kolorami i możliwością dodawania nowych typów.</p>
        <table class="w-full border-collapse mb-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">Nazwa typu</th>
                    <th class="border p-2 text-left">Slug</th>
                    <th class="border p-2 text-left">Kolor</th>
                    <th class="border p-2">Akcje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customerTypes as $type)
                    <tr class="hover:bg-gray-50">
                        <td class="border p-2 font-semibold">{{ $type->name }}</td>
                        <td class="border p-2"><code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $type->slug }}</code></td>
                        <td class="border p-2">
                            <span class="inline-block px-3 py-1 rounded text-white" style="background-color: {{ $type->color }};">{{ $type->color }}</span>
                        </td>
                        <td class="border p-2 text-center">
                            <button onclick="editCustomerType({{ $type->id }})" class="text-blue-600 hover:underline">✏️ Edytuj</button>
                            <form action="{{ route('crm.customer-types.destroy', $type->id) }}" method="POST" class="inline" onsubmit="return confirm('Usunąć typ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline ml-2">🗑️ Usuń</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button onclick="showAddCustomerTypeModal()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">➕ Dodaj Nowy Typ</button>
    </div>

    <!-- MODAL DODAWANIA/EDYCJI TYPU KLIENTA -->
    <div id="customer-type-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4" id="customer-type-modal-title">Dodaj Typ Klienta</h3>
            <form id="customer-type-form" method="POST" action="{{ route('crm.customer-types.store') }}">
                @csrf
                <input type="hidden" id="customer-type-method" name="_method" value="">
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Nazwa typu *</label>
                    <input type="text" name="name" id="customer-type-name" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Slug (unikalny identyfikator) *</label>
                    <input type="text" name="slug" id="customer-type-slug" required class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Np: klient, partner, konkurencja (bez spacji, małe litery)</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Kolor (hex) *</label>
                    <input type="color" name="color" id="customer-type-color" required class="w-full border rounded px-3 py-2 h-10">
                    <div class="mt-2">
                        <p class="text-xs text-gray-600 mb-2">Szybki wybór koloru:</p>
                        <div class="flex gap-2 flex-wrap">
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#dc2626'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #dc2626;" title="Czerwony"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#2563eb'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #2563eb;" title="Niebieski"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#eab308'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #eab308;" title="Żółty"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#16a34a'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #16a34a;" title="Zielony"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#a855f7'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #a855f7;" title="Fioletowy"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#f97316'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #f97316;" title="Pomarańczowy"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#ec4899'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #ec4899;" title="Różowy"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#06b6d4'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #06b6d4;" title="Turkusowy"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#8b4513'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #8b4513;" title="Brązowy"></button>
                            <button type="button" onclick="document.getElementById('customer-type-color').value='#64748b'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #64748b;" title="Szary"></button>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeCustomerTypeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showAddCustomerTypeModal() {
        document.getElementById('customer-type-modal-title').textContent = 'Dodaj Typ Klienta';
        document.getElementById('customer-type-form').action = '{{ route("crm.customer-types.store") }}';
        document.getElementById('customer-type-method').value = '';
        document.getElementById('customer-type-name').value = '';
        document.getElementById('customer-type-slug').value = '';
        document.getElementById('customer-type-slug').readOnly = false;
        document.getElementById('customer-type-color').value = '#3b82f6';
        document.getElementById('customer-type-modal').classList.remove('hidden');
    }
    function editCustomerType(id) {
        fetch(`/crm/customer-types/${id}`)
            .then(res => res.json())
            .then(type => {
                document.getElementById('customer-type-modal-title').textContent = 'Edytuj Typ Klienta';
                document.getElementById('customer-type-form').action = `/crm/customer-types/${id}`;
                document.getElementById('customer-type-method').value = 'PUT';
                document.getElementById('customer-type-name').value = type.name;
                document.getElementById('customer-type-slug').value = type.slug;
                document.getElementById('customer-type-slug').readOnly = true;
                document.getElementById('customer-type-color').value = type.color;
                document.getElementById('customer-type-modal').classList.remove('hidden');
            });
    }
    function closeCustomerTypeModal() {
        document.getElementById('customer-type-modal').classList.add('hidden');
    }
    </script>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Etapy Szans Sprzedażowych</h2>
        <p class="text-gray-600 mb-4">Zarządzaj etapami w lejku sprzedażowym</p>
        
        <table class="w-full border-collapse mb-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">Kolejność</th>
                    <th class="border p-2 text-left">Nazwa etapu</th>
                    <th class="border p-2 text-left">Slug</th>
                    <th class="border p-2 text-left">Kolor</th>
                    <th class="border p-2 text-center">Aktywny</th>
                    <th class="border p-2 text-center">Zakończenie Lejka</th>
                    <th class="border p-2">Akcje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($crmStages as $stage)
                    <tr class="hover:bg-gray-50">
                        <td class="border p-2">{{ $stage->order }}</td>
                        <td class="border p-2 font-semibold">{{ $stage->name }}</td>
                        <td class="border p-2"><code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $stage->slug }}</code></td>
                        <td class="border p-2">
                            <span class="inline-block px-3 py-1 rounded text-white" style="background-color: {{ $stage->color }};">{{ $stage->color }}</span>
                        </td>
                        <td class="border p-2 text-center">
                            @if($stage->is_active)
                                <span class="text-green-600">✓ Tak</span>
                            @else
                                <span class="text-red-600">✗ Nie</span>
                            @endif
                        </td>
                        <td class="border p-2 text-center">
                            @if($stage->is_closed ?? false)
                                <span class="text-green-600">✓ Tak</span>
                            @else
                                <span class="text-gray-600">✗ Nie</span>
                            @endif
                        </td>
                        <td class="border p-2 text-center">
                            <button onclick="editStage({{ $stage->id }})" class="text-blue-600 hover:underline">✏️ Edytuj</button>
                            @if(!in_array($stage->slug, ['wygrana', 'przegrana']))
                                <form action="{{ route('crm.stage.delete', $stage->id) }}" method="POST" class="inline" onsubmit="return confirm('Usunąć etap?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline ml-2">🗑️ Usuń</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <button onclick="showAddStageModal()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">➕ Dodaj Nowy Etap</button>
    </div>
</div>

<!-- MODAL DODAWANIA/EDYCJI ETAPU -->
<div id="stage-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-xl font-bold mb-4" id="stage-modal-title">Dodaj Etap</h3>
        <form id="stage-form" method="POST" action="{{ route('crm.stage.add') }}">
            @csrf
            <input type="hidden" id="stage-method" name="_method" value="">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Nazwa etapu *</label>
                <input type="text" name="name" id="stage-name" required class="w-full border rounded px-3 py-2">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Slug (unikalny identyfikator) *</label>
                <input type="text" name="slug" id="stage-slug" required class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Np: nowy_etap, pierwszy_kontakt (bez spacji, małe litery)</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Kolor (hex) *</label>
                <input type="color" name="color" id="stage-color" required class="w-full border rounded px-3 py-2 h-10">
                <div class="mt-2">
                    <p class="text-xs text-gray-600 mb-2">Szybki wybór koloru:</p>
                    <div class="flex gap-2 flex-wrap">
                        <button type="button" onclick="document.getElementById('stage-color').value='#dc2626'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #dc2626;" title="Czerwony"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#2563eb'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #2563eb;" title="Niebieski"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#eab308'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #eab308;" title="Żółty"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#16a34a'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #16a34a;" title="Zielony"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#a855f7'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #a855f7;" title="Fioletowy"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#f97316'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #f97316;" title="Pomarańczowy"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#ec4899'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #ec4899;" title="Różowy"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#06b6d4'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #06b6d4;" title="Turkusowy"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#8b4513'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #8b4513;" title="Brązowy"></button>
                        <button type="button" onclick="document.getElementById('stage-color').value='#64748b'" class="w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-600" style="background-color: #64748b;" title="Szary"></button>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Kolejność *</label>
                <input type="number" name="order" id="stage-order" required min="0" class="w-full border rounded px-3 py-2">
            </div>
            
            <div class="mb-4" id="stage-active-field" style="display:none;">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="stage-is-active" value="1" class="w-4 h-4">
                    <span class="text-sm font-semibold">Aktywny</span>
                </label>
            </div>
            <div class="mb-4" id="stage-closed-field">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_closed" id="stage-is-closed" value="1" class="w-4 h-4">
                    <span class="text-sm font-semibold">Zakończenie Lejka</span>
                </label>
                <p class="text-xs text-gray-500 mt-1">Etapy oznaczone jako "Zakończenie Lejka" kończą proces sprzedaży (np. wygrana, przegrana, rezygnacja)</p>
            </div>
            
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeStageModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddStageModal() {
    document.getElementById('stage-modal-title').textContent = 'Dodaj Nowy Etap';
    document.getElementById('stage-form').action = '{{ route("crm.stage.add") }}';
    document.getElementById('stage-method').value = '';
    document.getElementById('stage-name').value = '';
    document.getElementById('stage-slug').value = '';
    document.getElementById('stage-slug').readOnly = false;
    document.getElementById('stage-color').value = '#3b82f6';
    document.getElementById('stage-order').value = {{ $crmStages->max('order') + 1 }};
    document.getElementById('stage-is-closed').checked = false;
    document.getElementById('stage-active-field').style.display = 'none';
    document.getElementById('stage-closed-field').style.display = 'block';
    document.getElementById('stage-modal').classList.remove('hidden');
}

function editStage(id) {
    fetch(`/crm/stage/${id}/edit`)
        .then(res => res.json())
        .then(stage => {
            document.getElementById('stage-modal-title').textContent = 'Edytuj Etap';
            document.getElementById('stage-form').action = `/crm/stage/${id}`;
            document.getElementById('stage-method').value = 'PUT';
            document.getElementById('stage-name').value = stage.name;
            document.getElementById('stage-slug').value = stage.slug;
            document.getElementById('stage-slug').readOnly = true;
            document.getElementById('stage-color').value = stage.color;
            document.getElementById('stage-order').value = stage.order;
            document.getElementById('stage-is-active').checked = stage.is_active == 1;
            document.getElementById('stage-active-field').style.display = 'block';
            document.getElementById('stage-is-closed').checked = stage.is_closed == 1;
            document.getElementById('stage-closed-field').style.display = 'block';
            document.getElementById('stage-modal').classList.remove('hidden');
        });
}

function closeStageModal() {
    document.getElementById('stage-modal').classList.add('hidden');
}
</script>

</body>
</html>
