@php
    $company = $company ?? null;
    $showUnitPrices = $showUnitPrices ?? true;

    // Resolve logo for inline HTML
    $logoSrc = null;
    if ($company && $company->logo) {
        if (str_starts_with($company->logo, 'data:image')) {
            $logoSrc = $company->logo;
        } else {
            foreach ([
                storage_path('app/public/' . $company->logo),
                public_path($company->logo),
                public_path('storage/' . $company->logo),
            ] as $path) {
                if (file_exists($path)) {
                    $mime = mime_content_type($path);
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                    break;
                }
            }
        }
    }

    // Calculate totals
    $services   = array_filter($offer->services ?? [],   fn($r) => !empty($r['name']));
    $works      = array_filter($offer->works ?? [],      fn($r) => !empty($r['name']));
    $materials  = array_filter($offer->materials ?? [],  fn($r) => !empty($r['name']));

    $customSectionsRaw = $offer->custom_sections ?? [];
    $builtinKeys = ['services_name','works_name','materials_name','services_enabled','works_enabled','materials_enabled','show_unit_prices'];
    $customSections = array_values(array_filter($customSectionsRaw, fn($v, $k) => is_array($v) && !in_array($k, $builtinKeys, true) && !empty($v['name']), ARRAY_FILTER_USE_BOTH));

    // Section labels from settings
    $servicesLabel  = $customSectionsRaw['services_name']  ?? 'Usługi';
    $worksLabel     = $customSectionsRaw['works_name']     ?? 'Prace własne';
    $materialsLabel = $customSectionsRaw['materials_name'] ?? 'Materiały';

    $grandTotal = (float)$offer->total_price;
