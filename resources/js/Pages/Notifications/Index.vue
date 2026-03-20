<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';

const props = defineProps({
    notifications: Object
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
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-widest">
                    Your Intelligence Feed
                </h2>
                <button 
                    v-if="notifications.data.some(n => !n.read_at)"
                    @click="markAllAsRead" 
                    class="text-[10px] font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest transition-colors"
                >
                    Mark all as read
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-2xl shadow-slate-900/5 sm:rounded-[2rem] border border-slate-100">
                    <div class="p-8">
                        <div v-if="notifications.data.length > 0" class="space-y-4">
                            <div 
                                v-for="notif in notifications.data" 
                                :key="notif.id"
                                @click="markAsRead(notif.id)"
                                :class="{ 'bg-slate-50 border-transparent': notif.read_at, 'bg-white border-blue-100 shadow-lg shadow-blue-500/5': !notif.read_at }"
                                class="group relative flex items-start gap-6 p-6 rounded-3xl border transition-all cursor-pointer hover:scale-[1.01]"
                            >
                                <div v-if="!notif.read_at" class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-12 bg-blue-600 rounded-r-full"></div>
                                
                                <div class="relative flex-shrink-0">
                                    <div :class="{
                                        'bg-rose-50 text-rose-600': notif.data.type === 'urgent',
                                        'bg-amber-50 text-amber-600': notif.data.type === 'warning',
                                        'bg-emerald-50 text-emerald-600': notif.data.type === 'success',
                                        'bg-blue-50 text-blue-600': notif.data.type === 'ticket_created' || !notif.data.type,
                                    }" class="h-14 w-14 rounded-2xl flex items-center justify-center font-bold text-xl ring-8 ring-white">
                                        <svg v-if="notif.data.type === 'ticket_created'" class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                        <svg v-else class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-4 mb-1">
                                        <h4 class="text-lg font-black text-slate-900 truncate tracking-tight">{{ notif.data.subject || 'Notification' }}</h4>
                                        <span class="text-xs font-bold text-slate-400 whitespace-nowrap">{{ formatTime(notif.created_at) }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-slate-500 leading-relaxed mb-4">{{ notif.data.message }}</p>
                                    
                                    <div class="flex items-center gap-3">
                                        <Link 
                                            v-if="notif.data.url"
                                            :href="notif.data.url" 
                                            class="inline-flex items-center text-[10px] font-black text-blue-600 uppercase tracking-widest hover:text-blue-700 transition-colors"
                                        >
                                            View Related Resource
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-20 text-center">
                            <div class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-300 mb-6">
                                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-widest">Inbox Zero!</h3>
                            <p class="text-sm font-medium text-slate-400 mt-2">You don't have any notifications at the moment.</p>
                        </div>

                        <!-- Pagination (Simple) -->
                        <div v-if="notifications.links && notifications.links.length > 3" class="mt-12 flex justify-center gap-2">
                             <Link 
                                v-for="(link, k) in notifications.links" 
                                :key="k" 
                                :href="link.url || '#'" 
                                v-html="link.label"
                                :class="{ 'bg-slate-900 text-white shadow-xl shadow-slate-900/20': link.active, 'bg-white text-slate-500 hover:bg-slate-50': !link.active, 'opacity-50 pointer-events-none': !link.url }"
                                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest border border-slate-100 transition-all"
                             />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
