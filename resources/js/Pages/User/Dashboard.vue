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
</script>

<template>
    <Head title="User Dashboard" />

    <UserNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">
                Dashboard Overview
            </h1>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="truncate text-sm font-medium text-gray-500">
                                            Total Tickets
                                        </dt>
                                        <dd>
                                            <div class="text-lg font-medium text-gray-900">
                                                {{ stats.total_tickets }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <Link :href="route('user.tickets.index')" class="font-medium text-blue-700 hover:text-blue-900">
                                    View all
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="truncate text-sm font-medium text-gray-500">
                                            Open Tickets
                                        </dt>
                                        <dd>
                                            <div class="text-lg font-medium text-gray-900">
                                                {{ stats.open_tickets }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <Link :href="route('user.tickets.index', { status: 'open' })" class="font-medium text-blue-700 hover:text-blue-900">
                                    View open
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="truncate text-sm font-medium text-gray-500">
                                            Closed Tickets
                                        </dt>
                                        <dd>
                                            <div class="text-lg font-medium text-gray-900">
                                                {{ stats.closed_tickets }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <Link :href="route('user.tickets.index', { status: 'closed' })" class="font-medium text-blue-700 hover:text-blue-900">
                                    View closed
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets -->
                <div class="mt-8">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h2 class="text-base font-semibold leading-6 text-gray-900">
                                My Recent Tickets
                            </h2>
                            <p class="mt-2 text-sm text-gray-700">
                                A list of your latest support requests and their current status.
                            </p>
                        </div>
                        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                            <Link
                                :href="route('user.tickets.create')"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Ticket
                            </Link>
                        </div>
                    </div>

                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
                                    <table v-if="recent_tickets.length > 0" class="min-w-full divide-y divide-gray-300">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                    Ticket
                                                </th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    Status
                                                </th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    Priority
                                                </th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    Created
                                                </th>
                                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                                    <span class="sr-only">View</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            <tr v-for="ticket in recent_tickets" :key="ticket.id">
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                    <div class="flex items-center">
                                                        <div class="font-medium text-gray-900">{{ ticket.ticket_number }}</div>
                                                        <div class="ml-2 text-gray-500 max-w-xs truncate">{{ ticket.subject }}</div>
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium"
                                                          :class="{
                                                              'bg-green-100 text-green-800': ticket.status.toLowerCase() === 'open',
                                                              'bg-yellow-100 text-yellow-800': ticket.status.toLowerCase() === 'pending' || ticket.status.toLowerCase() === 'in progress',
                                                              'bg-gray-100 text-gray-800': ticket.status.toLowerCase() === 'closed' || ticket.status.toLowerCase() === 'resolved',
                                                          }">
                                                        {{ ticket.status }}
                                                    </span>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    {{ ticket.priority }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    {{ ticket.created_at }}
                                                </td>
                                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                    <Link :href="route('user.tickets.show', ticket.id)" class="text-blue-600 hover:text-blue-900">
                                                        View<span class="sr-only">, {{ ticket.ticket_number }}</span>
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <div v-else class="text-center py-10">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No tickets found</h3>
                                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new ticket.</p>
                                        <div class="mt-6">
                                            <Link :href="route('user.tickets.create')" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                                </svg>
                                                New Ticket
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserNavigation>
</template>
