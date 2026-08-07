<template>
  <div class="relative overflow-hidden border-b" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-light)' }">
    <!-- Decorative background -->
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-50/40 to-emerald-50/40 opacity-60"></div>
    <div class="absolute right-0 top-0 w-96 h-96 bg-gradient-to-br from-indigo-100/30 to-purple-100/30 blur-3xl rounded-full -translate-y-1/2 translate-x-1/3"></div>

    <div class="px-5 sm:px-8 py-5 sm:py-8 relative z-10 max-w-[1600px] mx-auto w-full">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <!-- Left: Greeting + Role badge -->
        <div class="flex-1 min-w-0">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-semibold mb-3 shadow-sm"
            :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)', color: 'var(--text-secondary)' }">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>{{ roleLabel }}</span>
            <span class="text-zinc-300">·</span>
            <span>{{ branchLabel }}</span>
          </div>

          <SkHeading level="2" gradient class="!text-2xl sm:!text-3xl">
            {{ greeting }}, {{ userName }}
          </SkHeading>

          <SkText variant="subtitle-sm" class="mt-1.5">
            {{ subtitle }}
          </SkText>
        </div>

        <!-- Right: Quick Actions -->
        <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
          <slot name="actions" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SkHeading from '@/Enterprise/Components/Typography/Heading.vue';
import SkText from '@/Enterprise/Components/Typography/Text.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  userName: { type: String, default: '' },
  userRole: { type: String, default: 'admin' },
  branchName: { type: String, default: '' },
});

const page = usePage();
const { greeting, currentDate } = useFormatter();

const roleLabels = {
  owner: 'Owner Workspace',
  admin: 'Administrator Workspace',
  manager: 'Manager Workspace',
  head_store: 'Kepala Toko Workspace',
  cs: 'Customer Service Workspace',
  technician: 'Teknisi Workspace',
  cashier: 'Kasir Workspace',
  courier: 'Kurir Workspace',
  custom: 'Custom Workspace',
};

const roleLabel = computed(() => roleLabels[props.userRole] || 'Workspace');
const branchLabel = computed(() => props.branchName || page.props.currentBranch?.name || 'Pusat');
const subtitle = computed(() => `Ringkasan operasional — ${currentDate.value}`);
</script>
