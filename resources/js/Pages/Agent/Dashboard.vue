<!-- resources/js/Pages/Agent/Dashboard.vue -->
<script setup>
import AgentNavigation from '@/Components/AgentNavigation.vue'; // ← assuming you created AgentLayout or renamed to AgentNavigation
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            my_open_tickets: 0,
            my_pending_tickets: 0,
            my_resolved_tickets: 0,
            total_my_tickets: 0,
            avg_response_time: '0h',
        }),
    },
    recent_tickets: {
        type: Array,
        default: () => [],
    },
});

// Navigation / redirect helpers
const viewMyTickets = (status = null) => {
    const query = status ? { status } : {};
    router.visit(route('agent.tickets.index', query));
};

const createNewTicket = () => {
    router.visit(route('agent.tickets.create'));
};

const viewTicket = (ticketId) => {
    router.visit(route('agent.tickets.show', ticketId));
};

const viewAllMyTickets = () => {
    router.visit(route('agent.tickets.index'));
};

const viewKnowledgeBase = () => {
    router.visit(route('agent.kb.index')); // adjust route name if different
};

// Status & priority badge helpers (same as manager)
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
    <Head title="Agent Dashboard" />

    <AgentNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">My Dashboard</h1>
        </template>

        <div class="p-6">
            <!-- Welcome -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900">
                    Hi {{ page.props.auth.user.first_name }}!
                </h2>
                <p class="mt-2 text-gray-600">
                    Here's a quick overview of your assigned tickets and activity.
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total My Tickets -->
                <button
                    @click="viewMyTickets"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">My Tickets</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_my_tickets }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Open Tickets -->
                <button
                    @click="viewMyTickets('open')"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-blue-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Open</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.my_open_tickets }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Pending Tickets -->
                <button
                    @click="viewMyTickets('pending')"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-left shadow-sm transition-all hover:border-yellow-300 hover:shadow-md cursor-pointer"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pending Reply</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.my_pending_tickets }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <button
                        @click="createNewTicket"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">New Ticket</p>
                            <p class="text-sm text-gray-500">Create a new support request</p>
                        </div>
                    </button>

                    <button
                        @click="viewMyTickets"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">My Tickets</p>
                            <p class="text-sm text-gray-500">View all assigned tickets</p>
                        </div>
                    </button>

                    <button
                        @click="viewKnowledgeBase"
                        class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-emerald-300 hover:shadow-md"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Knowledge Base</p>
                            <p class="text-sm text-gray-500">Search solutions & articles</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Recent / Assigned Tickets -->
            <div class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent / Assigned Tickets</h3>
                    <button
                        @click="viewAllMyTickets"
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
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="ticket in recent_tickets" :key="ticket.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ ticket.ticket_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ ticket.subject }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getStatusColor(ticket.status)">
                                        {{ ticket.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getPriorityColor(ticket.priority)">
                                        {{ ticket.priority }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ ticket.created_at }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <button @click="viewTicket(ticket.id)" class="text-emerald-600 hover:text-emerald-900">
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">No tickets assigned yet</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        When new tickets are assigned to you, they will appear here.
                    </p>
                </div>
            </div>
        </div>
    </AgentNavigation>
</template>