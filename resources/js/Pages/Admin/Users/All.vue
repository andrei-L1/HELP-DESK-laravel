<script setup>
import { ref, watch } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

// Create user modal
const showCreateModal = ref(false);
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

// Fix autofocus warning by not automatically focusing
const modalInitialized = ref(false);

const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
    // Delay any focus operations
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
        }),
    },
    roles: {
        type: [Object, Array], // Accept both Object and Array
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
});

// Transform roles to array format for easier handling
const rolesList = ref([]);

// Watch for changes in props.roles and transform to array
watch(() => props.roles, (roles) => {
    if (Array.isArray(roles)) {
        rolesList.value = roles;
    } else if (roles && typeof roles === 'object') {
        // Transform object { id: name } to array [{ id, name }]
        rolesList.value = Object.entries(roles).map(([id, name]) => ({
            id: parseInt(id),
            name: name
        }));
    } else {
        rolesList.value = [];
    }
}, { immediate: true });

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
</script>

<template>
    <Head title="Users" />

    <AdminNavigation>
        <!-- Create User Modal -->
        <Teleport to="body">
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Create user"
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
                        <form @submit.prevent="submitCreate" class="p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                            <h3 class="text-lg font-semibold text-gray-900">Create User</h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="create-first_name" class="block text-sm font-medium text-gray-700">First name *</label>
                                    <input
                                        id="create-first_name"
                                        v-model="createForm.first_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    />
                                    <p v-if="createForm.errors.first_name" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.first_name }}
                                    </p>
                                </div>
                                
                                <div>
                                    <label for="create-last_name" class="block text-sm font-medium text-gray-700">Last name *</label>
                                    <input
                                        id="create-last_name"
                                        v-model="createForm.last_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    />
                                    <p v-if="createForm.errors.last_name" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.last_name }}
                                    </p>
                                </div>
                            </div>
                            
                            <div>
                                <label for="create-middle_name" class="block text-sm font-medium text-gray-700">Middle name</label>
                                <input
                                    id="create-middle_name"
                                    v-model="createForm.middle_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                            </div>
                            
                            <div>
                                <label for="create-display_name" class="block text-sm font-medium text-gray-700">Display name</label>
                                <input
                                    id="create-display_name"
                                    v-model="createForm.display_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                            </div>
                            
                            <div>
                                <label for="create-username" class="block text-sm font-medium text-gray-700">Username *</label>
                                <input
                                    id="create-username"
                                    v-model="createForm.username"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="createForm.errors.username" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.username }}
                                </p>
                            </div>
                            
                            <div>
                                <label for="create-email" class="block text-sm font-medium text-gray-700">Email *</label>
                                <input
                                    id="create-email"
                                    v-model="createForm.email"
                                    type="email"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="createForm.errors.email" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.email }}
                                </p>
                            </div>
                            
                            <div>
                                <label for="create-phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input
                                    id="create-phone"
                                    v-model="createForm.phone"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                            </div>
                            
                            <div>
                                <label for="create-role_id" class="block text-sm font-medium text-gray-700">Role *</label>
                                <select
                                    id="create-role_id"
                                    v-model="createForm.role_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option value="">Select a role</option>
                                    <option v-for="role in rolesList" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <p v-if="createForm.errors.role_id" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.role_id }}
                                </p>
                            </div>
                            
                            <div>
                                <label for="create-timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                                <select
                                    id="create-timezone"
                                    v-model="createForm.timezone"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
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
                            
                            <div>
                                <label for="create-password" class="block text-sm font-medium text-gray-700">Password *</label>
                                <input
                                    id="create-password"
                                    v-model="createForm.password"
                                    type="password"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="createForm.errors.password" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.password }}
                                </p>
                            </div>
                            
                            <div>
                                <label for="create-password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password *</label>
                                <input
                                    id="create-password_confirmation"
                                    v-model="createForm.password_confirmation"
                                    type="password"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                            </div>
                            
                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="createForm.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Account active</span>
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
                                    {{ createForm.processing ? 'Creating…' : 'Create User' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>

        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Users</h1>
        </template>

        <div class="p-6 space-y-6">
            <!-- Filters -->
            <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm md:flex-row md:items-end md:justify-between">
                <div class="flex flex-1 flex-col gap-3 md:flex-row">
                    <!-- Search -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700" for="search">
                            Search
                        </label>
                        <div class="mt-1 relative">
                            <input
                                id="search"
                                type="text"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 pr-10"
                                :value="filters.search || ''"
                                placeholder="Search by name, username or email"
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
                            @change="applyFilter('status', $event.target.value || null)"
                        >
                            <option value="">All</option>
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Role filter -->
                    <div class="w-full md:w-52">
                        <label class="block text-sm font-medium text-gray-700" for="role">Role</label>
                        <select
                            id="role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm"
                            :value="filters.role || ''"
                            @change="applyFilter('role', $event.target.value || null)"
                        >
                            <option value="">All Roles</option>
                            <option v-for="role in rolesList" :key="role.id" :value="role.id">
                                {{ role.name }}
                            </option>
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
                        New User
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Username
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Last Login
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
                        v-for="user in users.data"
                        :key="user.id"
                        class="cursor-pointer hover:bg-gray-50 transition-colors"
                        @click="viewUser(user.id)"
                    >
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ user.username }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ user.full_name || user.display_name || (user.first_name + ' ' + user.last_name) || '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ user.email }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800">
                                {{ user.role_name || user.role || 'User' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span
                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                :class="{
                                    'bg-green-100 text-green-800': user.is_active,
                                    'bg-red-100 text-red-800': !user.is_active,
                                }"
                            >
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ formatDate(user.last_login) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ formatDate(user.created_at) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-right">
                            <div class="flex justify-end gap-2" @click.stop>
                                <Link
                                    :href="route('admin.users.edit', user.id)"
                                    class="text-slate-600 hover:text-slate-900"
                                    title="Edit user"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </Link>
                                <button
                                    @click="impersonateUser(user.id)"
                                    v-if="user.is_active"
                                    class="text-amber-600 hover:text-amber-800"
                                    title="Impersonate user"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteUser(user.id)"
                                    class="text-red-600 hover:text-red-800"
                                    title="Delete user"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="users.data.length === 0">
                        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                            No users found with the current filters.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="users.links && users.links.length > 1"
                class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm sm:px-6"
            >
                <div class="hidden sm:block">
                    <p>
                        Showing
                        <span class="font-medium">{{ users.from || 0 }}</span>
                        to
                        <span class="font-medium">{{ users.to || 0 }}</span>
                        of
                        <span class="font-medium">{{ users.total || 0 }}</span>
                        results
                    </p>
                </div>
                <div class="flex flex-1 justify-between sm:justify-end gap-1">
                    <Link
                        v-for="link in users.links"
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