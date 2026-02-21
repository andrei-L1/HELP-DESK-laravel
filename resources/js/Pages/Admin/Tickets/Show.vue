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
    statuses: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
    messages: {
        type: Array,
        default: () => [],
    },
    attachments: {
        type: Array,
        default: () => [],
    },
    activity_logs: {
        type: Array,
        default: () => [],
    },
    sla_policy: {
        type: Object,
        default: null,
    },
});

const assignForm = useForm({
    assigned_to: props.ticket.assigned_to_id ?? '',
});

const statusDepartmentForm = useForm({
    status_id: props.ticket.status_id ?? '',
    department_id: props.ticket.department_id ?? '',
});

const messageForm = useForm({
    body: '',
    is_internal: false,
});

const attachmentForm = useForm({
    file: null,
});

watch(() => props.ticket.assigned_to_id, (id) => {
    assignForm.assigned_to = id ?? '';
}, { immediate: true });

watch(() => [props.ticket.status_id, props.ticket.department_id], ([statusId, departmentId]) => {
    statusDepartmentForm.status_id = statusId ?? '';
    statusDepartmentForm.department_id = departmentId ?? '';
}, { immediate: true });

const submitAssign = () => {
    const value = assignForm.assigned_to === '' ? null : Number(assignForm.assigned_to);
    assignForm.transform(() => ({ assigned_to: value })).patch(route('admin.tickets.update', props.ticket.id));
};

const submitStatusDepartment = () => {
    statusDepartmentForm.transform((data) => ({
        status_id: data.status_id ? Number(data.status_id) : null,
        department_id: data.department_id ? Number(data.department_id) : null,
    })).patch(route('admin.tickets.update', props.ticket.id));
};

const submitMessage = () => {
    messageForm.post(route('admin.tickets.messages.store', props.ticket.id), {
        onSuccess: () => messageForm.reset('body'),
    });
};

const onFileChange = (e) => {
    const f = e.target.files?.[0];
    attachmentForm.file = f || null;
};

const submitAttachment = () => {
    if (!attachmentForm.file) return;
    attachmentForm.post(route('admin.tickets.attachments.store', props.ticket.id), {
        forceFormData: true,
        onSuccess: () => {
            attachmentForm.reset();
            const input = document.getElementById('attachment-file');
            if (input) input.value = '';
        },
    });
};

