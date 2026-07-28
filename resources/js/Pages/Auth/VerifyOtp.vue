<template>
    <GuestLayout>
        <h2 class="text-xl font-bold text-dark-900 mb-2">Verifikasi Email</h2>
        <p class="text-sm text-dark-400 mb-2">Masukkan kode OTP yang dikirim ke:</p>
        <p class="text-sm font-medium text-dark-600 mb-6">{{ email }}</p>

        <div v-if="message" class="mb-4 p-3 bg-success-50 border border-success-200 rounded-xl">
            <p class="text-sm text-success-700">{{ message }}</p>
        </div>
        <div v-if="form.errors.otp" class="mb-4 p-3 bg-accent-50 border border-accent-200 rounded-xl">
            <p class="text-sm text-accent-700">{{ form.errors.otp }}</p>
        </div>

        <form @submit.prevent="submit">
            <div class="mb-6">
                <label class="block text-sm font-medium text-dark-600 mb-2">Kode OTP</label>
                <div class="flex gap-3 justify-center">
                    <input
                        v-for="i in 6"
                        :key="i"
                        :ref="el => { if (el) otpInputs[i-1] = el }"
                        v-model="otpDigits[i-1]"
                        type="text"
                        maxlength="1"
                        class="w-12 h-14 text-center text-xl font-bold input-premium"
                        @input="onInput($event, i-1)"
                        @keydown.backspace="onBackspace($event, i-1)"
                        :autofocus="i === 1"
                    />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="resend" class="text-sm text-premium-600 hover:text-premium-500 font-semibold">
                    Kirim ulang OTP
                </button>
                <button
                    type="submit"
                    class="btn-premium-primary disabled:opacity-50"
                    :disabled="form.processing || otpDigits.some(d => !d)"
                >
                    Verifikasi
                </button>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    email: { type: String, required: true },
});

const page = usePage();
const message = computed(() => page.props.flash?.success || '');

const otpDigits = ref(['', '', '', '', '', '']);
const otpInputs = ref([]);

const form = useForm({
    email: props.email,
    otp: '',
});

const submit = () => {
    form.otp = otpDigits.value.join('');
    form.post(route('register.verify.submit'));
};

const resend = () => {
    form.post(route('register.otp.resend'));
};

const onInput = (e, idx) => {
    const val = e.target.value;
    otpDigits.value[idx] = val.replace(/[^0-9]/g, '');
    if (otpDigits.value[idx] && idx < 5) {
        otpInputs.value[idx + 1]?.focus();
    }
};

const onBackspace = (e, idx) => {
    if (!otpDigits.value[idx] && idx > 0) {
        otpInputs.value[idx - 1]?.focus();
    }
};
</script>
