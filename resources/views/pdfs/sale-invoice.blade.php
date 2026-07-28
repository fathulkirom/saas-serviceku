<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $sale->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.5; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header .store-name { font-size: 20px; font-weight: bold; margin: 5px 0; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        .title-section { text-align: center; margin: 15px 0; }
        .title-section h2 { margin: 0; font-size: 16px; border: 2px solid #333; display: inline-block; padding: 5px 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 3px 8px; }
        .info-table .label { font-weight: bold; width: 20%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { background: #f0f0f0; padding: 6px 8px; text-align: left; border: 1px solid #ddd; font-size: 10px; }
        .items-table td { padding: 5px 8px; border: 1px solid #ddd; font-size: 10px; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .total-table { margin-left: auto; width: 250px; }
        .total-table td { padding: 4px 10px; }
        .total-table .label { text-align: right; }
        .total-table .value { text-align: right; }
        .total-table .grand-total { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
        .paid-stamp { text-align: center; margin: 20px 0; }
        .paid-stamp .stamp { display: inline-block; border: 3px solid #16a34a; color: #16a34a; padding: 10px 30px; font-size: 24px; font-weight: bold; transform: rotate(-15deg); border-radius: 10px; }
        .footer { text-align: center; margin-top: 20px; color: #999; font-size: 9px; border-top: 1px solid #ddd; padding-top: 8px; }
        .warranty-info { margin: 10px 0; padding: 8px; border: 1px dashed #2563eb; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @if($storeLogo)
            <img src="{{ public_path($storeLogo) }}" style="height: 50px; margin-bottom: 5px;">
        @endif
        <div class="store-name">{{ $storeName }}</div>
        <p>{{ $storeAddress }}</p>
        <p>Telp: {{ $storePhone }}</p>
    </div>

    <div class="title-section">
        <h2>INVOICE / NOTA PENJUALAN</h2>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">No. Invoice</td>
            <td>#{{ $sale->id }}</td>
            <td class="label">Tanggal</td>
            <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Pelanggan</td>
            <td>{{ $sale->customer?->name ?? 'Umum' }}</td>
            <td class="label">Kasir</td>
            <td>{{ $sale->branch?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tipe</td>
            <td colspan="3">{{ ucfirst($sale->sale_type) }}</td>
        </tr>
    </table>

    @if($sale->service && $sale->service->warranty_days > 0)
    <div class="warranty-info">
        <strong>Garansi Servis:</strong> {{ $sale->service->warranty_days }} hari (Berlaku sampai {{ $sale->service->warranty_expired_at ? $sale->service->warranty_expired_at->format('d/m/Y') : '-' }})
    </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Deskripsi</th>
                <th style="width:50px" class="text-center">Qty</th>
                <th style="width:80px" class="text-right">Harga</th>
                <th style="width:80px" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $item->description ?: ($item->product?->name ?: '-') }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount > 0)
        <tr>
            <td class="label">Diskon</td>
            <td class="value" style="color:#dc2626;">-Rp {{ number_format($sale->discount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td class="label">Total Bayar</td>
            <td class="value">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Dibayar</td>
            <td class="value">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
        </tr>
        @if($sale->change > 0)
        <tr>
            <td class="label">Kembalian</td>
            <td class="value">Rp {{ number_format($sale->change, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Metode Bayar</td>
            <td class="value">{{ $sale->payment_method }}</td>
        </tr>
    </table>

    <div class="paid-stamp">
        <div class="stamp">LUNAS</div>
    </div>

    @if($sale->service && $sale->service->warranty_days > 0)
    <div class="warranty-info">
        <strong>Garansi Servis:</strong> {{ $sale->service->warranty_days }} hari (s/d {{ $sale->service->warranty_expired_at ? $sale->service->warranty_expired_at->format('d/m/Y') : '-' }})<br>
        <em>Kami siap membantu jika ada kendala dalam masa garansi.</em>
    </div>
    @endif

    <div class="footer">
        <p>Terima kasih telah berbelanja di {{ $storeName }}!</p>
        <p>Dicetak pada {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
