<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Packing List - {{ $subOrder->reference }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0f172a; margin: 0; padding: 30px; background: #fff; }
        .invoice { max-width: 800px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 30px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th { background: #0f172a; color: #fff; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        @media print { .no-print { display: none; } .invoice { border: none; padding: 0; } }
    </style>
</head>
<body>
    <div class="no-print" style="max-width:800px; margin:0 auto 15px auto; text-align:right;">
        <button onclick="window.print()" style="background:#0f172a; color:#fff; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:bold;">Print / Download PDF</button>
    </div>
    <div class="invoice">
        <div class="header">
            <div>
                <div class="title">EXPORT PACKING LIST</div>
                <div style="font-size:12px; color:#64748b;">Vendor: {{ $subOrder->vendor->name }}</div>
            </div>
            <div style="text-align:right; font-size:12px;">
                <div>Doc No: <strong>EXP-PKL-{{ $subOrder->reference }}</strong></div>
                <div>Date: {{ now()->format('d M Y') }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Package #</th>
                    <th>Item snapshot</th>
                    <th>Qty</th>
                    <th>Batch / Expiry</th>
                    <th>Net Weight</th>
                    <th>Gross Weight</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subOrder->items as $index => $item)
                    <tr>
                        <td>Pkg #{{ $index + 1 }}</td>
                        <td><strong>{{ $item->name_snapshot }}</strong> (SKU: {{ $item->sku }})</td>
                        <td>{{ $item->qty }} {{ $item->unit }}</td>
                        <td>{{ $item->batch_no ?? 'BATCH-'.strtoupper(bin2hex(random_bytes(2))) }} / {{ $item->expiry_date ?? '2028-12' }}</td>
                        <td>{{ number_format(($item->qty * 0.5), 2) }} kg</td>
                        <td>{{ number_format(($item->qty * 0.55), 2) }} kg</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
