<script setup>
import { useForm }       from '@inertiajs/vue3';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import { computed } from 'vue';

const props = defineProps({
    response: { type: Object, required: true },
});

const form = useForm({
    title:     props.response.title,
    content:   props.response.content,
    shortcut:  props.response.shortcut ?? '',
    category:  props.response.category ?? '',
    is_shared: props.response.is_shared,
});

const submit = () => {
    form.patch(route('staff.canned-responses.update', props.response.id));
};

const page = usePage();
const layoutComponent = computed(() => {
    const role = page.props.auth?.user?.role;
    if (role === 'admin') return AdminNavigation;
    if (role === 'manager') return ManagerNavigation;
    return AgentNavigation;
});

const theme = computed(() => {
    const role = page.props.auth?.user?.role;
    if (role === 'admin') return {
        primary: 'bg-slate-900 text-white hover:bg-slate-800',
        focusRing: 'focus:border-slate-500 focus:ring-slate-500',
        textAccent: 'text-slate-600',
        checkbox: 'text-slate-900 focus:ring-slate-500',
    };
    if (role === 'manager') return {
        primary: 'bg-violet-600 text-white hover:bg-violet-700',
        focusRing: 'focus:border-violet-500 focus:ring-violet-500',
        textAccent: 'text-violet-600',
        checkbox: 'text-violet-600 focus:ring-violet-500',
    };
    return {
        primary: 'bg-emerald-600 text-white hover:bg-emerald-700',
        focusRing: 'focus:border-emerald-500 focus:ring-emerald-500',
        textAccent: 'text-emerald-600',
        checkbox: 'text-emerald-600 focus:ring-emerald-500',
    };
});
</script>

<template>
    <Head title="Edit Canned Response" />
    <component :is="layoutComponent">
        <template #header-title>
            <div class="flex items-center gap-3">
                <Link :href="route('staff.canned-responses.index')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Edit Canned Response</h1>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 space-y-5">

                        <!-- Title -->
                        <div>
                            <label for="cr-title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                            <input
                                id="cr-title"
                                v-model="form.title"
                                type="text"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none"
                                :class="[theme.focusRing, { 'border-red-400': form.errors.title }]"
                            />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <!-- Content -->
                        <div>
                            <label for="cr-content" class="block text-sm font-medium text-gray-700 mb-1">Response Body <span class="text-red-500">*</span></label>
                            <textarea
                                id="cr-content"
                                v-model="form.content"
                                rows="7"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none resize-y"
                                :class="[theme.focusRing, { 'border-red-400': form.errors.content }]"
                            />
                            <p v-if="form.errors.content" class="mt-1 text-xs text-red-600">{{ form.errors.content }}</p>
                        </div>

                        <!-- Category + Shortcut -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="cr-category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <input
                                    id="cr-category"
                                    v-model="form.category"
                                    type="text"
                                    placeholder="e.g. Billing, Technical"
                                    class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none"
                                    :class="theme.focusRing"
                                />
                            </div>
                            <div>
                                <label for="cr-shortcut" class="block text-sm font-medium text-gray-700 mb-1">Shortcut</label>
                                <input
                                    id="cr-shortcut"
                                    v-model="form.shortcut"
                                    type="text"
                                    placeholder="/greeting"
                                    class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-mono focus:outline-none"
                                    :class="[theme.focusRing, { 'border-red-400': form.errors.shortcut }]"
                                />
                                <p class="mt-1 text-xs text-gray-400">Must start with / and use only a-z, 0-9, - or _</p>
                                <p v-if="form.errors.shortcut" class="mt-1 text-xs text-red-600">{{ form.errors.shortcut }}</p>
                            </div>
                        </div>

                        <!-- Visibility -->
                        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 border border-gray-200">
                            <input
                                id="cr-shared"
                                v-model="form.is_shared"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300"
                                :class="theme.checkbox"
                            />
                            <div>
                                <label for="cr-shared" class="text-sm font-medium text-gray-700 cursor-pointer">Share with all staff</label>
                                <p class="text-xs text-gray-500 mt-0.5">When checked, all agents and managers can use this response.</p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="rounded-lg bg-blue-50 border border-blue-100 px-4 py-3 flex items-center gap-3">
                            <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="text-xs text-blue-700">This response has been used <strong>{{ response.use_count }}</strong> time{{ response.use_count !== 1 ? 's' : '' }}.</span>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4">
                        <Link
                            :href="route('staff.canned-responses.index')"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                        >Cancel</Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg px-5 py-2 text-sm font-semibold transition-colors disabled:opacity-50"
                            :class="theme.primary"
                        >
                            {{ form.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </component>
</template>
