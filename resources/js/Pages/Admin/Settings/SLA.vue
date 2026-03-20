<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    policies: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
        }),
    },
    priorities: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            priority: '',
            department: '',
        }),
    },
});

// Create/Edit modal
const showModal = ref(false);
const editingPolicy = ref(null);
const successMessage = ref('');
const showSuccess = ref(false);

const form = useForm({
    id: null,
    name: '',
    description: '',
    priority_id: '',
    department_id: '',
    response_time: 60, // in minutes
    resolution_time: 240, // in minutes
    escalate_after: 120, // in minutes
    notify_before: 30, // in minutes
    notify_escalation: true,
    is_active: true,
    business_hours_only: true,
    calendar_id: null,
});

// Delete modal
const showDeleteModal = ref(false);
const policyToDelete = ref(null);

// Filter state
const searchInput = ref(props.filters.search || '');

// Trigger success message
const triggerSuccess = (msg) => {
    successMessage.value = msg;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 5000);
};

// Modal functions
const openCreateModal = () => {
    editingPolicy.value = null;
    form.reset();
    form.priority_id = '';
    form.department_id = '';
    form.response_time = 60;
    form.resolution_time = 240;
    form.escalate_after = 120;
    form.notify_before = 30;
    form.notify_escalation = true;
    form.is_active = true;
    form.business_hours_only = true;
    showModal.value = true;
};

const openEditModal = (policy) => {
    editingPolicy.value = policy;
    form.id = policy.id;
    form.name = policy.name;
    form.description = policy.description || '';
    form.priority_id = policy.priority_id;
    form.department_id = policy.department_id || '';
    form.response_time = policy.response_time;
    form.resolution_time = policy.resolution_time;
    form.escalate_after = policy.escalate_after || 120;
    form.notify_before = policy.notify_before || 30;
    form.notify_escalation = policy.notify_escalation ?? true;
    form.is_active = policy.is_active ?? true;
    form.business_hours_only = policy.business_hours_only ?? true;
    form.calendar_id = policy.calendar_id;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingPolicy.value = null;
};

const openDeleteModal = (policy) => {
    policyToDelete.value = policy;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    policyToDelete.value = null;
};

// Form submissions
const submit = () => {
    if (editingPolicy.value) {
        form.put(route('admin.settings.sla.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                triggerSuccess('SLA Policy updated successfully');
            },
        });
    } else {
        form.post(route('admin.settings.sla.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                triggerSuccess('New SLA Policy created successfully');
            },
        });
    }
};

const confirmDelete = () => {
    if (policyToDelete.value) {
        router.delete(route('admin.settings.sla.destroy', policyToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteModal();
                triggerSuccess('SLA Policy deleted successfully');
            },
        });
    }
};

// Filter functions
const applyFilter = (key, value) => {
    router.get(
        route('admin.settings.sla'),
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
        route('admin.settings.sla'),
        {},
        {
            preserveState: false,
            preserveScroll: true,
        }
    );
};

// Helper functions
const formatMinutes = (minutes) => {
    if (!minutes) return '—';
    
    if (minutes < 60) {
        return `${minutes}m`;
    }
    
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    
    if (remainingMinutes === 0) {
        return `${hours}h`;
    }
    
    return `${hours}h ${remainingMinutes}m`;
};

const getPriorityName = (priorityId) => {
    const priority = props.priorities.find(p => p.id === priorityId);
    return priority ? priority.name : 'Unknown';
};

const getPriorityColor = (priorityId) => {
    const priority = props.priorities.find(p => p.id === priorityId);
    const colors = {
        'urgent': 'border-rose-100 bg-rose-50 text-rose-700 font-black',
        'high': 'border-orange-100 bg-orange-50 text-orange-700 font-black',
        'medium': 'border-amber-100 bg-amber-50 text-amber-700 font-black',
        'low': 'border-emerald-100 bg-emerald-50 text-emerald-700 font-black',
    };
    return colors[priority?.name?.toLowerCase()] || 'border-slate-100 bg-slate-50 text-slate-700 font-black';
};

