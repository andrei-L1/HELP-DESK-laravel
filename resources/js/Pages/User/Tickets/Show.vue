<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';

const props = defineProps({
    ticket: { type: Object, required: true },
    messages: { type: Array, default: () => [] },
    attachments: { type: Array, default: () => [] },
    activity_logs: { type: Array, default: () => [] },
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

// Dynamic status badge styles using color from database
const getStatusStyles = (status, colorHex) => {
    const color = colorHex || '#6b7280';
    const rgb = hexToRgb(color);
    
    return {
        badge: `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium`,
        style: {
            backgroundColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.1)` : `${color}20`,
            color: color,
            borderColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.3)` : `${color}40`,
        }
    };
};

// Dynamic priority badge styles using color from database
const getPriorityStyles = (priority, colorHex) => {
    const color = colorHex || '#6b7280';
    const rgb = hexToRgb(color);
    
    return {
        badge: `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium`,
        style: {
            backgroundColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.1)` : `${color}20`,
            color: color,
            borderColor: rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.3)` : `${color}40`,
        }
    };
};

// Reply form
const replyForm = useForm({ body: '' });
const submitReply = () => {
    replyForm.post(route('user.tickets.messages.store', props.ticket.id), {
        onSuccess: () => replyForm.reset(),
    });
};

