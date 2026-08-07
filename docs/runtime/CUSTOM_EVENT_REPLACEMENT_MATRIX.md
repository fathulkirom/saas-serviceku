# Custom Event Replacement Matrix

| Event | Old Behavior | Expected Business Action | New Runtime Action | Backend Endpoint | Status |
|-------|--------------|--------------------------|--------------------|------------------|--------|
| `service:create` | `window.dispatchEvent(new CustomEvent('service:create', ...))` | Buka halaman Create Service / buat draft service untuk customer terpilih | Navigasi nyata ke `route('services.create')` via Inertia dengan membawa ID customer. | `GET /services/create` (Vue Page) | WORKING |
| `customer:send-wa` | `window.dispatchEvent(new CustomEvent('customer:send-wa', ...))` | Kirim template pesan WhatsApp ke nomor customer yang terdaftar | Ditandai sebagai action non-operasional sementara (placeholder button) yang akan memanggil integrasi nyata nanti. Saat ini hanya `alert()` atau disabled button. | Tidak Ada (Belum Diimplementasi) | UNWIRED |
| `customer:add-note`| `window.dispatchEvent(new CustomEvent('customer:add-note', ...))` | Menambahkan catatan operasional/log internal ke profil Customer | Form modal riil yang melakukan `POST` via Inertia atau ditandai disabled. Saat ini belum ada backend policy khusus untuk add-note di luar form master. | Tidak Ada (Belum Diimplementasi) | UNWIRED |
