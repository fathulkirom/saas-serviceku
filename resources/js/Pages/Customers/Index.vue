<template>
    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Pelanggan" :subtitle="`${customers.total || 0} pelanggan terdaftar`">
                <Link :href="route('customers.create')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white transition-all hover:shadow-md" style="background: var(--accent-primary);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Pelanggan Baru
                </Link>
            </PageHeader>
        </template>

        <!-- Search -->
        <div class="bg-white rounded-xl border p-3 shadow-sm mb-4" style="border-color: var(--border-color);">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <input type="text" v-model="search" placeholder="Cari nama / telepon..." class="w-full rounded-lg border text-xs px-3 py-2 pl-8 focus:ring-2 focus:outline-none border-dark-200 text-dark-700 focus:border-blue-500 focus:ring-blue-200 transition-all" />
                        <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <KCard padding="sm">
            <KTable :columns="customerColumns" :rows="filteredCustomers">
                <template #cell-nama="{ row }">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0 shadow-sm" style="background: var(--accent-primary);">
                            {{ getInitials(row.name) }}
                        </div>
                        <span class="text-sm font-semibold text-dark-800">{{ row.name }}</span>
                    </div>
                </template>
                <template #cell-telepon="{ row }">
                    <span class="text-dark-500 font-medium">{{ row.phone || '-' }}</span>
                </template>
                <template #cell-email="{ row }">
                    <span class="text-dark-400">{{ row.email || '-' }}</span>
                </template>
                <template #cell-member="{ row }">
                    <div v-if="row.is_member">
                        <Badge variant="green" dot>Member</Badge>
                        <p class="font-mono text-[10px] font-bold text-blue-600 mt-0.5">{{ row.card_number || 'ACS' + row.id }}</p>
                    </div>
                    <button v-else @click="registerMember(row)" class="px-2 py-0.5 rounded text-[10px] font-bold text-white shadow-sm transition-all hover:opacity-90" style="background: #dc2626;" title="Terbitkan Kartu Member">
                        + Terbitkan Member
                    </button>
                </template>
                <template #cell-poin="{ row }">
                    <span class="font-bold text-emerald-600 text-xs">{{ row.points || 0 }} Poin</span>
                </template>
                <template #cell-servis="{ row }">
                    <span class="font-semibold text-dark-500">{{ row.services_count || 0 }}</span>
                </template>
                <template #cell-aksi="{ row }">
                    <Link :href="route('customers.show', row.id)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-all hover:shadow-md" style="background: #3498db;">
                        Detail
                    </Link>
                </template>
                <template #empty>
                    <EmptyState icon="users" title="Belum ada pelanggan" description="Belum ada pelanggan terdaftar." />
                </template>
            </KTable>
            <Pagination :meta="customers" :per-page="customers.per_page" />
        </KCard>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import KCard from '@/Components/KCard.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    customers: { type: Object, default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 }) },
});

const search = ref('');

const customerColumns = [
    { key: 'nama', label: 'Nama', align: 'left' },
    { key: 'telepon', label: 'Telepon', align: 'left' },
    { key: 'email', label: 'Email', align: 'left' },
    { key: 'member', label: 'No. Kartu Member', align: 'center' },
    { key: 'poin', label: 'Poin', align: 'center' },
    { key: 'servis', label: 'Servis', align: 'center' },
    { key: 'aksi', label: 'Aksi', align: 'center' },
];

const filteredCustomers = computed(() => {
    let items = props.customers.data || [];
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        items = items.filter(c => c.name?.toLowerCase().includes(q) || c.phone?.includes(q) || c.card_number?.toLowerCase().includes(q));
    }
    return items;
});

const registerMember = (customer) => {
    if (!confirm(`Terbitkan Kartu Member untuk ${customer.name}?`)) return;
    router.post(route('customers.register-member', customer.id), {}, {
        preserveScroll: true,
    });
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};
</script>
