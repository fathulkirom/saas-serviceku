<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota Inden #{{ $indent->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.5; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header .store-name { font-size: 20px; font-weight: bold; margin: 5px 0; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        .title-section { text-align: center; margin: 15px 0; }
        .title-section h2 { margin: 0; font-size: 16px; border: 2px solid #333; display: inline-block; padding: 5px 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px 8px; border: 1px solid #ddd; }
        .info-table .label { font-weight: bold; width: 30%; background: #f9f9f9; }
        .amount-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .amount-table td { padding: 6px 10px; border: 1px solid #ddd; }
        .amount-table .label { font-weight: bold; background: #f9f9f9; }
        .amount-table .value { text-align: right; font-weight: bold; }
        .amount-table .total { font-size: 14px; }
        .terms { margin-top: 20px; font-size: 9px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .terms ol { margin: 5px 0; padding-left: 20px; }
        .terms li { margin-bottom: 3px; }
        .signature { margin-top: 30px; }
        .signature table { width: 100%; }
        .signature td { width: 50%; text-align: center; padding: 10px; }
        .signature .line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; }
        .footer { text-align: center; margin-top: 20px; color: #999; font-size: 9px; border-top: 1px solid #ddd; padding-top: 8px; }
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
        <p>WA: {{ $whatsappNumber }}</p>
    </div>

    <div class="title-section">
        <h2>BUKTI PEMBAYARAN DP / INDENT</h2>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">No. Transaksi</td>
            <td>IND-{{ str_pad($indent->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td class="label">Tanggal</td>
            <td>{{ $indent->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Pelanggan</td>
            <td>{{ $indent->customer?->name ?? '-' }}</td>
            <td class="label">No. HP</td>
            <td>{{ $indent->customer?->phone ?? '-' }}</td>
        </tr>
        @if($indent->service)
        <tr>
            <td class="label">ID Servis</td>
            <td colspan="3">#{{ $indent->service->id }} - {{ $indent->service->customer?->name ?? '-' }}</td>
        </tr>
        @endif
    </table>

    <h3 style="font-size:12px; margin:10px 0 5px;">Detail Sparepart / Produk:</h3>
    <table class="info-table">
        <tr>
            <td class="label" style="width:25%">Nama Produk</td>
            <td style="width:25%">{{ $indent->product_name }}</td>
            <td class="label" style="width:25%">Jumlah</td>
            <td style="width:25%">{{ $indent->qty }} pcs</td>
        </tr>
        @if($indent->description)
        <tr>
            <td class="label">Keterangan</td>
            <td colspan="3">{{ $indent->description }}</td>
        </tr>
        @endif
    </table>

    <h3 style="font-size:12px; margin:10px 0 5px;">Rincian Pembayaran:</h3>
    <table class="amount-table">
        <tr>
            <td class="label" style="width:50%">Total Estimasi Harga</td>
            <td class="value" style="width:50%">Rp {{ number_format($indent->cost_estimate, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Uang Muka (DP)</td>
            <td class="value" style="color:#16a34a;">Rp {{ number_format($indent->deposit, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label total">Sisa Tagihan</td>
            <td class="value total" style="color:#dc2626;">Rp {{ number_format($indent->getRemainingAmount(), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">{{ strtoupper($indent->status) }}</td>
        </tr>
    </table>

    <div class="terms">
        <strong>Syarat & Ketentuan Indent:</strong>
        <ol>
            <li>DP tidak dapat dikembalikan jika pesanan dibatalkan sepihak oleh pelanggan.</li>
            <li>Estimasi kedatangan barang tergantung ketersediaan supplier.</li>
            <li>Pelanggan akan dihubungi via WhatsApp saat barang sudah tersedia.</li>
            <li>Sisa pembayaran dilunasi saat barang datang.</li>
        </ol>
    </div>

    <div class="signature">
        <table>
            <tr>
                <td>
                    <p>Kasir/Admin</p>
                    <div class="line">______________</div>
                </td>
                <td>
                    <p>Pelanggan</p>
                    <div class="line">______________</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} | {{ $storeName }}
    </div>
</body>
</html>
