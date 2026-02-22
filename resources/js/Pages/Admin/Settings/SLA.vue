<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.settings.sla.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = () => {
    if (policyToDelete.value) {
        router.delete(route('admin.settings.sla.destroy', policyToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => closeDeleteModal(),
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
        return `${minutes} minute${minutes === 1 ? '' : 's'}`;
    }
    
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    
    if (remainingMinutes === 0) {
        return `${hours} hour${hours === 1 ? '' : 's'}`;
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
        'urgent': 'bg-red-100 text-red-800',
        'high': 'bg-orange-100 text-orange-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800',
    };
    return colors[priority?.name?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const getDepartmentName = (deptId) => {
    if (!deptId) return 'All Departments';
    const dept = props.departments.find(d => d.id === deptId);
    return dept ? dept.name : 'Unknown';
};

const getStatusClass = (isActive) => {
    return isActive 
        ? 'bg-green-100 text-green-800' 
        : 'bg-red-100 text-red-800';
};
</script>

<template>
    <Head title="SLA Policies" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.settings.index')"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Settings
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">SLA Policies</h1>
            </div>
        </template>

        <div class="p-6 space-y-6">
            <!-- Create/Edit Modal -->
            <Teleport to="body">
                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="SLA policy modal"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div
                            class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            aria-hidden="true"
                            @click="closeModal"
                        />
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                        >
                            <form @submit.prevent="submit" class="p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ editingPolicy ? 'Edit SLA Policy' : 'Create SLA Policy' }}
                                </h3>
                                
                                <!-- Basic Information -->
                                <div class="border-b border-gray-200 pb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Basic Information</h4>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700">
                                                Policy Name <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="name"
                                                v-model="form.name"
                                                type="text"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                                placeholder="e.g., High Priority Support"
                                            />
                                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.name }}
                                            </p>
                                        </div>

                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-700">
                                                Description
                                            </label>
                                            <textarea
                                                id="description"
                                                v-model="form.description"
                                                rows="2"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                                placeholder="Describe when this SLA policy applies"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Application Rules -->
                                <div class="border-b border-gray-200 pb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Application Rules</h4>
                                    
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="priority_id" class="block text-sm font-medium text-gray-700">
                                                Priority <span class="text-red-500">*</span>
                                            </label>
                                            <select
                                                id="priority_id"
                                                v-model="form.priority_id"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                            >
                                                <option value="">Select Priority</option>
                                                <option v-for="priority in priorities" :key="priority.id" :value="priority.id">
                                                    {{ priority.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="department_id" class="block text-sm font-medium text-gray-700">
                                                Department
                                            </label>
                                            <select
                                                id="department_id"
                                                v-model="form.department_id"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                            >
                                                <option value="">All Departments</option>
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                                    {{ dept.name }}
                                                </option>
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500">Leave empty to apply to all departments</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Time Targets -->
                                <div class="border-b border-gray-200 pb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Time Targets</h4>
                                    
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div>
                                            <label for="response_time" class="block text-sm font-medium text-gray-700">
                                                Response Time (minutes) <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="response_time"
                                                v-model="form.response_time"
                                                type="number"
                                                min="1"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">First response due within</p>
                                        </div>

                                        <div>
                                            <label for="resolution_time" class="block text-sm font-medium text-gray-700">
                                                Resolution Time (minutes) <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="resolution_time"
                                                v-model="form.resolution_time"
                                                type="number"
                                                min="1"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">Ticket resolution due within</p>
                                        </div>

                                        <div>
                                            <label for="escalate_after" class="block text-sm font-medium text-gray-700">
                                                Escalate After (minutes)
                                            </label>
                                            <input
                                                id="escalate_after"
                                                v-model="form.escalate_after"
                                                type="number"
                                                min="1"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">Escalate if not resolved</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notification Settings -->
                                <div class="border-b border-gray-200 pb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Notification Settings</h4>
                                    
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="notify_before" class="block text-sm font-medium text-gray-700">
                                                Notify Before (minutes)
                                            </label>
                                            <input
                                                id="notify_before"
                                                v-model="form.notify_before"
                                                type="number"
                                                min="1"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">Send warning before deadline</p>
                                        </div>

                                        <div class="flex items-center">
                                            <label class="flex items-center text-sm text-gray-700">
                                                <input
                                                    v-model="form.notify_escalation"
                                                    type="checkbox"
                                                    class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                                />
                                                <span class="ml-2">Notify on escalation</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Settings -->
                                <div class="border-b border-gray-200 pb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Additional Settings</h4>
                                    
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <label class="flex items-center text-sm text-gray-700">
                                                <input
                                                    v-model="form.business_hours_only"
                                                    type="checkbox"
                                                    class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                                />
                                                <span class="ml-2">Count business hours only</span>
                                            </label>
                                        </div>

                                        <div class="flex items-center">
                                            <label class="flex items-center text-sm text-gray-700">
                                                <input
                                                    v-model="form.is_active"
                                                    type="checkbox"
                                                    class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                                />
                                                <span class="ml-2">Active</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Example Preview -->
                                <div class="bg-blue-50 p-3 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-xs font-medium text-blue-800">SLA Summary</h4>
                                            <p class="text-xs text-blue-700 mt-1">
                                                <strong>Response:</strong> {{ formatMinutes(form.response_time) }} |
                                                <strong>Resolution:</strong> {{ formatMinutes(form.resolution_time) }}
                                                <span v-if="form.escalate_after"> | <strong>Escalate:</strong> {{ formatMinutes(form.escalate_after) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-3 justify-end pt-2">
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                        @click="closeModal"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                        :disabled="form.processing"
                                    >
                                        {{ form.processing ? 'Saving...' : (editingPolicy ? 'Update Policy' : 'Create Policy') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div
                    v-if="showDeleteModal && policyToDelete"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="Delete policy"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div
                            class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            aria-hidden="true"
                            @click="closeDeleteModal"
                        />
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        >
                            <div class="p-6">
                                <div class="flex items-center justify-center text-red-600 mb-4">
                                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                
                                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Delete SLA Policy</h3>
                                
                                <p class="text-sm text-gray-500 text-center mb-4">
                                    Are you sure you want to delete <span class="font-semibold text-gray-700">"{{ policyToDelete.name }}"</span>?
                                    <br>
                                    <span class="text-red-600 font-medium">This action cannot be undone.</span>
                                </p>

                                <div class="bg-yellow-50 p-3 rounded-md mb-4">
                                    <p class="text-xs text-yellow-700">
                                        <strong>Warning:</strong> This policy may be applied to active tickets. 
                                        Deleting it could affect SLA calculations.
                                    </p>
                                </div>
                                
                                <div class="flex gap-3 justify-center">
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                        @click="closeDeleteModal"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700"
                                        @click="confirmDelete"
                                    >
                                        Delete Policy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- Filters -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Search</label>
                        <div class="mt-1 relative">
                            <input
                                type="text"
                                v-model="searchInput"
                                @keyup.enter="applyFilter('search', searchInput)"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 pr-10"
                                placeholder="Search by name or description"
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

                    <!-- Priority Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority</label>
                        <select
                            v-model="filters.priority"
                            @change="applyFilter('priority', $event.target.value)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">All Priorities</option>
                            <option v-for="priority in priorities" :key="priority.id" :value="priority.id">
                                {{ priority.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <select
                            v-model="filters.department"
                            @change="applyFilter('department', $event.target.value)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">All Departments</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end">
                        <button
                            @click="resetFilters"
                            class="text-sm text-gray-600 hover:text-gray-900 font-medium mr-3"
                        >
                            Reset
                        </button>
                        <button
                            @click="openCreateModal"
                            class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Policy
                        </button>
                    </div>
                </div>
            </div>

            <!-- SLA Policies Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Response Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Resolution Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Escalation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="policy in policies.data" :key="policy.id">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ policy.name }}</p>
                                    <p v-if="policy.description" class="text-xs text-gray-500 truncate max-w-xs">
                                        {{ policy.description }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="getPriorityColor(policy.priority_id)"
                                >
                                    {{ getPriorityName(policy.priority_id) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ getDepartmentName(policy.department_id) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatMinutes(policy.response_time) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatMinutes(policy.resolution_time) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-if="policy.escalate_after">
                                    {{ formatMinutes(policy.escalate_after) }}
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="getStatusClass(policy.is_active)"
                                >
                                    {{ policy.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="flex justify-end gap-2">
                                    <button
                                        @click="openEditModal(policy)"
                                        class="text-slate-600 hover:text-slate-900"
                                        title="Edit policy"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="openDeleteModal(policy)"
                                        class="text-red-600 hover:text-red-800"
                                        title="Delete policy"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="policies.data.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                                No SLA policies found.
                                <button
                                    @click="openCreateModal"
                                    class="ml-1 text-slate-600 hover:text-slate-800 font-medium underline"
                                >
                                    Create your first policy
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="policies.links && policies.links.length > 3"
                class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm sm:px-6"
            >
                <div class="hidden sm:block">
                    <p>
                        Showing
                        <span class="font-medium">{{ policies.from || 0 }}</span>
                        to
                        <span class="font-medium">{{ policies.to || 0 }}</span>
                        of
                        <span class="font-medium">{{ policies.total || 0 }}</span>
                        results
                    </p>
                </div>
                <div class="flex flex-1 justify-between sm:justify-end gap-1">
                    <Link
                        v-for="link in policies.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium"
                        :class="[
                            link.active
                                ? 'bg-slate-700 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                            !link.url ? 'cursor-default opacity-50' : '',
                        ]"
                    />
                </div>
            </div>

            <!-- Info Card -->
            <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">About SLA Policies</h3>
                        <div class="mt-2 text-xs text-blue-700 space-y-1">
                            <p>• <strong>Priority-based:</strong> Each policy is tied to a specific priority level</p>
                            <p>• <strong>Department-specific:</strong> Can be applied to specific departments or all departments</p>
                            <p>• <strong>Response Time:</strong> Time until first response from agent</p>
                            <p>• <strong>Resolution Time:</strong> Time until ticket is resolved</p>
                            <p>• <strong>Escalation:</strong> Automatic escalation if SLA is breached</p>
                            <p>• <strong>Business Hours:</strong> Option to count only business hours vs. 24/7</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>