# 12 — AI Provider

> **Sprint 6.2B · Blueprint Only.** Provider kecerdasan buatan — klasifikasi, rekomendasi, chat, transkripsi.
> **Prinsip:** Semua AI wajib menjadi Provider. Tidak boleh hardcode model/vendor.

---

## 1. Daftar Provider

| Provider | Kelebihan | Kekurangan | Biaya | Target |
|---|---|---|---|---|
| **OpenAI** (GPT-4, Whisper) | Paling matang, ekosistem besar | Mahal, data ke server US | Per token | Enterprise |
| **Gemini** (Google) | Terintegrasi Google, multimodal | Ekosistem lebih baru | Per token | Enterprise |
| **Claude** (Anthropic) | Keamanan & etika kuat | Lebih lambat | Per token | Enterprise (compliance) |
| **DeepSeek** | Murah, performa bagus | Ekosistem lebih kecil | Per token (murah) | Toko berkembang |
| **Local LLM** (Ollama, llama.cpp) | Gratis, data on-premise, privat | Butuh GPU/server | Infrastruktur | Enterprise on-premise |
| **Future** | — | — | — | — |

---

## 2. Fungsionalitas AI (Blueprint)

| Fungsi | Interface method | Contoh penggunaan | Prioritas |
|---|---|---|---|
| **Klasifikasi** | `classify(text, context)` | Klasifikasi Request type dari chat WA | P1 |
| **Rekomendasi** | `suggest(context)` | Rekomendasi harga servis, sparepart | P2 |
| **Chat / Assistant** | `chat(message, history)` | CS bot, bantu teknisi diagnosa | P2 |
| **Transkripsi** | `transcribe(audio)` | Voice note customer → teks | P2 |
| **Ringkasan** | `summarize(text)` | Ringkasan laporan, catatan servis | P2 |
| **Deteksi** | `detect(image)` | Deteksi kerusakan dari foto servis | Future |

---

## 3. Aturan

1. **AI = opsional** — tidak wajib; tenant mengaktifkan di Settings.
2. **Provider dipilih tenant** — OpenAI / Gemini / DeepSeek / Local.
3. **Data customer TIDAK dikirim ke AI** — kecuali tenant setuju (policy). Data dianonimkan jika perlu.
4. **AI adalah asisten, bukan pengganti** — keputusan akhir tetap manusia.
5. **Fallback**: AI gagal → tidak ada respons AI (bukan error). Fungsionalitas non-AI tetap berjalan.
6. **Prompt template** — prompt AI dikonfigurasi per tenant (policy), bukan hardcode.

---

## 4. Contoh: Klasifikasi Request dari WhatsApp

```
Customer WA: "Mau servis HP Samsung layar pecah, nanti dijemput ya"

→ AIProvider::classify("Mau servis HP Samsung layar pecah, nanti dijemput ya", {
    types: [walk_in, pickup, home_service, ...]
})

→ Response: { type: "pickup", confidence: 0.92, device: "Samsung", issue: "layar pecah" }

→ Request auto-created: type=pickup, device_brand=Samsung, issue="layar pecah"
```

---

## 5. Verifikasi

Konsisten dengan `docs/request-engine/07_RequestFuture.md` (AI auto-classify), `docs/architecture-engine/FutureRoadmap.md` (Sprint 5.2 — AI Assistant), prinsip **Vendor Independence** (tidak terkunci OpenAI), **Simple by Default** (AI = off by default).
