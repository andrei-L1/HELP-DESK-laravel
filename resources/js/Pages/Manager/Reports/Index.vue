<!-- resources/js/Pages/Manager/Reports/Index.vue -->
<script setup>
import ManagerLayout from '@/Components/ManagerNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    reports: {
        type: Object,
        default: () => ({})
    },
    summary: {
        type: Object,
        default: () => ({
            total_tickets: 0,
            resolved_tickets: 0,
            resolution_rate: 0,
            avg_response_time: 0,
            avg_resolution_time: 0
        })
    },
    filters: {
        type: Object,
        default: () => ({
            start_date: '',
            end_date: '',
            type: 'overview',
            department_id: null
        })
    },
    departments: {
        type: Array,
        default: () => []
    },
    reportTypes: {
        type: Array,
        default: () => []
    }
});

// Local filter state
const localFilters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    type: props.filters.type,
    department_id: props.filters.department_id
});

// Date range presets
const datePresets = [
    { label: 'Last 7 days', days: 7 },
    { label: 'Last 30 days', days: 30 },
    { label: 'Last 90 days', days: 90 },
    { label: 'This month', custom: 'this_month' },
    { label: 'Last month', custom: 'last_month' },
    { label: 'This year', custom: 'this_year' },
];

// Apply date preset
const applyDatePreset = (preset) => {
    const today = new Date();
    let startDate = new Date();
    
    if (preset.days) {
        startDate.setDate(today.getDate() - preset.days);
        localFilters.value.start_date = startDate.toISOString().split('T')[0];
        localFilters.value.end_date = today.toISOString().split('T')[0];
    } else {
        switch(preset.custom) {
            case 'this_month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                localFilters.value.start_date = startDate.toISOString().split('T')[0];
                localFilters.value.end_date = today.toISOString().split('T')[0];
                break;
            case 'last_month':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                localFilters.value.start_date = startDate.toISOString().split('T')[0];
                localFilters.value.end_date = endDate.toISOString().split('T')[0];
                break;
            case 'this_year':
                startDate = new Date(today.getFullYear(), 0, 1);
                localFilters.value.start_date = startDate.toISOString().split('T')[0];
                localFilters.value.end_date = today.toISOString().split('T')[0];
                break;
        }
    }
    
    applyFilters();
};

