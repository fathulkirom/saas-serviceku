<template>
    <div class="flex flex-wrap items-center gap-2 p-4 rounded-xl border" style="border-color: var(--border-color); background: var(--bg-card);">
        <template v-if="isActive">
            <KButton v-if="canAssign" variant="action-indigo" :disabled="processingAction" @click="emit('assign')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Assign Teknisi
            </KButton>
            <KButton v-if="canAccept" variant="action-info" :disabled="processingAction === 'services.accept'" @click="postAction('services.accept')">
                {{ processingAction === 'services.accept' ? 'Memproses...' : 'Terima Pekerjaan' }}
            </KButton>
            <KButton v-if="canStart" variant="action-info" :disabled="processingAction === 'services.start'" @click="postAction('services.start')">
                {{ processingAction === 'services.start' ? 'Memproses...' : 'Mulai Pekerjaan' }}
            </KButton>
            <KButton v-if="canFinish" variant="action-success" :disabled="processingAction === 'services.finish'" @click="postAction('services.finish')">
                <svg v-if="processingAction !== 'services.finish'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ processingAction === 'services.finish' ? 'Memproses...' : 'Selesaikan Pekerjaan' }}
            </KButton>
            <KButton v-if="canConfirmCustomer" variant="action-warning" :disabled="processingAction === 'services.confirm-customer'" @click="postAction('services.confirm-customer')">
                {{ processingAction === 'services.confirm-customer' ? 'Memproses...' : 'Konfirmasi Pelanggan' }}
            </KButton>
            <KButton v-if="canConfirmInternal" variant="action-warning" :disabled="processingAction === 'services.confirm-internal'" @click="postAction('services.confirm-internal')">
                {{ processingAction === 'services.confirm-internal' ? 'Memproses...' : 'Konfirmasi Internal' }}
            </KButton>
            <KButton v-if="canApprove" variant="action-success" :disabled="processingAction === 'services.approve-confirmation'" @click="postAction('services.approve-confirmation')">
                {{ processingAction === 'services.approve-confirmation' ? 'Memproses...' : 'Setujui Konfirmasi' }}
            </KButton>
            <KButton v-if="canReallocate" variant="action-danger" :disabled="processingAction === 'services.request-reallocation'" @click="postAction('services.request-reallocation')">
                {{ processingAction === 'services.request-reallocation' ? 'Memproses...' : 'Request Alokasi Ulang' }}
            </KButton>
            <KButton v-if="canPartner" variant="action-indigo" :disabled="processingAction" @click="emit('partner')">
                Kirim ke Partner
            </KButton>
            <KButton v-if="canCompletePartner" variant="action-indigo" :disabled="processingAction === 'services.complete-partner'" @click="postAction('services.complete-partner')">
                {{ processingAction === 'services.complete-partner' ? 'Memproses...' : 'Partner Selesai' }}
            </KButton>
            <KButton v-if="canTakeOver" variant="action-warning" :disabled="processingAction === 'services.take-over'" @click="postAction('services.take-over')">
                {{ processingAction === 'services.take-over' ? 'Memproses...' : 'Ambil Alih' }}
            </KButton>
            <KButton v-if="canCancel" variant="action-danger" :disabled="processingAction" @click="emit('cancel')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Batalkan
            </KButton>
            <KButton v-if="canIndent" variant="action-indigo" :disabled="processingAction === 'services.indent'" @click="postAction('services.indent')">
                {{ processingAction === 'services.indent' ? 'Memproses...' : 'Indent Sparepart' }}
            </KButton>
            <KButton v-if="canResumeIndent" variant="action-success" :disabled="processingAction === 'services.resume-from-indent'" @click="postAction('services.resume-from-indent')">
                {{ processingAction === 'services.resume-from-indent' ? 'Memproses...' : 'Lanjutkan dari Indent' }}
            </KButton>
            <KButton v-if="canWork" variant="action-outline" :disabled="processingAction" @click="emit('checklist-masuk')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                {{ checklistMasuk ? 'Edit Checklist Masuk' : 'Isi Checklist Masuk' }}
            </KButton>
            <KButton v-if="canWork" variant="action-blue" :disabled="processingAction" @click="emit('checklist-keluar')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                {{ checklistKeluar ? 'Edit Checklist Keluar' : 'Isi Checklist Keluar' }}
            </KButton>
        </template>
        <template v-if="service.status === 'selesai'">
            <KButton v-if="!service.sale" variant="action-warning" :to="route('keuangan.index', { tab: 'penjualan' })">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Buat Nota
            </KButton>
            <KButton v-if="canComplete" variant="action-success" @click="emit('complete')">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Complete Servis
            </KButton>
            <KButton v-if="canClaimWarranty" variant="action-warning" @click="executeWarrantyClaim">
                Klaim Garansi
            </KButton>
        </template>
        <KButton variant="action-outline" :href="route('services.print-receipt', service.id)" target="_blank">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Tanda Terima
        </KButton>
        <KButton v-if="service.parent_service_id" variant="action-outline" :to="route('services.show', service.parent_service_id)">
            ← Servis Induk #{{ service.parent_service_id }}
        </KButton>
        <KButton v-if="service.sale" variant="action-indigo" :to="route('sales.show', service.sale.id)">
            Lihat Nota #{{ service.sale.id }}
        </KButton>
        <KButton variant="action-outline" :to="route('services.edit', service.id)">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Servis
        </KButton>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import KButton from '@/Components/KButton.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['assign', 'cancel', 'partner', 'complete', 'checklist-masuk', 'checklist-keluar']);

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const userRole = computed(() => user.value?.role || '');
const rolePerms = computed(() => page.props.role_permissions?.[userRole.value] || []);
const isOwner = computed(() => userRole.value === 'owner');
const isTechnician = computed(() => userRole.value === 'technician');
const canWork = computed(() => rolePerms.value.includes('work_on_services'));
const assignedToMe = computed(() => props.service.technician_id === user.value?.id);

