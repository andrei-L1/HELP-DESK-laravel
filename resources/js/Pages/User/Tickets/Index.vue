<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';

const props = defineProps({
    tickets: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] },
    priorities: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const stats = ref({ ...props.stats });

const page = usePage();
const localTickets = ref([...(props.tickets.data || [])]);

watch(() => props.tickets.data, (newVal) => {
    localTickets.value = [...(newVal || [])];
}, { deep: true });

onMounted(() => {
    if (window.Echo) {
        window.Echo.private(`user.${page.props.auth.user.id}.tickets`)
            .listen('.TicketCreated', (e) => {
                const isFirstPage = !props.tickets.prev_page_url;
                if (isFirstPage && !localTickets.value.find(t => t.id === e.ticket.id)) {
                    localTickets.value.unshift(e.ticket);
                    // Update stats
                    stats.value.total++;
                    stats.value.open++;
                }
            })
            .listen('.TicketUpdated', (e) => {
                const index = localTickets.value.findIndex(t => t.id === e.ticket.id);
                if (index !== -1) {
                    localTickets.value[index] = { ...localTickets.value[index], ...e.ticket };
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`user.${page.props.auth.user.id}.tickets`);
    }
});
const hexToRgb = (hex) => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
};

// Dynamic status badge styles using color from database
const getStatusStyles = (status, colorHex) => {
    const color = colorHex || '#6b7280';
    const rgb = hexToRgb(color);
    
    return {
        badge: `inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset`,
        style: {
            backgroundColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.1)` : `${color}20`,
            color: color,
            borderColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.3)` : `${color}40`,
        }
    };
};

// Dynamic priority badge styles using color from database
const getPriorityStyles = (priority, colorHex) => {
    const color = colorHex || '#6b7280';
    const rgb = hexToRgb(color);
    
    return {
        badge: `inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset`,
        style: {
            backgroundColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.1)` : `${color}20`,
            color: color,
            borderColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.3)` : `${color}40`,
        }
    };
};

// Get status icon based on status name
const getStatusIcon = (status) => {
    const icons = {
        open: 'M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        pending: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'in progress': 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99',
        resolved: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        closed: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
    };
    return icons[status?.toLowerCase()] ?? icons.open;
};

