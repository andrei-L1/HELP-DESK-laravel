<script setup>
import { ref } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const showCreateModal = ref(false);
const createForm = useForm({
    subject: '',
    description: '',
    priority: 'medium',
    category_id: '',
    department_id: '',
});
    
const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
};

const submitCreate = () => {
    createForm.transform((data) => ({
        ...data,
        category_id: data.category_id || null,
        department_id: data.department_id || null,
    })).post(route('admin.tickets.store'), {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

const props = defineProps({
    tickets: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            status: null,
            search: '',
        }),
    },
    statuses: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    departments: {
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
        route('admin.tickets.index'),
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
        route('admin.tickets.index'),
        {},
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
    <Head title="Tickets" />
    <AdminNavigation>
        <!-- Create Ticket Modal -->
        <Teleport to="body">
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="Create ticket"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="fixed inset-0 bg-gray-500/75 transition-opacity"
                        aria-hidden="true"
                        @click="closeCreateModal"
                    />
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    >
                        <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Create Ticket</h3>
                            <div>
                                <label for="create-subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                <input
                                    id="create-subject"
                                    v-model="createForm.subject"
                                    type="text"
                                    required
                                    maxlength="200"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="Brief subject"
                                />
                                <p v-if="createForm.errors.subject" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.subject }}
                                </p>
                            </div>
                            <div>
                                <label for="create-description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea
                                    id="create-description"
                                    v-model="createForm.description"
                                    rows="4"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="Describe the issue or request"
                                />
                                <p v-if="createForm.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.description }}
                                </p>
                            </div>
                            <div>
                                <label for="create-priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                <select
                                    id="create-priority"
                                    v-model="createForm.priority"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div v-if="categories.length">
                                <label for="create-category" class="block text-sm font-medium text-gray-700">Category</label>
                                <select
                                    id="create-category"
                                    v-model="createForm.category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option value="">— None —</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
                                </select>
                            </div>
                            <div v-if="departments.length">
                                <label for="create-department" class="block text-sm font-medium text-gray-700">Department</label>
                                <select
                                    id="create-department"
                                    v-model="createForm.department_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option value="">— None —</option>
                                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                            </div>
                            <div class="flex gap-3 justify-end pt-2">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                    @click="closeCreateModal"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                    :disabled="createForm.processing"
                                >
                                    {{ createForm.processing ? 'Creating…' : 'Create Ticket' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Tickets</h1>
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

                    <!-- Status filter -->
                    <div class="w-full md:w-52">
                        <label class="block text-sm font-medium text-gray-700" for="status">
                            Status
                        </label>
                        <select
                            id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm"
                            :value="filters.status || ''"
                            @change="applyFilter('status', $event.target.value || null)"
                        >
                            <option value="">All</option>
                            <option
                                v-for="status in statuses"
                                :key="status"
                                :value="status"
                            >
                                {{ status }}
                            </option>
                        </select>
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
                       @click="openCreateModal"
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
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="{
                                        'bg-blue-100 text-blue-800': ticket.status?.toLowerCase() === 'open',
                                        'bg-green-100 text-green-800': ticket.status?.toLowerCase() === 'resolved',
                                        'bg-gray-100 text-gray-800': ticket.status?.toLowerCase() === 'closed',
                                        'bg-yellow-100 text-yellow-800': ticket.status?.toLowerCase() === 'pending',
                                        'bg-red-100 text-red-800': ticket.status?.toLowerCase() === 'urgent',
                                    }"
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
                                No tickets found with the current filters.
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
                        results
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

