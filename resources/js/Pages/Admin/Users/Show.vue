<script setup>
import { ref, watch, computed } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

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

const logoutSession = (sessionId) => {
    if (confirm('Are you sure you want to terminate this login session?')) {
        router.delete(route('admin.users.sessions.destroy', [props.user.id, sessionId]), {
            preserveScroll: true,
        });
    }
};

const logoutAllSessions = () => {
    if (confirm('Are you sure you want to terminate ALL active sessions for this user? They will be logged out of every device immediately.')) {
        router.delete(route('admin.users.sessions.destroy-all', props.user.id), {
            preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusClass = (status) => {
    const s = status?.trim().toLowerCase() || '';
    if (s === 'open') return 'bg-emerald-50 text-emerald-600 border-emerald-100';
    if (s === 'resolved' || s === 'closed') return 'bg-slate-50 text-slate-500 border-slate-100';
    if (s === 'pending') return 'bg-amber-50 text-amber-600 border-amber-100';
    if (s === 'urgent') return 'bg-rose-50 text-rose-600 border-rose-100';
    return 'bg-slate-50 text-slate-600 border-slate-100';
};

const activeTab = ref('profile');

const getRoleClass = (role) => {
    const r = role?.toLowerCase() || '';
    if (r.includes('admin')) return 'bg-rose-50 text-rose-600 border-rose-100';
    if (r.includes('agent') || r.includes('staff')) return 'bg-blue-50 text-blue-600 border-blue-100';
    if (r.includes('manager')) return 'bg-amber-50 text-amber-600 border-amber-100';
    return 'bg-slate-50 text-slate-600 border-slate-100';
};

const userDisplayName = computed(() => {
    return props.user.full_name || props.user.display_name || (props.user.first_name + ' ' + (props.user.last_name || '')) || props.user.username;
});
</script>

<template>
    <Head :title="`User Profile: ${userDisplayName}`" />
    
    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">{{ userDisplayName }}</span>
            </div>
        </template>

        <template #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <Link :href="route('admin.users.index')" class="hover:text-slate-700 cursor-pointer">User Management</Link>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900">User Profile</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-10 pb-20 pt-4">
            <!-- Profile Hero Section -->
            <div class="relative px-4 stagger-1">
                <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/40 overflow-hidden relative">
                    <!-- Subtle background decoration -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full blur-3xl -mr-32 -mt-32 opacity-50"></div>
                    
                    <div class="p-8 flex flex-col md:flex-row items-center md:items-start gap-8 relative z-10">
                        <!-- Avatar Section -->
                        <div class="relative group">
                            <div class="h-32 w-32 rounded-3xl overflow-hidden ring-4 ring-slate-100 shadow-xl transition-transform group-hover:scale-[1.02] duration-300">
                                <img
                                    class="h-full w-full object-cover"
                                    :src="user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(userDisplayName) + '&size=128&background=f1f5f9&color=64748b&bold=true'"
                                    :alt="userDisplayName"
                                />
                            </div>
                            <!-- Live Status Indicator -->
                            <div class="absolute -bottom-2 -right-2 h-6 w-6 rounded-xl border-4 border-white flex items-center justify-center shadow-lg" 
                                 :class="user.is_active ? 'bg-emerald-500' : 'bg-slate-300'">
                                <div v-if="user.is_active" class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse-live"></div>
                            </div>
                            <!-- Mini Avatar Switcher Icon -->
                            <label for="avatar-upload" class="absolute inset-0 flex items-center justify-center bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-3xl cursor-pointer">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </label>
                            <input id="avatar-upload" type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
                        </div>

                        <!-- Identity Info -->
                        <div class="text-center md:text-left flex-1 space-y-4">
                            <div>
                                <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-1">{{ userDisplayName }}</h2>
                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                                    <span class="px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest" :class="getRoleClass(user.role_name || 'User')">
                                        {{ user.role_name || 'System User' }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-400">@{{ user.username }}</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-200 mx-1"></span>
                                    <span class="text-xs font-bold text-slate-500 italic">{{ user.email }}</span>
                                </div>
                            </div>
                            
                            <!-- Primary Meta -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-4 border-t border-slate-50">
                                <div>
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1.5">Last Login</p>
                                    <p class="text-xs font-bold text-slate-600">{{ formatDate(user.last_login) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1.5">Member Since</p>
                                    <p class="text-xs font-bold text-slate-600">{{ formatDate(user.created_at).split(',')[0] }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1.5">Status</p>
                                    <div class="flex items-center gap-1.5 mt-0.5" :class="user.email_verified ? 'text-blue-500' : 'text-amber-500'">
                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        <span class="text-xs font-black uppercase tracking-tighter">{{ user.email_verified ? 'Verified' : 'Unverified' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1.5">Availability</p>
                                    <span class="text-xs font-bold" :class="user.is_active ? 'text-emerald-600' : 'text-slate-400'">{{ user.is_active ? 'Active Account' : 'Inactive Account' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Action -->
                        <div class="hidden lg:block">
                            <Link :href="route('admin.users.index')" class="px-6 py-3 rounded-xl border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-all flex items-center gap-2">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                Return to Directory
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-8 md:grid-cols-4 px-4 stagger-2">
                <div v-for="stat in [
                    { label: 'Cumulative Tickets', value: stats.total_tickets || 0, color: 'slate' },
                    { label: 'Active Issues', value: stats.open_tickets || 0, color: 'emerald' },
                    { label: 'Resolved Cases', value: stats.resolved_tickets || 0, color: 'blue' },
                    { label: 'Avg Latency', value: stats.avg_response_time || '—', color: 'amber' }
                ]" :key="stat.label" class="group relative py-2">
                    <div class="flex flex-col">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">{{ stat.label }}</p>
                        <p class="text-3xl font-bold text-slate-900 tracking-tighter">{{ stat.value }}</p>
                        <div class="mt-4 h-1 w-8 rounded-full bg-slate-100 overflow-hidden">
                            <div :class="{
                                'bg-slate-900': stat.color === 'slate',
                                'bg-emerald-500': stat.color === 'emerald',
                                'bg-blue-500': stat.color === 'blue',
                                'bg-amber-500': stat.color === 'amber'
                            }" class="h-full w-full opacity-50 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 px-4 stagger-3">
                <!-- Navigation Sidebar -->
                <div class="lg:col-span-1 space-y-2">
                    <button 
                        v-for="tab in [
                            { id: 'profile', label: 'Basic Information', icon: 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
                            { id: 'tickets', label: 'Ticket History', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
                            { id: 'activity', label: 'Activity Log', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
                            { id: 'security', label: 'Account Security', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' }
                        ]" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="w-full flex items-center gap-3 px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all"
                        :class="[activeTab === tab.id ? 'bg-slate-900 text-white shadow-xl shadow-slate-200' : 'text-slate-400 hover:bg-white hover:text-slate-900 border border-transparent hover:border-slate-100']"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="tab.icon" /></svg>
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Tab Panels -->
                <div class="lg:col-span-3">
                    <!-- Profile Management Panel -->
                    <div v-show="activeTab === 'profile'" class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/40 p-8 stagger-4">
                        <section class="mb-10 last:mb-0">
                            <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-6">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Personal Information</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest italic">Essential user profile and account settings.</p>
                                </div>
                            </div>

                            <form @submit.prevent="submitProfile" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">First Name *</label>
                                        <input v-model="profileForm.first_name" type="text" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                        <p v-if="profileForm.errors.first_name" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ profileForm.errors.first_name }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Last Name *</label>
                                        <input v-model="profileForm.last_name" type="text" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                        <p v-if="profileForm.errors.last_name" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ profileForm.errors.last_name }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Display Alias</label>
                                        <input v-model="profileForm.display_name" type="text" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Username Identifier *</label>
                                        <input v-model="profileForm.username" type="text" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                        <p v-if="profileForm.errors.username" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ profileForm.errors.username }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Electronic Mail *</label>
                                        <input v-model="profileForm.email" type="email" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                        <p v-if="profileForm.errors.email" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ profileForm.errors.email }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Phone Link</label>
                                        <input v-model="profileForm.phone" type="text" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Security Authorization</label>
                                        <select v-model="profileForm.role_id" required class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all">
                                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Temporal Zone</label>
                                        <select v-model="profileForm.timezone" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all">
                                            <option value="Asia/Manila">Asia/Manila (GMT+8)</option>
                                            <option value="UTC">UTC</option>
                                            <option value="Asia/Singapore">Asia/Singapore</option>
                                            <option value="Europe/London">Europe/London</option>
                                            <option value="America/New_York">America/New York</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input v-model="profileForm.is_active" type="checkbox" class="h-5 w-5 rounded-lg border-slate-200 text-slate-900 focus:ring-slate-900/10 transition-all" />
                                        <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">Grant System Access Authorization</span>
                                    </label>
                                    <button type="submit" :disabled="profileForm.processing" class="px-10 py-4 rounded-2xl bg-slate-900 text-white text-sm font-bold shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50">
                                        {{ profileForm.processing ? 'Saving...' : 'Update Information' }}
                                    </button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <!-- Ticket History Panel -->
                    <div v-show="activeTab === 'tickets'" class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/40 p-8 stagger-4">
                        <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-6">
                            <div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Ticket History</h3>
                                <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest italic">Log of all historical tickets associated with this user.</p>
                            </div>
                        </div>

                        <div v-if="tickets.length" class="space-y-4">
                            <Link 
                                v-for="ticket in tickets" 
                                :key="ticket.id" 
                                :href="route('admin.tickets.show', ticket.id)"
                                class="flex items-center justify-between p-5 rounded-2xl border border-slate-100 hover:border-slate-300/40 hover:bg-slate-50 hover:shadow-sm transition-all group"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-900 border border-slate-200 group-hover:bg-white transition-colors">
                                        #{{ String(ticket.id).padStart(3, '0') }}
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-bold text-slate-900 group-hover:text-slate-700 transition-colors">{{ ticket.subject }}</h5>
                                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 tracking-tight uppercase tracking-widest">{{ formatDate(ticket.created_at) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 rounded-lg border text-[9px] font-black uppercase tracking-tighter" :class="getStatusClass(ticket.status)">
                                        {{ ticket.status }}
                                    </span>
                                    <svg class="h-4 w-4 text-slate-300 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="py-20 text-center">
                            <div class="h-16 w-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-200">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                            </div>
                            <p class="text-xs font-bold text-slate-400 italic">No tickets found in this user's registry.</p>
                        </div>
                    </div>

                    <!-- Activity Log Panel -->
                    <div v-show="activeTab === 'activity'" class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/40 p-8 stagger-4">
                        <div class="flex items-center justify-between mb-10 border-b border-slate-50 pb-6">
                            <div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Activity Log</h3>
                                <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest italic">Detailed record of user actions and system changes.</p>
                            </div>
                        </div>

                        <div v-if="activity_logs.length" class="relative pl-8 border-l border-slate-100 ml-4 space-y-12">
                            <div v-for="log in activity_logs" :key="log.id" class="relative">
                                <span class="absolute -left-10 top-1 h-3.5 w-3.5 rounded-full border-2 border-white bg-slate-400 ring-2 ring-slate-100"></span>
                                <div class="space-y-1">
                                    <h5 class="text-xs font-black uppercase text-slate-900 tracking-widest">{{ log.action.replace(/_/g, ' ') }}</h5>
                                    <p class="text-[11px] font-bold text-slate-400">{{ log.user_name }} • {{ formatDate(log.created_at) }}</p>
                                    <div v-if="log.old_value || log.new_value" class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100/50 inline-block min-w-[300px]">
                                        <p class="text-[10px] font-bold text-slate-500 flex items-center gap-2">
                                            <span v-if="log.old_value" class="opacity-50 line-through">{{ log.old_value }}</span>
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                            <span class="text-slate-900">{{ log.new_value }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-20 text-center text-xs font-bold text-slate-400 italic border-2 border-dashed border-slate-50 rounded-3xl">
                            Silent Registry - No audit events recorded.
                        </div>
                    </div>

                    <!-- Security Vault Panel -->
                    <div v-show="activeTab === 'security'" class="space-y-8 stagger-4">
                        <!-- Account Security -->
                        <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/40 p-8">
                            <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-6">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Account Security</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest italic">Manage user credentials and active login sessions.</p>
                                </div>
                            </div>

                            <form @submit.prevent="submitPassword" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Update Password</label>
                                        <input v-model="passwordForm.password" type="password" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-sm" placeholder="••••••••" />
                                        <p v-if="passwordForm.errors.password" class="px-2 text-[10px] font-bold text-rose-500 italic">{{ passwordForm.errors.password }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Confirm Credential</label>
                                        <input v-model="passwordForm.password_confirmation" type="password" class="block w-full rounded-xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-sm" placeholder="••••••••" />
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" :disabled="passwordForm.processing || !passwordForm.password" class="px-10 py-4 rounded-2xl bg-rose-600 text-white text-sm font-bold shadow-xl shadow-rose-200 hover:bg-rose-700 transition-all disabled:opacity-50">
                                        {{ passwordForm.processing ? 'Updating...' : 'Save Password' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Active Sessions -->
                        <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/40 p-8">
                            <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-6">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Active Sessions</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest italic">Current login sessions active across all platforms.</p>
                                </div>
                                <button v-if="user.sessions?.length" @click="logoutAllSessions" class="px-4 py-2 rounded-xl text-[9px] font-black uppercase text-rose-500 hover:bg-rose-50 transition-colors">Terminate All Sessions</button>
                            </div>

                            <div v-if="user.sessions && user.sessions.length" class="space-y-4">
                                <div v-for="session in user.sessions" :key="session.id" class="flex items-center justify-between p-5 rounded-2xl border border-slate-100 bg-slate-50/30">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-white border border-slate-100 shadow-sm flex items-center justify-center">
                                            <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 21h6l-.75-4M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-900">{{ session.device }}</h5>
                                            <p class="text-[10px] font-bold text-slate-400 mt-0.5 tracking-tight uppercase tracking-widest">{{ session.ip }} • Active: {{ formatDate(session.last_active) }}</p>
                                        </div>
                                    </div>
                                    <button @click="logoutSession(session.id)" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                </div>
                            </div>
                            <div v-else class="py-12 text-center text-xs font-bold text-slate-400 italic">
                                No active transmissions detected.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>