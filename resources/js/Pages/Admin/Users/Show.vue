<script setup>
import { ref, watch } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    activity_logs: {
        type: Array,
        default: () => [],
    },
    tickets: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total_tickets: 0,
            open_tickets: 0,
            resolved_tickets: 0,
            avg_response_time: null,
        }),
    },
});

const profileForm = useForm({
    first_name: props.user.first_name ?? '',
    last_name: props.user.last_name ?? '',
    middle_name: props.user.middle_name ?? '',
    display_name: props.user.display_name ?? '',
    email: props.user.email ?? '',
    username: props.user.username ?? '',
    phone: props.user.phone ?? '',
    timezone: props.user.timezone ?? 'Asia/Manila',
    role_id: props.user.role_id ?? '',
    is_active: props.user.is_active ?? true,
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const avatarForm = useForm({
    avatar: null,
});

// Watch for user changes
watch(() => props.user, (user) => {
    profileForm.first_name = user.first_name ?? '';
    profileForm.last_name = user.last_name ?? '';
    profileForm.middle_name = user.middle_name ?? '';
    profileForm.display_name = user.display_name ?? '';
    profileForm.email = user.email ?? '';
    profileForm.username = user.username ?? '';
    profileForm.phone = user.phone ?? '';
    profileForm.timezone = user.timezone ?? 'Asia/Manila';
    profileForm.role_id = user.role_id ?? '';
    profileForm.is_active = user.is_active ?? true;
}, { immediate: true, deep: true });

const submitProfile = () => {
    profileForm.put(route('admin.users.update', props.user.id), {
        preserveScroll: true,
    });
};

const submitPassword = () => {
    passwordForm.put(route('admin.users.password.update', props.user.id), {
        onSuccess: () => passwordForm.reset('password', 'password_confirmation'),
        preserveScroll: true,
    });
};

const onAvatarChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        avatarForm.avatar = file;
        submitAvatar();
    }
};

const submitAvatar = () => {
    if (!avatarForm.avatar) return;
    
    avatarForm.post(route('admin.users.avatar.update', props.user.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            avatarForm.reset();
            const input = document.getElementById('avatar-upload');
            if (input) input.value = '';
        },
    });
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
};

