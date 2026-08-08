<template>
  <AuthenticatedLayout>
    <template #header>
      <PageHeader title="Profil Saya" :subtitle="currentDate" />
    </template>

    <div class="max-w-2xl space-y-6">
      <!-- Flash Message -->
      <div v-if="flashMessage" class="p-3 rounded-lg border text-sm" :class="flashMessage.type === 'success' ? 'sk-bg-success-soft sk-text-success sk-border-primary' : flashMessage.type === 'error' ? 'sk-bg-danger-soft sk-text-danger sk-border-primary' : 'sk-bg-info-soft sk-text-info border-blue-200'">
        {{ flashMessage.text }}
      </div>

      <form @submit.prevent="submit">
        <KCard title="Data Diri">
          <div class="space-y-4">
            <div>
              <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Nama</label>
              <KInput  v-model="form.name" class="input text-sm mt-1" required />
            </div>
            <div>
              <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Email</label>
              <KInput  v-model="form.email" type="email" class="input text-sm mt-1" required />
            </div>
            <div>
              <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Password Baru <span class="text-muted">(kosongkan jika tidak ingin ganti)</span></label>
              <KInput  v-model="form.password" type="password" class="input text-sm mt-1" autocomplete="new-password" />
            </div>
            <div>
              <label class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Role</label>
              <KInput  :value="user.role" class="input text-sm mt-1" disabled />
            </div>
          </div>
          <div class="flex justify-end mt-6">
            <KButton  type="submit" :disabled="form.processing" class="btn-primary text-xs">
              {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </KButton>
          </div>
        </KCard>
      </form>

      <!-- 2FA Section -->
      <TwoFactorSetup @message="onMessage" />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';

import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KCard from '@/Components/KCard.vue';
import TwoFactorSetup from '@/Pages/Profile/TwoFactor.vue';
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

const flashMessage = ref(null);

function onMessage(msg) {
  flashMessage.value = msg;
  setTimeout(() => flashMessage.value = null, 5000);
}
</script>
