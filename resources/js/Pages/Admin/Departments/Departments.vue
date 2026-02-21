<script setup>
import { ref } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

// Create modal
const showCreateModal = ref(false);
const createForm = useForm({
    name: '',
    short_code: '',
    description: '',
    manager_id: '',
    is_active: true,
});

// Edit modal
const showEditModal = ref(false);
const editForm = useForm({
    id: null,
    name: '',
    short_code: '',
    description: '',
    manager_id: '',
    is_active: true,
});

// View modal
const showViewModal = ref(false);
const selectedDepartment = ref(null);

// Delete modal
const showDeleteModal = ref(false);
const departmentToDelete = ref(null);

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

// Modal management
const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
    createForm.is_active = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
};

const openEditModal = (dept) => {
    editForm.id = dept.id;
    editForm.name = dept.name;
    editForm.short_code = dept.short_code || '';
    editForm.description = dept.description || '';
    editForm.manager_id = dept.manager_id || '';
    editForm.is_active = dept.is_active;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
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
    return new Date(date).toLocaleDateString();
};

const getStatusClass = (isActive) => {
    return isActive 
        ? 'bg-green-100 text-green-800' 
        : 'bg-red-100 text-red-800';
};

const getManagerName = (dept) => {
    return dept.manager_name || 'Not assigned';
};
</script>

