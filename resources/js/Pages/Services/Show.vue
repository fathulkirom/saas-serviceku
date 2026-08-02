<template>
    <AuthenticatedLayout>
        <template #header>
            <ServiceHeader :service="service" />
        </template>

        <div class="max-w-5xl mx-auto space-y-5">
            <ServiceActionBar
                :service="service"
                @assign="assignModal.open()"
                @cancel="cancelModal.open()"
                @partner="partnerModal.open()"
                @complete="completeModal.open()"
                @checklist-masuk="checklistMasukModal.open()"
                @checklist-keluar="checklistKeluarModal.open()"
            />

            <ServiceStatusStepper :service="service" />

            <ServiceInfoCards :service="service" :previous-services="previousServices" />

            <ServiceSections
                :service="service"
                :templates-masuk="templatesMasuk"
                :templates-keluar="templatesKeluar"
            />

            <ServicePhotos :service="service" :drive-connected="driveConnected" />

            <ServiceHistory :service="service" />

            <ServiceAssignModal ref="assignModal" :service="service" :users="users" />
            <ServiceCancelModal ref="cancelModal" :service="service" />
            <ServicePartnerModal ref="partnerModal" :service="service" />
            <ServiceCompleteModal ref="completeModal" :service="service" :products="products" :templates-keluar="templatesKeluar" />
            <ServiceChecklistModal ref="checklistMasukModal" :service="service" :templates="templatesMasuk" mode="masuk" />
            <ServiceChecklistModal ref="checklistKeluarModal" :service="service" :templates="templatesKeluar" mode="keluar" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ServiceHeader from '@/Components/Services/ServiceHeader.vue';
import ServiceActionBar from '@/Components/Services/ServiceActionBar.vue';
import ServiceStatusStepper from '@/Components/Services/ServiceStatusStepper.vue';
import ServiceInfoCards from '@/Components/Services/ServiceInfoCards.vue';
import ServiceSections from '@/Components/Services/ServiceSections.vue';
import ServicePhotos from '@/Components/Services/ServicePhotos.vue';
import ServiceHistory from '@/Components/Services/ServiceHistory.vue';
import ServiceAssignModal from '@/Components/Services/ServiceAssignModal.vue';
import ServiceCancelModal from '@/Components/Services/ServiceCancelModal.vue';
import ServicePartnerModal from '@/Components/Services/ServicePartnerModal.vue';
import ServiceCompleteModal from '@/Components/Services/ServiceCompleteModal.vue';
import ServiceChecklistModal from '@/Components/Services/ServiceChecklistModal.vue';

const props = defineProps({
    service: { type: Object, default: () => ({}) },
    templatesKeluar: { type: Array, default: () => [] },
    templatesMasuk: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    previousServices: { type: Array, default: () => [] },
    driveConnected: { type: Boolean, default: false },
});

const assignModal = ref(null);
const cancelModal = ref(null);
const partnerModal = ref(null);
const completeModal = ref(null);
const checklistMasukModal = ref(null);
const checklistKeluarModal = ref(null);
</script>
