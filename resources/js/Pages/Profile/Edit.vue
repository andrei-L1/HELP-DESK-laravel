<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    DevicePhoneMobileIcon,
    IdentificationIcon,
    CalendarDaysIcon,
    GlobeAltIcon
} from '@heroicons/vue/24/outline';
import { ShieldCheckIcon } from '@heroicons/vue/24/solid';

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
    return user.avatar || `https://ui-avatars.com/api/?name=${user.first_name}+${user.last_name}&size=256&background=818cf8&color=fff`;
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
</script>

<template>
    <Head title="Account Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                        Account Settings
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Manage your profile, security preferences, and personal details.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50/50 min-h-[calc(100vh-4rem)]">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-8">
                    
                    <!-- Left Sidebar / Summary Card -->
                    <div class="lg:w-1/3 xl:w-1/4">
                        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-2xl overflow-hidden sticky top-8">
                            <!-- Hero Background -->
                            <div class="h-32 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600 relative overflow-hidden">
                                <!-- Abstract decorative shapes -->
                                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                                <div class="absolute -bottom-10 -left-10 w-24 h-24 bg-black/10 rounded-full blur-xl"></div>
                            </div>
                            
                            <div class="px-6 pb-6 relative">
                                <!-- Avatar -->
                                <div class="-mt-16 mb-4 flex justify-center">
                                    <div class="relative rounded-full p-1.5 bg-white shadow-sm">
                                        <img 
                                            :src="userAvatar" 
                                            alt="Profile" 
                                            class="w-28 h-28 rounded-full object-cover ring-2 ring-gray-50"
                                        />
                                        <div v-if="user.email_verified" class="absolute bottom-2 right-2 bg-white rounded-full p-0.5 shadow-sm">
                                            <ShieldCheckIcon class="w-6 h-6 text-indigo-500" />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- User Info -->
                                <div class="text-center mb-6">
                                    <h3 class="text-xl font-bold text-gray-900 truncate">
                                        {{ user.display_name || `${user.first_name} ${user.last_name}` }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1 truncate font-medium">{{ user.email }}</p>
                                    
                                    <div class="mt-4 flex justify-center">
                                        <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset uppercase tracking-wider', userRoleClass]">
                                            {{ user.role || 'User' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Details List -->
                                <div class="border-t border-gray-100 pt-5 space-y-4">
                                    <div class="flex items-center text-sm">
                                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-500">
                                            <IdentificationIcon class="w-4 h-4" />
                                        </div>
                                        <div class="ml-3 text-gray-700 font-medium truncate">
                                            {{ user.first_name }} {{ user.last_name }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center text-sm">
                                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-500">
                                            <DevicePhoneMobileIcon class="w-4 h-4" />
                                        </div>
                                        <div class="ml-3 text-gray-700 font-medium">
                                            {{ user.phone || 'No phone added' }}
                                        </div>
                                    </div>

                                    <div class="flex items-center text-sm">
                                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-500">
                                            <GlobeAltIcon class="w-4 h-4" />
                                        </div>
                                        <div class="ml-3 text-gray-700 font-medium truncate whitespace-nowrap">
                                            {{ user.timezone ? user.timezone.replace('_', ' ') : 'Not set' }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center text-sm">
                                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-500">
                                            <CalendarDaysIcon class="w-4 h-4" />
                                        </div>
                                        <div class="ml-3 text-gray-700 font-medium">
                                            Joined {{ joinedDate }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Form Contents -->
                    <div class="lg:w-2/3 xl:w-3/4 space-y-8">
                        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-2xl transition-all duration-200">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>
                        
                        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-2xl p-8 transition-all duration-200">
                            <DeleteUserForm />
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
