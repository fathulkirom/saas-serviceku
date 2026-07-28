<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Checklist Masuk #{{ $service->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.5; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header .store-name { font-size: 18px; font-weight: bold; margin: 5px 0; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        .title-section { text-align: center; margin: 15px 0; }
        .title-section h2 { margin: 0; font-size: 16px; border: 2px solid #333; display: inline-block; padding: 5px 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px 8px; border: 1px solid #ddd; }
        .info-table .label { font-weight: bold; width: 25%; background: #f9f9f9; }
        .checklist-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .checklist-table th { background: #f0f0f0; padding: 6px 8px; text-align: left; border: 1px solid #ddd; font-size: 10px; }
        .checklist-table td { padding: 5px 8px; border: 1px solid #ddd; font-size: 10px; }
        .checklist-table .text-center { text-align: center; }
        .checklist-table .checked { color: #16a34a; font-weight: bold; }
        .checklist-table .unchecked { color: #dc2626; }
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
    </div>

    <div class="title-section">
        <h2>LAPORAN CHECKLIST MASUK</h2>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">No. Servis</td>
            <td>#{{ $service->id }}</td>
            <td class="label">Tanggal</td>
            <td>{{ $service->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Pelanggan</td>
            <td>{{ $service->customer?->name ?? '-' }}</td>
            <td class="label">Teknisi</td>
            <td>{{ $service->technician?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Perangkat</td>
            <td colspan="3">
                {{ $service->kategoriPerangkat?->name ?? '-' }}
                {{ $service->merek?->name ?? '' }}
                {{ $service->tipe_unit ?? '' }}
            </td>
        </tr>
    </table>

    @php 
        $checklist = $service->checklists->where('type', 'masuk')->first(); 
        $activeTemplate = $checklist ? ($checklist->checklistTemplate ?? $checklist->template) : null;
    @endphp

    @if($checklist && $activeTemplate)
    <table class="checklist-table">
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Item Pengecekan</th>
                <th style="width:80px" class="text-center">Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activeTemplate->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $item->item_name }}</td>
                <td class="text-center {{ in_array($item->item_name, $checklist->checked_items ?? []) ? 'checked' : 'unchecked' }}">
                    {{ in_array($item->item_name, $checklist->checked_items ?? []) ? '✓ Berfungsi' : '✗ Rusak/Cacat' }}
                </td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#999; text-align:center; padding:20px;">Belum ada checklist masuk.</p>
    @endif

    <div class="signature">
        <table>
            <tr>
                <td>
                    <p>Teknisi/CS</p>
                    <div class="line">{{ $service->creator?->name ?? '______________' }}</div>
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
