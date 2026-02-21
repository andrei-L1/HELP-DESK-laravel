<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
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
});

const form = useForm({
    subject: '',
    description: '',
    priority: 'medium',
    category_id: '',
    department_id: '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        category_id: data.category_id || null,
        department_id: data.department_id || null,
    })).post(route('admin.tickets.store'));
};
</script>

<template>
    <Head title="Create Ticket" />

    <AdminNavigation>
        <div class="p-6 max-w-xl">
            <h1 class="text-xl font-semibold mb-4">Create Ticket</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <input
                        id="subject"
                        v-model="form.subject"
                        type="text"
                        required
                        maxlength="200"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                        placeholder="Brief subject"
                    />
                    <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                        placeholder="Describe the issue or request"
                    />
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select
                        id="priority"
                        v-model="form.priority"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div v-if="categories.length">
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select
                        id="category_id"
                        v-model="form.category_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                    >
                        <option value="">— None —</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
                    </select>
                </div>
                <div v-if="departments.length">
                    <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                    <select
                        id="department_id"
                        v-model="form.department_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                    >
                        <option value="">— None —</option>
                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('admin.tickets.index')"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Creating…' : 'Create Ticket' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminNavigation>
</template>