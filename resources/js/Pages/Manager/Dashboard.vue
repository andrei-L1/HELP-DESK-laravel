<!-- resources/js/Pages/Manager/Dashboard.vue -->
<script setup>
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_team_tickets: 0,
            pending_review: 0,
            team_members: 0,
            avg_response_time: '0h',
            tickets_by_status: [],
            tickets_by_priority: [],
        }),
    },
    recent_tickets: {
        type: Array,
        default: () => [],
    },
    team_performance: {
        type: Array,
        default: () => [],
    },
    tickets_by_status: {
        type: Array,
        default: () => [],
    },
});

// Check permissions
const hasPermission = (permission) => {
    const userPermissions = page.props.auth?.user?.permissions || [];
    return userPermissions.includes(permission);
};

const canEditTickets = computed(() => hasPermission('edit_ticket'));
const canViewTickets = computed(() => hasPermission('view_ticket'));
const canManageUsers = computed(() => hasPermission('manage_users'));

// Navigation actions
const viewTeamTickets = () => {
    router.visit(route('manager.tickets.team'));
};

const viewPendingReviews = () => {
    router.visit(route('manager.tickets.pending'));
};

const viewTeamMembers = () => {
    router.visit(route('manager.team.members'));
};

const assignTickets = () => {
    router.visit(route('manager.tickets.assign'));
};

const viewTicket = (ticketId) => {
    router.visit(route('manager.tickets.show', ticketId));
};

const viewAllTickets = () => {
    router.visit(route('manager.tickets.index'));
};

const viewReports = () => {
    router.visit(route('manager.reports.index'));
};

// Get status badge color
const getStatusColor = (status) => {
    const colors = {
        'open': 'bg-blue-100 text-blue-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
        'urgent': 'bg-red-100 text-red-800',
    };
    return colors[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

// Get priority badge color
const getPriorityColor = (priority) => {
    const colors = {
        'high': 'bg-red-100 text-red-800',
        'urgent': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800',
    };
    return colors[priority?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Manager Dashboard" />
    <ManagerNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Team Dashboard</h1>
        </template>

        <div class="p-6">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900">
                    Welcome back, {{ page.props.auth.user.first_name }} {{ page.props.auth.user.last_name }}!
                </h2>
                <p class="mt-2 text-gray-600">
                    Here's an overview of your team's performance and tickets.
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Team Tickets -->
                <button
                    @click="viewTeamTickets"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Team Tickets</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_team_tickets }}</p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100"
                        >
                            <svg
                                class="h-6 w-6 text-emerald-600"
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

                <!-- Pending Review -->
                <button
                    v-if="canEditTickets"
                    @click="viewPendingReviews"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-yellow-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pending Review</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.pending_review }}</p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100"
                        >
                            <svg
                                class="h-6 w-6 text-yellow-600"
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

                <!-- Team Members -->
                <button
                    v-if="canManageUsers"
                    @click="viewTeamMembers"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-blue-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Team Members</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.team_members }}</p>
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
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Avg Response Time -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Avg Response Time</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.avg_response_time }}</p>
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
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets by Status -->
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

            <!-- Team Performance -->
            <div v-if="team_performance.length > 0" class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Team Performance</h3>
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Agent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Assigned</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Resolved</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pending</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Avg Response</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="agent in team_performance" :key="agent.id">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 flex-shrink-0 rounded-full bg-emerald-100 flex items-center justify-center">
                                            <span class="text-xs font-medium text-emerald-800">
                                                {{ agent.name?.charAt(0) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ agent.name }}</div>
                                            <div class="text-sm text-gray-500">{{ agent.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ agent.assigned }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ agent.resolved }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ agent.pending }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ agent.avg_response }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <!-- Assign Tickets -->
                    <button
                        v-if="canEditTickets"
                        @click="assignTickets"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100"
                        >
                            <svg
                                class="h-5 w-5 text-emerald-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                                />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Assign Tickets</p>
                            <p class="text-sm text-gray-500">Distribute tickets to team members</p>
                        </div>
                    </button>

                    <!-- Review Tickets -->
                    <button
                        v-if="canEditTickets"
                        @click="viewPendingReviews"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100"
                        >
                            <svg
                                class="h-5 w-5 text-emerald-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Review Tickets</p>
                            <p class="text-sm text-gray-500">Review and approve pending tickets</p>
                        </div>
                    </button>

                    <!-- Team Overview -->
                    <button
                        v-if="canManageUsers"
                        @click="viewTeamMembers"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100"
                        >
                            <svg
                                class="h-5 w-5 text-emerald-600"
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
                        <div>
                            <p class="font-medium text-gray-900">Team Overview</p>
                            <p class="text-sm text-gray-500">View team members and performance</p>
                        </div>
                    </button>

                    <!-- View Reports -->
                    <button
                        @click="viewReports"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100"
                        >
                            <svg
                                class="h-5 w-5 text-emerald-600"
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
                            <p class="text-sm text-gray-500">Team analytics and insights</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Recent Tickets Table -->
            <div class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Team Tickets</h3>
                    <button
                        @click="viewAllTickets"
                        class="text-sm font-medium text-emerald-700 hover:text-emerald-900"
                    >
                        View All →
                    </button>
                </div>

                <div v-if="recent_tickets.length > 0" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ticket #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Assigned To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="ticket in recent_tickets"
                                :key="ticket.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ ticket.ticket_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ ticket.subject }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="getStatusColor(ticket.status)"
                                    >
                                        {{ ticket.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="getPriorityColor(ticket.priority)"
                                    >
                                        {{ ticket.priority }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ ticket.assigned_to || 'Unassigned' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ ticket.created_at }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <button
                                        @click="viewTicket(ticket.id)"
                                        class="text-emerald-600 hover:text-emerald-900"
                                    >
                                        View
                                    </button>
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
                        Your team hasn't received any tickets yet.
                    </p>
                </div>
            </div>
        </div>
    </ManagerNavigation>
</template>
