<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';

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
        type: Object,           // { id: name } or array — adjust based on your backend
        default: () => ({}),
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
    // Placeholder — implement detail page later
    alert(`View user #${userId} (detail page not implemented yet)`);
};
</script>

<template>
    <Head title="Users" />

    <AdminNavigation>
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
                            <option v-for="(name, id) in roles" :key="id" :value="id">
                                {{ name }}
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
                        @click="$emit('create-user')"
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
                            {{ user.full_name || user.display_name || '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ user.email }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800">
                                {{ user.role }}
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
                            {{ user.last_login ? user.last_login : 'Never' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ user.created_at }}
                        </td>
                    </tr>

                    <tr v-if="users.data.length === 0">
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                            No users found with the current filters.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (same as tickets) -->
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