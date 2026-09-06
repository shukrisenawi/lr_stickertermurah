<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #ffffff;
            color: #0f172a;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        .page {
            min-height: 297mm;
            padding: 16mm;
            position: relative;
        }
        .top-stripe {
            height: 5px;
            margin: -16mm -16mm 18px;
            background: #d91c5c;
        }
        .top-stripe:after {
            display: block;
            width: 69%;
            height: 5px;
            margin-left: 24%;
            background: #172036;
            content: '';
        }
        table { border-collapse: collapse; }
        .header { width: 100%; }
        .brand-cell { width: 58%; vertical-align: top; }
        .brand-logo {
            float: left;
            width: 58px;
            height: 58px;
            margin-right: 13px;
        }
        .brand-copy { padding-top: 3px; }
        .brand-name {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: -0.4px;
        }
        .tagline {
            margin: 2px 0 0;
            color: #64748b;
            font-size: 9px;
        }
        .brand-address {
            max-width: 285px;
            margin: 5px 0 0;
            color: #64748b;
            font-size: 8px;
            line-height: 1.45;
            white-space: pre-line;
        }
        .invoice-cell {
            width: 42%;
            text-align: right;
            vertical-align: top;
        }
        .eyebrow {
            margin: 0;
            color: #d91c5c;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .invoice-title {
            margin: 3px 0 8px;
            font-size: 30px;
            font-weight: bold;
            letter-spacing: -1px;
            line-height: 1;
        }
        .invoice-number {
            display: inline-block;
            min-width: 150px;
            padding: 8px 11px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            text-align: right;
        }
        .invoice-number strong { display: block; font-size: 11px; }
        .invoice-number span { display: block; margin-top: 3px; color: #64748b; font-size: 8px; }
        .info { width: 100%; margin-top: 24px; }
        .info-cell {
            width: 50%;
            padding: 13px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .info-cell:first-child { border-radius: 10px 0 0 10px; }
        .info-cell:last-child { border-color: #fbcada; background: #fef1f5; border-radius: 0 10px 10px 0; text-align: right; }
        .section-label {
            margin: 0;
            color: #64748b;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }
        .customer-name { margin: 8px 0 0; font-size: 13px; font-weight: bold; }
        .customer-detail { margin: 3px 0 0; color: #475569; font-size: 9px; line-height: 1.45; white-space: pre-line; }
        .tracking { display: inline-block; margin-top: 8px; padding: 5px 8px; border-radius: 5px; background: #fde5ed; color: #981243; font-size: 8px; font-weight: bold; }
        .status {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 9px;
            border: 1px solid #fbcada;
            border-radius: 20px;
            color: #981243;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .payment-detail { margin: 9px 0 0; color: #475569; font-size: 9px; }
        .details-heading { margin-top: 24px; }
        .details-heading strong { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        .details-heading span { float: right; color: #64748b; font-size: 8px; }
        .items { width: 100%; margin-top: 9px; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
        .items thead { display: table-header-group; }
        .items th {
            padding: 10px 9px;
            background: #0f172a;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.7px;
            text-align: left;
            text-transform: uppercase;
        }
        .items th:nth-child(1) { width: 8%; }
        .items th:nth-child(2) { width: 42%; }
        .items th:nth-child(3) { width: 13%; text-align: right; }
        .items th:nth-child(4) { width: 18%; text-align: right; }
        .items th:nth-child(5) { width: 19%; color: #fbcada; text-align: right; }
        .items td { padding: 10px 9px; border-bottom: 1px solid #f1f5f9; font-size: 9px; vertical-align: top; }
        .items tbody tr:nth-child(even) { background: #f8fafc; }
        .items tbody tr:last-child td { border-bottom: 0; }
        .items td:first-child { color: #64748b; }
        .items td:nth-child(3), .items td:nth-child(4), .items td:nth-child(5) { text-align: right; white-space: nowrap; }
        .items td:nth-child(5) { font-weight: bold; }
        .summary { width: 100%; margin-top: 18px; }
        .notes-cell { width: 58%; padding-right: 18px; vertical-align: top; }
        .notes-box { padding: 11px; border: 1px solid #e2e8f0; border-radius: 9px; background: #f8fafc; }
        .notes-text { margin: 7px 0 0; color: #475569; font-size: 9px; line-height: 1.5; white-space: pre-line; }
        .total-cell { width: 42%; padding: 13px; border: 1px solid #e2e8f0; border-radius: 9px; vertical-align: top; }
        .total-line { width: 100%; }
        .total-line td { padding: 0; color: #64748b; font-size: 9px; }
        .total-line td:last-child { color: #0f172a; font-weight: bold; text-align: right; }
        .total-divider { height: 1px; margin: 13px 0; background: #e2e8f0; }
        .total-label { color: #d91c5c; font-size: 8px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
        .total-amount { margin: 4px 0 0; font-size: 21px; font-weight: bold; text-align: right; }
        .footer { margin-top: 38px; padding-top: 14px; border-top: 1px solid #e2e8f0; }
        .footer table { width: 100%; }
        .footer-message { font-size: 10px; font-weight: bold; }
        .footer-copy { max-width: 250px; margin-top: 4px; color: #64748b; font-size: 8px; line-height: 1.45; }
        .footer-contact { color: #64748b; font-size: 8px; line-height: 1.5; text-align: right; }
        .footer-contact strong { color: #334155; }
        .footer-stripe { height: 5px; margin-top: 18px; background: #d91c5c; }
    </style>
</head>
<body>
    <main class="page">
        <div class="top-stripe"></div>

        <table class="header">
            <tr>
                <td class="brand-cell">
                    @if ($logoDataUri)
                        <img class="brand-logo" src="{{ $logoDataUri }}" alt="{{ $brandName }}">
                    @endif
                    <div class="brand-copy">
                        <h1 class="brand-name">{{ $brandName }}</h1>
                        @if ($brandTagline)
                            <p class="tagline">{{ $brandTagline }}</p>
                        @endif
                        @if ($brandAddress)
                            <p class="brand-address">{{ $brandAddress }}</p>
                        @endif
                    </div>
                </td>
                <td class="invoice-cell">
                    <p class="eyebrow">Dokumen Invoice</p>
                    <h2 class="invoice-title">Invoice</h2>
                    <div class="invoice-number">
                        <strong>{{ $invoice->invoice_no }}</strong>
                        <span>Dikeluarkan {{ $issueDate }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="info">
            <tr>
                <td class="info-cell">
                    <p class="section-label">Kepada</p>
                    <p class="customer-name">{{ $customerName }}</p>
                    <p class="customer-detail">{{ $customerPhone }}</p>
                    <p class="customer-detail">{{ $customerAddress }}</p>
                    @if ($trackingNo)
                        <span class="tracking">No. Tracking J&amp;T: {{ $trackingNo }}</span>
                    @endif
                </td>
                <td class="info-cell">
                    <p class="section-label">Status Bayaran</p>
                    <span class="status">{{ $paymentStatusLabel }}</span>
                    @if ($invoice->payment_type)
                        <p class="payment-detail">Jenis bayaran: <strong>{{ $invoice->payment_type === 'deposit' ? 'Deposit' : ($invoice->payment_type === 'custom' ? 'Jumlah Lain' : 'Bayaran Penuh') }}</strong></p>
                    @endif
                    @if ($paidAt)
                        <p class="payment-detail">Dibayar pada {{ $paidAt }}</p>
                    @endif
                </td>
            </tr>
        </table>

        <div class="details-heading">
            <strong>Butiran Tempahan</strong>
            <span>{{ $items->count() }} item</span>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Bil</th>
                    <th>Penerangan</th>
                    <th>Kuantiti</th>
                    <th>Harga Unit</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>RM {{ number_format($item['unit_price'], 2) }}</td>
                        <td>RM {{ number_format($item['line_total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="color: #64748b; text-align: center;">Tiada item dalam invoice ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td class="notes-cell">
                    @if ($invoice->notes)
                        <div class="notes-box">
                            <p class="section-label">Nota</p>
                            <p class="notes-text">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                </td>
                <td class="total-cell">
                    <table class="total-line">
                        <tr>
                            <td>Jumlah Kuantiti</td>
                            <td>{{ $totalQty }} unit</td>
                        </tr>
                    </table>
                    <div class="total-divider"></div>
                    <div class="total-label">Jumlah Bayaran</div>
                    <div class="total-amount">RM {{ number_format((float) $invoice->amount, 2) }}</div>
                </td>
            </tr>
        </table>

        <footer class="footer">
            <table>
                <tr>
                    <td>
                        <div class="footer-message">Terima kasih atas tempahan anda.</div>
                        <div class="footer-copy">Invoice ini dijana secara elektronik dan sah tanpa tandatangan.</div>
                    </td>
                    <td class="footer-contact">
                        <strong>{{ $brandName }}</strong><br>
                        @if ($brandAddress) {!! nl2br(e($brandAddress)) !!}<br> @endif
                        @if ($brandPhone) {{ $brandPhone }}<br> @endif
                        {{ $brandEmail }}
                    </td>
                </tr>
            </table>
            <div class="footer-stripe"></div>
        </footer>
    </main>
</body>
</html>
