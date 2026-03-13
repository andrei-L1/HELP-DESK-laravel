<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';

const props = defineProps({
    tickets: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] },
    priorities: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const search   = ref(props.filters.search ?? '');
const status   = ref(props.filters.status ?? '');
const priority = ref(props.filters.priority ?? '');

let searchTimer = null;
watch(search, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(), 400);
});
watch([status, priority], () => applyFilters());

function applyFilters() {
    router.get(route('user.tickets.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        priority: priority.value || undefined,
    }, { preserveScroll: true, preserveState: true, replace: true });
}

function clearFilters() {
    search.value   = '';
    status.value   = '';
    priority.value = '';
    applyFilters();
}

function statusClass(s) {
    const map = {
        open: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        'in progress': 'bg-blue-100 text-blue-800',
        resolved: 'bg-purple-100 text-purple-800',
        closed: 'bg-gray-100 text-gray-700',
    };
    return map[s?.toLowerCase()] ?? 'bg-slate-100 text-slate-700';
}

function priorityClass(p) {
    const map = {
        urgent: 'bg-red-100 text-red-800',
        high: 'bg-orange-100 text-orange-800',
        medium: 'bg-yellow-100 text-yellow-800',
        low: 'bg-green-100 text-green-800',
    };
    return map[p?.toLowerCase()] ?? 'bg-slate-100 text-slate-700';
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="My Tickets" />

    <UserNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">My Tickets</h1>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Stats Bar -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div v-for="(label, key) in { total: 'Total', open: 'Open', pending: 'Pending', resolved: 'Resolved', closed: 'Closed' }"
                         :key="key"
                         class="bg-white rounded-xl shadow-sm p-4 text-center border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ stats[key] ?? 0 }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">{{ label }}</p>
                    </div>
                </div>

                <!-- Filters + Create Button -->
                <div class="flex flex-wrap gap-3 items-center">
                    <!-- Search -->
                    <div class="relative flex-1 min-w-48">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search tickets…"
                            class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>

                    <!-- Status -->
                    <select v-model="status" class="rounded-lg border border-gray-300 text-sm py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>

                    <!-- Priority -->
                    <select v-model="priority" class="rounded-lg border border-gray-300 text-sm py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Priorities</option>
                        <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
                    </select>

                    <!-- Clear -->
                    <button
                        v-if="search || status || priority"
                        @click="clearFilters"
                        class="text-sm text-gray-500 hover:text-gray-700 underline"
                    >Clear</button>

                    <div class="ml-auto">
                        <Link
                            :href="route('user.tickets.create')"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Ticket
                        </Link>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div v-if="tickets.data.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ticket</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Updated</th>
                                    <th class="relative py-3.5 pl-3 pr-6"><span class="sr-only">View</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50 transition-colors group">
                                    <td class="py-4 pl-6 pr-3">
                                        <p class="text-sm font-semibold text-gray-900">{{ ticket.ticket_number }}</p>
                                        <p class="text-sm text-gray-500 max-w-xs truncate mt-0.5">{{ ticket.subject }}</p>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="statusClass(ticket.status)">
                                            {{ ticket.status }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="priorityClass(ticket.priority)">
                                            {{ ticket.priority }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ formatDate(ticket.created_at) }}</td>
                                    <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ formatDate(ticket.updated_at) }}</td>
                                    <td class="py-4 pl-3 pr-6 text-right">
                                        <Link
                                            :href="route('user.tickets.show', ticket.id)"
                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity"
                                        >View →</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="tickets.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
                            <p class="text-sm text-gray-500">
                                Showing {{ tickets.from }}–{{ tickets.to }} of {{ tickets.total }} tickets
                            </p>
                            <div class="flex gap-2">
                                <Link
                                    v-if="tickets.prev_page_url"
                                    :href="tickets.prev_page_url"
                                    class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                                >← Prev</Link>
                                <Link
                                    v-if="tickets.next_page_url"
                                    :href="tickets.next_page_url"
                                    class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                                >Next →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                        <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-semibold text-gray-900">No tickets found</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <span v-if="search || status || priority">Try adjusting your filters.</span>
                            <span v-else>Submit your first support request to get started.</span>
                        </p>
                        <Link
                            v-if="!search && !status && !priority"
                            :href="route('user.tickets.create')"
                            class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Ticket
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </UserNavigation>
</template>