const getDepartmentName = (deptId) => {
    if (!deptId) return 'All Departments';
    const dept = props.departments.find(d => d.id === deptId);
    return dept ? dept.name : 'Unknown';
};

watch(() => props.filters.search, (val) => {
    searchInput.value = val || '';
});
</script>

<template>
    <Head title="SLA Policies" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex flex-col">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 stagger-1">
                    <Link :href="route('admin.settings.index')" class="hover:text-slate-900 transition-colors">Settings</Link>
                    <span>/</span>
                    <span class="text-slate-900">SLA Policies</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight stagger-1">SLA Policies</h1>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-8 pb-20 pt-4">
            <!-- Alert Messages -->
            <div class="px-2 stagger-1">
                <Transition
                    enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-x-4"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition duration-300 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="showSuccess" class="flex items-center justify-between gap-3 px-6 py-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-bold shadow-sm">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            {{ successMessage }}
                        </div>
                        <button @click="showSuccess = false" class="text-emerald-400 hover:text-emerald-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </Transition>
            </div>

            <!-- Filters & Actions -->
            <div class="stagger-2 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center px-2">
                <div class="lg:col-span-9 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-4 flex items-center text-slate-400 group-focus-within:text-slate-900 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <input
                            type="text"
                            v-model="searchInput"
                            @keyup.enter="applyFilter('search', searchInput)"
                            placeholder="Search policies..."
                            class="w-full bg-slate-50 border-slate-200 rounded-xl pl-11 pr-4 py-3 text-xs font-bold text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all shadow-sm"
                        />
                    </div>

                    <!-- Priority Filter -->
                    <div class="relative group">
                        <select
                            v-model="filters.priority"
                            @change="applyFilter('priority', $event.target.value)"
                            class="w-full appearance-none bg-slate-50 border-slate-200 rounded-xl px-5 py-3 text-xs font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all shadow-sm"
                        >
                            <option value="">All Priorities</option>
                            <option v-for="priority in priorities" :key="priority.id" :value="priority.id">
                                {{ priority.name }}
                            </option>
                        </select>
                        <span class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                        </span>
                    </div>

                    <div class="flex items-center px-4">
                        <button @click="resetFilters" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Reset Filters</button>
                    </div>
                </div>

                <div class="lg:col-span-3 flex justify-end">
                    <button
                        @click="openCreateModal"
                        class="w-full lg:w-auto bg-slate-900 text-white px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200"
                    >
                        New Policy
                    </button>
                </div>
            </div>            <!-- SLA Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 stagger-3 px-2">
                <div
                    v-for="policy in policies.data"
                    :key="policy.id"
                    class="group relative bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 p-8 transition-all hover:shadow-xl hover:-translate-y-1.5 overflow-hidden"
                >
                    <!-- Background Accent -->
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-slate-50 border border-slate-100/50 -z-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

                    <div class="relative z-10">
                        <div class="flex items-start justify-between mb-8">
                            <div class="flex items-center gap-5">
                                <div class="h-14 w-14 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg transition-transform group-hover:rotate-6">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 21.355r7.103-3.155A12.003 12.003 0 0021 12.333V6.302l-9-4-9 4v6.031c0 2.397.705 4.629 1.897 6.502L12 21.355z" /></svg>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-lg font-black text-slate-900 tracking-tight truncate border-b-2 border-transparent group-hover:border-slate-900 transition-all inline-block">{{ policy.name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest border shadow-sm transition-colors"
                                            :class="policy.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100'"
                                        >
                                            <span class="h-1 w-1 rounded-full mr-1.5" :class="policy.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'"></span>
                                            {{ policy.is_active ? 'In Force' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                 <button
                                    @click="openEditModal(policy)"
                                    class="p-2.5 rounded-xl text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                 <button
                                    @click="openDeleteModal(policy)"
                                    class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <p class="text-[11px] font-semibold text-slate-500 leading-relaxed mb-8 line-clamp-2 italic font-serif min-h-[2.5rem]">
                            {{ policy.description || 'No description provided for this policy.' }}
                        </p>

                        <!-- Targets Section -->
                        <div class="bg-slate-50/50 rounded-2xl p-5 mb-6 border border-slate-100 transition-colors group-hover:bg-slate-50 group-hover:border-slate-200">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2">Service Targets</span>
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col">
                                            <span class="text-[7px] font-black text-slate-300 uppercase leading-none mb-1">Response</span>
                                            <span class="text-xs font-black text-slate-900">{{ formatMinutes(policy.response_time) }}</span>
                                        </div>
                                        <div class="h-6 w-px bg-slate-200"></div>
                                        <div class="flex flex-col">
                                            <span class="text-[7px] font-black text-slate-300 uppercase leading-none mb-1">Resolution</span>
                                            <span class="text-xs font-black text-slate-900">{{ formatMinutes(policy.resolution_time) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="h-10 w-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-300 group-hover:text-slate-900 transition-colors shadow-sm">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Scope Footer -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Target Priority</span>
                                    <span class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ getPriorityName(policy.priority_id) }}</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Applies To</span>
                                    <span class="text-xs font-black text-indigo-600 truncate max-w-[120px]">{{ getDepartmentName(policy.department_id) }}</span>
                                </div>
                            </div>

                            <!-- Progress (Subtle) -->
                            <div class="h-1.5 w-full bg-slate-50 rounded-full overflow-hidden border border-slate-100/50">
                                <div class="h-full bg-slate-900 transition-all duration-1000 w-1/4 opacity-10 group-hover:w-full group-hover:opacity-20"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 mt-6 border-t border-slate-50">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-slate-900 transition-colors flex items-center gap-1.5">
                                Edit Policy Detail
                                <svg class="h-3 w-3 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                            </span>
                            <span class="text-[8px] font-bold text-slate-300 uppercase tracking-tighter">Sync ID: #{{ policy.id.toString().padStart(4, '0') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="policies.data.length === 0" class="col-span-full py-20 flex flex-col items-center gap-6 stagger-1">
                    <div class="h-24 w-24 rounded-[2.5rem] bg-slate-50 flex items-center justify-center text-slate-200 border border-slate-100/60 shadow-inner">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="text-center">
                        <p class="font-black text-slate-900 uppercase tracking-widest text-lg">No Policies Active</p>
                        <p class="text-xs text-slate-400 font-medium italic font-serif">Try adjusting filters or create a new target.</p>
                    </div>
                    <button @click="openCreateModal" class="px-8 py-3 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                        Create First Target
                    </button>
                </div>
            </div>

            <!-- Table section removed and replaced by cards above -->

            <!-- Pagination -->
            <div v-if="policies.links && policies.links.length > 3" class="px-2 stagger-4">
                <div class="bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/40 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                        Showing <span class="text-slate-900">{{ policies.from }}</span> to <span class="text-slate-900">{{ policies.to }}</span> of <span class="text-slate-900">{{ policies.total }}</span> entries
                    </p>
                    <div class="flex items-center gap-1.5 p-1 bg-slate-50 rounded-xl border border-slate-100">
                        <template v-for="link in policies.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="h-9 px-4 rounded-lg flex items-center justify-center text-[10px] font-black uppercase transition-all"
                                :class="link.active ? 'bg-white text-slate-900 shadow-sm border border-slate-200 ring-4 ring-slate-900/5' : 'text-slate-400 hover:text-slate-900 hover:bg-white'"
                            />
                            <span v-else v-html="link.label" class="h-9 px-4 rounded-lg flex items-center justify-center text-[10px] font-black uppercase text-slate-300 opacity-50 cursor-not-allowed"></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Context Info Card -->
            <div class="stagger-4 grid grid-cols-1 md:grid-cols-3 gap-6 px-2">
                <div 
                    v-for="info in [
                        { title: 'Priority Focused', desc: 'Each policy is precision-tuned to a specific priority level.', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
                        { title: 'Auto Escalation', desc: 'System bridges unresolved issues to senior leadership automatically.', icon: 'M13 5l7 7-7 7M5 5l7 7-7 7' },
                        { title: 'Smart Calculations', desc: 'Count only business hours for precise performance metrics.', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }
                    ]"
                    :key="info.title"
                    class="p-8 rounded-3xl bg-slate-900 text-white flex flex-col gap-4 group hover:bg-slate-800 transition-all duration-500 shadow-xl shadow-slate-200"
                >
                    <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center group-hover:bg-emerald-400 group-hover:text-slate-900 transition-all">
                        <svg class="h-6 w-6 text-wrap" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="info.icon" /></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-black uppercase tracking-tighter">{{ info.title }}</h4>
                        <p class="text-xs text-slate-400 font-medium italic font-serif leading-relaxed mt-1">{{ info.desc }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
            <Teleport to="body">
                <!-- Create/Edit Modal -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-12 sm:py-24 font-sans">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModal" />
                        
                        <div class="relative mx-auto max-w-2xl bg-white rounded-[3rem] shadow-2xl overflow-hidden transform transition-all stagger-1">
                            <div class="p-10">
                                <div class="flex items-center justify-between mb-8 text-wrap">
                                    <div class="text-wrap">
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Policy Management</p>
                                        <h3 class="text-3xl font-black text-slate-900 tracking-tighter uppercase text-wrap">{{ editingPolicy ? 'Edit Policy' : 'New Policy' }}</h3>
                                    </div>
                                    <button @click="closeModal" class="h-12 w-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>

                                <form @submit.prevent="submit" class="space-y-8">
                                    <!-- Basic Config -->
                                    <div class="space-y-6">
                                        <div class="relative group">
                                            <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-slate-900 transition-colors">Policy Name</label>
                                            <input
                                                v-model="form.name"
                                                type="text"
                                                required
                                                placeholder="e.g., Premium Response SLA"
                                                class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-900 focus:border-slate-900 focus:ring-0 transition-all placeholder:text-slate-200"
                                            />
                                        </div>

                                        <div class="relative group">
                                            <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-slate-900 transition-colors">Description</label>
                                            <textarea
                                                v-model="form.description"
                                                rows="2"
                                                placeholder="High-priority response times for enterprise clients..."
                                                class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-900 focus:border-slate-900 focus:ring-0 transition-all placeholder:text-slate-200 resize-none"
                                            ></textarea>
                                        </div>
                                    </div>

                                    <!-- Targets & Rules -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-6">
                                            <div class="relative group">
                                                <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-slate-900 transition-colors">Priority Mapping</label>
                                                <div class="relative">
                                                    <select v-model="form.priority_id" required class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-900 appearance-none focus:border-slate-900 focus:ring-0 transition-all">
                                                        <option value="">Select Priority</option>
                                                        <option v-for="p in priorities" :key="p.id" :value="p.id">{{ p.name }}</option>
                                                    </select>
                                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="relative group">
                                                <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-slate-900 transition-colors">Department Boundary</label>
                                                <div class="relative">
                                                    <select v-model="form.department_id" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-900 appearance-none focus:border-slate-900 focus:ring-0 transition-all">
                                                        <option value="">All Departments</option>
                                                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                                    </select>
                                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-6">
                                            <div class="relative group">
                                                <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-slate-900 transition-colors">Response Time (Min)</label>
                                                <input v-model="form.response_time" type="number" required class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-900 focus:border-slate-900 focus:ring-0 transition-all" />
                                            </div>

                                            <div class="relative group">
                                                <label class="absolute -top-2.5 left-4 bg-white px-2 text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-slate-900 transition-colors">Resolution SLA (Min)</label>
                                                <input v-model="form.resolution_time" type="number" required class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-900 focus:border-slate-900 focus:ring-0 transition-all" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Switches -->
                                    <div class="p-8 bg-slate-50/50 rounded-3xl border border-slate-100 space-y-8">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                            <div class="relative group">
                                                <label class="absolute -top-2.5 left-4 bg-slate-50 px-2 text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-slate-900 transition-colors">Escalate After (Min)</label>
                                                <input v-model="form.escalate_after" type="number" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-900 focus:border-slate-900 focus:ring-0 transition-all placeholder:text-slate-200" placeholder="e.g. 120" />
                                            </div>
                                            <div class="relative group">
                                                <label class="absolute -top-2.5 left-4 bg-slate-50 px-2 text-[10px] font-black uppercase tracking-widest text-slate-400 group-focus-within:text-slate-900 transition-colors">Notify Before (Min)</label>
                                                <input v-model="form.notify_before" type="number" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-900 focus:border-slate-900 focus:ring-0 transition-all placeholder:text-slate-200" placeholder="e.g. 30" />
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-100">
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <div class="relative h-6 w-11 rounded-full bg-slate-200 transition-colors group-hover:bg-slate-300 shadow-inner" :class="form.business_hours_only ? 'bg-slate-900' : ''">
                                                    <input v-model="form.business_hours_only" type="checkbox" class="sr-only" />
                                                    <div class="absolute top-1 left-1 h-4 w-4 rounded-full bg-white transition-transform shadow-md" :class="form.business_hours_only ? 'translate-x-5' : ''" />
                                                </div>
                                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors">Business Hours</span>
                                            </label>

                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <div class="relative h-6 w-11 rounded-full bg-slate-200 transition-colors group-hover:bg-slate-300 shadow-inner" :class="form.notify_escalation ? 'bg-indigo-500' : ''">
                                                    <input v-model="form.notify_escalation" type="checkbox" class="sr-only" />
                                                    <div class="absolute top-1 left-1 h-4 w-4 rounded-full bg-white transition-transform shadow-md" :class="form.notify_escalation ? 'translate-x-5' : ''" />
                                                </div>
                                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors">Notify Breach</span>
                                            </label>

                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <div class="relative h-6 w-11 rounded-full bg-slate-200 transition-colors group-hover:bg-slate-300 shadow-inner" :class="form.is_active ? 'bg-emerald-500' : ''">
                                                    <input v-model="form.is_active" type="checkbox" class="sr-only" />
                                                    <div class="absolute top-1 left-1 h-4 w-4 rounded-full bg-white transition-transform shadow-md" :class="form.is_active ? 'translate-x-5' : ''" />
                                                </div>
                                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors">Enabled</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 pt-4 text-wrap">
                                        <button type="button" @click="closeModal" class="flex-1 px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] text-slate-400 hover:text-slate-900 transition-colors underline decoration-2 underline-offset-8">Cancel</button>
                                        <button
                                            type="submit"
                                            :disabled="form.processing"
                                            class="flex-[2] bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 disabled:opacity-50"
                                        >
                                            {{ form.processing ? 'Syncing...' : (editingPolicy ? 'Update Policy' : 'Initialize Policy') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Delete Modal -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-12 sm:py-24 font-sans">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeDeleteModal" />
                        
                        <div class="relative mx-auto max-w-lg bg-white rounded-[3rem] shadow-2xl overflow-hidden transform transition-all">
                            <div class="p-10 text-center">
                                <div class="h-24 w-24 bg-rose-50 text-rose-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-400 mb-1">Destructive Action</p>
                                <h3 class="text-3xl font-black text-slate-900 tracking-tighter uppercase mb-4 text-wrap">{{ policyToDelete?.name ? 'Delete SLA?' : 'Confirm Delete' }}</h3>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-8 px-4 leading-relaxed">
                                    You are about to dissolve the SLA targets for <span class="text-slate-900">"{{ policyToDelete?.name }}"</span>. 
                                    This action is irreversible and affects active tracking.
                                </p>

                                <div class="flex items-center gap-4 text-wrap">
                                    <button @click="closeDeleteModal" class="flex-1 px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] text-slate-400 hover:text-slate-900 transition-colors">Abort</button>
                                    <button @click="confirmDelete" class="flex-1 bg-rose-600 text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-rose-700 transition-all shadow-xl shadow-rose-200">Confirm</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>
    </AdminNavigation>
</template>