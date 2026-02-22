<script setup>
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    member: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    recentTickets: {
        type: Array,
        default: () => [],
    }
});

// Calculate success rate based on resolved / total (if any exist)
const successRate = computed(() => {
    if (props.stats.tickets_total === 0) return 0;
    return Math.round((props.stats.tickets_resolved / props.stats.tickets_total) * 100);
});

const getPriorityColor = (priorityName) => {
    const p = priorityName?.toLowerCase();
    if (p === 'high') return 'bg-red-100 text-red-800';
    if (p === 'medium') return 'bg-yellow-100 text-yellow-800';
    if (p === 'low') return 'bg-blue-100 text-blue-800';
    return 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`${member.first_name} ${member.last_name} - Performance`" />

    <ManagerNavigation>
        <template #header-title>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('manager.team.index')"
                    class="text-gray-400 hover:text-emerald-600 transition-colors"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">
                    Team Member Performance
                </h1>
            </div>
        </template>

        <div class="p-6 space-y-6">
            <!-- Profile Info Card -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    <img
                        class="h-24 w-24 rounded-full object-cover shadow-sm bg-gray-100"
                        :src="member.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(member.first_name + ' ' + member.last_name) + '&size=128&background=047857&color=fff'"
                        alt="Avatar"
                    />
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ member.first_name }} {{ member.last_name }}
                        </h2>
                        <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ member.email }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                @{{ member.username }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                    {{ member.role?.name || 'Unknown Role' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm flex flex-col justify-center items-center text-center">
                    <p class="text-sm font-medium text-gray-500">Historical Tickets Assigned</p>
                    <p class="mt-2 text-4xl font-bold text-gray-900">{{ stats.tickets_total }}</p>
                </div>
                
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm flex flex-col justify-center items-center text-center">
                    <p class="text-sm font-medium text-gray-500">Currently Open</p>
                    <p class="mt-2 text-4xl font-bold text-amber-600">{{ stats.tickets_open }}</p>
                </div>
                
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm flex flex-col justify-center items-center text-center">
                    <p class="text-sm font-medium text-gray-500">Successfully Resolved</p>
                    <p class="mt-2 text-4xl font-bold text-emerald-600">{{ stats.tickets_resolved }}</p>
                </div>
                
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm flex flex-col justify-center items-center text-center">
                    <p class="text-sm font-medium text-gray-500">Resolution Rate</p>
                    <div class="mt-2 flex items-baseline gap-1">
                        <p class="text-4xl font-bold text-indigo-600">{{ successRate }}</p>
                        <span class="text-lg font-medium text-indigo-600">%</span>
                    </div>
                </div>
            </div>

            <!-- Recent Tickets -->
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">
                        Recent Tickets Assigned to {{ member.first_name }}
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="ticket in recentTickets" :key="ticket.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    #{{ ticket.id }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ ticket.subject }}
                                    <div class="text-xs text-gray-500 font-normal mt-0.5" v-if="ticket.department">
                                        {{ ticket.department.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span 
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="getPriorityColor(ticket.priority?.name)"
                                    >
                                        {{ ticket.priority?.name || 'Unknown' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 ring-1 ring-inset ring-gray-500/10">
                                        <svg 
                                            class="h-1.5 w-1.5" 
                                            :class="{
                                                'fill-red-500': ticket.status?.name === 'Open',
                                                'fill-amber-500': ticket.status?.name === 'In Progress' || ticket.status?.name === 'Pending',
                                                'fill-emerald-500': ticket.status?.name === 'Resolved' || ticket.status?.name === 'Closed'
                                            }"
                                            viewBox="0 0 6 6" aria-hidden="true"
                                        >
                                            <circle cx="3" cy="3" r="3" />
                                        </svg>
                                        {{ ticket.status?.name || 'Unknown' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(ticket.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <Link 
                                        :href="route('manager.tickets.show', ticket.id)" 
                                        class="text-emerald-600 hover:text-emerald-900 font-medium"
                                    >
                                        View Ticket
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="recentTickets.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No tickets assigned recently.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </ManagerNavigation>
</template>
