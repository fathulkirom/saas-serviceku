# ServiceKU — Component Library (Standar `K*`)

> Referensi lengkap komponen UI. **Setiap elemen interaktif/form di halaman wajib memakai komponen ini. Dilarang menulis HTML mentah (`<button>/<input>/<select>/<textarea>`, checkbox/radio) di halaman.**
>
> Lokasi: `resources/js/Components/`. Semua komponen memakai Vue `<script setup>` + props.

---

## 1. Prinsip Umum

### Passthrough (penting!)
Komponen `K*` bersifat **passthrough** (`inheritAttrs`):
- Semua `class`/`style`/attr yang tidak dikenal **diteruskan ke elemen native** yang sama.
- Konsekuensi: `<KButton class="btn btn-primary">` me-render `<button class="btn btn-primary">` — **HTML output identik** dengan elemen mentah.
- Jika parent memberi `class` → class parent dipakai (default komponen tidak ditambahkan). Jika parent **tidak** memberi `class` → komponen memakai default styling konsisten.

### Mengapa passthrough
Agar migrasi tidak mengubah tampilan/HTML. Gunakan `variant`/`size` untuk style standar; gunakan `class`/`style` untuk penyesuaian spesifik.

### Aturan
- Jangan import komponen dari `Components/ui/` (leftover shadcn — tidak dipakai).
- Nama komponen: prefix `K` + PascalCase (`KButton`, `KInput`).
- Untuk aksi modal/konfirmasi selalu pakai `KDialog` (bukan overlay mentah).
- Ekspos aksi dari modal child via `defineExpose({ open })`, panggil dari parent dengan template ref.

---

## 2. KButton — `Components/KButton.vue`

Tombol / link bergaya tombol.

**Props**: `variant` (String, default `''`), `size` (String `''`), `shadow` (Boolean), `type` (String `button`), `disabled` (Boolean), `to` (String → render Inertia `Link`), `href` (String → render `<a>`), `target`, `extraClass`, `buttonStyle`.

**Variant** (memproduksi class/style standar):

| Variant | Output |
|---|---|
| `''` (default) | Passthrough — pakai `class`/`style` parent |
| `primary` | `btn btn-primary` |
| `secondary` | `btn btn-secondary` |
| `danger` | `btn btn-danger` |
| `success` | `btn btn-success` |
| `action-indigo` | `text-white bg-indigo-600` (+ base action `px-3 py-1.5 rounded-lg text-xs font-bold ...`) |
| `action-info` / `action-success` / `action-warning` / `action-danger` | `text-white` + `background: var(--info|success|warning|danger)` |
| `action-blue` | `text-white` + `background: #2563eb` |
| `action-outline` | `background: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color)` |
| `modal-secondary` | `flex-1 px-4 py-2 rounded-xl border text-sm font-semibold ...` |
| `modal-primary` / `-indigo` / `-danger` / `-success` | Tombol footer modal (warna via class/style) |
| `text-danger` | Teks kecil merah (tombol hapus inline) |
| `text-link` | Teks link indigo kecil |

**Size**: untuk varian `action-*`: default `sm` (`px-3 py-1.5 rounded-lg text-xs font-bold`), `md` (`px-4 py-2 rounded-lg text-xs font-bold`). Untuk varian `.btn*`: `xs` → `btn btn-xs`, `lg` → `btn btn-lg`.

**Contoh**:
```vue
<KButton variant="primary" @click="save">Simpan</KButton>
<KButton variant="danger" :disabled="loading" @click="hapus">Hapus</KButton>
<KButton :to="route('sales.show', sale.id)" variant="primary">Lihat</KButton>
<KButton class="px-4 py-2 bg-white border rounded-xl" @click="reset">Reset</KButton> <!-- passthrough -->
```

---

## 3. KInput — `Components/KInput.vue`

Input teks/angka/date/password/file, select, textarea (via prop `as`).

**Props**: `modelValue`, `as` (`input`|`select`|`textarea`, default `input`), `type` (default `text`), `placeholder`, `rows`, `disabled`, `size` (`sm`|`md`|`lg`), `widthClass` (mengganti `w-full`, mis. `w-16`, `flex-1`), `extraClass`, `modelModifiers` (mendukung `.number` — looseToNumber; kosong tetap string).

