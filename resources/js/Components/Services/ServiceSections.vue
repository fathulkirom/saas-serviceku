<template>
    <div class="space-y-5">
        <!-- PROBLEM + CONDITION -->
        <div class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-3 text-zinc-900">📝 Deskripsi Masalah</h3>
            <p class="text-sm whitespace-pre-wrap text-zinc-600">{{ service.problem_description || 'Tidak ada deskripsi' }}</p>
            <p v-if="service.condition_note" class="text-sm mt-3 pt-3 border-t whitespace-pre-wrap" style="border-color: var(--border-light); color: var(--text-muted);">{{ service.condition_note }}</p>
        </div>

        <!-- CHECKLIST MASUK -->
        <div v-if="checklistMasuk" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-3 text-zinc-900">✅ Checklist Masuk</h3>
            <div class="flex flex-wrap gap-2">
                <span v-for="item in checklistMasuk.checked_items" :key="item"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold"
                    style="background: var(--success-soft); color: var(--success-text);">
                    ✓ {{ getChecklistItemName(item, templatesMasuk, templatesKeluar) }}
                </span>
            </div>
            <p v-if="checklistMasuk.notes" class="text-xs mt-2 text-zinc-500">Catatan: {{ checklistMasuk.notes }}</p>
        </div>

        <!-- CHECKLIST KELUAR -->
        <div v-if="checklistKeluar" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-3 text-zinc-900">📋 Checklist Keluar</h3>
            <div class="flex flex-wrap gap-2">
                <span v-for="item in checklistKeluar.checked_items" :key="item"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold"
                    style="background: var(--success-soft); color: var(--success-text);">
                    ✓ {{ getChecklistItemName(item, templatesMasuk, templatesKeluar) }}
                </span>
            </div>
            <p v-if="checklistKeluar.notes" class="text-xs mt-2 text-zinc-500">Catatan: {{ checklistKeluar.notes }}</p>
        </div>

        <!-- SPAREPART + COST -->
        <div v-if="service.spareparts?.length" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-4 text-zinc-900">🔧 Sparepart Terpakai</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color: var(--border-light);">
                            <th class="text-left py-2 px-2 text-xs font-semibold text-zinc-500">Produk</th>
                            <th class="text-right py-2 px-2 text-xs font-semibold text-zinc-500">Qty</th>
                            <th class="text-right py-2 px-2 text-xs font-semibold text-zinc-500">Harga</th>
                            <th class="text-right py-2 px-2 text-xs font-semibold text-zinc-500">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sp in service.spareparts" :key="sp.id" class="border-b" style="border-color: var(--border-light);">
                            <td class="py-2 px-2 text-zinc-900">{{ sp.product?.name || 'Produk dihapus' }}</td>
                            <td class="text-right py-2 px-2 text-zinc-600">{{ sp.quantity }}</td>
                            <td class="text-right py-2 px-2 text-zinc-600">Rp {{ formatNumber(sp.unit_price) }}</td>
                            <td class="text-right py-2 px-2 font-semibold text-zinc-900">Rp {{ formatNumber(sp.subtotal) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BIAYA -->
        <div v-if="showCost" class="rounded-xl border p-5" style="border-color: var(--border-color); background: var(--bg-card);">
            <h3 class="text-sm font-bold mb-4 text-zinc-900">💰 Rincian Biaya</h3>
            <div class="space-y-2 text-sm max-w-sm">
                <div class="flex justify-between">
                    <span class="text-zinc-500">Biaya Jasa</span>
                    <span class="text-zinc-900">Rp {{ formatNumber(service.service_charge) }}</span>
                </div>
                <div v-if="service.spareparts?.length" class="flex justify-between">
                    <span class="text-zinc-500">Sparepart</span>
                    <span class="text-zinc-900">Rp {{ formatNumber(sparepartTotal) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t font-bold text-base border-zinc-200">
                    <span class="text-zinc-900">Total</span>
                    <span class="text-indigo-600">Rp {{ formatNumber(service.total_cost || service.service_charge + sparepartTotal) }}</span>
                </div>
                <div v-if="service.payment_status" class="flex justify-between pt-2">
                    <span class="text-zinc-500">Status Bayar</span>
                    <span class="font-semibold" :style="{ color: service.payment_status === 'paid' ? 'var(--success)' : 'var(--danger)' }">
                        {{ service.payment_status === 'paid' ? 'Lunas' : 'Belum Bayar' }}
                    </span>
                </div>
                <div v-if="service.warranty_expired_at" class="flex justify-between">
                    <span class="text-zinc-500">Garansi s.d.</span>
                    <span class="text-zinc-900">{{ formatDate(service.warranty_expired_at) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import {
    formatNumber, formatDate,
    getChecklistItemName,
} from '@/Composables/useServiceStatus.js';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    templatesMasuk: { type: Array, default: () => [] },
    templatesKeluar: { type: Array, default: () => [] },
});

const sparepartTotal = computed(() => {
    return (props.service.spareparts || []).reduce((sum, sp) => sum + Number(sp.subtotal || 0), 0);
});

const showCost = computed(() => {
    return Number(props.service.service_charge) > 0 || (props.service.spareparts?.length || 0) > 0;
});

const checklistMasuk = computed(() => {
    return (props.service.checklists || []).find(c => c.type === 'masuk');
});

const checklistKeluar = computed(() => {
    return (props.service.checklists || []).find(c => c.type === 'keluar');
});
</script>
