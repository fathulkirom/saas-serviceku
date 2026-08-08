<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sk-text-primary">Pusat Import Data</h2>
                    <p class="text-sm sk-text-muted mt-1">Import pelanggan, produk, atau device dari file.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('pengaturan.index')" class="inline-flex items-center gap-2 px-4 py-2 sk-bg-card hover:sk-bg-hover border sk-border sk-text-primary text-sm font-bold rounded-xl transition-colors shadow-sm">
                        ← Pengaturan
                    </Link>
                </div>
            </div>

            <!-- Import Card -->
            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm p-6">
                <h3 class="font-bold sk-text-primary mb-4">Import Baru</h3>
                <div class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-bold sk-text-muted uppercase tracking-wider mb-2">Tipe Data</label>
                        <KSelect  v-model="entity" class="w-full rounded-xl border sk-border text-sm px-4 py-2.5 sk-bg-card">
                            <option value="customer">Pelanggan</option>
                            <option value="product">Produk</option>
                            <option value="device">Device</option>
                        </KSelect>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold sk-text-muted uppercase tracking-wider mb-2">Paste Data (CSV/TSV)</label>
                        <KTextarea  v-model="rawData" rows="4" placeholder="nama<TAB>no_hp<TAB>alamat&#10;..." class="w-full rounded-xl border sk-border text-sm p-3 sk-bg-card" />
                    </div>
                    <KButton  @click="handleImport" :disabled="importing" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white sk-bg-primary hover:sk-bg-primary disabled:opacity-50">
                        {{ importing ? 'Importing...' : 'Import' }}
                    </KButton>
                </div>
                <p v-if="result" class="mt-4 text-sm font-semibold" :class="result.ok ? 'sk-text-success' : 'sk-text-danger'">{{ result.message }}</p>
            </div>

            <!-- Logs -->
            <div class="sk-bg-card rounded-2xl border sk-border shadow-sm">
                <div class="px-6 py-4 border-b sk-border-light">
                    <h3 class="font-bold sk-text-primary">Riwayat Import</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b sk-border-light sk-bg-hover">
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Entity</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider sk-text-muted">Sukses</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider sk-text-muted">Gagal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider sk-text-muted">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y sk-border-light">
                            <tr v-for="log in logs ?? []" :key="log.id" class="hover:sk-bg-hover">
                                <td class="px-6 py-3 text-sm font-semibold sk-text-primary capitalize">{{ log.entity }}</td>
                                <td class="px-6 py-3 text-sm sk-text-success text-right font-bold">{{ log.success_count ?? 0 }}</td>
                                <td class="px-6 py-3 text-sm sk-text-danger text-right font-bold">{{ log.failed_count ?? 0 }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold" :class="log.status === 'success' ? 'sk-bg-success-soft sk-text-success' : 'sk-bg-danger-soft sk-text-danger'">
                                        {{ log.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm sk-text-muted">{{ formatDate(log.created_at) }}</td>
                            </tr>
                            <tr v-if="!logs?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm sk-text-muted">Belum ada riwayat import.</td>
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