// Apply filters
const applyFilters = () => {
    router.get(route('manager.reports.index'), localFilters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

// Reset filters
const resetFilters = () => {
    localFilters.value = {
        start_date: '',
        end_date: '',
        type: 'overview',
        department_id: null
    };
    applyFilters();
};

// Export report
const exportReport = (format) => {
    const url = route('manager.reports.export', {
        ...localFilters.value,
        format
    });
    window.open(url, '_blank');
};

// Get report title
const reportTitle = computed(() => {
    const type = props.reportTypes.find(t => t.value === localFilters.value.type);
    return type ? type.label : 'Report';
});

// Get department name for display
const selectedDepartmentName = computed(() => {
    if (!localFilters.value.department_id) return 'All Departments';
    const dept = props.departments.find(d => d.id === localFilters.value.department_id);
    return dept ? dept.name : 'Selected Department';
});

// Format number for display
const formatNumber = (num) => {
    return new Intl.NumberFormat().format(num || 0);
};

// Format time
const formatTime = (hours) => {
    if (!hours || hours === 0) return 'N/A';
    if (hours < 1) return `${Math.round(hours * 60)} mins`;
    if (hours < 24) return `${Math.round(hours)} hrs`;
    return `${Math.round(hours / 24)} days`;
};

// Get status badge color
const getStatusColor = (status) => {
    const colors = {
        'open': 'bg-blue-100 text-blue-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800',
        'urgent': 'bg-red-100 text-red-800',
    };
    return colors[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

// Get priority badge color
const getPriorityColor = (priority) => {
    const colors = {
        'high': 'bg-red-100 text-red-800',
        'urgent': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800',
    };
    return colors[priority?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Department Reports - ${reportTitle}`" />
    
    <ManagerLayout>
        <template #header-title>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Department Reports & Analytics</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Viewing data for: <span class="font-medium text-emerald-700">{{ selectedDepartmentName }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="exportReport('csv')"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>
        </template>

        <div class="p-6">
            <!-- Filters -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="grid gap-4 md:grid-cols-4">
                    <!-- Report Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Report Type
                        </label>
                        <select
                            v-model="localFilters.type"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                            <option v-for="type in reportTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Department Filter (only show if manager has multiple departments) -->
                    <div v-if="departments.length > 1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Department
                        </label>
                        <select
                            v-model="localFilters.department_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                            <option :value="null">All Departments</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }} ({{ dept.short_code }})
                            </option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Start Date
                        </label>
                        <input
                            type="date"
                            v-model="localFilters.start_date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            End Date
                        </label>
                        <input
                            type="date"
                            v-model="localFilters.end_date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 flex items-center gap-2">
                    <button
                        @click="applyFilters"
                        class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
                    >
                        Apply Filters
                    </button>
                    <button
                        @click="resetFilters"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </button>
                    
                    <!-- Date Presets -->
                    <div class="ml-auto flex gap-2">
                        <button
                            v-for="preset in datePresets"
                            :key="preset.label"
                            @click="applyDatePreset(preset)"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50"
                        >
                            {{ preset.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="mb-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Department Tickets</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900">{{ formatNumber(summary.total_tickets) }}</p>
                        </div>
                        <div class="rounded-lg bg-blue-100 p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Resolution Rate</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-600">{{ summary.resolution_rate }}%</p>
                        </div>
                        <div class="rounded-lg bg-emerald-100 p-3">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Avg First Response</p>
                            <p class="mt-1 text-2xl font-bold text-blue-600">{{ formatTime(summary.avg_response_time) }}</p>
                        </div>
                        <div class="rounded-lg bg-purple-100 p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Avg Resolution Time</p>
                            <p class="mt-1 text-2xl font-bold text-purple-600">{{ formatTime(summary.avg_resolution_time) }}</p>
                        </div>
                        <div class="rounded-lg bg-yellow-100 p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Content based on type -->
            <div class="space-y-6">
                <!-- Overview Report -->
                <template v-if="filters.type === 'overview' && reports.daily_volume && reports.daily_volume.length > 0">
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Daily Volume Chart -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Daily Ticket Volume</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="pb-2 text-left text-sm font-medium text-gray-600">Date</th>
                                            <th class="pb-2 text-right text-sm font-medium text-gray-600">Total</th>
                                            <th class="pb-2 text-right text-sm font-medium text-gray-600">Resolved</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="day in reports.daily_volume" :key="day.date" class="border-b border-gray-100">
                                            <td class="py-2 text-sm text-gray-900">{{ day.date }}</td>
                                            <td class="py-2 text-right text-sm text-gray-900">{{ day.total }}</td>
                                            <td class="py-2 text-right text-sm text-gray-900">{{ day.resolved }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Status Distribution -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Status Distribution</h3>
                            <div class="space-y-3">
                                <div v-for="status in reports.status_distribution" :key="status.status" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">{{ status.status }}</span>
                                    <span class="text-sm font-medium text-gray-900">{{ status.count }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Priority Distribution -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Priority Distribution</h3>
                            <div class="space-y-3">
                                <div v-for="priority in reports.priority_distribution" :key="priority.priority" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">{{ priority.priority }}</span>
                                    <span class="text-sm font-medium text-gray-900">{{ priority.count }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Department Breakdown (only show when viewing all departments) -->
                        <div v-if="!filters.department_id && reports.department_breakdown && reports.department_breakdown.length > 0" 
                             class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Department Breakdown</h3>
                            <div class="space-y-3">
                                <div v-for="dept in reports.department_breakdown" :key="dept.department" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">{{ dept.department }}</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ dept.total }} tickets 
                                        <span class="text-xs text-gray-500">
                                            ({{ Math.round((dept.resolved / dept.total) * 100) || 0 }}% resolved)
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Agent Performance Report -->
                <template v-if="filters.type === 'agent' && reports.length > 0">
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Team Member Performance</h3>
                            <p class="text-sm text-gray-500 mt-1">All agents in {{ selectedDepartmentName }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Agent</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Assigned</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Resolved</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Resolution Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Avg Response</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Avg Resolution</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="agent in reports" :key="agent.id" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 flex-shrink-0 rounded-full bg-emerald-100 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-emerald-800">
                                                        {{ agent.first_name?.charAt(0) }}{{ agent.last_name?.charAt(0) }}
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ agent.first_name }} {{ agent.last_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">{{ agent.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ agent.assigned_tickets || 0 }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ agent.resolved_tickets || 0 }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                  :class="agent.assigned_tickets > 0 && (agent.resolved_tickets / agent.assigned_tickets) >= 0.7 
                                                      ? 'bg-green-100 text-green-800' 
                                                      : 'bg-yellow-100 text-yellow-800'">
                                                {{ agent.assigned_tickets > 0 ? Math.round((agent.resolved_tickets / agent.assigned_tickets) * 100) : 0 }}%
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ formatTime(agent.avg_response_time) }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ formatTime(agent.avg_resolution_time) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- Resolution Time Report -->
                <template v-if="filters.type === 'resolution' && reports.resolution_distribution && reports.resolution_distribution.length > 0">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Resolution Time Distribution</h3>
                            <div class="space-y-3">
                                <div v-for="item in reports.resolution_distribution" :key="item.resolution_time_range" 
                                     class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600">{{ item.resolution_time_range }}</span>
                                    <span class="text-sm font-medium text-gray-900">{{ item.count }} tickets</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Monthly Resolution Trends</h3>
                            <div class="space-y-3">
                                <div v-for="trend in reports.monthly_trends" :key="trend.month" 
                                     class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600">{{ trend.month }}</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ formatTime(trend.avg_resolution_hours) }} 
                                        <span class="text-xs text-gray-500">({{ trend.resolved_count }} tickets)</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Performance Report -->
                <template v-if="filters.type === 'performance' && reports.response_times && reports.response_times.length > 0">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Response Times by Department</h3>
                            <div class="space-y-4">
                                <div v-for="dept in reports.response_times" :key="dept.department" 
                                     class="border-b border-gray-100 pb-3 last:border-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-900">{{ dept.department }}</span>
                                        <span class="text-sm text-gray-600">{{ dept.ticket_count }} tickets</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Avg First Response:</span>
                                        <span class="font-medium text-blue-600">{{ formatTime(dept.avg_response_hours) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Avg Resolution:</span>
                                        <span class="font-medium text-purple-600">{{ formatTime(dept.avg_resolution_hours) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">SLA Compliance</h3>
                            <div class="space-y-3">
                                <div v-for="day in reports.sla_compliance?.slice(-7)" :key="day.date" 
                                     class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600">{{ day.date }}</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ day.within_sla }}/{{ day.total }} 
                                        <span class="text-xs" :class="(day.within_sla / day.total) >= 0.9 ? 'text-green-600' : 'text-yellow-600'">
                                            ({{ Math.round((day.within_sla / day.total) * 100) || 0 }}%)
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Volume Report -->
                <template v-if="filters.type === 'volume' && reports.peak_hours && reports.peak_hours.length > 0">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Peak Hours</h3>
                            <div class="space-y-2">
                                <div v-for="hour in reports.peak_hours" :key="hour.hour" 
                                     class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600">{{ hour.hour }}:00 - {{ hour.hour }}:59</span>
                                    <span class="text-sm font-medium text-gray-900">{{ hour.count }} tickets</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Weekly Trend</h3>
                            <div class="space-y-2">
                                <div v-for="week in reports.weekly_trend" :key="week.week" 
                                     class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600">Week of {{ week.week_start }}</span>
                                    <span class="text-sm font-medium text-gray-900">{{ week.total }} tickets</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div v-if="!reports || Object.keys(reports).length === 0 || 
                    (filters.type === 'overview' && (!reports.daily_volume || reports.daily_volume.length === 0))"
                    class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">No data available for this period</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Try adjusting your filters or date range to see more data.
                    </p>
                </div>
            </div>

            <!-- Data Period Note -->
            <div class="mt-4 text-xs text-gray-400 text-right">
                Data from {{ filters.start_date || 'N/A' }} to {{ filters.end_date || 'N/A' }}
            </div>
        </div>
    </ManagerLayout>
</template>