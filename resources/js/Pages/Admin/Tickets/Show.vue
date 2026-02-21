<script setup>
import { watch } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    ticket: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
});

const assignForm = useForm({
    assigned_to: props.ticket.assigned_to_id ?? '',
});

// Keep form in sync when ticket prop updates (e.g. after assign)
watch(() => props.ticket.assigned_to_id, (id) => {
    assignForm.assigned_to = id ?? '';
}, { immediate: true });

const submitAssign = () => {
    const value = assignForm.assigned_to === '' ? null : Number(assignForm.assigned_to);
    assignForm.transform(() => ({ assigned_to: value })).patch(route('admin.tickets.update', props.ticket.id));
};
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />
    <AdminNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Ticket {{ ticket.ticket_number }}</h1>
        </template>

        <div class="p-6 space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Link
                    :href="route('admin.tickets.index')"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to tickets
                </Link>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <dl class="divide-y divide-gray-200">
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Subject</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ ticket.subject }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 whitespace-pre-wrap">{{ ticket.description }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0">
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
                        </dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0">
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
                        </dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Created by</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ ticket.created_by }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Assigned to</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ ticket.assigned_to_name || 'Unassigned' }}
                        </dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ ticket.created_at }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Assign ticket -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Assign ticket</h2>
                <form @submit.prevent="submitAssign" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[200px] flex-1">
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign to</label>
                        <select
                            id="assigned_to"
                            v-model="assignForm.assigned_to"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                        >
                            <option value="">Unassigned</option>
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                        <p v-if="assignForm.errors.assigned_to" class="mt-1 text-sm text-red-600">
                            {{ assignForm.errors.assigned_to }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                        :disabled="assignForm.processing"
                    >
                        {{ assignForm.processing ? 'Saving…' : 'Save assignment' }}
                    </button>
                </form>
            </div>
        </div>
    </AdminNavigation>
</template>
