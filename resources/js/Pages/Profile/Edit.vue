<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import UserNavigation from '@/Components/UserNavigation.vue';
import UpdateNotificationSettingsForm from './Partials/UpdateNotificationSettingsForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    DevicePhoneMobileIcon,
    IdentificationIcon,
    CalendarDaysIcon,
    GlobeAltIcon,
    PhotoIcon
} from '@heroicons/vue/24/outline';
import { ShieldCheckIcon, CameraIcon } from '@heroicons/vue/24/solid';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const userAvatar = computed(() => {
    if (user.hide_google_avatar && !user.avatar_url) {
        return `https://ui-avatars.com/api/?name=${user.first_name}+${user.last_name}&size=256&background=4f46e5&color=fff`;
    }
    return user.avatar_url || `https://ui-avatars.com/api/?name=${user.first_name}+${user.last_name}&size=256&background=818cf8&color=fff`;
});

const roleColors = {
    admin: 'bg-rose-100 text-rose-700 ring-rose-600/20',
    manager: 'bg-amber-100 text-amber-700 ring-amber-600/20',
    agent: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
    user: 'bg-indigo-100 text-indigo-700 ring-indigo-600/20',
};

const userRoleClass = computed(() => {
    return roleColors[user.role] || roleColors.user;
});

const joinedDate = computed(() => {
    if (!user.created_at) return 'Recently';
    return new Date(user.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
});

const navigationComponents = {
    admin: AdminNavigation,
    agent: AgentNavigation,
    manager: ManagerNavigation,
    user: UserNavigation,
};

const currentNavigation = computed(() => navigationComponents[user.role] || UserNavigation);
</script>

<template>
    <Head title="Account Settings" />
    
    <component :is="currentNavigation">
        <template v-if="user.role !== 'admin'" #header-title>
            <div class="flex flex-col">
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Account Settings</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Profile Management</p>
            </div>
        </template>

        <template v-else #header-title>
            <div class="flex flex-col">
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Account Settings</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Profile Management</p>
            </div>
        </template>

        <template v-if="user.role === 'admin'" #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900">Profile</span>
            </div>
        </template>

        <div class="py-10 min-h-screen">
            <div class="mx-auto max-w-[1400px]">
                <div class="flex flex-col lg:flex-row gap-10">
                    
                    <!-- Left Sidebar / Summary Card -->
                    <div class="lg:w-1/3 xl:w-[350px]">
                        <div class="bg-white shadow-xl shadow-slate-200/50 rounded-[2.5rem] overflow-hidden border border-slate-200/50 sticky top-28 transition-all hover:shadow-2xl">
                            <!-- Hero Background -->
                            <div class="h-40 bg-slate-900 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-transparent to-emerald-500/20"></div>
                                <!-- Abstract decorative shapes -->
                                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>
                                
                                <div class="absolute top-6 left-6">
                                    <span class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-[9px] font-black text-white uppercase tracking-[0.2em] border border-white/10">Member since {{ new Date(user.created_at).getFullYear() }}</span>
                                </div>
                            </div>
                            
                            <div class="px-8 pb-10 relative">
                                <!-- Avatar -->
                                <div class="-mt-20 mb-6 flex justify-center">
                                    <div class="relative rounded-[2.5rem] p-2 bg-white shadow-2xl">
                                        <div class="w-32 h-32 rounded-[2rem] overflow-hidden ring-4 ring-slate-50">
                                            <img 
                                                :src="userAvatar" 
                                                alt="Profile" 
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                        <div v-if="user.email_verified" class="absolute -bottom-2 -right-2 bg-white rounded-2xl p-1.5 shadow-xl border border-slate-50">
                                            <div class="bg-emerald-500 rounded-xl p-1">
                                                <ShieldCheckIcon class="w-5 h-5 text-white" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- User Info -->
                                <div class="text-center mb-10">
                                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter">
                                        {{ user.display_name || `${user.first_name} ${user.last_name}` }}
                                    </h3>
                                    <p class="text-sm text-slate-500 mt-1 font-bold italic">{{ user.email }}</p>
                                    
                                    <div class="mt-6 flex justify-center gap-2">
                                        <span :class="['inline-flex items-center rounded-xl px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.15em] border transition-all', userRoleClass]">
                                            {{ user.role || 'User' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Details List -->
                                <div class="space-y-6 pt-6 border-t border-slate-50">
                                    <div class="flex items-center group cursor-default">
                                        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all">
                                            <IdentificationIcon class="w-5 h-5" />
                                        </div>
                                        <div class="ml-4 flex flex-col">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Name</span>
                                            <span class="text-sm text-slate-700 font-bold truncate">
                                                {{ user.first_name }} {{ user.last_name }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center group cursor-default">
                                        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all">
                                            <DevicePhoneMobileIcon class="w-5 h-5" />
                                        </div>
                                        <div class="ml-4 flex flex-col">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Phone Number</span>
                                            <span class="text-sm text-slate-700 font-bold">
                                                {{ user.phone || 'No phone added' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Form Contents -->
                    <div class="flex-1 space-y-10">
                        <div class="bg-white shadow-xl shadow-slate-200/50 rounded-[2.5rem] border border-slate-200/50 overflow-hidden transition-all hover:shadow-2xl">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>
                        
                        <div class="bg-white shadow-xl shadow-slate-200/50 rounded-[2.5rem] border border-slate-200/50 overflow-hidden transition-all hover:shadow-2xl">
                             <UpdateNotificationSettingsForm />
                        </div>
                        
                        <div class="bg-rose-50/50 rounded-[2.5rem] border border-rose-100 p-10 transition-all hover:bg-rose-50">
                            <DeleteUserForm />
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </component>
</template>
