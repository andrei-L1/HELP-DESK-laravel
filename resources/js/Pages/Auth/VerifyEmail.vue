<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: String },
});

const form = useForm({});

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const submit = () => {
    form.post(route('verification.send'));
};

// Poll every 4 seconds — if the user verified in another tab/window,
// this page will auto-reload and the middleware will forward them to their dashboard.
let pollInterval = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({ only: [] }); // lightweight ping — if verified, server redirects to dashboard
    }, 4000);
});

onUnmounted(() => {
    clearInterval(pollInterval);
});
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-blue-100">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Check your inbox</h2>
                    <p class="text-xs text-gray-500">Verification email sent</p>
                </div>
            </div>

            <p class="text-sm text-gray-600">
                We sent a verification link to your email address. Click the link in the email to activate your account.
            </p>

            <!-- Explain the new-window behavior -->
            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5">
                <p class="text-xs text-amber-700">
                    <span class="font-semibold">ℹ️ The link will open in a new browser tab</span> — that's normal. 
                    Once you click it, this page will automatically redirect you to your dashboard.
                </p>
            </div>
        </div>

        <!-- Auto-redirect status -->
        <div v-if="verificationLinkSent" class="mb-4 rounded-lg bg-green-50 px-3 py-2 text-sm font-medium text-green-700 border border-green-200">
            ✓ A new verification link has been sent to your email address.
        </div>

        <!-- Polling indicator -->
        <div class="mb-4 flex items-center gap-2 text-xs text-gray-400">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
            </span>
            Waiting for verification… this page will update automatically.
        </div>

        <form @submit.prevent="submit">
            <div class="flex items-center justify-between gap-3">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Sending…' : 'Resend Verification Email' }}
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-gray-500 underline hover:text-gray-700"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
