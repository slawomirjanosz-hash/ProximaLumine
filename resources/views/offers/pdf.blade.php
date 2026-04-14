@php
    // Resolve logo for dompdf (supports data URI and file path)
    $pdfLogoSrc = null;
    if ($company && $company->logo) {
        if (str_starts_with($company->logo, 'data:image')) {
            $pdfLogoSrc = $company->logo; // data URI works directly in dompdf
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
    $showUnitPrices = $showUnitPrices ?? true;
@endphp
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Oferta {{ $offer->offer_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; background: #fff; }
        .page { padding: 28px 32px; }

        /* HEADER – logo left + company name at logo height, details right */
        .header { display: table; width: 100%; margin-bottom: 24px; border-bottom: 2px solid #0F295F; padding-bottom: 14px; }
        .header-left { display: table-cell; vertical-align: middle; width: 55%; }
        .header-logo img { max-height: 54px; max-width: 170px; display: block; }
        .header-company-name { font-size: 11pt; font-weight: bold; color: #0F295F; margin-top: 5px; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; font-size: 8.5pt; color: #444; line-height: 1.6; }

        /* OFFER TITLE – flex: left=title+nr, right=customer name */
        .offer-title-block { background: #0F295F; color: #fff; padding: 12px 18px; border-radius: 4px; margin-bottom: 20px; display: table; width: 100%; }
        .offer-title-left { display: table-cell; vertical-align: middle; }
        .offer-title-right { display: table-cell; vertical-align: middle; text-align: right; white-space: nowrap; padding-left: 16px; }
        .offer-title-block .label { font-size: 8pt; text-transform: uppercase; letter-spacing: 1px; opacity: 0.75; }
        .offer-title-block .title { font-size: 14pt; font-weight: bold; margin-top: 2px; }
        .offer-title-block .number { font-size: 9pt; opacity: 0.85; margin-top: 3px; }
        .offer-title-block .customer-label { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.7; }
        .offer-title-block .customer-name { font-size: 12pt; font-weight: bold; margin-top: 3px; }

        /* INFO GRID */
        .info-grid { display: flex; gap: 16px; margin-bottom: 20px; }
        .info-box { flex: 1; border: 1px solid #dde2ef; border-radius: 4px; padding: 10px 14px; background: #f8f9ff; }
        .info-box .box-title { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.8px; color: #0F295F; font-weight: bold; margin-bottom: 6px; border-bottom: 1px solid #dde2ef; padding-bottom: 4px; }
        .info-box .row { font-size: 9pt; color: #333; margin-bottom: 3px; line-height: 1.4; }
        .info-box .row b { color: #111; }

        /* DESCRIPTION */
        .description-block { background: #f8f9ff; border-left: 3px solid #0F295F; padding: 10px 14px; margin-bottom: 20px; font-size: 9.5pt; color: #333; line-height: 1.5; border-radius: 0 4px 4px 0; }
        .description-block .desc-title { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.8px; color: #0F295F; font-weight: bold; margin-bottom: 5px; }

        /* SECTION */
        .section { margin-bottom: 18px; }
        .section-title { font-size: 10pt; font-weight: bold; color: #0F295F; background: #eef1fa; padding: 6px 12px; border-radius: 3px; margin-bottom: 0; border-bottom: 2px solid #0F295F; }
        table.items { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        table.items thead tr th { background: #f0f3fa; padding: 6px 8px; text-align: left; font-weight: bold; color: #333; border-bottom: 1px solid #c8d0e0; }
        table.items thead tr th.right { text-align: right; }
        table.items tbody tr td { padding: 5px 8px; border-bottom: 1px solid #eaedf5; color: #222; vertical-align: top; }
        table.items tbody tr td.right { text-align: right; }
        table.items tbody tr:nth-child(even) { background: #f9faff; }
        table.items tbody tr.subtotal td { border-top: 2px solid #c8d0e0; font-weight: bold; background: #eef1fa; }

        /* TOTALS */
        .totals-block { margin-top: 20px; display: flex; justify-content: flex-end; }
        .totals-table { border-collapse: collapse; min-width: 260px; }
        .totals-table tr td { padding: 6px 12px; font-size: 10pt; border: 1px solid #dde2ef; }
        .totals-table tr td:first-child { color: #555; background: #f8f9ff; }
        .totals-table tr td:last-child { text-align: right; font-weight: bold; min-width: 110px; }
        .totals-table tr.grand td { background: #0F295F; color: #fff; font-size: 12pt; }
        .totals-table tr.grand td:last-child { font-size: 13pt; }

        /* PAYMENT / SCHEDULE */
        .payment-block { margin-top: 18px; background: #f8f9ff; border: 1px solid #dde2ef; border-radius: 4px; padding: 10px 14px; }
        .payment-block .block-title { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.8px; color: #0F295F; font-weight: bold; margin-bottom: 7px; }
        .schedule-row { font-size: 9pt; color: #333; padding: 3px 0; border-bottom: 1px solid #eaedf5; display: flex; justify-content: space-between; }
        .schedule-row:last-child { border-bottom: none; }

        /* FOOTER – signature on the right only */
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #dde2ef; display: flex; justify-content: flex-end; }
        .footer .sign-block { text-align: center; min-width: 160px; font-size: 9pt; color: #555; }
        .footer .sign-block .sign-line { border-top: 1px solid #888; width: 160px; margin: 36px auto 6px; }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
<div class="page">

    {{-- HEADER: logo + company name left / company details right --}}
    <div class="header">
        <div class="header-left">
            @if($pdfLogoSrc)
                <img src="{{ $pdfLogoSrc }}" alt="Logo">
            @endif
            @if($company && $company->name)
                <div class="header-company-name">{{ $company->name }}</div>
            @endif
        </div>
        <div class="header-right">
            @if($company)
                @if($company->address)<div>{{ $company->address }}</div>@endif
                @if($company->postal_code || $company->city)<div>{{ $company->postal_code }} {{ $company->city }}</div>@endif
                @if($company->nip)<div>NIP: {{ $company->nip }}</div>@endif
                @if($company->phone)<div>Tel: {{ $company->phone }}</div>@endif
                @if($company->email)<div>{{ $company->email }}</div>@endif
            @endif
        </div>
    </div>

    {{-- OFFER TITLE BLOCK: title+nr left | customer name right --}}
    <div class="offer-title-block">
        <div class="offer-title-left">
            <div class="label">Oferta handlowa</div>
            <div class="title">{{ $offer->offer_title }}</div>
            <div class="number">Nr: {{ $offer->offer_number }} &nbsp;|&nbsp; Data: {{ $offer->offer_date ? $offer->offer_date->format('d.m.Y') : '' }}</div>
        </div>
        @if($offer->customer_name)
        <div class="offer-title-right">
            <div class="customer-label">Klient</div>
            <div class="customer-name">{{ $offer->customer_name }}</div>
        </div>
        @endif
    </div>

    {{-- INFO GRID (customer details + CRM deal) --}}
    <div class="info-grid">
        @if($offer->customer_nip || $offer->customer_address || $offer->customer_phone || $offer->customer_email)
        <div class="info-box">
            <div class="box-title">Dane klienta</div>
            @if($offer->customer_name)<div class="row"><b>{{ $offer->customer_name }}</b></div>@endif
            @if($offer->customer_nip)<div class="row">NIP: {{ $offer->customer_nip }}</div>@endif
            @if($offer->customer_address)<div class="row">{{ $offer->customer_address }}</div>@endif
            @if($offer->customer_postal_code || $offer->customer_city)<div class="row">{{ $offer->customer_postal_code }} {{ $offer->customer_city }}</div>@endif
            @if($offer->customer_phone)<div class="row">Tel: {{ $offer->customer_phone }}</div>@endif
            @if($offer->customer_email)<div class="row">{{ $offer->customer_email }}</div>@endif
        </div>
        @endif
    </div>

    {{-- DESCRIPTION --}}
    @if($offer->offer_description)
    <div class="description-block">
        <div class="desc-title">Opis oferty</div>
        {!! $offer->offer_description !!}
    </div>
    @endif

    {{-- SERVICES --}}
    @php
        $services = $offer->services ?? [];
        $services = array_filter($services, fn($r) => !empty($r['name']));
    @endphp
    @if(count($services) > 0)
    <div class="section">
        <div class="section-title">Usługi</div>
        <table class="items">
            <thead><tr>
                <th style="width:40%">Nazwa</th>
                <th>Opis</th>
                <th class="right" style="width:70px">Ilość</th>
                @if($showUnitPrices)<th class="right" style="width:80px">Cena jedn.</th>@endif
                <th class="right" style="width:90px">Wartość</th>
            </tr></thead>
            <tbody>
            @php $servTotal = 0; @endphp
            @foreach($services as $row)
                @php
                    $qty = (float)($row['quantity'] ?? 1);
                    $price = (float)($row['price'] ?? 0);
                    $val = $qty * $price;
                    $servTotal += $val;
                @endphp
                <tr>
                    <td>{{ $row['name'] ?? '' }}</td>
                    <td>{{ $row['description'] ?? '' }}</td>
                    <td class="right">{{ number_format($qty, 2, ',', ' ') }}</td>
                    @if($showUnitPrices)<td class="right">{{ number_format($price, 2, ',', ' ') }} zł</td>@endif
                    <td class="right">{{ number_format($val, 2, ',', ' ') }} zł</td>
                </tr>
            @endforeach
            <tr class="subtotal">
                <td colspan="{{ $showUnitPrices ? 4 : 3 }}" class="right">Suma usługi:</td>
                <td class="right">{{ number_format($servTotal, 2, ',', ' ') }} zł</td>
            </tr>
            </tbody>
        </table>
    </div>
    @endif

    {{-- WORKS --}}
    @php
        $works = $offer->works ?? [];
        $works = array_filter($works, fn($r) => !empty($r['name']));
    @endphp
    @if(count($works) > 0)
    <div class="section">
        <div class="section-title">Roboty</div>
        <table class="items">
            <thead><tr>
                <th style="width:40%">Nazwa</th>
                <th>Opis</th>
                <th class="right" style="width:70px">Ilość</th>
                @if($showUnitPrices)<th class="right" style="width:80px">Cena jedn.</th>@endif
                <th class="right" style="width:90px">Wartość</th>
            </tr></thead>
            <tbody>
            @php $worksTotal = 0; @endphp
            @foreach($works as $row)
                @php
                    $qty = (float)($row['quantity'] ?? 1);
                    $price = (float)($row['price'] ?? 0);
                    $val = $qty * $price;
                    $worksTotal += $val;
                @endphp
                <tr>
                    <td>{{ $row['name'] ?? '' }}</td>
                    <td>{{ $row['description'] ?? '' }}</td>
                    <td class="right">{{ number_format($qty, 2, ',', ' ') }}</td>
                    @if($showUnitPrices)<td class="right">{{ number_format($price, 2, ',', ' ') }} zł</td>@endif
                    <td class="right">{{ number_format($val, 2, ',', ' ') }} zł</td>
                </tr>
            @endforeach
            <tr class="subtotal">
                <td colspan="{{ $showUnitPrices ? 4 : 3 }}" class="right">Suma roboty:</td>
                <td class="right">{{ number_format($worksTotal, 2, ',', ' ') }} zł</td>
            </tr>
            </tbody>
        </table>
    </div>
    @endif

    {{-- MATERIALS --}}
    @php
        $materials = $offer->materials ?? [];
        $materials = array_filter($materials, fn($r) => !empty($r['name']));
    @endphp
    @if(count($materials) > 0)
    <div class="section">
        <div class="section-title">Materiały</div>
        <table class="items">
            <thead><tr>
                <th style="width:40%">Nazwa</th>
                <th>Opis</th>
                <th class="right" style="width:70px">Ilość</th>
                @if($showUnitPrices)<th class="right" style="width:80px">Cena jedn.</th>@endif
                <th class="right" style="width:90px">Wartość</th>
            </tr></thead>
            <tbody>
            @php $matsTotal = 0; @endphp
            @foreach($materials as $row)
                @php
                    $qty = (float)($row['quantity'] ?? 1);
                    $price = (float)($row['price'] ?? 0);
                    $val = $qty * $price;
                    $matsTotal += $val;
                @endphp
                <tr>
                    <td>{{ $row['name'] ?? '' }}</td>
                    <td>{{ $row['description'] ?? '' }}</td>
                    <td class="right">{{ number_format($qty, 2, ',', ' ') }}</td>
                    @if($showUnitPrices)<td class="right">{{ number_format($price, 2, ',', ' ') }} zł</td>@endif
                    <td class="right">{{ number_format($val, 2, ',', ' ') }} zł</td>
                </tr>
            @endforeach
            <tr class="subtotal">
                <td colspan="{{ $showUnitPrices ? 4 : 3 }}" class="right">Suma materiały:</td>
                <td class="right">{{ number_format($matsTotal, 2, ',', ' ') }} zł</td>
            </tr>
            </tbody>
        </table>
    </div>
    @endif

    {{-- CUSTOM SECTIONS --}}
    @php $customSections = $offer->custom_sections ?? []; @endphp
    @foreach($customSections as $cs)
        @php
            $csRows = array_filter($cs['rows'] ?? [], fn($r) => !empty($r['name']));
        @endphp
        @if(count($csRows) > 0)
        <div class="section">
            <div class="section-title">{{ $cs['name'] ?? 'Sekcja' }}</div>
            <table class="items">
                <thead><tr>
                    <th style="width:40%">Nazwa</th>
                    <th>Opis</th>
                    <th class="right" style="width:70px">Ilość</th>
                    @if($showUnitPrices)<th class="right" style="width:80px">Cena jedn.</th>@endif
                    <th class="right" style="width:90px">Wartość</th>
                </tr></thead>
                <tbody>
                @php $csTotal = 0; @endphp
                @foreach($csRows as $row)
                    @php
                        $qty = (float)($row['quantity'] ?? 1);
                        $price = (float)($row['price'] ?? 0);
                        $val = $qty * $price;
                        $csTotal += $val;
                    @endphp
                    <tr>
                        <td>{{ $row['name'] ?? '' }}</td>
                        <td>{{ $row['description'] ?? '' }}</td>
                        <td class="right">{{ number_format($qty, 2, ',', ' ') }}</td>
                        @if($showUnitPrices)<td class="right">{{ number_format($price, 2, ',', ' ') }} zł</td>@endif
                        <td class="right">{{ number_format($val, 2, ',', ' ') }} zł</td>
                    </tr>
                @endforeach
                <tr class="subtotal">
                    <td colspan="{{ $showUnitPrices ? 4 : 3 }}" class="right">Suma {{ $cs['name'] ?? '' }}:</td>
                    <td class="right">{{ number_format($csTotal, 2, ',', ' ') }} zł</td>
                </tr>
                </tbody>
            </table>
        </div>
        @endif
    @endforeach

    {{-- TOTALS --}}
    <div class="totals-block">
        <table class="totals-table">
            <tr class="grand">
                <td>Łączna cena oferty:</td>
                <td>{{ number_format((float)$offer->total_price, 2, ',', ' ') }} zł</td>
            </tr>
        </table>
    </div>

    {{-- PAYMENT TERMS / SCHEDULE --}}
    @if($offer->schedule_enabled && !empty($offer->schedule))
    <div class="payment-block" style="margin-top:20px;">
        <div class="block-title">Harmonogram płatności</div>
        @foreach($offer->schedule as $entry)
        <div class="schedule-row">
            <span>{{ $entry['label'] ?? '' }}</span>
            <span>{{ number_format((float)($entry['amount'] ?? 0), 2, ',', ' ') }} zł</span>
        </div>
        @endforeach
    </div>
    @endif

    @if(!empty($offer->payment_terms))
    <div class="payment-block" style="margin-top:12px;">
        <div class="block-title">Warunki płatności</div>
        @foreach($offer->payment_terms as $term)
        <div class="schedule-row">
            <span>{{ $term['label'] ?? '' }}</span>
            <span>{{ $term['value'] ?? '' }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- FOOTER – signature and stamp on the right --}}
    <div class="footer">
        <div class="sign-block">
            <div class="sign-line"></div>
            <div>Podpis i pieczęć</div>
        </div>
    </div>

</div>
</body>
</html>