// Get priority icon based on priority name
const getPriorityIcon = (priority) => {
    const icons = {
        urgent: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
        high: 'M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z',
        medium: 'M3 8.25V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V8.25m-18 0V6a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 6v2.25m-18 0h18M5.25 6h.008v.008H5.25V6zM7.5 6h.008v.008H7.5V6z',
        low: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    return icons[priority?.toLowerCase()] ?? icons.medium;
};

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

function formatDate(d) {
    if (!d) return '—';
    const date = new Date(d);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 1) {
        return `Today at ${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`;
    } else if (diffDays === 1) {
        return `Yesterday at ${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`;
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    }
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

const activeFilterCount = computed(() => {
    return [search.value, status.value, priority.value].filter(Boolean).length;
});
</script>

<template>
    <Head title="My Tickets" />

    <UserNavigation>
        <template #header-title>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">My Tickets</h1>
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                    {{ tickets.total }} total
                </span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Stats Bar with Icons -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div v-for="(item, key) in {
                        total: { label: 'Total Tickets', icon: 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z', color: 'text-gray-600' },
                        open: { label: 'Open', icon: 'M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z', color: 'text-emerald-600' },
                        pending: { label: 'Pending', icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-amber-600' },
                        resolved: { label: 'Resolved', icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-violet-600' },
                        closed: { label: 'Closed', icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', color: 'text-gray-600' }
                    }"
                         :key="key"
                         class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8" :class="item.color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ stats[key] ?? 0 }}</p>
                                <p class="text-xs font-medium text-gray-500 mt-0.5">{{ item.label }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex flex-wrap gap-4 items-center">
                        <!-- Search with enhanced design -->
                        <div class="relative flex-1 min-w-[280px]">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by ticket number or subject..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-shadow"
                            />
                            <button
                                v-if="search"
                                @click="search = ''"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Filter Badges -->
                        <div class="flex items-center gap-3">
                            <!-- Status Dropdown -->
                            <div class="relative">
                                <select v-model="status" class="appearance-none rounded-xl border border-gray-200 text-sm py-2.5 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white min-w-[140px]">
                                    <option value="">All Statuses</option>
                                    <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <!-- Priority Dropdown -->
                            <div class="relative">
                                <select v-model="priority" class="appearance-none rounded-xl border border-gray-200 text-sm py-2.5 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white min-w-[140px]">
                                    <option value="">All Priorities</option>
                                    <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <!-- Active Filters -->
                            <button
                                v-if="activeFilterCount > 0"
                                @click="clearFilters"
                                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 bg-gray-50 hover:bg-gray-100 px-3 py-2 rounded-xl transition-colors"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear filters
                                <span class="bg-white px-1.5 py-0.5 rounded-full text-xs font-medium text-gray-600">{{ activeFilterCount }}</span>
                            </button>
                        </div>

                        <!-- Create Button -->
                        <div class="ml-auto">
                            <Link
                                :href="route('user.tickets.create')"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all hover:scale-105 active:scale-100"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                New Ticket
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table/Card View -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Table View for larger screens -->
                    <div v-if="tickets.data.length > 0" class="hidden lg:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-4 pl-6 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ticket Details</th>
                                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Updated</th>
                                    <th class="relative py-4 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="ticket in localTickets" :key="ticket.id" class="hover:bg-gray-50/80 transition-colors group animate-fade-in">
                                    <td class="py-4 pl-6 pr-3">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-1">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9h8M8 13h6M9 18h6a3 3 0 003-3V9a3 3 0 00-3-3H9a3 3 0 00-3 3v6a3 3 0 003 3z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">#{{ ticket.ticket_number }}</p>
                                                <p class="text-sm text-gray-600 mt-0.5 line-clamp-1">{{ ticket.subject }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span 
                                            :class="getStatusStyles(ticket.status, ticket.status_color).badge"
                                            :style="getStatusStyles(ticket.status, ticket.status_color).style"
                                        >
                                            <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon(ticket.status)" />
                                            </svg>
                                            {{ ticket.status }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span 
                                            :class="getPriorityStyles(ticket.priority, ticket.priority_color).badge"
                                            :style="getPriorityStyles(ticket.priority, ticket.priority_color).style"
                                        >
                                            <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getPriorityIcon(ticket.priority)" />
                                            </svg>
                                            {{ ticket.priority }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="text-sm text-gray-600">
                                            <p>{{ formatDate(ticket.created_at) }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5">{{ new Date(ticket.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}</p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="text-sm text-gray-600">
                                            <p>{{ formatDate(ticket.updated_at) }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5">{{ new Date(ticket.updated_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4 pl-3 pr-6 text-right">
                                        <Link
                                            :href="route('user.tickets.show', ticket.id)"
                                            class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900 text-sm font-medium opacity-0 group-hover:opacity-100 transition-all hover:gap-2"
                                        >
                                            View Details
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Card View for mobile/tablet -->
                    <div v-if="localTickets.length > 0" class="lg:hidden divide-y divide-gray-100">
                        <div v-for="ticket in localTickets" :key="ticket.id" class="p-4 hover:bg-gray-50 transition-colors animate-fade-in">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        <span class="text-sm font-medium text-gray-900">#{{ ticket.ticket_number }}</span>
                                        <span 
                                            :class="getStatusStyles(ticket.status, ticket.status_color).badge"
                                            :style="getStatusStyles(ticket.status, ticket.status_color).style"
                                            class="inline-flex items-center px-2 py-0.5 text-xs"
                                        >
                                            <svg class="h-2.5 w-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon(ticket.status)" />
                                            </svg>
                                            {{ ticket.status }}
                                        </span>
                                        <span 
                                            :class="getPriorityStyles(ticket.priority, ticket.priority_color).badge"
                                            :style="getPriorityStyles(ticket.priority, ticket.priority_color).style"
                                            class="inline-flex items-center px-2 py-0.5 text-xs"
                                        >
                                            <svg class="h-2.5 w-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getPriorityIcon(ticket.priority)" />
                                            </svg>
                                            {{ ticket.priority }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">{{ ticket.subject }}</p>
                                    <div class="flex items-center gap-3 text-xs text-gray-400">
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ formatDate(ticket.created_at) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            {{ formatDate(ticket.updated_at) }}
                                        </span>
                                    </div>
                                </div>
                                <Link
                                    :href="route('user.tickets.show', ticket.id)"
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-50 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Pagination -->
                    <div v-if="tickets.last_page > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 order-2 sm:order-1">
                            Showing <span class="font-medium text-gray-900">{{ tickets.from }}</span> to 
                            <span class="font-medium text-gray-900">{{ tickets.to }}</span> of 
                            <span class="font-medium text-gray-900">{{ tickets.total }}</span> tickets
                        </p>
                        <div class="flex gap-2 order-1 sm:order-2">
                            <Link
                                v-if="tickets.prev_page_url"
                                :href="tickets.prev_page_url"
                                class="inline-flex items-center gap-1 rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Previous
                            </Link>
                            <Link
                                v-if="tickets.next_page_url"
                                :href="tickets.next_page_url"
                                class="inline-flex items-center gap-1 rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors"
                            >
                                Next
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Enhanced Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-20 px-4 text-center">
                        <div class="bg-gray-50 rounded-2xl p-6 mb-4">
                            <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-lg font-semibold text-gray-900">No tickets found</p>
                        <p class="text-sm text-gray-500 mt-1 max-w-sm">
                            <span v-if="activeFilterCount > 0">We couldn't find any tickets matching your filters. Try adjusting your search criteria.</span>
                            <span v-else>You haven't created any tickets yet. Start by submitting your first support request.</span>
                        </p>
                        <div class="mt-6 flex gap-3">
                            <button
                                v-if="activeFilterCount > 0"
                                @click="clearFilters"
                                class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors"
                            >
                                Clear all filters
                            </button>
                            <Link
                                v-if="activeFilterCount === 0"
                                :href="route('user.tickets.create')"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-all hover:scale-105"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create your first ticket
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </UserNavigation>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
/* Add any necessary styles */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.transition-shadow {
    transition-property: box-shadow;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.hover\:scale-105:hover {
    transform: scale(1.05);
}

.active\:scale-100:active {
    transform: scale(1);
}

/* Better text rendering */
.text-gray-900,
.text-gray-700,
.text-gray-600 {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}

/* Focus styles for accessibility */
:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
    border-radius: 0.5rem;
}
</style>