@endphp
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oferta {{ $offer->offer_number }} – {{ $offer->offer_title }}</title>
    <style>
        /* ── Reset & base ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 14px; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1c1c2e; background: #f0f2f8; }

        /* ── Print button (hides on print) ─────────────── */
        .print-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            display: flex; align-items: center; justify-content: space-between;
            background: #0F295F; color: #fff;
            padding: 10px 32px; gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }
        .print-bar h1 { font-size: 1rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .print-bar .btns { display: flex; gap: 8px; flex-shrink: 0; }
        .btn-print, .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 18px; border-radius: 6px; border: none; cursor: pointer;
            font-size: .85rem; font-weight: 600; text-decoration: none; line-height: 1;
        }
        .btn-print { background: #f59e0b; color: #1c1c2e; }
        .btn-print:hover { background: #d97706; }
        .btn-back  { background: rgba(255,255,255,.15); color: #fff; }
        .btn-back:hover  { background: rgba(255,255,255,.25); }

        /* ── Wrapper ───────────────────────────────────── */
        .wrapper { max-width: 900px; margin: 0 auto; padding: 76px 20px 48px; }

        /* ── Page ──────────────────────────────────────── */
        .page {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 32px rgba(15,41,95,.10);
            overflow: hidden;
        }

        /* ── Cover bar ─────────────────────────────────── */
        .cover {
            background: linear-gradient(135deg, #0F295F 0%, #1a4a9e 60%, #2563eb 100%);
            padding: 36px 44px 32px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .cover::after {
            content: '';
            position: absolute; right: -60px; top: -60px;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
        }
        .cover-inner { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; }
        .cover-left { flex: 1; }
        .cover-right { flex-shrink: 0; text-align: right; }
        .cover-logo img { max-height: 56px; max-width: 180px; }
        .cover-company { font-size: .85rem; opacity: .85; margin-top: 8px; }
        .cover-label { font-size: .72rem; text-transform: uppercase; letter-spacing: 1.5px; opacity: .7; margin-top: 20px; }
        .cover-title { font-size: 1.65rem; font-weight: 700; margin-top: 4px; line-height: 1.3; }
        .cover-number { font-size: .9rem; opacity: .8; margin-top: 6px; }
        .cover-date { font-size: .82rem; opacity: .7; margin-top: 2px; }
        .cover-meta { display: flex; gap: 28px; margin-top: 16px; }
        .cover-meta-item .cm-label { font-size: .68rem; text-transform: uppercase; letter-spacing: 1px; opacity: .65; }
        .cover-meta-item .cm-val   { font-size: .88rem; font-weight: 600; margin-top: 1px; }

        /* ── Body content ──────────────────────────────── */
        .body { padding: 36px 44px; }

        /* ── Info cards ────────────────────────────────── */
        .cards { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .card-header { background: #f8faff; border-bottom: 1px solid #e2e8f0; padding: 8px 16px; font-size: .68rem; text-transform: uppercase; letter-spacing: 1px; color: #0F295F; font-weight: 700; }
        .card-body { padding: 12px 16px; font-size: .88rem; line-height: 1.7; color: #374151; }
        .card-body b { color: #111827; }

        /* ── Description ───────────────────────────────── */
        .desc-block { background: #f8faff; border-left: 4px solid #2563eb; border-radius: 0 8px 8px 0; padding: 14px 18px; margin-bottom: 28px; font-size: .9rem; color: #374151; line-height: 1.65; }
        .desc-block .block-lbl { font-size: .68rem; text-transform: uppercase; letter-spacing: 1px; color: #0F295F; font-weight: 700; margin-bottom: 6px; }

        /* ── Sections ──────────────────────────────────── */
        .section { margin-bottom: 24px; }
        .section-hdr { display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: #0F295F; border-radius: 8px 8px 0 0; color: #fff; font-weight: 700; font-size: .9rem; }
        .section-hdr .s-dot { width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; flex-shrink: 0; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items thead th { background: #eef2ff; padding: 8px 12px; font-size: .78rem; font-weight: 700; color: #374151; text-align: left; border-bottom: 2px solid #c7d2fe; }
        table.items thead th.r { text-align: right; }
        table.items tbody td { padding: 9px 12px; font-size: .87rem; color: #374151; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.items tbody td.r { text-align: right; }
        table.items tbody tr:nth-child(even) td { background: #f8faff; }
        table.items tbody tr:hover td { background: #eff6ff; }
        table.items tfoot td { padding: 9px 12px; background: #eef2ff; font-weight: 700; font-size: .9rem; color: #0F295F; border-top: 2px solid #c7d2fe; }
        table.items tfoot td.r { text-align: right; }

        /* ── Grand total ───────────────────────────────── */
        .grand-wrap { display: flex; justify-content: flex-end; margin-top: 8px; margin-bottom: 28px; }
        .grand-box { background: linear-gradient(135deg, #0F295F, #1a4a9e); color: #fff; border-radius: 10px; padding: 14px 28px; min-width: 280px; display: flex; justify-content: space-between; align-items: center; gap: 24px; box-shadow: 0 4px 16px rgba(15,41,95,.2); }
        .grand-box .g-lbl { font-size: .85rem; opacity: .85; }
        .grand-box .g-val { font-size: 1.5rem; font-weight: 800; }

        /* ── Payment & schedule ────────────────────────── */
        .terms-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; }
        .terms-card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .terms-card.full { grid-column: 1 / -1; }
        .terms-card .tc-header { background: #f8faff; border-bottom: 1px solid #e2e8f0; padding: 8px 16px; font-size: .68rem; text-transform: uppercase; letter-spacing: 1px; color: #0F295F; font-weight: 700; }
        .terms-card table { width: 100%; border-collapse: collapse; }
        .terms-card table td { padding: 8px 14px; font-size: .87rem; color: #374151; border-bottom: 1px solid #f1f5f9; }
        .terms-card table td:first-child { color: #555; max-width: 60%; }
        .terms-card table td:last-child { font-weight: 600; color: #111; }
        .terms-card table tr:last-child td { border-bottom: none; }

        /* ── Footer / signature ────────────────────────── */
        .doc-footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; }
        .footer-note { font-size: .8rem; color: #9ca3af; max-width: 420px; line-height: 1.5; }
        .sign-box { text-align: center; min-width: 200px; }
        .sign-line { border-top: 1.5px solid #9ca3af; margin: 48px auto 8px; }
        .sign-lbl { font-size: .82rem; color: #6b7280; }

        /* ── Watermark / validity ───────────────────────── */
        .validity-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;
            border-radius: 20px; padding: 4px 12px; font-size: .78rem; font-weight: 600;
            margin-top: 10px;
        }
        .validity-badge svg { width: 14px; height: 14px; }

        /* ── Print styles ──────────────────────────────── */
        @media print {
            .print-bar { display: none !important; }
            body { background: #fff; }
            .wrapper { padding: 0; max-width: 100%; }
            .page { box-shadow: none; border-radius: 0; }
            .cover { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .section-hdr, .grand-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.items tbody tr:hover td { background: transparent; }
        }
    </style>
</head>
<body>

{{-- ── Print bar ──────────────────────────────── --}}
<div class="print-bar">
    <h1>{{ $offer->offer_number }} – {{ $offer->offer_title }}</h1>
    <div class="btns">
        <a href="{{ url()->previous() }}" class="btn-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Wróć
        </a>
        <button onclick="window.print()" class="btn-print">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Drukuj / Zapisz PDF
        </button>
    </div>
</div>

<div class="wrapper">
<div class="page">

    {{-- ── Cover ─────────────────────────────────── --}}
    <div class="cover">
        <div class="cover-inner">
            <div class="cover-left">
                @if($logoSrc)
                    <div class="cover-logo"><img src="{{ $logoSrc }}" alt="Logo"></div>
                @endif
                @if($company && $company->name && !$logoSrc)
                    <div style="font-size:1.2rem;font-weight:700;">{{ $company->name }}</div>
                @endif
                @if($company && $company->name && $logoSrc)
                    <div class="cover-company">{{ $company->name }}</div>
                @endif
                <div class="cover-label" style="margin-top:{{ $logoSrc ? '16px' : '20px' }}">Oferta handlowa</div>
                <div class="cover-title">{{ $offer->offer_title }}</div>
                <div class="cover-number">Nr oferty: <strong>{{ $offer->offer_number }}</strong></div>
                <div class="cover-date">Data: {{ $offer->offer_date ? $offer->offer_date->format('d.m.Y') : now()->format('d.m.Y') }}</div>
            </div>

            <div class="cover-right">
                @if($offer->customer_name)
                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;opacity:.65;">Przygotowano dla</div>
                    <div style="font-size:1.2rem;font-weight:700;margin-top:4px;">{{ $offer->customer_name }}</div>
                @endif
                <div class="validity-badge" style="margin-top:12px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    Ważna 30 dni
                </div>
            </div>
        </div>

        @if($company)
        <div class="cover-meta">
            @if($company->address)<div class="cover-meta-item"><div class="cm-label">Adres</div><div class="cm-val">{{ $company->address }}, {{ $company->postal_code }} {{ $company->city }}</div></div>@endif
            @if($company->nip)<div class="cover-meta-item"><div class="cm-label">NIP</div><div class="cm-val">{{ $company->nip }}</div></div>@endif
            @if($company->phone)<div class="cover-meta-item"><div class="cm-label">Telefon</div><div class="cm-val">{{ $company->phone }}</div></div>@endif
            @if($company->email)<div class="cover-meta-item"><div class="cm-label">E-mail</div><div class="cm-val">{{ $company->email }}</div></div>@endif
        </div>
        @endif
    </div>

    <div class="body">

        {{-- ── Info cards ──────────────────────────── --}}
        @if($offer->customer_name || $offer->customer_nip || $offer->customer_address || $offer->customer_phone || $offer->customer_email)
        <div class="cards" style="grid-template-columns: {{ $offer->crm_deal_id ? '1fr 1fr' : '1fr' }}">
            <div class="card">
                <div class="card-header">Dane klienta</div>
                <div class="card-body">
                    @if($offer->customer_name)<div><b>{{ $offer->customer_name }}</b></div>@endif
                    @if($offer->customer_nip)<div>NIP: {{ $offer->customer_nip }}</div>@endif
                    @if($offer->customer_address)<div>{{ $offer->customer_address }}</div>@endif
                    @if($offer->customer_postal_code || $offer->customer_city)<div>{{ $offer->customer_postal_code }} {{ $offer->customer_city }}</div>@endif
                    @if($offer->customer_phone)<div>Tel: {{ $offer->customer_phone }}</div>@endif
                    @if($offer->customer_email)<div>{{ $offer->customer_email }}</div>@endif
                </div>
            </div>
        </div>
        @endif

        {{-- ── Description ────────────────────────── --}}
        @if($offer->offer_description)
        <div class="desc-block">
            <div class="block-lbl">Opis oferty</div>
            {!! $offer->offer_description !!}
        </div>
        @endif

        {{-- ── Helper macro to render one section table ── --}}
        @php
            function renderSectionTable($rows, $label, $showUnit) {
                if (empty($rows)) return;
                $total = 0;
                foreach ($rows as $r) {
                    $total += (float)($r['price'] ?? 0) * (float)($r['quantity'] ?? 1);
                }
                echo '<div class="section">';
                echo '<div class="section-hdr"><span class="s-dot"></span>' . e($label) . '</div>';
                echo '<table class="items">';
                echo '<thead><tr><th style="width:38%">Nazwa</th><th>Opis</th><th class="r" style="width:70px">Ilość</th>';
                if ($showUnit) echo '<th class="r" style="width:90px">Cena jedn.</th>';
                echo '<th class="r" style="width:100px">Wartość</th></tr></thead>';
                echo '<tbody>';
                foreach ($rows as $r) {
                    $qty = (float)($r['quantity'] ?? 1);
                    $price = (float)($r['price'] ?? 0);
                    $val = $qty * $price;
                    echo '<tr>';
                    echo '<td>' . e($r['name'] ?? '') . '</td>';
                    echo '<td>' . e($r['description'] ?? '') . '</td>';
                    echo '<td class="r">' . number_format($qty, 2, ',', ' ') . '</td>';
                    if ($showUnit) echo '<td class="r">' . number_format($price, 2, ',', ' ') . ' zł</td>';
                    echo '<td class="r">' . number_format($val, 2, ',', ' ') . ' zł</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '<tfoot><tr>';
                echo '<td colspan="' . ($showUnit ? 4 : 3) . '" class="r">Suma ' . e($label) . ':</td>';
                echo '<td class="r">' . number_format($total, 2, ',', ' ') . ' zł</td>';
                echo '</tr></tfoot>';
                echo '</table></div>';
            }
        @endphp

        {{-- Services --}}
        @if(count($services) > 0)
            @php renderSectionTable($services, $servicesLabel, $showUnitPrices); @endphp
        @endif

        {{-- Works --}}
        @if(count($works) > 0)
            @php renderSectionTable($works, $worksLabel, $showUnitPrices); @endphp
        @endif

        {{-- Materials --}}
        @if(count($materials) > 0)
            @php renderSectionTable($materials, $materialsLabel, $showUnitPrices); @endphp
        @endif

        {{-- Custom sections --}}
        @foreach($customSections as $cs)
            @php
                $csRows = array_filter($cs['rows'] ?? $cs['items'] ?? [], fn($r) => !empty($r['name']));
                renderSectionTable($csRows, $cs['name'] ?? 'Sekcja', $showUnitPrices);
            @endphp
        @endforeach

        {{-- ── Grand total ─────────────────────────── --}}
        <div class="grand-wrap">
            <div class="grand-box">
                <span class="g-lbl">Łączna cena oferty netto:</span>
                <span class="g-val">{{ number_format($grandTotal, 2, ',', ' ') }} zł</span>
            </div>
        </div>

        {{-- ── Payment terms / Schedule ────────────── --}}
        @php
            $hasSchedule = $offer->schedule_enabled && !empty($offer->schedule);
            $hasPaymentTerms = !empty($offer->payment_terms);
        @endphp
        @if($hasSchedule || $hasPaymentTerms)
        <div class="terms-grid">
            @if($hasSchedule)
            <div class="terms-card {{ !$hasPaymentTerms ? 'full' : '' }}">
                <div class="tc-header">Harmonogram płatności</div>
                <table>
                    @foreach($offer->schedule as $entry)
                    <tr>
                        <td>{{ $entry['label'] ?? '' }}</td>
                        <td>{{ number_format((float)($entry['amount'] ?? 0), 2, ',', ' ') }} zł</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif

            @if($hasPaymentTerms)
            <div class="terms-card {{ !$hasSchedule ? 'full' : '' }}">
                <div class="tc-header">Warunki płatności</div>
                <table>
                    @foreach($offer->payment_terms as $term)
                    <tr>
                        <td>{{ $term['label'] ?? '' }}</td>
                        <td>{{ $term['value'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif
        </div>
        @endif

        {{-- ── Footer ────────────────────────────────── --}}
        <div class="doc-footer">
            <div class="footer-note">
                Oferta ważna przez 30 dni od daty wystawienia. Ceny netto. Oferta nie stanowi wiążącej umowy.<br>
                Dziękujemy za zainteresowanie naszą ofertą.
            </div>
            <div class="sign-box">
                <div class="sign-line" style="width:200px;"></div>
                <div class="sign-lbl">Podpis i pieczęć</div>
            </div>
        </div>

    </div>{{-- /body --}}
</div>{{-- /page --}}
</div>{{-- /wrapper --}}

</body>
</html>
