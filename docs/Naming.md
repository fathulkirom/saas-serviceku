# ServiceKU — Naming Conventions

> Konvensi penamaan di seluruh project. Patuhi agar konsisten dan mudah dicari. Berdasarkan praktik nyata di source code.

---

## 1. Frontend — File & Folder

| Objek | Konvensi | Contoh |
|---|---|---|
| File komponen Vue | PascalCase `.vue` | `KButton.vue`, `ServiceActionBar.vue` |
| Primitif UI | Prefix `K` + PascalCase | `KButton`, `KInput`, `KDialog` |
| Komponen khusus modul | `Components/<Modul>/<Nama>.vue` | `Components/Services/ServiceHeader.vue` |
| Halaman Inertia | `Pages/<Modul>/<Nama>.vue` (nama = route) | `Pages/Services/Show.vue` |
| Composables | camelCase prefix `use` + `.js` | `useFormatter.js`, `useToast.js` |
| Helper non-composable | camelCase `.js` | `layoutHelpers.js`, `statusMaps.js` |
| Icons | `Icons.js` → `getIcon(id)` | key kebab-case (`'dashboard'`, `'checklist'`) |

### Import path
- `@/Components/KX.vue`, `@/Composables/useX.js`, `@/Layouts/X.vue`, `@/Pages/...`.
- Alias `@` = `resources/js`.

---

## 2. Frontend — Variabel & Komponen

| Objek | Konvensi | Contoh |
|---|---|---|
| Props (JS) | camelCase | `modelValue`, `widthClass`, `templatesKeluar` |
| Props (template) | kebab-case | `:templates-keluar="..."`, `:extra-class="..."` |
| Emits | camelCase | `emit('checklist-masuk')` → event `checklist-masuk` (kebab di template) |
| Variabel ref | camelCase | `const processing = ref(null)` |
| Computed | camelCase (`is*`/`can*`/`show*` prefiks) | `isActive`, `canAssign`, `showCost`, `selectedIds` |
| Event handler | `on<Event>` / `handle<Event>` | `onChange`, `onInput`, `handleKeydown`, `closeSearch` |
| Modal refs | `<nama>Modal` | `assignModal`, `completeModal`, `checklistMasukModal` |

---

## 3. Frontend — Komponen `K*` (API)

| Komponen | Props utama | Event |
|---|---|---|
| `KButton` | `variant`, `size`, `shadow`, `type`, `disabled`, `to`, `href`, `target`, `extraClass`, `buttonStyle` | `click` (inherit) |
| `KInput` | `modelValue`, `as`, `type`, `placeholder`, `rows`, `disabled`, `size`, `widthClass`, `extraClass` | `update:modelValue` |
| `KTextarea` | `modelValue`, `rows`, `size`, `widthClass`, `extraClass` | `update:modelValue` |
| `KSelect` | `modelValue`, `size`, `widthClass`, `extraClass` | `update:modelValue` |
| `KCheckbox` | `modelValue`, `value`, `checked`, `disabled`, `trueValue`, `falseValue` | `update:modelValue` |
| `KRadio` | `modelValue`, `value`, `disabled` | `update:modelValue` |
| `KBadge` | `variant`, `extraClass`, `style` | — |
| `KCard` | `title`, `padding`, `hover`, `borderColor` | — |
| `KDialog` | `modelValue`, `maxWidth`, `scrollable` | `update:modelValue` |
| `KDrawer` | `open`, `position`, `width`, `title` | `close` |
| `KAvatar` | `name`, `size`, `style`, `extraClass` | — |
| `KLoading` | `loading`, `size`, `style`, `extraClass` | — |
| `KTable` | `columns`, `rows`, `emptyTitle`, `emptyDescription`, `emptyActionLabel` | `empty-action` |

Detail lengkap: `docs/Component.md`.

---

## 4. Backend — PHP

