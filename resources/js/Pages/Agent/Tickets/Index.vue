<script setup>
import AgentNavigation from '@/Components/AgentNavigation.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    tickets: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            priority: '',
        }),
    },
    currentStatus: {
        type: String,
        default: 'All',
    }
});

const page = usePage();

// Filter state
const searchInput = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || (props.currentStatus && props.currentStatus !== 'All' ? props.currentStatus : ''));
const priorityFilter = ref(props.filters.priority || '');

// Statuses and Priorities options (hardcoded for quick filtering if not passed from backend)
const availableStatuses = ['Open', 'Pending', 'Resolved', 'Closed'];
const availablePriorities = ['Low', 'Medium', 'High', 'Urgent'];

// Real-time synchronization
const localTickets = ref([...(props.tickets.data || [])]);

watch(() => props.tickets.data, (newVal) => {
    localTickets.value = [...(newVal || [])];
}, { deep: true });

onMounted(() => {
    if (window.Echo) {
        window.Echo.private('staff.tickets')
            .listen('.TicketCreated', (e) => {
                console.log('[Echo] New ticket created:', e.ticket);
                
                // Only show if it belongs to this agent's view (assigned to them)
                if (e.ticket.assigned_to_id !== page.props.auth.user.id) return;

                // Check if it matches current status filter
                if (statusFilter.value && e.ticket.status !== statusFilter.value) return;
                
                // Check if it matches current priority filter
                if (priorityFilter.value && e.ticket.priority !== priorityFilter.value) return;

                // Only prepend if we are on the first page
                const isFirstPage = !props.tickets.prev_page_url;
                if (isFirstPage && !localTickets.value.find(t => t.id === e.ticket.id)) {
                    localTickets.value.unshift(e.ticket);
                    if (localTickets.value.length > (props.tickets.per_page || 15)) {
                        localTickets.value.pop();
                    }
                }
            })
            .listen('.TicketUpdated', (e) => {
                console.log('[Echo] Ticket updated:', e.ticket);
                const index = localTickets.value.findIndex(t => t.id === e.ticket.id);
                
                // If it's in our list, check if it still belongs there
                if (index !== -1) {
                    const stillMatches = (
                        e.ticket.assigned_to_id === page.props.auth.user.id &&
                        (!statusFilter.value || e.ticket.status === statusFilter.value) &&
                        (!priorityFilter.value || e.ticket.priority === priorityFilter.value)
                    );

                    if (stillMatches) {
                        localTickets.value[index] = { ...localTickets.value[index], ...e.ticket };
                    } else {
                        // No longer matches filters (e.g. status changed), remove it
                        localTickets.value.splice(index, 1);
                    }
                } else {
                    // Not in list, check if it SHOULD be now (e.g. assigned to me or status changed to match filter)
                    const matchesNow = (
                        e.ticket.assigned_to_id === page.props.auth.user.id &&
                        (!statusFilter.value || e.ticket.status === statusFilter.value) &&
                        (!priorityFilter.value || e.ticket.priority === priorityFilter.value)
                    );

                    if (matchesNow && !props.tickets.prev_page_url) {
                        localTickets.value.unshift(e.ticket);
                        if (localTickets.value.length > (props.tickets.per_page || 15)) {
                            localTickets.value.pop();
                        }
                    }
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('staff.tickets');
    }
});

// Apply filters
const applyFilter = (key, value) => {
    // If we're on a specific status route like /agent/tickets/open, we might want to stay there
    // but the easiest approach for search/filtering is to use the main index route
    let routeName = 'agent.tickets.index';
    
    // Maintain other active filters
    const currentFilters = {
        search: key === 'search' ? value : searchInput.value,
        status: key === 'status' ? value : statusFilter.value,
        priority: key === 'priority' ? value : priorityFilter.value,
    };

    // Remove empty ones
    Object.keys(currentFilters).forEach(k => {
        if (!currentFilters[k]) delete currentFilters[k];
    });

    router.get(
        route(routeName),
        { ...currentFilters, page: 1 },
        { preserveState: true, preserveScroll: true }
    );
};

const resetFilters = () => {
    searchInput.value = '';
    statusFilter.value = '';
    priorityFilter.value = '';
    
    router.get(
        route('agent.tickets.index'),
        {},
        { preserveState: false, preserveScroll: true }
    );
};

const viewTicket = (id) => {
    router.visit(route('agent.tickets.show', id));
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString();
};

const getPriorityClass = (priority) => {
    const map = {
        'urgent': 'bg-red-100 text-red-800',
        'high': 'bg-orange-100 text-orange-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800',
    };
    return map[priority?.name?.toLowerCase() || priority?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const getStatusClass = (status) => {
    const map = {
        'open': 'bg-blue-100 text-blue-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
    };
    return map[status?.name?.toLowerCase() || status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
</style>

<template>
    <Head title="My Tickets" />

    <AgentNavigation>
        <template #header-title>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">
                    <span v-if="currentStatus && currentStatus !== 'All'">{{ currentStatus }} Tickets</span>
                    <span v-else>My Assigned Tickets</span>
                </h1>
                <Link
                    :href="route('agent.tickets.create')"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700"
                >
                    <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Ticket
                </Link>
            </div>
        </template>

        <div class="p-6 space-y-6">
            
            <!-- Filters -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Search</label>
                        <div class="mt-1 relative">
                            <input
                                type="text"
                                v-model="searchInput"
                                @keyup.enter="applyFilter('search', searchInput)"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 pr-10"
                                placeholder="Ticket # or subject"
                            />
                            <button
                                @click="applyFilter('search', searchInput)"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div v-if="!currentStatus || currentStatus === 'All'">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select
                            v-model="statusFilter"
                            @change="applyFilter('status', $event.target.value)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">All Statuses</option>
                            <option v-for="status in availableStatuses" :key="status" :value="status">
                                {{ status }}
                            </option>
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority</label>
                        <select
                            v-model="priorityFilter"
                            @change="applyFilter('priority', $event.target.value)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">All Priorities</option>
                            <option v-for="priority in availablePriorities" :key="priority" :value="priority">
                                {{ priority }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 flex justify-end">
                    <button
                        @click="resetFilters"
                        class="text-sm text-gray-600 hover:text-emerald-700 font-medium"
                    >
                        Reset Filters
                    </button>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ticket #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Updated</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="ticket in localTickets"
                            :key="ticket.id"
                            class="cursor-pointer hover:bg-gray-50 transition-colors animate-fade-in"
                            @click="viewTicket(ticket.id)"
                        >
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                {{ ticket.ticket_number }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                {{ ticket.subject }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ ticket.creator ? (ticket.creator.first_name + ' ' + ticket.creator.last_name) : 'System' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="getStatusClass(ticket.status)"
                                >
                                    {{ ticket.status || 'Open' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="getPriorityClass(ticket.priority)"
                                >
                                    {{ ticket.priority || 'Low' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ formatDate(ticket.updated_at) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-right">
                                <Link
                                    :href="route('agent.tickets.show', ticket.id)"
                                    class="text-emerald-600 hover:text-emerald-900 font-medium"
                                    @click.stop
                                >
                                    View
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="tickets.data && tickets.data.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                No tickets found matching your criteria.
                                <br>
                                <Link
                                    :href="route('agent.tickets.index')"
                                    class="mt-2 inline-block text-emerald-600 hover:text-emerald-800 font-medium underline"
                                    v-if="filters.search || filters.status || filters.priority || currentStatus !== 'All'"
                                >
                                    Clear filters
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="tickets.links && tickets.links.length > 3"
                class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm sm:px-6 rounded-lg"
            >
                <div class="hidden sm:block">
                    <p>
                        Showing
                        <span class="font-medium">{{ tickets.from || 0 }}</span>
                        to
                        <span class="font-medium">{{ tickets.to || 0 }}</span>
                        of
                        <span class="font-medium">{{ tickets.total || 0 }}</span>
                        results
                    </p>
                </div>
                <div class="flex flex-1 justify-between sm:justify-end gap-1">
                    <Link
                        v-for="link in tickets.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium"
                        :class="[
                            link.active
                                ? 'bg-emerald-600 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                            !link.url ? 'cursor-default opacity-50' : '',
                        ]"
                    />
                </div>
            </div>
        </div>
    </AgentNavigation>
</template>
