<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import SlaTimer from '@/Components/SlaTimer.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';

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

const page = usePage();

// Local reactive copy of messages for real-time updates
const localMessages = ref([...(props.ticket.messages || [])]);

const scrollContainer = ref(null);
const typingUser = ref(null);
const internalTypingUser = ref(null);
let typingTimeout = null;
let internalTypingTimeout = null;
let whisperThrottle = null;
let publicEchoChannel = null;
let internalEchoChannel = null;
let presenceEchoChannel = null;
const activeUsers = ref([]);
const collisionWarning = ref(false);

const scrollToBottom = async () => {
    await nextTick();
    if (scrollContainer.value) {
        scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
    }
};

// Sync when Inertia reloads props
watch(() => props.ticket.messages, (newVal) => {
    localMessages.value = [...(newVal || [])];
    scrollToBottom();
}, { deep: true });

onMounted(() => {
    scrollToBottom();
    if (window.Echo) {
        // Listen to public ticket channel
        publicEchoChannel = window.Echo.private(`ticket.${props.ticket.id}`);
        console.log('[Echo] Subscribed to private channel: ticket.' + props.ticket.id);
        
        publicEchoChannel.listen('.TicketMessageSent', (e) => {
                if (!localMessages.value.find(m => m.id === e.messageData.id)) {
                    localMessages.value.push(e.messageData);
                    scrollToBottom();
                }
            })
            .listenForWhisper('typing', (e) => {
                console.log('[Echo] Received typing whisper (public) from:', e.name);
                typingUser.value = e.name;
                if (typingTimeout) clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    typingUser.value = null;
                }, 3000);
            });

        // Listen to internal ticket channel
        internalEchoChannel = window.Echo.private(`ticket.${props.ticket.id}.internal`);
        console.log('[Echo] Subscribed to private channel: ticket.' + props.ticket.id + '.internal');
        
        internalEchoChannel.listen('.TicketMessageSent', (e) => {
                if (!localMessages.value.find(m => m.id === e.messageData.id)) {
                    localMessages.value.push(e.messageData);
                    scrollToBottom();
                }
            })
            .listenForWhisper('typing', (e) => {
                console.log('[Echo] Received typing whisper (internal) from:', e.name);
                internalTypingUser.value = e.name;
                if (internalTypingTimeout) clearTimeout(internalTypingTimeout);
                internalTypingTimeout = setTimeout(() => {
                    internalTypingUser.value = null;
                }, 3000);
            });
        
        // Listen for general ticket updates (SLA changes, priority, etc.)
        publicEchoChannel.listen('.TicketUpdated', (e) => {
            console.log('[Echo] Received TicketUpdated:', e.ticket);
            // We update the ticket prop selectively or just rely on the parent if needed, 
            // but for Inertia we can just update local reactive state if we had it.
            // Since props.ticket is the source of truth, we can manually trigger a reload or update a local copy.
            Object.assign(props.ticket, e.ticket);
        });

        console.log('[Echo] Connection state:', window.Echo.connector.pusher.connection.state);

        // Join Presence Channel
        presenceEchoChannel = window.Echo.join(`ticket.${props.ticket.id}.presence`)
            .here((users) => {
                activeUsers.value = users;
                checkCollision();
            })
            .joining((user) => {
                if (!activeUsers.value.find(u => u.id === user.id)) {
                    activeUsers.value.push(user);
                }
                checkCollision();
            })
            .leaving((user) => {
                activeUsers.value = activeUsers.value.filter(u => u.id !== user.id);
                checkCollision();
            })
            .error((error) => {
                console.error('[Echo] Presence channel error:', error);
            });
    }
});

const checkCollision = () => {
    // Collision exists if more than one user is viewing the ticket
    // We can be more specific: if more than one STAFF member is viewing
    collisionWarning.value = activeUsers.value.length > 1;
};

const handleTyping = () => {
    const targetChannel = messageForm.is_internal ? internalEchoChannel : publicEchoChannel;
    if (!targetChannel) return;
    // Throttle whispers to at most once per second to avoid Pusher rate limits
    if (whisperThrottle) return;
    whisperThrottle = setTimeout(() => { whisperThrottle = null; }, 1000);
    console.log('[Echo] Sending typing whisper as:', page.props.auth.user.first_name);
    targetChannel.whisper('typing', {
        name: page.props.auth.user.first_name
    });
};

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`ticket.${props.ticket.id}`);
        window.Echo.leave(`ticket.${props.ticket.id}.internal`);
        window.Echo.leave(`ticket.${props.ticket.id}.presence`);
    }
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

                <!-- Presence Indicators & Collision Warning -->
                <div v-if="activeUsers.length > 0" class="mb-6 flex flex-wrap items-center justify-between gap-4 p-4 bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2 overflow-hidden">
                            <div 
                                v-for="user in activeUsers" 
                                :key="user.id"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-slate-100 text-[10px] font-bold text-slate-600 ring-2 ring-transparent transition-all hover:translate-y-[-2px] hover:z-10"
                                :title="`${user.name} (${user.role})`"
                                :class="{ 'ring-emerald-400': user.id === $page.props.auth.user.id }"
                            >
                                {{ user.avatar_initials }}
                            </div>
                        </div>
                        <span class="text-xs font-medium text-gray-500">
                            <span v-if="activeUsers.length === 1">Only you are viewing this ticket</span>
                            <span v-else>{{ activeUsers.length }} people are viewing this ticket</span>
                        </span>
                    </div>

                    <!-- Collision Warning Banner -->
                    <div v-if="collisionWarning && activeUsers.length > 1" class="flex items-center gap-2 px-3 py-1.5 bg-amber-50 rounded-lg border border-amber-200 animate-pulse">
                        <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Potential Collision</span>
                        <span class="text-xs text-amber-700 font-medium">Coordinate with others to avoid duplicate replies.</span>
                    </div>
                </div>

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
                                        {{ localMessages.length }}
                                    </span>
                                </h3>
                            </div>

                            <!-- Conversations -->
                            <div 
                                ref="scrollContainer"
                                class="divide-y divide-gray-50 max-h-[500px] overflow-y-auto scroll-smooth"
                            >
                                <div v-if="localMessages.length === 0" class="py-10 text-center text-sm text-gray-400">
                                    No replies yet.
                                </div>
                                <div
                                    v-for="reply in localMessages"
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
                                    <div class="flex items-center justify-between">
                                        <label for="message-body" class="block text-sm font-medium text-gray-700">Reply to Customer</label>
                                        <div v-if="typingUser || internalTypingUser" class="text-xs animate-pulse font-medium">
                                            <span v-if="internalTypingUser" class="text-amber-600">Staff: {{ internalTypingUser }} is typing a note...</span>
                                            <span v-else-if="typingUser" class="text-emerald-600">{{ typingUser }} is typing...</span>
                                        </div>
                                    </div>
                                    <textarea
                                        id="message-body"
                                        v-model="messageForm.body"
                                        @input="handleTyping"
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
                                <div v-if="ticket.due_at" class="px-5 py-4 border-b border-gray-50 bg-slate-50/30">
                                    <SlaTimer 
                                        :due-at="ticket.due_at" 
                                        :is-breached="!!ticket.is_sla_breached"
                                        :status="ticket.status?.name"
                                    />
                                    <div class="mt-2 text-[10px] text-gray-400 font-medium px-1 flex justify-between">
                                        <span>Deadline:</span>
                                        <span>{{ formatDate(ticket.due_at) }}</span>
                                    </div>
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