<template>
    <Head title="Departments" />

    <AdminNavigation>
        <!-- Create Modal -->
        <Teleport to="body">
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Create department"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="fixed inset-0 bg-gray-500/75 transition-opacity"
                        aria-hidden="true"
                        @click="closeCreateModal"
                    />
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    >
                        <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Create Department</h3>
                            
                            <div>
                                <label for="create-name" class="block text-sm font-medium text-gray-700">
                                    Department Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="create-name"
                                    v-model="createForm.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="e.g., IT Support, Customer Service"
                                />
                                <p v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.name }}
                                </p>
                            </div>
                            
                            <div>
                                <label for="create-short_code" class="block text-sm font-medium text-gray-700">
                                    Short Code
                                </label>
                                <input
                                    id="create-short_code"
                                    v-model="createForm.short_code"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="e.g., IT, CS, HR"
                                />
                                <p v-if="createForm.errors.short_code" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.short_code }}
                                </p>
                            </div>
                            
                            <div>
                                <label for="create-description" class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <textarea
                                    id="create-description"
                                    v-model="createForm.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="Describe the department's function"
                                ></textarea>
                            </div>
                            
                            <div>
                                <label for="create-manager_id" class="block text-sm font-medium text-gray-700">
                                    Department Manager
                                </label>
                                <select
                                    id="create-manager_id"
                                    v-model="createForm.manager_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option value="">Select a manager</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                            </div>
                            
                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="createForm.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Active</span>
                                </label>
                            </div>
                            
                            <div class="flex gap-3 justify-end pt-2">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                    @click="closeCreateModal"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                    :disabled="createForm.processing"
                                >
                                    {{ createForm.processing ? 'Creating…' : 'Create Department' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div
                v-if="showEditModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Edit department"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="fixed inset-0 bg-gray-500/75 transition-opacity"
                        aria-hidden="true"
                        @click="closeEditModal"
                    />
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    >
                        <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Edit Department</h3>
                            
                            <div>
                                <label for="edit-name" class="block text-sm font-medium text-gray-700">
                                    Department Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="edit-name"
                                    v-model="editForm.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ editForm.errors.name }}
                                </p>
                            </div>
                            
                            <div>
                                <label for="edit-short_code" class="block text-sm font-medium text-gray-700">
                                    Short Code
                                </label>
                                <input
                                    id="edit-short_code"
                                    v-model="editForm.short_code"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="editForm.errors.short_code" class="mt-1 text-sm text-red-600">
                                    {{ editForm.errors.short_code }}
                                </p>
                            </div>
                            
                            <div>
                                <label for="edit-description" class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <textarea
                                    id="edit-description"
                                    v-model="editForm.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                ></textarea>
                            </div>
                            
                            <div>
                                <label for="edit-manager_id" class="block text-sm font-medium text-gray-700">
                                    Department Manager
                                </label>
                                <select
                                    id="edit-manager_id"
                                    v-model="editForm.manager_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option value="">Select a manager</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                            </div>
                            
                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="editForm.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Active</span>
                                </label>
                            </div>
                            
                            <div class="flex gap-3 justify-end pt-2">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                    @click="closeEditModal"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                    :disabled="editForm.processing"
                                >
                                    {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Modal -->
            <div
                v-if="showViewModal && selectedDepartment"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="View department"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="fixed inset-0 bg-gray-500/75 transition-opacity"
                        aria-hidden="true"
                        @click="closeViewModal"
                    />
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Department Details</h3>
                            
                            <dl class="space-y-3">
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ selectedDepartment.name }}</dd>
                                </div>
                                
                                <div v-if="selectedDepartment.short_code" class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Short Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ selectedDepartment.short_code }}</dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ selectedDepartment.description || 'No description provided' }}
                                    </dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Manager</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ getManagerName(selectedDepartment) }}
                                    </dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="getStatusClass(selectedDepartment.is_active)"
                                        >
                                            {{ selectedDepartment.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Users</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800">
                                            {{ selectedDepartment.users_count || 0 }} users
                                        </span>
                                    </dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Tickets</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                            {{ selectedDepartment.tickets_count || 0 }} tickets
                                        </span>
                                    </dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ formatDate(selectedDepartment.created_at) }}
                                    </dd>
                                </div>
                                
                                <div class="pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Last updated</dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ formatDate(selectedDepartment.updated_at) }}
                                    </dd>
                                </div>
                            </dl>
                            
                            <div class="flex justify-end pt-4">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                    @click="closeViewModal"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div
                v-if="showDeleteModal && departmentToDelete"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Delete department"
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
                            
                            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Delete Department</h3>
                            
                            <p class="text-sm text-gray-500 text-center mb-4">
                                Are you sure you want to delete <span class="font-semibold text-gray-700">"{{ departmentToDelete.name }}"</span>?
                                <br>
                                <span class="text-red-600 font-medium">This action cannot be undone.</span>
                            </p>

                            <div v-if="departmentToDelete.users_count > 0" class="mb-4 rounded-md bg-yellow-50 p-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            This department has {{ departmentToDelete.users_count }} user(s) assigned.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="departmentToDelete.tickets_count > 0" class="mb-4 rounded-md bg-yellow-50 p-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            This department has {{ departmentToDelete.tickets_count }} ticket(s) assigned.
                                        </p>
                                    </div>
                                </div>
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
                                    Delete Department
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Departments</h1>
        </template>

        <div class="p-6 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Departments</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_departments }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Active Departments</p>
                    <p class="mt-1 text-2xl font-semibold text-green-600">{{ stats.active_departments }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Users in Depts</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_users }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Assignments</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_assignments }}</p>
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm md:flex-row md:items-end md:justify-between">
                <div class="flex flex-1 flex-col gap-3 md:flex-row">
                    <!-- Search -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700" for="search">
                            Search Departments
                        </label>
                        <div class="mt-1 relative">
                            <input
                                id="search"
                                type="text"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 pr-10"
                                :value="filters.search || ''"
                                placeholder="Search by name, code, or manager"
                                @keyup.enter="applyFilter('search', $event.target.value)"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                @click="applyFilter('search', $event.target.previousElementSibling.value)"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Status filter -->
                    <div class="w-full md:w-44">
                        <label class="block text-sm font-medium text-gray-700" for="status">Status</label>
                        <select
                            id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm"
                            :value="filters.status || ''"
                            @change="applyFilter('status', $event.target.value)"
                        >
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="resetFilters"
                    >
                        Reset
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        @click="openCreateModal"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4" />
                        </svg>
                        New Department
                    </button>
                </div>
            </div>

            <!-- Departments Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Manager
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Users
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Tickets
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    <tr
                        v-for="dept in departments.data"
                        :key="dept.id"
                        class="cursor-pointer hover:bg-gray-50 transition-colors"
                        @click="router.visit(route('admin.departments.show', dept.id))" 
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-8 w-8 flex-shrink-0 rounded-full bg-slate-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-slate-600">
                                        {{ dept.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ dept.name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ dept.short_code || '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ dept.manager_name || '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800">
                                {{ dept.users_count || 0 }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                {{ dept.tickets_count || 0 }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span
                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                :class="getStatusClass(dept.is_active)"
                            >
                                {{ dept.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-right">
                            <div class="flex justify-end gap-2" @click.stop>
                                <button
                                    @click="openEditModal(dept)"
                                    class="text-slate-600 hover:text-slate-900"
                                    title="Edit department"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="openDeleteModal(dept)"
                                    class="text-red-600 hover:text-red-800"
                                    title="Delete department"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="departments.data.length === 0">
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                            No departments found.
                            <button
                                @click="openCreateModal"
                                class="ml-1 text-slate-600 hover:text-slate-800 font-medium underline"
                            >
                                Create your first department
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="departments.links && departments.links.length > 1"
                class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm sm:px-6"
            >
                <div class="hidden sm:block">
                    <p>
                        Showing
                        <span class="font-medium">{{ departments.from || 0 }}</span>
                        to
                        <span class="font-medium">{{ departments.to || 0 }}</span>
                        of
                        <span class="font-medium">{{ departments.total || 0 }}</span>
                        results
                    </p>
                </div>
                <div class="flex flex-1 justify-between sm:justify-end gap-1">
                    <Link
                        v-for="link in departments.links"
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
        </div>
    </AdminNavigation>
</template>