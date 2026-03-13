<script setup>
// no reactive state needed beyond useForm
import { Head, Link, useForm } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';

const props = defineProps({
    categories:      { type: Array, default: () => [] },
    departments:     { type: Array, default: () => [] },
    priorities:      { type: Array, default: () => [] },
    user_department: { type: Number, default: null },
});

const form = useForm({
    subject:       '',
    description:   '',
    priority:      props.priorities[0]?.name?.toLowerCase() ?? 'medium',
    category_id:   '',
    department_id: props.user_department ?? '',
});

// ── SLA hint logic ─────────────────────────────────────────────────────────
// When department or priority changes, show the applicable SLA from each priority's sla map.
// Priority.sla is: { [deptId|'global']: { response_time, resolution_time } }
function getSlaForPriority(priority) {
    if (!priority?.sla) return null;
    const deptId = form.department_id ? String(form.department_id) : null;
    // prefer dept-specific SLA, fallback to global
    if (deptId && priority.sla[deptId]) return priority.sla[deptId];
    if (priority.sla['global'])          return priority.sla['global'];
    return null;
}

function formatMinutes(mins) {
    if (!mins) return null;
    if (mins < 60)   return `${mins}m`;
    if (mins < 1440) return `${Math.round(mins / 60)}h`;
    return `${Math.round(mins / 1440)}d`;
}

// ── Priority colour helper ─────────────────────────────────────────────────
function priorityBg(name) {
    const map = {
        urgent: 'border-red-400 bg-red-50 ring-1 ring-red-400',
        high:   'border-orange-400 bg-orange-50 ring-1 ring-orange-400',
        medium: 'border-blue-500 bg-blue-50 ring-1 ring-blue-500',
        low:    'border-green-500 bg-green-50 ring-1 ring-green-500',
    };
    return map[name?.toLowerCase()] ?? 'border-slate-500 bg-slate-50 ring-1 ring-slate-500';
}

function priorityInactive(name) {
    const map = {
        urgent: 'border-gray-200 hover:border-red-200 hover:bg-red-50/30',
        high:   'border-gray-200 hover:border-orange-200 hover:bg-orange-50/30',
        medium: 'border-gray-200 hover:border-blue-200 hover:bg-blue-50/30',
        low:    'border-gray-200 hover:border-green-200 hover:bg-green-50/30',
    };
    return map[name?.toLowerCase()] ?? 'border-gray-200 hover:bg-gray-50';
}

function priorityDot(name) {
    const map = {
        urgent: 'bg-red-500',
        high:   'bg-orange-500',
        medium: 'bg-blue-500',
        low:    'bg-green-500',
    };
    return map[name?.toLowerCase()] ?? 'bg-slate-500';
}

function priorityText(name) {
    const map = {
        urgent: 'text-red-700',
        high:   'text-orange-700',
        medium: 'text-blue-700',
        low:    'text-green-700',
    };
    return map[name?.toLowerCase()] ?? 'text-slate-700';
}

const submit = () => {
    form.transform((data) => ({
        ...data,
        category_id:   data.category_id   || null,
        department_id: data.department_id || null,
    })).post(route('user.tickets.store'));
};
</script>

<template>
    <Head title="Create Ticket" />

    <UserNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">Create New Ticket</h1>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                    <Link :href="route('user.tickets.index')" class="hover:text-gray-700">My Tickets</Link>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">New Ticket</span>
                </nav>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-base font-semibold text-gray-900">Submit a Support Request</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Describe your issue and we'll assign it to the right team.</p>
                    </div>

                    <form @submit.prevent="submit" class="px-6 py-6 space-y-6">

                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="subject"
                                v-model="form.subject"
                                type="text"
                                required
                                maxlength="200"
                                placeholder="Brief one-line description of your issue"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500': form.errors.subject }"
                            />
                            <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="6"
                                required
                                placeholder="Describe your problem in detail — include steps to reproduce, error messages, or any relevant context."
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-y"
                                :class="{ 'border-red-400': form.errors.description }"
                            />
                            <div class="flex justify-between mt-1">
                                <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                                <p class="text-xs text-gray-400 ml-auto">{{ form.description.length }} / 10 000</p>
                            </div>
                        </div>

                        <!-- Department (if available) -->
                        <div v-if="departments.length">
                            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select
                                id="department_id"
                                v-model="form.department_id"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">— Select a department (optional) —</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Choosing a department helps route your ticket faster.</p>
                        </div>

                        <!-- Priority (from DB, with SLA hints) -->
                        <div v-if="priorities.length">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Priority <span class="text-red-500">*</span>
                                </label>
                                <span class="text-xs text-gray-400">Response &amp; resolution times shown per SLA</span>
                            </div>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <label
                                    v-for="p in priorities"
                                    :key="p.id"
                                    :for="'priority-' + p.id"
                                    class="relative flex cursor-pointer flex-col rounded-xl border p-4 transition-all"
                                    :class="form.priority === p.name.toLowerCase()
                                        ? priorityBg(p.name)
                                        : priorityInactive(p.name)"
                                >
                                    <input
                                        :id="'priority-' + p.id"
                                        v-model="form.priority"
                                        type="radio"
                                        :value="p.name.toLowerCase()"
                                        class="sr-only"
                                    />
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="h-2.5 w-2.5 rounded-full flex-shrink-0" :class="priorityDot(p.name)"></span>
                                        <span class="text-sm font-semibold" :class="form.priority === p.name.toLowerCase() ? priorityText(p.name) : 'text-gray-700'">
                                            {{ p.name }}
                                        </span>
                                        <!-- checkmark -->
                                        <svg v-if="form.priority === p.name.toLowerCase()" class="ml-auto h-4 w-4 flex-shrink-0" :class="priorityText(p.name)" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>

                                    <!-- SLA times -->
                                    <template v-if="getSlaForPriority(p)">
                                        <div class="mt-1 flex gap-4 text-xs" :class="form.priority === p.name.toLowerCase() ? priorityText(p.name) : 'text-gray-400'">
                                            <span v-if="getSlaForPriority(p).response_time">
                                                ⚡ Response: <strong>{{ formatMinutes(getSlaForPriority(p).response_time) }}</strong>
                                            </span>
                                            <span v-if="getSlaForPriority(p).resolution_time">
                                                ✓ Resolution: <strong>{{ formatMinutes(getSlaForPriority(p).resolution_time) }}</strong>
                                            </span>
                                        </div>
                                    </template>
                                    <p v-else class="mt-1 text-xs text-gray-400 italic">No SLA configured</p>
                                </label>
                            </div>
                            <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">{{ form.errors.priority }}</p>
                        </div>

                        <!-- Fallback: no priorities in DB -->
                        <div v-else class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3">
                            <p class="text-sm text-yellow-700">⚠ No ticket priorities are configured. Please contact an administrator.</p>
                        </div>

                        <!-- Category -->
                        <div v-if="categories.length">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                            >
                                <option value="">— Select a category (optional) —</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                            <button
                                type="submit"
                                :disabled="form.processing || !priorities.length"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 transition-colors"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                {{ form.processing ? 'Submitting…' : 'Submit Ticket' }}
                            </button>
                            <Link
                                :href="route('user.tickets.index')"
                                class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </Link>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </UserNavigation>
</template>
