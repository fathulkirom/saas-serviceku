<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tanda Terima Servis #{{ $service->id }}</title>
    <style>
        @page { 
            margin: {{ in_array($paperSize ?? 'a4', ['thermal_80', 'thermal_58']) ? '5px' : '15px 20px' }}; 
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: {{ in_array($paperSize ?? 'a4', ['thermal_80', 'thermal_58']) ? '9px' : '10px' }}; 
            line-height: 1.3; 
            color: #333; 
            margin: 0; 
        }
        .header { text-align: center; margin-bottom: 8px; border-bottom: 1.5px solid #333; padding-bottom: 5px; }
        .header .store-name { font-size: 15px; font-weight: bold; margin: 2px 0; }
        .header p { margin: 1px 0; color: #555; font-size: 8.5px; }
        
        .title-section { text-align: center; margin: 5px 0; }
        .title-section h2 { margin: 0; font-size: 11px; border: 1.5px solid #333; display: inline-block; padding: 2px 10px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-table td { padding: 3px 5px; border: 1px solid #ddd; font-size: 8.5px; }
        .info-table .label { font-weight: bold; width: 30%; background: #f9f9f9; }
        
        .section-title { font-size: 9.5px; font-weight: bold; margin: 6px 0 3px 0; border-bottom: 1px solid #333; padding-bottom: 2px; }
        
        .box { border: 1px solid #ddd; padding: 5px; min-height: 20px; font-size: 8.5px; margin-bottom: 5px; background-color: #fafafa; }
        
        .checklist-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .checklist-table th { background: #f0f0f0; padding: 4px 5px; text-align: left; border: 1px solid #ddd; font-size: 8.5px; }
        .checklist-table td { padding: 3px 5px; border: 1px solid #ddd; font-size: 8.5px; }
        
        .qr-section { text-align: center; margin: 5px 0; padding: 4px; border: 1px solid #eee; background: #fafafa; }
        .qr-section p { font-size: 8.5px; color: #555; margin: 0 0 2px 0; }
        .tracking-url { font-size: 8.5px; font-weight: bold; color: #2563eb; }
        
        .terms { margin-top: 6px; font-size: 8px; color: #666; border-top: 1px solid #ddd; padding-top: 4px; }
        .terms ol { margin: 1px 0; padding-left: 12px; }
        .terms li { margin-bottom: 2px; }
        
        .signature { margin-top: 8px; page-break-inside: avoid; }
        .signature table { width: 100%; }
        .signature td { width: 50%; text-align: center; padding: 2px; }
        .signature .line { border-top: 1px solid #333; margin-top: 25px; padding-top: 2px; font-weight: bold; font-size: 8.5px; }

        /* 2-Column layout helper for A4/A5 */
        .columns { width: 100%; }
        .column { width: 49%; vertical-align: top; }
        .col-left { padding-right: 1%; }
        .col-right { padding-left: 1%; border-left: 1px dashed #ddd; }
    </style>
</head>
<body>
    <div class="header">
        @if($storeLogo)
            <img src="{{ public_path($storeLogo) }}" style="height: 40px; margin-bottom: 3px;">
        @endif
        <div class="store-name">{{ $storeName }}</div>
        <p>{{ $storeAddress }}</p>
        <p>Telp: {{ $storePhone }}</p>
    </div>

    <div class="title-section">
        <h2>TANDA TERIMA SERVIS</h2>
    </div>

    @if(in_array($paperSize ?? 'a4', ['thermal_80', 'thermal_58']))
        <!-- ================= THERMAL LAYOUT (Single Column) ================= -->
        <table class="info-table">
            <tr>
                <td class="label">No. Servis</td>
                <td>#{{ $service->id }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td>{{ $service->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Kode Tracking</td>
                <td><strong>{{ $service->tracking_code }}</strong></td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td>{{ $service->getStatusLabel() }}</td>
            </tr>
            @if($service->technician)
            <tr>
                <td class="label">Teknisi</td>
                <td>{{ $service->technician->name }}</td>
            </tr>
            @endif
        </table>

        <div class="section-title">Pelanggan</div>
        <table class="info-table">
            <tr>
                <td class="label">Nama</td>
                <td>{{ $service->customer?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">No. HP</td>
                <td>{{ $service->customer?->phone ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Perangkat</div>
        <table class="info-table">
            <tr>
                <td class="label">Merek/Tipe</td>
                <td>{{ $service->merek?->name ?? '-' }} {{ $service->tipe_unit ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">IMEI/SN</td>
                <td>{{ $service->imei_sn ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Sandi/Pola</td>
                <td>{{ $service->sandi_pola ?? '-' }}</td>
            </tr>
        </table>

        @if($service->kelengkapan)
        <div class="section-title">Kelengkapan Bawaan</div>
        <div class="box">
            {{ implode(', ', $service->kelengkapan) }}
        </div>
        @endif

        <div class="section-title">Keluhan & Kerusakan</div>
        <div class="box">{{ $service->problem_description ?? '-' }}</div>

        <div class="section-title">Catatan Kondisi</div>
        <div class="box">{{ $service->condition_note ?? '-' }}</div>

        @php 
            $checklist = $service->checklists->where('type', 'masuk')->first(); 
            $activeTemplate = $checklist ? ($checklist->checklistTemplate ?? $checklist->template) : null;
        @endphp
        @if($checklist && $activeTemplate)
        <div class="section-title">Checklist Masuk</div>
        <table class="checklist-table">
            @foreach($activeTemplate->items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td style="width: 50px; text-align: center;">{{ in_array($item->item_name, $checklist->checked_items ?? []) ? '✓ Baik' : '-' }}</td>
            </tr>
            @endforeach
        </table>
        @endif

    @else
        <!-- ================= A4/A5 COMPACT DUAL COLUMN LAYOUT (1 Page Fit Guaranteed) ================= -->
        <table class="columns">
            <tr>
                <!-- LEFT COLUMN -->
                <td class="column col-left">
                    <div class="section-title" style="margin-top:0">Informasi Servis & Pelanggan</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">No. Servis</td>
                            <td>#{{ $service->id }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal</td>
                            <td>{{ $service->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Kode Tracking</td>
                            <td><strong>{{ $service->tracking_code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Pelanggan</td>
                            <td>{{ $service->customer?->name ?? '-' }} ({{ $service->customer?->phone ?? '-' }})</td>
                        </tr>
                    </table>

                    <div class="section-title">Informasi Perangkat</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Kategori</td>
                            <td>{{ $service->kategoriPerangkat?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Merek/Tipe</td>
                            <td>{{ $service->merek?->name ?? '-' }} {{ $service->tipe_unit ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">IMEI/SN</td>
                            <td>{{ $service->imei_sn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Sandi/Pola</td>
                            <td>{{ $service->sandi_pola ?? '-' }}</td>
                        </tr>
                    </table>

                    <div class="section-title">Keluhan & Kerusakan</div>
                    <div class="box">{{ $service->problem_description ?? '-' }}</div>

                    <div class="section-title">Catatan Kondisi Fisik</div>
                    <div class="box">{{ $service->condition_note ?? '-' }}</div>
                </td>

                <!-- RIGHT COLUMN -->
                <td class="column col-right">
                    @if($service->kelengkapan)
                    <div class="section-title" style="margin-top:0">Kelengkapan Bawaan</div>
                    <div class="box" style="min-height: 15px;">
                        {{ implode(', ', $service->kelengkapan) }}
                    </div>
                    @endif

                    @php 
                        $checklist = $service->checklists->where('type', 'masuk')->first(); 
                        $activeTemplate = $checklist ? ($checklist->checklistTemplate ?? $checklist->template) : null;
                    @endphp
                    @if($checklist && $activeTemplate)
                    <div class="section-title" style="margin-top: {{ $service->kelengkapan ? '6px' : '0' }}">Checklist Masuk</div>
                    <table class="checklist-table">
                        <thead>
                            <tr>
                                <th>Item Pengecekan</th>
                                <th style="width: 50px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeTemplate->items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td style="text-align: center; font-weight: bold; color: {{ in_array($item->item_name, $checklist->checked_items ?? []) ? '#16a34a' : '#888' }}">
                                    {{ in_array($item->item_name, $checklist->checked_items ?? []) ? '✓ Baik' : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <!-- QR & Status Tracking -->
    <div class="qr-section">
        <p>Scan atau kunjungi tautan berikut untuk memantau status servis secara realtime:</p>
        <div class="tracking-url">{{ url('/track/' . $service->tracking_code) }}</div>
    </div>

    <!-- Syarat & Ketentuan -->
    <div class="terms">
        <strong>Syarat & Ketentuan Servis:</strong>
        <ol>
            <li>Toko tidak bertanggung jawab atas hilangnya data/sistem selama proses perbaikan berlangsung.</li>
            <li>Estimasi biaya awal bersifat tidak mengikat dan dapat disesuaikan setelah dibongkar.</li>
            <li>Garansi unit hanya berlaku pada komponen/bagian yang diganti sesuai dengan nota invoice resmi.</li>
            <li>Barang servis yang tidak diambil dalam waktu 90 hari di luar tanggung jawab toko.</li>
        </ol>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature">
        <table>
            <tr>
                <td>
                    <p>Penerima (Toko)</p>
                    <div class="line">{{ $service->creator?->name ?? 'Penerima' }}</div>
                </td>
                <td>
                    <p>Penyerah (Pelanggan)</p>
                    <div class="line">Pelanggan</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer" style="text-align:center; margin-top:10px; color:#999; font-size:7.5px; border-top:1px dashed #ddd; padding-top:4px;">
        Dokumen ini dicetak pada {{ now()->format('d/m/Y H:i') }} | {{ $storeName }}
    </div>
</body>
</html>
