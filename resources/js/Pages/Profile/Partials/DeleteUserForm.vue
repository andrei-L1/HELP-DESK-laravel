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
        <header class="flex items-start gap-4">
            <div class="mt-1 flex-shrink-0 w-12 h-12 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center">
                <ExclamationTriangleIcon class="w-6 h-6 text-rose-600" />
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Danger Zone
                </h2>
                <p class="mt-2 text-sm text-gray-500 max-w-xl leading-relaxed">
                    Once your account is deleted, all of its resources and data will
                    be permanently deleted. This action <strong>cannot</strong> be undone. Before deleting your account, please
                    download any data or information that you wish to retain.
                </p>
            </div>
        </header>

        <!-- Action Button -->
        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
            <button 
                @click="confirmUserDeletion"
                class="inline-flex items-center px-6 py-3 bg-white border border-rose-200 rounded-xl font-bold text-rose-600 shadow-sm hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all"
            >
                <ExclamationTriangleIcon class="w-5 h-5 mr-2" />
                Delete Account
            </button>
        </div>

        <!-- Confirmation Modal -->
        <Modal :show="confirmingUserDeletion" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <!-- Modal Header -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                        <ExclamationTriangleIcon class="w-6 h-6 text-rose-600" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">
                        Are you absolutely sure?
                    </h2>
                </div>

                <p class="text-sm text-gray-600 leading-relaxed">
                    This action will permanently delete your account, 
                    messages, settings, and all associated data.
                    <br><br>
                    Please enter your password to confirm deletion.
                </p>

                <!-- Password Input -->
                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 transition duration-150"
                        placeholder="Enter your password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <!-- Modal Actions -->
                <div class="mt-8 flex justify-end gap-3">
                    <button 
                        @click="closeModal"
                        class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 rounded-xl font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all"
                    >
                        Cancel
                    </button>

                    <button
                        :class="[
                            form.processing ? 'opacity-50 cursor-not-allowed' : '',
                            'inline-flex items-center px-5 py-2.5 bg-rose-600 border border-transparent rounded-xl font-bold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all'
                        ]"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ form.processing ? 'Deleting...' : 'Delete Account' }}
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
