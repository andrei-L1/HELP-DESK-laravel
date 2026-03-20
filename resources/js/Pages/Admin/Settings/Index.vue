<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_ticket_statuses: 0,
            total_ticket_priorities: 0,
            total_ticket_categories: 0,
            total_sla_policies: 0,
            total_departments: 0,
            total_roles: 0,
        })
    }
});

const settingsCategories = [
    {
        name: 'General',
        description: 'Application name, URL, timezone, and company information',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        href: route('admin.settings.general'),
        color: 'slate',
        stats: []
    },
    {
        name: 'Ticket Settings',
        description: 'Configure ticket statuses, priorities, categories and default values',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        href: route('admin.settings.ticket'),
        color: 'blue',
        stats: [
            { name: 'Statuses', count: props.stats.total_ticket_statuses },
            { name: 'Priorities', count: props.stats.total_ticket_priorities },
            { name: 'Categories', count: props.stats.total_ticket_categories },
        ]
    },
    {
        name: 'Email Settings',
        description: 'Configure SMTP, email templates, and notification settings',
        icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        href: route('admin.settings.email'),
        color: 'emerald',
        stats: []
    },
    {
        name: 'SLA Policies',
        description: 'Manage Service Level Agreements for ticket response and resolution times',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        href: route('admin.settings.sla'),
        color: 'violet',
        stats: [
            { name: 'Active Policies', count: props.stats.total_sla_policies }
        ]
    },
    {
        name: 'Departments',
        description: 'Manage departments and assignments',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        href: route('admin.departments.index'),
        color: 'amber',
        stats: [
            { name: 'Departments', count: props.stats.total_departments }
        ]
    },
    {
        name: 'Notifications',
        description: 'Manage how and when you receive alerts and updates',
        icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        href: route('admin.settings.notifications'),
        color: 'rose',
        stats: []
    },
    {
        name: 'User Roles',
        description: 'Manage roles and permissions',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        href: route('admin.users.roles'),
        color: 'indigo',
        stats: [
            { name: 'Roles', count: props.stats.total_roles }
        ]
    }
];
</script>

<template>
    <Head title="System Settings" />

    <AdminNavigation>
        <template #header-title>
             <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">System Settings</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-12 pb-20 pt-4">
            <!-- New Minimalist Welcome -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2 stagger-1">
                <div class="space-y-1">
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Configuration Center</h2>
                    <p class="text-slate-600 font-medium font-serif italic">Fine-tune your helpdesk experience and system behavior.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Backup Settings
                    </button>
                </div>
            </div>

            <!-- Settings Categories Grid -->
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 px-1 stagger-2">
                <div
                    v-for="category in settingsCategories"
                    :key="category.name"
                    class="group relative bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 hover:shadow-xl hover:shadow-slate-200/50 hover:border-slate-400/40 transition-all duration-300 overflow-hidden"
                >
                    <Link :href="category.href" class="block p-8">
                        <!-- Icon & Arrow -->
                        <div class="mb-6 flex items-center justify-between">
                            <div :class="{
                                'bg-slate-100 text-slate-600': category.color === 'slate',
                                'bg-blue-100 text-blue-600': category.color === 'blue',
                                'bg-emerald-100 text-emerald-600': category.color === 'emerald',
                                'bg-rose-100 text-rose-600': category.color === 'rose',
                                'bg-amber-100 text-amber-600': category.color === 'amber',
                                'bg-indigo-100 text-indigo-600': category.color === 'indigo',
                                'bg-violet-100 text-violet-600': category.color === 'violet',
                            }" class="h-12 w-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300 shadow-sm">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="category.icon" />
                                </svg>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center opacity-0 group-hover:opacity-100 group-hover:translate-x-0 -translate-x-4 transition-all duration-300">
                                <svg class="h-4 w-4 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:text-slate-500 transition-colors">Core Module</h4>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ category.name }}</h3>
                            <p class="text-sm text-slate-500 font-medium leading-relaxed">{{ category.description }}</p>
                        </div>

                        <!-- Divider & Stats -->
                        <div v-if="category.stats.length > 0" class="mt-8 pt-6 border-t border-slate-50">
                            <div class="flex flex-wrap gap-4">
                                <div
                                    v-for="stat in category.stats"
                                    :key="stat.name"
                                    class="flex items-center gap-2"
                                >
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ stat.name }}</span>
                                    <span class="text-sm font-bold text-slate-900 bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100">{{ stat.count }}</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="stagger-3 px-1">
                <div class="bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 p-8">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="h-8 w-1.5 rounded-full bg-slate-900"></div>
                        <h3 class="text-xl font-bold text-slate-900 tracking-tight">Quick Shortcuts</h3>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link
                            v-for="action in [
                                { name: 'General', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', href: route('admin.settings.general') },
                                { name: 'Tickets', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', href: route('admin.settings.ticket') },
                                { name: 'SMTP/Email', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', href: route('admin.settings.email') },
                                { name: 'SLA Policies', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', href: route('admin.settings.sla') },
                            ]"
                            :key="action.name"
                            :href="action.href"
                            class="flex flex-col items-center gap-3 p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-slate-300 hover:shadow-lg transition-all group"
                        >
                            <div class="h-10 w-10 flex items-center justify-center text-slate-400 group-hover:text-slate-900 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon" />
                                </svg>
                            </div>
                            <span class="text-[11px] font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-900">{{ action.name }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>

<style scoped>
/* Stagger animations inherited from AdminNavigation.vue */
</style>