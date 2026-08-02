# ServiceKU — Frontend

> Standar frontend Vue 3 + Inertia + Tailwind. Berdasarkan source code saat ini. **Aturan emas: semua elemen interaktif/form harus memakai komponen `K*` — dilarang HTML mentah di halaman** (lihat `docs/Component.md`).

---

## 1. Stack

| Aspek | Versi / Pilihan |
|---|---|
| Framework | Vue `^3.5` (Composition API, `<script setup>`) |
| Data layer | Inertia.js v3 (`@inertiajs/vue3 ^3.6`) |
| Routing frontend | Ziggy (`ziggy-js ^2.6`) — `route('name', params)` global |
| Styling | TailwindCSS `^3.4` + CSS Variables |
| Build | Vite `^6` (`laravel-vite-plugin`, `@vitejs/plugin-vue`) |
| PWA | `vite-plugin-pwa` (autoUpdate, manifest ServiceKU) |
| Icon | SVG inline via `Components/Icons.js` (`getIcon(id)`) — lucide jarang dipakai |

> **Catatan**: `radix-vue` & `class-variance-authority`/`clsx`/`tailwind-merge` terpasang tetapi **tidak dipakai** oleh aplikasi utama (leftover shadcn di `Components/ui/` — jangan digunakan untuk fitur baru).

---

## 2. Build & Entry

- **Entry**: `resources/js/app.js`
  - `createInertiaApp` + `resolvePageComponent('./Pages/${name}.vue', import.meta.glob('./Pages/**/*.vue'))`.
  - `route()` global dipasang dari Ziggy (`@routes` di `resources/views/app.blade.php`).
  - `progress.color: '#4B5563'`; judul halaman: `ServiceKU - <title>`.
- **Alias**: `@` → `/resources/js` (di `vite.config.js`).
- Perintah: `npm run dev` / `npm run build` / `npm run test:e2e`.
- PWA: manifest + service worker otomatis saat build (icons, shortcuts Dashboard).

---

## 3. Halaman (Pages) & Resolusi

- Setiap halaman = satu file di `resources/js/Pages/<Modul>/<Nama>.vue`, nama sesuai route Inertia (`inertia('Modul/Nama')`).
- Halaman adalah **komponen Vue dengan `<script setup>`** yang menerima **props dari controller**.
- Halaman besar dipecah: section/komponen sub-halaman diletakkan di `resources/js/Components/<Modul>/` (contoh: `Components/Services/`).
- Wajib dibungkus layout:
  - Tenant: `<AuthenticatedLayout>`
  - Admin: `<AdminLayout>`
  - Guest/auth: `<GuestLayout>`
  - Landing/Public/Errors boleh tanpa layout.

---

## 4. Layout System

```
AuthenticatedLayout  (facade: filter menu 4 lapis + branding tenant)
└── LayoutNew         (orkestrator: sidebar/header/content/global-search/toast)
    ├── Sidebar       (nav, branch switcher, logo)
    ├── HeaderBar     (topbar, clock, user dropdown, branch dropdown)
    ├── GlobalSearch  (modal Cmd/Ctrl+K)
    └── Toast         (notifikasi singleton)
```

- **Layout per-halaman, bukan persistent** — setiap halaman membungkus dirinya sendiri.
- `LayoutNew` menyediakan slot `#header` (optional) dan slot default (konten). Konten dibatasi `max-w-7xl mx-auto`.
- **Filter menu** (`AuthenticatedLayout`): plan feature → role → owner custom `menu_access` → plan `default_menus`; ada `onboarding_focus_mode` untuk tenant baru.
- Varian layout: `modern`, `slim`, `classic`, `pro`, `elegant` (dari `user.ui_preferences.layout`).

---

## 5. Shared Props (dari `HandleInertiaRequests`)

Setiap halaman mendapat (via `usePage().props`):

| Prop | Isi |
|---|---|
| `app_env`, `app_version` | Lingkungan & versi |
| `auth.user` | User login (termasuk `role`, `ui_preferences`, `custom_permissions`) |
| `flash.success/error` | Flash message |
| `tenant` | `{ name, id, primary_color }` (cache 5 menit) |
| `demo_mode` | Boolean (cache) |
| `plan_access` | Map fitur → level akses efektif tenant |
| `default_menus` | Menu default per role |
| `onboarding_focus_mode` | Boolean (0 customer & 0 servis) |
| `timezone` | Timezone aplikasi |
| `role_permissions` | Matriks role → permission (owner/admin/manager/head_store/cs/technician/cashier/courier/custom) |

