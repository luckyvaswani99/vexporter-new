<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proforma Invoice - {{ $order->reference }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1e293b; margin: 0; padding: 40px; background: #f8fafc; }
        .invoice-card { max-width: 800px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 30px; }
        .brand { font-size: 24px; font-weight: 800; color: #e31837; letter-spacing: -0.5px; }
        .subtitle { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 600; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { background: #f8fafc; border: 1px solid #f1f5f9; padding: 15px; border-radius: 8px; font-size: 13px; }
        .info-box h4 { margin: 0 0 8px 0; color: #475569; font-size: 11px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f1f5f9; text-align: left; padding: 10px; font-size: 12px; text-transform: uppercase; color: #475569; }
        td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        .totals { margin-left: auto; width: 280px; font-size: 13px; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; }
        .totals-row.grand { font-size: 16px; font-weight: bold; border-top: 2px solid #1e293b; padding-top: 10px; }
        .bank-details { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 20px; margin-top: 30px; font-size: 13px; }
        .bank-details h3 { margin-top: 0; color: #065f46; font-size: 14px; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-card { border: none; box-shadow: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="max-width: 800px; margin: 0 auto 20px auto; text-align: right;">
        <button onclick="window.print()" style="background: #1e293b; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: bold;">
            Print / Save as PDF
        </button>
    </div>

    <div class="invoice-card">
        <div class="header">
            <div>
                <div class="brand">VEXPORTER</div>
                <div class="subtitle">Where The World Trades</div>
            </div>
            <div style="text-align: right;">
                <h2 style="margin:0; color:#1e293b;">PROFORMA INVOICE</h2>
                <div style="font-size:13px; color:#64748b; margin-top:4px;">No: <strong>{{ $order->reference }}</strong></div>
                <div style="font-size:13px; color:#64748b;">Date: {{ $order->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h4>Exporter / Seller</h4>
                <strong>VEXPORTER GLOBAL TRADING PVT LTD</strong><br>
                Trade Tower, BKC Commercial Complex,<br>
                Mumbai 400051, Maharashtra, India<br>
                GSTIN: 27AAAAA0000A1Z5 · IEC: 0300000000
            </div>
            <div class="info-box">
                <h4>Buyer / Consignee</h4>
                <strong>{{ $order->shipping_address['contact_name'] ?? $order->buyer?->name }}</strong><br>
                {{ $order->shipping_address['company'] ?? '' }}<br>
                {{ $order->shipping_address['line1'] }}, {{ $order->shipping_address['city'] }}<br>
                {{ $order->shipping_address['country_code'] }} · Phone: {{ $order->shipping_address['phone'] }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Vendor</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Unit Price</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->subOrders as $subOrder)
                    @foreach($subOrder->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->name_snapshot }}</strong><br>
                                <span style="font-size:11px; color:#64748b;">SKU: {{ $item->sku }}</span>
                            </td>
                            <td>{{ $subOrder->vendor->name }}</td>
                            <td style="text-align:center;">{{ $item->qty }} {{ $item->unit }}</td>
                            <td style="text-align:right;">{{ \App\Support\Money::format($item->unit_price, $order->currency) }}</td>
                            <td style="text-align:right;">{{ \App\Support\Money::format($item->total, $order->currency) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>{{ \App\Support\Money::format($order->subtotal, $order->currency) }}</span>
            </div>
            <div class="totals-row">
                <span>Freight & Shipping:</span>
                <span>{{ \App\Support\Money::format($order->shipping_total, $order->currency) }}</span>
            </div>
            <div class="totals-row grand">
                <span>Grand Total:</span>
                <span>{{ $order->grand_total_label }}</span>
            </div>
        </div>

        <div class="bank-details">
            <h3>Wire Transfer (T/T) Payment Instructions</h3>
            <table style="margin-bottom:0; background:transparent;">
                @forelse ($bankDetails as $label => $value)
                    <tr>
                        <td style="border:none; padding:4px 0; width:140px;"><strong>{{ $label }}:</strong></td>
                        <td style="border:none; padding:4px 0;">{{ $value }}</td>
                    </tr>
                @empty
                    {{-- Set these in Admin → Finance → Payment methods. --}}
                    <tr>
                        <td colspan="2" style="border:none; padding:4px 0;">
                            Our finance team will send the beneficiary account details separately.
                        </td>
                    </tr>
                @endforelse
                <tr><td style="border:none; padding:4px 0;"><strong>Remittance Note:</strong></td><td style="border:none; padding:4px 0; color:#065f46; font-weight:bold;">Payment for Order {{ $order->reference }}</td></tr>
            </table>
        </div>
    </div>

</body>
</html>
