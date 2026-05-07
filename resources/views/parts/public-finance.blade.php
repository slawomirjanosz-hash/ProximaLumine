<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harmonogram finansowy – {{ $project->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto p-4 lg:p-6 mt-4">

    {{-- NAGŁÓWEK --}}
    <div class="bg-white rounded-lg shadow p-5 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold mb-1">💰 {{ $project->name }}</h2>
                <p class="text-sm text-gray-600">Nr projektu: <strong>{{ $project->project_number }}</strong></p>
                @if($project->status)
                <p class="text-sm text-gray-500 mt-1">Status:
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                        @if($project->status === 'in_progress') bg-blue-100 text-blue-800
                        @elseif($project->status === 'warranty') bg-yellow-100 text-yellow-800
                        @elseif($project->status === 'archived') bg-gray-200 text-gray-600
                        @else bg-green-100 text-green-800 @endif">
                        @if($project->status === 'in_progress') W toku
                        @elseif($project->status === 'warranty') Gwarancja
                        @elseif($project->status === 'archived') Archiwum
                        @else {{ $project->status }} @endif
                    </span>
                </p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400">Widok publiczny – tylko do odczytu</p>
                <p class="text-xs text-gray-400 mt-1">Dane aktualne na chwilę otwarcia: <strong>{{ now()->format('d.m.Y H:i') }}</strong></p>
            </div>
        </div>
    </div>

    {{-- PODSUMOWANIE FINANSOWE --}}
    <div class="bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm mb-6">
        <h3 class="text-lg font-semibold flex items-center gap-2 mb-4">
            <span class="text-green-600">💰</span>
            Harmonogram finansowy
        </h3>

        <div class="w-full sm:max-w-xs mb-6">
            <table class="w-auto text-sm">
                <tbody>
                    <tr class="bg-blue-50 text-blue-900">
                        <td class="px-3 py-2 font-semibold">Wartość projektu:</td>
                        <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['project_value'] ?? 0), 2, ',', ' ') }} zł</td>
                    </tr>
                    <tr class="bg-indigo-50 text-indigo-900">
                        <td class="px-3 py-2 font-semibold">Faktury kosztowe:</td>
                        <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['cost_invoices'] ?? 0), 2, ',', ' ') }} zł</td>
                    </tr>
                    <tr class="bg-amber-50 text-amber-900">
                        <td class="px-3 py-2 font-semibold">Materiały i usługi zamówione:</td>
                        <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['ordered_materials_services'] ?? 0), 2, ',', ' ') }} zł</td>
                    </tr>
                    <tr class="bg-rose-50 text-rose-900">
                        <td class="px-3 py-2 font-semibold">Bilans:</td>
                        <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['balance'] ?? 0), 2, ',', ' ') }} zł</td>
                    </tr>
                    <tr class="bg-emerald-50 text-emerald-900 border-t border-emerald-200">
                        <td class="px-3 py-2 text-sm">Faktury wystawione (info):</td>
                        <td class="pl-2 pr-3 py-2 text-right text-sm whitespace-nowrap">{{ number_format((float)($financeSummary['issued_invoices'] ?? 0), 2, ',', ' ') }} zł</td>
                    </tr>
                    @if(($financeSummary['planned_invoices'] ?? 0) > 0)
                    <tr class="bg-violet-50 text-violet-900">
                        <td class="px-3 py-2 text-sm">Faktury planowane (info):</td>
                        <td class="pl-2 pr-3 py-2 text-right text-sm whitespace-nowrap">{{ number_format((float)($financeSummary['planned_invoices'] ?? 0), 2, ',', ' ') }} zł</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- WYKRES CASHFLOW --}}
        @php
            $chartCostRows = collect($importedCostRows ?? [])
                ->filter(fn($r) => ($r['date'] ?? '') !== '' && ($r['amount_net'] ?? '') !== '')
                ->map(fn($r) => ['date' => $r['date'], 'amount' => (float) $r['amount_net'], 'type' => 'expense', 'label' => 'Faktura kosztowa'])
                ->values()->all();
            $chartIssuedRows = collect($issuedInvoiceRows ?? [])
                ->filter(fn($r) => ($r['date'] ?? '') !== '' && ($r['amount_net'] ?? '') !== '')
                ->map(fn($r) => ['date' => $r['date'], 'amount' => (float) $r['amount_net'], 'type' => ($r['status'] ?? '') === 'Planowana' ? 'planned_income' : 'income', 'label' => 'Faktura wystawiona'])
                ->values()->all();
            $chartOrderRows = collect($orderRows ?? [])
                ->filter(fn($r) => ($r['date'] ?? '') !== '' && ($r['amount_net'] ?? '') !== '')
                ->map(fn($r) => ['date' => $r['date'], 'amount' => (float) $r['amount_net'], 'type' => 'expense', 'label' => 'Zamówienie'])
                ->values()->all();
            $allChartData = array_merge($chartCostRows, $chartIssuedRows, $chartOrderRows);
            $hasChartData = !empty($allChartData);
        @endphp

        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Wykres kosztów i przychodów (cash flow)</h4>
            @if($hasChartData)
            <div class="flex flex-wrap items-center gap-1 mb-2">
                <div class="flex rounded border border-gray-200 overflow-hidden text-xs font-medium">
                    <button class="cf-mode-btn px-3 py-1.5 hover:bg-gray-100 border-r border-gray-200" data-mode="day">Dzień</button>
                    <button class="cf-mode-btn px-3 py-1.5 hover:bg-gray-100 border-r border-gray-200" data-mode="week">Tydzień</button>
                    <button class="cf-mode-btn px-3 py-1.5 bg-indigo-600 text-white border-r border-gray-200" data-mode="month">Miesiąc</button>
                    <button class="cf-mode-btn px-3 py-1.5 hover:bg-gray-100" data-mode="year">Rok</button>
                </div>
                <button id="cf-prev" class="px-3 py-1.5 bg-gray-100 rounded border border-gray-200 text-xs hover:bg-gray-200 font-medium ml-1">‹ Wcześniej</button>
                <span id="cf-range-label" class="text-xs text-gray-600 font-semibold px-2 py-1 bg-gray-50 rounded border border-gray-200 min-w-[170px] text-center"></span>
                <button id="cf-next" class="px-3 py-1.5 bg-gray-100 rounded border border-gray-200 text-xs hover:bg-gray-200 font-medium">Dalej ›</button>
                <button id="cf-reset" class="px-3 py-1.5 bg-gray-100 rounded border border-gray-200 text-xs hover:bg-gray-200 ml-auto">↺ Resetuj</button>
            </div>
            <div style="position:relative; height:280px;">
                <canvas id="cf-chart"></canvas>
            </div>
            <div class="mt-2">
                <p class="text-xs text-gray-400 mb-1">Przegląd całości – przeciągnij, aby przybliżyć zakres:</p>
                <div id="cf-brush-wrap" style="position:relative; height:54px; user-select:none;">
                    <canvas id="cf-overview-chart" style="position:absolute; top:0; left:0; width:100%; height:100%;"></canvas>
                    <div id="cf-brush-sel" style="position:absolute; top:0; height:100%; display:none; background:rgba(99,102,241,0.14); border-left:2px solid #6366f1; border-right:2px solid #6366f1; pointer-events:none;"></div>
                    <div id="cf-brush-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; cursor:crosshair;"></div>
                </div>
            </div>
            <script>
            (function(){
                const rawData = @json($allChartData);
                if (!rawData || !rawData.length) return;
                rawData.sort((a,b) => a.date.localeCompare(b.date));
                const minDateStr = rawData[0].date, maxDateStr = rawData[rawData.length-1].date;
                const minTs = +new Date(minDateStr+'T00:00:00'), maxTs = +new Date(maxDateStr+'T00:00:00');
                const MONTHS_PL = ['Sty','Lut','Mar','Kwi','Maj','Cze','Lip','Sie','Wrz','Paź','Lis','Gru'];

                function parseDate(s){const p=s.split('-');return new Date(+p[0],+p[1]-1,+p[2]);}
                function fmtDate(d){return d.toLocaleDateString('pl-PL');}
                function fmtAmt(v){return v.toFixed(2).replace('.',',')+' zł';}

                function getPeriodKey(dateStr, mode) {
                    if (mode==='day') return dateStr;
                    if (mode==='month') return dateStr.slice(0,7);
                    if (mode==='year') return dateStr.slice(0,4);
                    if (mode==='week') {
                        const d=parseDate(dateStr), off=(d.getDay()+6)%7, m=new Date(d);
                        m.setDate(d.getDate()-off);
                        return m.getFullYear()+'-'+String(m.getMonth()+1).padStart(2,'0')+'-'+String(m.getDate()).padStart(2,'0');
                    }
                }
                function getLabelForKey(key, mode) {
                    if (mode==='day') return fmtDate(parseDate(key));
                    if (mode==='month') {const[y,m]=key.split('-');return MONTHS_PL[+m-1]+' '+y;}
                    if (mode==='year') return key;
                    if (mode==='week') {const d=parseDate(key),e=new Date(d);e.setDate(d.getDate()+6);return fmtDate(d)+'–'+fmtDate(e);}
                }
                function groupData(mode, fs, fe) {
                    const map=new Map();
                    rawData.forEach(t=>{
                        const ts=+new Date(t.date+'T00:00:00');
                        if (fs&&ts<fs.getTime()) return;
                        if (fe&&ts>fe.getTime()) return;
                        const k=getPeriodKey(t.date,mode);
                        if (!map.has(k)) map.set(k,{income:0,expense:0,planned:0});
                        const e=map.get(k);
                        if (t.type==='income') e.income+=t.amount;
                        else if (t.type==='planned_income') e.planned+=t.amount;
                        else e.expense+=t.amount;
                    });
                    return [...map.entries()].sort((a,b)=>a[0].localeCompare(b[0])).map(([k,v])=>({key:k,...v}));
                }

                const overviewGrouped = groupData('month',null,null);
                let currentMode='month', windowStart=null, windowEnd=null, mainChart=null, overviewChart=null;

                function getDefaultWindow(mode) {
                    const mn=parseDate(minDateStr), mx=parseDate(maxDateStr);
                    if (mode==='year') return {start:new Date(mn.getFullYear(),0,1),end:new Date(mx.getFullYear(),11,31)};
                    const end=new Date(mx); let start;
                    if (mode==='month'){start=new Date(end.getFullYear()-1,end.getMonth()+1,1);}
                    else if (mode==='week'){start=new Date(end);start.setDate(end.getDate()-83);}
                    else {start=new Date(end);start.setDate(end.getDate()-29);}
                    return {start,end};
                }
                function buildMainData() {
                    const g=groupData(currentMode,windowStart,windowEnd);
                    const labels=g.map(d=>getLabelForKey(d.key,currentMode));
                    const income=g.map(d=>d.income), expense=g.map(d=>d.expense), planned=g.map(d=>d.planned);
                    const balance=[]; let cum=0;
                    g.forEach(d=>{cum+=d.income+d.planned-d.expense;balance.push(cum);});
                    return {labels,income,expense,planned,balance};
                }
                function updateRangeLabel() {
                    const el=document.getElementById('cf-range-label'); if (!el) return;
                    if (currentMode==='year') el.textContent=windowStart.getFullYear()+' – '+windowEnd.getFullYear();
                    else el.textContent=fmtDate(windowStart)+' – '+fmtDate(windowEnd);
                }
                function updateModeButtons() {
                    document.querySelectorAll('.cf-mode-btn').forEach(btn=>{
                        const a=btn.dataset.mode===currentMode;
                        btn.classList.toggle('bg-indigo-600',a); btn.classList.toggle('text-white',a);
                        btn.classList.remove('hover:bg-gray-100'); if (!a) btn.classList.add('hover:bg-gray-100');
                    });
                }
                function updateMainChart() {
                    if (!mainChart) return;
                    const {labels,income,expense,planned,balance}=buildMainData();
                    mainChart.data.labels=labels;
                    mainChart.data.datasets[0].data=income;
                    mainChart.data.datasets[1].data=expense;
                    mainChart.data.datasets[2].data=planned;
                    mainChart.data.datasets[3].data=balance;
                    mainChart.update('none');
                }
                function fractionFromTs(ts){if(maxTs===minTs)return 0;return Math.max(0,Math.min(1,(ts-minTs)/(maxTs-minTs)));}
                function tsFromFraction(f){return minTs+f*(maxTs-minTs);}
                function updateBrushSelection() {
                    const sel=document.getElementById('cf-brush-sel'), wrap=document.getElementById('cf-brush-wrap');
                    if (!sel||!wrap||!windowStart||!windowEnd) return;
                    const W=wrap.offsetWidth; if (!W) return;
                    const f1=fractionFromTs(windowStart.getTime()), f2=fractionFromTs(windowEnd.getTime());
                    sel.style.display='block'; sel.style.left=Math.round(f1*W)+'px'; sel.style.width=Math.max(4,Math.round((f2-f1)*W))+'px';
                }
                function initBrush() {
                    const overlay=document.getElementById('cf-brush-overlay'), wrap=document.getElementById('cf-brush-wrap');
                    if (!overlay||!wrap) return;
                    let dragging=false, dragF1=0;
                    function getF(e){const r=wrap.getBoundingClientRect(),cx=e.touches?e.touches[0].clientX:e.clientX;return Math.max(0,Math.min(1,(cx-r.left)/wrap.offsetWidth));}
                    function drawLive(f1,f2){const sel=document.getElementById('cf-brush-sel'),W=wrap.offsetWidth,a=Math.min(f1,f2),b=Math.max(f1,f2);sel.style.display='block';sel.style.left=Math.round(a*W)+'px';sel.style.width=Math.max(4,Math.round((b-a)*W))+'px';}
                    function applyBrush(f1r,f2r){
                        const f1=Math.min(f1r,f2r),f2=Math.max(f1r,f2r); if(f2-f1<0.01)return;
                        const t1=tsFromFraction(f1),t2=tsFromFraction(f2),span=(t2-t1)/86400000;
                        if (span<=45) currentMode='day'; else if(span<=120) currentMode='week'; else if(span<=800) currentMode='month'; else currentMode='year';
                        windowStart=new Date(t1); windowEnd=new Date(t2);
                        updateModeButtons(); updateMainChart(); updateBrushSelection(); updateRangeLabel();
                    }
                    overlay.addEventListener('mousedown',e=>{dragging=true;dragF1=getF(e);e.preventDefault();});
                    document.addEventListener('mousemove',e=>{if(!dragging)return;drawLive(dragF1,getF(e));});
                    document.addEventListener('mouseup',e=>{if(!dragging)return;dragging=false;applyBrush(dragF1,getF(e));});
                    overlay.addEventListener('touchstart',e=>{dragging=true;dragF1=getF(e);e.preventDefault();},{passive:false});
                    overlay.addEventListener('touchmove',e=>{if(!dragging)return;drawLive(dragF1,getF(e));e.preventDefault();},{passive:false});
                    overlay.addEventListener('touchend',e=>{if(!dragging)return;dragging=false;const t=e.changedTouches&&e.changedTouches[0];if(!t)return;const r=wrap.getBoundingClientRect();applyBrush(dragF1,Math.max(0,Math.min(1,(t.clientX-r.left)/wrap.offsetWidth)));});
                }

                const w=getDefaultWindow(currentMode);
                windowStart=w.start; windowEnd=w.end;

                document.addEventListener('DOMContentLoaded', function() {
                    if (!window.Chart) return;
                    const {labels,income,expense,planned,balance}=buildMainData();
                    mainChart=new Chart(document.getElementById('cf-chart'),{
                        type:'bar',
                        data:{labels,datasets:[
                            {label:'Przychody',data:income,backgroundColor:'rgba(34,197,94,0.6)',borderColor:'rgb(34,197,94)',borderWidth:1,order:2},
                            {label:'Wydatki',data:expense,backgroundColor:'rgba(239,68,68,0.6)',borderColor:'rgb(239,68,68)',borderWidth:1,order:2},
                            {label:'Planowane przychody',data:planned,backgroundColor:'rgba(139,92,246,0.35)',borderColor:'rgb(139,92,246)',borderWidth:2,order:2},
                            {label:'Bilans narastająco',data:balance,type:'line',borderColor:'rgb(99,102,241)',backgroundColor:'rgba(99,102,241,0.07)',borderWidth:2,fill:true,tension:0.3,pointRadius:3,order:1}
                        ]},
                        options:{responsive:true,maintainAspectRatio:false,animation:{duration:200},plugins:{legend:{display:true,position:'top'},tooltip:{callbacks:{label:ctx=>ctx.dataset.label+': '+fmtAmt(ctx.parsed.y)}}},scales:{y:{ticks:{callback:v=>v.toFixed(0)+' zł'}}}}
                    });
                    overviewChart=new Chart(document.getElementById('cf-overview-chart'),{
                        type:'bar',
                        data:{labels:overviewGrouped.map(d=>d.key),datasets:[
                            {data:overviewGrouped.map(d=>d.income),backgroundColor:'rgba(34,197,94,0.45)',borderWidth:0},
                            {data:overviewGrouped.map(d=>d.expense),backgroundColor:'rgba(239,68,68,0.4)',borderWidth:0},
                            {data:overviewGrouped.map(d=>d.planned),backgroundColor:'rgba(139,92,246,0.35)',borderWidth:0}
                        ]},
                        options:{responsive:true,maintainAspectRatio:false,animation:false,plugins:{legend:{display:false},tooltip:{enabled:false}},scales:{x:{display:false},y:{display:false}}}
                    });
                    setTimeout(()=>{updateBrushSelection();updateRangeLabel();updateModeButtons();initBrush();},60);
                    document.querySelectorAll('.cf-mode-btn').forEach(btn=>{btn.addEventListener('click',function(){
                        currentMode=this.dataset.mode;const w=getDefaultWindow(currentMode);windowStart=w.start;windowEnd=w.end;
                        updateModeButtons();updateMainChart();updateBrushSelection();updateRangeLabel();
                    });});
                    document.getElementById('cf-prev').addEventListener('click',function(){
                        if(currentMode==='year')return;const s=windowEnd.getTime()-windowStart.getTime();
                        windowStart=new Date(windowStart.getTime()-s);windowEnd=new Date(windowEnd.getTime()-s);
                        updateMainChart();updateBrushSelection();updateRangeLabel();
                    });
                    document.getElementById('cf-next').addEventListener('click',function(){
                        if(currentMode==='year')return;const s=windowEnd.getTime()-windowStart.getTime();
                        windowStart=new Date(windowStart.getTime()+s);windowEnd=new Date(windowEnd.getTime()+s);
                        updateMainChart();updateBrushSelection();updateRangeLabel();
                    });
                    document.getElementById('cf-reset').addEventListener('click',function(){
                        const w=getDefaultWindow(currentMode);windowStart=w.start;windowEnd=w.end;
                        updateMainChart();updateBrushSelection();updateRangeLabel();
                    });
                });
            })();
            </script>
            @else
            <p class="text-sm text-gray-500">Brak danych do wyświetlenia wykresu.</p>
            @endif
        </div>

        {{-- TABS --}}
        <div class="mb-4 border-b border-gray-200">
            <div class="flex flex-wrap gap-2">
                <button type="button" class="pf-tab-btn px-4 py-2 rounded-t bg-white border border-gray-200 border-b-white text-sm font-semibold" data-tab="costs">
                    Faktury kosztowe ({{ number_format((float)($financeSummary['cost_invoices'] ?? 0), 2, ',', ' ') }} zł)
                </button>
                <button type="button" class="pf-tab-btn px-4 py-2 rounded-t bg-gray-100 border border-gray-200 text-sm" data-tab="issued">
                    Faktury wystawione ({{ number_format((float)($financeSummary['issued_invoices'] ?? 0), 2, ',', ' ') }} zł)@if(($financeSummary['planned_invoices'] ?? 0) > 0) + planowane ({{ number_format((float)($financeSummary['planned_invoices'] ?? 0), 2, ',', ' ') }} zł)@endif
                </button>
                <button type="button" class="pf-tab-btn px-4 py-2 rounded-t bg-gray-100 border border-gray-200 text-sm" data-tab="orders">
                    Zamówienia ({{ number_format((float)($financeSummary['ordered_materials_services'] ?? 0), 2, ',', ' ') }} zł)
                </button>
            </div>
        </div>

        {{-- TAB: Faktury kosztowe --}}
        <div id="pf-tab-costs" class="pf-tab-content">
            @if(!empty($importedCostGroupSummaries ?? []))
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-gray-800 mb-3">Grupy kosztów</h4>
                @php
                    $groupRowStyles = ['bg-blue-50 text-blue-900','bg-indigo-50 text-indigo-900','bg-emerald-50 text-emerald-900','bg-amber-50 text-amber-900','bg-rose-50 text-rose-900'];
                @endphp
                <div class="overflow-x-auto">
                    <table class="w-auto text-sm">
                        <tbody>
                            @foreach($importedCostGroupSummaries as $idx => $gs)
                            <tr class="{{ $groupRowStyles[$idx % count($groupRowStyles)] }} border-b border-white/60">
                                <td class="px-3 py-2 font-semibold whitespace-nowrap">{{ $gs['group'] }}</td>
                                <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float) $gs['total_amount'], 2, ',', ' ') }} zł</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(!empty($importedCostRows ?? []))
            <div class="w-full overflow-x-auto rounded border border-gray-200 mb-4">
                <table class="w-full table-auto text-xs">
                    <thead>
                        <tr class="bg-indigo-50 text-indigo-900">
                            <th class="px-2 py-2 text-left whitespace-nowrap">Data</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Dostawca</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Dokument</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Grupa</th>
                            <th class="px-2 py-2 text-right whitespace-nowrap">Kwota netto</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Opis</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Status</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Data płatności</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($importedCostRows as $i => $row)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-indigo-50/40">
                            <td class="px-2 py-1.5 whitespace-nowrap">{{ $row['date'] ?? '' }}</td>
                            <td class="px-2 py-1.5 truncate max-w-[150px]" title="{{ $row['subject_or_supplier'] ?? '' }}">{{ $row['subject_or_supplier'] ?? '' }}</td>
                            <td class="px-2 py-1.5">{{ $row['document'] ?? '' }}</td>
                            <td class="px-2 py-1.5">{{ $row['group'] ?? '' }}</td>
                            <td class="px-2 py-1.5 text-right whitespace-nowrap font-semibold">{{ ($row['amount_net'] ?? '') !== '' ? number_format((float) $row['amount_net'], 2, ',', ' ') : '' }} zł</td>
                            <td class="px-2 py-1.5 truncate max-w-[200px]" title="{{ $row['description'] ?? '' }}">{{ \Illuminate\Support\Str::limit($row['description'] ?? '', 50, '…') }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap">{{ $row['status'] ?? '' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap">{{ $row['payment_date'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-indigo-100 text-indigo-900 font-semibold">
                            <td colspan="4" class="px-2 py-2 text-right">Suma:</td>
                            <td class="px-2 py-2 text-right whitespace-nowrap">{{ number_format((float)($financeSummary['cost_invoices'] ?? 0), 2, ',', ' ') }} zł</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500">Brak faktur kosztowych.</p>
            @endif
        </div>

        {{-- TAB: Faktury wystawione --}}
        <div id="pf-tab-issued" class="pf-tab-content hidden">
            @if(!empty($issuedInvoiceRows ?? []))
            <div class="w-full overflow-x-auto rounded border border-gray-200 mb-4">
                <table class="w-full table-auto text-xs">
                    <thead>
                        <tr class="bg-emerald-50 text-emerald-900">
                            <th class="px-2 py-2 text-left whitespace-nowrap">Data</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Nr faktury</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Opis</th>
                            <th class="px-2 py-2 text-right whitespace-nowrap">Kwota netto</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Data płatności</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($issuedInvoiceRows as $i => $row)
                        @php $isPlanned = ($row['status'] ?? '') === 'Planowana'; @endphp
                        <tr class="{{ $isPlanned ? 'bg-violet-50/60' : ($i % 2 === 0 ? 'bg-white' : 'bg-gray-50') }} hover:bg-emerald-50/40">
                            <td class="px-2 py-1.5 whitespace-nowrap">{{ $row['date'] ?? '' }}</td>
                            <td class="px-2 py-1.5">{{ $row['invoice_number'] ?? '' }}</td>
                            <td class="px-2 py-1.5 truncate max-w-[200px]" title="{{ $row['description'] ?? '' }}">{{ \Illuminate\Support\Str::limit($row['description'] ?? '', 50, '…') }}</td>
                            <td class="px-2 py-1.5 text-right whitespace-nowrap font-semibold {{ $isPlanned ? 'text-violet-700' : '' }}">{{ ($row['amount_net'] ?? '') !== '' ? number_format((float) $row['amount_net'], 2, ',', ' ') : '' }} zł</td>
                            <td class="px-2 py-1.5 whitespace-nowrap">{{ $row['payment_date'] ?? '' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @if($isPlanned)
                                <span class="inline-block px-1.5 py-0.5 rounded text-xs bg-violet-100 text-violet-700">{{ $row['status'] }}</span>
                                @else
                                {{ $row['status'] ?? '' }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-emerald-100 text-emerald-900 font-semibold">
                            <td colspan="3" class="px-2 py-2 text-right">Suma wystawionych:</td>
                            <td class="px-2 py-2 text-right whitespace-nowrap">{{ number_format((float)($financeSummary['issued_invoices'] ?? 0), 2, ',', ' ') }} zł</td>
                            <td colspan="2"></td>
                        </tr>
                        @if(($financeSummary['planned_invoices'] ?? 0) > 0)
                        <tr class="bg-violet-100 text-violet-900 font-semibold">
                            <td colspan="3" class="px-2 py-2 text-right">Suma planowanych:</td>
                            <td class="px-2 py-2 text-right whitespace-nowrap">{{ number_format((float)($financeSummary['planned_invoices'] ?? 0), 2, ',', ' ') }} zł</td>
                            <td colspan="2"></td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500">Brak faktur wystawionych.</p>
            @endif
        </div>

        {{-- TAB: Zamówienia --}}
        <div id="pf-tab-orders" class="pf-tab-content hidden">
            @if(!empty($orderRows ?? []))
            <div class="w-full overflow-x-auto rounded border border-gray-200 mb-4">
                <table class="w-full table-auto text-xs">
                    <thead>
                        <tr class="bg-amber-50 text-amber-900">
                            <th class="px-2 py-2 text-left whitespace-nowrap">Data</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Nr zamówienia</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Kategoria</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Dostawca</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Opis</th>
                            <th class="px-2 py-2 text-right whitespace-nowrap">Kwota netto</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Data płatności</th>
                            <th class="px-2 py-2 text-left whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderRows as $i => $row)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-amber-50/40">
                            <td class="px-2 py-1.5 whitespace-nowrap">{{ $row['date'] ?? '' }}</td>
                            <td class="px-2 py-1.5">{{ $row['order_number'] ?? '' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @if(($row['category'] ?? '') === 'materials')
                                <span class="inline-block px-1.5 py-0.5 rounded text-xs bg-amber-100 text-amber-800">Materiały</span>
                                @elseif(($row['category'] ?? '') === 'services')
                                <span class="inline-block px-1.5 py-0.5 rounded text-xs bg-sky-100 text-sky-800">Usługi</span>
                                @else
                                {{ $row['category'] ?? '' }}
                                @endif
                            </td>
                            <td class="px-2 py-1.5 truncate max-w-[120px]" title="{{ $row['supplier'] ?? '' }}">{{ $row['supplier'] ?? '' }}</td>
                            <td class="px-2 py-1.5 truncate max-w-[200px]" title="{{ $row['description'] ?? '' }}">{{ \Illuminate\Support\Str::limit($row['description'] ?? '', 50, '…') }}</td>
                            <td class="px-2 py-1.5 text-right whitespace-nowrap font-semibold">{{ ($row['amount_net'] ?? '') !== '' ? number_format((float) $row['amount_net'], 2, ',', ' ') : '' }} zł</td>
                            <td class="px-2 py-1.5 whitespace-nowrap">{{ $row['payment_date'] ?? '' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap">{{ $row['status'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-amber-100 text-amber-900 font-semibold">
                            <td colspan="5" class="px-2 py-2 text-right">Suma zamówionych:</td>
                            <td class="px-2 py-2 text-right whitespace-nowrap">{{ number_format((float)($financeSummary['ordered_materials_services'] ?? 0), 2, ',', ' ') }} zł</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500">Brak zamówień.</p>
            @endif
        </div>

    </div>

</div>

<script>
(function(){
    const btns = document.querySelectorAll('.pf-tab-btn');
    const panes = document.querySelectorAll('.pf-tab-content');
    btns.forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.tab;
            btns.forEach(b => {
                const active = b.dataset.tab === target;
                b.classList.toggle('bg-white', active);
                b.classList.toggle('border-b-white', active);
                b.classList.toggle('font-semibold', active);
                b.classList.toggle('bg-gray-100', !active);
                b.classList.remove('border-b-white'); // reset
                if (active) b.classList.add('border-b-white');
            });
            panes.forEach(p => {
                p.classList.toggle('hidden', !p.id.endsWith(target));
            });
        });
    });
})();
</script>

</body>
</html>
