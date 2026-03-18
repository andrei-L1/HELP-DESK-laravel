<script setup>
import { ref, watch } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    departments: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            total_departments: 0,
            active_departments: 0,
            total_users: 0,
            total_tickets: 0,
            departments_with_managers: 0,
        }),
    },
});

// Modal state
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const departmentToDelete = ref(null);
const selectedDepartment = ref(null);
const modalInitialized = ref(false);

const createForm = useForm({
    name: '',
    short_code: '',
    description: '',
    manager_id: '',
    is_active: true,
});

const editForm = useForm({
    id: null,
    name: '',
    short_code: '',
    description: '',
    manager_id: '',
    is_active: true,
});

// Modal management
const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
    createForm.is_active = true;
    setTimeout(() => modalInitialized.value = true, 100);
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
    modalInitialized.value = false;
};

const openEditModal = (dept) => {
    editForm.id = dept.id;
    editForm.name = dept.name;
    editForm.short_code = dept.short_code || '';
    editForm.description = dept.description || '';
    editForm.manager_id = dept.manager_id || '';
    editForm.is_active = dept.is_active;
    showEditModal.value = true;
    setTimeout(() => modalInitialized.value = true, 100);
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
    modalInitialized.value = false;
};

const openViewModal = (dept) => {
    selectedDepartment.value = dept;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedDepartment.value = null;
};

const openDeleteModal = (dept) => {
    departmentToDelete.value = dept;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    departmentToDelete.value = null;
};