**Perilaku**:
- `v-model` → bind `value` + `@input`/`@change` → `update:modelValue`.
- `.number` diproses agar empty → string kosong (sama dengan native Vue).
- `type="file"` → tidak bind `value`.
- Jika parent memberi `class` → passthrough; jika tidak → default `rounded-xl border transition-all px-3 py-2 text-sm w-full` + style CSS-var.
- expose `focus()`, `blur()`, `select()`.

**Contoh**:
```vue
<KInput v-model="form.name" class="input" placeholder="Nama" />
<KInput v-model.number="form.qty" type="number" min="1" />
<KInput v-model="x" as="select"><option ...>...</option></KInput> <!-- via slot -->
```

---

## 4. KTextarea — `Components/KTextarea.vue`

Textarea standar. **Props**: `modelValue`, `rows` (default `3`), `disabled`, `size`, `widthClass`, `extraClass`, `modelModifiers`. Default menambahkan `resize-none`. Passthrough class/style. expose `focus/blur/select`.

```vue
<KTextarea v-model="form.notes" rows="3" class="input" />
```

---

## 5. KSelect — `Components/KSelect.vue`

Select standar; **opsi via slot**. **Props**: `modelValue`, `disabled`, `size`, `widthClass`, `extraClass`. Passthrough class/style. expose `focus/blur/select`.

```vue
<KSelect v-model="form.plan" class="input">
  <option value="">Pilih</option>
  <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
</KSelect>
```

---

## 6. KCheckbox — `Components/KCheckbox.vue`

Checkbox dengan 4 mode:
1. **Boolean**: `v-model="bool"`.
2. **Array**: `:value="itemId" v-model="array"` (menambah/menghapus item).
3. **Controlled**: `:checked="x" @change="handler"` (tanpa v-model).
4. **true/false-value**: `:true-value="'1'" :false-value="'0'"`.

**Props**: `modelValue` (Boolean|Array), `value`, `checked`, `disabled`, `trueValue`, `falseValue`. Class/style dari parent diteruskan (mis. `class="rounded"`, `style="accent-color: var(--primary)"`, `class="sr-only peer"`).

```vue
<KCheckbox v-model="form.is_active" class="rounded" />
<KCheckbox :value="item.id" v-model="selectedIds" class="w-4 h-4 rounded" />
<KCheckbox :checked="allSelected" @change="toggleAll" class="w-4 h-4 rounded" />
```

---

## 7. KRadio — `Components/KRadio.vue`

Radio. **Props**: `modelValue` (nilai terpilih), `value` (nilai opsi), `disabled`. `checked = modelValue === value`. Class/style parent diteruskan.

```vue
<KRadio v-model="form.payment_gateway" value="midtrans" class="mr-3" />
```

---

## 8. KBadge — `Components/KBadge.vue`

Badge/tag. **Props**: `variant` (`''`|`success`|`warning`|`danger`|`info`), `extraClass`, `style`. Variant menghasilkan `badge badge-<variant>`. Untuk badge kustom (inline style status) gunakan passthrough class+style.

```vue
<KBadge variant="success">Lunas</KBadge>
<KBadge :style="statusStyle(service.status)">On Progress</KBadge>
```

---

## 9. KCard — `Components/KCard.vue`

Kartu kontainer. **Props**: `title`, `padding` (`none`|`sm`|`md`|`lg`), `hover` (Boolean), `borderColor`. Slot `title`, `action`, default. **Catatan**: KCard memakai style default (`rounded-2xl border-zinc-200 bg-white`); untuk kartu yang memakai CSS-var (`rounded-xl border` + `var(--bg-card)`), gunakan passthrough `class`/`style` atau `<div class="rounded-xl border p-5" :style="...">` (konvensi kartu CSS-var tetap valid).

---

## 10. KDialog — `Components/KDialog.vue`

