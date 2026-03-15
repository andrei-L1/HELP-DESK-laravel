<script setup>
import AgentNavigation from '@/Components/AgentNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    priorities: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    subject: '',
    description: '',
    priority_id: '',
});

const submit = () => {
    form.post(route('agent.tickets.store'));
};
</script>

<template>
    <Head title="Create Ticket" />

    <AgentNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Create New Ticket</h1>
        </template>

        <div class="p-6 max-w-3xl">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject / Issue Summary</label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            placeholder="Briefly describe the issue..."
                        />
                        <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="6"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            placeholder="Provide as much context and detail as possible..."
                        />
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div v-if="priorities && priorities.length > 0">
                        <label for="priority_id" class="block text-sm font-medium text-gray-700">Priority Level</label>
                        <select
                            id="priority_id"
                            v-model="form.priority_id"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                        >
                            <option value="" disabled>Select a priority level</option>
                            <option v-for="p in priorities" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <p v-if="form.errors.priority_id" class="mt-1 text-sm text-red-600">{{ form.errors.priority_id }}</p>
                    </div>
                    
                    <!-- Fallback if priorities prop is empty -->
                    <div v-else>
                        <label for="priority_fallback" class="block text-sm font-medium text-gray-700">Priority Level (ID)</label>
                        <input
                            id="priority_fallback"
                            v-model="form.priority_id"
                            type="number"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            placeholder="Enter Priority ID (e.g., 1 for Low, 2 for Medium)"
                        />
                         <p v-if="form.errors.priority_id" class="mt-1 text-sm text-red-600">{{ form.errors.priority_id }}</p>
                         <p class="mt-1 text-xs text-gray-500">Priorities not loaded from backend. Entering ID manually.</p>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                        <Link
                            :href="route('agent.tickets.index')"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Creating Ticket...' : 'Create Ticket' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AgentNavigation>
</template>
