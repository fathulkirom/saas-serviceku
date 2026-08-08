<template>
    <AuthenticatedLayout>
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sk-text-primary">Pusat Persetujuan</h2>
                    <p class="text-sm sk-text-muted mt-1">Semua item yang menunggu approval owner/admin.</p>
                </div>
            </div>

            <!-- Quotations -->
            <ApprovalSection title="Quotation Menunggu Persetujuan" :count="quotations?.length" :empty="'Tidak ada quotation menunggu.'">
                <div class="divide-y sk-border-light">
                    <div v-for="q in quotations ?? []" :key="q.id" class="flex items-center justify-between gap-4 px-6 py-3">
                        <div>
                            <p class="text-sm font-semibold sk-text-primary">#{{ q.service_id }} — {{ q.service?.customer?.name }}</p>
                            <p class="text-xs sk-text-muted">{{ q.total_amount ? 'Rp ' + formatNumber(q.total_amount) : '' }}</p>
                        </div>
                        <Link :href="route('services.show', q.service_id)" class="text-xs font-bold px-3 py-1.5 rounded-lg sk-text-primary-brand sk-bg-primary-soft hover:sk-bg-primary-soft">Detail</Link>
                    </div>
                </div>
            </ApprovalSection>

            <!-- Price Changes -->
            <ApprovalSection title="Perubahan Harga" :count="priceChanges?.length" :empty="'Tidak ada perubahan harga.'">
                <div class="divide-y sk-border-light">
                    <div v-for="p in priceChanges ?? []" :key="p.id" class="flex items-center justify-between gap-4 px-6 py-3">
                        <div>
                            <p class="text-sm font-semibold sk-text-primary">#{{ p.service_id }} — {{ p.service?.customer?.name }}</p>
                            <p class="text-xs sk-text-muted">{{ p.requested_by || '—' }}</p>
                        </div>
                        <Link :href="route('services.show', p.service_id)" class="text-xs font-bold px-3 py-1.5 rounded-lg sk-text-primary-brand sk-bg-primary-soft hover:sk-bg-primary-soft">Detail</Link>
                    </div>
                </div>
            </ApprovalSection>

            <!-- Reopens -->
            <ApprovalSection title="Permintaan Reopen" :count="reopens?.length" :empty="'Tidak ada permintaan reopen.'">
                <div class="divide-y sk-border-light">
                    <div v-for="r in reopens ?? []" :key="r.id" class="flex items-center justify-between gap-4 px-6 py-3">
                        <div>
                            <p class="text-sm font-semibold sk-text-primary">#{{ r.service_id }} — {{ r.service?.customer?.name }}</p>
                            <p class="text-xs sk-text-muted">{{ r.reason || '—' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link :href="route('services.show', r.service_id)" class="text-xs font-bold px-3 py-1.5 rounded-lg sk-text-primary-brand sk-bg-primary-soft hover:sk-bg-primary-soft">Detail</Link>
                            <button type="button" :disabled="approving === r.id" @click="approveReopen(r.id)"
                                class="text-xs font-bold px-3 py-1.5 rounded-lg sk-text-success sk-bg-success-soft hover:sk-bg-success-soft disabled:opacity-50">
                                {{ approving === r.id ? 'Memproses...' : 'Setujui' }}
                            </button>
                        </div>
                    </div>
                </div>
            </ApprovalSection>

            <!-- Returns -->
            <ApprovalSection title="Retur Penjualan" :count="returns?.length" :empty="'Tidak ada retur.'">
                <div class="divide-y sk-border-light">
                    <div v-for="r in returns ?? []" :key="r.id" class="flex items-center justify-between gap-4 px-6 py-3">
                        <div>
                            <p class="text-sm font-semibold sk-text-primary">Retur #{{ r.id }} — {{ r.sale?.customer?.name }}</p>
                            <p class="text-xs sk-text-muted">{{ r.reason || '—' }}</p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1.5 rounded-lg sk-text-warning sk-bg-warning-soft">Pending</span>
                    </div>
                </div>
            </ApprovalSection>

            <!-- Stock Adjustments -->
            <ApprovalSection title="Penyesuaian Stok" :count="stockAdjustments?.length" :empty="'Tidak ada penyesuaian.'">
                <div class="divide-y sk-border-light">
                    <div v-for="s in stockAdjustments ?? []" :key="s.id" class="flex items-center justify-between gap-4 px-6 py-3">
                        <div>
                            <p class="text-sm font-semibold sk-text-primary">{{ s.product?.name || '—' }}</p>
                            <p class="text-xs sk-text-muted">{{ s.reason || '—' }}</p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1.5 rounded-lg sk-text-warning sk-bg-warning-soft">Pending</span>
                    </div>
                </div>
            </ApprovalSection>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    quotations: { type: Array, default: () => [] },
    priceChanges: { type: Array, default: () => [] },
    reopens: { type: Array, default: () => [] },
    returns: { type: Array, default: () => [] },
    stockAdjustments: { type: Array, default: () => [] },
});

const approving = ref(null);

function approveReopen(id) {
    approving.value = id;
    router.post(route('service-reopens.approve', id), {}, {
        preserveScroll: true,
        onSuccess: () => { approving.value = null; router.reload({ only: ['reopens'] }); },
        onError: () => { approving.value = null; },
    });
}

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>
