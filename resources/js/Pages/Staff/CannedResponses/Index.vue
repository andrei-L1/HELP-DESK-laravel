<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';

const props = defineProps({
    responses:  { type: Object, required: true },
    categories: { type: Array,  default: () => [] },
    filters:    { type: Object, default: () => ({}) },
});

const page    = usePage();
const search  = ref(props.filters.search  ?? '');
const catFilter = ref(props.filters.category ?? '');

function applyFilters() {
    router.get(route('staff.canned-responses.index'), {
        search:   search.value   || undefined,
        category: catFilter.value || undefined,
    }, { preserveState: true, replace: true });
}

function clearFilters() {
    search.value    = '';
    catFilter.value = '';
    applyFilters();
}

function deleteResponse(id) {
    if (!confirm('Delete this canned response?')) return;
    router.delete(route('staff.canned-responses.destroy', id));
}

const isAdmin   = page.props.auth?.user?.role === 'admin';
const isManager = page.props.auth?.user?.role === 'manager';
const myId      = page.props.auth?.user?.id;

const layoutComponent = computed(() => {
    if (isAdmin) return AdminNavigation;
    if (isManager) return ManagerNavigation;
    return AgentNavigation;
});

const theme = computed(() => {
    if (isAdmin) return {
        primary: 'bg-slate-900 text-white hover:bg-slate-800',
        focusRing: 'focus:border-slate-500 focus:ring-slate-500',
        textLink: 'text-slate-600 hover:text-slate-800 hover:underline',
        badge: 'bg-slate-100 text-slate-700 border-slate-200',
        paginationActive: 'bg-slate-900 text-white font-semibold',
    };
    if (isManager) return {
        primary: 'bg-violet-600 text-white hover:bg-violet-700',
        focusRing: 'focus:border-violet-500 focus:ring-violet-500',
        textLink: 'text-violet-600 hover:text-violet-800 hover:underline',
        badge: 'bg-violet-50 text-violet-700 border-violet-200',
        paginationActive: 'bg-violet-600 text-white font-semibold',
    };
    return {
        primary: 'bg-emerald-600 text-white hover:bg-emerald-700',
        focusRing: 'focus:border-emerald-500 focus:ring-emerald-500',
        textLink: 'text-emerald-600 hover:text-emerald-800 hover:underline',
        badge: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        paginationActive: 'bg-emerald-600 text-white font-semibold',
    };
});
</script>

<template>
    <Head title="Canned Responses" />
    <component :is="layoutComponent">
        <template #header-title>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <h1 class="text-xl font-semibold text-gray-900">Canned Responses</h1>
                <Link
                    :href="route('staff.canned-responses.create')"
                    class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition-colors"
                    :class="theme.primary"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Response
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Explainer -->
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 flex items-start gap-3">
                    <svg class="h-5 w-5 text-amber-500 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 2L4.09 12.96A1 1 0 005 14.5h6.5L11 22l8.91-10.96A1 1 0 0019 9.5H12.5L13 2z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">What are canned responses?</p>
                        <p class="text-xs text-amber-700 mt-0.5">Pre-written replies you can quickly insert when replying to tickets. Use the <kbd class="rounded bg-amber-200 px-1 py-0.5 font-mono text-[10px]">⚡ Canned Responses</kbd> button in any ticket reply box.</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        v-model="search"
                        @keydown.enter="applyFilters"
                        type="text"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none w-64"
                        :class="theme.focusRing"
                    />
                    <select
                        v-model="catFilter"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none"
                        :class="theme.focusRing"
                    >
                        <option value="">All categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                    <button
                        type="button"
                        @click="applyFilters"
                        class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                        :class="theme.primary"
                    >Search</button>
                    <button
                        v-if="filters.search || filters.category"
                        type="button"
                        @click="clearFilters"
                        class="text-sm text-gray-500 hover:text-gray-700 underline"
                    >Clear</button>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div v-if="responses.data.length === 0" class="py-16 text-center text-sm text-gray-400">
                        No canned responses yet.
                        <Link :href="route('staff.canned-responses.create')" class="ml-1 font-medium" :class="theme.textLink">Create the first one →</Link>
                    </div>
                    <table v-else class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Shortcut</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Uses</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Visibility</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="r in responses.data" :key="r.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3">
                                    <p class="text-sm font-medium text-gray-800">{{ r.title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ r.content }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <span v-if="r.category" class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ r.category }}</span>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span v-if="r.shortcut" class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600">{{ r.shortcut }}</span>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600">{{ r.use_count }}</td>
                                <td class="px-5 py-3">
                                    <span :class="r.is_shared
                                        ? theme.badge
                                        : 'bg-gray-50 text-gray-600 border-gray-200'"
                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                                    >
                                        {{ r.is_shared ? 'Shared' : 'Private' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    {{ r.creator ? `${r.creator.first_name} ${r.creator.last_name}` : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <template v-if="isAdmin || isManager || r.created_by === myId">
                                        <Link
                                            :href="route('staff.canned-responses.edit', r.id)"
                                            class="text-xs font-medium text-blue-600 hover:text-blue-800 mr-3"
                                        >Edit</Link>
                                        <button
                                            type="button"
                                            @click="deleteResponse(r.id)"
                                            class="text-xs font-medium text-red-500 hover:text-red-700"
                                        >Delete</button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="responses.last_page > 1" class="flex justify-center gap-1">
                    <template v-for="link in responses.links" :key="link.label">
                        <component
                            :is="link.url ? 'a' : 'span'"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            class="inline-flex items-center justify-center min-w-[2rem] h-8 rounded-lg text-xs px-2 transition-colors"
                            :class="link.active
                                ? theme.paginationActive
                                : link.url
                                    ? 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
                                    : 'bg-white border border-gray-200 text-gray-300 cursor-default'"
                        />
                    </template>
                </div>

            </div>
        </div>
    </component>
</template>
