<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
});

const title = computed(() => {
    return {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Page Not Found',
        403: '403: Forbidden',
    }[props.status] || 'Error';
});

const description = computed(() => {
    return {
        503: 'Sorry, we are doing some maintenance. Please check back soon.',
        500: 'Whoops, something went wrong on our servers.',
        404: 'The page you are looking for could not be found.',
        403: 'Sorry, you are forbidden from accessing this page.',
    }[props.status] || 'An unexpected error occurred.';
});

const icon = computed(() => {
    return {
        503: '🛠️',
        500: '🧨',
        404: '🔎',
        403: '🚫',
    }[props.status] || '⚠️';
});
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md text-center">
            <div class="mb-6 text-8xl">{{ icon }}</div>
            <h1 class="mb-2 text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">
                {{ title }}
            </h1>
            <p class="mb-8 text-lg text-gray-600">
                {{ description }}
            </p>
            <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-4 sm:justify-center">
                <Link
                    href="/"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-6 py-3 text-base font-bold text-white shadow-lg transition-all hover:bg-slate-800 hover:scale-105 active:scale-95"
                >
                    Back to Home
                </Link>
                <button
                    @click="() => window.history.back()"
                    class="inline-flex items-center justify-center rounded-xl bg-white border border-gray-200 px-6 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:scale-105 active:scale-95"
                >
                    Go Back
                </button>
            </div>
            
            <div class="mt-12 text-sm text-gray-400">
                If you think this is a mistake, please contact our support team.
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add some nice subtle animations */
.text-8xl {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
</style>
