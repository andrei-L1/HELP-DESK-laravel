<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import UserNavigation from '@/Components/UserNavigation.vue';
import { 
    BellIcon, 
    CheckCircleIcon, 
    EnvelopeIcon, 
    TicketIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    ArrowRightIcon,
    InboxIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    notifications: Object
});

const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role || 'user');

const Layout = computed(() => {
    const roles = {
        admin: AdminNavigation,
        agent: AgentNavigation,
        manager: ManagerNavigation,
        user: UserNavigation,
    };
    return roles[userRole.value] || UserNavigation;
});

const markAsRead = (id) => {
    router.patch(route('notifications.markAsRead', id), {}, {
        preserveScroll: true
    });
};

const markAllAsRead = () => {
    router.patch(route('notifications.markAllAsRead'), {}, {
        preserveScroll: true
    });
};

const formatTime = (time) => {
    return formatDistanceToNow(new Date(time), { addSuffix: true });
};

const getNotifIcon = (type) => {
    if (type === 'ticket_created') return TicketIcon;
    if (type === 'new_message') return EnvelopeIcon;
    if (type === 'urgent') return ExclamationTriangleIcon;
    return BellIcon;
};

const getNotifColor = (type) => {
    const colors = {
        ticket_created: 'text-indigo-600 bg-indigo-50',
        new_message: 'text-emerald-600 bg-emerald-50',
        urgent: 'text-rose-600 bg-rose-50',
        default: 'text-slate-600 bg-slate-50'
    };
    return colors[type] || colors.default;
};
</script>

<template>
    <Head title="Intelligence Center" />

    <component :is="Layout">
        <template #header-title>
            <div class="flex flex-col">
                <h1 class="text-lg font-black text-slate-900 tracking-tight leading-none">Notifications</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Direct Activity Stream</p>
            </div>
        </template>

        <div class="max-w-[1200px] mx-auto pb-20 pt-8 px-4">
            <!-- Thinned Header Section -->
            <div class="flex items-center justify-between mb-8 stagger-1 px-1">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Recent Activity</h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Summary of your latest {{ notifications.total }} notifications</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Unread:</span>
                        <span class="text-xs font-black text-rose-600">{{ notifications.data.filter(n => !n.read_at).length }}</span>
                    </div>
                    <button 
                        v-if="notifications.data.some(n => !n.read_at)"
                        @click="markAllAsRead" 
                        class="px-4 py-2 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-200"
                    >
                        Clear All Unread
                    </button>
                </div>
            </div>

            <!-- Thinned Notifications Feed -->
            <div class="stagger-2">
                <div v-if="notifications.data.length > 0" class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
                    <div 
                        v-for="(notif, index) in notifications.data" 
                        :key="notif.id"
                        @click="markAsRead(notif.id)"
                        :class="[
                            'group flex items-center gap-6 p-5 transition-all duration-300 cursor-pointer border-l-[4px]',
                            !notif.read_at 
                                ? 'bg-white border-slate-900' 
                                : 'bg-slate-50/30 border-transparent opacity-80 hover:opacity-100 grayscale-[0.3] hover:grayscale-0',
                            index !== notifications.data.length - 1 ? 'border-b border-slate-50' : ''
                        ]"
                    >
                        <!-- Thinned Icon -->
                        <div class="flex-shrink-0">
                            <div :class="['h-11 w-11 rounded-xl flex items-center justify-center shadow-sm', getNotifColor(notif.data.type)]">
                                <component :is="getNotifIcon(notif.data.type)" class="h-5 w-5 stroke-2" />
                            </div>
                        </div>

                        <!-- Thinned Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-black text-slate-900 tracking-tight">
                                        {{ notif.data.subject || 'System Update' }}
                                    </h4>
                                    <div v-if="!notif.read_at" class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">
                                    {{ formatTime(notif.created_at) }}
                                </span>
                            </div>
                            <p class="text-xs font-medium text-slate-500 line-clamp-1 group-hover:line-clamp-none transition-all duration-300">
                                {{ notif.data.message }}
                            </p>
                        </div>

                        <!-- Action Link -->
                        <Link 
                            v-if="notif.data.url"
                            :href="notif.data.url" 
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50 text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all transform group-hover:translate-x-1"
                        >
                            <ArrowRightIcon class="h-4 w-4" />
                        </Link>
                    </div>
                </div>

                <!-- Thinned Empty State -->
                <div v-else class="py-24 text-center bg-white rounded-3xl border border-dashed border-slate-200 shadow-sm">
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-200 mb-4">
                         <InboxIcon class="h-8 w-8" />
                    </div>
                    <h4 class="text-lg font-black text-slate-900 tracking-tight">No Active Signals</h4>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Check back later for system updates</p>
                </div>

                <!-- Thinned Pagination -->
                <div v-if="notifications.links && notifications.links.length > 3" class="mt-10 flex items-center justify-center gap-2">
                    <Link 
                        v-for="(link, k) in notifications.links" 
                        :key="k" 
                        :href="link.url || '#'" 
                        v-html="link.label"
                        :class="[
                            'min-w-[36px] h-9 flex items-center justify-center px-3 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all',
                            link.active 
                                ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' 
                                : 'bg-white text-slate-500 border border-slate-100 hover:border-slate-900 hover:text-slate-900',
                            !link.url ? 'opacity-30 cursor-not-allowed border-dashed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </component>
</template>

<style scoped>
.stagger-1 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
.stagger-2 { animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both; }

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

