<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import UpdateNotificationSettingsForm from '../../Profile/Partials/UpdateNotificationSettingsForm.vue';

defineProps({
    status: String,
});
</script>

<template>
    <Head title="Notification Settings" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex flex-col">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                    <Link :href="route('admin.settings.index')" class="hover:text-slate-900 transition-colors">Settings</Link>
                    <span>/</span>
                    <span class="text-slate-900">Notifications</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Notification Center</h1>
            </div>
        </template>

        <div class="max-w-4xl mx-auto pb-20 pt-4 stagger-1">
            <div class="flex items-center justify-between px-2 mb-8">
                <Link :href="route('admin.settings.index')" class="group inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-slate-900 transition-all">
                    <div class="h-8 w-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center group-hover:border-slate-300 shadow-sm transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                    </div>
                    Back to Overview
                </Link>
            </div>

            <div class="bg-white shadow-xl shadow-slate-200/50 rounded-[2.5rem] border border-slate-200/50 overflow-hidden transition-all hover:shadow-2xl stagger-2">
                <UpdateNotificationSettingsForm />
            </div>
        </div>
    </AdminNavigation>
</template>
