<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota Penjualan #{{ $sale->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 2px 0; color: #666; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 2px 5px; }
        .info td:last-child { text-align: right; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #f5f5f5; padding: 8px 5px; text-align: left; border-bottom: 2px solid #ddd; }
        table.items td { padding: 6px 5px; border-bottom: 1px solid #eee; }
        table.items .text-right { text-align: right; }
        .total { text-align: right; font-size: 14px; }
        .total table { margin-left: auto; width: 300px; }
        .total td { padding: 4px 10px; }
        .total .grand-total { font-weight: bold; font-size: 16px; border-top: 2px solid #333; }
        .footer { text-align: center; margin-top: 30px; color: #999; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $sale->branch->name ?? 'ServiceKU' }}</h1>
        <p>{{ $sale->branch->address ?? '' }}</p>
        <p>Telp: {{ $sale->branch->phone ?? '' }}</p>
        <h2 style="margin-top: 10px;">NOTA PENJUALAN</h2>
        <p>No: INV/{{ $sale->created_at->format('Ymd') }}/{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>Tanggal:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td><strong>Pelanggan:</strong> {{ $sale->customer->name ?? 'Umum' }}</td>
            </tr>
            <tr>
                <td><strong>Tipe:</strong> {{ ucfirst($sale->sale_type) }}</td>
                <td><strong>Metode Bayar:</strong> {{ ucfirst($sale->payment_method ?? 'Cash') }}</td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Item</th>
                <th style="width: 15%;" class="text-right">Qty</th>
                <th style="width: 20%;" class="text-right">Harga</th>
                <th style="width: 15%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->description ?: ($item->product->name ?? 'Item') }}</td>
                <td class="text-right">{{ $item->quantity }} {{ $item->product->unit ?? 'pcs' }}</td>
                <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <table>
            <tr>
                <td>Subtotal</td>
                <td>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($sale->discount > 0)
            <tr>
                <td>Diskon</td>
                <td>Rp {{ number_format($sale->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>Total</td>
                <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bayar</td>
                <td>Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
            </tr>
            @if($sale->change > 0)
            <tr>
                <td>Kembali</td>
                <td>Rp {{ number_format($sale->change, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer">
        <p>Terima kasih telah menggunakan layanan kami</p>
        <p>Nota ini sah sebagai bukti pembayaran</p>
    </div>
</body>
</html>