// Form submissions
const submitCreate = () => {
    createForm.post(route('admin.departments.store'), {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

const submitEdit = () => {
    editForm.put(route('admin.departments.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => closeEditModal(),
    });
};

const confirmDelete = () => {
    if (departmentToDelete.value) {
        router.delete(route('admin.departments.destroy', departmentToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => closeDeleteModal(),
        });
    }
};

// Filtering
const searchInput = ref(props.filters.search || '');

const applyFilter = (key, value) => {
    router.get(
        route('admin.departments.index'),
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
    searchInput.value = '';
    router.get(
        route('admin.departments.index'),
        {},
        {
            preserveState: false,
            preserveScroll: true,
        }
    );
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusBadgeStyle = (isActive) => {
    return isActive 
        ? { backgroundColor: 'rgba(16, 185, 129, 0.1)', color: '#059669', borderColor: 'rgba(16, 185, 129, 0.2)' }
        : { backgroundColor: 'rgba(244, 63, 94, 0.1)', color: '#e11d48', borderColor: 'rgba(244, 63, 94, 0.2)' };
};

const getDeptIconColor = (name) => {
    const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    const colors = [
        'bg-slate-900 shadow-slate-200',
        'bg-indigo-600 shadow-indigo-200',
        'bg-blue-600 shadow-blue-200',
        'bg-emerald-600 shadow-emerald-200',
        'bg-amber-600 shadow-amber-200',
        'bg-rose-600 shadow-rose-200',
        'bg-violet-600 shadow-violet-200',
    ];
    return colors[hash % colors.length];
};
</script>

<template>
    <Head title="Departments" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">Departments</span>
            </div>
        </template>

        <template #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900 uppercase">Departments</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-10 pb-20 pt-4">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-4 stagger-1">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Department List</h2>
                    <p class="text-slate-500 font-medium italic">Manage your company departments and team assignments.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-900 text-white text-sm font-bold shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        New Department
                    </button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-8 md:grid-cols-4 px-1 stagger-2">
                <div v-for="stat in [
                    { label: 'Total Departments', value: stats.total_departments || departments.total || 0, color: 'slate' },
                    { label: 'Active', value: stats.active_departments || 0, color: 'emerald' },
                    { label: 'Total Users', value: stats.total_users || 0, color: 'indigo' },
                    { label: 'Ticket Count', value: stats.total_tickets || 0, color: 'amber' }
                ]" :key="stat.label" class="group relative py-2">
                    <div class="flex flex-col">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">{{ stat.label }}</p>
                        <p class="text-3xl font-bold text-slate-900 tracking-tighter">{{ stat.value }}</p>
                        <div class="mt-4 h-1 w-8 rounded-full bg-slate-100 overflow-hidden">
                            <div :class="{
                                'bg-slate-900': stat.color === 'slate',
                                'bg-emerald-500': stat.color === 'emerald',
                                'bg-indigo-600': stat.color === 'indigo',
                                'bg-amber-500': stat.color === 'amber'
                            }" class="h-full w-full opacity-50 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 stagger-3 overflow-hidden">
                <div class="p-6 flex flex-col lg:flex-row lg:items-center gap-6">
                    <div class="flex-1 relative group">
                        <svg class="absolute left-4 top-3.5 h-5 w-5 text-slate-400 group-focus-within:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                        <input
                            type="text"
                            v-model="searchInput"
                            @keyup.enter="applyFilter('search', searchInput)"
                            class="block w-full rounded-xl border-none bg-slate-100/50 px-4 py-4 pl-12 text-sm placeholder:text-slate-500 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all font-bold text-slate-700"
                            placeholder="Search by name, code or manager..."
                        />
                    </div>
                    <div class="flex items-center gap-4">
                        <select
                            :value="filters.status || ''"
                            @change="applyFilter('status', $event.target.value)"
                            class="rounded-xl border-none bg-slate-100/50 px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-600 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all appearance-none cursor-pointer pr-12"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button
                            v-if="filters.search || filters.status"
                            @click="resetFilters"
                            class="p-4 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all border border-transparent hover:border-rose-100"
                            title="Reset filters"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Department Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 stagger-4 px-4 lg:px-0">
                <div
                    v-for="(dept, index) in departments.data"
                    :key="dept.id"
                    class="group relative bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 p-8 transition-all hover:shadow-xl hover:-translate-y-1.5 cursor-pointer overflow-hidden"
                    @click="router.visit(route('admin.departments.show', dept.id))"
                >
                    <!-- Background Accent -->
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-slate-50 border border-slate-100/50 -z-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

                    <div class="relative z-10">
                        <div class="flex items-start justify-between mb-8">
                            <div class="flex items-center gap-5">
                                <div class="h-14 w-14 rounded-2xl flex items-center justify-center text-white text-xl font-black shadow-lg" :class="getDeptIconColor(dept.name)">
                                    {{ (dept.short_code || dept.name.charAt(0)).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-lg font-black text-slate-900 tracking-tight truncate border-b-2 border-transparent group-hover:border-slate-900 transition-all inline-block">{{ dept.name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ dept.short_code || 'No Code' }}</span>
                                        <span class="h-1 w-1 bg-slate-200 rounded-full"></span>
                                        <span 
                                            class="text-[9px] font-black uppercase tracking-tight px-2 py-0.5 rounded-md border"
                                            :style="getStatusBadgeStyle(dept.is_active)"
                                        >
                                            {{ dept.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0" @click.stop>
                                 <button
                                    @click="openEditModal(dept)"
                                    class="p-2.5 rounded-xl text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                 <button
                                    @click="openDeleteModal(dept)"
                                    class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <p class="text-xs font-semibold text-slate-500 leading-relaxed mb-8 line-clamp-2 italic min-h-[3rem]">
                            {{ dept.description || 'No description provided for this department.' }}
                        </p>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-2">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Manager</span>
                                    <span class="text-[11px] font-bold text-slate-700 truncate max-w-[120px]">{{ dept.manager_name || 'Not assigned' }}</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex flex-col items-end">
                                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest text-right">Users</span>
                                        <span class="text-[11px] font-black text-slate-900">{{ dept.users_count || 0 }} Members</span>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest text-right">Tickets</span>
                                        <span class="text-[11px] font-black text-slate-900">{{ dept.tickets_count || 0 }} Cases</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Indicator (subtle) -->
                            <div class="h-1.5 w-full bg-slate-50 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-slate-900 transition-all duration-1000 group-hover:w-full opacity-10" :style="{ width: '10%' }"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 mt-6 border-t border-slate-50/80">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-slate-900 transition-colors flex items-center gap-1.5">
                                View Details
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                            </span>
                            <span class="text-[9px] font-bold text-slate-300 uppercase">{{ formatDate(dept.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="departments.data.length === 0" class="col-span-full py-32 flex flex-col items-center text-center px-4 stagger-5">
                    <div class="relative mb-8">
                        <div class="absolute inset-0 bg-slate-100 rounded-full blur-3xl opacity-50 scale-150"></div>
                        <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-white shadow-xl shadow-slate-200 border border-slate-100 italic font-black text-slate-200 text-3xl">Ø</div>
                    </div>
                    <h4 class="text-xl font-black text-slate-900 tracking-tight mb-2">No departments found</h4>
                    <p class="text-slate-500 max-w-xs font-medium italic">All departments will appear here once they are created.</p>
                    <button @click="resetFilters" class="mt-8 text-sm font-bold text-slate-900 border-b-2 border-slate-900 pb-0.5 hover:text-slate-600 hover:border-slate-600 transition-all">Clear Filters</button>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="departments.links && departments.links.length > 3"
                class="flex flex-col sm:flex-row items-center justify-between px-4 py-8 stagger-5"
            >
                <div class="mb-4 sm:mb-0">
                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                        Showing <span class="text-slate-900">{{ departments.from || 0 }}</span> - <span class="text-slate-900">{{ departments.to || 0 }}</span> of <span class="text-slate-900">{{ departments.total || 0 }}</span> Entries
                    </p>
                </div>
                <div class="flex items-center gap-1.5 p-1.5 bg-white rounded-xl border border-slate-300/40 shadow-sm shadow-slate-200/40">
                    <Link
                        v-for="link in departments.links"
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

        <!-- Modals -->
        <Teleport to="body">
            <!-- Create/Edit Modal -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
                    <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-900/20 w-full max-w-xl overflow-hidden border border-slate-100">
                        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">
                                    {{ showCreateModal ? 'Create Department' : 'Edit Department' }}
                                </h3>
                                <p class="text-xs font-bold text-slate-400 mt-0.5">Set the department name, code, and manager.</p>
                            </div>
                            <button @click="showCreateModal ? closeCreateModal() : closeEditModal()" class="p-2.5 rounded-2xl hover:bg-slate-50 text-slate-400 transition-colors border border-slate-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form @submit.prevent="showCreateModal ? submitCreate() : submitEdit()" class="p-10 space-y-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5 md:col-span-1">
                                    <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Department Name *</label>
                                    <input 
                                        v-model="(showCreateModal ? createForm : editForm).name" 
                                        type="text" 
                                        required 
                                        class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner" 
                                        placeholder="e.g. IT Support" 
                                    />
                                    <p v-if="(showCreateModal ? createForm : editForm).errors.name" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ (showCreateModal ? createForm : editForm).errors.name }}</p>
                                </div>
                                <div class="space-y-1.5 md:col-span-1">
                                    <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Short Code</label>
                                    <input 
                                        v-model="(showCreateModal ? createForm : editForm).short_code" 
                                        type="text" 
                                        class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner" 
                                        placeholder="e.g. IT" 
                                    />
                                    <p v-if="(showCreateModal ? createForm : editForm).errors.short_code" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ (showCreateModal ? createForm : editForm).errors.short_code }}</p>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Description</label>
                                <textarea 
                                    v-model="(showCreateModal ? createForm : editForm).description" 
                                    rows="3" 
                                    class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all resize-none shadow-inner" 
                                    placeholder="Enter department description..."
                                ></textarea>
                            </div>

                            <div class="space-y-1.5">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Manager</label>
                                <select 
                                    v-model="(showCreateModal ? createForm : editForm).manager_id"
                                    class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner cursor-pointer"
                                >
                                    <option value="">No Manager Assigned</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-center px-2 group cursor-pointer" @click="(showCreateModal ? createForm : editForm).is_active = !(showCreateModal ? createForm : editForm).is_active">
                                <div 
                                    class="h-6 w-11 rounded-full p-1 transition-colors duration-200"
                                    :class="(showCreateModal ? createForm : editForm).is_active ? 'bg-emerald-500' : 'bg-slate-200'"
                                >
                                    <div 
                                        class="h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"
                                        :class="(showCreateModal ? createForm : editForm).is_active ? 'translate-x-5' : 'translate-x-0'"
                                    ></div>
                                </div>
                                <span class="ml-4 text-xs font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors">Active Status</span>
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-slate-50">
                                <button type="button" @click="showCreateModal ? closeCreateModal() : closeEditModal()" class="flex-1 px-8 py-4 rounded-2xl bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                                <button 
                                    type="submit" 
                                    :disabled="(showCreateModal ? createForm : editForm).processing" 
                                    class="flex-[2] px-8 py-4 rounded-2xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50"
                                >
                                    {{ (showCreateModal ? createForm : editForm).processing ? 'Processing...' : (showCreateModal ? 'Create Department' : 'Save Changes') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>

            <!-- Delete Confirmation -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showDeleteModal && departmentToDelete" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
                    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden border border-rose-100">
                        <div class="p-10 text-center">
                            <div class="h-24 w-24 rounded-[2rem] bg-rose-50 flex items-center justify-center text-rose-500 mx-auto mb-8 shadow-inner">
                                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Delete Department?</h3>
                            <p class="text-sm font-medium text-slate-500 mb-8 leading-relaxed">
                                Are you sure you want to delete <span class="text-slate-900 font-black">"{{ departmentToDelete.name }}"</span>? 
                                <br><span class="text-rose-600 font-bold">This will remove all {{ departmentToDelete.users_count }} user assignments.</span>
                            </p>

                            <div class="flex flex-col gap-3">
                                <button @click="confirmDelete" class="w-full px-8 py-4 rounded-2xl bg-rose-600 text-white font-black text-xs uppercase tracking-widest shadow-xl shadow-rose-200 hover:bg-rose-700 transition-all active:scale-95">Delete Department</button>
                                <button @click="closeDeleteModal" class="w-full px-8 py-4 rounded-2xl bg-slate-100 text-slate-600 font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminNavigation>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

.stagger-1 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.stagger-2 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; opacity: 0; }
.stagger-3 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }
.stagger-4 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards; opacity: 0; }
.stagger-5 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.4s forwards; opacity: 0; }

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 1.25rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; }
</style>