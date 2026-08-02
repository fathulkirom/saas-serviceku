<template>
    <GuestLayout>
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-zinc-900 mb-2">Verifikasi OTP</h2>
            <p class="text-sm text-zinc-500 mb-1">Masukkan kode OTP yang telah dikirim ke email:</p>
            <p class="text-sm font-bold text-zinc-900">{{ email }}</p>
        </div>

        <div v-if="message" class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold text-emerald-800">{{ message }}</p>
        </div>
        
        <div v-if="form.errors.otp" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold text-red-800">{{ form.errors.otp }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <div class="flex gap-2 sm:gap-3 justify-center mb-2">
                    <KInput 
                        v-for="i in 6"
                        :key="i"
                        :ref="el => { if (el) otpInputs[i-1] = el }"
                        v-model="otpDigits[i-1]"
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        class="w-10 h-12 sm:w-14 sm:h-16 text-center text-xl sm:text-2xl font-black rounded-xl border border-zinc-300 bg-white text-zinc-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none shadow-sm transition-all"
                        @input="onInput($event, i-1)"
                        @keydown.backspace="onBackspace($event, i-1)"
                        :autofocus="i === 1" />
                </div>
            </div>

            <div class="pt-2 flex flex-col gap-4">
                <KButton 
                    type="submit"
                    class="w-full flex items-center justify-center px-6 py-3 rounded-xl bg-zinc-900 text-white text-sm font-bold shadow-md hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all disabled:opacity-70"
                    :disabled="form.processing || otpDigits.some(d => !d)">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Verifikasi OTP
                </KButton>
                <KButton  type="button" @click="resend" class="text-center text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                    Belum menerima kode? Kirim ulang
                </KButton>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import KButton from '@/Components/KButton.vue';
import KInput from '@/Components/KInput.vue';

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
