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
        onSuccess: () => {
            showSuccessMessage();
        },
    });
};

const timezones = [
    'UTC', 'Asia/Manila', 'Asia/Singapore', 'Asia/Tokyo', 'Asia/Shanghai', 'Asia/Dubai',
    'Australia/Sydney', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'America/New_York',
    'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'America/Toronto', 'America/Sao_Paulo',
];

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
    }, 5000);
};
</script>

<template>
    <Head title="General Settings" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex flex-col">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                    <Link :href="route('admin.settings.index')" class="hover:text-slate-900 transition-colors">Settings</Link>
                    <span>/</span>
                    <span class="text-slate-900">General</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">General Configuration</h1>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-8 pb-20 pt-4">
            <!-- Breadcrumb Navigation for Mobile/Alternative -->
            <div class="flex items-center justify-between px-2 stagger-1">
                <Link :href="route('admin.settings.index')" class="group inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-slate-900 transition-all">
                    <div class="h-8 w-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center group-hover:border-slate-300 shadow-sm transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                    </div>
                    Back to Overview
                </Link>
                <div v-if="saveSuccess" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold animate-in fade-in slide-in-from-bottom-4">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    Changes saved successfully
                </div>
            </div>

            <!-- Error Banner -->
            <div v-if="form.hasErrors" class="px-2 stagger-1">
                <div class="bg-rose-50 border border-rose-100 rounded-2xl p-6 flex items-start gap-4">
                    <div class="h-10 w-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-rose-900 uppercase tracking-tight mb-1">Configuration Errors</h3>
                        <p class="text-sm text-rose-700 font-medium">Please review the highlighted fields below before saving.</p>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden stagger-2">
                <form @submit.prevent="submit" class="divide-y divide-slate-100">
                    <!-- Section: Core Settings -->
                    <div class="p-8 lg:p-10 space-y-8">
                        <div class="flex items-center gap-4 mb-2">
                            <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg shadow-slate-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 tracking-tight">Platform Identity</h3>
                                <p class="text-sm text-slate-500 font-medium font-serif italic">Define your application naming and connectivity.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="app_name" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Application Name</label>
                                <input id="app_name" v-model="form.app_name" type="text" 
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all placeholder:text-slate-300"
                                    placeholder="HelpDesk Pro" />
                                <p v-if="form.errors.app_name" class="text-xs font-bold text-rose-500 mt-2 ml-1 italic">{{ form.errors.app_name }}</p>
                            </div>

                            <div class="space-y-2">
                                <label for="app_url" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Instance URL</label>
                                <input id="app_url" v-model="form.app_url" type="url" 
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all placeholder:text-slate-300"
                                    placeholder="https://support.example.com" />
                                <p v-if="form.errors.app_url" class="text-xs font-bold text-rose-500 mt-2 ml-1 italic">{{ form.errors.app_url }}</p>
                            </div>

                            <div class="space-y-2">
                                <label for="app_timezone" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Regional Timezone</label>
                                <div class="relative group">
                                    <select id="app_timezone" v-model="form.app_timezone" 
                                        class="w-full appearance-none rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                        <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
                                    </select>
                                    <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover:text-slate-900 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="app_locale" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Default Locale</label>
                                <div class="relative group">
                                    <select id="app_locale" v-model="form.app_locale" 
                                        class="w-full appearance-none rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                        <option v-for="locale in locales" :key="locale.value" :value="locale.value">{{ locale.label }}</option>
                                    </select>
                                    <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover:text-slate-900 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Company Attribution -->
                    <div class="p-8 lg:p-10 space-y-8 bg-slate-50/30">
                        <div class="flex items-center gap-4 mb-2">
                            <div class="h-10 w-10 rounded-xl bg-white border border-slate-200 text-slate-900 flex items-center justify-center shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 tracking-tight">Organization Details</h3>
                                <p class="text-sm text-slate-500 font-medium">Public-facing information used for branding and automated emails.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="company_name" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Legal Entity Name</label>
                                <input id="company_name" v-model="form.company_name" type="text" 
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" />
                            </div>

                            <div class="space-y-2">
                                <label for="company_email" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Support Endpoint Email</label>
                                <input id="company_email" v-model="form.company_email" type="email" 
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" />
                                <p v-if="form.errors.company_email" class="text-xs font-bold text-rose-500 mt-2 ml-1 italic">{{ form.errors.company_email }}</p>
                            </div>

                            <div class="space-y-2">
                                <label for="company_phone" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Contact Phone</label>
                                <input id="company_phone" v-model="form.company_phone" type="text" 
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" />
                            </div>

                            <div class="space-y-2">
                                <label for="company_address" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Physical Headquarters</label>
                                <input id="company_address" v-model="form.company_address" type="text" 
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-8 lg:p-10 flex items-center justify-between gap-6 bg-slate-50/50">
                        <div class="hidden sm:flex items-center gap-3 text-slate-400">
                             <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                             <p class="text-xs font-bold uppercase tracking-widest italic leading-tight max-w-[200px]">Sensitive changes may require a system cache clear.</p>
                        </div>
                        <div class="flex items-center gap-4 w-full sm:w-auto">
                            <Link :href="route('admin.settings.index')" 
                                class="flex-1 sm:flex-none text-center px-8 py-4 rounded-2xl bg-white border border-slate-200 text-sm font-black text-slate-600 hover:bg-slate-50 transition-all active:scale-95 shadow-sm">
                                Discard
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-10 py-4 rounded-2xl bg-slate-900 text-white text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50 active:scale-95">
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>{{ form.processing ? 'Persisting...' : 'Save Configuration' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Enhanced Footer Info -->
            <div class="bg-blue-600/5 border border-blue-600/10 rounded-2xl p-8 stagger-3">
                <div class="flex items-start gap-5">
                    <div class="h-12 w-12 rounded-xl bg-blue-600/10 flex items-center justify-center text-blue-600 shrink-0">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-sm font-black text-blue-900 uppercase tracking-tight">Configuration Guide</h4>
                        <p class="text-sm text-blue-800/70 font-medium leading-relaxed italic">
                            The Application URL must match your primary domain to ensure OAuth and link sharing work correctly. 
                            Timezone changes affect ticket timestamps and SLA calculation cycles.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>

<style scoped>
/* Inherited animations from AdminNavigation.vue */
</style>