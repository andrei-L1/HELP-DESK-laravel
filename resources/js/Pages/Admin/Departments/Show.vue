<script setup>
import { ref } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    department: {
        type: Object,
        required: true,
    },
    assignedUsers: {
        type: Array,
        default: () => [],
    },
    availableUsers: {
        type: Array,
        default: () => [],
    },
});

// Assign users modal
const showAssignModal = ref(false);
const assignForm = useForm({
    user_ids: [],
    is_primary: false,
});

const openAssignModal = () => {
    assignForm.reset();
    assignForm.user_ids = [];
    showAssignModal.value = true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    assignForm.reset();
};

const submitAssign = () => {
    assignForm.post(route('admin.departments.assign-users', props.department.id), {
        preserveScroll: true,
        onSuccess: () => closeAssignModal(),
    });
};

const removeUser = (userId) => {
    if (confirm('Are you sure you want to remove this user from the department?')) {
        router.delete(route('admin.departments.remove-user', [props.department.id, userId]), {
            preserveScroll: true,
        });
    }
};

const setPrimary = (userId) => {
    router.post(route('admin.departments.set-primary', [props.department.id, userId]), {
        preserveScroll: true,
    });
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

const goBack = () => {
    router.visit(route('admin.departments.index'));
};
</script>

<template>
    <Head :title="department.name" />

    <AdminNavigation>
        <!-- Assign Users Modal -->
        <Teleport to="body">
            <div
                v-if="showAssignModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Assign users"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="fixed inset-0 bg-gray-500/75 transition-opacity"
                        aria-hidden="true"
                        @click="closeAssignModal"
                    />
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    >
                        <form @submit.prevent="submitAssign" class="p-6 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Assign Users to {{ department.name }}</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Users
                                </label>
                                <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-3">
                                    <div v-for="user in availableUsers" :key="user.id" class="flex items-center">
                                        <input
                                            :id="'user-' + user.id"
                                            type="checkbox"
                                            :value="user.id"
                                            v-model="assignForm.user_ids"
                                            class="h-4 w-4 rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                        />
                                        <label :for="'user-' + user.id" class="ml-2 block text-sm text-gray-700">
                                            {{ user.name }} ({{ user.email }})
                                        </label>
                                    </div>
                                    <div v-if="availableUsers.length === 0" class="text-sm text-gray-500 text-center py-4">
                                        No available users to assign.
                                    </div>
                                </div>
                                <p v-if="assignForm.errors.user_ids" class="mt-1 text-sm text-red-600">
                                    {{ assignForm.errors.user_ids }}
                                </p>
                            </div>
                            
                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="assignForm.is_primary"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Set as primary department for selected users</span>
                                </label>
                            </div>
                            
                            <div class="flex gap-3 justify-end pt-2">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                    @click="closeAssignModal"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                    :disabled="assignForm.processing || assignForm.user_ids.length === 0"
                                >
                                    {{ assignForm.processing ? 'Assigning…' : 'Assign Users' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>

        <template #header-title>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button
                        @click="goBack"
                        class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
                    >
                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to departments
                    </button>
                    <h1 class="text-xl font-semibold text-gray-900">{{ department.name }}</h1>
                    <span
                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                        :class="getStatusClass(department.is_active)"
                    >
                        {{ department.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </template>

        <div class="p-6 space-y-6">
            <!-- Department Details -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700">Department Information</h2>
                </div>
                <dl class="divide-y divide-gray-200">
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Department Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ department.name }}</dd>
                    </div>
                    <div v-if="department.short_code" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Short Code</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ department.short_code }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ department.description || 'No description provided' }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Manager</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ department.manager_name || 'Not assigned' }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ formatDate(department.created_at) }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Last updated</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ formatDate(department.updated_at) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Users Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-sm font-semibold text-gray-700">Department Members ({{ assignedUsers.length }})</h2>
                    <button
                        @click="openAssignModal"
                        class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        :disabled="availableUsers.length === 0"
                        :class="{ 'opacity-50 cursor-not-allowed': availableUsers.length === 0 }"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Assign Users
                    </button>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Primary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="user in assignedUsers" :key="user.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ user.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ user.email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ user.username }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span v-if="user.is_primary" class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                    Primary
                                </span>
                                <button
                                    v-else
                                    @click="setPrimary(user.id)"
                                    class="text-xs text-slate-600 hover:text-slate-900 font-medium"
                                >
                                    Set as primary
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(user.joined_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <button
                                    @click="removeUser(user.id)"
                                    class="text-red-600 hover:text-red-800"
                                    title="Remove from department"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="assignedUsers.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No users assigned to this department.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminNavigation>
</template>