const isActive = computed(() => !['selesai', 'cancel', 'void', 'close'].includes(props.service.status));

const processingAction = ref(null);

const checklistMasuk = computed(() => {
    return (props.service.checklists || []).find(c => c.type === 'masuk');
});

const checklistKeluar = computed(() => {
    return (props.service.checklists || []).find(c => c.type === 'keluar');
});

const canClaimWarranty = computed(() => {
    if (props.service.status !== 'selesai') return false;
    if (props.service.payment_status !== 'paid') return false;
    if (!props.service.warranty_expired_at) return false;
    return new Date(props.service.warranty_expired_at) > new Date();
});

// Action permissions
const canAssign = computed(() => isOwner.value && props.service.status === 'menunggu_alokasi');
const canAccept = computed(() => isTechnician.value && props.service.status === 'menunggu_alokasi');
const canStart = computed(() => (isOwner.value || assignedToMe.value) && props.service.status === 'diterima');
const canFinish = computed(() => (isOwner.value || assignedToMe.value) && props.service.status === 'dikerjakan');
const canConfirmCustomer = computed(() => (isOwner.value || assignedToMe.value) && props.service.status === 'dikerjakan');
const canConfirmInternal = computed(() => (isOwner.value || assignedToMe.value) && props.service.status === 'dikerjakan');
const canApprove = computed(() => isOwner.value && ['menunggu_konfirmasi_pelanggan', 'menunggu_konfirmasi_internal'].includes(props.service.status));
const canReallocate = computed(() => (isOwner.value || assignedToMe.value) && ['diterima', 'dikerjakan'].includes(props.service.status));
const canPartner = computed(() => (isOwner.value || canWork.value) && ['dikerjakan', 'menunggu_alokasi'].includes(props.service.status));
const canCompletePartner = computed(() => (isOwner.value || canWork.value) && props.service.status === 'onpartner');
const canTakeOver = computed(() => !isTechnician.value && isActive.value && props.service.status !== 'onpartner');
const canCancel = computed(() => (isOwner.value || assignedToMe.value) && [
    'menunggu_alokasi', 'diterima', 'diagnosa', 'dikerjakan',
    'menunggu_konfirmasi_pelanggan', 'menunggu_konfirmasi_internal',
    'indent', 'onpartner'
].includes(props.service.status));
const canComplete = computed(() => (isOwner.value || canWork.value) && props.service.status === 'selesai');
const canIndent = computed(() => (isOwner.value || assignedToMe.value) && ['dikerjakan', 'menunggu_alokasi'].includes(props.service.status));
const canResumeIndent = computed(() => (isOwner.value || canWork.value) && props.service.status === 'indent');

// Actions
function postAction(routeName) {
    processingAction.value = routeName;
    router.post(route(routeName, props.service.id), {}, {
        preserveScroll: true,
        onFinish: () => { processingAction.value = null; },
    });
}

function executeWarrantyClaim() {
    if (!confirm('Buat klaim garansi untuk servis #' + props.service.id + '?')) return;
    router.post(route('services.warranty-claim', props.service.id), {}, {
        preserveScroll: true,
    });
}
</script>
