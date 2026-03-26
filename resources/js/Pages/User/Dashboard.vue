<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';
import TrendCard from '@/Components/TrendCard.vue';
import { useRealtimeDashboard } from '@/Composables/useRealtimeDashboard';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recent_tickets: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const userId = page.props.auth.user.id;
const { isLive, lastUpdated } = useRealtimeDashboard(`user.${userId}.tickets`, ['stats', 'recent_tickets']);

// Helper function to convert hex to RGB
const hexToRgb = (hex) => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
};
</script>

<template>
    <Head title="User Dashboard" />

    <UserNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold tracking-tight text-blue-950">My Dashboard</span>
                <div v-if="isLive" class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-blue-100/80 border border-blue-200/80 ml-2" title="Real-time updates active">
                    <div class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                    <span class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Live</span>
                </div>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-12 pb-20 pt-4">
            <!-- New Minimalist Welcome -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
                <div class="space-y-1">
                    <h2 class="text-3xl font-bold tracking-tight text-blue-950">Welcome back, {{ $page.props.auth.user.first_name }}.</h2>
                    <p class="font-medium text-blue-900/60">Here's the current status of your support requests.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('user.tickets.create')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        New Ticket
                    </Link>
                    <Link :href="route('user.tickets.index')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-blue-950 text-sm font-bold border border-blue-100 shadow-sm hover:border-blue-200 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        All Tickets
                    </Link>
                </div>
            </div>

            <!-- Stats Using TrendCard -->
            <div class="grid gap-8 md:grid-cols-3 px-1">
                <Link :href="route('user.tickets.index')">
                    <TrendCard 
                        label="Total Tickets" 
                        :value="stats.total_tickets" 
                        :trend="[5, 12, 18, 14, 25, 20, 28]" 
                        percentage="+15%"
                        color="blue"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
                <Link :href="route('user.tickets.index', { status: 'open' })">
                    <TrendCard 
                        label="Open Tickets" 
                        :value="stats.open_tickets" 
                        :trend="[2, 5, 3, 8, 4, 6, 5]" 
                        percentage="+2%"
                        color="amber"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
                <Link :href="route('user.tickets.index', { status: 'closed' })">
                    <TrendCard 
                        label="Closed Tickets" 
                        :value="stats.closed_tickets" 
                        :trend="[3, 7, 15, 6, 21, 14, 23]" 
                        percentage="+10%"
                        color="emerald"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
            </div>

            <!-- Main Content Area -->
            <div class="grid gap-10 lg:grid-cols-12 px-1">
                
                <!-- Left: Quick Info & Actions -->
                <div class="lg:col-span-4 space-y-8">
                    <div class="bg-blue-50/50 rounded-2xl p-8 border border-blue-100/50 relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-100 rounded-full blur-3xl opacity-60 transition-transform group-hover:scale-110 duration-700"></div>
                        <h3 class="text-sm font-black text-blue-900/60 uppercase tracking-widest mb-4 relative z-10 transition-colors group-hover:text-blue-900">Need Help?</h3>
                        <p class="text-blue-950 font-medium mb-6 relative z-10 text-sm">Check our knowledge base before submitting a ticket. You might find your answer instantly.</p>
                        <Link :href="route('public.kb.index')" class="flex items-center justify-between p-4 rounded-xl bg-white border border-blue-100 hover:border-blue-200 hover:shadow-md transition-all relative z-10">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                </div>
                                <span class="font-bold text-blue-950">Browse Articles</span>
                            </div>
                            <svg class="h-4 w-4 text-blue-300 group-hover:translate-x-1 group-hover:text-blue-600 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </Link>
                    </div>
                </div>

                <!-- Right: Recent Tickets -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-xl font-bold tracking-tight text-blue-950">Recent Requests</h3>
                        <Link :href="route('user.tickets.index')" class="text-xs font-bold text-blue-400 hover:text-blue-600 transition-colors">View all requests</Link>
                    </div>

                    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
                        <div v-if="recent_tickets.length > 0" class="divide-y divide-blue-50">
                            <Link v-for="ticket in recent_tickets" :key="ticket.id" :href="route('user.tickets.show', ticket.id)" 
                                class="flex flex-col sm:flex-row sm:items-center justify-between py-5 px-7 hover:bg-blue-50/30 transition-all group block">
                                <div class="flex items-start gap-5">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50/50 text-[10px] font-black text-blue-400 border border-blue-100 group-hover:border-blue-200 group-hover:bg-white group-hover:text-blue-600 transition-all shadow-sm">
                                        #{{ ticket.ticket_number.toString().slice(-3) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-blue-950 truncate mb-1 group-hover:text-blue-700 transition-colors">{{ ticket.subject }}</h4>
                                        <div class="flex items-center gap-3 text-[11px] font-medium text-blue-900/40">
                                            <span>Requested on {{ ticket.created_at }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 sm:mt-0 flex items-center gap-4">
                                     <div class="flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: ticket.priority.color_hex }"></span>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest hidden sm:block">Priority</span>
                                    </div>
                                    <div class="h-8 w-px bg-blue-50 hidden sm:block"></div>
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black tracking-widest border" :style="{ color: ticket.status.color_hex, backgroundColor: ticket.status.color_hex + '10', borderColor: ticket.status.color_hex + '20' }">
                                        {{ ticket.status.title }}
                                    </span>
                                </div>
                            </Link>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-24 flex flex-col items-center text-center px-4">
                            <div class="relative mb-8">
                                <div class="absolute inset-0 bg-blue-50 rounded-full blur-3xl opacity-50 scale-150"></div>
                                <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-xl shadow-blue-100/50 border border-blue-50">
                                     <svg class="h-10 w-10 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-xl font-black text-blue-950 tracking-tight mb-2">No tickets yet</h4>
                            <p class="text-blue-900/50 max-w-xs font-medium italic">You haven't submitted any support requests.</p>
                            <Link :href="route('user.tickets.create')" class="mt-8 px-6 py-2.5 rounded-xl bg-white border border-blue-200 text-sm font-bold text-blue-900 shadow-sm hover:bg-blue-50 transition-all">Create Your First Ticket</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserNavigation>
</template>

<style scoped>
/* Scoped styles removed in favor of tailwind utility classes */
</style>