Baca props dengan `const page = usePage(); const user = computed(() => page.props.auth?.user)`.

---

## 6. State Management

**Tidak ada Pinia/Vuex.** Pola yang dipakai:

1. **Inertia server props** — sumber utama data (controller → props).
2. **Composable singleton** — `useToast.js` (toast instance didaftarkan layout, halaman memanggil `useToast().success(msg)`).
3. **`localStorage`** — preferensi tema (dark/light, key `theme`).
4. **Ref lokal + computed** — state UI (modal, tab, filter).
5. **`router.put`/`router.visit`** — persistensi preferensi (`ui_preferences`) ke server.

Jangan memperkenalkan store global baru tanpa persetujuan arsitektur; gunakan Inertia props + composable.

---

## 7. Composables (`resources/js/Composables/`)

| File | Ekspor |
|---|---|
| `useFormatter.js` | `formatNumber`, `formatCurrency`, `formatDate`, `currentDate`, `greeting`, `getInitials` (format id-ID) |
| `useToast.js` | `setToastInstance(instance)`, `useToast()` → `success/error/warning/info` |
| `useServiceStatus.js` | `statusTimeline`, `statusLabel`, `statusDot`, `statusStyle`, `getChecklistItemName`, `formatNumber`, `formatDate` (bulan panjang), `getInitials` — khusus modul Services |
| `layoutHelpers.js` | `groupColors`, `getGroupAccent`, `isActive(href)` — khusus layout |

---

## 8. Routing & Navigasi (Ziggy)

- Gunakan `route('nama.route', params)` di template & script (global dari Ziggy).
- Navigasi: `<Link :href="route(...)">` untuk link; `router.visit`/`router.post` untuk aksi; `router.visit(route(...), { data: { branch_id } })` untuk query.
- Aksi POST ke server: `router.post(route('x.store', id), payload, { preserveScroll: true, onSuccess, onFinish })`.
- Global search (Cmd/Ctrl+K) memanggil `route('search')` dengan `Accept: application/json`.

---

## 9. Konvensi Kode

### `<script setup>` wajib
```vue
<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import KButton from '@/Components/KButton.vue';

const props = defineProps({ service: { type: Object, default: () => ({}) } });
const emit = defineEmits(['save']);
</script>
```

### Aturan
- **Dilarang** `<button>`, `<input>`, `<select>`, `<textarea>`, checkbox/radio mentah di halaman — wajib `KButton`, `KInput`, `KSelect`, `KTextarea`, `KCheckbox`, `KRadio` (lihat `docs/Component.md`).
- Modal → `KDialog`; Drawer → `KDrawer`; Badge → `KBadge`; Avatar → `KAvatar`; Loading → `KLoading`/`Skeleton`.
- Warna/tema lewat CSS variables (`var(--primary)`, `var(--bg-card)`) atau utility Tailwind; **jangan hardcode warna ad-hoc** bila ada token.
- String UI dalam **Bahasa Indonesia** (label, tombol, flash, empty state).
- Props kebab-case di template (`:previous-services`), camelCase di JS.
- Form: pakai `KInput`+`v-model`; checkbox array → `KCheckbox :value :v-model`.
- Toast: `useToast().success('Berhasil disimpan')` setelah aksi sukses.

### Contoh Halaman Minimum
```vue
<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader title="Daftar Servis" description="..." />
    </template>
    <div class="max-w-7xl mx-auto space-y-5">
      <KButton variant="primary" @click="openCreate">Tambah</KButton>
      <KInput v-model="search" placeholder="Cari..." class="input" />
    </div>
  </AuthenticatedLayout>
</template>
```

---

## 10. Performa

- Cache server: dashboard stats & tenant theme pakai `Cache::remember(..., 300, ...)`.
- Hindari N+1: eager-load relasi (`->with(['customer','technician'])`).
- Defer komponen berat bila perlu; gunakan `Skeleton` saat loading.
- Build produksi via `npm run build`; PWA precache otomatis.