const formatBytes = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const formatSlaMinutes = (mins) => {
    if (mins < 60) return mins + ' min';
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    return m ? `${h}h ${m}m` : `${h}h`;
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
                    <div v-if="ticket.category_title" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ ticket.category_title }}</dd>
                    </div>
                    <div v-if="ticket.department_name" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Department</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ ticket.department_name }}</dd>
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
                    <div v-if="ticket.due_at" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Due date</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ ticket.due_at }}</dd>
                    </div>
                    <div v-if="ticket.first_response_at" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">First response</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ ticket.first_response_at }}</dd>
                    </div>
                    <div v-if="ticket.resolved_at" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Resolved at</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ ticket.resolved_at }}</dd>
                    </div>
                    <div v-if="ticket.closed_at" class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Closed at</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ ticket.closed_at }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ ticket.created_at }}</dd>
                    </div>
                </dl>
            </div>

            <!-- SLA policy -->
            <div v-if="sla_policy" class="rounded-lg border border-gray-200 bg-slate-50 p-4 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">SLA policy</h3>
                <p class="text-sm text-gray-700">{{ sla_policy.name }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    Response: {{ formatSlaMinutes(sla_policy.response_time) }} · Resolution: {{ formatSlaMinutes(sla_policy.resolution_time) }}
                </p>
            </div>

            <!-- Status & department -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Status & department</h2>
                <form @submit.prevent="submitStatusDepartment" class="flex flex-wrap items-end gap-4">
                    <div v-if="statuses.length" class="min-w-[180px]">
                        <label for="status_id" class="block text-sm font-medium text-gray-700">Status</label>
                        <select
                            id="status_id"
                            v-model="statusDepartmentForm.status_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                        >
                            <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.title || s.name }}</option>
                        </select>
                        <p v-if="statusDepartmentForm.errors.status_id" class="mt-1 text-sm text-red-600">
                            {{ statusDepartmentForm.errors.status_id }}
                        </p>
                    </div>
                    <div class="min-w-[180px]">
                        <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                        <select
                            id="department_id"
                            v-model="statusDepartmentForm.department_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                        >
                            <option value="">— None —</option>
                            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                        <p v-if="statusDepartmentForm.errors.department_id" class="mt-1 text-sm text-red-600">
                            {{ statusDepartmentForm.errors.department_id }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                        :disabled="statusDepartmentForm.processing"
                    >
                        {{ statusDepartmentForm.processing ? 'Saving…' : 'Save' }}
                    </button>
                </form>
                <p class="mt-2 text-xs text-gray-500">Set status to <strong>Resolved</strong> or <strong>Closed</strong> to record resolution/closure time.</p>
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
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
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

            <!-- Messages -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Conversation</h2>
                <div class="space-y-4 mb-6">
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        class="rounded-md border p-4"
                        :class="msg.is_internal ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-gray-50'"
                    >
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                            <span>{{ msg.author }} · {{ msg.created_at }}</span>
                            <span v-if="msg.is_internal" class="rounded bg-amber-200 px-2 py-0.5 font-medium text-amber-900">Internal</span>
                        </div>
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ msg.body }}</p>
                    </div>
                    <p v-if="messages.length === 0" class="text-sm text-gray-500">No replies yet.</p>
                </div>
                <form @submit.prevent="submitMessage" class="space-y-3">
                    <div>
                        <label for="message-body" class="block text-sm font-medium text-gray-700">Add reply or note</label>
                        <textarea
                            id="message-body"
                            v-model="messageForm.body"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                            placeholder="Type your reply or internal note…"
                        />
                        <p v-if="messageForm.errors.body" class="mt-1 text-sm text-red-600">{{ messageForm.errors.body }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input v-model="messageForm.is_internal" type="checkbox" class="rounded border-gray-300 text-slate-600 focus:ring-slate-500" />
                            <span class="ml-2">Internal note (not visible to requester)</span>
                        </label>
                        <button
                            type="submit"
                            class="rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                            :disabled="messageForm.processing || !messageForm.body?.trim()"
                        >
                            {{ messageForm.processing ? 'Sending…' : 'Send' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Attachments -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Attachments</h2>
                <ul v-if="attachments.length" class="space-y-2 mb-4">
                    <li
                        v-for="a in attachments"
                        :key="a.id"
                        class="flex items-center justify-between rounded border border-gray-200 px-3 py-2 text-sm"
                    >
                        <span class="text-gray-700">{{ a.file_name }}</span>
                        <span class="text-gray-500 text-xs">{{ formatBytes(a.file_size) }} · {{ a.uploaded_at }}</span>
                        <a
                            :href="route('admin.tickets.attachments.download', [ticket.id, a.id])"
                            class="text-slate-600 hover:text-slate-800 font-medium"
                        >
                            Download
                        </a>
                    </li>
                </ul>
                <p v-else class="text-sm text-gray-500 mb-4">No attachments.</p>
                <form @submit.prevent="submitAttachment" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="attachment-file" class="block text-sm font-medium text-gray-700">Upload file (max 10 MB)</label>
                        <input
                            id="attachment-file"
                            type="file"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                            @change="onFileChange"
                        />
                    </div>
                    <button
                        type="submit"
                        class="rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                        :disabled="attachmentForm.processing || !attachmentForm.file"
                    >
                        {{ attachmentForm.processing ? 'Uploading…' : 'Upload' }}
                    </button>
                </form>
            </div>

            <!-- Activity log -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Activity</h2>
                <ul class="space-y-2">
                    <li
                        v-for="log in activity_logs"
                        :key="log.id"
                        class="flex flex-col text-sm border-l-2 border-gray-200 pl-3 py-1"
                    >
                        <span class="text-gray-900 font-medium">{{ log.action.replace(/_/g, ' ') }}</span>
                        <span class="text-gray-500">{{ log.user_name }} · {{ log.created_at }}</span>
                        <span v-if="log.old_value || log.new_value" class="text-gray-600 text-xs">
                            {{ log.old_value ? log.old_value + ' → ' : '' }}{{ log.new_value }}
                        </span>
                    </li>
                    <p v-if="activity_logs.length === 0" class="text-sm text-gray-500">No activity yet.</p>
                </ul>
            </div>
        </div>
    </AdminNavigation>
</template>
