<template>
    <AdminLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-slate-100">💾 Backup & Restore</h2>
                <p class="text-sm text-slate-400 mt-0.5">Backup database dan upload ke Google Drive</p>
            </div>
        </template>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 rounded-xl border flex items-center gap-3" style="background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.2);">
            <p class="text-sm text-emerald-300">{{ $page.props.flash.success }}</p>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-4 p-4 rounded-xl border flex items-center gap-3" style="background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.2);">
            <p class="text-sm text-red-300">{{ $page.props.flash.error }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Settings -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Setting Form -->
                <div class="rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengaturan Backup</h3>
                    <div class="flex items-center gap-2 mb-4 p-3 bg-blue-50 rounded-lg">
                        <span class="text-lg">💡</span>
                        <p class="text-sm text-blue-700">Backup disimpan ke HDD lokal dulu, lalu bisa diupload ke Google Drive untuk keamanan ekstra (off-site).</p>
                    </div>
                    <form @submit.prevent="updateSettings">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-300">Lokasi Backup (HDD)</label>
                                <KInput  v-model="form.backup_path" type="text"
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    placeholder="/mnt/hdd/Backup/ServiceKU" />
                                <p class="mt-1 text-xs text-slate-400">Path ke direktori HDD untuk menyimpan backup</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Simpan Backup (hari)</label>
                                <KInput  v-model.number="form.backup_retention_days" name="backup_retention_days" type="number" min="1" max="365"
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Jam Backup Otomatis</label>
                                <KInput  v-model="form.backup_auto_time" type="time"
                                    class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            </div>
                            <div class="col-span-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <KCheckbox  v-model="autoEnabled" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    <span class="ml-3 text-sm font-medium text-slate-300">Aktifkan Backup Otomatis (via cron)</span>
                                </label>
                                <p class="mt-1 text-xs text-slate-400">Pastikan cron job sudah diset: <code class="bg-slate-800 px-1 rounded">* * * * * cd {{ basePath }} && php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code></p>
                            </div>
                        </div>

                        <!-- Google Drive Settings -->
                        <div class="mt-6 pt-4 border-t">
                            <h4 class="text-base font-semibold text-gray-900 mb-3">☁️ Google Drive Backup</h4>
                            <p class="text-xs text-slate-400 mb-3">Backup otomatis diupload ke Google Drive sebagai cadangan off-site. <a href="https://rclone.org/drive/" target="_blank" class="text-indigo-600 hover:underline">Cara setup rclone</a></p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <KCheckbox  v-model="gdriveEnabled" class="sr-only peer" />
                                        <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        <span class="ml-3 text-sm font-medium text-slate-300">Upload ke Google Drive</span>
                                    </label>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-300">Folder ID Google Drive</label>
                                    <KInput  v-model="form.gdrive_folder_id" type="text" placeholder="xxx_FOLDER_ID_xxx"
                                        class="mt-1 block w-full rounded-md border-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                                    <p class="mt-1 text-xs text-slate-400">Dapatkan dari URL folder Google Drive: <code class="bg-slate-800 px-1 rounded">https://drive.google.com/drive/folders/xxx_FOLDER_ID_xxx</code></p>
                                </div>
                                <div class="col-span-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <KCheckbox  v-model="gdriveDeleteLocal" class="sr-only peer" />
                                        <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        <span class="ml-3 text-sm font-medium text-slate-300">Hapus file lokal setelah upload (hemat SSD)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end mt-6 pt-4 border-t">
                            <KButton  type="submit" :disabled="form.processing"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm">
                                Simpan Pengaturan
                            </KButton>
                        </div>
                    </form>
                </div>

                <!-- Daftar File Backup -->
                <div class="rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">📂 File Backup</h3>
                        <KButton  @click="runBackup" :disabled="form.processing"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 text-sm">
                            🔄 Backup Sekarang
                        </KButton>
                    </div>

                    <div v-if="backupFiles.length === 0" class="text-center py-8 text-slate-400">
                        <p class="text-4xl mb-2">📭</p>
                        <p class="text-sm">Belum ada file backup</p>
                        <p class="text-xs mt-1">Klik "Backup Sekarang" untuk memulai</p>
                    </div>

                    <div v-else class="space-y-2">
                        <div v-for="file in backupFiles" :key="file.path"
                            class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg hover:bg-slate-800">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-lg flex-shrink-0">
                                    {{ file.type === 'database' ? '🗄️' : file.type === 'storage' ? '📦' : '🔐' }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ file.name }}</p>
                                    <p class="text-xs text-slate-400">{{ file.date }} • {{ file.size }}</p>
                                </div>
                            </div>
                            <KButton  @click="deleteBackup(file.path)"
                                class="flex-shrink-0 px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 rounded">
                                Hapus
                            </KButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Info -->
            <div class="space-y-6">
                <!-- Status -->
                <div class="rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Status</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Backup Terakhir:</span>
                            <span class="font-medium">{{ config.backup_last_run || '-' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Status:</span>
                            <span class="font-medium" :class="config.backup_last_status === 'success' ? 'text-green-600' : 'text-red-600'">
                                {{ config.backup_last_status === 'success' ? '✅ Sukses' : '❌ ' + config.backup_last_status }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Auto Backup:</span>
                            <span class="font-medium">{{ config.backup_auto_enabled === 'true' ? '✅ Aktif' : '❌ Nonaktif' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Total File:</span>
                            <span class="font-medium">{{ backupFiles.length }} file</span>
                        </div>
                    </div>
                </div>

                <!-- Disk Info -->
                <div class="rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">💽 Info Disk (HDD)</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Total:</span>
                            <span class="font-medium">{{ diskInfo.total }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Terpakai:</span>
                            <span class="font-medium">{{ diskInfo.used }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Sisa:</span>
                            <span class="font-medium text-green-600">{{ diskInfo.free }}</span>
                        </div>
                        <div class="mt-2">
                            <div class="w-full bg-slate-700 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: diskInfo.percent + '%' }"></div>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">{{ diskInfo.percent }}% terpakai</p>
                        </div>
                    </div>
                </div>

                <!-- Google Drive Status -->
                <div class="rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">☁️ Google Drive</h3>
                    <div v-if="gdriveInfo" class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">rclone:</span>
                            <span class="font-medium" :class="gdriveInfo.rclone_installed ? 'text-green-600' : 'text-red-600'">
                                {{ gdriveInfo.rclone_installed ? '✅ ' + (gdriveInfo.rclone_version || '') : '❌ Tidak terinstall' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Remote:</span>
                            <span class="font-medium" :class="gdriveInfo.remote_configured ? 'text-green-600' : 'text-yellow-600'">
                                {{ gdriveInfo.remote_configured ? '✅ Terkonfigurasi' : '❌ Belum' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Upload otomatis:</span>
                            <span class="font-medium">{{ config.gdrive_enabled === 'true' ? '✅ Aktif' : '❌ Nonaktif' }}</span>
                        </div>
                        <div v-if="gdriveFiles.length > 0" class="flex justify-between text-sm">
                            <span class="text-slate-400">File di Drive:</span>
                            <span class="font-medium">{{ gdriveFiles.length }} file</span>
                        </div>
                        <div v-if="gdriveInfo.error" class="bg-yellow-50 border border-yellow-200 rounded-md p-2 mt-2">
                            <p class="text-xs text-yellow-700">{{ gdriveInfo.error }}</p>
                        </div>
                        <div v-if="gdriveInfo.remote_configured && !gdriveInfo.error" class="mt-3">
                            <KButton  @click="uploadToDrive" :disabled="form.processing"
                                class="w-full px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 text-sm">
                                ☁️ Upload Backup ke Drive Sekarang
                            </KButton>
                        </div>
                    </div>
                </div>

                <!-- Setup Guide -->
                <div class="rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">📋 Setup Google Drive</h3>
                    <ol class="text-sm text-slate-400 space-y-1.5 list-decimal list-inside">
                        <li>Install rclone: <code class="bg-slate-800 px-1 rounded text-xs">brew install rclone</code></li>
                        <li>Konfigurasi: <code class="bg-slate-800 px-1 rounded text-xs">rclone config</code></li>
                        <li>Buat remote baru: <strong>serviceku-backup</strong></li>
                        <li>Pilih jenis: <strong>drive</strong> (Google Drive)</li>
                        <li>Buat folder di Google Drive</li>
                        <li>Copy Folder ID ke pengaturan di atas</li>
                    </ol>
                    <p class="text-xs text-slate-400 mt-2">Script setup: <code class="bg-slate-800 px-1 rounded">docker/cloudflare/setup-gdrive.sh</code></p>
                </div>

                <!-- Panduan Cron -->
                <div class="rounded-2xl p-6 border bg-slate-900/50 border-slate-800 backdrop-blur-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">🤖 Cron Job</h3>
                    <p class="text-sm text-slate-400 mb-3">Untuk backup otomatis, tambahkan ke crontab:</p>
                    <div class="bg-slate-900 text-green-400 rounded-md p-3 text-xs font-mono">
                        # Setiap jam 03:00 pagi<br>
                        <span class="text-yellow-300">0 3</span> * * * cd {{ basePath }} && php artisan backup:run --force
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Atau biarkan Laravel Scheduler menanganinya:</p>
                    <div class="bg-slate-900 text-green-400 rounded-md p-3 text-xs font-mono mt-2">
                        * * * * * cd {{ basePath }} && php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';
import KCheckbox from '@/Components/KCheckbox.vue';

import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    config: { type: Object, default: () => ({}) },
    backupFiles: { type: Array, default: () => [] },
    diskInfo: { type: Object, default: () => ({ total: '-', used: '-', free: '-', percent: 0 }) },
    gdriveInfo: { type: Object, default: null },
    gdriveFiles: { type: Array, default: () => [] },
    gdriveStorage: { type: String, default: null },
});

const basePath = window.location.origin;

const autoEnabled = ref(props.config.backup_auto_enabled === 'true');
const gdriveEnabled = ref(props.config.gdrive_enabled === 'true');
const gdriveDeleteLocal = ref(props.config.gdrive_delete_local === 'true');

const form = useForm({
    backup_path: props.config.backup_path || '/mnt/hdd/Backup/ServiceKU',
    backup_retention_days: props.config.backup_retention_days || 30,
    backup_auto_enabled: props.config.backup_auto_enabled || 'false',
    backup_auto_time: props.config.backup_auto_time || '03:00',
    gdrive_enabled: props.config.gdrive_enabled || 'false',
    gdrive_folder_id: props.config.gdrive_folder_id || '',
    gdrive_delete_local: props.config.gdrive_delete_local || 'false',
});

const updateSettings = () => {
    form.backup_auto_enabled = autoEnabled.value ? 'true' : 'false';
    form.gdrive_enabled = gdriveEnabled.value ? 'true' : 'false';
    form.gdrive_delete_local = gdriveDeleteLocal.value ? 'true' : 'false';
    form.post(route('admin.backup.settings'));
};

const runBackup = () => {
    if (confirm('Jalankan backup sekarang?')) {
        form.post(route('admin.backup.run'));
    }
};

const uploadToDrive = () => {
    if (confirm('Upload file backup ke Google Drive?')) {
        form.post(route('admin.backup.upload-drive'));
    }
};

const deleteBackup = (path) => {
    if (confirm('Hapus file backup ini?')) {
        router.post(route('admin.backup.delete'), { path });
    }
};
</script>
