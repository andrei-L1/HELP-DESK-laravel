<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    tickets: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            status: 'Open',           // pre-set for this view
            search: '',
        }),
    },
    statuses: {
        type: Array,
        default: () => [],
    },
    view: {
        type: String,
        required: true,
    },
});

const applyFilter = (key, value) => {
    router.get(
        route('admin.tickets.open'),           // ← changed to open route
        {
            ...props.filters,
            [key]: value || null,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const resetFilters = () => {
    router.get(
        route('admin.tickets.open'),           // ← changed to open route
        { status: 'Open' },                    // keep forced to Open
        {
            preserveState: false,
            preserveScroll: true,
        },
    );
};

const viewTicket = (ticketId) => {
    router.visit(route('admin.tickets.show', ticketId));
};
</script>

<template>
    <Head title="Open Tickets" />
    <AdminNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Open Tickets</h1>
        </template>

        <div class="p-6 space-y-6">
            <!-- Filters -->
            <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm md:flex-row md:items-end md:justify-between">
                <div class="flex flex-1 flex-col gap-3 md:flex-row">
                    <!-- Search -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700" for="search">
                            Search
                        </label>
                        <div class="mt-1 relative">
                            <input
                                id="search"
                                type="text"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 pr-10"
                                :value="filters.search || ''"
                                placeholder="Search by ticket # or subject"
                                @keyup.enter="applyFilter('search', $event.target.value)"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                @click="applyFilter('search', $event.target.previousElementSibling.value)"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Status filter (locked to Open in this view) -->
                    <div class="w-full md:w-52">
                        <label class="block text-sm font-medium text-gray-700" for="status">
                            Status
                        </label>
                        <select
                            id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm cursor-not-allowed"
                            :value="filters.status || 'Open'"
                            disabled
                        >
                            <option value="Open">Open</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            (This view shows open tickets only)
                        </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="resetFilters"
                    >
                        Reset
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        @click="$emit('create-ticket')"
                    >
                        <svg
                            class="-ml-0.5 mr-1.5 h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        New Ticket
                    </button>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Ticket #
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Subject
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Priority
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Created By
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Assigned To
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Created At
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="ticket in tickets.data"
                            :key="ticket.id"
                            class="cursor-pointer hover:bg-gray-50 transition-colors"
                            @click="viewTicket(ticket.id)"
                        >
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                {{ ticket.ticket_number }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ ticket.subject }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800"
                                >
                                    {{ ticket.status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="{
                                        'bg-red-100 text-red-800': ticket.priority?.toLowerCase() === 'high' || ticket.priority?.toLowerCase() === 'urgent',
                                        'bg-yellow-100 text-yellow-800': ticket.priority?.toLowerCase() === 'medium',
                                        'bg-green-100 text-green-800': ticket.priority?.toLowerCase() === 'low',
                                    }"
                                >
                                    {{ ticket.priority }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ ticket.created_by }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ ticket.assigned_to }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ ticket.created_at }}
                            </td>
                        </tr>

                        <tr v-if="tickets.data.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No open tickets found with the current filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="tickets.links && tickets.links.length > 1"
                class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm sm:px-6"
            >
                <div class="hidden sm:block">
                    <p>
                        Showing
                        <span class="font-medium">{{ tickets.from || 0 }}</span>
                        to
                        <span class="font-medium">{{ tickets.to || 0 }}</span>
                        of
                        <span class="font-medium">{{ tickets.total || 0 }}</span>
                        open tickets
                    </p>
                </div>
                <div class="flex flex-1 justify-between sm:justify-end gap-1">
                    <Link
                        v-for="link in tickets.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium"
                        :class="[
                            link.active
                                ? 'bg-slate-700 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                            !link.url ? 'cursor-default opacity-50' : '',
                        ]"
                    />
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>