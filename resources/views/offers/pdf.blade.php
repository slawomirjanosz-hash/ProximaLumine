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

        /* HEADER */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #0F295F; padding-bottom: 16px; }
        .header-logo img { max-height: 56px; max-width: 180px; }
        .header-company { text-align: right; font-size: 8.5pt; color: #444; line-height: 1.5; }
        .header-company .company-name { font-size: 11pt; font-weight: bold; color: #0F295F; }

        /* OFFER TITLE */
        .offer-title-block { background: #0F295F; color: #fff; padding: 12px 18px; border-radius: 4px; margin-bottom: 20px; }
        .offer-title-block .label { font-size: 8pt; text-transform: uppercase; letter-spacing: 1px; opacity: 0.75; }
        .offer-title-block .title { font-size: 14pt; font-weight: bold; margin-top: 2px; }
        .offer-title-block .number { font-size: 9pt; opacity: 0.85; margin-top: 3px; }

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

        /* CUSTOM SECTIONS */
        .custom-section-name { font-size: 9pt; font-weight: bold; color: #0F295F; margin-top: 4px; padding: 4px 8px; background: #f0f3fa; border-left: 3px solid #0F295F; }

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

        /* FOOTER */
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #dde2ef; display: flex; justify-content: space-between; font-size: 8pt; color: #888; }
        .footer .sign-block { text-align: center; min-width: 140px; }
        .footer .sign-block .sign-line { border-top: 1px solid #aaa; width: 140px; margin: 30px auto 5px; }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-logo">
            @if($company && $company->logo && file_exists(public_path($company->logo)))
                <img src="{{ public_path($company->logo) }}" alt="Logo">
            @elseif(file_exists(public_path('logo.png')))
                <img src="{{ public_path('logo.png') }}" alt="Logo">
            @endif
        </div>
        <div class="header-company">
            @if($company)
                <div class="company-name">{{ $company->name }}</div>
                @if($company->address)<div>{{ $company->address }}</div>@endif
                @if($company->postal_code || $company->city)<div>{{ $company->postal_code }} {{ $company->city }}</div>@endif
                @if($company->nip)<div>NIP: {{ $company->nip }}</div>@endif
                @if($company->phone)<div>Tel: {{ $company->phone }}</div>@endif
                @if($company->email)<div>{{ $company->email }}</div>@endif
            @endif
        </div>
    </div>

    {{-- OFFER TITLE BLOCK --}}
    <div class="offer-title-block">
        <div class="label">Oferta handlowa</div>
        <div class="title">{{ $offer->offer_title }}</div>
        <div class="number">Nr: {{ $offer->offer_number }} &nbsp;|&nbsp; Data: {{ $offer->offer_date ? $offer->offer_date->format('d.m.Y') : '' }}</div>
    </div>

    {{-- INFO GRID --}}
    <div class="info-grid">
        @if($offer->customer_name || $offer->customer_nip || $offer->customer_address)
        <div class="info-box">
            <div class="box-title">Klient</div>
            @if($offer->customer_name)<div class="row"><b>{{ $offer->customer_name }}</b></div>@endif
            @if($offer->customer_nip)<div class="row">NIP: {{ $offer->customer_nip }}</div>@endif
            @if($offer->customer_address)<div class="row">{{ $offer->customer_address }}</div>@endif
            @if($offer->customer_postal_code || $offer->customer_city)<div class="row">{{ $offer->customer_postal_code }} {{ $offer->customer_city }}</div>@endif
            @if($offer->customer_phone)<div class="row">Tel: {{ $offer->customer_phone }}</div>@endif
            @if($offer->customer_email)<div class="row">{{ $offer->customer_email }}</div>@endif
        </div>
        @endif
        @if($offer->crmDeal)
        <div class="info-box">
            <div class="box-title">Szansa CRM</div>
            <div class="row"><b>{{ $offer->crmDeal->name }}</b></div>
            @if($offer->crmDeal->company)<div class="row">{{ $offer->crmDeal->company->name }}</div>@endif
        </div>
        @endif
    </div>

    {{-- DESCRIPTION --}}
    @if($offer->offer_description)
    <div class="description-block">
        <div class="desc-title">Opis oferty</div>
        {{ $offer->offer_description }}
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
                <th class="right" style="width:80px">Cena jedn.</th>
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
                    <td class="right">{{ number_format($price, 2, ',', ' ') }} zł</td>
                    <td class="right">{{ number_format($val, 2, ',', ' ') }} zł</td>
                </tr>
            @endforeach
            <tr class="subtotal">
                <td colspan="4" class="right">Suma usługi:</td>
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
                <th class="right" style="width:80px">Cena jedn.</th>
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
                    <td class="right">{{ number_format($price, 2, ',', ' ') }} zł</td>
                    <td class="right">{{ number_format($val, 2, ',', ' ') }} zł</td>
                </tr>
            @endforeach
            <tr class="subtotal">
                <td colspan="4" class="right">Suma roboty:</td>
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
                <th class="right" style="width:80px">Cena jedn.</th>
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
                    <td class="right">{{ number_format($price, 2, ',', ' ') }} zł</td>
                    <td class="right">{{ number_format($val, 2, ',', ' ') }} zł</td>
                </tr>
            @endforeach
            <tr class="subtotal">
                <td colspan="4" class="right">Suma materiały:</td>
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
                    <th class="right" style="width:80px">Cena jedn.</th>
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
                        <td class="right">{{ number_format($price, 2, ',', ' ') }} zł</td>
                        <td class="right">{{ number_format($val, 2, ',', ' ') }} zł</td>
                    </tr>
                @endforeach
                <tr class="subtotal">
                    <td colspan="4" class="right">Suma {{ $cs['name'] ?? '' }}:</td>
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
            @if(($offer->profit_amount ?? 0) > 0)
            <tr>
                <td>Suma netto (koszty):</td>
                <td>{{ number_format((float)($offer->total_price) - (float)($offer->profit_amount), 2, ',', ' ') }} zł</td>
            </tr>
            <tr>
                <td>Zysk ({{ number_format((float)($offer->profit_percent ?? 0), 2, ',', ' ') }}%):</td>
                <td>{{ number_format((float)($offer->profit_amount), 2, ',', ' ') }} zł</td>
            </tr>
            @endif
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

    {{-- FOOTER --}}
    <div class="footer">
        <div style="color:#888; font-size:8pt;">
            Dokument wygenerowany: {{ now()->format('d.m.Y H:i') }}
        </div>
        <div class="sign-block">
            <div class="sign-line"></div>
            <div>Podpis i pieczęć</div>
        </div>
    </div>

</div>
</body>
</html>