// Attachment upload
const attachmentForm = useForm({ file: null });
const fileInput = ref(null);
const onFileChange = (e) => {
    attachmentForm.file = e.target.files[0] ?? null;
};
const uploadAttachment = () => {
    if (!attachmentForm.file) return;
    attachmentForm.post(route('user.tickets.attachments.store', props.ticket.id), {
        onSuccess: () => { 
            attachmentForm.reset(); 
            if (fileInput.value) fileInput.value.value = ''; 
        },
    });
};

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function formatBytes(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function formatAction(action) {
    return action.replace(/_/g, ' ').replace(/^\w/, c => c.toUpperCase());
}

const showActivity = ref(false);
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />

    <UserNavigation>
        <template #header-title>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-semibold text-gray-900">{{ ticket.ticket_number }}</h1>
                <!-- Dynamic status badge -->
                <span 
                    :class="getStatusStyles(ticket.status, ticket.status_color).badge"
                    :style="getStatusStyles(ticket.status, ticket.status_color).style"
                >
                    {{ ticket.status }}
                </span>
                <!-- Dynamic priority badge -->
                <span 
                    :class="getPriorityStyles(ticket.priority, ticket.priority_color).badge"
                    :style="getPriorityStyles(ticket.priority, ticket.priority_color).style"
                >
                    {{ ticket.priority }}
                </span>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-gray-500">
                    <Link :href="route('user.tickets.index')" class="hover:text-gray-700">My Tickets</Link>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">{{ ticket.ticket_number }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left: Main Content -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Ticket Detail Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-base font-semibold text-gray-900">{{ ticket.subject }}</h2>
                                <p class="text-xs text-gray-400 mt-0.5">Submitted {{ formatDate(ticket.created_at) }}</p>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ ticket.description }}</p>
                            </div>
                        </div>

                        <!-- Conversation Thread -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Conversation
                                    <span class="ml-1.5 inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                        {{ messages.length }}
                                    </span>
                                </h3>
                            </div>

                            <!-- Messages list -->
                            <div class="divide-y divide-gray-50">
                                <div v-if="messages.length === 0" class="py-10 text-center text-sm text-gray-400">
                                    No replies yet.
                                </div>
                                <div
                                    v-for="msg in messages"
                                    :key="msg.id"
                                    class="px-6 py-4"
                                    :class="{ 'bg-blue-50/50': msg.is_mine }"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold text-white"
                                                :class="msg.is_mine ? 'bg-blue-600' : 'bg-slate-600'"
                                            >
                                                {{ msg.author?.[0]?.toUpperCase() ?? '?' }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ msg.is_mine ? 'You' : msg.author }}
                                            </span>
                                            <span v-if="!msg.is_mine" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Support</span>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ formatDate(msg.created_at) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed ml-9">{{ msg.body }}</p>
                                </div>
                            </div>

                            <!-- Reply Box (disabled when closed/resolved) -->
                            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                                <template v-if="ticket.status?.toLowerCase() === 'closed' || ticket.status?.toLowerCase() === 'resolved'">
                                    <p class="text-sm text-center text-gray-500 italic">
                                        This ticket is <strong>{{ ticket.status }}</strong> and no longer accepts replies.
                                    </p>
                                </template>
                                <template v-else>
                                    <form @submit.prevent="submitReply" class="space-y-3">
                                        <label class="block text-sm font-medium text-gray-700">Add a Reply</label>
                                        <textarea
                                            v-model="replyForm.body"
                                            rows="4"
                                            required
                                            placeholder="Type your message here…"
                                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-y"
                                            :class="{ 'border-red-400': replyForm.errors.body }"
                                        />
                                        <p v-if="replyForm.errors.body" class="text-sm text-red-600">{{ replyForm.errors.body }}</p>
                                        <div class="flex justify-end">
                                            <button
                                                type="submit"
                                                :disabled="replyForm.processing || !replyForm.body.trim()"
                                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                            >
                                                <svg v-if="replyForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                                </svg>
                                                {{ replyForm.processing ? 'Sending…' : 'Send Reply' }}
                                            </button>
                                        </div>
                                    </form>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Sidebar -->
                    <div class="space-y-5">

                        <!-- Ticket Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-700">Ticket Details</h3>
                            </div>
                            <dl class="divide-y divide-gray-50">
                                <div class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Status</dt>
                                    <dd>
                                        <span 
                                            :class="getStatusStyles(ticket.status, ticket.status_color).badge"
                                            :style="getStatusStyles(ticket.status, ticket.status_color).style"
                                        >
                                            {{ ticket.status }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Priority</dt>
                                    <dd>
                                        <span 
                                            :class="getPriorityStyles(ticket.priority, ticket.priority_color).badge"
                                            :style="getPriorityStyles(ticket.priority, ticket.priority_color).style"
                                        >
                                            {{ ticket.priority }}
                                        </span>
                                    </dd>
                                </div>
                                <div v-if="ticket.category_title" class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Category</dt>
                                    <dd class="text-xs text-gray-700 font-medium">{{ ticket.category_title }}</dd>
                                </div>
                                <div v-if="ticket.department_name" class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Department</dt>
                                    <dd class="text-xs text-gray-700 font-medium">{{ ticket.department_name }}</dd>
                                </div>
                                <div v-if="ticket.due_at" class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Due</dt>
                                    <dd class="text-xs text-gray-700">{{ formatDate(ticket.due_at) }}</dd>
                                </div>
                                <div v-if="ticket.resolved_at" class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Resolved</dt>
                                    <dd class="text-xs text-gray-700">{{ formatDate(ticket.resolved_at) }}</dd>
                                </div>
                                <div class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Created</dt>
                                    <dd class="text-xs text-gray-700">{{ formatDate(ticket.created_at) }}</dd>
                                </div>
                                <div class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Last updated</dt>
                                    <dd class="text-xs text-gray-700">{{ formatDate(ticket.updated_at) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Attachments -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-700">
                                    Attachments
                                    <span v-if="attachments.length" class="ml-1 text-xs text-gray-400">({{ attachments.length }})</span>
                                </h3>
                            </div>
                            <div class="px-5 py-3 space-y-2">
                                <div v-if="attachments.length === 0" class="text-xs text-gray-400 py-2">No attachments.</div>
                                <a
                                    v-for="att in attachments"
                                    :key="att.id"
                                    :href="att.download_url"
                                    target="_blank"
                                    class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50 transition-colors group"
                                >
                                    <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-gray-700 truncate group-hover:text-blue-600">{{ att.file_name }}</p>
                                        <p class="text-xs text-gray-400">{{ formatBytes(att.file_size) }}</p>
                                    </div>
                                    <svg class="h-4 w-4 text-gray-300 group-hover:text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>

                                <!-- Upload (only if not closed) -->
                                <template v-if="ticket.status?.toLowerCase() !== 'closed' && ticket.status?.toLowerCase() !== 'resolved'">
                                    <form @submit.prevent="uploadAttachment" class="mt-3 pt-3 border-t border-gray-100 space-y-2">
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            @change="onFileChange"
                                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                        />
                                        <button
                                            type="submit"
                                            :disabled="!attachmentForm.file || attachmentForm.processing"
                                            class="w-full text-xs rounded-lg border border-gray-300 py-1.5 px-3 font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-colors"
                                        >
                                            {{ attachmentForm.processing ? 'Uploading…' : 'Upload File' }}
                                        </button>
                                    </form>
                                </template>
                            </div>
                        </div>

                        <!-- Activity Log (collapsible) -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" v-if="activity_logs.length">
                            <button
                                type="button"
                                @click="showActivity = !showActivity"
                                class="w-full flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50 hover:bg-gray-100 transition-colors"
                            >
                                <h3 class="text-sm font-semibold text-gray-700">Activity Log</h3>
                                <svg class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': showActivity }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-if="showActivity" class="px-5 py-3 space-y-3 max-h-64 overflow-y-auto">
                                <div v-for="log in activity_logs" :key="log.id" class="flex items-start gap-2 text-xs">
                                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-blue-400 flex-shrink-0"></div>
                                    <div>
                                        <span class="font-medium text-gray-700">{{ formatAction(log.action) }}</span>
                                        <span v-if="log.new_value" class="text-gray-500"> → {{ log.new_value }}</span>
                                        <br />
                                        <span class="text-gray-400">{{ log.user_name }} · {{ formatDate(log.created_at) }}</span>
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

<style scoped>
/* Add any necessary styles */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Smooth transitions */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.transition-transform {
    transition-property: transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.rotate-180 {
    transform: rotate(180deg);
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