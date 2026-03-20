<script setup>
import UserNavigation from '@/Components/UserNavigation.vue';
import { Head } from '@inertiajs/vue3';
import UpdateNotificationSettingsForm from '../Profile/Partials/UpdateNotificationSettingsForm.vue';

defineProps({
    status: String,
});
</script>

<template>
    <Head title="Settings" />

    <UserNavigation>
        <template #header-title>
            <div class="flex flex-col">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                    <span>Account</span>
                    <span>/</span>
                    <span class="text-slate-900">Settings</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Personal Workspace Settings</h1>
            </div>
        </template>

        <div class="max-w-4xl mx-auto pb-20 pt-4 stagger-1">
            <div class="bg-white shadow-xl shadow-slate-200/50 rounded-[2.5rem] border border-slate-200/50 overflow-hidden transition-all hover:shadow-2xl stagger-2">
                <UpdateNotificationSettingsForm />
            </div>
        </div>
    </UserNavigation>
</template>
