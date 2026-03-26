<script setup>
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import TrendCard from '@/Components/TrendCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useRealtimeDashboard } from '@/Composables/useRealtimeDashboard';

const page = usePage();

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_team_tickets: 0,
            pending_review: 0,
            team_members: 0,
            avg_response_time: '0h',
        }),
    },
    recent_tickets: {
        type: Array,
        default: () => [],
    },
    team_performance: {
        type: Array,
        default: () => [],
    },
    tickets_by_status: {
        type: Array,
        default: () => [],
    },
});

// Check permissions
const hasPermission = (permission) => {
    const userPermissions = page.props.auth?.user?.permissions || [];
    return userPermissions.includes(permission);
};

const { isLive, lastUpdated } = useRealtimeDashboard('staff.tickets', ['stats', 'recent_tickets', 'team_performance', 'tickets_by_status']);

const canEditTickets = computed(() => hasPermission('edit_ticket'));
const canManageUsers = computed(() => hasPermission('manage_users'));

// Helper mapping for string-based colors without touching backend
const getStatusHex = (status) => {
    const map = {
        'open': '#8b5cf6',     // Violet 
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
    <Head title="Manager Dashboard" />

    <ManagerNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold tracking-tight text-violet-950">Team Dashboard</span>
                <div v-if="isLive" class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-violet-100/80 border border-violet-200/80 ml-2" title="Real-time updates active">
                    <div class="h-1.5 w-1.5 rounded-full bg-violet-500 animate-pulse"></div>
                    <span class="text-[10px] font-bold text-violet-700 uppercase tracking-wider">Live</span>
                </div>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-12 pb-20 pt-4">
            <!-- New Minimalist Welcome -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
                <div class="space-y-1">
                    <h2 class="text-3xl font-bold tracking-tight text-violet-950">Overview, {{ $page.props.auth.user.first_name }}.</h2>
                    <p class="font-medium text-violet-900/60">Here is how your team is performing today.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('manager.tickets.create')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-bold shadow-lg shadow-violet-200 hover:bg-violet-700 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        Create Ticket
                    </Link>
                    <Link :href="route('manager.reports.index')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-violet-950 text-sm font-bold border border-violet-100 shadow-sm hover:border-violet-200 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        View Reports
                    </Link>
                </div>
            </div>

            <!-- Stats Using TrendCard -->
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4 px-1">
                <Link :href="route('manager.tickets.all')">
                    <TrendCard 
                        label="Team Tickets" 
                        :value="stats.total_team_tickets" 
                        :trend="[20, 25, 22, 28, 24, 30, 26]" 
                        percentage="+15%"
                        color="violet"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
                <Link v-if="canEditTickets" :href="route('manager.tickets.index', { status: 'Pending' })">
                    <TrendCard 
                        label="Pending Review" 
                        :value="stats.pending_review" 
                        :trend="[5, 4, 3, 6, 4, 3, 2]" 
                        percentage="-5%"
                        color="amber"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
                <div v-else>
                     <TrendCard 
                        label="Avg Response" 
                        :value="stats.avg_response_time" 
                        :trend="[8, 7, 7, 6, 5, 5, 4]" 
                        percentage="-10%"
                        color="cyan"
                    />
                </div>
                <Link v-if="canManageUsers" :href="route('manager.team.index')">
                    <TrendCard 
                        label="Team Members" 
                        :value="stats.team_members" 
                        :trend="[2, 2, 2, 3, 3, 3, 4]" 
                        percentage="+1"
                        color="emerald"
                        class="cursor-pointer hover:-translate-y-1 transition-transform duration-300"
                    />
                </Link>
                <div v-if="canEditTickets && canManageUsers">
                     <TrendCard 
                        label="Avg Response" 
                        :value="stats.avg_response_time" 
                        :trend="[8, 7, 7, 6, 5, 5, 4]" 
                        percentage="-10%"
                        color="cyan"
                    />
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="grid gap-10 lg:grid-cols-12 px-1">
                
                <!-- Left: Distribution & Actions -->
                <div class="lg:col-span-4 space-y-10">
                    <!-- Status Checklist View -->
                    <div class="bg-white rounded-2xl p-8 border border-violet-100 shadow-sm shadow-violet-100/60">
                        <h3 class="text-sm font-black text-violet-900/60 uppercase tracking-widest mb-6 transition-colors hover:text-violet-950">Queue by Status</h3>
                        
                        <div v-if="tickets_by_status.length > 0" class="space-y-4">
                            <div v-for="status in tickets_by_status" :key="status.name" class="flex items-center justify-between p-4 rounded-xl bg-violet-50/50 border border-violet-50 hover:bg-white hover:border-violet-100 hover:shadow-md transition-all group">
                                <div class="flex items-center gap-4">
                                    <div :style="{ color: getStatusHex(status.name), backgroundColor: getStatusHex(status.name) + '15' }" class="h-10 w-10 rounded-lg flex items-center justify-center font-bold text-xs uppercase transition-transform group-hover:scale-110">
                                        {{ status.name?.[0] }}
                                    </div>
                                    <span class="text-sm font-bold text-violet-900/60 group-hover:text-violet-950 transition-colors">{{ status.name }}</span>
                                </div>
                                <span class="text-lg font-black text-violet-950">{{ status.count }}</span>
                            </div>
                        </div>
                        <div v-else class="text-sm text-center text-violet-900/40 italic py-4">
                            No tickets in queue to summarize.
                        </div>
                    </div>

                    <!-- Quick Navigation Actions -->
                    <div class="space-y-4">
                        <Link v-if="canManageUsers" :href="route('manager.team.index')" class="w-full flex items-center justify-between p-5 rounded-2xl bg-white border border-violet-100 shadow-sm shadow-violet-50 hover:border-violet-200 hover:shadow-xl transition-all group block">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600 group-hover:bg-violet-600 group-hover:text-white transition-all">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                                <span class="font-bold text-violet-950">Team Overview</span>
                            </div>
                            <svg class="h-5 w-5 text-violet-300 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </Link>
                        
                        <Link v-if="canEditTickets" :href="route('manager.tickets.index')" class="w-full flex items-center justify-between p-5 rounded-2xl bg-white border border-violet-100 shadow-sm shadow-violet-50 hover:border-violet-200 hover:shadow-xl transition-all group block">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                </div>
                                <span class="font-bold text-violet-950">Assign Tickets</span>
                            </div>
                            <svg class="h-5 w-5 text-violet-300 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </Link>
                    </div>
                </div>

                <!-- Right: Performance & Recent Tickets -->
                <div class="lg:col-span-8 space-y-10">
                    
                    <!-- Team Performance Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-2">
                            <h3 class="text-xl font-bold tracking-tight text-violet-950">Team Performance</h3>
                        </div>
                        
                        <div class="bg-white rounded-2xl border border-violet-100 shadow-sm overflow-hidden">
                            <div v-if="team_performance.length > 0">
                                <div class="hidden sm:grid grid-cols-12 gap-4 px-7 py-4 border-b border-violet-50 bg-violet-50/20 text-xs font-bold uppercase tracking-widest text-violet-900/40">
                                    <div class="col-span-5">Agent</div>
                                    <div class="col-span-2 text-center">Assigned</div>
                                    <div class="col-span-2 text-center">Resolved</div>
                                    <div class="col-span-3 text-right">Avg Resp.</div>
                                </div>
                                <div class="divide-y divide-violet-50">
                                    <div v-for="agent in team_performance" :key="agent.id" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center py-4 px-7 hover:bg-violet-50/20 transition-all group">
                                        <div class="col-span-1 sm:col-span-5 flex items-center gap-4">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-violet-100 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-all shadow-sm">
                                                <span class="text-xs font-bold text-violet-800 group-hover:text-white">
                                                    {{ agent.name?.charAt(0) }}
                                                </span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-bold text-violet-950">{{ agent.name }}</div>
                                                <div class="text-[11px] font-medium text-violet-900/40">{{ agent.email }}</div>
                                            </div>
                                        </div>
                                        <div class="col-span-1 sm:col-span-2 text-left sm:text-center text-sm font-black text-slate-800 flex justify-between sm:block">
                                            <span class="sm:hidden text-xs font-bold text-violet-900/40">Assigned:</span>
                                            {{ agent.assigned }}
                                        </div>
                                        <div class="col-span-1 sm:col-span-2 text-left sm:text-center text-sm font-black text-emerald-600 flex justify-between sm:block">
                                            <span class="sm:hidden text-xs font-bold text-violet-900/40">Resolved:</span>
                                            {{ agent.resolved }}
                                        </div>
                                        <div class="col-span-1 sm:col-span-3 text-left sm:text-right text-xs font-black text-violet-900/60 flex justify-between sm:block">
                                            <span class="sm:hidden text-xs font-bold text-violet-900/40">Avg Response:</span>
                                            {{ agent.avg_response }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Empty State -->
                            <div v-else class="py-16 text-center px-4">
                                <h4 class="text-sm font-bold text-violet-900/60">No performance data available</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Tickets Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-2">
                            <h3 class="text-xl font-bold tracking-tight text-violet-950">Recent Team Tickets</h3>
                            <Link :href="route('manager.tickets.all')" class="text-xs font-bold text-violet-500 hover:text-violet-700 transition-colors">View all</Link>
                        </div>

                        <div class="bg-white rounded-2xl border border-violet-100 shadow-sm overflow-hidden">
                            <div v-if="recent_tickets.length > 0" class="divide-y divide-violet-50">
                                <Link v-for="ticket in recent_tickets" :key="ticket.id" :href="route('manager.tickets.show', ticket.id)" 
                                    class="flex flex-col sm:flex-row sm:items-center justify-between py-5 px-7 hover:bg-violet-50/20 transition-all group block">
                                    <div class="flex items-start gap-5">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50/50 text-[10px] font-black text-violet-400 border border-violet-100 group-hover:border-violet-200 group-hover:bg-white group-hover:text-violet-600 transition-all shadow-sm">
                                            #{{ ticket.ticket_number.toString().slice(-3) }}
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-bold text-violet-950 truncate mb-1 group-hover:text-violet-700 transition-colors">{{ ticket.subject }}</h4>
                                            <div class="flex items-center gap-3 text-[11px] font-medium text-violet-900/40">
                                                <span class="flex items-center gap-1">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                    {{ ticket.assigned_to || 'Unassigned' }}
                                                </span>
                                                <span class="h-1 w-1 rounded-full bg-violet-200"></span>
                                                <span>{{ ticket.created_at }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 sm:mt-0 flex items-center gap-4">
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border" :style="{ color: getPriorityHex(ticket.priority), backgroundColor: getPriorityHex(ticket.priority) + '10', borderColor: getPriorityHex(ticket.priority) + '20' }">
                                            {{ ticket.priority }}
                                        </span>
                                        <div class="h-8 w-px bg-violet-50 hidden sm:block"></div>
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border" :style="{ color: getStatusHex(ticket.status), backgroundColor: getStatusHex(ticket.status) + '10', borderColor: getStatusHex(ticket.status) + '20' }">
                                            {{ ticket.status }}
                                        </span>
                                    </div>
                                </Link>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="py-24 flex flex-col items-center text-center px-4">
                                <div class="relative mb-8">
                                    <div class="absolute inset-0 bg-violet-50 rounded-full blur-3xl opacity-50 scale-150"></div>
                                    <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-xl shadow-violet-100/50 border border-violet-50">
                                        <svg class="h-10 w-10 text-violet-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <h4 class="text-xl font-black text-violet-950 tracking-tight mb-2">Team queue is completely clear</h4>
                                <p class="text-violet-900/50 max-w-xs font-medium italic">There are no recent tickets from your team.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </ManagerNavigation>
</template>

<style scoped>
/* Inherit cleanly */
</style>
