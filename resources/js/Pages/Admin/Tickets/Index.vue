<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import TrendCard from '@/Components/TrendCard.vue';

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
            department: '',
            sort: 'latest',
        }),
    },
    statuses: {
        type: Array,
        default: () => [],
    },
    priorities: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            open: 0,
            pending: 0,
            resolved: 0,
            closed: 0,
            urgent: 0,
        }),
    },
});

// Real-time synchronization
const localTickets = ref([...(props.tickets.data || [])]);
const stats = ref({ ...props.stats });

watch(() => props.tickets.data, (newVal) => {
    localTickets.value = [...(newVal || [])];
}, { deep: true });

onMounted(() => {
    if (window.Echo) {
        window.Echo.private('staff.tickets')
            .listen('.TicketCreated', (e) => {
                console.log('[Echo] New ticket created:', e.ticket);
                
                // Match current filters
                if (props.filters.status && e.ticket.status !== props.filters.status) return;
                if (props.filters.priority && e.ticket.priority !== props.filters.priority) return;
                if (props.filters.department && e.ticket.department_id != props.filters.department) return;

                const isFirstPage = !props.tickets.prev_page_url;
                if (isFirstPage && !localTickets.value.find(t => t.id === e.ticket.id)) {
                    localTickets.value.unshift(e.ticket);
                    if (localTickets.value.length > (props.tickets.per_page || 15)) {
                        localTickets.value.pop();
                    }
                    // Update stats
                    stats.value.total++;
                    stats.value.open++;
                }
            })
            .listen('.TicketUpdated', (e) => {
                const index = localTickets.value.findIndex(t => t.id === e.ticket.id);
                
                // Determine if it matches current filters
                const matchesFilters = (
                    (!props.filters.status || e.ticket.status === props.filters.status) &&
                    (!props.filters.priority || e.ticket.priority === props.filters.priority) &&
                    (!props.filters.department || (e.ticket.department_id && e.ticket.department_id == props.filters.department))
                );

                if (index !== -1) {
                    if (matchesFilters) {
                        // Remove from current position and unshift to top (since list is sorted by updated_at)
                        localTickets.value.splice(index, 1);
                        localTickets.value.unshift(e.ticket);
                    } else {
                        // No longer matches filters, remove it
                        localTickets.value.splice(index, 1);
                    }
                } else {
                    // Not in list, check if it should be unshifted
                    const isFirstPage = !props.tickets.prev_page_url;
                    if (matchesFilters && isFirstPage) {
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

// Filter state
const searchInput = ref(props.filters.search || '');
const showFilters = ref(false);

const activeFilterCount = computed(() => {
    let count = 0;
    if (props.filters.status) count++;
    if (props.filters.priority) count++;
    if (props.filters.department) count++;
    return count;
});

// Apply filters
const applyFilter = (key, value) => {
    router.get(
        route('admin.tickets.index'),
        {
            ...props.filters,
            [key]: value || null,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const resetFilters = () => {
    router.get(
        route('admin.tickets.index'),
        {},
        {
            preserveState: false,
            preserveScroll: true,
        }
    );
};

const setQuickFilter = (status = null, priority = null) => {
    router.get(
        route('admin.tickets.index'),
        {
            ...props.filters,
            status: status,
            priority: priority,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const viewTicket = (id) => {
    router.visit(route('admin.tickets.show', id));
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusClass = (status) => {
    const s = status?.toLowerCase() || '';
    if (s === 'open') return 'bg-blue-50 text-blue-600 border-blue-100';
    if (s === 'pending') return 'bg-amber-50 text-amber-600 border-amber-100';
    if (s === 'resolved') return 'bg-emerald-50 text-emerald-600 border-emerald-100';
    if (s === 'closed') return 'bg-slate-100 text-slate-600 border-slate-200';
    return 'bg-slate-50 text-slate-500 border-slate-100';
};

const getPriorityClass = (priority) => {
    const p = priority?.toLowerCase() || '';
    if (p === 'urgent') return 'bg-rose-50 text-rose-600 border-rose-100';
    if (p === 'high') return 'bg-orange-50 text-orange-600 border-orange-100';
    if (p === 'medium') return 'bg-blue-50 text-blue-600 border-blue-100';
    if (p === 'low') return 'bg-slate-50 text-slate-500 border-slate-100';
    return 'bg-slate-50 text-slate-400 border-slate-100';
};

const getStatusTabClass = (status) => {
    const s = status?.toLowerCase() || '';
    if (s === 'open') return 'bg-blue-500 text-white shadow-lg shadow-blue-100';
    if (s === 'pending') return 'bg-amber-500 text-white shadow-lg shadow-amber-100';
    if (s === 'resolved') return 'bg-emerald-500 text-white shadow-lg shadow-emerald-100';
    if (s === 'closed') return 'bg-slate-900 text-white shadow-lg shadow-slate-200';
    return 'bg-slate-500 text-white';
};

const sortOptions = [
    { label: 'Recently Created', value: 'latest' },
    { label: 'Oldest First', value: 'oldest' },
    { label: 'Last Updated', value: 'updated' },
    { label: 'Priority (High to Low)', value: 'priority_desc' },
];

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
};

// Bulk Selection & Actions
const selectedTickets = ref([]);
const toggleTicketSelection = (id) => {
    const index = selectedTickets.value.indexOf(id);
    if (index === -1) {
        selectedTickets.value.push(id);
    } else {
        selectedTickets.value.splice(index, 1);
    }
};

const toggleAllTickets = () => {
    if (selectedTickets.value.length === props.tickets.data.length) {
        selectedTickets.value = [];
    } else {
        selectedTickets.value = props.tickets.data.map(t => t.id);
    }
};

const bulkDelete = () => {
    if (confirm(`Are you sure you want to delete ${selectedTickets.value.length} tickets? This action is permanent.`)) {
        router.delete(route('admin.tickets.bulk-destroy'), {
            data: { ids: selectedTickets.value },
            preserveScroll: true,
            onSuccess: () => selectedTickets.value = [],
        });
    }
};

const bulkUpdateStatus = (statusId) => {
    router.post(route('admin.tickets.bulk-update'), {
        ids: selectedTickets.value,
        status_id: statusId,
    }, {
        preserveScroll: true,
        onSuccess: () => selectedTickets.value = [],
    });
};

const maxPriorityCount = computed(() => {
    return Math.max(
        props.stats.urgent || 0,
        props.stats.high || 0,
        props.stats.medium || 0,
        props.stats.low || 0,
        1
    );
});
</script>

<template>
    <Head title="Manage Tickets" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">Ticket Management</span>
            </div>
        </template>

        <template #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <Link :href="route('admin.tickets.index')" class="hover:text-slate-700 cursor-pointer">Support</Link>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900">Ticket Dashboard</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-10 pb-20 pt-4">
            <!-- Executive Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-4 stagger-1">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Ticket Analytics</h2>
                    <p class="text-slate-500 font-medium">Overview of support ticket status, priorities, and resolution rates.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        v-if="route().has('admin.tickets.all')"
                        :href="route('admin.tickets.all')"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-slate-900 hover:border-slate-300 transition-all flex items-center gap-2 shadow-sm text-sm font-bold"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Ticket List
                    </Link>
                </div>
            </div>

            <!-- Distribution Grids & Analytical Insights -->
            <div class="grid lg:grid-cols-3 gap-8 px-1 stagger-2">
                <!-- Priority Breakdown -->
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-8 flex flex-col justify-between overflow-hidden relative">
                    <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-slate-50 -z-0 border border-slate-100/50"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Tickets by Priority</h3>
                            <div class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                <span class="text-[10px] font-black text-slate-400 uppercase">Real-time Status</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div v-for="priority in ['Urgent', 'High', 'Medium', 'Low']" :key="priority" class="space-y-4 group">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest" :class="priority === 'Urgent' ? 'text-rose-600' : 'text-slate-500'">{{ priority }}</span>
                                    <span class="text-xs font-black text-slate-900">{{ stats[priority.toLowerCase()] || 0 }}</span>
                                </div>
                                <div class="h-20 w-full bg-slate-50 rounded-2xl p-1 flex items-end">
                                    <div 
                                        :class="[
                                            priority === 'Urgent' ? 'bg-rose-500' : 
                                            priority === 'High' ? 'bg-orange-500' : 
                                            priority === 'Medium' ? 'bg-blue-500' : 'bg-slate-400'
                                        ]" 
                                        class="w-full rounded-xl transition-all duration-1000 group-hover:opacity-80"
                                        :style="{ height: ((stats[priority.toLowerCase()] || 0) / maxPriorityCount * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Velocity Monitor -->
                <div class="bg-indigo-600 rounded-3xl shadow-xl shadow-indigo-200/50 p-8 text-white relative flex flex-col justify-between overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-white/10 to-transparent pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-sm font-black uppercase tracking-widest text-indigo-200">Resolution Status</h3>
                            <svg class="h-5 w-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div class="text-center py-4">
                            <div class="text-5xl font-black tracking-tighter mb-2">{{ Math.round((stats.resolved / (stats.total || 1)) * 100) }}%</div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-200">Resolution Rate</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-8 pt-8 border-t border-white/10">
                        <div class="text-center">
                            <div class="text-xl font-bold text-white tracking-widest">{{ stats.open }}</div>
                            <div class="text-[9px] font-black uppercase text-indigo-300 tracking-widest">Open Tickets</div>
                        </div>
                        <div class="text-center border-l border-white/10">
                            <div class="text-xl font-bold text-white tracking-widest">{{ stats.pending }}</div>
                            <div class="text-[9px] font-black uppercase text-indigo-300 tracking-widest">Pending Tickets</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Intelligent Command Center / Trends -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 px-1 stagger-3">
                <TrendCard 
                    label="Active Tickets" 
                    :value="stats.open + stats.pending" 
                    :trend="[40, 45, 38, 50, 42, 48, 44]" 
                    percentage="+4%"
                    color="blue"
                    @click="setQuickFilter('Open', null)"
                />
                <TrendCard 
                    label="Total Tickets" 
                    :value="stats.total" 
                    :trend="[100, 105, 110, 108, 115, 120, 125]" 
                    percentage="+18%"
                    color="slate"
                />
                <TrendCard 
                    label="Resolved" 
                    :value="stats.resolved" 
                    :trend="[60, 65, 70, 75, 80, 85, 90]" 
                    percentage="+24%"
                    color="emerald"
                    @click="setQuickFilter('Resolved', null)"
                />
                <TrendCard 
                    label="Urgent Cases" 
                    :value="stats.urgent" 
                    :trend="[25, 20, 30, 15, 22, 18, 24]" 
                    percentage="+12%"
                    color="rose"
                    @click="setQuickFilter(null, 'Urgent')"
                />
            </div>

            <!-- Table Section Headers -->
            <div class="flex items-center justify-between px-6 stagger-4 mt-12">
                <div class="space-y-0.5">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Latest Tickets</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-none">History of the most recent support requests</p>
                </div>
                <div class="flex items-center gap-3">
                     <button
                        @click="showFilters = !showFilters"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest border border-slate-200 hover:border-slate-900 transition-all flex items-center gap-2"
                    >
                        Advanced Filters
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0l-3.75-3.75M17.25 21L21 17.25" /></svg>
                    </button>
                    <Link :href="route('admin.tickets.create')" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">Create Ticket</Link>
                </div>
            </div>

            <!-- Smart Filter Bar (Relocated & Restyled) -->
            <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="max-h-0 opacity-0"
                enter-to-class="max-h-96 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="max-h-96 opacity-100"
                leave-to-class="max-h-0 opacity-0"
            >
                <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4 px-4 stagger-4.5">
                     <div class="md:col-span-1 relative group">
                        <svg class="absolute left-4 top-3 h-5 w-5 text-slate-400 group-focus-within:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" /></svg>
                        <input v-model="searchInput" @keyup.enter="applyFilter('search', searchInput)" class="block w-full rounded-xl border-none bg-slate-100/50 px-4 py-3 pl-12 text-sm placeholder:text-slate-500 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="Search tickets..." />
                    </div>
                    <select v-model="filters.status" @change="applyFilter('status', $event.target.value)" class="block rounded-xl border-none bg-slate-100/50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white transition-all"><option value="">All Statuses</option><option v-for="s in statuses" :key="s.id" :value="s.name">{{ s.name }}</option></select>
                    <select v-model="filters.priority" @change="applyFilter('priority', $event.target.value)" class="block rounded-xl border-none bg-slate-100/50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white transition-all"><option value="">All Priorities</option><option v-for="p in priorities" :key="p.id" :value="p.name">{{ p.name }}</option></select>
                    <select v-model="filters.sort" @change="applyFilter('sort', $event.target.value)" class="block rounded-xl border-none bg-slate-100/50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white transition-all"><option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option></select>
                </div>
            </transition>

            <!-- Executive Ticket Directory -->
            <div class="bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden stagger-5 relative">
                <!-- Directory Control Header -->
                <div class="bg-slate-50/50 border-b border-slate-100 px-8 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" :checked="selectedTickets.length === tickets.data.length && tickets.data.length > 0" @change="toggleAllTickets" class="rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20" />
                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Active Tickets</span>
                    </div>
                </div>

                <div v-if="localTickets.length > 0" class="divide-y divide-slate-50">
                    <div
                        v-for="(ticket, index) in localTickets"
                        :key="ticket.id"
                        class="flex flex-col lg:flex-row lg:items-center justify-between py-4 px-8 hover:bg-slate-50/50 transition-all cursor-pointer group relative animate-fade-in"
                        :class="[selectedTickets.includes(ticket.id) ? 'bg-slate-50/80 text-slate-900 shadow-sm' : '', `stagger-${Math.min(index + 5, 5)}`]"
                        @click="viewTicket(ticket.id)"
                    >
                        <!-- Selection Indicator -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-900 scale-y-0 group-hover:scale-y-100 transition-transform origin-center"></div>

                        <!-- Selection Checkbox -->
                        <div class="relative z-10 flex-shrink-0 mr-4" @click.stop>
                            <input 
                                type="checkbox" 
                                :checked="selectedTickets.includes(ticket.id)" 
                                @change="toggleTicketSelection(ticket.id)" 
                                class="h-4 w-4 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20 cursor-pointer" 
                            />
                        </div>

                        <!-- Ticket Profile Card -->
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="relative flex-shrink-0 group-hover:scale-105 transition-transform duration-500">
                                <div class="h-12 w-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center shadow-lg shadow-slate-200 group-hover:shadow-slate-300 transition-all overflow-hidden">
                                     <span class="text-[9px] font-black text-slate-400">#{{ ticket.ticket_number.split('-').pop() }}</span>
                                </div>
                                <div class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-white flex items-center justify-center transition-all shadow-sm"
                                     :class="getStatusClass(ticket.status)">
                                    <div class="h-1.5 w-1.5 rounded-full bg-current"></div>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-black text-slate-900 truncate tracking-tight group-hover:text-slate-700 transition-colors">{{ ticket.subject }}</h4>
                                    <span :class="getPriorityClass(ticket.priority)" class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-black uppercase border tracking-tighter">{{ ticket.priority }}</span>
                                </div>
                                <div class="flex items-center gap-3 mt-0.5">
                                    <span class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                        {{ ticket.created_by }}
                                    </span>
                                    <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-300">{{ ticket.ticket_number }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Management Details -->
                        <div class="mt-6 lg:mt-0 flex items-center gap-12 lg:px-10">
                            <div class="flex flex-col gap-1 min-w-[140px]">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] leading-none">Agent</span>
                                <span class="text-xs font-black text-slate-600 uppercase tracking-tighter truncate max-w-[120px]">{{ ticket.assigned_to || 'UNASSIGNED' }}</span>
                            </div>
                            <div class="hidden sm:flex flex-col gap-1 min-w-[120px]">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] leading-none">Created At</span>
                                <span class="text-xs font-bold text-slate-500">{{ formatDate(ticket.created_at) }}</span>
                            </div>
                        </div>

                        <!-- Action Cluster -->
                        <div class="mt-6 lg:mt-0 flex items-center justify-end gap-1.5" @click.stop>
                             <div class="flex items-center gap-1 p-1 bg-slate-50 rounded-2xl border border-slate-100 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-4 group-hover:translate-x-0">
                                <Link :href="route('admin.tickets.show', ticket.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-slate-900 hover:bg-white transition-all shadow-sm" title="View Ticket"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg></Link>
                                <Link v-if="route().has('admin.tickets.edit')" :href="route('admin.tickets.edit', ticket.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-white transition-all shadow-sm" title="Edit Ticket"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></Link>
                             </div>
                             <div class="h-10 w-10 flex items-center justify-center text-slate-300 group-hover:hidden transition-all">
                                 <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State (Illustrated) -->
                <div v-else class="py-32 flex flex-col items-center text-center px-4 stagger-5">
                    <div class="relative mb-8">
                        <div class="absolute inset-0 bg-slate-100 rounded-full blur-3xl opacity-50 scale-150"></div>
                        <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-white shadow-xl shadow-slate-200 border border-slate-100">
                             <svg class="h-10 w-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div class="absolute -bottom-2 -right-2 h-8 w-8 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                    <h4 class="text-xl font-black text-slate-900 tracking-tight mb-2">No tickets found</h4>
                    <p class="text-slate-500 max-w-xs font-medium italic">No tickets found matching your current filters.</p>
                    <button @click="resetFilters" class="mt-8 text-sm font-bold text-slate-900 border-b-2 border-slate-900 pb-0.5 hover:text-slate-600 hover:border-slate-600 transition-all">Reset Filters</button>
                </div>
            </div>

            <!-- Modern Pagination -->
            <div
                v-if="tickets.links && tickets.links.length > 3"
                class="flex flex-col sm:flex-row items-center justify-between px-4 py-8"
            >
                <div class="mb-4 sm:mb-0">
                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                        Showing <span class="text-slate-900">{{ tickets.from || 0 }}</span> - <span class="text-slate-900">{{ tickets.to || 0 }}</span> of <span class="text-slate-900">{{ tickets.total || 0 }}</span> entries
                    </p>
                </div>
                <div class="flex items-center gap-1.5 p-1.5 bg-white rounded-xl border border-slate-300/40 shadow-sm shadow-slate-200/40">
                    <Link
                        v-for="link in tickets.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="h-10 min-w-[40px] px-3 flex items-center justify-center rounded-lg text-xs font-black transition-all"
                        :class="[
                            link.active
                                ? 'bg-slate-900 text-white shadow-lg shadow-slate-200'
                                : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50',
                            !link.url ? 'opacity-30 cursor-not-allowed' : '',
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Floating Bulk Actions Bar (UI Placeholder) -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div v-if="selectedTickets.length > 0" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50">
                <div class="bg-slate-900 text-white rounded-2xl shadow-2xl shadow-slate-900/20 px-6 py-4 flex items-center gap-6 min-w-[400px]">
                    <div class="flex items-center gap-3 pr-6 border-r border-slate-800">
                        <span class="h-6 w-6 rounded-lg bg-white/10 flex items-center justify-center text-[10px] font-black">{{ selectedTickets.length }}</span>
                        <span class="text-xs font-bold tracking-tight">Tickets Selected</span>
                    </div>
                    
                    <div class="flex items-center gap-2 flex-1">
                        <!-- Update Status Dropdown -->
                        <div class="relative group/status">
                            <button class="inline-flex items-center gap-2.5 px-6 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold transition-all border border-white/5">
                                Change Status
                                <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute bottom-full mb-3 left-0 w-48 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl overflow-hidden opacity-0 invisible group-hover/status:opacity-100 group-hover/status:visible transition-all">
                                <button v-for="status in statuses" :key="status.id" @click="bulkUpdateStatus(status.id)" class="w-full text-left px-4 py-3 text-xs font-bold text-slate-400 hover:text-white hover:bg-white/5 transition-colors border-b border-slate-800/50 last:border-0">
                                    {{ status.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Bulk Delete -->
                        <button @click="bulkDelete" class="inline-flex items-center gap-2.5 px-6 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white text-xs font-bold transition-all border border-rose-500/20 group/delete">
                            Delete
                            <svg class="h-3.5 w-3.5 opacity-60 group-hover/delete:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>

                    <button @click="selectedTickets = []" class="text-slate-500 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/5">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        </transition>
    </AdminNavigation>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/csv' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    appearance: none;
}
</style>