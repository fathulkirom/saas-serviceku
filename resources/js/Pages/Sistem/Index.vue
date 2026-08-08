<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader :title="pageTitle" :subtitle="subtitle">
        <KButton  v-if="activeTab === 'pengguna'" @click="openUserModal()"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold sk-bg-primary hover:sk-bg-primary text-white transition-all shadow-sm hover:shadow-md">
          + Tambah Pengguna
        </KButton>
        <KButton  v-if="activeTab === 'cabang'" @click="openBranchModal()"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold sk-bg-primary hover:sk-bg-primary text-white transition-all shadow-sm hover:shadow-md">
          + Tambah Cabang
        </KButton>
        <KButton  v-if="activeTab === 'shift'" @click="openShiftModal()"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold sk-bg-primary hover:sk-bg-primary text-white transition-all shadow-sm hover:shadow-md">
          + Tambah Shift Kerja
        </KButton>
        <KButton  v-if="activeTab === 'absensi'" @click="openAttendanceModal()"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold sk-bg-primary hover:sk-bg-primary text-white transition-all shadow-sm hover:shadow-md">
          + Catat Absensi Manual
        </KButton>
        <KButton  v-if="canManageDelegations && activeTab === 'delegasi'" @click="openDelegationModal()"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold sk-bg-primary hover:sk-bg-primary text-white transition-all shadow-sm hover:shadow-md">
          + Beri Delegasi
        </KButton>
      </PageHeader>
    </template>

    <TabPage :tabs="tabs" v-model="activeTab" @update:model-value="switchTab">
      <!-- MANAJEMEN PENGGUNA -->
      <template #pengguna>
        <div class="space-y-6">
          <Skeleton v-if="!users" type="table" :count="5" />
          <KTable
            v-else
            :columns="userColumns"
            :rows="users?.data ?? []"
            :emptyTitle="'Belum ada data pengguna'"
            :emptyDescription="'Data pengguna akun akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Tambah Pengguna Baru'"
            @empty-action="openUserModal()"
          >
            <template #cell-name="{ row }">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0"
                  :style="{ background: 'var(--primary)' }">{{ getInitials(row.name) }}</div>
                <div>
                  <span class="font-medium text-sm">{{ row.name }}</span>
                  <p class="text-[11px] sk-text-muted">{{ row.email }}</p>
                </div>
              </div>
            </template>
            <template #cell-role="{ row }">
              <Badge :variant="roleVariant(row.role)">{{ row.role }}</Badge>
            </template>
            <template #cell-branch_name="{ row }">
              <!-- BR-FIX-02: primary/home branch + additional branch access -->
              <div class="flex flex-wrap items-center gap-1">
                <Badge variant="blue">{{ row.branch?.name ?? '-' }}</Badge>
                <template v-if="(row.branches ?? []).length">
                  <span v-for="b in row.branches" :key="b.id">
                    <Badge variant="indigo">{{ b.name }}</Badge>
                  </span>
                </template>
                <span v-else-if="!row.branch" class="text-[11px] sk-text-muted">Global</span>
              </div>
            </template>
            <template #cell-active="{ row }">
              <Badge :variant="row.active ? 'green' : 'red'">{{ row.active ? 'Aktif' : 'Nonaktif' }}</Badge>
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center gap-1 justify-end">
                <KButton  @click="openMenuAccessModal(row)" class="text-xs px-2 py-1 rounded border font-medium transition-colors"
                  style="borderColor: var(--border-color); color: #8e44ad;">Akses Menu</KButton>
                <KButton  @click="openUserModal(row)" class="text-xs px-2 py-1 rounded border font-medium transition-colors sk-border sk-text-primary-brand">Edit</KButton>
                <KButton  @click="toggleUser(row)" class="text-xs px-2 py-1 rounded border font-medium transition-colors"
                  :style="{ borderColor: row.active ? '#fca5a5' : '#86efac', color: row.active ? '#ef4444' : '#10b981' }">{{ row.active ? 'Nonaktifkan' : 'Aktifkan' }}</KButton>
              </div>
            </template>
          </KTable>

          <Pagination :meta="users" />
        </div>
      </template>

      <!-- MANAJEMEN CABANG -->
      <template #cabang>
        <div class="space-y-6">
          <Skeleton v-if="!branches" type="table" :count="5" />
          <KTable
            v-else
            :columns="branchColumns"
            :rows="branches?.data ?? branches ?? []"
            :emptyTitle="'Belum ada data cabang'"
            :emptyDescription="'Data cabang toko akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Tambah Cabang Baru'"
            @empty-action="openBranchModal()"
          >
            <template #cell-name="{ row }">
              <span class="font-medium text-sm">{{ row.name }}</span>
              <p class="text-[11px] sk-text-muted">{{ row.address || '-' }}</p>
            </template>
            <template #cell-users_count="{ row }">
              {{ formatNumber(row.users_count ?? 0) }} orang
            </template>
            <template #cell-services_count="{ row }">
              {{ formatNumber(row.services_count ?? 0) }} servis
            </template>
            <template #cell-products_count="{ row }">
              {{ formatNumber(row.products_count ?? 0) }} produk
            </template>
            <template #cell-is_active="{ row }">
              <Badge :variant="row.is_active ? 'green' : 'red'">{{ row.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <KButton  @click="openBranchModal(row)" class="px-2.5 py-1 rounded text-xs font-medium border sk-border sk-text-primary-brand">Edit</KButton>
              </div>
            </template>
          </KTable>
        </div>
      </template>

      <!-- SHIFT KERJA -->
      <template #shift>
        <div class="space-y-6">
          <Skeleton v-if="!shifts" type="table" :count="5" />
          <KTable
            v-else
            :columns="shiftColumns"
            :rows="shifts?.data ?? shifts ?? []"
            :emptyTitle="'Belum ada data shift'"
            :emptyDescription="'Data shift kerja akan muncul setelah ditambahkan.'"
            :emptyActionLabel="'+ Tambah Shift Kerja'"
            @empty-action="openShiftModal()"
          >
            <template #cell-name="{ row }">
              <span class="font-medium">{{ row.name }}</span>
            </template>
            <template #cell-start_time="{ row }">
              <span class="font-mono text-xs font-bold">{{ row.start_time }}</span>
            </template>
            <template #cell-end_time="{ row }">
              <span class="font-mono text-xs font-bold">{{ row.end_time }}</span>
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center justify-end gap-1">
                <KButton  @click="openShiftModal(row)" class="px-2.5 py-1 rounded text-xs font-medium border sk-border sk-text-primary-brand">Edit</KButton>
                <KButton  @click="deleteShift(row)" class="px-2.5 py-1 rounded text-xs font-medium sk-text-danger border sk-border-primary hover:sk-bg-danger-soft">Hapus</KButton>
              </div>
            </template>
          </KTable>
        </div>
      </template>

      <!-- ABSENSI KARYAWAN -->
      <template #absensi>
        <div class="space-y-6">
          <Skeleton v-if="!attendances" type="table" :count="5" />
          <KTable
            v-else
            :columns="attendanceColumns"
            :rows="attendances?.data ?? attendances ?? []"
            :emptyTitle="'Belum ada data absensi'"
            :emptyDescription="'Data absensi akan muncul setelah karyawan melakukan check-in.'"
            :emptyActionLabel="'+ Catat Absensi Manual'"
            @empty-action="openAttendanceModal()"
          >
            <template #cell-user_name="{ row }">
              <span class="font-medium">{{ row.user?.name ?? '-' }}</span>
            </template>
            <template #cell-check_in="{ row }">
              {{ formatDate(row.check_in) }}
            </template>
            <template #cell-check_out="{ row }">
              {{ row.check_out ? formatDate(row.check_out) : '-' }}
            </template>
            <template #cell-status="{ row }">
              <Badge :variant="row.status === 'present' ? 'green' : row.status === 'late' ? 'yellow' : 'red'">
                {{ row.status === 'present' ? 'Hadir' : row.status === 'late' ? 'Terlambat' : row.status }}
              </Badge>
            </template>
            <template #cell-action="{ row }">
              <KSelect  @change="updateAttendanceStatus(row, $event.target.value)" class="text-xs py-1 px-2 rounded border font-semibold sk-bg-card sk-border">
                <option disabled selected>Ubah Status</option>
                <option value="present">Hadir</option>
                <option value="late">Terlambat</option>
                <option value="sick">Sakit</option>
                <option value="leave">Izin</option>
                <option value="absent">Alpa</option>
              </KSelect>
            </template>
          </KTable>

          <Pagination :meta="attendances" />
        </div>
      </template>

      <!-- BR-FIX-03: DELEGASI AKSES (temporary/restricted capability grants) -->
      <template v-if="canManageDelegations" #delegasi>
        <div class="space-y-6">
          <Skeleton v-if="!delegations" type="table" :count="5" />
          <KTable
            v-else
            :columns="delegationColumns"
            :rows="delegations ?? []"
            :emptyTitle="'Belum ada delegasi akses'"
            :emptyDescription="'Delegasi akses memungkinkan karyawan menangani tugas tertentu tanpa mengganti role-nya.'"
            :emptyActionLabel="'+ Beri Delegasi'"
            @empty-action="openDelegationModal()"
          >
            <template #cell-user_name="{ row }">
              <div>
                <span class="font-medium text-sm">{{ row.user_name }}</span>
                <Badge class="ml-1.5" :variant="roleVariant(row.user_role)">{{ row.user_role }}</Badge>
              </div>
            </template>
            <template #cell-permission="{ row }">
              <Badge variant="indigo">{{ permissionLabel(row.permission) }}</Badge>
            </template>
            <template #cell-branch_name="{ row }">
              <Badge variant="blue">{{ row.branch_name }}</Badge>
            </template>
            <template #cell-active="{ row }">
              <Badge :variant="row.active ? 'green' : 'red'">{{ row.active ? 'Aktif' : row.revoked_at ? 'Dicabut' : 'Kadaluarsa' }}</Badge>
            </template>
            <template #cell-expires_at="{ row }">
              <span class="text-xs sk-text-secondary">{{ row.expires_at ? formatDate(row.expires_at) : 'Tanpa batas' }}</span>
            </template>
            <template #cell-action="{ row }">
              <div class="flex items-center gap-1 justify-end">
                <KButton v-if="row.active" @click="revokeDelegation(row)" class="text-xs px-2 py-1 rounded border font-medium transition-colors"
                  style="borderColor: '#fca5a5'; color: '#ef4444';">Cabut</KButton>
                <span v-else class="text-[11px] sk-text-muted">—</span>
              </div>
            </template>
          </KTable>
        </div>
      </template>
    </TabPage>

    <!-- BR-FIX-03: DRAWER BERI DELEGASI AKSES -->
    <Drawer :open="showDelegationDrawer" title="Beri Delegasi Akses (Sementara)" @close="showDelegationDrawer = false" width="460px">
      <form @submit.prevent="submitDelegation" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Karyawan (penerima) *</label>
          <KSelect v-model="delegationForm.user_id" class="input text-sm" required>
            <option value="" disabled>Pilih karyawan</option>
            <option v-for="u in users?.data ?? []" :key="u.id" :value="u.id">
              {{ u.name }} — {{ u.role }} ({{ u.branch?.name ?? 'Global' }})
            </option>
          </KSelect>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Capability yang didelegasikan *</label>
          <KSelect v-model="delegationForm.permission" class="input text-sm" required>
            <option value="" disabled>Pilih capability</option>
            <option v-for="(label, key) in delegationCapabilities" :key="key" :value="key">{{ label }}</option>
          </KSelect>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Cabang (kosongkan = semua cabang yang terjangkau)</label>
          <KSelect v-model="delegationForm.branch_id" class="input text-sm">
            <option value="">Semua cabang</option>
            <option v-for="b in branchesForSelect" :key="b.id" :value="b.id">{{ b.name }}</option>
          </KSelect>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Mulai (opsional)</label>
            <KInput type="datetime-local" v-model="delegationForm.starts_at" class="input text-sm" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Berakhir (opsional)</label>
            <KInput type="datetime-local" v-model="delegationForm.expires_at" class="input text-sm" />
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Alasan (opsional)</label>
          <KInput type="textarea" v-model="delegationForm.reason" class="input text-sm" placeholder="Mis: CS cuti, bantu kasir hari ini" />
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <KButton type="button" @click="showDelegationDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton type="submit" class="btn-primary text-xs" :disabled="delegationForm.processing">Beri Delegasi</KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER TAMBAH / EDIT PENGGUNA -->
    <Drawer :open="showUserDrawer" :title="editingUser ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" @close="showUserDrawer = false" width="420px">
      <form @submit.prevent="submitUser" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Nama *</label>
          <KInput  v-model="userForm.name" required class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Email *</label>
          <KInput  v-model="userForm.email" type="email" required class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Password {{ editingUser ? '(Opsional)' : '*' }}</label>
          <KInput  v-model="userForm.password" type="password" :required="!editingUser" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Role *</label>
          <KSelect  v-model="userForm.role" required class="input text-sm">
            <option value="" disabled>Pilih Role</option>
            <option v-for="r in roleOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
          </KSelect>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Cabang Utama (Home)</label>
          <KSelect  v-model="userForm.branch_id" class="input text-sm">
            <option value="">Pilih Cabang</option>
            <option v-for="b in branchesForSelect" :key="b.id" :value="b.id">{{ b.name }}</option>
          </KSelect>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Akses Cabang Tambahan</label>
          <div class="space-y-1.5 border rounded-xl p-2.5" style="borderColor: var(--border-light); background: var(--bg-hover);">
            <label v-for="b in branchesForSelect" :key="b.id" class="flex items-center gap-2 cursor-pointer">
              <KCheckbox
                :value="b.id"
                :checked="userForm.additional_branches.includes(b.id)"
                @change="toggleAdditionalBranch(b.id)"
                class="w-4 h-4 rounded"
              />
              <span class="text-xs sk-text-primary">{{ b.name }}</span>
            </label>
            <p v-if="!branchesForSelect.length" class="text-[11px] sk-text-muted">Tidak ada cabang lain.</p>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showUserDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="userForm.processing" class="btn-primary text-xs">
            {{ userForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER TAMBAH / EDIT CABANG -->
    <Drawer :open="showBranchDrawer" :title="editingBranch ? 'Edit Cabang Toko' : 'Tambah Cabang Toko'" @close="showBranchDrawer = false" width="420px">
      <form @submit.prevent="submitBranch" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Nama Cabang *</label>
          <KInput  v-model="branchForm.name" required placeholder="e.g. Cabang Bandung Center" class="input text-sm" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Alamat Lengkap</label>
          <KTextarea  v-model="branchForm.address" rows="2" placeholder="Jl. Merdeka No. 45..." class="input text-sm"></KTextarea>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">No. Telepon Cabang</label>
          <KInput  v-model="branchForm.phone" placeholder="022-xxxxxxx" class="input text-sm" />
        </div>
        <div class="flex items-center gap-2 pt-2">
          <KCheckbox  v-model="branchForm.is_active" id="branch_active" class="rounded" />
          <label for="branch_active" class="text-xs font-medium">Aktifkan Cabang Ini</label>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showBranchDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="branchForm.processing" class="btn-primary text-xs">
            {{ branchForm.processing ? 'Menyimpan...' : 'Simpan Cabang' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER TAMBAH / EDIT SHIFT KERJA -->
    <Drawer :open="showShiftDrawer" :title="editingShift ? 'Edit Shift Kerja' : 'Tambah Shift Kerja'" @close="showShiftDrawer = false" width="420px">
      <form @submit.prevent="submitShift" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Nama Shift *</label>
          <KInput  v-model="shiftForm.name" required placeholder="e.g. Shift Pagi / Shift Malam" class="input text-sm" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Jam Mulai *</label>
            <KInput  v-model="shiftForm.start_time" type="time" required class="input text-sm" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-semibold sk-text-muted">Jam Selesai *</label>
            <KInput  v-model="shiftForm.end_time" type="time" required class="input text-sm" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showShiftDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="shiftForm.processing" class="btn-primary text-xs">
            {{ shiftForm.processing ? 'Menyimpan...' : 'Simpan Shift' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER CATAT ABSENSI MANUAL -->
    <Drawer :open="showAttendanceDrawer" title="Catat Absensi Karyawan Manual" @close="showAttendanceDrawer = false" width="420px">
      <form @submit.prevent="submitAttendance" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Pilih Karyawan *</label>
          <KSelect  v-model="attendanceForm.user_id" required class="input text-sm">
            <option value="" disabled>-- Pilih Karyawan --</option>
            <option v-for="u in attendanceUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.role }})</option>
          </KSelect>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Status Kehadiran *</label>
          <KSelect  v-model="attendanceForm.status" required class="input text-sm">
            <option value="present">Hadir</option>
            <option value="late">Terlambat</option>
            <option value="sick">Sakit</option>
            <option value="leave">Izin</option>
            <option value="absent">Alpa</option>
          </KSelect>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold sk-text-muted">Waktu Masuk *</label>
          <KInput  v-model="attendanceForm.check_in" type="datetime-local" required class="input text-sm" />
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <KButton  type="button" @click="showAttendanceDrawer = false" class="btn-secondary text-xs">Batal</KButton>
          <KButton  type="submit" :disabled="attendanceForm.processing" class="btn-primary text-xs">
            {{ attendanceForm.processing ? 'Menyimpan...' : 'Simpan Absensi' }}
          </KButton>
        </div>
      </form>
    </Drawer>

    <!-- DRAWER AKSES MENU PENGGUNA -->
    <Drawer :open="showMenuAccessDrawer" :title="'Akses Menu — ' + (selectedMenuUser?.name || 'User')" @close="showMenuAccessDrawer = false" width="460px">
      <div class="space-y-4">
        <p class="text-xs text-muted">Pilih menu yang dapat diakses oleh karyawan ini.</p>
        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
          <div v-for="menu in availableMenus" :key="menu.id" class="flex items-center justify-between p-2.5 rounded-xl border" style="borderColor: var(--border-color); background: var(--bg-hover);">
            <div>
              <p class="text-xs font-bold sk-text-primary">{{ menu.label }}</p>
              <p class="text-[10px] sk-text-muted">{{ menu.group }}</p>
            </div>
            <KCheckbox  :value="menu.id" v-model="selectedMenuIds" class="w-4 h-4 rounded accent-purple-600 cursor-pointer" />
          </div>
        </div>
        <div class="flex justify-between items-center pt-3 border-t sk-border">
          <KButton  type="button" @click="resetMenuAccess" class="text-xs sk-text-danger font-semibold hover:underline cursor-pointer">Reset Default Role</KButton>
          <div class="flex gap-2">
            <KButton  type="button" @click="showMenuAccessDrawer = false" class="btn-secondary text-xs">Batal</KButton>
            <KButton  type="button" @click="saveMenuAccess" class="btn-primary text-xs">Simpan Akses</KButton>
          </div>
        </div>
      </div>
    </Drawer>
  </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

import { computed, ref } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TabPage from '@/Components/TabPage.vue';
import KCard from '@/Components/KCard.vue';
import KTable from '@/Components/KTable.vue';
import Pagination from '@/Components/Pagination.vue';
import Badge from '@/Components/Badge.vue';
import Skeleton from '@/Components/Skeleton.vue';
import Drawer from '@/Components/Drawer.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const { formatNumber, formatCurrency, formatDate, currentDate } = useFormatter();

const props = defineProps({
  activeTab: { type: String, default: 'pengguna' },
  users: { type: Object, default: null },
  systemBranches: { type: [Object, Array], default: null },
  branches: { type: Object, default: null },
  shifts: { type: [Object, Array], default: null },
  attendances: { type: [Object, Array], default: null },
  attendanceUsers: { type: Array, default: () => [] },
  attendanceShifts: { type: Array, default: () => [] },
  // BR-FIX-03: delegation management
  canManageDelegations: { type: Boolean, default: false },
  delegations: { type: Array, default: () => [] },
});

const activeTab = ref(props.activeTab);

// User Drawer
const showUserDrawer = ref(false);
const editingUser = ref(null);
const userForm = useForm({ name: '', email: '', password: '', role: '', branch_id: '', additional_branches: [] });
// PILOT-UAT-02 STEP 9: official tenant roles listed first; head_store/courier
// are legacy/compatibility values (kept for history, NOT newly-approved).
const roleOptions = [
  { value: 'owner', label: 'Owner (Pemilik)' },
  { value: 'admin', label: 'Admin' },
  { value: 'manager', label: 'Manager' },
  { value: 'cs', label: 'CS (Customer Service)' },
  { value: 'technician', label: 'Teknisi' },
  { value: 'cashier', label: 'Kasir' },
  { value: 'head_store', label: 'Head Store (legacy)' },
  { value: 'courier', label: 'Kurir (legacy)' },
];
const roles = roleOptions.map(r => r.value);

// BR-FIX-02: toggle an additional branch in the multi-branch access list.
const toggleAdditionalBranch = (id) => {
  const idx = userForm.additional_branches.indexOf(id);
  if (idx >= 0) {
    userForm.additional_branches.splice(idx, 1);
  } else {
    userForm.additional_branches.push(id);
  }
};

const openUserModal = (row = null) => {
  editingUser.value = row;
  if (row) {
    userForm.name = row.name;
    userForm.email = row.email;
    userForm.role = row.role;
    userForm.branch_id = row.branch_id || '';
    userForm.password = '';
    userForm.additional_branches = (row.branches ?? []).map(b => b.id);
  } else {
    userForm.reset();
  }
  showUserDrawer.value = true;
};

const submitUser = () => {
  const url = editingUser.value ? route('users.update', editingUser.value.id) : route('users.store');
  const method = editingUser.value ? 'put' : 'post';
  userForm[method](url, { preserveScroll: true, onSuccess: () => { showUserDrawer.value = false; userForm.reset(); editingUser.value = null; } });
};

const toggleUser = (row) => {
  router.put(route('users.update', row.id), { active: !row.active }, { preserveState: true, preserveScroll: true });
};

// Branch Drawer
const showBranchDrawer = ref(false);
const editingBranch = ref(null);
const branchForm = useForm({ name: '', address: '', phone: '', is_active: true });

const openBranchModal = (row = null) => {
  editingBranch.value = row;
  if (row) {
    branchForm.name = row.name;
    branchForm.address = row.address || '';
    branchForm.phone = row.phone || '';
    branchForm.is_active = Boolean(row.is_active);
  } else {
    branchForm.reset();
  }
  showBranchDrawer.value = true;
};

const submitBranch = () => {
  const url = editingBranch.value ? route('branches.update', editingBranch.value.id) : route('branches.store');
  const method = editingBranch.value ? 'put' : 'post';
  branchForm[method](url, { preserveScroll: true, onSuccess: () => { showBranchDrawer.value = false; } });
};

// Shift Drawer
const showShiftDrawer = ref(false);
const editingShift = ref(null);
const shiftForm = useForm({ name: '', start_time: '08:00', end_time: '17:00' });

const openShiftModal = (row = null) => {
  editingShift.value = row;
  if (row) {
    shiftForm.name = row.name;
    shiftForm.start_time = row.start_time || '08:00';
    shiftForm.end_time = row.end_time || '17:00';
  } else {
    shiftForm.reset();
  }
  showShiftDrawer.value = true;
};

const submitShift = () => {
  const url = editingShift.value ? route('shifts.update', editingShift.value.id) : route('shifts.store');
  const method = editingShift.value ? 'put' : 'post';
  shiftForm[method](url, { preserveScroll: true, onSuccess: () => { showShiftDrawer.value = false; } });
};

const deleteShift = (row) => {
  if (confirm(`Hapus shift "${row.name}"?`)) {
    router.delete(route('shifts.destroy', row.id), { preserveScroll: true });
  }
};

// Attendance Drawer
const showAttendanceDrawer = ref(false);
const attendanceForm = useForm({ user_id: '', status: 'present', check_in: new Date().toISOString().slice(0, 16) });

const openAttendanceModal = () => {
  attendanceForm.reset();
  attendanceForm.check_in = new Date().toISOString().slice(0, 16);
  showAttendanceDrawer.value = true;
};

const submitAttendance = () => {
  attendanceForm.post(route('attendances.clock-in'), { preserveScroll: true, onSuccess: () => { showAttendanceDrawer.value = false; } });
};

const updateAttendanceStatus = (row, status) => {
  router.post(route('attendances.status', row.id), { status }, { preserveScroll: true });
};

const branchesForSelect = computed(() => props.systemBranches?.data ?? props.systemBranches ?? []);

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const tabs = computed(() => {
  const base = [
    { key: 'pengguna', label: 'Pengguna' },
    { key: 'cabang', label: 'Cabang' },
    { key: 'shift', label: 'Shift' },
    { key: 'absensi', label: 'Absensi' },
  ];
  if (props.canManageDelegations) {
    base.push({ key: 'delegasi', label: 'Delegasi Akses' });
  }
  return base;
});

const tabLabels = { pengguna: 'Pengguna', cabang: 'Cabang', shift: 'Shift', absensi: 'Absensi', delegasi: 'Delegasi Akses' };
const pageTitle = computed(() => 'Sistem — ' + (tabLabels[activeTab.value] || 'Pengguna'));
const subtitle = computed(() => currentDate.value);

const userColumns = [
  { key: 'name', label: 'Nama' },
  { key: 'role', label: 'Role' },
  { key: 'branch_name', label: 'Cabang / Akses' },
  { key: 'active', label: 'Aktif' },
  { key: 'action', label: '', align: 'right' },
];

const branchColumns = [
  { key: 'name', label: 'Nama Cabang' },
  { key: 'users_count', label: 'User' },
  { key: 'services_count', label: 'Servis' },
  { key: 'products_count', label: 'Produk' },
  { key: 'is_active', label: 'Aktif' },
  { key: 'action', label: '', align: 'right' },
];

const shiftColumns = [
  { key: 'name', label: 'Nama Shift' },
  { key: 'start_time', label: 'Mulai' },
  { key: 'end_time', label: 'Selesai' },
  { key: 'action', label: '', align: 'right' },
];

const attendanceColumns = [
  { key: 'user_name', label: 'Karyawan' },
  { key: 'check_in', label: 'Masuk' },
  { key: 'check_out', label: 'Keluar' },
  { key: 'status', label: 'Status' },
  { key: 'action', label: '', align: 'right' },
];

const roleVariant = (role) => {
  const map = { owner: 'purple', admin: 'blue', manager: 'green', head_store: 'orange', cs: 'cyan', technician: 'yellow', cashier: 'pink', courier: 'default' };
  return map[role] || 'default';
};

const showMenuAccessDrawer = ref(false);
const selectedMenuUser = ref(null);
const selectedMenuIds = ref([]);

const availableMenus = [
  { id: 'dashboard', label: 'Dashboard', group: 'Utama' },
  { id: 'services', label: 'Servis', group: 'Utama' },
  { id: 'customers', label: 'Pelanggan', group: 'Utama' },
  { id: 'keuangan', label: 'Keuangan', group: 'Transaksi' },
  { id: 'kas', label: 'Kas', group: 'Transaksi' },
  { id: 'inventaris', label: 'Inventaris', group: 'Manajemen' },
  { id: 'servis_tools', label: 'Servis Tools', group: 'Manajemen' },
  { id: 'laporan', label: 'Laporan', group: 'Manajemen' },
  { id: 'sistem', label: 'Sistem', group: 'Manajemen' },
  { id: 'dokumen', label: 'Dokumen', group: 'Manajemen' },
  { id: 'pengaturan', label: 'Pengaturan', group: 'Manajemen' },
  { id: 'monitoring', label: 'Monitoring', group: 'Manajemen' },
  { id: 'qr_scanner', label: 'QR Scanner', group: 'Manajemen' },
];

const openMenuAccessModal = async (row) => {
  selectedMenuUser.value = row;
  try {
    const response = await fetch(route('users.menu-access', row.id), {
      headers: { 'Accept': 'application/json' }
    });
    const data = await response.json();
    selectedMenuIds.value = data.customMenus || data.defaultMenus || availableMenus.map(m => m.id);
  } catch (e) {
    selectedMenuIds.value = availableMenus.map(m => m.id);
  }
  showMenuAccessDrawer.value = true;
};

const saveMenuAccess = () => {
  if (!selectedMenuUser.value) return;
  router.post(route('users.menu-access.update', selectedMenuUser.value.id), {
    menu_access: selectedMenuIds.value,
  }, {
    preserveScroll: true,
    onSuccess: () => { showMenuAccessDrawer.value = false; }
  });
};

const resetMenuAccess = () => {
  if (!selectedMenuUser.value) return;
  router.post(route('users.menu-access.update', selectedMenuUser.value.id), {
    reset_to_default: true,
  }, {
    preserveScroll: true,
    onSuccess: () => { showMenuAccessDrawer.value = false; }
  });
};

// BR-FIX-03: Delegation management (grant/revoke granular capabilities)
const showDelegationDrawer = ref(false);
const delegationForm = useForm({
  user_id: '',
  permission: '',
  branch_id: '',
  starts_at: '',
  expires_at: '',
  reason: '',
});

const delegationCapabilities = {
  'service.create': 'Buat Service / CS Intake',
  'service.pickup': 'Proses Pickup Service',
  'sales.create': 'Catat Pembayaran / Kasir',
  'finance.view': 'Lihat Laporan Keuangan',
  'report.view': 'Lihat Laporan',
};

const delegationColumns = [
  { key: 'user_name', label: 'Karyawan' },
  { key: 'permission', label: 'Capability' },
  { key: 'branch_name', label: 'Cabang' },
  { key: 'expires_at', label: 'Berakhir' },
  { key: 'active', label: 'Status' },
  { key: 'action', label: '', align: 'right' },
];

const permissionLabel = (key) => delegationCapabilities[key] || key;

const openDelegationModal = () => {
  delegationForm.reset();
  showDelegationDrawer.value = true;
};

const submitDelegation = () => {
  delegationForm.post(route('delegations.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showDelegationDrawer.value = false;
      delegationForm.reset();
    },
  });
};

const revokeDelegation = (row) => {
  if (!window.confirm('Cabut delegasi akses ini? Efeknya langsung berlaku.')) return;
  router.post(route('delegations.revoke', row.id), {}, {
    preserveScroll: true,
  });
};

const switchTab = (key) => {
  router.get(route('sistem.index'), { tab: key }, { preserveState: true, preserveScroll: true });
};
</script>

