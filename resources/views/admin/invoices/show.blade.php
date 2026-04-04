<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 mb-1">Invoice</h1>
            <div>No: {{ $invoice->invoice_no }}</div>
            <div>Tarikh: {{ $invoice->issue_date->format('d/m/Y') }}</div>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">Print</button>
    </div>

    <div class="mb-3">
        <div><strong>Pelanggan:</strong> {{ $invoice->order->customer_name }}</div>
        <div><strong>No HP:</strong> {{ $invoice->order->customer_phone }}</div>
        <div><strong>Alamat:</strong> {{ $invoice->order->customer_address }}</div>
    </div>

    <table class="table table-bordered table-sm">
        <thead><tr><th>Design</th><th>Saiz</th><th>Qty</th><th>Harga</th><th>Jumlah</th></tr></thead>
        <tbody>
            @foreach($invoice->order->items as $item)
                <tr>
                    <td>{{ $item->design->name }}</td>
                    <td>{{ $item->size->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>RM {{ number_format($item->unit_price, 2) }}</td>
                    <td>RM {{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot><tr><th colspan="4" class="text-end">Jumlah</th><th>RM {{ number_format($invoice->amount, 2) }}</th></tr></tfoot>
    </table>

    <div><strong>Nota:</strong> {{ $invoice->notes ?: '-' }}</div>
</div>
</body>
</html>
