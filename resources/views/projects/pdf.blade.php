@php
    $pdfLogoSrc = null;
    if ($company && $company->logo) {
        if (str_starts_with($company->logo, 'data:image')) {
            $pdfLogoSrc = $company->logo;
        } else {
            $tryPaths = [
                storage_path('app/public/' . $company->logo),
                public_path($company->logo),
                public_path('storage/' . $company->logo),
            ];
            foreach ($tryPaths as $tryPath) {
                if (file_exists($tryPath)) {
                    $pdfLogoSrc = 'file://' . str_replace('\\', '/', $tryPath);
                    break;
                }
            }
        }
    }
    if (!$pdfLogoSrc && file_exists(public_path('logo.png'))) {
        $pdfLogoSrc = 'file://' . str_replace('\\', '/', public_path('logo.png'));
    }
@endphp
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Projekt {{ $project->project_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; background: #fff; }
        .page { padding: 28px 32px; }

        .header { display: table; width: 100%; margin-bottom: 24px; border-bottom: 2px solid #1e40af; padding-bottom: 14px; }
        .header-left { display: table-cell; vertical-align: middle; width: 55%; }
        .header-logo img { max-height: 54px; max-width: 170px; display: block; }
        .header-company-name { font-size: 11pt; font-weight: bold; color: #1e40af; margin-top: 5px; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; font-size: 8.5pt; color: #444; line-height: 1.6; }

        .project-title-block { background: #1e40af; color: #fff; padding: 12px 18px; border-radius: 4px; margin-bottom: 20px; display: table; width: 100%; }
        .project-title-left { display: table-cell; vertical-align: middle; }
        .project-title-block .label { font-size: 8pt; text-transform: uppercase; letter-spacing: 1px; opacity: 0.75; }
        .project-title-block .title { font-size: 14pt; font-weight: bold; margin-top: 2px; }
        .project-title-block .number { font-size: 9pt; opacity: 0.85; margin-top: 3px; }

        .info-grid { display: flex; gap: 16px; margin-bottom: 20px; }
        .info-box { flex: 1; border: 1px solid #dde2ef; border-radius: 4px; padding: 10px 14px; background: #f8f9ff; }
        .info-box .box-title { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.8px; color: #1e40af; font-weight: bold; margin-bottom: 6px; border-bottom: 1px solid #dde2ef; padding-bottom: 4px; }
        .info-box .row { font-size: 9pt; color: #333; margin-bottom: 3px; line-height: 1.4; }
        .info-box .row b { color: #111; }

        .section { margin-bottom: 18px; }
        .section-title { font-size: 10pt; font-weight: bold; color: #1e40af; background: #eef1fa; padding: 6px 12px; border-radius: 3px; margin-bottom: 0; border-bottom: 2px solid #1e40af; }
        table.items { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        table.items thead tr th { background: #f0f3fa; padding: 6px 8px; text-align: left; font-weight: bold; color: #333; border-bottom: 1px solid #c8d0e0; }
        table.items thead tr th.right { text-align: right; }
        table.items tbody tr td { padding: 5px 8px; border-bottom: 1px solid #eaedf5; color: #222; vertical-align: top; }
        table.items tbody tr td.right { text-align: right; }
        table.items tbody tr:nth-child(even) { background: #f9faff; }

        .totals-block { margin-top: 20px; display: flex; justify-content: flex-end; }
        .totals-table { border-collapse: collapse; min-width: 260px; }
        .totals-table tr td { padding: 6px 12px; font-size: 10pt; border: 1px solid #dde2ef; }
        .totals-table tr td:first-child { color: #555; background: #f8f9ff; }
        .totals-table tr.total td { font-weight: bold; font-size: 12pt; background: #1e40af; color: #fff; border-color: #1e40af; }

        .footer { margin-top: 32px; border-top: 1px solid #dde2ef; padding-top: 10px; font-size: 7.5pt; color: #888; text-align: center; }

        .badge-status { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 8pt; font-weight: bold; }
        .badge-in-progress { background: #dbeafe; color: #1e40af; }
        .badge-warranty { background: #d1fae5; color: #065f46; }
        .badge-archived { background: #f3f4f6; color: #374151; }
    </style>
</head>
<body>
<div class="page">
    {{-- NAGŁÓWEK --}}
    <div class="header">
        <div class="header-left">
            @if($pdfLogoSrc)
                <div class="header-logo"><img src="{{ $pdfLogoSrc }}" alt="Logo"></div>
            @endif
            @if($company && $company->name)
                <div class="header-company-name">{{ $company->name }}</div>
            @endif
        </div>
        <div class="header-right">
            @if($company)
                @if($company->address)<div>{{ $company->address }}</div>@endif
                @if($company->nip)<div>NIP: {{ $company->nip }}</div>@endif
                @if($company->phone)<div>Tel: {{ $company->phone }}</div>@endif
                @if($company->email)<div>{{ $company->email }}</div>@endif
            @endif
        </div>
    </div>

    {{-- TYTUŁ PROJEKTU --}}
    <div class="project-title-block">
        <div class="project-title-left">
            <div class="label">Projekt</div>
            <div class="title">{{ $project->name }}</div>
            <div class="number">Nr: {{ $project->project_number }}</div>
        </div>
    </div>

    {{-- INFORMACJE --}}
    <div class="info-grid">
        <div class="info-box">
            <div class="box-title">Szczegóły projektu</div>
            <div class="row"><b>Nr projektu:</b> {{ $project->project_number }}</div>
            <div class="row">
                <b>Status:</b>
                @if($project->status === 'in_progress') <span class="badge-status badge-in-progress">W toku</span>
                @elseif($project->status === 'warranty') <span class="badge-status badge-warranty">Gwarancja</span>
                @else <span class="badge-status badge-archived">Archiwalne</span>
                @endif
            </div>
            @if($project->responsibleUser)
                <div class="row"><b>Osoba odpowiedzialna:</b> {{ $project->responsibleUser->name }}</div>
            @endif
            @if($project->sourceOffer)
                <div class="row"><b>Oferta źródłowa:</b> {{ $project->sourceOffer->offer_number }} – {{ $project->sourceOffer->offer_title }}</div>
            @endif
        </div>
        <div class="info-box">
            <div class="box-title">Daty</div>
            @if($project->started_at)
                <div class="row"><b>Data rozpoczęcia:</b> {{ $project->started_at->format('d.m.Y') }}</div>
            @endif
            @if($project->finished_at)
                <div class="row"><b>Data zakończenia:</b> {{ $project->finished_at->format('d.m.Y') }}</div>
            @endif
            @if($project->warranty_period)
                <div class="row"><b>Okres gwarancji:</b> {{ $project->warranty_period }} mies.</div>
            @endif
        </div>
        <div class="info-box">
            <div class="box-title">Finansowe</div>
            <div class="row"><b>Cena końcowa:</b> {{ $project->budget ? number_format($project->budget, 2, ',', ' ') . ' zł' : '-' }}</div>
        </div>
    </div>

    {{-- STOPKA --}}
    <div class="footer">
        Wygenerowano dnia {{ now()->format('d.m.Y H:i') }}
        @if($company && $company->name) | {{ $company->name }}@endif
    </div>
</div>
</body>
</html>
