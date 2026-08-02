<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Commercial Invoice - {{ $order->reference }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0f172a; margin: 0; padding: 30px; background: #fff; }
        .invoice { max-width: 800px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 30px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: bold; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; font-size: 12px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th { background: #0f172a; color: #fff; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .declaration { background: #fffbeb; border: 1px solid #fef3c7; padding: 10px; font-size: 11px; color: #92400e; margin-top: 20px; }
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
                <div class="title">COMMERCIAL INVOICE</div>
                <div style="font-size:12px; color:#64748b;">VEXPORTER Multivendor B2B Platform</div>
            </div>
            <div style="text-align:right; font-size:12px;">
                <div>Invoice No: <strong>EXP-INV-{{ $order->reference }}</strong></div>
                <div>Date: {{ $order->created_at->format('d M Y') }}</div>
            </div>
        </div>

        <div class="grid">
            <div class="box">
                <strong>EXPORTER / SELLER:</strong><br>
                VEXPORTER GLOBAL TRADING PVT LTD<br>
                BKC Commercial Complex, Mumbai 400051, India<br>
                GSTIN: 27AAAAA0000A1Z5 | IEC: 0300000000
            </div>
            <div class="box">
                <strong>BUYER / IMPORTER:</strong><br>
                {{ $order->shipping_address['contact_name'] ?? $order->buyer?->name }}<br>
                {{ $order->shipping_address['company'] ?? '' }}<br>
                {{ $order->shipping_address['line1'] }}, {{ $order->shipping_address['city'] }}<br>
                Country: {{ $order->shipping_address['country_code'] }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>HSN</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->subOrders as $subOrder)
                    @foreach($subOrder->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->name_snapshot }}</strong><br>
                                <span style="font-size:10px; color:#64748b;">Vendor: {{ $subOrder->vendor->name }}</span>
                            </td>
                            <td>{{ $item->product?->hsn_code ?? '3004' }}</td>
                            <td>{{ $item->qty }} {{ $item->unit }}</td>
                            <td>{{ \App\Support\Money::format($item->unit_price, $order->currency) }}</td>
                            <td style="text-align:right;">{{ \App\Support\Money::format($item->total, $order->currency) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <div style="float:right; width:220px; font-size:12px; text-align:right;">
            <div>Subtotal: {{ \App\Support\Money::format($order->subtotal, $order->currency) }}</div>
            <div>Freight: {{ \App\Support\Money::format($order->shipping_total, $order->currency) }}</div>
            <div style="font-size:14px; font-weight:bold; border-top:1px solid #0f172a; padding-top:4px; margin-top:4px;">
                Total: {{ $order->grand_total_label }}
            </div>
        </div>
        <div style="clear:both;"></div>

        <div class="declaration">
            <strong>STATUTORY EXPORT DECLARATION:</strong><br>
            "SUPPLY MEANT FOR EXPORT UNDER LUT WITHOUT PAYMENT OF INTEGRATED TAX (IGST)".<br>
            We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
        </div>
    </div>
</body>
</html>
