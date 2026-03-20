<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';
import Swal from 'sweetalert2';

const isOpen = ref(false);
const page = usePage();

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

const audioUnlocked = ref(false);

const unlockAudio = () => {
    if (audioUnlocked.value) return;
    
    // Play a short silent sound to unlock audio context on first interaction
    const silentAudio = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');
    silentAudio.volume = 0; // Silent
    silentAudio.play().then(() => {
        audioUnlocked.value = true;
        window.removeEventListener('click', unlockAudio);
        window.removeEventListener('keydown', unlockAudio);
        console.log('Audio context unlocked');
    }).catch(() => {
        // Still blocked, will try again on next click
    });
};

const playNotificationSound = () => {
    // Only attempt if unlocked or browser allows
    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');
    audio.volume = 0.4;
    audio.play().catch(e => {
        console.log('Audio still blocked. User interaction required.');
    });
};

const unreadCount = computed(() => page.props.auth.user.unread_notifications_count);
const notifications = computed(() => page.props.auth.user.latest_notifications);

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
    
    // Unlock audio context on first user interaction (Browser Requirement)
    window.addEventListener('click', unlockAudio);
    window.addEventListener('keydown', unlockAudio);

    // Listen for real-time notifications via Pusher/Echo
    if (page.props.auth.user) {
        window.Echo.private(`App.Models.User.${page.props.auth.user.id}`)
            .notification((notification) => {
                // Play subtle sound and show premium toast
                playNotificationSound();
                
                Toast.fire({
                    icon: 'info',
                    title: notification.subject || 'New Activity',
                    text: notification.message || 'You have a new message.',
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'rounded-2xl border-l-[6px] border-slate-900 shadow-2xl',
                        title: 'text-sm font-black uppercase tracking-tight',
                        htmlContainer: 'text-xs font-medium text-slate-500'
                    }
                });
                
                // Add to the list (top) if not already exists
                const existingIndex = page.props.auth.user.latest_notifications.findIndex(n => n.id === notification.id);
                if (existingIndex === -1) {
                    const newNotif = {
                        id: notification.id,
                        data: notification, // Echo sends the data object directly
                        created_at: new Date().toISOString(),
                        read_at: null
                    };
                    page.props.auth.user.latest_notifications.unshift(newNotif);
                    
                    // Increment count only for truly new ones
                    page.props.auth.user.unread_notifications_count++;
                    
                    // Keep only latest 10
                    if (page.props.auth.user.latest_notifications.length > 10) {
                        page.props.auth.user.latest_notifications.pop();
                    }
                }
            });
    }
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
    window.removeEventListener('click', unlockAudio);
    window.removeEventListener('keydown', unlockAudio);
});

const markAllRead = () => {
    router.patch(route('notifications.markAllAsRead'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Unread count will update automatically via shared props
        }
    });
};

const markAsRead = (id) => {
    router.patch(route('notifications.markAsRead', id), {}, {
        preserveScroll: true
    });
};

const formatTime = (time) => {
    return formatDistanceToNow(new Date(time), { addSuffix: true });
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
                            @click="markAsRead(notif.id)"
                            class="group relative flex items-start gap-4 p-4 rounded-2xl hover:bg-slate-50 transition-all cursor-pointer overflow-hidden"
                        >
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-slate-900 rounded-r-full scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                            
                            <div class="relative flex-shrink-0 mt-1">
                                <div :class="{
                                    'bg-rose-50 text-rose-600': notif.data.type === 'urgent',
                                    'bg-amber-50 text-amber-600': notif.data.type === 'warning',
                                    'bg-emerald-50 text-emerald-600': notif.data.type === 'success',
                                    'bg-blue-50 text-blue-100': notif.data.type === 'ticket_created',
                                    'bg-slate-50 text-slate-600': !notif.data.type,
                                }" class="h-10 w-10 rounded-xl flex items-center justify-center font-bold text-xs">
                                    <svg v-if="notif.data.type === 'ticket_created'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div v-if="!notif.read_at" class="absolute -top-1 -right-1 h-3 w-3 bg-rose-500 rounded-full border-2 border-white shadow-sm"></div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-0.5">
                                    <h4 class="text-[13px] font-black text-slate-900 truncate leading-none pt-1">{{ notif.data.subject || 'Notification' }}</h4>
                                    <span class="text-[10px] font-bold text-slate-400 whitespace-nowrap">{{ formatTime(notif.created_at) }}</span>
                                </div>
                                <p class="text-[11px] font-medium text-slate-500 line-clamp-2 leading-relaxed">{{ notif.data.message }}</p>
                            </div>
                        </div>
                        <div v-if="notifications.length === 0" class="p-8 text-center bg-slate-50 rounded-2xl mx-2">
                             <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No notifications yet</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                    <Link :href="route('notifications.index')" class="block w-full text-center py-2.5 rounded-xl bg-white border border-slate-200 text-[10px] font-black text-slate-900 uppercase tracking-[0.2em] shadow-sm hover:bg-slate-50 transition-all active:scale-[0.98]">
                        View All Notifications
                    </Link>
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
