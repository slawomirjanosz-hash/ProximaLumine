<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesy Produkcyjne</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    @include('parts.menu')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">📋 Procesy Produkcyjne</h1>
            <div class="flex gap-3">
                <a href="{{ route('recipes.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    ← Receptury
                </a>
                <a href="{{ route('processes.create') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    ➕ Nowy Proces
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($processes->isEmpty())
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-600 mb-4">Brak procesów produkcyjnych</p>
                <a href="{{ route('processes.create') }}" class="inline-block px-6 py-3 bg-green-600 text-white rounded hover:bg-green-700">
                    Utwórz pierwszy proces
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($processes as $process)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ $process->name }}</h3>
                            <p class="text-sm opacity-90 mt-1 text-black">Receptura: <span class="font-semibold text-blue-500">{{ $process->recipe->name }}</span></p>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ilość:</span>
                                    <span class="font-semibold">{{ $process->quantity }} szt.</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Koszt:</span>
                                    <span class="font-semibold text-green-600">{{ number_format($process->total_cost, 2) }} zł</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Data:</span>
                                    <span class="text-sm">{{ $process->created_at->format('d.m.Y H:i') }}</span>
                                </div>
                            </div>

                            @if($process->notes)
                                <div class="bg-gray-50 rounded p-2 mb-4">
                                    <p class="text-sm text-gray-700">{{ Str::limit($process->notes, 80) }}</p>
                                </div>
                            @endif

                            <div class="flex gap-2">
                                <a href="{{ route('processes.show', $process) }}" class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    👁️ Szczegóły
                                </a>
                                <a href="{{ route('processes.edit', $process) }}" class="px-3 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                    ✏️
                                </a>
                                <form action="{{ route('processes.destroy', $process) }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć ten proces?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
