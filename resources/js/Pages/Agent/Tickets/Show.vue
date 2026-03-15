<script setup>
import { ref } from 'vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    ticket: {
        type: Object,
        required: true,
    },
    attachments: {
        type: Array,
        default: () => [],
    },
    activity_logs: {
        type: Array,
        default: () => [],
    },
});

const attachmentForm = useForm({
    file: null,
});

const onFileChange = (e) => {
    attachmentForm.file = e.target.files?.[0] || null;
};

const submitAttachment = () => {
    if (!attachmentForm.file) return;
    attachmentForm.post(route('agent.tickets.attachments.store', props.ticket.id), {
        forceFormData: true,
        onSuccess: () => {
            attachmentForm.reset();
            const input = document.getElementById('attachment-file');
            if (input) input.value = '';
        },
    });
};

function formatBytes(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

const messageForm = useForm({
    body: '',
    is_internal: false,
});

const submitMessage = () => {
    messageForm.post(route('agent.tickets.reply', props.ticket.id), {
        onSuccess: () => messageForm.reset('body'),
    });
};

const resolveTicket = () => {
    if (confirm('Are you sure you want to mark this ticket as resolved?')) {
        router.post(route('agent.tickets.resolve', props.ticket.id));
    }
};

const reopenTicket = () => {
    router.post(route('agent.tickets.reopen', props.ticket.id));
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

// Helper function to convert hex to RGB
const hexToRgb = (hex) => {
    if (!hex) return null;
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

function formatAction(action) {
    if (!action) return 'Unknown Action';
    return action.replace(/_/g, ' ').replace(/^\w/, c => c.toUpperCase());
}

const showActivity = ref(false);

</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />
    <AgentNavigation>
        <template #header-title>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl font-semibold text-gray-900">{{ ticket.ticket_number }}</h1>
                    <!-- Dynamic status badge -->
                    <span 
                        v-if="ticket.status?.name"
                        :class="getStatusStyles(ticket.status.name, ticket.status?.color).badge"
                        :style="getStatusStyles(ticket.status.name, ticket.status?.color).style"
                    >
                        {{ ticket.status.name }}
                    </span>
                    <!-- Dynamic priority badge -->
                    <span 
                        v-if="ticket.priority?.name"
                        :class="getPriorityStyles(ticket.priority.name, ticket.priority?.color).badge"
                        :style="getPriorityStyles(ticket.priority.name, ticket.priority?.color).style"
                    >
                        {{ ticket.priority.name }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <button @click="resolveTicket" v-if="ticket.status?.name !== 'Resolved' && ticket.status?.name !== 'Closed'" type="button" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                        Mark as Resolved
                    </button>
                    <button @click="reopenTicket" v-if="ticket.status?.name === 'Resolved' || ticket.status?.name === 'Closed'" type="button" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
                        Reopen Ticket
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                    <Link :href="route('agent.tickets.index')" class="hover:text-gray-700">My Tickets</Link>
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
                                <p class="text-xs text-gray-400 mt-0.5">Submitted by {{ ticket.creator ? `${ticket.creator.first_name} ${ticket.creator.last_name}` : 'System' }} on {{ formatDate(ticket.created_at) }}</p>
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
                                        {{ ticket.messages?.length || 0 }}
                                    </span>
                                </h3>
                            </div>

                            <!-- Messages list -->
                            <div class="divide-y divide-gray-50">
                                <div v-if="!ticket.messages || ticket.messages.length === 0" class="py-10 text-center text-sm text-gray-400">
                                    No replies yet. Start the conversation below.
                                </div>
                                <div
                                    v-for="reply in ticket.messages"
                                    :key="reply.id"
                                    class="px-6 py-4"
                                    :class="[
                                        reply.is_internal ? 'bg-amber-50/50' : (reply.user_id === $page.props.auth.user.id ? 'bg-emerald-50/50' : '')
                                    ]"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold text-white overflow-hidden"
                                                :class="reply.is_internal ? 'bg-amber-600' : (reply.user_id === $page.props.auth.user.id ? 'bg-emerald-600' : 'bg-slate-600')"
                                            >
                                                <img v-if="reply.user?.avatar_url" :src="reply.user.avatar_url" alt="" class="h-full w-full object-cover" />
                                                <span v-else>{{ reply.user?.first_name?.[0]?.toUpperCase() ?? '?' }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ reply.user_id === $page.props.auth.user.id ? 'You' : (reply.user ? `${reply.user.first_name} ${reply.user.last_name}` : 'System') }}
                                            </span>
                                            <span v-if="reply.is_internal" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700 font-medium border border-amber-200">Internal Note</span>
                                            <span v-else-if="reply.user_id === $page.props.auth.user.id" class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700 font-medium border border-emerald-200">Agent</span>
                                            <span v-else class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600 font-medium border border-slate-200">Customer</span>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ formatDate(reply.created_at) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed ml-9">{{ reply.body }}</p>
                                </div>
                            </div>

                            <!-- Reply Box -->
                            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                                <form @submit.prevent="submitMessage" class="space-y-3">
                                    <label for="message-body" class="block text-sm font-medium text-gray-700">Reply to Customer</label>
                                    <textarea
                                        id="message-body"
                                        v-model="messageForm.body"
                                        rows="4"
                                        required
                                        placeholder="Type your response here…"
                                        class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm placeholder-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none resize-y"
                                        :class="{ 'border-red-400': messageForm.errors.body }"
                                    />
                                    <p v-if="messageForm.errors.body" class="text-sm text-red-600">{{ messageForm.errors.body }}</p>
                                    <div class="flex items-center justify-between pt-2">
                                        <label class="inline-flex items-center text-sm text-gray-700">
                                            <input v-model="messageForm.is_internal" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-2" />
                                            <span>Internal note (not visible to customer)</span>
                                        </label>
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                                            :disabled="messageForm.processing || !messageForm.body?.trim()"
                                        >
                                            <svg v-if="messageForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                            </svg>
                                            {{ messageForm.processing ? 'Sending...' : 'Send Reply' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Sidebar -->
                    <div class="space-y-5">

                        <!-- Ticket Details -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-700">Ticket Details</h3>
                            </div>
                            <dl class="divide-y divide-gray-50">
                                <div class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Status</dt>
                                    <dd>
                                        <span 
                                            v-if="ticket.status?.name"
                                            :class="getStatusStyles(ticket.status.name, ticket.status?.color).badge"
                                            :style="getStatusStyles(ticket.status.name, ticket.status?.color).style"
                                        >
                                            {{ ticket.status.name }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Priority</dt>
                                    <dd>
                                        <span 
                                            v-if="ticket.priority?.name"
                                            :class="getPriorityStyles(ticket.priority.name, ticket.priority?.color).badge"
                                            :style="getPriorityStyles(ticket.priority.name, ticket.priority?.color).style"
                                        >
                                            {{ ticket.priority.name }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Customer</dt>
                                    <dd class="text-xs text-gray-700 font-medium">
                                        {{ ticket.creator ? `${ticket.creator.first_name} ${ticket.creator.last_name}` : 'Unknown' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Customer Email</dt>
                                    <dd class="text-xs text-gray-700">{{ ticket.creator?.email || 'N/A' }}</dd>
                                </div>
                                <div v-if="ticket.due_at" class="flex justify-between px-5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Due</dt>
                                    <dd class="text-xs text-gray-700">{{ formatDate(ticket.due_at) }}</dd>
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
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
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
                                    :href="route('agent.tickets.attachments.download', [ticket.id, att.id])"
                                    class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50 transition-colors group"
                                >
                                    <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-gray-700 truncate group-hover:text-emerald-600">{{ att.file_name }}</p>
                                        <p class="text-xs text-gray-400">{{ formatBytes(att.file_size) }}</p>
                                    </div>
                                    <svg class="h-4 w-4 text-gray-300 group-hover:text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>

                                <!-- Upload -->
                                <template v-if="ticket.status?.name !== 'Closed' && ticket.status?.name !== 'Resolved'">
                                    <form @submit.prevent="submitAttachment" class="mt-3 pt-3 border-t border-gray-100 space-y-2">
                                        <input
                                            id="attachment-file"
                                            type="file"
                                            @change="onFileChange"
                                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
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
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" v-if="activity_logs && activity_logs.length">
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
                                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-emerald-400 flex-shrink-0"></div>
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
    </AgentNavigation>
</template>

<style scoped>
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

.text-gray-900,
.text-gray-700,
.text-gray-600 {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}
</style>
