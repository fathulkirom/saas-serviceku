<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader title="Profil Saya" :subtitle="currentDate" />
    </template>

    <div class="max-w-2xl">
      <form @submit.prevent="submit">
        <KCard title="Data Diri">
          <div class="space-y-4">
            <div>
              <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Nama</label>
              <input v-model="form.name" class="input text-sm mt-1" required />
            </div>
            <div>
              <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Email</label>
              <input v-model="form.email" type="email" class="input text-sm mt-1" required />
            </div>
            <div>
              <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Password Baru <span class="text-muted">(kosongkan jika tidak ingin ganti)</span></label>
              <input v-model="form.password" type="password" class="input text-sm mt-1" autocomplete="new-password" />
            </div>
            <div>
              <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Role</label>
              <input :value="user.role" class="input text-sm mt-1" disabled />
            </div>
          </div>
          <div class="flex justify-end mt-6">
            <button type="submit" :disabled="form.processing" class="btn-primary text-xs">
              {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </KCard>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KCard from '@/Components/KCard.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const { currentDate } = useFormatter();

const props = defineProps({
  user: { type: Object, required: true },
});

const form = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  password: '',
});

function submit() {
  form.put(route('user.profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      form.password = '';
    },
  });
}
</script>
