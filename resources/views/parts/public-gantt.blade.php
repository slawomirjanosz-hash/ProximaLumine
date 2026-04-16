<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Harmonogram – {{ $project->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto p-4 lg:p-6 mt-4">

    {{-- NAGŁÓWEK --}}
    <div class="bg-white rounded-lg shadow p-5 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold mb-1">📊 {{ $project->name }}</h2>
                <p class="text-sm text-gray-600">Nr projektu: <strong>{{ $project->project_number }}</strong></p>
                @if($project->started_at)
                <p class="text-sm text-gray-500 mt-1">Rozpoczęcie: {{ $project->started_at->format('d.m.Y') }}</p>
                @endif
                @if($project->finished_at)
                <p class="text-sm text-gray-500">Planowane zakończenie: <strong class="text-red-600">{{ $project->finished_at->format('d.m.Y') }}</strong></p>
                @endif
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                    @if($project->status === 'in_progress') bg-blue-100 text-blue-800
                    @elseif($project->status === 'warranty') bg-yellow-100 text-yellow-800
                    @elseif($project->status === 'archived') bg-gray-200 text-gray-600
                    @else bg-green-100 text-green-800 @endif">
                    @if($project->status === 'in_progress') W toku
                    @elseif($project->status === 'warranty') Gwarancja
                    @elseif($project->status === 'archived') Archiwum
                    @else {{ $project->status }} @endif
                </span>
                <p class="text-xs text-gray-400 mt-2">Widok publiczny – tylko do odczytu</p>
            </div>
        </div>
    </div>

    {{-- GANTT --}}
    <div class="bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <span class="text-blue-600">📊</span>
                Gantt Frappe – Interaktywny harmonogram
            </h3>
        </div>

        <div class="mb-4 flex gap-2 items-center flex-wrap">
            <label class="text-sm font-semibold text-gray-700">Widok:</label>
            <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Quarter Day">Ćwierć dnia</button>
            <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Half Day">Pół dnia</button>
            <button class="frappe-view-btn bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm" data-mode="Day">Dzień</button>
            <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Week">Tydzień</button>
            <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Month">Miesiąc</button>
        </div>

        <div class="mb-3 p-2 bg-gray-50 rounded border">
            <p class="text-xs text-gray-600">
                🔴 Zadania zaznaczone na czerwono — termin przekroczony.<br>
                🟦 Pasek w środku zadania oznacza % wykonania.<br>
                @if($project->finished_at) 📅 Przerywana czerwona linia pionowa = planowana data zakończenia projektu ({{ $project->finished_at->format('d.m.Y') }}). @endif
            </p>
        </div>

        <div id="frappe-gantt"></div>

        <div id="frappe-task-list" class="mt-8">
            <!-- Lista zadań pojawi się tutaj -->
        </div>
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
    #frappe-gantt .bar-wrapper.overdue-task .bar {
        fill: #ef4444 !important;
    }
    #frappe-gantt .bar-wrapper.overdue-task .bar-progress {
        fill: #b91c1c !important;
    }
    #frappe-gantt .bar-wrapper.overdue-task .bar-label {
        fill: #7f1d1d !important;
        font-weight: 700;
    }
    #frappe-gantt .bar-label.big {
        fill: #111111 !important;
        font-weight: 600;
    }
    #frappe-gantt svg,
    #frappe-gantt .gantt svg {
        overflow: visible !important;
    }
    #frappe-gantt .bar-group {
        overflow: visible !important;
    }
    /* progress slider styling */
    input[type=range].task-progress-slider {
        -webkit-appearance: none;
        height: 8px;
        border-radius: 4px;
        outline: none;
    }
    input[type=range].task-progress-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 14px; height: 14px;
        border-radius: 50%;
        background: #2563eb;
        cursor: default;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Gantt === 'undefined') {
        document.getElementById('frappe-gantt').innerHTML = '<div class="text-red-500 p-4">Błąd: Biblioteka Frappe Gantt nie została załadowana.</div>';
        return;
    }

    const TOKEN = '{{ $token }}';
    const projectEndDateStr = @json($project->finished_at ? $project->finished_at->format('Y-m-d') : null);
    const projectEndDate = projectEndDateStr
        ? (function() { const p = projectEndDateStr.split('-'); return new Date(+p[0], +p[1]-1, +p[2]); })()
        : null;
    let frappeGanttInstance = null;
    let frappeTasks = [];
    let currentViewMode = 'Day';

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

    function applyProgressSliderFill(slider) {
        if (!slider) return;
        const value = Number(slider.value || 0);
        slider.style.background = `linear-gradient(to right, #2563eb 0%, #2563eb ${value}%, #e5e7eb ${value}%, #e5e7eb 100%)`;
    }

    function isTaskOverdue(task) {
        const taskEnd = task.end instanceof Date ? task.end : parseDate(task.end);
        const progressValue = Number(task.progress || 0);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return progressValue < 100 && taskEnd < today;
    }

    function getTaskDaysInfo(task) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const end = task.end instanceof Date ? task.end : parseDate(task.end);
        const progress = Number(task.progress || 0);
        if (progress >= 100) {
            let completedAt = null;
            if (task.completed_at) {
                const raw = typeof task.completed_at === 'string' ? task.completed_at : String(task.completed_at);
                completedAt = new Date(raw.substring(0, 10) + 'T00:00:00');
            }
            if (!completedAt || isNaN(completedAt.getTime())) {
                completedAt = today;
            }
            const days = Math.round((end - completedAt) / (1000 * 60 * 60 * 24));
            const label = days > 0 ? `+${days}` : `${days}`;
            return { status: 'done', days, label };
        } else {
            const days = Math.round((end - today) / (1000 * 60 * 60 * 24));
            const label = days > 0 ? `+${days}` : `${days}`;
            return { status: days >= 0 ? 'ok' : 'overdue', days, label };
        }
    }

    function loadTasksFromDB() {
        return fetch(`/api/public/gantt/${TOKEN}`)
            .then(response => response.json())
            .then(tasks => {
                let tasksArray = Array.isArray(tasks) ? tasks : Object.values(tasks);
                tasksArray = tasksArray.filter(t => t && t.id);
                frappeTasks = tasksArray.map(t => ({
                    id: t.id.toString(),
                    name: t.name,
                    start: parseDate(t.start),
                    end: parseDate(t.end),
                    progress: t.progress || 0,
                    dependencies: t.dependencies || '',
                    description: t.description || '',
                    assignee: t.assignee || '',
                    completed_at: t.completed_at || null,
                }));
            })
            .catch(() => { frappeTasks = []; });
    }

    function applyOverdueTaskStyles() {
        const wrappers = document.querySelectorAll('#frappe-gantt .bar-wrapper');
        wrappers.forEach((wrapper, idx) => {
            const task = frappeTasks[idx];
            if (!task) return;
            if (isTaskOverdue(task)) {
                wrapper.classList.add('overdue-task');
            } else {
                wrapper.classList.remove('overdue-task');
            }
        });
    }

    function drawProjectEndLine() {
        if (!projectEndDate || !frappeGanttInstance) return;
        const svg = document.querySelector('#frappe-gantt svg');
        if (!svg) return;
        const prev = svg.querySelector('.gantt-project-end-group');
        if (prev) prev.remove();

        const ganttStart = frappeGanttInstance.gantt_start;
        if (!ganttStart) return;
        const step = frappeGanttInstance.options.step;
        const colWidth = frappeGanttInstance.options.column_width;
        const diffHours = (projectEndDate.getTime() - new Date(ganttStart).getTime()) / 3600000;
        const x = Math.round((diffHours / step) * colWidth);
        if (x < 0) return;

        const svgHeight = parseInt(svg.getAttribute('height') || svg.getBoundingClientRect().height || 400);

        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.classList.add('gantt-project-end-group');
        g.style.pointerEvents = 'none';

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x); line.setAttribute('y1', 0);
        line.setAttribute('x2', x); line.setAttribute('y2', svgHeight);
        line.setAttribute('stroke', '#dc2626');
        line.setAttribute('stroke-width', '1.5');
        line.setAttribute('stroke-dasharray', '6,4');
        line.setAttribute('opacity', '0.75');
        g.appendChild(line);

        const d = projectEndDate;
        const label = d.getDate().toString().padStart(2,'0') + '.' + (d.getMonth()+1).toString().padStart(2,'0') + '.' + d.getFullYear();
        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rect.setAttribute('x', x - 36); rect.setAttribute('y', 2);
        rect.setAttribute('width', 72); rect.setAttribute('height', 16);
        rect.setAttribute('rx', 3); rect.setAttribute('fill', '#dc2626');
        rect.setAttribute('opacity', '0.85');
        g.appendChild(rect);

        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', x); text.setAttribute('y', 14);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('fill', '#ffffff');
        text.setAttribute('font-size', '9');
        text.setAttribute('font-family', 'sans-serif');
        text.textContent = label;
        g.appendChild(text);

        svg.appendChild(g);
    }

    function fixGanttBarLabels() {
        document.querySelectorAll('#frappe-gantt .bar-wrapper').forEach(function(wrapper) {
            const bar = wrapper.querySelector('.bar');
            const label = wrapper.querySelector('.bar-label');
            if (!bar || !label) return;
            const barW = parseFloat(bar.getAttribute('width') || 0);
            const barX = parseFloat(bar.getAttribute('x') || 0);
            try {
                const lw = label.getBBox().width;
                if (lw > barW - 8) {
                    label.setAttribute('x', barX + barW + 5);
                    label.setAttribute('text-anchor', 'start');
                    label.style.fill = '#111111';
                    label.classList.add('big');
                }
            } catch(e) {}
        });
    }

    function addTaskHoverTooltips() {
        let tooltip = document.getElementById('gantt-task-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.id = 'gantt-task-tooltip';
            tooltip.style.cssText = 'position:fixed;z-index:10000;background:#1e293b;color:#f1f5f9;border-radius:7px;padding:10px 14px;font-size:12px;line-height:1.7;pointer-events:none;display:none;box-shadow:0 6px 18px rgba(0,0,0,0.35);min-width:180px;';
            document.body.appendChild(tooltip);
        }
        let tooltipTimer = null;

        document.querySelectorAll('#frappe-gantt .bar-wrapper').forEach(wrapper => {
            const fresh = wrapper.cloneNode(true);
            wrapper.parentNode.replaceChild(fresh, wrapper);

            fresh.addEventListener('mouseenter', function(e) {
                const taskId = this.getAttribute('data-id');
                const task = frappeTasks.find(t => String(t.id) === String(taskId))
                           || frappeTasks[Array.from(document.querySelectorAll('#frappe-gantt .bar-wrapper')).indexOf(this)];
                if (!task) return;
                tooltipTimer = setTimeout(() => {
                    const start = task._start instanceof Date ? task._start : parseDate(task.start);
                    const end   = task._end   instanceof Date ? task._end   : parseDate(task.end);
                    const progress = Math.round(Number(task.progress || 0));
                    const fmtStart = start.toLocaleDateString('pl-PL');
                    const fmtEnd   = end.toLocaleDateString('pl-PL');
                    tooltip.innerHTML =
                        '<div style="font-weight:700;margin-bottom:5px;font-size:13px;">' + task.name + '</div>' +
                        (task.assignee ? '<div>👤 ' + task.assignee + '</div>' : '') +
                        '<div>▶ Rozpoczęcie: <strong>' + fmtStart + '</strong></div>' +
                        '<div>◼ Zakończenie: <strong>' + fmtEnd + '</strong></div>' +
                        '<div style="margin-top:4px;">✅ Wykonanie: <strong style="color:#4ade80;">' + progress + '%</strong></div>' +
                        (task.description ? '<div style="margin-top:6px;border-top:1px solid #334155;padding-top:5px;color:#cbd5e1;font-size:11px;">' + task.description.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' : '');
                    tooltip.style.display = 'block';
                    const box = this.getBoundingClientRect();
                    const ttW = tooltip.offsetWidth;
                    const ttH = tooltip.offsetHeight;
                    let left = box.left + box.width / 2 - ttW / 2;
                    let top  = box.top - ttH - 8;
                    if (left < 4) left = 4;
                    if (left + ttW > window.innerWidth - 4) left = window.innerWidth - ttW - 4;
                    if (top < 4) top = box.bottom + 8;
                    tooltip.style.left = left + 'px';
                    tooltip.style.top  = top  + 'px';
                }, 600);
            });
            fresh.addEventListener('mouseleave', function() {
                clearTimeout(tooltipTimer);
                tooltip.style.display = 'none';
            });
        });
    }

    function renderTaskList() {
        const container = document.getElementById('frappe-task-list');
        if (!frappeTasks.length) { container.innerHTML = ''; return; }

        let html = '<h4 class="text-lg font-bold mb-2">Lista zadań</h4>';
        html += '<div class="overflow-x-auto"><table class="w-full text-sm border border-gray-200">';
        html += '<thead class="bg-gray-50"><tr>';
        html += '<th class="px-3 py-2 text-left border-b">Zadanie</th>';
        html += '<th class="px-3 py-2 text-left border-b">Osoba</th>';
        html += '<th class="px-3 py-2 text-left border-b">Opis</th>';
        html += '<th class="px-3 py-2 text-left border-b">Termin</th>';
        html += '<th class="px-3 py-2 text-left border-b">Wykonanie</th>';
        html += '<th class="px-3 py-2 text-left border-b">Status</th>';
        html += '<th class="px-3 py-2 text-center border-b">Dni</th>';
        html += '</tr></thead><tbody>';

        frappeTasks.forEach((task) => {
            const end = task.end instanceof Date ? task.end : parseDate(task.end);
            const progressValue = Math.max(0, Math.min(100, Number(task.progress || 0)));
            const daysInfo = getTaskDaysInfo(task);
            const overdue = daysInfo.status === 'overdue';
            const rowClass = overdue ? 'bg-red-50' : (daysInfo.status === 'done' ? 'bg-green-50/40' : 'bg-white');

            let statusBadge, daysCell;
            if (daysInfo.status === 'done') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800 text-xs font-semibold">✓ Wykonano</span>';
                const daysColor = daysInfo.days >= 0 ? 'text-green-700' : 'text-red-700';
                const daysTitle = daysInfo.days > 0 ? `${daysInfo.days} dni przed terminem`
                                : (daysInfo.days < 0 ? `${Math.abs(daysInfo.days)} dni po terminie` : 'dokładnie w terminie');
                daysCell = `<span class="${daysColor} font-semibold text-xs" title="${daysTitle}">${daysInfo.label}</span>`;
            } else if (daysInfo.status === 'overdue') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Po terminie</span>';
                daysCell = `<span class="text-red-700 font-semibold text-xs" title="${Math.abs(daysInfo.days)} dni po terminie">${daysInfo.label}</span>`;
            } else {
                statusBadge = '<span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Termin OK</span>';
                daysCell = `<span class="text-blue-700 font-semibold text-xs" title="${daysInfo.days} dni do terminu">${daysInfo.label}</span>`;
            }

            html += `<tr class="${rowClass} border-b border-gray-100">
                <td class="px-3 py-2 font-semibold">${task.name}</td>
                <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap">${task.assignee ? '<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-700">' + task.assignee + '</span>' : '<span class="text-gray-300">—</span>'}</td>
                <td class="px-3 py-2 text-xs text-gray-500 max-w-[200px]">${task.description ? '<span title="' + task.description.replace(/"/g, '&quot;') + '">' + (task.description.length > 60 ? task.description.substring(0, 60) + '…' : task.description) + '</span>' : '<span class="text-gray-300">—</span>'}</td>
                <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap">${formatDateForInput(end)}</td>
                <td class="px-3 py-2">
                    <div class="flex items-center gap-2 min-w-[180px]">
                        <input type="range" min="0" max="100" value="${progressValue}"
                            class="task-progress-slider w-full h-2 rounded-lg appearance-none opacity-70 cursor-default" disabled>
                        <span class="text-xs font-bold ${overdue ? 'text-red-700' : 'text-gray-800'} whitespace-nowrap">${progressValue}%</span>
                    </div>
                </td>
                <td class="px-3 py-2">${statusBadge}</td>
                <td class="px-3 py-2 text-center">${daysCell}</td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;

        container.querySelectorAll('.task-progress-slider').forEach(slider => {
            applyProgressSliderFill(slider);
        });
    }

    function renderGantt() {
        if (frappeTasks.length === 0) {
            document.getElementById('frappe-gantt').innerHTML = '<div class="text-gray-500 p-4 text-center">Brak zadań w harmonogramie.</div>';
            document.getElementById('frappe-task-list').innerHTML = '';
            return;
        }
        try {
            document.getElementById('frappe-gantt').innerHTML = '';
            const ganttConfig = {
                header_height: 50,
                column_width: 30,
                step: 24,
                view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
                bar_height: 20,
                bar_corner_radius: 3,
                arrow_curve: 5,
                padding: 18,
                view_mode: currentViewMode,
                date_format: 'YYYY-MM-DD',
                language: 'en',
                readonly: true,
                custom_popup_html: function(task) {
                    let startDate = task._start || task.start;
                    let endDate = task._end || task.end;
                    let start = startDate ? new Date(startDate).toLocaleDateString('pl-PL') : 'Brak';
                    let end = endDate ? new Date(endDate).toLocaleDateString('pl-PL') : 'Brak';
                    let duration = (startDate && endDate) ? Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24)) : '-';
                    const depText = task.dependencies ? frappeTasks.find(t => t.id === task.dependencies)?.name || 'Nieznane' : 'Brak';
                    const fullTask = frappeTasks.find(t => String(t.id) === String(task.id)) || task;
                    return '<div style="padding: 10px;">'
                        + '<h5 style="margin: 0 0 10px 0; font-weight: bold;">' + (task.name || 'Brak') + '</h5>'
                        + (fullTask.assignee ? '<p style="margin:5px 0;">👤 <strong>' + fullTask.assignee + '</strong></p>' : '')
                        + '<p style="margin: 5px 0;"><strong>Start:</strong> ' + start + '</p>'
                        + '<p style="margin: 5px 0;"><strong>Koniec:</strong> ' + end + '</p>'
                        + '<p style="margin: 5px 0;"><strong>Czas trwania:</strong> ' + duration + ' dni</p>'
                        + '<p style="margin: 5px 0;"><strong>Postęp:</strong> ' + (task.progress ?? '-') + '%</p>'
                        + '<p style="margin: 5px 0;"><strong>Zależność:</strong> ' + depText + '</p>'
                        + (fullTask.description ? '<p style="margin:8px 0 0 0;font-size:11px;color:#666;border-top:1px solid #ddd;padding-top:6px;">' + fullTask.description.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</p>' : '')
                        + '</div>';
                },
                on_view_change: function(mode) {
                    currentViewMode = mode;
                    document.querySelectorAll('.frappe-view-btn').forEach(btn => {
                        if (btn.dataset.mode === mode) {
                            btn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        } else {
                            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                            btn.classList.add('bg-blue-500', 'hover:bg-blue-600');
                        }
                    });
                    setTimeout(() => {
                        applyOverdueTaskStyles();
                        drawProjectEndLine();
                        fixGanttBarLabels();
                        addTaskHoverTooltips();
                    }, 200);
                }
            };

            if (currentViewMode === 'Month') {
                ganttConfig.step = 24 * 30; ganttConfig.column_width = 60;
            } else if (currentViewMode === 'Week') {
                ganttConfig.step = 24 * 7; ganttConfig.column_width = 40;
            } else if (currentViewMode === 'Day') {
                ganttConfig.step = 24; ganttConfig.column_width = 30;
            } else if (currentViewMode === 'Half Day') {
                ganttConfig.step = 12; ganttConfig.column_width = 18;
            } else if (currentViewMode === 'Quarter Day') {
                ganttConfig.step = 6; ganttConfig.column_width = 12;
            }

            frappeGanttInstance = new Gantt('#frappe-gantt', frappeTasks, ganttConfig);
            renderTaskList();
            applyOverdueTaskStyles();
            drawProjectEndLine();
            addTaskHoverTooltips();
            fixGanttBarLabels();
        } catch(error) {
            document.getElementById('frappe-gantt').innerHTML = '<div class="text-red-500 p-4">Błąd: ' + error.message + '</div>';
        }
    }

    // Przyciski widoku
    document.querySelectorAll('.frappe-view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentViewMode = this.dataset.mode;
            if (frappeGanttInstance) {
                frappeGanttInstance.change_view_mode(this.dataset.mode);
            }
        });
    });

    // Załaduj i wyświetl
    loadTasksFromDB().then(() => { renderGantt(); });
});
</script>

</body>
</html>
