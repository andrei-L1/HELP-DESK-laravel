<script setup>
import { ref, watch } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

// Create role modal
const showCreateModal = ref(false);
const createForm = useForm({
    name: '',
    description: '',
    permissions: [],
});

// Edit role modal
const showEditModal = ref(false);
const editForm = useForm({
    id: null,
    name: '',
    description: '',
    permissions: [],
});

// Delete confirmation modal
const showDeleteModal = ref(false);
const roleToDelete = ref(null);

// View role details modal
const showViewModal = ref(false);
const selectedRole = ref(null);

const props = defineProps({
    roles: {
        type: Object,
        required: true,
    },
    permissions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            total_roles: 0,
            total_users: 0,
        }),
    },
});

// Modal management
const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
};

const openEditModal = (role) => {
    editForm.id = role.id;
    editForm.name = role.name;
    editForm.description = role.description || '';
    editForm.permissions = role.permissions || [];
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
};

const openViewModal = (role) => {
    selectedRole.value = role;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedRole.value = null;
};

const openDeleteModal = (role) => {
    roleToDelete.value = role;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    roleToDelete.value = null;
};

// Form submissions
const submitCreate = () => {
    createForm.post(route('admin.roles.store'), {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

const submitEdit = () => {
    editForm.put(route('admin.roles.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => closeEditModal(),
    });
};

const confirmDelete = () => {
    if (roleToDelete.value) {
        router.delete(route('admin.roles.destroy', roleToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => closeDeleteModal(),
        });
    }
};

// Filtering
const applyFilter = (key, value) => {
    router.get(
        route('admin.users.roles'),
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
        route('admin.users.roles'),
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

// Get user count display
const getUserCountText = (role) => {
    if (!role.users_count) return 'No users';
    return role.users_count + (role.users_count === 1 ? ' user' : ' users');
};
</script>

<template>
    <Head title="Roles" />

    <AdminNavigation>
        <!-- Create Role Modal -->
        <Teleport to="body">
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Create role"
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
                            <h3 class="text-lg font-semibold text-gray-900">Create Role</h3>
                            
                            <div>
                                <label for="create-name" class="block text-sm font-medium text-gray-700">
                                    Role name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="create-name"
                                    v-model="createForm.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="e.g., Admin, Agent, Customer"
                                />
                                <p v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.name }}
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
                                    placeholder="Describe the role's purpose and permissions"
                                ></textarea>
                                <p v-if="createForm.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.description }}
                                </p>
                            </div>

                            <!-- Permissions Checkboxes -->
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Permissions</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-2">
                                    <div v-for="permission in permissions" :key="permission.id" class="flex items-start">
                                        <div class="flex h-5 items-center">
                                            <input
                                                :id="'create-perm-' + permission.id"
                                                :value="permission.id"
                                                v-model="createForm.permissions"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label :for="'create-perm-' + permission.id" class="font-medium text-gray-700">
                                                {{ permission.title || permission.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
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
                                    {{ createForm.processing ? 'Creating…' : 'Create Role' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Role Modal -->
            <div
                v-if="showEditModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Edit role"
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
                            <h3 class="text-lg font-semibold text-gray-900">Edit Role</h3>
                            
                            <div>
                                <label for="edit-name" class="block text-sm font-medium text-gray-700">
                                    Role name <span class="text-red-500">*</span>
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
                                <label for="edit-description" class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <textarea
                                    id="edit-description"
                                    v-model="editForm.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                ></textarea>
                                <p v-if="editForm.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ editForm.errors.description }}
                                </p>
                            </div>

                            <!-- Permissions Checkboxes -->
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Permissions</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-2">
                                    <div v-for="permission in permissions" :key="permission.id" class="flex items-start">
                                        <div class="flex h-5 items-center">
                                            <input
                                                :id="'edit-perm-' + permission.id"
                                                :value="permission.id"
                                                v-model="editForm.permissions"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label :for="'edit-perm-' + permission.id" class="font-medium text-gray-700">
                                                {{ permission.title || permission.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
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

            <!-- View Role Modal -->
            <div
                v-if="showViewModal && selectedRole"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="View role"
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
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Role Details</h3>
                            
                            <dl class="space-y-3">
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ selectedRole.name }}</dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ selectedRole.description || 'No description provided' }}
                                    </dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Users</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800">
                                            {{ getUserCountText(selectedRole) }}
                                        </span>
                                    </dd>
                                </div>

                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Permissions</dt>
                                    <dd class="mt-1 flex flex-wrap gap-2 text-sm text-gray-900">
                                        <span v-for="permId in selectedRole.permissions" :key="'sp-'+permId" class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                            {{ permissions.find(p => p.id === permId)?.title || permId }}
                                        </span>
                                        <span v-if="!selectedRole.permissions || selectedRole.permissions.length === 0" class="text-gray-500 italic">No permissions assigned</span>
                                    </dd>
                                </div>
                                
                                <div class="border-b border-gray-200 pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ formatDate(selectedRole.created_at) }}
                                    </dd>
                                </div>
                                
                                <div class="pb-2">
                                    <dt class="text-sm font-medium text-gray-500">Last updated</dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ formatDate(selectedRole.updated_at) }}
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

            <!-- Delete Confirmation Modal -->
            <div
                v-if="showDeleteModal && roleToDelete"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Delete role"
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
                            
                            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Delete Role</h3>
                            
                            <p class="text-sm text-gray-500 text-center mb-4">
                                Are you sure you want to delete the role <span class="font-semibold text-gray-700">"{{ roleToDelete.name }}"</span>?
                                <br>
                                <span class="text-red-600 font-medium">This action cannot be undone.</span>
                            </p>

                            <div v-if="roleToDelete.users_count > 0" class="mb-4 rounded-md bg-yellow-50 p-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            This role has {{ getUserCountText(roleToDelete) }} assigned. 
                                            Deleting it will affect those users.
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
                                    Delete Role
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Roles</h1>
        </template>

        <div class="p-6 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Roles</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_roles || roles.total || 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_users || 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Avg Users per Role</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ stats.total_roles ? Math.round((stats.total_users || 0) / stats.total_roles) : 0 }}
                    </p>
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm md:flex-row md:items-end md:justify-between">
                <div class="flex flex-1 flex-col gap-3 md:flex-row">
                    <!-- Search -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700" for="search">
                            Search Roles
                        </label>
                        <div class="mt-1 relative">
                            <input
                                id="search"
                                type="text"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 pr-10"
                                :value="filters.search || ''"
                                placeholder="Search by name or description"
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
                        New Role
                    </button>
                </div>
            </div>

            <!-- Roles Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Role Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Users
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Created
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    <tr
                        v-for="role in roles.data"
                        :key="role.id"
                        class="cursor-pointer hover:bg-gray-50 transition-colors"
                        @click="openViewModal(role)"
                    >
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-8 w-8 flex-shrink-0 rounded-full bg-slate-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-slate-600">
                                        {{ role.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ role.name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                            {{ role.description || '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800">
                                {{ getUserCountText(role) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ formatDate(role.created_at) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-right">
                            <div class="flex justify-end gap-2" @click.stop>
                                <button
                                    @click="openEditModal(role)"
                                    class="text-slate-600 hover:text-slate-900"
                                    title="Edit role"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="openDeleteModal(role)"
                                    class="text-red-600 hover:text-red-800"
                                    title="Delete role"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="roles.data.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                            No roles found.
                            <button
                                @click="openCreateModal"
                                class="ml-1 text-slate-600 hover:text-slate-800 font-medium underline"
                            >
                                Create your first role
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="roles.links && roles.links.length > 1"
                class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm sm:px-6"
            >
                <div class="hidden sm:block">
                    <p>
                        Showing
                        <span class="font-medium">{{ roles.from || 0 }}</span>
                        to
                        <span class="font-medium">{{ roles.to || 0 }}</span>
                        of
                        <span class="font-medium">{{ roles.total || 0 }}</span>
                        results
                    </p>
                </div>
                <div class="flex flex-1 justify-between sm:justify-end gap-1">
                    <Link
                        v-for="link in roles.links"
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