<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            role: '',
        }),
    },
    roles: {
        type: [Object, Array],
        default: () => [],
    },
    statuses: {
        type: Object,
        default: () => ({
            active: 'Active',
            inactive: 'Inactive',
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            inactive: 0,
            verified: 0,
            unverified: 0,
            with_avatar: 0,
        }),
    },
});

// Transform roles to array format for easier handling
const rolesList = ref([]);

watch(() => props.roles, (roles) => {
    if (Array.isArray(roles)) {
        rolesList.value = roles;
    } else if (roles && typeof roles === 'object') {
        rolesList.value = Object.entries(roles).map(([id, name]) => ({
            id: parseInt(id),
            name: name
        }));
    } else {
        rolesList.value = [];
    }
}, { immediate: true });

// Filter state
const searchInput = ref(props.filters.search || '');
const showFilters = ref(false);

const activeFilterCount = computed(() => {
    let count = 0;
    if (props.filters.status) count++;
    if (props.filters.role) count++;
    return count;
});

const getStatusLabel = (value) => props.statuses[value] || value;
const getRoleLabel = (id) => {
    const role = rolesList.value.find(r => r.id === parseInt(id));
    return role ? role.name : id;
};

// Apply filters
const applyFilter = (key, value) => {
    router.get(
        route('admin.users.index'),
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
        route('admin.users.index'),
        {},
        {
            preserveState: false,
            preserveScroll: true,
        }
    );
};

