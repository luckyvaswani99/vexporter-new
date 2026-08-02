<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Origin - {{ $subOrder->reference }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #0f172a; margin: 0; padding: 30px; background: #fff; }
        .invoice { max-width: 800px; margin: 0 auto; border: 2px solid #0f172a; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 15px; margin-bottom: 20px; font-size: 13px; border-radius: 6px; }
        @media print { .no-print { display: none; } .invoice { border: 2px solid #0f172a; padding: 20px; } }
    </style>
</head>
<body>
    <div class="no-print" style="max-width:800px; margin:0 auto 15px auto; text-align:right;">
        <button onclick="window.print()" style="background:#0f172a; color:#fff; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:bold;">Print / Download PDF</button>
    </div>
    <div class="invoice">
        <div class="header">
            <div class="title">CERTIFICATE OF ORIGIN</div>
            <div style="font-size:12px; color:#64748b; margin-top:4px;">Ref No: EXP-COO-{{ $subOrder->reference }}</div>
        </div>

        <div class="box">
            <strong>EXPORTER DETAILS:</strong><br>
            {{ $subOrder->vendor->legal_name ?? $subOrder->vendor->name }}<br>
            City: {{ $subOrder->vendor->city }}, Country: {{ $subOrder->vendor->country_code ?? 'IN' }}
        </div>

        <div class="box">
            <strong>CONSIGNEE DETAILS:</strong><br>
            {{ $order->shipping_address['contact_name'] ?? $order->buyer?->name }}<br>
            Destination: {{ $order->shipping_address['city'] }}, {{ $order->shipping_address['country_code'] }}
        </div>

        <div class="box" style="background:#ecfdf5; border-color:#a7f3d0; color:#065f46;">
            <strong>DECLARATION & CERTIFICATION OF ORIGIN:</strong><br>
            We hereby certify that the goods specified in Sub-Order <strong>{{ $subOrder->reference }}</strong> originated in <strong>INDIA</strong> and conform to the origin laws of export.
        </div>
    </div>
</body>
</html>
