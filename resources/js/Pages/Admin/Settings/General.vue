<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    app_name: props.settings.app_name,
    app_url: props.settings.app_url,
    app_timezone: props.settings.app_timezone,
    app_locale: props.settings.app_locale,
    company_name: props.settings.company_name,
    company_email: props.settings.company_email,
    company_phone: props.settings.company_phone,
    company_address: props.settings.company_address,
});

const submit = () => {
    form.post(route('admin.settings.general.update'), {
        preserveScroll: true,
    });
};

// Timezone options
const timezones = [
    'UTC',
    'Asia/Manila',
    'Asia/Singapore',
    'Asia/Tokyo',
    'Asia/Shanghai',
    'Asia/Dubai',
    'Australia/Sydney',
    'Europe/London',
    'Europe/Paris',
    'Europe/Berlin',
    'America/New_York',
    'America/Chicago',
    'America/Denver',
    'America/Los_Angeles',
    'America/Toronto',
    'America/Sao_Paulo',
];

// Locale options
const locales = [
    { value: 'en', label: 'English' },
    { value: 'es', label: 'Spanish' },
    { value: 'fr', label: 'French' },
    { value: 'de', label: 'German' },
    { value: 'it', label: 'Italian' },
    { value: 'pt', label: 'Portuguese' },
    { value: 'ru', label: 'Russian' },
    { value: 'ja', label: 'Japanese' },
    { value: 'ko', label: 'Korean' },
    { value: 'zh', label: 'Chinese' },
    { value: 'tl', label: 'Tagalog' },
];

const saveSuccess = ref(false);

const showSuccessMessage = () => {
    saveSuccess.value = true;
    setTimeout(() => {
        saveSuccess.value = false;
    }, 3000);
};
</script>

<template>
    <Head title="General Settings" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.settings.index')"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Settings
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">General Settings</h1>
            </div>
        </template>

        <div class="p-6">
            <!-- Success Message -->
            <div
                v-if="saveSuccess"
                class="mb-4 rounded-md bg-green-50 p-4"
            >
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            Settings saved successfully!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error Summary -->
            <div
                v-if="form.hasErrors"
                class="mb-4 rounded-md bg-red-50 p-4"
            >
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Please fix the following errors:
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li v-for="(error, field) in form.errors" :key="field">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <form @submit.prevent="submit">
                    <!-- Application Settings -->
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700">Application Settings</h2>
                        <p class="text-xs text-gray-500 mt-1">Configure your application name and URL</p>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="app_name" class="block text-sm font-medium text-gray-700">
                                    Application Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="app_name"
                                    v-model="form.app_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="HelpDesk System"
                                />
                                <p v-if="form.errors.app_name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.app_name }}
                                </p>
                            </div>

                            <div>
                                <label for="app_url" class="block text-sm font-medium text-gray-700">
                                    Application URL <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="app_url"
                                    v-model="form.app_url"
                                    type="url"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="https://helpdesk.example.com"
                                />
                                <p v-if="form.errors.app_url" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.app_url }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="app_timezone" class="block text-sm font-medium text-gray-700">
                                    Timezone <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="app_timezone"
                                    v-model="form.app_timezone"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option v-for="tz in timezones" :key="tz" :value="tz">
                                        {{ tz }}
                                    </option>
                                </select>
                                <p v-if="form.errors.app_timezone" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.app_timezone }}
                                </p>
                            </div>

                            <div>
                                <label for="app_locale" class="block text-sm font-medium text-gray-700">
                                    Default Language <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="app_locale"
                                    v-model="form.app_locale"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option v-for="locale in locales" :key="locale.value" :value="locale.value">
                                        {{ locale.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.app_locale" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.app_locale }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Company Information -->
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700">Company Information</h2>
                        <p class="text-xs text-gray-500 mt-1">Your company details for notifications and branding</p>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">
                                    Company Name
                                </label>
                                <input
                                    id="company_name"
                                    v-model="form.company_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="ACME Inc."
                                />
                            </div>

                            <div>
                                <label for="company_email" class="block text-sm font-medium text-gray-700">
                                    Company Email
                                </label>
                                <input
                                    id="company_email"
                                    v-model="form.company_email"
                                    type="email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="info@company.com"
                                />
                                <p v-if="form.errors.company_email" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.company_email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="company_phone" class="block text-sm font-medium text-gray-700">
                                    Company Phone
                                </label>
                                <input
                                    id="company_phone"
                                    v-model="form.company_phone"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="+1 (555) 123-4567"
                                />
                            </div>

                            <div>
                                <label for="company_address" class="block text-sm font-medium text-gray-700">
                                    Company Address
                                </label>
                                <input
                                    id="company_address"
                                    v-model="form.company_address"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="123 Business St, City, Country"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end gap-3">
                        <Link
                            :href="route('admin.settings.index')"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            class="inline-flex justify-center rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Card -->
            <div class="mt-6 rounded-lg bg-blue-50 border border-blue-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">About General Settings</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>
                                These settings control the basic behavior of your helpdesk system. 
                                Changes to Application Name and URL may require clearing your browser cache.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>