<script setup>
import { ref, watch, computed } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
            status: null,
            role: null,
            sort: 'latest',
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
    view: {
        type: String,
        default: 'all',
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            inactive: 0,
            verified: 0,
            unverified: 0,
        }),
    },
});

// Create user modal logic
const showCreateModal = ref(false);
const modalInitialized = ref(false); // Fix autofocus warning

const createForm = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    first_name: '',
    last_name: '',
    middle_name: '',
    display_name: '',
    role_id: '',
    phone: '',
    timezone: 'Asia/Manila',
    is_active: true,
});

const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
    setTimeout(() => {
        modalInitialized.value = true;
    }, 100);
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
    modalInitialized.value = false;
};

const submitCreate = () => {
    createForm.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

// Roles transformation
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

// Filter logic
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

const applyFilter = (key, value) => {
    router.get(
        route('admin.users.all'),
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
        route('admin.users.all'),
        {},
        {
            preserveState: false,
            preserveScroll: true,
        }
    );
};

const setQuickFilter = (status = null, role = null) => {
    router.get(
        route('admin.users.all'),
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

const getRoleTabClass = (role) => {
    const r = role?.toLowerCase() || '';
    if (r.includes('admin')) return 'bg-rose-500 text-white shadow-lg shadow-rose-100';
    if (r.includes('agent') || r.includes('staff')) return 'bg-blue-500 text-white shadow-lg shadow-blue-100';
    if (r.includes('manager')) return 'bg-amber-500 text-white shadow-lg shadow-amber-100';
    return 'bg-slate-900 text-white shadow-lg shadow-slate-200';
};

// Table actions
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

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
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
                <span class="text-slate-900">User List</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-10 pb-20 pt-4">
            <!-- Modal (Teleported) -->
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
                        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-900/20 w-full max-w-2xl overflow-hidden border border-slate-100">
                            <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Create New User</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest italic">Add a new user account to the system.</p>
                                </div>
                                <button @click="closeCreateModal" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 transition-colors">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <form @submit.prevent="submitCreate" class="p-8 space-y-6 max-h-[75vh] overflow-y-auto custom-scrollbar">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">First Name *</label>
                                        <input v-model="createForm.first_name" type="text" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="John" />
                                        <p v-if="createForm.errors.first_name" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ createForm.errors.first_name }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Last Name *</label>
                                        <input v-model="createForm.last_name" type="text" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="Doe" />
                                        <p v-if="createForm.errors.last_name" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ createForm.errors.last_name }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Middle Name</label>
                                        <input v-model="createForm.middle_name" type="text" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Display Name</label>
                                        <input v-model="createForm.display_name" type="text" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Username *</label>
                                        <input v-model="createForm.username" type="text" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="johndoe" />
                                        <p v-if="createForm.errors.username" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ createForm.errors.username }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Email Address *</label>
                                        <input v-model="createForm.email" type="email" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="john@example.com" />
                                        <p v-if="createForm.errors.email" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ createForm.errors.email }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Phone Number</label>
                                        <input v-model="createForm.phone" type="text" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Role *</label>
                                        <select v-model="createForm.role_id" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all">
                                            <option value="">Choose a role</option>
                                            <option v-for="role in rolesList" :key="role.id" :value="role.id">{{ role.name }}</option>
                                        </select>
                                        <p v-if="createForm.errors.role_id" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ createForm.errors.role_id }}</p>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Timezone</label>
                                    <select v-model="createForm.timezone" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all">
                                        <option value="Asia/Manila">Asia/Manila (GMT+8)</option>
                                        <option value="UTC">UTC</option>
                                        <option value="America/New_York">America/New York</option>
                                        <option value="America/Chicago">America/Chicago</option>
                                        <option value="America/Denver">America/Denver</option>
                                        <option value="America/Los_Angeles">America/Los Angeles</option>
                                        <option value="Europe/London">Europe/London</option>
                                        <option value="Europe/Paris">Europe/Paris</option>
                                        <option value="Asia/Tokyo">Asia/Tokyo</option>
                                        <option value="Asia/Singapore">Asia/Singapore</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Password *</label>
                                        <input v-model="createForm.password" type="password" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="••••••••" />
                                        <p v-if="createForm.errors.password" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ createForm.errors.password }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Confirm Password *</label>
                                        <input v-model="createForm.password_confirmation" type="password" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" placeholder="••••••••" />
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <label class="flex items-center gap-3 cursor-pointer group w-fit">
                                        <input v-model="createForm.is_active" type="checkbox" class="h-5 w-5 rounded-lg border-slate-200 text-slate-900 focus:ring-slate-900/10 transition-all" />
                                        <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">Activate account immediately</span>
                                    </label>
                                </div>

                                <div class="flex items-center gap-4 pt-4 border-t border-slate-50">
                                    <button type="button" @click="closeCreateModal" class="flex-1 px-6 py-4 rounded-2xl bg-slate-50 text-slate-600 text-sm font-bold hover:bg-slate-100 transition-all">Discard</button>
                                    <button type="submit" :disabled="createForm.processing" class="flex-[2] px-6 py-4 rounded-2xl bg-slate-900 text-white text-sm font-bold shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50">
                                        {{ createForm.processing ? 'Saving...' : 'Create User' }}
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
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">User Directory</h2>
                    <p class="text-slate-500 font-medium">Browse and manage every registered user account in the system.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-900 text-white text-sm font-bold shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        New User
                    </button>
                </div>
            </div>

            <!-- Stats Overview / Upgraded with Trends -->
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4 px-1 stagger-2">
                <TrendCard 
                    label="Total Users" 
                    :value="stats?.total || 0" 
                    :trend="[150, 155, 148, 160, 165, 172, 180]" 
                    percentage="+14%"
                    color="slate"
                />
                <TrendCard 
                    label="Active Users" 
                    :value="stats?.active || 0" 
                    :trend="[120, 125, 130, 128, 135, 140, 145]" 
                    percentage="+12%"
                    color="emerald"
                    @click="setQuickFilter('active', null)"
                    class="cursor-pointer"
                />
                <TrendCard 
                    label="Unverified Users" 
                    :value="stats?.unverified || 0" 
                    :trend="[15, 12, 18, 10, 8, 14, 22]" 
                    percentage="+8%"
                    color="rose"
                    @click="setQuickFilter('inactive', null)"
                    class="cursor-pointer"
                />
                <TrendCard 
                    label="Total Roles" 
                    :value="rolesList?.length || 0" 
                    :trend="[4, 4, 4, 5, 5, 5, 5]" 
                    percentage="+20%"
                    color="amber"
                />
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
                            placeholder="Search by name, username or email..."
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
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Role</label>
                                <select
                                    v-model="filters.role"
                                    @change="applyFilter('role', $event.target.value)"
                                    class="block w-full rounded-xl border-none bg-white shadow-sm ring-1 ring-slate-200 px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/10 transition-all"
                                >
                                    <option value="">All Roles</option>
                                    <option v-for="role in rolesList" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                            </div>

                            <!-- Sort By -->
                            <div class="space-y-3">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Sort By</label>
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
                <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-slate-50 to-transparent z-10 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide px-2">
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button @click="setQuickFilter(null, null)" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap" :class="[!filters.status && !filters.role ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-400 hover:text-slate-900 hover:bg-white']">Overview</button>
                        <button @click="setQuickFilter('active', null)" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap" :class="[filters.status === 'active' && !filters.role ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-100' : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50/50']">Active <span v-if="stats?.active > 0" class="opacity-60 text-[9px] font-bold">{{ stats.active }}</span></button>
                        <button @click="setQuickFilter('inactive', null)" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 whitespace-nowrap" :class="[filters.status === 'inactive' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-400 hover:text-slate-900 hover:bg-slate-100']">Suspended</button>
                    </div>
                    <div class="h-4 w-px bg-slate-200 mx-2 shrink-0"></div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button v-for="role in rolesList" :key="role.id" @click="setQuickFilter(null, role.id)" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap" :class="[filters.role == role.id ? getRoleTabClass(role.name) : 'text-slate-400 hover:text-slate-900 hover:bg-white']">{{ role.name }}</button>
                    </div>
                </div>
            </div>

            <!-- Power Console / High-Density Table -->
            <div class="bg-white rounded-3xl border border-slate-300/40 shadow-xl shadow-slate-200/40 overflow-hidden stagger-4 relative">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="py-5 px-7 w-12">
                                    <input type="checkbox" :checked="selectedUsers.length === users.data.length && users.data.length > 0" @change="toggleAllUsers" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900/20" />
                                </th>
                                <th class="py-5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                                <th class="py-5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">Role</th>
                                <th class="py-5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell">Status</th>
                                <th class="py-5 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:table-cell">Last Login</th>
                                <th class="py-5 px-7 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody v-if="users.data.length > 0" class="divide-y divide-slate-50">
                            <tr
                                v-for="(user, index) in users.data"
                                :key="user.id"
                                @click="viewUser(user.id)"
                                class="group hover:bg-slate-50/50 transition-all cursor-pointer relative"
                                :class="[selectedUsers.includes(user.id) ? 'bg-slate-50' : '', `stagger-${Math.min(index + 5, 5)}`]"
                            >
                                <td class="py-4 px-7" @click.stop>
                                    <input type="checkbox" :checked="selectedUsers.includes(user.id)" @change="toggleUserSelection(user.id)" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900/20" />
                                </td>
                                <td class="py-4 px-4 min-w-[300px]">
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex-shrink-0 transition-transform group-hover:scale-105 duration-300">
                                            <img
                                                class="h-11 w-11 rounded-xl object-cover ring-2 ring-slate-100 group-hover:ring-white transition-all shadow-sm"
                                                :src="user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.full_name || user.display_name || user.first_name) + '&size=64&background=f1f5f9&color=64748b&bold=true'"
                                                :alt="user.display_name"
                                            />
                                            <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white flex items-center justify-center transition-all shadow-sm" 
                                                 :class="user.is_active ? 'bg-emerald-500' : 'bg-slate-300'">
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-slate-900 truncate tracking-tight">{{ user.full_name || user.display_name || (user.first_name + ' ' + user.last_name) }}</span>
                                                <div v-if="user.email_verified" class="h-4 w-4 rounded-full bg-blue-500 flex items-center justify-center border border-white shrink-0 shadow-sm">
                                                    <svg class="h-2.5 w-2.5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-[10px] font-bold text-slate-400 truncate">@{{ user.username }}</p>
                                                <span class="h-0.5 w-3 bg-slate-100 rounded-full"></span>
                                                <p class="text-[10px] font-bold text-slate-400 truncate">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 hidden md:table-cell">
                                    <span class="px-2.5 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-tighter" :class="getRoleClass(user.role_name || user.role)">
                                        {{ user.role_name || user.role }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 hidden lg:table-cell">
                                    <div class="flex items-center gap-2.5">
                                        <div :class="user.is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-slate-300'" class="h-2 w-2 rounded-full"></div>
                                        <span class="text-[11px] font-black uppercase tracking-widest" :class="user.is_active ? 'text-slate-700' : 'text-slate-400'">{{ user.is_active ? 'Active' : 'Suspended' }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 hidden sm:table-cell">
                                    <span class="text-xs font-bold text-slate-500">{{ formatDate(user.last_login) }}</span>
                                </td>
                                <td class="py-4 px-7 text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="impersonateUser(user.id)" v-if="user.is_active" class="p-2.5 rounded-xl text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all" title="Impersonate"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></button>
                                        <Link :href="route('admin.users.edit', user.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all font-bold" title="Edit"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></Link>
                                        <button @click="deleteUser(user.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all" title="Delete"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="py-40 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-20 w-20 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200 mb-6">
                                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <h4 class="text-xl font-black text-slate-900 tracking-tight">No Results Found</h4>
                                        <p class="text-xs font-bold text-slate-400 mt-2 max-w-xs">Your current filters or search query did not match any user records.</p>
                                        <button @click="resetFilters" class="mt-8 px-6 py-2.5 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-slate-200">Reset Filters</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modern Pagination -->
            <div v-if="users.links && users.links.length > 3" class="flex flex-col sm:flex-row items-center justify-between px-4 py-8 stagger-5">
                <div class="mb-4 sm:mb-0"><p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Showing <span class="text-slate-900">{{ users.from || 0 }}</span> - <span class="text-slate-900">{{ users.to || 0 }}</span> of <span class="text-slate-900">{{ users.total || 0 }}</span> entries</p></div>
                <div class="flex items-center gap-1.5 p-1.5 bg-white rounded-xl border border-slate-300/40 shadow-sm shadow-slate-200/40">
                    <Link v-for="link in users.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="h-10 min-w-[40px] px-3 flex items-center justify-center rounded-lg text-xs font-black transition-all" :class="[link.active ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50', !link.url ? 'opacity-30 cursor-not-allowed' : '']" />
                </div>
            </div>
        </div>

        <!-- Floating Bulk Actions Bar -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-y-full opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-full opacity-0">
            <div v-if="selectedUsers.length > 0" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50">
                <div class="bg-slate-900 text-white rounded-2xl shadow-2xl shadow-slate-900/20 px-6 py-4 flex items-center gap-6 min-w-[400px]">
                    <div class="flex items-center gap-3 pr-6 border-r border-slate-800"><span class="h-6 w-6 rounded-lg bg-white/10 flex items-center justify-center text-[10px] font-black">{{ selectedUsers.length }}</span><span class="text-xs font-bold tracking-tight">Users Selected</span></div>
                        <button @click="bulkDeactivate" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-white/10 transition-colors text-xs font-bold">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                            Suspend
                        </button>
                        <button @click="bulkDelete" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-rose-500 transition-colors text-xs font-bold text-rose-400 hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Delete
                        </button>
                    <button @click="selectedUsers = []" class="text-slate-500 hover:text-white transition-colors"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
            </div>
        </transition>
    </AdminNavigation>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; appearance: none; }
</style>