const setQuickFilter = (status = null, role = null) => {
    router.get(
        route('admin.users.index'),
        {
            ...props.filters,
            status: status,
            role: role,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const viewUser = (userId) => {
    router.visit(route('admin.users.show', userId));
};

const impersonateUser = (userId) => {
    if (confirm('Are you sure you want to impersonate this user?')) {
        router.post(route('admin.users.impersonate', userId));
    }
};

const deleteUser = (userId) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('admin.users.destroy', userId), {
            preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString();
};

const getRoleClass = (role) => {
    const r = role?.toLowerCase() || '';
    if (r.includes('admin')) return 'bg-rose-50 text-rose-600 border-rose-100';
    if (r.includes('agent') || r.includes('staff')) return 'bg-blue-50 text-blue-600 border-blue-100';
    if (r.includes('manager')) return 'bg-amber-50 text-amber-600 border-amber-100';
    return 'bg-slate-100 text-slate-600 border-slate-200';
};

const getRoleTabClass = (role) => {
    const r = role?.toLowerCase() || '';
    if (r.includes('admin')) return 'bg-rose-500 text-white shadow-lg shadow-rose-100';
    if (r.includes('agent') || r.includes('staff')) return 'bg-blue-500 text-white shadow-lg shadow-blue-100';
    if (r.includes('manager')) return 'bg-amber-500 text-white shadow-lg shadow-amber-100';
    return 'bg-slate-900 text-white shadow-lg shadow-slate-200';
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    // Optional: Add a toast notification here if available
};

const sortOptions = [
    { label: 'Recently Joined', value: 'latest' },
    { label: 'Oldest First', value: 'oldest' },
    { label: 'Name (A-Z)', value: 'name_asc' },
    { label: 'Name (Z-A)', value: 'name_desc' },
    { label: 'Last Active', value: 'active' },
];

// Bulk Selection
const selectedUsers = ref([]);
const toggleUserSelection = (userId) => {
    const index = selectedUsers.value.indexOf(userId);
    if (index === -1) {
        selectedUsers.value.push(userId);
    } else {
        selectedUsers.value.splice(index, 1);
    }
};

const toggleAllUsers = () => {
    if (selectedUsers.value.length === props.users.data.length) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.users.data.map(u => u.id);
    }
};

const bulkDelete = () => {
    if (confirm(`Are you sure you want to delete ${selectedUsers.value.length} users? This action is permanent.`)) {
        router.delete(route('admin.users.bulk-destroy'), {
            data: { ids: selectedUsers.value },
            preserveScroll: true,
            onSuccess: () => selectedUsers.value = [],
        });
    }
};

const bulkDeactivate = () => {
    if (confirm(`Are you sure you want to suspend ${selectedUsers.value.length} users?`)) {
        router.post(route('admin.users.bulk-update'), {
            ids: selectedUsers.value,
            status: 'inactive',
        }, {
            preserveScroll: true,
            onSuccess: () => selectedUsers.value = [],
        });
    }
};
</script>

<template>
    <Head title="User Directory" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">User Management</span>
            </div>
        </template>

        <template #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <Link :href="route('admin.users.index')" class="hover:text-slate-700 cursor-pointer">User Management</Link>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900">User Index</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-10 pb-20 pt-4">
            <!-- Executive Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-4 stagger-1">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">User Management</h2>
                    <p class="text-slate-500 font-medium">Executive overview of system access, personnel distribution, and security health.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.users.all')"
                        class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-bold shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        Power Console
                    </Link>
                    <Link
                        :href="route('admin.users.create')"
                        class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl bg-slate-900 text-white text-sm font-bold shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        Create User
                    </Link>
                </div>
            </div>

            <!-- Intelligent Analytics Command Center -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 px-1 stagger-2">
                <!-- Health Metric: Verified Access -->
                <div class="bg-white p-6 rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/50 group hover:shadow-xl hover:shadow-slate-200/40 transition-all duration-500 overflow-hidden relative">
                    <div class="absolute -right-4 -top-4 h-24 w-24 bg-blue-50/50 rounded-full blur-2xl group-hover:bg-blue-100/50 transition-colors"></div>
                    <div class="relative flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-blue-50 rounded-xl text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest bg-blue-50 px-2 py-1 rounded-lg">Verified Account</span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-3xl font-black text-slate-900 tracking-tighter">{{ stats.verified }}</p>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Identity Verified</p>
                            <div class="mt-4 flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                     <div class="h-full bg-blue-500 rounded-full transition-all duration-1000" :style="{ width: (stats.verified / stats.total * 100) + '%' }"></div>
                                </div>
                                <span class="text-[10px] font-black text-slate-500">{{ Math.round(stats.total > 0 ? (stats.verified / stats.total * 100) : 0) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Availability Metric: Active Access -->
                <div class="bg-white p-6 rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/50 group hover:shadow-xl hover:shadow-slate-200/40 transition-all duration-500 overflow-hidden relative">
                    <div class="absolute -right-4 -top-4 h-24 w-24 bg-emerald-50/50 rounded-full blur-2xl group-hover:bg-emerald-100/50 transition-colors"></div>
                    <div class="relative flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-emerald-50 rounded-xl text-emerald-600 border border-emerald-100 shadow-sm shadow-emerald-50">
                                <div class="h-5 w-5 rounded-full border-2 border-current flex items-center justify-center">
                                    <div class="h-1.5 w-1.5 bg-emerald-600 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2 py-1 rounded-lg">Live Personnel</span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-3xl font-black text-slate-900 tracking-tighter">{{ stats.active }}</p>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Operational Access</p>
                            <div class="mt-4 flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                     <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000" :style="{ width: (stats.active / stats.total * 100) + '%' }"></div>
                                </div>
                                <span class="text-[10px] font-black text-slate-500">{{ Math.round(stats.total > 0 ? (stats.active / stats.total * 100) : 0) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Volume Metric: Total Population -->
                <div class="bg-slate-900 p-6 rounded-3xl shadow-xl shadow-slate-200/80 group overflow-hidden relative">
                    <div class="absolute -right-4 -top-4 h-24 w-24 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-colors"></div>
                    <div class="relative flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-white/10 rounded-xl text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <p class="text-3xl font-black text-white tracking-tighter">{{ stats.total }}</p>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total Population</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <div class="px-2 py-1 rounded bg-white/5 border border-white/10 text-[9px] font-black text-slate-400 uppercase tracking-tighter">System Census</div>
                                <div class="px-2 py-1 rounded bg-white/5 border border-white/10 text-[9px] font-black text-slate-400 uppercase tracking-tighter">Active Sync</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Metric: Pending/Security -->
                <div class="bg-white p-6 rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/50 group hover:shadow-xl hover:shadow-slate-200/40 transition-all duration-500 overflow-hidden relative">
                    <div class="absolute -right-4 -top-4 h-24 w-24 bg-rose-50/50 rounded-full blur-2xl group-hover:bg-rose-100/50 transition-colors"></div>
                    <div class="relative flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-rose-50 rounded-xl text-rose-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest bg-rose-50 px-2 py-1 rounded-lg">Access Risk</span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-3xl font-black text-slate-900 tracking-tighter">{{ stats.unverified }}</p>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Awaiting Validation</p>
                            <p class="text-[9px] font-bold text-rose-500 mt-3 animate-pulse">Action required for {{ stats.unverified }} accounts</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Smart Filter Bar (Collapsible) -->
            <div class="bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 stagger-3 overflow-hidden">
                <!-- Primary Search & Toggle -->
                <div class="p-6 flex flex-col md:flex-row md:items-center gap-6">
                    <!-- Search -->
                    <div class="flex-1 relative group">
                        <svg class="absolute left-4 top-3 h-5 w-5 text-slate-400 group-focus-within:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                        <input
                            type="text"
                            v-model="searchInput"
                            @keyup.enter="applyFilter('search', searchInput)"
                            class="block w-full rounded-xl border-none bg-slate-100/50 px-4 py-3.5 pl-12 text-sm placeholder:text-slate-500 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all"
                            placeholder="Find name, username or email..."
                        />
                    </div>

                    <!-- Actions Area -->
                    <div class="flex items-center gap-3">
                        <!-- Active Filter Pills (Visible when collapsed) -->
                        <div v-if="!showFilters && activeFilterCount > 0" class="hidden lg:flex items-center gap-2">
                            <div v-if="filters.status" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg text-[10px] font-black uppercase text-slate-600 border border-slate-200">
                                <span>Status: {{ getStatusLabel(filters.status) }}</span>
                            </div>
                            <div v-if="filters.role" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg text-[10px] font-black uppercase text-slate-600 border border-slate-200">
                                <span>Role: {{ getRoleLabel(filters.role) }}</span>
                            </div>
                        </div>

                        <!-- Toggle Button -->
                        <button
                            @click="showFilters = !showFilters"
                            class="flex items-center gap-2.5 px-5 py-3.5 rounded-xl text-sm font-bold transition-all border"
                            :class="[
                                showFilters 
                                    ? 'bg-slate-900 text-white border-slate-900 shadow-lg shadow-slate-200' 
                                    : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'
                            ]"
                        >
                            <svg class="h-4 w-4" :class="{ 'rotate-180': showFilters }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0l-3.75-3.75M17.25 21L21 17.25" />
                            </svg>
                            Filters
                            <span v-if="activeFilterCount > 0" class="ml-1 h-5 w-5 rounded-full bg-emerald-500 text-[10px] flex items-center justify-center text-white ring-2 ring-white">
                                {{ activeFilterCount }}
                            </span>
                        </button>

                        <!-- Reset -->
                        <button
                            v-if="activeFilterCount > 0 || filters.search"
                            @click="resetFilters"
                            class="p-3.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all border border-transparent hover:border-rose-100"
                            title="Reset all filters"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Expanded Advanced Filters -->
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-96 opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-96 opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-show="showFilters" class="border-t border-slate-100 bg-slate-50/30 p-8 pt-6">
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                            <!-- Status Filter -->
                            <div class="space-y-3">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Select Status</label>
                                <select
                                    v-model="filters.status"
                                    @change="applyFilter('status', $event.target.value)"
                                    class="block w-full rounded-xl border-none bg-white shadow-sm ring-1 ring-slate-200 px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 transition-all"
                                >
                                    <option value="">All Statuses</option>
                                    <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                                </select>
                            </div>

                            <!-- Role Filter -->
                            <div class="space-y-3">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Security Role</label>
                                <select
                                    v-model="filters.role"
                                    @change="applyFilter('role', $event.target.value)"
                                    class="block w-full rounded-xl border-none bg-white shadow-sm ring-1 ring-slate-200 px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 transition-all"
                                >
                                    <option value="">All Account Types</option>
                                    <option v-for="role in rolesList" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                            </div>

                            <!-- Sort By -->
                            <div class="space-y-3">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Ordering By</label>
                                <select
                                    v-model="filters.sort"
                                    @change="applyFilter('sort', $event.target.value)"
                                    class="block w-full rounded-xl border-none bg-white shadow-sm ring-1 ring-slate-200 px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 transition-all"
                                >
                                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Rapid View Scrollable Ribbon (Quick Filters) -->
            <div class="relative group stagger-3.5">
                <!-- Subtle right-side fade to indicate more items -->
                <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-slate-50 to-transparent z-10 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide px-2">
                    <!-- Status Group -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button 
                            @click="setQuickFilter(null, null)"
                            class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap"
                            :class="[!filters.status && !filters.role ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-400 hover:text-slate-900 hover:bg-white']"
                        >
                            Overview
                        </button>
                        <button 
                            @click="setQuickFilter('active', null)"
                            class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap"
                            :class="[filters.status === 'active' && !filters.role ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-100' : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50/50']"
                        >
                            Active
                            <span v-if="stats.active > 0" class="opacity-60 text-[9px] font-bold">{{ stats.active }}</span>
                        </button>
                        <button 
                            @click="setQuickFilter('inactive', null)"
                            class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap"
                            :class="[filters.status === 'inactive' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-400 hover:text-slate-900 hover:bg-slate-100']"
                        >
                            Suspended
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="h-4 w-px bg-slate-200 mx-2 shrink-0"></div>

                    <!-- Roles Group (Dynamic) -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button 
                            v-for="role in rolesList"
                            :key="role.id"
                            @click="setQuickFilter(null, role.id)"
                            class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap"
                            :class="[
                                filters.role == role.id 
                                    ? getRoleTabClass(role.name) 
                                    : 'text-slate-400 hover:text-slate-900 hover:bg-white'
                            ]"
                        >
                            {{ role.name }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Executive User Directory -->
            <div class="bg-white rounded-[2.5rem] border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden stagger-4 relative">
                <!-- Directory Control Header -->
                <div class="bg-slate-50/50 border-b border-slate-100 px-8 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" :checked="selectedUsers.length === users.data.length && users.data.length > 0" @change="toggleAllUsers" class="rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20" />
                        <span class="text-[11px] font-black text-slate-900 uppercase tracking-[0.15em]">Personnel Directory List</span>
                    </div>
                </div>

                <div v-if="users.data.length > 0" class="divide-y divide-slate-50">
                    <div
                        v-for="(user, index) in users.data"
                        :key="user.id"
                        class="flex flex-col lg:flex-row lg:items-center justify-between py-6 px-8 hover:bg-slate-50/50 transition-all cursor-pointer group relative"
                        :class="[selectedUsers.includes(user.id) ? 'bg-slate-50/80' : '', `stagger-${Math.min(index + 5, 5)}`]"
                        @click="viewUser(user.id)"
                    >
                        <!-- Selection Indicator -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-900 scale-y-0 group-hover:scale-y-100 transition-transform origin-center"></div>

                        <!-- User Profile Card -->
                        <div class="flex items-center gap-6 min-w-0 flex-1">
                            <div class="relative flex-shrink-0 group-hover:scale-105 transition-transform duration-500">
                                <img
                                    class="h-16 w-16 rounded-2xl object-cover ring-4 ring-white shadow-xl shadow-slate-200 group-hover:shadow-slate-300 transition-all"
                                    :src="user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.display_name) + '&size=64&background=f1f5f9&color=64748b&bold=true'"
                                    :alt="user.display_name"
                                />
                                <div class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-2 border-white flex items-center justify-center transition-all bg-white shadow-sm" 
                                     :class="user.is_active ? 'text-emerald-500' : 'text-slate-300'">
                                    <div :class="user.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'" class="h-2 w-2 rounded-full"></div>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-lg font-black text-slate-900 truncate tracking-tight group-hover:text-slate-700 transition-colors">{{ user.display_name }}</h4>
                                    <span v-if="user.email_verified" class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-[9px] font-black text-blue-600 uppercase border border-blue-100 tracking-tighter">Verified</span>
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    <button @click.stop="copyToClipboard(user.email)" class="text-xs font-bold text-slate-400 truncate hover:text-slate-900 transition-colors flex items-center gap-1.5 group/email">
                                        {{ user.email }}
                                        <svg class="h-3 w-3 opacity-0 group-hover/email:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                    </button>
                                    <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-300">@{{ user.username }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Management Details -->
                        <div class="mt-6 lg:mt-0 flex items-center gap-12 lg:px-10">
                            <div class="flex flex-col gap-1 min-w-[120px]">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] leading-none">Security Level</span>
                                <span class="text-xs font-black text-slate-600 uppercase tracking-tighter">{{ user.role }}</span>
                            </div>
                            <div class="hidden sm:flex flex-col gap-1 min-w-[120px]">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] leading-none">Last Interaction</span>
                                <span class="text-xs font-bold text-slate-500">{{ formatDate(user.last_login) }}</span>
                            </div>
                        </div>

                        <!-- Action Cluster -->
                        <div class="mt-6 lg:mt-0 flex items-center justify-end gap-1.5" @click.stop>
                             <div class="flex items-center gap-1 p-1 bg-slate-50 rounded-2xl border border-slate-100 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-4 group-hover:translate-x-0">
                                <button @click="impersonateUser(user.id)" v-if="user.is_active" class="p-2.5 rounded-xl text-slate-400 hover:text-amber-600 hover:bg-white transition-all shadow-sm" title="Impersonate Access"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></button>
                                <Link :href="route('admin.users.edit', user.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-slate-900 hover:bg-white transition-all shadow-sm" title="Modify Records"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></Link>
                                <button @click="deleteUser(user.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-white transition-all shadow-sm" title="Deprovision"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="absolute -bottom-2 -right-2 h-8 w-8 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                    <h4 class="text-xl font-black text-slate-900 tracking-tight mb-2">No personnel found</h4>
                    <p class="text-slate-500 max-w-xs font-medium italic">Your current filters or search query did not yield any results from the executive database.</p>
                    <button @click="resetFilters" class="mt-8 text-sm font-bold text-slate-900 border-b-2 border-slate-900 pb-0.5 hover:text-slate-600 hover:border-slate-600 transition-all">Reset Intelligence Console</button>
                </div>
            </div>

            <!-- Modern Pagination -->
            <div
                v-if="users.links && users.links.length > 3"
                class="flex flex-col sm:flex-row items-center justify-between px-4 py-8"
            >
                <div class="mb-4 sm:mb-0">
                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                        Showing <span class="text-slate-900">{{ users.from || 0 }}</span> - <span class="text-slate-900">{{ users.to || 0 }}</span> of <span class="text-slate-900">{{ users.total || 0 }}</span> entries
                    </p>
                </div>
                <div class="flex items-center gap-1.5 p-1.5 bg-white rounded-xl border border-slate-300/40 shadow-sm shadow-slate-200/40">
                    <Link
                        v-for="link in users.links"
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

        <!-- Floating Bulk Actions Bar -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div v-if="selectedUsers.length > 0" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50">
                <div class="bg-slate-900 text-white rounded-2xl shadow-2xl shadow-slate-900/20 px-6 py-4 flex items-center gap-6 min-w-[400px]">
                    <div class="flex items-center gap-3 pr-6 border-r border-slate-800">
                        <span class="h-6 w-6 rounded-lg bg-white/10 flex items-center justify-center text-[10px] font-black">{{ selectedUsers.length }}</span>
                        <span class="text-xs font-bold tracking-tight">Users Selected</span>
                    </div>
                    
                    <div class="flex items-center gap-2 flex-1">
                        <button @click="bulkDeactivate" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-white/10 transition-colors text-xs font-bold">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                            Suspend
                        </button>
                        <button @click="bulkDelete" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-rose-500 transition-colors text-xs font-bold text-rose-400 hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Delete
                        </button>
                    </div>

                    <button @click="selectedUsers = []" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        </transition>
    </AdminNavigation>
</template>

<style scoped>
/* Smooth transitions inherited from app.css */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    appearance: none;
}
</style>