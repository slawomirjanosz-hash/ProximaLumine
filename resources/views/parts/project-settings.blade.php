<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ustawienia projektów</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-6xl mx-auto mt-6">
    <a href="{{ route('magazyn.projects') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow transition-all text-gray-700 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Powrót do projektów
    </a>
</div>

{{-- KOMUNIKATY --}}
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
    
    <h2 class="text-xl font-bold mb-6">Ustawienia projektów</h2>
    
    <div class="space-y-4">
        
        {{-- SEKCJA: LISTY PROJEKTOWE --}}
        <div class="bg-white rounded shadow border">
            <button type="button" class="collapsible-btn w-full flex items-center gap-2 p-6 cursor-pointer hover:bg-gray-50" data-target="project-lists-content">
                <span class="toggle-arrow text-lg">▶</span>
                <h3 class="text-lg font-semibold">📋 Listy projektowe</h3>
            </button>
            <div id="project-lists-content" class="collapsible-content hidden p-6 border-t">
                
                {{-- Przycisk dodania nowej listy --}}
                <div class="mb-6">
                    <button type="button" id="btn-add-new-list" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Utwórz nową listę projektową
                    </button>
                </div>

                {{-- Formularz tworzenia nowej listy (ukryty domyślnie) --}}
                <div id="new-list-form" class="hidden mb-6 p-4 bg-gray-50 rounded border">
                    <h4 class="font-semibold mb-4">Nowa lista projektowa</h4>
                    <form method="POST" action="{{ route('magazyn.projects.lists.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium mb-2">Nazwa listy *</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 border rounded" placeholder="Np. Lista standardowa, Lista premium, itp.">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Opis</label>
                            <textarea name="description" class="w-full px-3 py-2 border rounded" rows="3" placeholder="Opcjonalny opis listy"></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded">
                                Zapisz listę
                            </button>
                            <button type="button" id="btn-cancel-new-list" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded">
                                Anuluj
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Lista istniejących list projektowych --}}
                <div>
                    <h4 class="font-semibold mb-3">Istniejące listy projektowe</h4>
                    
                    @if(isset($projectLists) && count($projectLists) > 0)
                        <div class="border rounded overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="text-left p-3 border-b">Nazwa</th>
                                        <th class="text-left p-3 border-b" style="width: 120px;">Twórca</th>
                                        <th class="text-left p-3 border-b" style="width: 150px;">Utworzono</th>
                                        <th class="text-center p-3 border-b" style="width: 100px;">Produkty</th>
                                        <th class="text-center p-3 border-b" style="width: 350px;">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projectLists as $index => $list)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3">
                                                <span class="font-medium">{{ $list->name }}</span>
                                                @if($list->description)
                                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($list->description, 50) }}</p>
                                                @endif
                                            </td>
                                            <td class="p-3 text-sm">
                                                @if($list->creator)
                                                    <span title="{{ $list->creator->name }}">{{ $list->creator->short_name ?? Str::limit($list->creator->name, 8) }}</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-sm">{{ $list->created_at->format('d.m.Y H:i') }}</td>
                                            <td class="p-3 text-center text-sm">{{ $list->items_count }}</td>
                                            <td class="p-3">
                                                <div class="flex gap-2 justify-center flex-wrap">
                                                    <a href="{{ route('magazyn.projects.lists.edit', $list) }}" 
                                                       class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm" title="Edytuj">
                                                        ✏️
                                                    </a>
                                                    <form method="POST" action="{{ route('magazyn.projects.lists.destroy', $list) }}" 
                                                          class="inline"
                                                          onsubmit="return confirm('Czy na pewno chcesz usunąć tę listę?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm" title="Usuń">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('magazyn.projects.lists.moveUp', $list) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded text-sm {{ $index > 0 ? 'bg-gray-500 hover:bg-gray-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}" {{ $index > 0 ? '' : 'disabled' }} title="Przesuń w górę">
                                                            ⬆️
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('magazyn.projects.lists.moveDown', $list) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded text-sm {{ $index < count($projectLists) - 1 ? 'bg-gray-500 hover:bg-gray-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}" {{ $index < count($projectLists) - 1 ? '' : 'disabled' }} title="Przesuń w dół">
                                                            ⬇️
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
                        <p class="text-gray-600 p-4 bg-gray-50 rounded">
                            Brak utworzonych list projektowych. Kliknij przycisk powyżej, aby utworzyć pierwszą listę.
                        </p>
                    @endif
                </div>
                
            </div>
        </div>

        {{-- SEKCJA: INNE USTAWIENIA (placeholder na przyszłość) --}}
        <div class="bg-white rounded shadow border">
            <button type="button" class="collapsible-btn w-full flex items-center gap-2 p-6 cursor-pointer hover:bg-gray-50" data-target="other-settings-content">
                <span class="toggle-arrow text-lg">▶</span>
                <h3 class="text-lg font-semibold">⚙️ Ogólne ustawienia projektów</h3>
            </button>
            <div id="other-settings-content" class="collapsible-content hidden p-6 border-t">
                <p class="text-gray-600">W przyszłości tutaj będą dostępne dodatkowe ustawienia dla projektów.</p>
            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Automatycznie rozwiń sekcję "Listy projektowe"
    const projectListsContent = document.getElementById('project-lists-content');
    const projectListsBtn = document.querySelector('[data-target="project-lists-content"]');
    if (projectListsContent && projectListsBtn) {
        projectListsContent.classList.remove('hidden');
        const arrow = projectListsBtn.querySelector('.toggle-arrow');
        if (arrow) arrow.textContent = '▼';
    }
    
    // Collapsible sections
    const collapsibleBtns = document.querySelectorAll('.collapsible-btn');
    collapsibleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const content = document.getElementById(targetId);
            const arrow = this.querySelector('.toggle-arrow');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                arrow.textContent = '▼';
            } else {
                content.classList.add('hidden');
                arrow.textContent = '▶';
            }
        });
    });

    // Show/hide new list form
    const btnAddNewList = document.getElementById('btn-add-new-list');
    const newListForm = document.getElementById('new-list-form');
    const btnCancelNewList = document.getElementById('btn-cancel-new-list');

    if (btnAddNewList && newListForm) {
        btnAddNewList.addEventListener('click', function() {
            newListForm.classList.remove('hidden');
            btnAddNewList.classList.add('hidden');
        });
    }

    if (btnCancelNewList && newListForm) {
        btnCancelNewList.addEventListener('click', function() {
            newListForm.classList.add('hidden');
            btnAddNewList.classList.remove('hidden');
        });
    }

});
</script>

</body>
</html>