| Objek | Konvensi | Contoh |
|---|---|---|
| Namespace | `App\Http\Controllers\<Area>` | `Tenant\`, `Admin\`, `Auth\`, `Api\` |
| Controller | PascalCase + `Controller` | `ServiceWorkflowController` |
| Model | PascalCase | `Tenant\User`, `Plan`, `Payment` |
| Model tenant | `App\Models\Tenant\*` | `Tenant\Service`, `Tenant\Branch` |
| Service | PascalCase + `Service` | `PaymentGatewayService` |
| Policy | PascalCase + `Policy` | `ServicePolicy` |
| Middleware | PascalCase (deskriptif) | `CheckSubscription`, `CheckPlanFeature` |
| FormRequest | `Store<Model>Request` | `StoreServiceRequest` |
| Job | PascalCase + aksi | `GenerateInvoicePdf` |
| Mail | PascalCase + `Mail` | `OtpMail`, `WelcomeMail` |
| Notification | PascalCase + `Notification` | `TwoFactorCodeNotification` |
| Trait | PascalCase (prefix `Has`) | `HasRoles`, `HasCustomFields` |
| Command | `kebab-case` (artisan) | `backup:run`, `subscription:check` |

---

## 5. Database

| Objek | Konvensi | Contoh |
|---|---|---|
| Tabel | snake_case jamak | `services`, `checklist_items`, `sale_items` |
| Kolom | snake_case | `tracking_code`, `technician_id`, `created_at` |
| Foreign key | `<tunggal>_id` | `branch_id`, `customer_id` |
| JSON column | snake_case (`data`, `features`) | `Tenant.data`, `Plan.features` |
| Migrasi tenant | `database/migrations/tenant/` | `2024_01_01_000001_create_tenant_core_tables.php` |

---

## 6. Routes

| Objek | Konvensi | Contoh |
|---|---|---|
| Nama route | dot notation | `services.show`, `sales.print`, `settings.theme` |
| Resource | `resources('nama')` | `Route::resource('customers', ...)` |
| Aksi status | `POST /services/{id}/<aksi>` | `services.accept`, `services.finish` |
| Feature gate | `check.plan.feature:<feature>` | `check.plan.feature:services` |

---

## 7. Domain / Domain Keys (harus konsisten!)

### Status servis (string, snake_case) — `useServiceStatus.js` / `Service.php`
`menunggu_alokasi`, `diterima`, `diagnosa`, `dikerjakan`, `menunggu_konfirmasi_pelanggan`, `menunggu_konfirmasi_internal`, `siap_diambil`, `indent`, `onpartner`, `selesai`, `cancel`, `void`, `close`, `diambil`.

### Role user tenant (string)
`owner`, `admin`, `manager`, `head_store`, `cs`, `technician`, `cashier`, `courier`, `custom`.

### Business type tenant
`full_service`, `aksesoris_service`, `aksespare_service`, `gadget_full`, `retail_only`.

### Feature keys plan
`services`, `customers`, `products`, `sales`, `reports`, `settings`, `monitoring`, `multi_branch`, `transfer_stock`, `users`, `expenses`, `purchases`, `deposits`, `checklist`, `indents`, `cash_register`, `master_data`.

### Subscription status
`trial`, `active`, `expired`, `suspended`.

### Payment status
`pending`, `success`, `failed`, `expired`, `refunded`.

---

## 8. Bahasa & Label

- UI & pesan dalam **Bahasa Indonesia** (label, tombol, flash, empty state).
- Label status di UI memakai istilah id-ID (`Menunggu Alokasi`, `Dikerjakan` → "On Progress", `Selesai` → "Finish", dsb. sesuai `useServiceStatus.js`).
- Nama route/URL tetap English (`/services`, `dashboard`).

---

## 9. Aturan Singkat

1. Frontend: komponen PascalCase (+`K` untuk primitif), file kebab/camel sesuai jenis, props camelCase di JS / kebab di template.
2. Backend: PascalCase untuk class, snake_case untuk tabel/kolom.
3. Jangan membuat key status/role/feature baru dengan nama berbeda — gunakan yang sudah ada.
4. Jangan mencampur bahasa: kode (identifier) English, UI (string) Indonesia.
5. Tetap konsisten dengan `docs/Component.md` untuk API komponen.
