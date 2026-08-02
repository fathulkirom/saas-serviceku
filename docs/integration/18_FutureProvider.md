# 18 — Future Provider

> **Sprint 6.2B · Blueprint Only.** Provider baru yang mungkin muncul di masa depan — arsitektur harus siap tanpa perubahan.

---

## 1. Provider Masa Depan

| Provider Type | Provider baru | Mengapa | Kesiapan arsitektur |
|---|---|---|---|
| **Storage** | Wasabi, Storj (decentralized), IPFS | Harga lebih murah, decentralized | ✅ Interface sudah siap |
| **Messaging** | Telegram, LINE, WeChat, RCS | Pasar global | ✅ Interface sudah siap |
| **Payment** | Stripe, PayPal, GoPay langsung, OVO langsung, ShopeePay, Crypto, BNPL | Pasar global / metode baru | ✅ Interface + Hybrid payment |
| **Marketplace** | Bukalapak, Blibli, Amazon | Ekspansi marketplace | ✅ Interface + registry |
| **Printing** | Label printer (Zebra, Dymo), e-Invoice (PDF/A) | Logistik, compliance | ✅ Interface sudah siap |
| **Scanning** | RFID, NFC, Face ID, Fingerprint | Keamanan, identifikasi | ✅ Interface; scan type baru |
| **AI** | Local LLM (Ollama, vLLM), AI Indonesia (bahasa lokal) | Privasi, biaya, bahasa | ✅ Interface + prompt template |
| **Location** | HERE Maps, Mapbox, Badan Informasi Geospasial (BIG) Indonesia | Akurasi lokal, harga | ✅ Interface |
| **Backup** | Wasabi, Backblaze, Hetzner Storage Box | Harga murah | ✅ Interface |
| **Notification** | Telegram Bot, Discord, Slack (internal) | Komunikasi tim | ✅ Interface |
| **Auth** | Apple Sign In, Microsoft, SAML/SSO | Enterprise auth | ✅ Socialite / SAML |
| **IoT** | Device telemetry (suhu, kelembaban gudang), GPS tracker kurir | Smart warehouse | 🔶 Butuh interface baru (`IoTInterface`) |
| **Blockchain** | Verifikasi garansi, sertifikat servis | Anti-pemalsuan | 🔶 Butuh interface baru |
| **E-Sign** | TTD digital tersertifikasi (Privy, Vida, Adobe Sign) | Legal compliance | 🔶 Interface baru / extend existing |

---

## 2. Interface Baru (Future — bukan sekarang)

| Interface | Fungsi | Prioritas |
|---|---|---|
| `IoTInterface` | Terima data sensor, GPS tracker | P2 (Enterprise) |
| `BlockchainInterface` | Verifikasi hash, timestamping | P3 (Research) |
| `ESignInterface` | Tanda tangan digital tersertifikasi | P2 |
| `VideoCallInterface` | Remote diagnostics (video call teknisi↔pelanggan) | P2 |
| `ContractInterface` | e-Contract, terms of service digital | P2 (Corporate) |

---

## 3. Aturan

1. **Provider baru = tidak ubah fondasi** — tambah class, daftar registry.
2. **Interface baru = hati-hati** — perlu ADR karena mengubah domain layer.
3. **Setiap provider masa depan** mengikuti Provider Pattern yang sama.
4. **Registry = extensible** — tidak perlu deployment untuk tambah provider.

---

## 4. Verifikasi

Konsisten dengan `docs/architecture-engine/FutureRoadmap.md` (Sprint 5.2), `docs/domain/FutureExpansion.md` (Sprint 6.1), prinsip **Grow Without Migration**.
