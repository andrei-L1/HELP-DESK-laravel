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
        color: 'bg-slate-100 text-slate-600',
        stats: []
    },
    {
        name: 'Ticket Settings',
        description: 'Configure ticket statuses, priorities, categories and default values',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        href: route('admin.settings.ticket'),
        color: 'bg-blue-100 text-blue-600',
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
        color: 'bg-green-100 text-green-600',
        stats: []
    },
    {
        name: 'SLA Policies',
        description: 'Manage Service Level Agreements for ticket response and resolution times',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        href: route('admin.settings.sla'),
        color: 'bg-purple-100 text-purple-600',
        stats: [
            { name: 'Active Policies', count: props.stats.total_sla_policies }
        ]
    },
    {
        name: 'Departments',
        description: 'Manage departments and assignments',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        href: route('admin.departments.index'),
        color: 'bg-amber-100 text-amber-600',
        stats: [
            { name: 'Departments', count: props.stats.total_departments }
        ]
    },
    {
        name: 'User Roles',
        description: 'Manage roles and permissions',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        href: route('admin.users.roles'),
        color: 'bg-indigo-100 text-indigo-600',
        stats: [
            { name: 'Roles', count: props.stats.total_roles }
        ]
    }
];
</script>

<template>
    <Head title="Settings" />

    <AdminNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Settings</h1>
        </template>

        <div class="p-6">
            <!-- Welcome Card -->
            <div class="mb-6 rounded-lg bg-gradient-to-r from-slate-700 to-slate-900 p-6 text-white shadow-lg">
                <h2 class="text-2xl font-bold mb-2">System Settings</h2>
                <p class="text-slate-300">
                    Configure your helpdesk system settings, ticket workflows, email notifications, and more.
                </p>
            </div>

            <!-- Settings Categories Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="category in settingsCategories"
                    :key="category.name"
                    class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-slate-300"
                >
                    <Link :href="category.href" class="block">
                        <!-- Icon -->
                        <div class="mb-4 flex items-center justify-between">
                            <div :class="['inline-flex rounded-lg p-3', category.color]">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="category.icon" />
                                </svg>
                            </div>
                            <svg class="h-5 w-5 text-gray-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                        <!-- Content -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ category.name }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ category.description }}</p>

                        <!-- Stats -->
                        <div v-if="category.stats.length > 0" class="flex gap-3">
                            <div
                                v-for="stat in category.stats"
                                :key="stat.name"
                                class="flex items-center gap-1"
                            >
                                <span class="text-xs font-medium text-gray-500">{{ stat.name }}:</span>
                                <span class="text-sm font-semibold text-gray-900">{{ stat.count }}</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('admin.settings.general')"
                        class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                        General Settings
                    </Link>
                    <Link
                        :href="route('admin.settings.ticket')"
                        class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ticket Settings
                    </Link>
                    <Link
                        :href="route('admin.settings.email')"
                        class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Email Settings
                    </Link>
                    <Link
                        :href="route('admin.settings.sla')"
                        class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                    >
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        SLA Policies
                    </Link>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>