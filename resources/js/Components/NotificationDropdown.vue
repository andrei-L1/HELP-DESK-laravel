<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isOpen = ref(false);
const unreadCount = ref(3);

const notifications = ref([
    {
        id: 1,
        title: 'New High Priority Ticket',
        message: 'A critical server issue has been reported by John Doe.',
        time: '5m ago',
        type: 'urgent',
        unread: true,
    },
    {
        id: 2,
        title: 'SLA Breach Warning',
        message: 'Ticket #1234 approach its resolution deadline.',
        time: '12m ago',
        type: 'warning',
        unread: true,
    },
    {
        id: 3,
        title: 'User Feedback Received',
        message: 'A new 5-star rating was submitted for Agent Sarah.',
        time: '1h ago',
        type: 'success',
        unread: true,
    },
    {
        id: 4,
        title: 'System Update Completed',
        message: 'All system patches were applied successfully.',
        time: '3h ago',
        type: 'info',
        unread: false,
    }
]);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (e) => {
    if (isOpen.value && !e.target.closest('#notification-button') && !e.target.closest('#notification-dropdown')) {
        isOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
});

const markAllRead = () => {
    notifications.value.forEach(n => n.unread = false);
    unreadCount.value = 0;
};
</script>

<template>
    <div class="relative">
        <!-- Notification Bell -->
        <button
            id="notification-button"
            @click="toggleDropdown"
            class="relative p-2.5 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-white transition-all duration-300 group"
            :class="{ 'bg-white shadow-sm text-slate-900': isOpen }"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            
            <span v-if="unreadCount > 0" class="absolute top-2 right-2 h-2.5 w-2.5 bg-rose-500 rounded-full border-2 border-slate-50 ring-2 ring-rose-500/20 group-hover:scale-110 transition-transform"></span>
        </button>

        <!-- Dropdown Menu -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-2 scale-95"
        >
            <div 
                v-if="isOpen"
                id="notification-dropdown"
                class="absolute right-0 mt-4 w-96 max-h-[550px] bg-white rounded-[2rem] shadow-2xl shadow-slate-900/10 border border-slate-100 overflow-hidden z-50 flex flex-col"
            >
                <!-- Header -->
                <div class="p-6 pb-4 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Notifications</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">You have {{ unreadCount }} unread messages</p>
                    </div>
                    <button @click="markAllRead" class="text-[10px] font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest transition-colors">Mark all as read</button>
                </div>

                <!-- Notifications List -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-2">
                    <div class="space-y-1">
                        <div 
                            v-for="notif in notifications" 
                            :key="notif.id"
                            class="group relative flex items-start gap-4 p-4 rounded-2xl hover:bg-slate-50 transition-all cursor-pointer overflow-hidden"
                        >
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-slate-900 rounded-r-full scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                            
                            <div class="relative flex-shrink-0 mt-1">
                                <div :class="{
                                    'bg-rose-50 text-rose-600': notif.type === 'urgent',
                                    'bg-amber-50 text-amber-600': notif.type === 'warning',
                                    'bg-emerald-50 text-emerald-600': notif.type === 'success',
                                    'bg-blue-50 text-blue-600': notif.type === 'info',
                                }" class="h-10 w-10 rounded-xl flex items-center justify-center font-bold text-xs">
                                    <svg v-if="notif.type === 'urgent'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    <svg v-if="notif.type === 'warning'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <svg v-if="notif.type === 'success'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <svg v-if="notif.type === 'info'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div v-if="notif.unread" class="absolute -top-1 -right-1 h-3 w-3 bg-rose-500 rounded-full border-2 border-white shadow-sm"></div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-0.5">
                                    <h4 class="text-[13px] font-black text-slate-900 truncate leading-none pt-1">{{ notif.title }}</h4>
                                    <span class="text-[10px] font-bold text-slate-400 whitespace-nowrap">{{ notif.time }}</span>
                                </div>
                                <p class="text-[11px] font-medium text-slate-500 line-clamp-2 leading-relaxed">{{ notif.message }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                    <button class="w-full py-2.5 rounded-xl bg-white border border-slate-200 text-[10px] font-black text-slate-900 uppercase tracking-[0.2em] shadow-sm hover:bg-slate-50 transition-all active:scale-[0.98]">
                        View Intelligence Feed
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