Modal/Dialog standar. **Props**: `modelValue` (Boolean), `maxWidth` (`sm`→`max-w-sm` | `lg`→`max-w-lg`), `scrollable` (Boolean → `overflow-y-auto py-8`). Overlay `bg-black/40 backdrop-blur-sm`; klik luar menutup; panel `rounded-2xl shadow-2xl p-5 w-full mx-3 border` + CSS-var. Konten via slot.

```vue
<KDialog :model-value="open" @update:model-value="open = $event" max-width="lg" scrollable>
  <h3 class="text-base font-bold mb-4 text-zinc-900">Judul</h3>
  <!-- isi -->
  <div class="flex gap-2 mt-5">
    <KButton variant="modal-secondary" @click="open = false">Batal</KButton>
    <KButton variant="modal-primary-indigo" @click="save">Simpan</KButton>
  </div>
</KDialog>
```

> `KModal.vue` = alias `KDialog` (kompatibilitas). Gunakan `KDialog`.

---

## 11. KDrawer — `Components/KDrawer.vue`

Drawer sisi (alias `Drawer.vue`). **Props** (diteruskan): `open`, `position` (`right`|`left`), `width` (default `448px`), `title`. Slot default + `footer`. Event `close`; Esc menutup; body scroll terkunci saat terbuka.

---

## 12. KAvatar — `Components/KAvatar.vue`

Avatar inisial. **Props**: `name` (dihitung inisialnya), `size` (`sm`|`md`|`lg`), `style`, `extraClass`. Default `flex items-center justify-center font-bold text-white rounded-xl shadow-sm bg-indigo-600`. Isi bisa di-override via slot.

---

## 13. KLoading — `Components/KLoading.vue`

Spinner loading. **Props**: `loading` (Boolean), `size` (`sm`|`md`|`lg`), `style`, `extraClass`. Role `status`. Untuk placeholder tabel/daftar gunakan `Skeleton` (`type`, `count`).

---

## 14. KTable — `Components/KTable.vue`

Tabel data. API berbasis kolom: `:columns` (array `{ key, label }`), `:rows`, `:emptyTitle`, `:emptyDescription`, `:emptyActionLabel`, `@empty-action`. Slot per kolom: `#cell-<key>="{ row }"`.

```vue
<KTable :columns="[{key:'id',label:'ID'}]" :rows="rows" :emptyTitle="'Kosong'">
  <template #cell-id="{ row }">{{ row.id }}</template>
</KTable>
```

> Tabel sederhana lain masih boleh `<table>`; standar: migrasi bertahap ke `KTable`.

---

## 15. Komponen Pendukung Lainnya

| Komponen | Fungsi |
|---|---|
| `Badge` | Badge status (`:status`), varian lama |
| `Drawer` | Implementasi drawer (dipakai `KDrawer`) |
| `Dropdown` / `DropdownLink` | Menu dropdown (slot `trigger`/`content`) |
| `EmptyState` | Empty state (icon + title + description) |
| `PageHeader` | Header halaman (title + description) |
| `Pagination` | Pagination (prop `meta` dari Laravel paginator) |
| `Skeleton` | Placeholder loading (prop `type`, `count`) |
| `StatCard` | Kartu statistik (icon, label, value) |
| `TabPage` | Tab section |
| `Toast` | Notifikasi toast (singleton via `useToast`) |
| `ThemeSwitcher` | Toggle dark/light (localStorage `theme`) |
| `Logo` | Logo (prop `link`, `size`, `theme`) |
| `ProgressBar` | Bar progres (Inertia progress) |
| `DynamicFormFields` | Render kolom custom field tenant |

---

## 16. Menambah Komponen Baru

1. Buat file `resources/js/Components/<Nama>.vue` (PascalCase, prefix `K` bila primitif).
2. Gunakan `<script setup>` + `defineProps`/`defineEmits`/`defineExpose`.
3. Primitif: terapkan **passthrough** (`inheritAttrs`) agar tidak mengubah tampilan parent.
4. Style via **CSS variables** (`var(--primary)` dsb.) atau utility Tailwind; jangan hardcode warna bila ada token.
5. Dokumentasikan props/variant di komentar file.
6. Impor: `import KX from '@/Components/KX.vue'`.
