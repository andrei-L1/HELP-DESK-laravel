<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <!-- Header Section -->
        <header class="flex items-start gap-6">
            <div class="mt-1 flex-shrink-0 w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center shadow-inner">
                <ExclamationTriangleIcon class="w-8 h-8 text-rose-600 animate-pulse" />
            </div>
            <div>
                <h2 class="text-xl font-black text-rose-600 tracking-tight uppercase">
                    Danger Zone
                </h2>
                <p class="mt-2 text-sm text-slate-500 max-w-xl leading-relaxed font-bold italic">
                    Once your account is deleted, all resources and data will
                    be permanently removed from our infrastructure. This action <strong>cannot</strong> be rolled back.
                </p>
            </div>
        </header>

        <!-- Action Button -->
        <div class="mt-10 pt-8 border-t border-rose-100 flex justify-end">
            <button 
                @click="confirmUserDeletion"
                class="inline-flex items-center px-8 py-4 bg-white border border-rose-200 rounded-2xl font-black text-[10px] uppercase tracking-widest text-rose-600 shadow-sm hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all active:scale-95"
            >
                <ExclamationTriangleIcon class="w-4 h-4 mr-2.5" />
                Delete Account
            </button>
        </div>

        <!-- Confirmation Modal -->
        <Modal :show="confirmingUserDeletion" @close="closeModal" maxWidth="md">
            <div class="p-10 bg-white">
                <!-- Modal Header -->
                <div class="flex flex-col items-center text-center mb-8">
                    <div class="w-20 h-20 rounded-3xl bg-rose-50 flex items-center justify-center mb-6 shadow-inner ring-1 ring-rose-100">
                        <ExclamationTriangleIcon class="w-10 h-10 text-rose-600" />
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                        Delete Account
                    </h2>
                    <p class="mt-4 text-sm text-slate-500 leading-relaxed font-bold italic">
                        Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                    </p>
                </div>

                <!-- Password Input -->
                <div class="mt-8">
                    <label class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2 block">Password</label>

                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="block w-full rounded-2xl border-none bg-slate-50 shadow-inner px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-rose-500/10 transition-all"
                        placeholder="••••••••••••"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <!-- Modal Actions -->
                <div class="mt-10 flex flex-col gap-3">
                    <button
                        :class="[
                            form.processing ? 'opacity-30 cursor-not-allowed' : '',
                            'w-full inline-flex items-center justify-center px-8 py-5 bg-rose-600 border border-transparent rounded-2xl font-black text-[11px] uppercase tracking-[0.15em] text-white shadow-xl shadow-rose-200 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all active:scale-95'
                        ]"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ form.processing ? 'DELETING...' : 'DELETE ACCOUNT' }}
                    </button>

                    <button 
                        @click="closeModal"
                        class="w-full inline-flex items-center justify-center px-8 py-4 bg-slate-50 border border-slate-100 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-all active:scale-95"
                    >
                        CANCEL
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