const getStatusClass = (status) => {
    const map = {
        'open': 'bg-blue-100 text-blue-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'urgent': 'bg-red-100 text-red-800',
    };
    return map[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

// Tab management
const activeTab = ref('profile');
</script>

<template>
    <Head :title="`User: ${user.display_name || user.first_name + ' ' + user.last_name}`" />
    
    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">
                    User: {{ user.display_name || user.first_name + ' ' + user.last_name }}
                </h1>
                <span
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                    :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                >
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </template>

        <div class="p-6 space-y-6">
            <!-- Back button -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Link
                    :href="route('admin.users.index')"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to users
                </Link>
            </div>

            <!-- User details card (styled like ticket details) -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <dl class="divide-y divide-gray-200">
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Username</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ user.username }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Full name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ user.first_name }} {{ user.middle_name }} {{ user.last_name }}</dd>
                    </div>
                    <div v-if="user.display_name" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Display name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ user.display_name }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ user.email }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Role</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0">
                            <span class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800">
                                {{ user.role_name || 'User' }}
                            </span>
                        </dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0">
                            <span
                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            >
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Email verified</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0">
                            <span
                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                :class="user.email_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                            >
                                {{ user.email_verified ? 'Verified' : 'Unverified' }}
                            </span>
                        </dd>
                    </div>
                    <div v-if="user.phone" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ user.phone }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ user.timezone }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Last login</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ formatDate(user.last_login) }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Member since</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ formatDate(user.created_at) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Avatar upload section (styled like SLA policy) -->
            <div class="rounded-lg border border-gray-200 bg-slate-50 p-4 shadow-sm">
                <div class="flex items-center space-x-4">
                    <img
                        class="h-16 w-16 rounded-full object-cover"
                        :src="user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.first_name + ' ' + user.last_name) + '&size=64&background=475569&color=fff'"
                        :alt="user.display_name"
                    />
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">Profile picture</h3>
                        <p class="text-xs text-gray-500 mt-1">Upload a new avatar (max 2MB)</p>
                    </div>
                    <div>
                        <input
                            id="avatar-upload"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onAvatarChange"
                        />
                        <label
                            for="avatar-upload"
                            class="cursor-pointer rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        >
                            {{ avatarForm.processing ? 'Uploading...' : 'Change' }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- Stats cards (like ticket stats) -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Tickets</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_tickets }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Open Tickets</p>
                    <p class="mt-1 text-2xl font-semibold text-yellow-600">{{ stats.open_tickets }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Resolved Tickets</p>
                    <p class="mt-1 text-2xl font-semibold text-green-600">{{ stats.resolved_tickets }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Avg Response Time</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.avg_response_time || '—' }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'profile'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'profile' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Profile
                    </button>
                    <button
                        @click="activeTab = 'tickets'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'tickets' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Tickets ({{ tickets.length }})
                    </button>
                    <button
                        @click="activeTab = 'activity'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'activity' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Activity
                    </button>
                    <button
                        @click="activeTab = 'security'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'security' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Security
                    </button>
                </nav>
            </div>

            <!-- Profile Tab - Edit profile form (styled like ticket assignment) -->
            <div v-if="activeTab === 'profile'" class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Edit profile</h2>
                    <form @submit.prevent="submitProfile" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="first_name">
                                    First name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="first_name"
                                    v-model="profileForm.first_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="profileForm.errors.first_name" class="mt-1 text-sm text-red-600">
                                    {{ profileForm.errors.first_name }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="last_name">
                                    Last name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="last_name"
                                    v-model="profileForm.last_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="profileForm.errors.last_name" class="mt-1 text-sm text-red-600">
                                    {{ profileForm.errors.last_name }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="middle_name">
                                    Middle name
                                </label>
                                <input
                                    id="middle_name"
                                    v-model="profileForm.middle_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="display_name">
                                    Display name
                                </label>
                                <input
                                    id="display_name"
                                    v-model="profileForm.display_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="email">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="profileForm.email"
                                    type="email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600">
                                    {{ profileForm.errors.email }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="username">
                                    Username <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="username"
                                    v-model="profileForm.username"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p v-if="profileForm.errors.username" class="mt-1 text-sm text-red-600">
                                    {{ profileForm.errors.username }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="phone">
                                    Phone
                                </label>
                                <input
                                    id="phone"
                                    v-model="profileForm.phone"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700" for="timezone">
                                    Timezone
                                </label>
                                <select
                                    id="timezone"
                                    v-model="profileForm.timezone"
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
                                <label class="block text-sm font-medium text-gray-700" for="role_id">
                                    Role
                                </label>
                                <select
                                    id="role_id"
                                    v-model="profileForm.role_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <p v-if="profileForm.errors.role_id" class="mt-1 text-sm text-red-600">
                                    {{ profileForm.errors.role_id }}
                                </p>
                            </div>
                            
                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="profileForm.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Account active</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex justify-center rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                :disabled="profileForm.processing"
                            >
                                {{ profileForm.processing ? 'Saving…' : 'Save changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tickets Tab (styled like messages) -->
            <div v-if="activeTab === 'tickets'" class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Tickets</h2>
                    <div v-if="tickets.length" class="space-y-4">
                        <div
                            v-for="ticket in tickets"
                            :key="ticket.id"
                            class="rounded-md border border-gray-200 bg-gray-50 p-4"
                        >
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                <span>#{{ ticket.ticket_number }}</span>
                                <span>{{ formatDate(ticket.created_at) }}</span>
                            </div>
                            <Link
                                :href="route('admin.tickets.show', ticket.id)"
                                class="text-sm font-medium text-slate-600 hover:text-slate-800"
                            >
                                {{ ticket.subject }}
                            </Link>
                            <div class="mt-2 flex gap-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="getStatusClass(ticket.status)"
                                >
                                    {{ ticket.status }}
                                </span>
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="{
                                        'bg-red-100 text-red-800': ticket.priority?.toLowerCase() === 'high' || ticket.priority?.toLowerCase() === 'urgent',
                                        'bg-yellow-100 text-yellow-800': ticket.priority?.toLowerCase() === 'medium',
                                        'bg-green-100 text-green-800': ticket.priority?.toLowerCase() === 'low',
                                    }"
                                >
                                    {{ ticket.priority }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">No tickets found for this user.</p>
                </div>
            </div>

            <!-- Activity Tab (styled like activity log) -->
            <div v-if="activeTab === 'activity'" class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Activity</h2>
                    <ul class="space-y-2">
                        <li
                            v-for="log in activity_logs"
                            :key="log.id"
                            class="flex flex-col text-sm border-l-2 border-gray-200 pl-3 py-1"
                        >
                            <span class="text-gray-900 font-medium">{{ log.action.replace(/_/g, ' ') }}</span>
                            <span class="text-gray-500">{{ log.user_name }} · {{ formatDate(log.created_at) }}</span>
                            <span v-if="log.old_value || log.new_value" class="text-gray-600 text-xs">
                                {{ log.old_value ? log.old_value + ' → ' : '' }}{{ log.new_value }}
                            </span>
                        </li>
                        <p v-if="activity_logs.length === 0" class="text-sm text-gray-500">No activity yet.</p>
                    </ul>
                </div>
            </div>

            <!-- Security Tab (styled like ticket forms) -->
            <div v-if="activeTab === 'security'" class="space-y-6">
                <!-- Change password -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Change password</h2>
                    <form @submit.prevent="submitPassword" class="space-y-3">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">New password</label>
                            <input
                                id="password"
                                v-model="passwordForm.password"
                                type="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                            />
                            <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">
                                {{ passwordForm.errors.password }}
                            </p>
                        </div>
                        
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm new password</label>
                            <input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                            />
                        </div>
                        
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                :disabled="passwordForm.processing || !passwordForm.password"
                            >
                                {{ passwordForm.processing ? 'Updating…' : 'Update password' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Session management -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Active sessions</h2>
                    <ul v-if="user.sessions && user.sessions.length" class="space-y-2">
                        <li
                            v-for="session in user.sessions"
                            :key="session.id"
                            class="flex items-center justify-between rounded border border-gray-200 px-3 py-2 text-sm"
                        >
                            <div>
                                <span class="text-gray-700">{{ session.device }}</span>
                                <span class="text-gray-500 text-xs ml-2">{{ session.ip }} · {{ formatDate(session.last_active) }}</span>
                            </div>
                            <button
                                @click="$emit('logout-session', session.id)"
                                class="text-red-600 hover:text-red-800 font-medium text-xs"
                            >
                                Logout
                            </button>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">No active sessions found.</p>
                    
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="$emit('logout-all-sessions', user.id)"
                            class="text-sm text-red-600 hover:text-red-800 font-medium"
                        >
                            Logout all sessions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>