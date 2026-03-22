<!-- resources/js/Pages/Agent/Dashboard.vue -->
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import TrendCard from '@/Components/TrendCard.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            my_open_tickets: 0,
            my_pending_tickets: 0,
            my_resolved_tickets: 0,
            total_my_tickets: 0,
            avg_response_time: '0h',
        }),
    },
    recent_tickets: {
        type: Array,
        default: () => [],
    },
});

// Since Agent backend is using text-based status/priority instead of objects,
// we provide hex mapping for the generic strings so they fit the minimalist UI perfectly.
const getStatusHex = (status) => {
    const map = {
        'open': '#3b82f6',     // Blue
        'pending': '#f59e0b',  // Amber
        'resolved': '#10b981', // Emerald
        'closed': '#64748b',   // Slate
        'urgent': '#ef4444',   // Red
    };
    return map[status?.toLowerCase()] || '#64748b';
};

const getPriorityHex = (priority) => {
    const map = {
        'high': '#ef4444',     // Red
        'urgent': '#ef4444',   // Red
        'medium': '#f59e0b',   // Amber
        'low': '#10b981',      // Emerald
    };
    return map[priority?.toLowerCase()] || '#64748b';
};
</script>

<template>
    <Head title="Agent Dashboard" />

    <AgentNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold tracking-tight text-emerald-950">Agent Workspace</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-12 pb-20 pt-4">
            <!-- New Minimalist Welcome -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
                <div class="space-y-1">
                    <h2 class="text-3xl font-bold tracking-tight text-emerald-950">Welcome, {{ $page.props.auth.user.first_name }}.</h2>
                    <p class="font-medium text-emerald-900/60">Here's the summary of your support queue.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('agent.tickets.create')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        New Ticket
                    </Link>
                    <Link :href="route('agent.tickets.index')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-950 text-sm font-bold border border-emerald-100 shadow-sm hover:border-emerald-200 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        All Tickets
                    </Link>
                </div>
            </div>

            <!-- Stats Using TrendCard -->
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4 px-1">
                <Link :href="route('agent.tickets.index')">
                    <TrendCard 
                        label="My Assignments" 
                        :value="stats.total_my_tickets" 
                        :trend="[2, 4, 3, 5, 2, 6, 8]" 
                        percentage="+12%"
                        color="slate"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
                <Link :href="route('agent.tickets.index', { status: 'open' })">
                    <TrendCard 
                        label="Open Tasks" 
                        :value="stats.my_open_tickets" 
                        :trend="[5, 4, 3, 6, 4, 3, 2]" 
                        percentage="-5%"
                        color="emerald"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
                <Link :href="route('agent.tickets.index', { status: 'pending' })">
                    <TrendCard 
                        label="Pending Reply" 
                        :value="stats.my_pending_tickets" 
                        :trend="[1, 2, 4, 2, 3, 1, 3]" 
                        percentage="+8%"
                        color="amber"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
                <!-- Replacing traditional stat with average response time inside TrendCard -->
                <TrendCard 
                    label="Response Time" 
                    :value="stats.avg_response_time" 
                    :trend="[8, 7, 7, 6, 5, 5, 4]" 
                    percentage="-10%"
                    color="cyan"
                />
            </div>

            <!-- Main Content Area -->
            <div class="grid gap-10 lg:grid-cols-12 px-1">
                
                <!-- Left: Quick Info & Actions -->
                <div class="lg:col-span-4 space-y-8">
                    <!-- Shift Metrics -->
                    <div class="bg-white rounded-2xl p-8 border border-emerald-100 shadow-sm">
                        <h3 class="text-sm font-black text-emerald-900/60 uppercase tracking-widest mb-6 transition-colors hover:text-emerald-900">Shift Metrics</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-50/50 border border-emerald-50 hover:bg-white hover:border-emerald-100 hover:shadow-md transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="bg-emerald-100 text-emerald-600 h-10 w-10 rounded-lg flex items-center justify-center font-bold text-xs uppercase transition-transform group-hover:scale-110">
                                        R
                                    </div>
                                    <span class="text-sm font-bold text-emerald-600 group-hover:text-emerald-900 transition-colors">Resolved Today</span>
                                </div>
                                <span class="text-lg font-black text-emerald-950">{{ stats.my_resolved_tickets }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Navigation -->
                    <div class="space-y-4">
                        <Link :href="route('staff.kb.categories.index')" class="flex items-center justify-between p-5 rounded-2xl bg-white border border-emerald-100 shadow-sm hover:border-emerald-200 hover:shadow-xl transition-all group block">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                </div>
                                <span class="font-bold text-emerald-950">Knowledge Base</span>
                            </div>
                            <svg class="h-5 w-5 text-emerald-300 group-hover:translate-x-1 group-hover:text-emerald-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </Link>
                    </div>
                </div>

                <!-- Right: Recent Tickets -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-xl font-bold tracking-tight text-emerald-950">Assigned To Me</h3>
                        <Link :href="route('agent.tickets.index')" class="text-xs font-bold text-emerald-400 hover:text-emerald-600 transition-colors">View all assigned</Link>
                    </div>

                    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden">
                        <div v-if="recent_tickets.length > 0" class="divide-y divide-emerald-50">
                            <Link v-for="ticket in recent_tickets" :key="ticket.id" :href="route('agent.tickets.show', ticket.id)" 
                                class="flex flex-col sm:flex-row sm:items-center justify-between py-5 px-7 hover:bg-emerald-50/30 transition-all group block">
                                <div class="flex items-start gap-5">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50/50 text-[10px] font-black text-emerald-400 border border-emerald-100 group-hover:border-emerald-200 group-hover:bg-white group-hover:text-emerald-600 transition-all shadow-sm">
                                        #{{ ticket.ticket_number.toString().slice(-3) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-emerald-950 truncate mb-1 group-hover:text-emerald-700 transition-colors">{{ ticket.subject }}</h4>
                                        <div class="flex items-center gap-3 text-[11px] font-medium text-emerald-900/40">
                                            <span>Received {{ ticket.created_at }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 sm:mt-0 flex items-center gap-4">
                                     <div class="flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: getPriorityHex(ticket.priority) }"></span>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest hidden sm:block">Priority</span>
                                    </div>
                                    <div class="h-8 w-px bg-emerald-50 hidden sm:block"></div>
                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border" :style="{ color: getStatusHex(ticket.status), backgroundColor: getStatusHex(ticket.status) + '10', borderColor: getStatusHex(ticket.status) + '20' }">
                                        {{ ticket.status }}
                                    </span>
                                </div>
                            </Link>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-24 flex flex-col items-center text-center px-4">
                            <div class="relative mb-8">
                                <div class="absolute inset-0 bg-emerald-50 rounded-full blur-3xl opacity-50 scale-150"></div>
                                <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-xl shadow-emerald-100/50 border border-emerald-50">
                                     <svg class="h-10 w-10 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-xl font-black text-emerald-950 tracking-tight mb-2">Queue is clear</h4>
                            <p class="text-emerald-900/50 max-w-xs font-medium italic">You have no tickets currently assigned to you.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AgentNavigation>
</template>

<style scoped>
/* Inherit cleanly */
</style>