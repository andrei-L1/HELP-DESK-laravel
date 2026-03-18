<script setup>
import { ref, computed } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

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
    view: {
        type: String,
        required: true,
    },
});

// Create Ticket Modal Logic
const showCreateModal = ref(false);
const createForm = useForm({
    subject: '',
    description: '',
    priority: 'medium',
    category_id: '',
    department_id: '',
});

const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
};

const submitCreate = () => {
    createForm.transform((data) => ({
        ...data,
        category_id: data.category_id || null,
        department_id: data.department_id || null,
    })).post(route('admin.tickets.store'), {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

// Filter Logic
const searchInput = ref(props.filters.search || '');
const showFilters = ref(false);

const activeFilterCount = computed(() => {
    let count = 0;
    if (props.filters.status) count++;
    if (props.filters.priority) count++;
    if (props.filters.department) count++;
    return count;
});

const applyFilter = (key, value) => {
    router.get(
        route('admin.tickets.all'),
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
        route('admin.tickets.all'),
        {},
        {
            preserveState: false,
            preserveScroll: true,
        }
    );
};

const setQuickFilter = (status = null, priority = null) => {
    router.get(
        route('admin.tickets.all'),
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

// Table Actions
const viewTicket = (id) => {
    router.visit(route('admin.tickets.show', id));
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
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

const sortOptions = [
    { label: 'Recently Created', value: 'latest' },
    { label: 'Oldest First', value: 'oldest' },
    { label: 'Last Updated', value: 'updated' },
    { label: 'Priority (High to Low)', value: 'priority_desc' },
];

// Bulk Selection
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
</script>

<template>
    <Head title="Full Ticket Registry" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">Tickets</span>
            </div>
        </template>

        <template #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <Link :href="route('admin.tickets.index')" class="hover:text-slate-700 cursor-pointer">Tickets</Link>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900 uppercase tracking-widest">All Tickets</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-10 pb-20 pt-4">
            <!-- Create Ticket Modal (Teleport) -->
            <Teleport to="body">
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
                        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-900/20 w-full max-w-xl overflow-hidden border border-slate-100">
                            <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Create Ticket</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest italic tracking-tight">Add a new ticket to the system.</p>
                                </div>
                                <button @click="closeCreateModal" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 transition-colors">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <form @submit.prevent="submitCreate" class="p-8 space-y-6">
                                <div class="space-y-4">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Subject *</label>
                                        <input v-model="createForm.subject" type="text" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="Brief subject" />
                                        <p v-if="createForm.errors.subject" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ createForm.errors.subject }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Description *</label>
                                        <textarea v-model="createForm.description" rows="4" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all resize-none" placeholder="Provide details..." />
                                        <p v-if="createForm.errors.description" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ createForm.errors.description }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1.5">
                                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Priority</label>
                                            <select v-model="createForm.priority" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all">
                                                <option value="low">Low</option>
                                                <option value="medium">Medium</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Department</label>
                                            <select v-model="createForm.department_id" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all">
                                                <option value="">None</option>
                                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 pt-4 border-t border-slate-50">
                                    <button type="button" @click="closeCreateModal" class="flex-1 px-6 py-4 rounded-2xl bg-slate-50 text-slate-600 text-sm font-bold hover:bg-slate-100 transition-all">Discard</button>
                                    <button type="submit" :disabled="createForm.processing" class="flex-[2] px-6 py-4 rounded-2xl bg-slate-900 text-white text-sm font-bold shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50">
                                        {{ createForm.processing ? 'Processing...' : 'Create Ticket' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Transition>
            </Teleport>

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-4 stagger-1">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">All Tickets</h2>
                    <p class="text-slate-500 font-medium">Browse and manage every support ticket in the system.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-900 text-white text-sm font-bold shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        New Ticket
                    </button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-8 md:grid-cols-3 lg:grid-cols-4 px-1 stagger-2">
                <div v-for="stat in [
                    { label: 'Total Tickets', value: stats?.total || 0, color: 'slate' },
                    { label: 'Urgent Cases', value: stats?.urgent || 0, color: 'rose' },
                    { label: 'Pending Sync', value: stats?.pending || 0, color: 'amber' },
                    { label: 'Resolution Rate', value: (Math.round(stats?.total > 0 ? (stats?.resolved / stats?.total * 100) : 0)) + '%', color: 'emerald' }
                ]" :key="stat.label" class="group relative py-2">
                    <div class="flex flex-col">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2">{{ stat.label }}</p>
                        <p class="text-3xl font-bold text-slate-900 tracking-tighter">{{ stat.value }}</p>
                        <div class="mt-4 h-1 w-8 rounded-full bg-slate-100 overflow-hidden">
                            <div :class="{
                                'bg-slate-900': stat.color === 'slate',
                                'bg-emerald-500': stat.color === 'emerald',
                                'bg-rose-500': stat.color === 'rose',
                                'bg-amber-500': stat.color === 'amber'
                            }" class="h-full w-full opacity-50 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Smart Filter Bar -->
            <div class="bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden stagger-3">
                <div class="p-6 flex flex-col md:flex-row md:items-center gap-6">
                    <div class="flex-1 relative group">
                        <svg class="absolute left-4 top-3 h-5 w-5 text-slate-400 group-focus-within:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                        <input
                            type="text"
                            v-model="searchInput"
                            @keyup.enter="applyFilter('search', searchInput)"
                            class="block w-full rounded-xl border-none bg-slate-100/50 px-4 py-3.5 pl-12 text-sm placeholder:text-slate-500 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all"
                            placeholder="Search by ID, subject or user..."
                        />
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            @click="showFilters = !showFilters"
                            class="flex items-center gap-2.5 px-5 py-3.5 rounded-xl text-sm font-bold transition-all border"
                            :class="[showFilters ? 'bg-slate-900 text-white border-slate-900 shadow-lg shadow-slate-200' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50']"
                        >
                            <svg class="h-4 w-4" :class="{ 'rotate-180': showFilters }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0l-3.75-3.75M17.25 21L21 17.25" />
                            </svg>
                            Filters
                            <span v-if="activeFilterCount > 0" class="ml-1 h-5 w-5 rounded-full bg-emerald-500 text-[10px] flex items-center justify-center text-white ring-2 ring-white">
                                {{ activeFilterCount }}
                            </span>
                        </button>

                        <button
                            v-if="activeFilterCount > 0 || filters.search"
                            @click="resetFilters"
                            class="p-3.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all border border-transparent hover:border-rose-100"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-96 opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-96 opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-show="showFilters" class="border-t border-slate-100 bg-slate-50/30 p-8 pt-6">
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                            <div class="space-y-3">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Status</label>
                                <select v-model="filters.status" @change="applyFilter('status', $event.target.value)" class="block w-full rounded-xl border-none bg-white shadow-sm ring-1 ring-slate-200 px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 transition-all">
                                    <option value="">All Statuses</option>
                                    <option v-for="s in statuses" :key="s.id" :value="s.name">{{ s.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-3">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Priority</label>
                                <select v-model="filters.priority" @change="applyFilter('priority', $event.target.value)" class="block w-full rounded-xl border-none bg-white shadow-sm ring-1 ring-slate-200 px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 transition-all">
                                    <option value="">All Priorities</option>
                                    <option v-for="p in priorities" :key="p.id" :value="p.name">{{ p.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-3">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Department</label>
                                <select v-model="filters.department" @change="applyFilter('department', $event.target.value)" class="block w-full rounded-xl border-none bg-white shadow-sm ring-1 ring-slate-200 px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 transition-all">
                                    <option value="">All Departments</option>
                                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-3">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Sort By</label>
                                <select v-model="filters.sort" @change="applyFilter('sort', $event.target.value)" class="block w-full rounded-xl border-none bg-white shadow-sm ring-1 ring-slate-200 px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 transition-all">
                                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Quick Filter Ribbon -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide px-2 stagger-3.5">
                <button @click="setQuickFilter(null, null)" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap" :class="[!filters.status && !filters.priority ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:text-slate-900 hover:bg-white']">All Tickets</button>
                <button v-for="status in statuses.slice(0, 4)" :key="status.id" @click="setQuickFilter(status.name, null)" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap" :class="[filters.status === status.name ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:text-slate-900 hover:bg-white']">{{ status.name }}</button>
            </div>

            <!-- Table-Based Layout (Matching Users/All.vue) -->
            <div class="bg-white rounded-3xl border border-slate-300/40 shadow-xl shadow-slate-200/40 overflow-hidden relative stagger-4">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="py-5 px-7 w-12">
                                    <input type="checkbox" :checked="selectedTickets.length === tickets.data.length && tickets.data.length > 0" @change="toggleAllTickets" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900/20" />
                                </th>
                                <th class="py-5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Ticket</th>
                                <th class="py-5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">Status</th>
                                <th class="py-5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell">Priority</th>
                                <th class="py-5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:table-cell">Date Created</th>
                                <th class="py-5 px-7 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody v-if="tickets.data.length > 0" class="divide-y divide-slate-50">
                            <tr
                                v-for="(ticket, index) in tickets.data"
                                :key="ticket.id"
                                @click="viewTicket(ticket.id)"
                                class="group hover:bg-slate-50/50 transition-all cursor-pointer relative"
                                :class="[selectedTickets.includes(ticket.id) ? 'bg-slate-50' : '', `stagger-${Math.min(index + 5, 5)}`]"
                            >
                                <td class="py-4 px-7" @click.stop>
                                    <input type="checkbox" :checked="selectedTickets.includes(ticket.id)" @change="toggleTicketSelection(ticket.id)" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900/20" />
                                </td>
                                <td class="py-4 px-4 min-w-[300px]">
                                    <div class="flex items-center gap-4">
                                        <div class="h-11 w-11 rounded-xl bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400 border border-slate-200 shadow-sm shrink-0 group-hover:bg-white group-hover:scale-105 transition-all duration-300">
                                            #{{ ticket.ticket_number.split('-').pop() }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-slate-900 truncate tracking-tight">{{ ticket.subject }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-[10px] font-bold text-slate-400 truncate">{{ ticket.created_by }}</p>
                                                <span class="h-0.5 w-3 bg-slate-100 rounded-full"></span>
                                                <p class="text-[10px] font-bold text-slate-400 truncate lowercase">{{ ticket.ticket_number }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 hidden md:table-cell">
                                    <span class="px-2.5 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-tighter" :class="getStatusClass(ticket.status)">
                                        {{ ticket.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 hidden lg:table-cell">
                                    <span class="px-2.5 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-tighter" :class="getPriorityClass(ticket.priority)">
                                        {{ ticket.priority }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 hidden sm:table-cell">
                                    <span class="text-xs font-bold text-slate-500">{{ formatDate(ticket.created_at) }}</span>
                                </td>
                                <td class="py-4 px-7 text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('admin.tickets.show', ticket.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-slate-900 hover:bg-white transition-all shadow-sm border border-transparent hover:border-slate-100 group/btn" title="View Ticket">
                                            <svg class="h-5 w-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="py-40 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-20 w-20 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200 mb-6 font-black italic">!</div>
                                        <h4 class="text-xl font-black text-slate-900 tracking-tight">No Results Found</h4>
                                        <p class="text-xs font-bold text-slate-400 mt-2 max-w-xs">Your current filters or search query did not match any records.</p>
                                        <button @click="resetFilters" class="mt-8 px-6 py-2.5 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-slate-200">Reset Console</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modern Pagination -->
            <div v-if="tickets.links && tickets.links.length > 3" class="flex flex-col sm:flex-row items-center justify-between px-4 py-8">
                <div class="mb-4 sm:mb-0"><p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Showing <span class="text-slate-900">{{ tickets.from || 0 }}</span> - <span class="text-slate-900">{{ tickets.to || 0 }}</span> of <span class="text-slate-900">{{ tickets.total || 0 }}</span> entries</p></div>
                <div class="flex items-center gap-1.5 p-1.5 bg-white rounded-xl border border-slate-300/40 shadow-sm shadow-slate-200/40">
                    <Link v-for="link in tickets.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="h-10 min-w-[40px] px-3 flex items-center justify-center rounded-lg text-xs font-black transition-all" :class="[link.active ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50', !link.url ? 'opacity-30 cursor-not-allowed' : '']" />
                </div>
            </div>
        </div>

        <!-- Floating Bulk Actions Bar -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div v-if="selectedTickets.length > 0" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50">
                <div class="bg-slate-900 text-white rounded-2xl shadow-2xl shadow-slate-900/20 px-6 py-4 flex items-center gap-6 min-w-[400px]">
                    <div class="flex items-center gap-3 pr-6 border-r border-slate-800"><span class="h-6 w-6 rounded-lg bg-white/10 flex items-center justify-center text-[10px] font-black">{{ selectedTickets.length }}</span><span class="text-xs font-bold tracking-tight">Tickets Selected</span></div>
                    
                    <div class="flex items-center gap-2 flex-1">
                        <div class="relative group/status">
                            <button class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-white/10 transition-colors text-xs font-bold">
                                Change Status
                                <svg class="h-4 w-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute bottom-full mb-3 left-0 w-48 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl overflow-hidden opacity-0 invisible group-hover/status:opacity-100 group-hover/status:visible transition-all">
                                <button v-for="status in statuses" :key="status.id" @click="bulkUpdateStatus(status.id)" class="w-full text-left px-4 py-3 text-xs font-bold text-slate-400 hover:text-white hover:bg-white/5 transition-colors border-b border-slate-800/50 last:border-0">
                                    {{ status.name }}
                                </button>
                            </div>
                        </div>

                        <button @click="bulkDelete" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-rose-500 transition-colors text-xs font-bold text-rose-400 hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Delete
                        </button>
                    </div>

                    <button @click="selectedTickets = []" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        </Transition>
    </AdminNavigation>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; appearance: none; }
</style>
