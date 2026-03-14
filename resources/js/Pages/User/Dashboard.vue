<script setup>
import { Head, Link } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';

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

// Helper function to convert hex to RGB
const hexToRgb = (hex) => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
};

// Helper function for status badge styles using dynamic colors
const getStatusStyles = (status) => {
    const colorHex = status.color_hex || '#6b7280';
    const rgb = hexToRgb(colorHex);
    
    return {
        badge: `inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset`,
        style: {
            backgroundColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.1)` : `${colorHex}20`,
            color: colorHex,
            borderColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.3)` : `${colorHex}40`,
        }
    };
};

// Helper function for priority styles using dynamic colors
const getPriorityStyles = (priority) => {
    const colorHex = priority.color_hex || '#6b7280';
    const rgb = hexToRgb(colorHex);
    
    return {
        color: colorHex,
        bgColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.1)` : `${colorHex}20`,
        borderColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.3)` : `${colorHex}40`,
        icon: '●',
    };
};

// Helper to get gradient style from color
const getGradientStyle = (colorHex) => {
    const rgb = hexToRgb(colorHex);
    if (rgb) {
        return `linear-gradient(to right, ${colorHex}, rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.8))`;
    }
    return `linear-gradient(to right, ${colorHex}, ${colorHex})`;
};
</script>

<template>
    <Head title="User Dashboard" />

    <UserNavigation>
        <template #header-title>
            <div class="flex items-center space-x-3">
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text">
                        Dashboard Overview
                    </h1>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Total Tickets Card -->
                    <div class="group relative overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-400 opacity-0 group-hover:opacity-5 transition-opacity duration-300"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Tickets</p>
                                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_tickets }}</p>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
                                <span class="text-xs text-gray-500">All time</span>
                                <Link :href="route('user.tickets.index')" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                    View all
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Open Tickets Card -->
                    <div class="group relative overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-500 to-yellow-500 opacity-0 group-hover:opacity-5 transition-opacity duration-300"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Open Tickets</p>
                                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.open_tickets }}</p>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
                                <span class="text-xs text-gray-500">Need attention</span>
                                <Link :href="route('user.tickets.index', { status: 'open' })" class="inline-flex items-center text-sm font-medium text-amber-600 hover:text-amber-700 transition-colors">
                                    View open
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Closed Tickets Card -->
                    <div class="group relative overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-green-500 opacity-0 group-hover:opacity-5 transition-opacity duration-300"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Closed Tickets</p>
                                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.closed_tickets }}</p>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
                                <span class="text-xs text-gray-500">Resolved</span>
                                <Link :href="route('user.tickets.index', { status: 'closed' })" class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                    View closed
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets Section -->
                <div class="mt-12">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div class="sm:flex-auto">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-1 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
                                <h2 class="text-xl font-semibold text-gray-900">Recent Tickets</h2>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 max-w-2xl">
                                Stay on top of your support requests. View and manage your most recent tickets below.
                            </p>
                        </div>
                        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                            <Link
                                :href="route('user.tickets.create')"
                                class="group inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create New Ticket
                            </Link>
                        </div>
                    </div>

                    <!-- Tickets Grid -->
                    <div class="mt-8">
                        <div v-if="recent_tickets.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <!-- Ticket Card with Dynamic Colors -->
                            <div v-for="ticket in recent_tickets" :key="ticket.id" 
                                 class="group relative rounded-2xl bg-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 overflow-hidden">
                                <!-- Priority indicator strip with dynamic color -->
                                <div class="absolute top-0 left-0 w-full h-1" 
                                     :style="{ background: getGradientStyle(ticket.priority.color_hex) }"></div>
                                
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="text-sm font-mono text-gray-500">#{{ ticket.ticket_number }}</span>
                                                <!-- Dynamic priority indicator -->
                                                <span class="text-xs" :style="{ color: ticket.priority.color_hex }">
                                                    ●
                                                </span>
                                            </div>
                                            <h3 class="text-base font-semibold text-gray-900 line-clamp-2" :title="ticket.subject">
                                                {{ ticket.subject }}
                                            </h3>
                                        </div>
                                        <!-- Dynamic status badge with color from database -->
                                        <span :class="getStatusStyles(ticket.status).badge"
                                              :style="getStatusStyles(ticket.status).style">
                                            {{ ticket.status.title }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center space-x-2 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ ticket.created_at }}</span>
                                        </div>
                                        <Link :href="route('user.tickets.show', ticket.id)" 
                                              class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                            View Details
                                            <svg class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-else class="relative overflow-hidden rounded-2xl bg-white shadow-lg">
                            <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-gray-100 opacity-50"></div>
                            <div class="relative px-6 py-12 text-center">
                                <div class="mx-auto h-24 w-24 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center animate-pulse">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900">No tickets yet</h3>
                                <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">Get started by creating your first support ticket. We're here to help!</p>
                                <div class="mt-6">
                                    <Link :href="route('user.tickets.create')" 
                                          class="inline-flex items-center rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl hover:-translate-y-0.5">
                                        <svg class="-ml-0.5 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create Your First Ticket
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-12 grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <Link :href="route('user.tickets.index')" 
                          class="group flex items-center justify-between rounded-xl bg-white p-4 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-lg bg-blue-50 p-2 group-hover:bg-blue-100 transition-colors">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-700">All Tickets</span>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    
                    <Link :href="route('user.tickets.create')" 
                          class="group flex items-center justify-between rounded-xl bg-white p-4 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-lg bg-green-50 p-2 group-hover:bg-green-100 transition-colors">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-700">New Ticket</span>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    
                    <Link :href="route('user.tickets.index', { status: 'open' })" 
                          class="group flex items-center justify-between rounded-xl bg-white p-4 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-lg bg-amber-50 p-2 group-hover:bg-amber-100 transition-colors">
                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-700">Open Tickets</span>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    
                    <Link :href="route('user.tickets.index', { status: 'closed' })" 
                          class="group flex items-center justify-between rounded-xl bg-white p-4 shadow-md transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-lg bg-gray-50 p-2 group-hover:bg-gray-100 transition-colors">
                                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-700">Closed Tickets</span>
                        </div>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </UserNavigation>
</template>

<style scoped>
/* Keep all your existing styles exactly as they were */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Animation keyframes */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: .7;
        transform: scale(1.05);
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Smooth scrolling for overflow areas */
.overflow-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-auto::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.overflow-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Reduced motion preferences */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
    
    .animate-pulse,
    .group:hover,
    .group-hover\:scale-110,
    .hover\:-translate-y-1 {
        animation: none !important;
        transform: none !important;
        transition: none !important;
    }
}

/* Focus visible for accessibility */
:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
    border-radius: 0.5rem;
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .group:hover {
        transform: none !important;
    }
    
    .group:active {
        transform: scale(0.98);
        transition: transform 0.1s;
    }
    
    button, 
    a, 
    [role="button"] {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Better text rendering */
.text-gray-900,
.text-gray-700,
.text-gray-600 {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}
</style>