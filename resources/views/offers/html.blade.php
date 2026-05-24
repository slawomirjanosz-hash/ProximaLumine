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

    // Section rows
    $services  = array_filter($offer->services  ?? [], fn($r) => !empty($r['name']));
    $works     = array_filter($offer->works     ?? [], fn($r) => !empty($r['name']));
    $materials = array_filter($offer->materials ?? [], fn($r) => !empty($r['name']));

    $customSectionsRaw = $offer->custom_sections ?? [];
    $builtinKeys = ['services_name','works_name','materials_name','services_enabled','works_enabled','materials_enabled','show_unit_prices'];
    $customSections = array_values(array_filter(
        $customSectionsRaw,
        fn($v, $k) => is_array($v) && !in_array($k, $builtinKeys, true) && !empty($v['name']),
        ARRAY_FILTER_USE_BOTH
    ));

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
    <title>Oferta</title>
    <style>
        /* ─── Reset ───────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 13.5px; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; background: #eef0f5; }

        /* ─── Screen print bar ────────────────────────── */
        .print-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            display: flex; align-items: center; justify-content: space-between;
            background: #0F295F; color: #fff;
            padding: 9px 28px; gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        .print-bar h1 { font-size: .93rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .print-bar .btns { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
        .btn-print, .btn-back {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 6px 15px; border-radius: 5px; border: none; cursor: pointer;
            font-size: .82rem; font-weight: 600; text-decoration: none; line-height: 1;
        }
        .btn-print { background: #f59e0b; color: #1c1c2e; }
        .btn-print:hover { background: #d97706; }
        .btn-back { background: rgba(255,255,255,.15); color: #fff; }
        .btn-back:hover { background: rgba(255,255,255,.25); }
        .print-hint { font-size: .72rem; opacity: .7; }

        /* ─── Wrapper & page ──────────────────────────── */
        .wrapper { max-width: 880px; margin: 0 auto; padding: 70px 16px 40px; }
        .page { background: #fff; border-radius: 8px; box-shadow: 0 2px 24px rgba(0,0,0,.1); }

        /* ─── LETTERHEAD ──────────────────────────────── */
        /* White bg, only a thick bottom rule – saves all ink from the old gradient cover */
        .lh {
            padding: 26px 40px 20px;
            border-bottom: 3px solid #0F295F;
            display: flex; align-items: flex-start; justify-content: space-between; gap: 24px;
        }
        .lh-left { flex: 1; }
        .lh-logo img { max-height: 54px; max-width: 200px; display: block; }
        .lh-company { font-size: .97rem; font-weight: 700; color: #0F295F; margin-top: 7px; }
        .lh-right { text-align: right; font-size: .8rem; color: #555; line-height: 1.75; flex-shrink: 0; max-width: 220px; }

        /* ─── OFFER TITLE BLOCK ───────────────────────── */
        /* No fill – just colored text + thin divider */
        .oh {
            padding: 16px 40px 14px;
            border-bottom: 1px solid #d1d5db;
            display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;
        }
        .oh-left { flex: 1; }
        .oh-tag {
            display: inline-block; font-size: .63rem; text-transform: uppercase;
            letter-spacing: 1.2px; color: #0F295F; font-weight: 700;
            border: 1px solid #0F295F; border-radius: 3px; padding: 2px 8px; margin-bottom: 6px;
        }
        .oh-title { font-size: 1.4rem; font-weight: 800; color: #0F295F; line-height: 1.25; }
        .oh-meta { font-size: .82rem; color: #555; margin-top: 5px; }
        .oh-meta strong { color: #111; }
        .oh-right { text-align: right; flex-shrink: 0; }
        .oh-for-lbl { font-size: .63rem; text-transform: uppercase; letter-spacing: 1px; color: #888; }
        .oh-for-name { font-size: 1rem; font-weight: 700; color: #111; margin-top: 2px; }
        .validity-tag {
            display: inline-flex; align-items: center; gap: 4px; margin-top: 6px;
            font-size: .73rem; color: #92400e; font-weight: 600;
            border: 1px solid #d97706; border-radius: 20px; padding: 3px 10px;
        }

        /* ─── BODY ────────────────────────────────────── */
        .body { padding: 22px 40px 34px; }

        /* ─── CLIENT INFO ─────────────────────────────── */
        /* Left accent border only, no fill */
        .client-card {
            border: 1px solid #d1d5db; border-left: 3px solid #0F295F;
            border-radius: 0 5px 5px 0; padding: 12px 16px;
            margin-bottom: 20px; font-size: .87rem; line-height: 1.7; color: #333;
        }
        .client-card .cc-lbl { font-size: .63rem; text-transform: uppercase; letter-spacing: 1px; color: #0F295F; font-weight: 700; margin-bottom: 5px; }

        /* ─── DESCRIPTION ─────────────────────────────── */
        .desc-block {
            border-left: 3px solid #0F295F; padding: 10px 16px;
            margin-bottom: 20px; font-size: .88rem; color: #333; line-height: 1.6;
        }
        .desc-lbl { font-size: .63rem; text-transform: uppercase; letter-spacing: 1px; color: #0F295F; font-weight: 700; margin-bottom: 4px; }

        /* ─── SECTION HEADER ──────────────────────────── */
        /* Left rule + colored bold text, zero ink fill */
        .section { margin-bottom: 18px; }
        .section-hdr {
            font-size: .87rem; font-weight: 700; color: #0F295F;
            padding: 5px 0 5px 10px;
            border-left: 4px solid #0F295F;
            border-bottom: 1px solid #d1d5db;
            margin-bottom: 0;
        }

        /* ─── ITEMS TABLE ─────────────────────────────── */
        /* Lines only, no background fills */
        table.items { width: 100%; border-collapse: collapse; }
        table.items thead th {
            padding: 7px 10px; font-size: .76rem; font-weight: 700;
            color: #333; text-align: left;
            border-bottom: 2px solid #0F295F;
        }
        table.items thead th.r { text-align: right; }
        table.items tbody td {
            padding: 7px 10px; font-size: .85rem; color: #222;
            border-bottom: 1px solid #e5e7eb; vertical-align: top;
        }
        table.items tbody td.r { text-align: right; }
        table.items tfoot td {
            padding: 7px 10px; font-weight: 700; font-size: .88rem;
            color: #0F295F; border-top: 2px solid #0F295F;
        }
        table.items tfoot td.r { text-align: right; }

        /* ─── GRAND TOTAL ─────────────────────────────── */
        /* Double-border box – no fill, colored text */
        .grand-wrap { display: flex; justify-content: flex-end; margin: 10px 0 24px; }
        .grand-box {
            border: 2px solid #0F295F; border-radius: 5px;
            padding: 11px 22px; min-width: 270px;
            display: flex; justify-content: space-between; align-items: baseline; gap: 20px;
        }
        .grand-box .g-lbl { font-size: .85rem; color: #555; }
        .grand-box .g-val { font-size: 1.4rem; font-weight: 800; color: #0F295F; }

        /* ─── PAYMENT / SCHEDULE ──────────────────────── */
        .terms-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px; }
        .terms-card { border: 1px solid #d1d5db; border-radius: 5px; overflow: hidden; }
        .terms-card.full { grid-column: 1 / -1; }
        .terms-card .tc-hdr {
            padding: 6px 14px; font-size: .63rem; text-transform: uppercase;
            letter-spacing: 1px; color: #0F295F; font-weight: 700;
            border-bottom: 1px solid #d1d5db;
        }
        .terms-card table { width: 100%; border-collapse: collapse; }
        .terms-card table td { padding: 7px 14px; font-size: .85rem; color: #333; border-bottom: 1px solid #f0f0f0; }
        .terms-card table td:last-child { font-weight: 600; color: #111; }
        .terms-card table tr:last-child td { border-bottom: none; }

        /* ─── FOOTER / SIGNATURE ──────────────────────── */
        .doc-footer {
            margin-top: 32px; padding-top: 14px; border-top: 1px solid #d1d5db;
            display: flex; justify-content: space-between; align-items: flex-end; gap: 24px;
        }
        .footer-note { font-size: .78rem; color: #888; max-width: 400px; line-height: 1.5; }
        .sign-box { text-align: center; min-width: 180px; }
        .sign-line { border-top: 1px solid #888; margin: 44px auto 7px; width: 180px; }
        .sign-lbl { font-size: .8rem; color: #666; }

        /* ─── PRINT ───────────────────────────────────── */
        @page { margin: 14mm 12mm; size: A4 portrait; }
        @media print {
            .print-bar { display: none !important; }
            body { background: #fff; }
            .wrapper { padding: 0; max-width: 100%; }
            .page { box-shadow: none; border-radius: 0; }
            /* Preserve accent colors on print */
            .lh, .section-hdr, .grand-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

{{-- ── Print bar (screen only) ──────────────── --}}
<div class="print-bar">
    <h1>{{ $offer->offer_number }} – {{ $offer->offer_title }}</h1>
    <div class="btns">
        <a href="{{ url()->previous() }}" class="btn-back">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Wróć
        </a>
        <button onclick="window.print()" class="btn-print">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Drukuj / Zapisz PDF
        </button>
        <span class="print-hint">W oknie druku odznacz „Nagłówki i stopki"</span>
    </div>
</div>

<div class="wrapper">
<div class="page">

    {{-- ── Letterhead ─────────────────────────── --}}
    <div class="lh">
        <div class="lh-left">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo" style="max-height:54px;max-width:200px;display:block;">
            @endif
            @if($company && $company->name)
                <div class="lh-company">{{ $company->name }}</div>
            @endif
        </div>
        <div class="lh-right">
            @if($company)
                @if($company->address)<div>{{ $company->address }}</div>@endif
                @if($company->postal_code || $company->city)<div>{{ $company->postal_code }} {{ $company->city }}</div>@endif
                @if($company->nip)<div><strong>NIP: {{ $company->nip }}</strong></div>@endif
                @if($company->phone)<div>Tel: {{ $company->phone }}</div>@endif
                @if($company->email)<div>{{ $company->email }}</div>@endif
            @endif
        </div>
    </div>

    {{-- ── Offer title block ───────────────────── --}}
    <div class="oh">
        <div class="oh-left">
            <div class="oh-tag">Oferta handlowa</div>
            <div class="oh-title">{{ $offer->offer_title }}</div>
            <div class="oh-meta">
                Nr: <strong>{{ $offer->offer_number }}</strong>
                &nbsp;·&nbsp; Data: <strong>{{ $offer->offer_date ? $offer->offer_date->format('d.m.Y') : now()->format('d.m.Y') }}</strong>
            </div>
        </div>
        @if($offer->customer_name)
        <div class="oh-right">
            <div class="oh-for-lbl">Przygotowano dla</div>
            <div class="oh-for-name">{{ $offer->customer_name }}</div>
            <div class="validity-tag">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                Ważna 30 dni
            </div>
        </div>
        @endif
    </div>

    <div class="body">

        {{-- ── Client info ─────────────────────── --}}
        @if($offer->customer_name || $offer->customer_nip || $offer->customer_address || $offer->customer_phone || $offer->customer_email)
        <div class="client-card">
            <div class="cc-lbl">Dane klienta</div>
            <div>
                @if($offer->customer_name)<strong>{{ $offer->customer_name }}</strong>@endif
                @if($offer->customer_nip) &nbsp;·&nbsp; NIP: {{ $offer->customer_nip }}@endif
                @if($offer->customer_address)<br>{{ $offer->customer_address }}@if($offer->customer_postal_code || $offer->customer_city), {{ $offer->customer_postal_code }} {{ $offer->customer_city }}@endif
                @endif
                @if($offer->customer_phone)<br>Tel: {{ $offer->customer_phone }}@endif
                @if($offer->customer_email)<br>{{ $offer->customer_email }}@endif
            </div>
        </div>
        @endif

        {{-- ── Description ────────────────────── --}}
        @if($offer->offer_description)
        <div class="desc-block">
            <div class="desc-lbl">Opis oferty</div>
            {!! $offer->offer_description !!}
        </div>
        @endif

        {{-- ── Section table helper ────────────── --}}
        @php
            function renderSectionTable($rows, $label, $showUnit) {
                if (empty($rows)) return;
                $total = 0;
                foreach ($rows as $r) {
                    $total += (float)($r['price'] ?? 0) * (float)($r['quantity'] ?? 1);
                }
                echo '<div class="section">';
                echo '<div class="section-hdr">' . e($label) . '</div>';
                echo '<table class="items">';
                echo '<thead><tr>';
                echo '<th style="width:38%">Nazwa</th><th>Opis</th><th class="r" style="width:70px">Ilość</th>';
                if ($showUnit) echo '<th class="r" style="width:90px">Cena jedn.</th>';
                echo '<th class="r" style="width:100px">Wartość</th>';
                echo '</tr></thead><tbody>';
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
                echo '</tbody><tfoot><tr>';
                echo '<td colspan="' . ($showUnit ? 4 : 3) . '" class="r">Razem ' . e($label) . ':</td>';
                echo '<td class="r">' . number_format($total, 2, ',', ' ') . ' zł</td>';
                echo '</tr></tfoot></table></div>';
            }
        @endphp

        @if(count($services) > 0)  @php renderSectionTable($services, $servicesLabel, $showUnitPrices); @endphp @endif
        @if(count($works) > 0)     @php renderSectionTable($works, $worksLabel, $showUnitPrices); @endphp @endif
        @if(count($materials) > 0) @php renderSectionTable($materials, $materialsLabel, $showUnitPrices); @endphp @endif
        @foreach($customSections as $cs)
            @php renderSectionTable(array_filter($cs['rows'] ?? $cs['items'] ?? [], fn($r) => !empty($r['name'])), $cs['name'] ?? 'Sekcja', $showUnitPrices); @endphp
        @endforeach

        {{-- ── Grand total ─────────────────────── --}}
        <div class="grand-wrap">
            <div class="grand-box">
                <span class="g-lbl">Łączna cena oferty netto:</span>
                <span class="g-val">{{ number_format($grandTotal, 2, ',', ' ') }} zł</span>
            </div>
        </div>

        {{-- ── Payment terms / Schedule ─────────── --}}
        @php
            $hasSchedule     = $offer->schedule_enabled && !empty($offer->schedule);
            $hasPaymentTerms = !empty($offer->payment_terms);
        @endphp
        @if($hasSchedule || $hasPaymentTerms)
        <div class="terms-grid">
            @if($hasSchedule)
            <div class="terms-card {{ !$hasPaymentTerms ? 'full' : '' }}">
                <div class="tc-hdr">Harmonogram płatności</div>
                <table>
                    @foreach($offer->schedule as $e)
                    <tr>
                        <td>{{ $e['label'] ?? '' }}</td>
                        <td>{{ number_format((float)($e['amount'] ?? 0), 2, ',', ' ') }} zł</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif
            @if($hasPaymentTerms)
            <div class="terms-card {{ !$hasSchedule ? 'full' : '' }}">
                <div class="tc-hdr">Warunki płatności</div>
                <table>
                    @foreach($offer->payment_terms as $t)
                    <tr>
                        <td>{{ $t['label'] ?? '' }}</td>
                        <td>{{ $t['value'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif
        </div>
        @endif

        {{-- ── Footer ──────────────────────────── --}}
        <div class="doc-footer">
            <div class="footer-note">
                Oferta ważna 30 dni od daty wystawienia. Ceny netto.<br>
                Dziękujemy za zainteresowanie naszą ofertą.
            </div>
            <div class="sign-box">
                <div class="sign-line"></div>
                <div class="sign-lbl">Podpis i pieczęć</div>
            </div>
        </div>

    </div>{{-- /body --}}
</div>{{-- /page --}}
</div>{{-- /wrapper --}}

</body>
</html>
