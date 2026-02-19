<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Harmonogram - {{ $project->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow mt-6">
    
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-2">📊 {{ $project->name }}</h2>
        <p class="text-sm text-gray-600">Nr projektu: {{ $project->project_number }}</p>
        <p class="text-xs text-gray-500 mt-2">Widok publiczny - tylko do odczytu</p>
    </div>

    {{-- Gantt Frappe --}}
    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
        <h3 class="text-lg font-bold text-blue-800 mb-4">📊 Harmonogram projektu</h3>
        
        <div class="mb-4 flex gap-2 items-center flex-wrap">
            <label class="text-sm font-semibold text-gray-700">Widok:</label>
            <button class="frappe-view-btn bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm" data-mode="Quarter Day">Ćwierć dnia</button>
            <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Half Day">Pół dnia</button>
            <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Day">Dzień</button>
            <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Week">Tydzień</button>
            <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Month">Miesiąc</button>
        </div>

        <div id="frappe-gantt"></div>

        <div id="frappe-task-list" class="mt-8"></div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.0/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.0/dist/frappe-gantt.min.js"></script>
<style>
    #frappe-gantt { 
        min-height: 320px; 
        min-width: 100%; 
        background-color: white;
    }
    .gantt-container {
        background-color: white !important;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Gantt === 'undefined') {
        console.error('❌ Frappe Gantt nie został załadowany z CDN!');
        document.getElementById('frappe-gantt').innerHTML = '<div class="text-red-500 p-4">Błąd: Biblioteka Frappe Gantt nie została załadowana.</div>';
        return;
    }
    
    const TOKEN = '{{ $token }}';
    let frappeGanttInstance = null;
    let frappeTasks = [];
    
    function parseDate(dateStr) {
        if (!dateStr) return new Date();
        if (dateStr instanceof Date) return dateStr;
        const parts = dateStr.toString().split('-');
        if (parts.length === 3) {
            return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        }
        return new Date(dateStr);
    }
    
    function formatDateForInput(date) {
        if (!(date instanceof Date)) date = new Date(date);
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }
    
    function loadTasksFromDB() {
        return fetch(`/api/public/gantt/${TOKEN}`)
        .then(response => response.json())
        .then(tasks => {
            console.log('📥 Otrzymano dane z API (publiczny):', tasks);
            console.log('📋 Typ danych:', typeof tasks, Array.isArray(tasks) ? 'TABLICA' : 'OBIEKT');
            
            // Konwersja obiektu na tablicę jeśli potrzebne (podobny problem jak w głównym widoku)
            let tasksArray = Array.isArray(tasks) ? tasks : Object.values(tasks);
            console.log('📦 Po konwersji:', tasksArray);
            
            // Filtruj tylko zadania z id
            tasksArray = tasksArray.filter(t => t && t.id);
            console.log('✅ Zadania z id:', tasksArray.length);
            
            frappeTasks = tasksArray.map(t => ({
                id: t.id.toString(),
                name: t.name,
                start: parseDate(t.start),
                end: parseDate(t.end),
                progress: t.progress || 0,
                dependencies: t.dependencies || ''
            }));
            console.log('✅ Załadowano zadania z bazy:', frappeTasks);
        })
        .catch(error => {
            console.error('❌ Błąd ładowania zadań:', error);
            frappeTasks = [];
        });
    }
    
    function renderTaskList() {
        const container = document.getElementById('frappe-task-list');
        if (!frappeTasks.length) { container.innerHTML = ''; return; }
        let html = '<h4 class="text-lg font-bold mb-2">Lista zadań</h4>';
        html += '<ul class="divide-y divide-gray-200">';
        frappeTasks.forEach((task) => {
            const end = task.end instanceof Date ? task.end : parseDate(task.end);
            html += `<li class="flex items-center justify-between py-2">
                <div>
                    <span class="font-semibold">${task.name}</span>
                    <span class="ml-2 text-xs text-gray-500">(koniec: ${formatDateForInput(end)})</span>
                </div>
            </li>`;
        });
        html += '</ul>';
        container.innerHTML = html;
    }
    
    function renderGantt() {
        if (frappeTasks.length === 0) {
            document.getElementById('frappe-gantt').innerHTML = '<div class="text-gray-500 p-4 text-center">Brak zadań w harmonogramie.</div>';
            document.getElementById('frappe-task-list').innerHTML = '';
            return;
        }
        try {
            document.getElementById('frappe-gantt').innerHTML = '';
            let ganttConfig = {
                header_height: 50,
                column_width: 30,
                step: 24,
                view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
                bar_height: 20,
                bar_corner_radius: 3,
                arrow_curve: 5,
                padding: 18,
                view_mode: 'Month',
                date_format: 'YYYY-MM-DD',
                language: 'en',
                readonly: true,
                custom_popup_html: function(task) {
                    let startDate = task._start || task.start;
                    let endDate = task._end || task.end;
                    let start = startDate ? new Date(startDate).toLocaleDateString('pl-PL') : 'Brak';
                    let end = endDate ? new Date(endDate).toLocaleDateString('pl-PL') : 'Brak';
                    let duration = (startDate && endDate) ? Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24)) + 1 : '-';
                    const depText = task.dependencies ? frappeTasks.find(t => t.id === task.dependencies)?.name || 'Nieznane' : 'Brak';
                    return '<div style="padding: 10px;"><h5 style="margin: 0 0 10px 0; font-weight: bold;">' + (task.name || 'Brak') + '</h5><p style="margin: 5px 0;"><strong>Start:</strong> ' + start + '</p><p style="margin: 5px 0;"><strong>Koniec:</strong> ' + end + '</p><p style="margin: 5px 0;"><strong>Czas trwania:</strong> ' + duration + ' dni</p><p style="margin: 5px 0;"><strong>Postęp:</strong> ' + (task.progress ?? '-') + '%</p><p style="margin: 5px 0;"><strong>Zależność:</strong> ' + depText + '</p></div>';
                },
                on_view_change: function(mode) {
                    document.querySelectorAll('.frappe-view-btn').forEach(btn => {
                        if (btn.dataset.mode === mode) {
                            btn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        } else {
                            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                            btn.classList.add('bg-blue-500', 'hover:bg-blue-600');
                        }
                    });
                }
            };
            frappeGanttInstance = new Gantt("#frappe-gantt", frappeTasks, ganttConfig);
            renderTaskList();
            console.log('✅ Frappe Gantt zrenderowany!');
        } catch(error) {
            console.error('❌ Błąd Frappe Gantt:', error);
            document.getElementById('frappe-gantt').innerHTML = '<div class="text-red-500 p-4">Błąd: ' + error.message + '</div>';
        }
    }
    
    // Obsługa przycisków widoku
    document.querySelectorAll('.frappe-view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (frappeGanttInstance) {
                frappeGanttInstance.change_view_mode(this.dataset.mode);
            }
        });
    });
    
    // Załaduj i wyświetl
    loadTasksFromDB().then(() => {
        renderGantt();
    });
});
</script>

</body>
</html>
