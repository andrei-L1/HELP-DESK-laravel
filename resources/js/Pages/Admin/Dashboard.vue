<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import TrendCard from '@/Components/TrendCard.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_tickets: 0,
            open_tickets: 0,
            active_users: 0,
            total_departments: 0,
        }),
    },
    recent_tickets: {
        type: Array,
        default: () => [],
    },
    tickets_by_status: {
        type: Array,
        default: () => [],
    },
});

const viewAllTickets = () => {
    router.visit(route('admin.tickets.index'));
};

const createTicket = () => {
    router.visit(route('admin.tickets.create'));
};

const addUser = () => {
    router.visit(route('admin.users.create'));
};

const viewReports = () => {
    router.visit(route('admin.analytics'));
};

const viewTicket = (ticketId) => {
    router.visit(route('admin.tickets.show', ticketId));
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">Dashboard</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-12 pb-20 pt-4">
            <!-- New Minimalist Welcome -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2 stagger-1">
                <div class="space-y-1">
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Welcome back, {{ $page.props.auth.user.first_name }}.</h2>
                    <p class="text-slate-600 font-medium">Here's an overview of the system today.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="createTicket" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        Create Ticket
                    </button>
                    <button @click="viewReports" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-slate-900 text-sm font-bold border border-slate-200/60 shadow-sm hover:border-slate-300 transition-all active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 10-8 0v2m8-1a4 4 0 11-8 0c0-1.1.2-2.1.6-3M3 13V6a2 2 0 012-2h14a2 2 0 012 2v7" /></svg>
                        Analytics
                    </button>
                </div>
            </div>

            <!-- Clean Floating Stats / Upgraded with Trends -->
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4 px-1 stagger-2">
                <TrendCard 
                    label="Total Tickets" 
                    :value="stats.total_tickets" 
                    :trend="[20, 35, 30, 45, 40, 60, 55]" 
                    percentage="+18%"
                    color="slate"
                    @click="viewAllTickets"
                    class="cursor-pointer"
                />
                <TrendCard 
                    label="Open Tickets" 
                    :value="stats.open_tickets" 
                    :trend="[10, 15, 8, 12, 20, 14, 18]" 
                    percentage="+4%"
                    color="rose"
                    @click="viewAllTickets"
                    class="cursor-pointer"
                />
                <TrendCard 
                    label="Active Members" 
                    :value="stats.active_users" 
                    :trend="[50, 52, 55, 54, 58, 60, 62]" 
                    percentage="+12%"
                    color="emerald"
                    @click="addUser"
                    class="cursor-pointer"
                />
                <TrendCard 
                    label="Departments" 
                    :value="stats.total_departments" 
                    :trend="[8, 8, 9, 9, 9, 10, 10]" 
                    percentage="+2%"
                    color="violet"
                />
            </div>

            <!-- Balanced Content Area -->
            <div class="grid gap-10 lg:grid-cols-12 px-1 stagger-3">
                <!-- Left: Distribution & Actions -->
                <div class="lg:col-span-4 space-y-10">
                    <!-- Status Checklist View -->
                    <div class="bg-white rounded-2xl p-8 border border-slate-300/40 shadow-sm shadow-slate-200/60">
                        <h3 class="text-sm font-black text-slate-600 uppercase tracking-widest mb-6 transition-colors group-hover:text-slate-900">Status Distribution</h3>
                        <div class="space-y-4">
                            <div v-for="status in tickets_by_status" :key="status.name" class="flex items-center justify-between p-4 rounded-xl bg-slate-50/50 border border-slate-50 hover:bg-white hover:border-slate-100 hover:shadow-md transition-all group">
                                <div class="flex items-center gap-4">
                                    <div :class="{
                                        'bg-blue-100 text-blue-600': status.name?.toLowerCase() === 'open',
                                        'bg-emerald-100 text-emerald-600': status.name?.toLowerCase() === 'resolved',
                                        'bg-slate-100 text-slate-500': status.name?.toLowerCase() === 'closed',
                                        'bg-rose-100 text-rose-600': status.name?.toLowerCase() === 'urgent',
                                        'bg-amber-100 text-amber-600': status.name?.toLowerCase() === 'pending',
                                    }" class="h-10 w-10 rounded-lg flex items-center justify-center font-bold text-xs uppercase transition-transform group-hover:scale-110">
                                        {{ status.name?.[0] }}
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">{{ status.name }}</span>
                                </div>
                                <span class="text-lg font-black text-slate-900">{{ status.count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Navigation -->
                    <div class="space-y-4">
                        <button @click="addUser" class="w-full flex items-center justify-between p-5 rounded-2xl bg-white border border-slate-300/40 shadow-sm shadow-slate-200/30 hover:border-slate-400/40 hover:shadow-xl transition-all group">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-slate-50 flex items-center justify-center text-slate-900 group-hover:bg-slate-900 group-hover:text-white transition-all">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                </div>
                                <span class="font-bold text-slate-900">Add New User</span>
                            </div>
                            <svg class="h-5 w-5 text-slate-300 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Right: Recent Activity -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-xl font-bold text-slate-900 tracking-tight">Recent Tickets</h3>
                        <Link :href="route('admin.tickets.index')" class="text-xs font-bold text-slate-400 hover:text-slate-900 transition-colors">View all tickets</Link>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden">
                        <div v-if="recent_tickets.length > 0" class="divide-y divide-slate-50">
                            <div v-for="ticket in recent_tickets" :key="ticket.id" @click="viewTicket(ticket.id)" 
                                class="flex flex-col sm:flex-row sm:items-center justify-between py-5 px-7 hover:bg-slate-50/50 transition-all cursor-pointer group">
                                <div class="flex items-start gap-5">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-[10px] font-black text-slate-400 border border-slate-100 group-hover:border-slate-200 transition-all shadow-sm">
                                        #{{ ticket.ticket_number.toString().slice(-3) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-slate-900 truncate mb-1 group-hover:text-slate-700 transition-colors">{{ ticket.subject }}</h4>
                                        <div class="flex items-center gap-3 text-[11px] font-medium text-slate-500">
                                            <span>{{ ticket.created_by }}</span>
                                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                            <span>{{ ticket.created_at }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 sm:mt-0 flex items-center gap-4">
                                     <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border" :style="{ color: ticket.priority_color, backgroundColor: ticket.priority_color + '10', borderColor: ticket.priority_color + '20' }">
                                        {{ ticket.priority }}
                                    </span>
                                    <div class="h-8 w-px bg-slate-100 hidden sm:block"></div>
                                    <span class="text-xs font-bold text-slate-900">{{ ticket.status }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State (Illustrated) -->
                        <div v-else class="py-24 flex flex-col items-center text-center px-4">
                            <div class="relative mb-8">
                                <div class="absolute inset-0 bg-slate-100 rounded-full blur-3xl opacity-50 scale-150"></div>
                                <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-xl shadow-slate-200 border border-slate-100">
                                     <svg class="h-10 w-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 4-8-4" />
                                    </svg>
                                </div>
                                <div class="absolute -bottom-2 -right-2 h-8 w-8 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4" /></svg>
                                </div>
                            </div>
                            <h4 class="text-xl font-black text-slate-900 tracking-tight mb-2">No recent tickets</h4>
                            <p class="text-slate-500 max-w-xs font-medium italic">The support system is currently fully caught up.</p>
                            <button @click="createTicket" class="mt-8 px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-900 shadow-sm hover:bg-slate-50 transition-all">Create New Ticket</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>

<style scoped>
/* Focus and selection styles inherited from app.css */
</style>
