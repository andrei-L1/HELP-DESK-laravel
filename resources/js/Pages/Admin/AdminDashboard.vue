<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_tickets: 0,
            open_tickets: 0,
            active_users: 0,
            total_departments: 0,
        }),
    },
    recent_tickets: {
        type: Array,
        default: () => [],
    },
    tickets_by_status: {
        type: Array,
        default: () => [],
    },
});

const viewAllTickets = () => {
    router.visit(route('admin.tickets.index'));
};

const createTicket = () => {
    // Navigate to create ticket page when route is created
    alert('Create ticket page coming soon!');
};

const addUser = () => {
    // Navigate to add user page when route is created
    alert('Add user page coming soon!');
};

const viewReports = () => {
    // Navigate to reports page when route is created
    alert('Reports page coming soon!');
};

const viewTicket = (ticketId) => {
    router.visit(route('admin.tickets.show', ticketId));
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
        </template>

        <div class="p-6">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900">Welcome back, Administrator!</h2>
                <p class="mt-2 text-gray-600">
                    Here's an overview of your help desk system.
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Tickets -->
                <button
                    @click="viewAllTickets"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-slate-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Tickets</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_tickets }}</p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100"
                        >
                            <svg
                                class="h-6 w-6 text-slate-700"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Open Tickets -->
                <button
                    @click="viewAllTickets"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-blue-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Open Tickets</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.open_tickets }}</p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100"
                        >
                            <svg
                                class="h-6 w-6 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Active Users -->
                <button
                    @click="addUser"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-green-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Active Users</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.active_users }}</p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100"
                        >
                            <svg
                                class="h-6 w-6 text-green-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Departments -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Departments</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_departments }}</p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100"
                        >
                            <svg
                                class="h-6 w-6 text-purple-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets by Status Chart -->
            <div v-if="tickets_by_status.length > 0" class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tickets by Status</h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="status in tickets_by_status"
                        :key="status.name"
                        class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ status.name }}</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">{{ status.count }}</p>
                            </div>
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg"
                                :class="{
                                    'bg-blue-100': status.name?.toLowerCase() === 'open',
                                    'bg-green-100': status.name?.toLowerCase() === 'resolved',
                                    'bg-gray-100': status.name?.toLowerCase() === 'closed',
                                    'bg-yellow-100': status.name?.toLowerCase() === 'pending',
                                    'bg-red-100': status.name?.toLowerCase() === 'urgent',
                                }"
                            >
                                <svg
                                    class="h-5 w-5"
                                    :class="{
                                        'text-blue-600': status.name?.toLowerCase() === 'open',
                                        'text-green-600': status.name?.toLowerCase() === 'resolved',
                                        'text-gray-600': status.name?.toLowerCase() === 'closed',
                                        'text-yellow-600': status.name?.toLowerCase() === 'pending',
                                        'text-red-600': status.name?.toLowerCase() === 'urgent',
                                    }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <button
                        @click="createTicket"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-slate-300 hover:shadow-md"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100"
                        >
                            <svg
                                class="h-5 w-5 text-slate-700"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Create Ticket</p>
                            <p class="text-sm text-gray-500">Create a new support ticket</p>
                        </div>
                    </button>

                    <button
                        @click="addUser"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-slate-300 hover:shadow-md"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100"
                        >
                            <svg
                                class="h-5 w-5 text-slate-700"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                                />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Add User</p>
                            <p class="text-sm text-gray-500">Create a new user account</p>
                        </div>
                    </button>

                    <button
                        @click="viewReports"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-slate-300 hover:shadow-md"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100"
                        >
                            <svg
                                class="h-5 w-5 text-slate-700"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">View Reports</p>
                            <p class="text-sm text-gray-500">Analytics and insights</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Recent Tickets Table -->
            <div class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Tickets</h3>
                    <button
                        @click="viewAllTickets"
                        class="text-sm font-medium text-slate-700 hover:text-slate-900"
                    >
                        View All →
                    </button>
                </div>

                <div v-if="recent_tickets.length > 0" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Ticket #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Subject
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Priority
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Created By
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Assigned To
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="ticket in recent_tickets"
                                :key="ticket.id"
                                @click="viewTicket(ticket.id)"
                                class="cursor-pointer hover:bg-gray-50 transition-colors"
                            >
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ ticket.ticket_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ ticket.subject }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800': ticket.status?.toLowerCase() === 'open',
                                            'bg-green-100 text-green-800': ticket.status?.toLowerCase() === 'resolved',
                                            'bg-gray-100 text-gray-800': ticket.status?.toLowerCase() === 'closed',
                                            'bg-yellow-100 text-yellow-800': ticket.status?.toLowerCase() === 'pending',
                                            'bg-red-100 text-red-800': ticket.status?.toLowerCase() === 'urgent',
                                        }"
                                    >
                                        {{ ticket.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="{
                                            'bg-red-100 text-red-800': ticket.priority?.toLowerCase() === 'high' || ticket.priority?.toLowerCase() === 'urgent',
                                            'bg-yellow-100 text-yellow-800': ticket.priority?.toLowerCase() === 'medium',
                                            'bg-green-100 text-green-800': ticket.priority?.toLowerCase() === 'low',
                                        }"
                                    >
                                        {{ ticket.priority }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ ticket.created_by }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ ticket.assigned_to }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ ticket.created_at }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">No tickets yet</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Get started by creating your first support ticket.
                    </p>
                    <div class="mt-6">
                        <button
                            @click="createTicket"
                            class="inline-flex items-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        >
                            <svg
                                class="-ml-1 mr-2 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Create Ticket
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>
