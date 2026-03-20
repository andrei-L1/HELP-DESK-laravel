<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import TrendCard from '@/Components/TrendCard.vue';

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
const getRoleIconColor = (name) => {
    const n = name?.toLowerCase() || '';
    if (n.includes('admin')) return 'bg-rose-500';
    if (n.includes('agent') || n.includes('staff')) return 'bg-blue-500';
    if (n.includes('manager')) return 'bg-amber-500';
    return 'bg-slate-900';
};

const maxRoleCount = computed(() => {
    const distribution = props.stats.roles_distribution || {};
    const values = Object.values(distribution);
    return values.length > 0 ? Math.max(...values) : 1;
});
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
                <span class="text-slate-900">Analytics Dashboard</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-10 pb-20 pt-4">
            <!-- Executive Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-4 stagger-1">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">User Analytics</h2>
                    <p class="text-slate-500 font-medium">System-wide user distribution, security compliance, and growth metrics.</p>
                </div>
                <div class="flex items-center gap-3 text-sm font-bold">
                    <Link
                        :href="route('admin.users.all')"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-slate-900 hover:border-slate-300 transition-all flex items-center gap-2 shadow-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        User List
                    </Link>
                </div>
            </div>

            <!-- Distribution Grids & Analytical Insights -->
            <div class="grid lg:grid-cols-3 gap-8 px-1 stagger-2">
                <!-- Role Distribution -->
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-8 flex flex-col justify-between overflow-hidden relative">
                    <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-slate-50 -z-0"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Users by Role</h3>
                            <Link :href="route('admin.users.roles')" class="text-[10px] font-bold text-slate-400 hover:text-slate-900 transition-colors uppercase tracking-widest border-b border-slate-100 pb-1">Manage Roles</Link>
                        </div>
                        <div class="space-y-6">
                            <div v-for="role in rolesList.slice(0, 4)" :key="role.id" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600 flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full" :class="getRoleIconColor(role.name)"></span>
                                        {{ role.name }}
                                    </span>
                                    <span class="text-xs font-black text-slate-900 tracking-tight">{{ stats.roles_distribution?.[role.id] || 0 }} Users</span>
                                </div>
                                <div class="h-2 w-full bg-slate-50 rounded-full overflow-hidden">
                                    <div :class="getRoleTabClass(role.name)" class="h-full rounded-full transition-all duration-1000" :style="{ width: ((stats.roles_distribution?.[role.id] || 0) / maxRoleCount * 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Access Score -->
                <div class="bg-slate-900 rounded-3xl shadow-xl shadow-slate-200/50 p-8 text-white relative flex flex-col justify-between overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/[0.05] to-transparent pointer-events-none"></div>
                    <div class="relative z-10">
                         <div class="flex items-center justify-between mb-8">
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400">Account Security</h3>
                            <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.33.166.678.166 1.039 0 5.8-3.045 10.89-7.66 13.528l-.34.194-.34-.194c-4.615-2.638-7.66-7.728-7.66-13.528 0-.361.056-.708.166-1.039zM10 5.003L4.53 6.42c.553 4.846 3.02 9.06 6.47 11.23a10.8 10.8 0 006.47-11.23l-5.47-1.417z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="text-center py-4">
                            <div class="text-5xl font-black tracking-tighter mb-2">{{ Math.round((stats.verified / (stats.total || 1)) * 100) }}%</div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Email Verification Rate</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-8 pt-8 border-t border-white/10">
                        <div class="text-center">
                            <div class="text-xl font-bold text-white tracking-widest">{{ stats.unverified }}</div>
                            <div class="text-[9px] font-black uppercase text-slate-500 tracking-widest">Unverified</div>
                        </div>
                        <div class="text-center border-l border-white/10">
                            <div class="text-xl font-bold text-emerald-500 tracking-widest">{{ stats.active }}</div>
                            <div class="text-[9px] font-black uppercase text-slate-500 tracking-widest">Active Users</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Intelligent Command Center / Trends -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 px-1 stagger-3">
                <TrendCard 
                    label="Active Users" 
                    :value="stats.active" 
                    :trend="[40, 42, 45, 43, 48, 50, 52]" 
                    percentage="+8%"
                    color="emerald"
                />
                <TrendCard 
                    label="Total Users" 
                    :value="stats.total" 
                    :trend="[100, 110, 115, 120, 125, 130, 135]" 
                    percentage="+5%"
                    color="slate"
                />
                <TrendCard 
                    label="Verified Users" 
                    :value="stats.verified" 
                    :trend="[60, 65, 70, 68, 75, 80, 85]" 
                    percentage="+14%"
                    color="blue"
                />
                <TrendCard 
                    label="Unverified Users" 
                    :value="stats.unverified" 
                    :trend="[10, 8, 12, 5, 4, 3, 2]" 
                    percentage="-65%"
                    color="rose"
                />
            </div>

            <!-- Table Section Headers -->
            <div class="flex items-center justify-between px-6 stagger-4 mt-12">
                <div class="space-y-0.5">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Recent User Activity</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-none">Overview of the latest registered users</p>
                </div>
                <div class="flex items-center gap-3">
                     <button
                        @click="showFilters = !showFilters"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest border border-slate-200 hover:border-slate-900 transition-all flex items-center gap-2"
                    >
                        Search & Filter
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                    <Link :href="route('admin.users.create')" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">New User</Link>
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
                     <div class="md:col-span-2 relative group">
                        <svg class="absolute left-4 top-3 h-5 w-5 text-slate-400 group-focus-within:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" /></svg>
                        <input v-model="searchInput" @keyup.enter="applyFilter('search', searchInput)" class="block w-full rounded-xl border-none bg-slate-100/50 px-4 py-3 pl-12 text-sm placeholder:text-slate-500 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="Search users..." />
                    </div>
                    <select v-model="filters.status" @change="applyFilter('status', $event.target.value)" class="block rounded-xl border-none bg-slate-100/50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white transition-all"><option value="">All Status</option><option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option></select>
                    <select v-model="filters.role" @change="applyFilter('role', $event.target.value)" class="block rounded-xl border-none bg-slate-100/50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white transition-all"><option value="">All Roles</option><option v-for="role in rolesList" :key="role.id" :value="role.id">{{ role.name }}</option></select>
                </div>
            </transition>

            <!-- Executive User Directory -->
            <div class="bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden stagger-5 relative">
                <!-- Directory Control Header -->
                <div class="bg-slate-50/50 border-b border-slate-100 px-8 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" :checked="selectedUsers.length === users.data.length && users.data.length > 0" @change="toggleAllUsers" class="rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20" />
                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Active Users</span>
                    </div>
                </div>

                <div v-if="users.data.length > 0" class="divide-y divide-slate-50">
                    <div
                        v-for="(user, index) in users.data"
                        :key="user.id"
                        class="flex flex-col lg:flex-row lg:items-center justify-between py-4 px-8 hover:bg-slate-50/50 transition-all cursor-pointer group relative"
                        :class="[selectedUsers.includes(user.id) ? 'bg-slate-50/80 text-slate-900 shadow-sm' : '', `stagger-${Math.min(index + 5, 5)}`]"
                        @click="viewUser(user.id)"
                    >
                        <!-- Selection Indicator -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-900 scale-y-0 group-hover:scale-y-100 transition-transform origin-center"></div>

                        <!-- Selection Checkbox -->
                        <div class="relative z-10 flex-shrink-0 mr-4" @click.stop>
                            <input 
                                type="checkbox" 
                                :checked="selectedUsers.includes(user.id)" 
                                @change="toggleUserSelection(user.id)" 
                                class="h-4 w-4 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20 cursor-pointer" 
                            />
                        </div>

                        <!-- User Profile -->
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="relative flex-shrink-0 group-hover:scale-105 transition-transform duration-500">
                                <img
                                    class="h-12 w-12 rounded-xl object-cover ring-2 ring-white shadow-lg shadow-slate-200 group-hover:shadow-slate-300 transition-all"
                                    :src="user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.display_name) + '&size=64&background=f1f5f9&color=64748b&bold=true'"
                                    :alt="user.display_name"
                                />
                                <div class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-white flex items-center justify-center transition-all bg-white shadow-sm" 
                                     :class="user.is_active ? 'text-emerald-500' : 'text-slate-300'">
                                    <div :class="user.is_active ? 'bg-emerald-500' : 'bg-slate-300'" class="h-1.5 w-1.5 rounded-full"></div>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-black text-slate-900 truncate tracking-tight group-hover:text-slate-700 transition-colors">{{ user.display_name }}</h4>
                                    <span v-if="user.email_verified" class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 text-[8px] font-black text-blue-600 uppercase border border-blue-100 tracking-tighter">Verified</span>
                                </div>
                                <div class="flex items-center gap-3 mt-0.5">
                                    <button @click.stop="copyToClipboard(user.email)" class="text-[11px] font-bold text-slate-400 truncate hover:text-slate-900 transition-colors flex items-center gap-1 group/email">
                                        {{ user.email }}
                                    </button>
                                    <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-300">@{{ user.username }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Management Details -->
                        <div class="mt-6 lg:mt-0 flex items-center gap-12 lg:px-10">
                            <div class="flex flex-col gap-1 min-w-[120px]">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] leading-none">Role</span>
                                <span class="text-xs font-black text-slate-600 uppercase tracking-tighter">{{ user.role }}</span>
                            </div>
                            <div class="hidden sm:flex flex-col gap-1 min-w-[120px]">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] leading-none">Last Login</span>
                                <span class="text-xs font-bold text-slate-500">{{ formatDate(user.last_login) }}</span>
                            </div>
                        </div>

                        <!-- Action Cluster -->
                        <div class="mt-6 lg:mt-0 flex items-center justify-end gap-1.5" @click.stop>
                             <div class="flex items-center gap-1 p-1 bg-slate-50 rounded-2xl border border-slate-100 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-4 group-hover:translate-x-0">
                                <button @click="impersonateUser(user.id)" v-if="user.is_active" class="p-2.5 rounded-xl text-slate-400 hover:text-amber-600 hover:bg-white transition-all shadow-sm" title="Impersonate"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></button>
                                <Link :href="route('admin.users.edit', user.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-slate-900 hover:bg-white transition-all shadow-sm" title="Edit"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></Link>
                                <button @click="deleteUser(user.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-white transition-all shadow-sm" title="Delete"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
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
                    <h4 class="text-xl font-black text-slate-900 tracking-tight mb-2">No users found</h4>
                    <p class="text-slate-500 max-w-xs font-medium italic">Your current filters or search query did not yield any results from the database.</p>
                    <button @click="resetFilters" class="mt-8 text-sm font-bold text-slate-900 border-b-2 border-slate-900 pb-0.5 hover:text-slate-600 hover:border-slate-600 transition-all">Reset Filters</button>
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