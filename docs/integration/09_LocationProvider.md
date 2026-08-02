# 09 — Location Provider

> **Sprint 6.2B · Blueprint Only.** Provider peta & geolokasi — alamat, jarak, rute.

---

## 1. Daftar Provider

| Provider | Kelebihan | Kekurangan | Biaya |
|---|---|---|---|
| **Google Maps** | Data terlengkap, geocoding akurat, Places API | Berbayar (di atas kuota gratis) | Gratis (kuota) → Berbayar |
| **OpenStreetMap (Nominatim)** | Gratis, open source, tanpa batas | Geocoding kurang akurat di daerah | Gratis |
| **Future** (HERE, Mapbox) | — | — | — |

---

## 2. Fungsi

| Fungsi | Deskripsi | Prioritas |
|---|---|---|
| `geocode(address)` | Alamat → koordinat (lat, lng) | P0 — untuk pickup/home service |
| `reverseGeocode(lat,lng)` | Koordinat → alamat | P1 |
| `distance(origin, destination)` | Jarak & estimasi waktu | P0 — untuk estimasi SLA |
| `autocomplete(query)` | Saran alamat (input UI) | P1 |

---

## 3. Aturan

1. **Default: OpenStreetMap** — gratis, cukup untuk geocoding dasar.
2. **Google Maps = opsi** — tenant dengan API key sendiri.
3. **Alamat disimpan sebagai teks** — geocoding adalah enrichment, bukan data primer.
4. **Pickup/home service** — butuh lokasi akurat; jika geocoding gagal → minta input manual.

---

## 4. Verifikasi

Konsisten dengan `docs/request-engine/04_RequestChannel.md` (pickup/home service butuh alamat), prinsip **Simple by Default** (OpenStreetMap gratis), **Vendor Independence** (tidak terkunci Google).
