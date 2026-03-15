<script setup>
import AgentNavigation from '@/Components/AgentNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
    priorities: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    subject: '',
    description: '',
    priority: '',
    category_id: '',
    department_id: '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        category_id: data.category_id || null,
        department_id: data.department_id || null,
    })).post(route('agent.tickets.store'));
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
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            required
                            maxlength="200"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            placeholder="Briefly describe the issue..."
                        />
                        <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="departments.length > 0">
                            <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                            <select
                                id="department_id"
                                v-model="form.department_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            >
                                <option value="">— None (Global) —</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                            <p v-if="form.errors.department_id" class="mt-1 text-sm text-red-600">{{ form.errors.department_id }}</p>
                        </div>
                        <div v-if="categories.length > 0">
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            >
                                <option value="">— None —</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title ?? c.name }}</option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                        </div>
                    </div>

                    <div v-if="priorities.length > 0">
                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority Level</label>
                        <select
                            id="priority"
                            v-model="form.priority"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                        >
                            <option value="" disabled>Select a priority level</option>
                            <option v-for="p in priorities" :key="p.id" :value="p.name.toLowerCase()">{{ p.name }}</option>
                        </select>
                        <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">{{ form.errors.priority }}</p>
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
