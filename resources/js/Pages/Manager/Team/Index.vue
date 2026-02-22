<script setup>
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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

const searchInput = ref(props.filters.search || '');

const applyFilter = (key, value) => {
    router.get(
        route('manager.team.index'),
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
        route('manager.team.index'),
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
        ? 'bg-emerald-100 text-emerald-800' 
        : 'bg-red-100 text-red-800';
};

const getVerifiedClass = (isVerified) => {
    return isVerified 
        ? 'bg-blue-100 text-blue-800' 
        : 'bg-yellow-100 text-yellow-800';
};
</script>

<template>
    <Head title="My Team" />

    <ManagerNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">My Team</h1>
        </template>

        <div class="p-6 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Team</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Active</p>
                    <p class="mt-1 text-2xl font-semibold text-emerald-600">{{ stats.active }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Inactive</p>
                    <p class="mt-1 text-2xl font-semibold text-red-600">{{ stats.inactive }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Verified</p>
                    <p class="mt-1 text-2xl font-semibold text-blue-600">{{ stats.verified }}</p>
                 </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Unverified</p>
                    <p class="mt-1 text-2xl font-semibold text-yellow-600">{{ stats.unverified }}</p>
                </div>
            </div>

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
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 pr-10"
                                placeholder="Name, username or email"
                            />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilter('status', $event.target.value)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">All Statuses</option>
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select
                            v-model="filters.role"
                            @change="applyFilter('role', $event.target.value)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">All Roles</option>
                            <option v-for="role in rolesList" :key="role.id" :value="role.id">
                                {{ role.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex items-end">
                        <button
                            @click="resetFilters"
                            class="text-sm text-gray-600 hover:text-emerald-700 font-medium"
                        >
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Team Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Verified</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Login</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="hover:bg-gray-50 transition-colors"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 flex-shrink-0">
                                        <img
                                            class="h-8 w-8 rounded-full object-cover"
                                            :src="user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.full_name || user.display_name) + '&size=32&background=047857&color=fff'"
                                            :alt="user.display_name"
                                        />
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ user.display_name }}</p>
                                        <p class="text-xs text-gray-500">{{ user.full_name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ user.username }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ user.email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="getStatusClass(user.is_active)"
                                >
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="getVerifiedClass(user.email_verified)"
                                >
                                    {{ user.email_verified ? 'Verified' : 'Unverified' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(user.last_login) }}
                            </td>
                        </tr>

                        <tr v-if="users.data.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No team members found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="users.links && users.links.length > 3"
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
                                ? 'bg-emerald-700 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                            !link.url ? 'cursor-default opacity-50' : '',
                        ]"
                    />
                </div>
            </div>
        </div>
    </ManagerNavigation>
</template>
