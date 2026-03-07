<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Diagnostyka eksportu projektu</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-5xl mx-auto mt-6 mb-10 bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Diagnostyka eksportu XLSX</h1>
            <p class="text-sm text-gray-600 mt-1">
                Projekt: <strong>{{ $project->project_number ?? ('#' . $project->id) }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('magazyn.projects.exportProductsXlsx', $project->id) }}" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-semibold">
                📊 Test eksportu
            </a>
            <a href="{{ route('magazyn.projects.exportProductsCsv', $project->id) }}" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm font-semibold">
                CSV awaryjny
            </a>
            <a href="{{ route('magazyn.projects.show', $project->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
                Powrót do projektu
            </a>
        </div>
    </div>

    <div class="mb-4 rounded border border-amber-300 bg-amber-50 p-4 text-amber-900 text-sm">
        Jeśli test XLSX na Railway się wywali, aplikacja automatycznie zwraca CSV z tymi samymi danymi.
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-collapse text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-3 py-2 text-left">Parametr</th>
                    <th class="border px-3 py-2 text-left">Wartość</th>
                </tr>
            </thead>
            <tbody>
                @foreach($diagnostics as $key => $value)
                    <tr>
                        <td class="border px-3 py-2 font-semibold">{{ $key }}</td>
                        <td class="border px-3 py-2">
                            @if(is_bool($value))
                                @if($value)
                                    <span class="text-green-700 font-semibold">true</span>
                                @else
                                    <span class="text-red-700 font-semibold">false</span>
                                @endif
                            @elseif(is_null($value))
                                <span class="text-gray-500">null</span>
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
