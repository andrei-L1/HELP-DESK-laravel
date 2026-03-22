<script setup>
/**
 * CannedResponsePicker
 *
 * Drop this component inside any textarea reply box.
 * It emits an "insert" event with the response content string,
 * and fires a fire-and-forget POST to increment use_count.
 *
 * Usage:
 *   <CannedResponsePicker @insert="(text) => messageForm.body = text" />
 */
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const emit = defineEmits(['insert']);
const page = usePage();

const theme = computed(() => {
    const role = page.props.auth?.user?.role;
    if (role === 'admin') return {
        focusRing: 'focus:ring-slate-500',
        focusInput: 'focus:border-slate-500 focus:ring-slate-500',
        hoverItem: 'hover:bg-slate-50',
        hoverTitle: 'group-hover:text-slate-700',
        hoverShortcutBg: 'group-hover:bg-slate-100 group-hover:text-slate-700',
        manageLink: 'text-slate-600 hover:text-slate-700',
    };
    if (role === 'manager') return {
        focusRing: 'focus:ring-violet-500',
        focusInput: 'focus:border-violet-500 focus:ring-violet-500',
        hoverItem: 'hover:bg-violet-50',
        hoverTitle: 'group-hover:text-violet-700',
        hoverShortcutBg: 'group-hover:bg-violet-100 group-hover:text-violet-700',
        manageLink: 'text-violet-600 hover:text-violet-700',
    };
    return {
        focusRing: 'focus:ring-emerald-500',
        focusInput: 'focus:border-emerald-500 focus:ring-emerald-500',
        hoverItem: 'hover:bg-emerald-50',
        hoverTitle: 'group-hover:text-emerald-700',
        hoverShortcutBg: 'group-hover:bg-emerald-100 group-hover:text-emerald-700',
        manageLink: 'text-emerald-600 hover:text-emerald-700',
    };
});

const isOpen    = ref(false);
const search    = ref('');
const results   = ref([]);
const loading   = ref(false);
const container = ref(null);

let debounceTimer = null;

// ── Click-outside via native document listener (no external directive needed) ──
function handleClickOutside(e) {
    if (container.value && !container.value.contains(e.target)) {
        isOpen.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

// ── Open / close ──
const toggle = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        search.value = '';
        fetchResults('');
    }
};

watch(search, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchResults(val), 250);
});

// ── Fetch from our JSON API ──
async function fetchResults(q) {
    loading.value = true;
    try {
        const url = new URL('/api/canned-responses/search', window.location.origin);
        if (q) url.searchParams.set('search', q);
        const res = await fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        });
        results.value = await res.json();
    } catch (e) {
        console.error('Canned response fetch failed', e);
        results.value = [];
    } finally {
        loading.value = false;
    }
}

// ── Insert selected response ──
function insertResponse(item) {
    emit('insert', item.content);
    // Increment use_count — fire-and-forget
    fetch(`/api/canned-responses/${item.id}/use`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'X-Requested-With': 'XMLHttpRequest',
        },
    }).catch(() => {});
    isOpen.value = false;
}

// ── Group results by category for display ──
const grouped = computed(() => {
    const map = {};
    for (const r of results.value) {
        const cat = r.category || 'General';
        if (!map[cat]) map[cat] = [];
        map[cat].push(r);
    }
    return map;
});
</script>

<template>
    <!-- Wrapper ref used for click-outside detection -->
    <div ref="container" class="relative inline-block">

        <!-- Trigger button -->
        <button
            type="button"
            @click="toggle"
            title="Insert canned response"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:border-gray-400 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
            :class="theme.focusRing"
        >
            <!-- Lightning bolt icon -->
            <svg class="h-3.5 w-3.5 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M13 2L4.09 12.96A1 1 0 005 14.5h6.5L11 22l8.91-10.96A1 1 0 0019 9.5H12.5L13 2z"/>
            </svg>
            Canned Responses
            <svg
                class="h-3 w-3 transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Dropdown panel -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute bottom-full mb-2 left-0 z-50 w-80 rounded-xl border border-gray-200 bg-white shadow-xl ring-1 ring-black/5 overflow-hidden origin-bottom-left"
            >
                <!-- Search input -->
                <div class="p-3 border-b border-gray-100 bg-gray-50">
                    <div class="relative">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search responses or shortcut…"
                            autofocus
                            class="w-full rounded-lg border border-gray-300 bg-white pl-8 pr-3 py-1.5 text-xs placeholder-gray-400 focus:outline-none"
                            :class="theme.focusInput"
                        />
                    </div>
                </div>

                <!-- Results -->
                <div class="max-h-72 overflow-y-auto">
                    <div v-if="loading" class="py-6 text-center text-xs text-gray-400">Loading…</div>
                    <div v-else-if="Object.keys(grouped).length === 0" class="py-6 text-center text-xs text-gray-400">
                        No responses found.
                    </div>
                    <template v-else>
                        <div v-for="(items, category) in grouped" :key="category">
                            <div class="sticky top-0 bg-gray-50 border-b border-gray-100 px-3 py-1.5">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ category }}</span>
                            </div>
                            <button
                                v-for="item in items"
                                :key="item.id"
                                type="button"
                                @click="insertResponse(item)"
                                class="w-full text-left px-3 py-2.5 transition-colors group border-b border-gray-50 last:border-0"
                                :class="theme.hoverItem"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-xs font-semibold text-gray-800 leading-snug" :class="theme.hoverTitle">{{ item.title }}</p>
                                    <span
                                        v-if="item.shortcut"
                                        class="shrink-0 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-mono text-gray-500"
                                        :class="theme.hoverShortcutBg"
                                    >{{ item.shortcut }}</span>
                                </div>
                                <p class="mt-0.5 text-[11px] text-gray-500 line-clamp-2 leading-relaxed">{{ item.content }}</p>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between px-3 py-2 border-t border-gray-100 bg-gray-50">
                    <span class="text-[10px] text-gray-400">{{ results.length }} response{{ results.length !== 1 ? 's' : '' }}</span>
                    <a
                        href="/staff/canned-responses"
                        target="_blank"
                        class="text-[10px] font-medium"
                        :class="theme.manageLink"
                    >Manage →</a>
                </div>
            </div>
        </Transition>
    </div>
</template>
