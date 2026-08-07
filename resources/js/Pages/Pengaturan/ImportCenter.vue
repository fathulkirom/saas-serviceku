<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Pusat Import Data</h2>
                    <p class="text-sm text-zinc-500 mt-1">Import pelanggan, produk, atau device dari file.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('pengaturan.index')" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Pengaturan
                    </Link>
                </div>
            </div>

            <!-- Import Card -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6">
                <h3 class="font-bold text-zinc-900 mb-4">Import Baru</h3>
                <div class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Tipe Data</label>
                        <KSelect  v-model="entity" class="w-full rounded-xl border border-zinc-300 text-sm px-4 py-2.5 bg-white">
                            <option value="customer">Pelanggan</option>
                            <option value="product">Produk</option>
                            <option value="device">Device</option>
                        </KSelect>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Paste Data (CSV/TSV)</label>
                        <KTextarea  v-model="rawData" rows="4" placeholder="nama<TAB>no_hp<TAB>alamat&#10;..." class="w-full rounded-xl border border-zinc-300 text-sm p-3 bg-white" />
                    </div>
                    <KButton  @click="handleImport" :disabled="importing" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                        {{ importing ? 'Importing...' : 'Import' }}
                    </KButton>
                </div>
                <p v-if="result" class="mt-4 text-sm font-semibold" :class="result.ok ? 'text-emerald-600' : 'text-red-600'">{{ result.message }}</p>
            </div>

            <!-- Logs -->
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm">
                <div class="px-6 py-4 border-b border-zinc-100">
                    <h3 class="font-bold text-zinc-900">Riwayat Import</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Entity</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500">Sukses</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500">Gagal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <tr v-for="log in logs ?? []" :key="log.id" class="hover:bg-zinc-50">
                                <td class="px-6 py-3 text-sm font-semibold text-zinc-900 capitalize">{{ log.entity }}</td>
                                <td class="px-6 py-3 text-sm text-emerald-600 text-right font-bold">{{ log.success_count ?? 0 }}</td>
                                <td class="px-6 py-3 text-sm text-red-600 text-right font-bold">{{ log.failed_count ?? 0 }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="log.status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                                        {{ log.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-zinc-500">{{ formatDate(log.created_at) }}</td>
                            </tr>
                            <tr v-if="!logs?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-zinc-400">Belum ada riwayat import.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KButton from '@/Components/KButton.vue';
import KSelect from '@/Components/KSelect.vue';
import KTextarea from '@/Components/KTextarea.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    logs: { type: Array, default: () => [] },
});

const entity = ref('customer');
const rawData = ref('');
const importing = ref(false);
const result = ref(null);

const formatDate = (d) => d ? new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '—';

const handleImport = () => {
    if (!rawData.value.trim()) return;
    importing.value = true;
    result.value = null;

    const rows = rawData.value
        .trim().split('\n')
        .map(line => line.split('\t').map(c => c.trim()))
        .filter(r => r.some(c => c));

    router.post(route('import.process'), { entity: entity.value, rows }, {
        preserveScroll: true,
        onSuccess: (page) => {
            result.value = { ok: true, message: page.props.flash?.success || 'Import berhasil.' };
            rawData.value = '';
        },
        onError: (errors) => {
            result.value = { ok: false, message: Object.values(errors).join(', ') };
        },
        onFinish: () => { importing.value = false; },
    });
};
</script>
