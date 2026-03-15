<script setup>
import { ref } from 'vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    ticket: {
        type: Object,
        required: true,
    },
});

const messageForm = useForm({
    body: '',
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

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString();
};
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />
    <AgentNavigation>
        <template #header-title>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Ticket {{ ticket.ticket_number }}</h1>
                <div class="flex gap-2">
                    <form @submit.prevent="$inertia.post(route('agent.tickets.resolve', ticket.id))" v-if="ticket.status?.name !== 'Resolved' && ticket.status?.name !== 'Closed'">
                        <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                            Mark as Resolved
                        </button>
                    </form>
                    <form @submit.prevent="$inertia.post(route('agent.tickets.reopen', ticket.id))" v-if="ticket.status?.name === 'Resolved' || ticket.status?.name === 'Closed'">
                        <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            Reopen Ticket
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <div class="p-6 space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Link
                    :href="route('agent.tickets.index')"
                    class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-900"
                >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to my tickets
                </Link>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <dl class="divide-y divide-gray-200">
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 bg-gray-50">
                        <dt class="text-sm font-medium text-gray-500">Subject</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 sm:col-span-2 sm:mt-0">{{ ticket.subject }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 whitespace-pre-wrap">{{ ticket.description }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Customer</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ ticket.creator ? `${ticket.creator.first_name} ${ticket.creator.last_name}` : 'System / Unknown' }}
                            <span v-if="ticket.creator" class="text-gray-500 ml-2">({{ ticket.creator.email }})</span>
                        </dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0 text-sm font-medium text-gray-900">
                             {{ ticket.status ? ticket.status.name : 'Unknown' }}
                        </dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0 text-gray-900 text-sm">
                            {{ ticket.priority ? ticket.priority.name : 'Unknown' }}
                        </dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ formatDate(ticket.created_at) }}</dd>
                    </div>
                    <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 sm:mt-0">{{ formatDate(ticket.updated_at) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Messages/Replies -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Conversation</h2>
                
                <div class="space-y-6 mb-8">
                    <div
                        v-for="reply in ticket.replies"
                        :key="reply.id"
                        class="rounded-lg border p-5"
                        :class="[
                            reply.is_internal ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-gray-50',
                            reply.author_id === $page.props.auth.user.id ? 'border-emerald-100 bg-emerald-50 ml-8' : 'mr-8'
                        ]"
                    >
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-3 border-b pb-2" :class="reply.is_internal ? 'border-amber-200' : 'border-gray-200'">
                            <span class="font-medium text-gray-900 flex items-center gap-2">
                                <span class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-xs overflow-hidden">
                                     <img v-if="reply.author?.avatar_url" :src="reply.author.avatar_url" alt="" />
                                     <span v-else>{{ reply.author?.first_name?.charAt(0) || 'U' }}</span>
                                </span>
                                {{ reply.author ? `${reply.author.first_name} ${reply.author.last_name}` : 'System' }}
                            </span>
                            <div class="flex items-center gap-3">
                                <span v-if="reply.is_internal" class="rounded bg-amber-200 px-2 py-0.5 font-medium text-amber-900">Internal Note</span>
                                <span>{{ formatDate(reply.created_at) }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">{{ reply.body }}</p>
                    </div>
                    
                    <div v-if="!ticket.replies || ticket.replies.length === 0" class="text-center py-8 text-gray-500 border rounded-lg border-dashed">
                        No replies yet. Start the conversation below.
                    </div>
                </div>

                <form @submit.prevent="submitMessage" class="space-y-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div>
                        <label for="message-body" class="block text-sm font-medium text-gray-900 mb-1">Reply to Customer</label>
                        <textarea
                            id="message-body"
                            v-model="messageForm.body"
                            rows="4"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            placeholder="Type your response here..."
                        />
                        <p v-if="messageForm.errors.body" class="mt-1 text-sm text-red-600">{{ messageForm.errors.body }}</p>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 disabled:opacity-50 transition-colors"
                            :disabled="messageForm.processing || !messageForm.body?.trim()"
                        >
                            {{ messageForm.processing ? 'Sending...' : 'Send Reply' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AgentNavigation>
</template>
