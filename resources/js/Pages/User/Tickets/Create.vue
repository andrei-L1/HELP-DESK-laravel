<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';

const props = defineProps({
    categories:      { type: Array, default: () => [] },
    departments:     { type: Array, default: () => [] },
    priorities:      { type: Array, default: () => [] },
    user_department: { type: Number, default: null },
});

// Form state
const form = useForm({
    subject:       '',
    description:   '',
    priority:      props.priorities[0]?.name?.toLowerCase() ?? 'medium',
    category_id:   '',
    department_id: props.user_department ?? '',
});

// UI state
const charCount = computed(() => form.description.length);
const isDescriptionTooLong = computed(() => charCount.value > 9500); // Warning at 95%
const showPreview = ref(false);
const isSubmitting = ref(false);

// ── SLA hint logic ─────────────────────────────────────────────────────────
function getSlaForPriority(priority) {
    if (!priority?.sla) return null;
    const deptId = form.department_id ? String(form.department_id) : null;
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

// ── Priority styling ──────────────────────────────────────────────────────
const priorityStyles = {
    urgent: {
        bg: 'border-red-400 bg-red-50 ring-1 ring-red-400',
        hover: 'hover:border-red-200 hover:bg-red-50/50',
        dot: 'bg-red-500',
        text: 'text-red-700',
        badge: 'bg-red-100 text-red-800',
        light: 'text-red-600',
        icon: 'text-red-500'
    },
    high: {
        bg: 'border-orange-400 bg-orange-50 ring-1 ring-orange-400',
        hover: 'hover:border-orange-200 hover:bg-orange-50/50',
        dot: 'bg-orange-500',
        text: 'text-orange-700',
        badge: 'bg-orange-100 text-orange-800',
        light: 'text-orange-600',
        icon: 'text-orange-500'
    },
    medium: {
        bg: 'border-blue-500 bg-blue-50 ring-1 ring-blue-500',
        hover: 'hover:border-blue-200 hover:bg-blue-50/50',
        dot: 'bg-blue-500',
        text: 'text-blue-700',
        badge: 'bg-blue-100 text-blue-800',
        light: 'text-blue-600',
        icon: 'text-blue-500'
    },
    low: {
        bg: 'border-green-500 bg-green-50 ring-1 ring-green-500',
        hover: 'hover:border-green-200 hover:bg-green-50/50',
        dot: 'bg-green-500',
        text: 'text-green-700',
        badge: 'bg-green-100 text-green-800',
        light: 'text-green-600',
        icon: 'text-green-500'
    }
};

function getPriorityStyle(name, isSelected = false) {
    const style = priorityStyles[name?.toLowerCase()] ?? {
        bg: 'border-slate-500 bg-slate-50 ring-1 ring-slate-500',
        hover: 'hover:border-slate-200 hover:bg-slate-50/50',
        dot: 'bg-slate-500',
        text: 'text-slate-700',
        badge: 'bg-slate-100 text-slate-800',
        light: 'text-slate-600',
        icon: 'text-slate-500'
    };
    
    if (isSelected) return style.bg;
    return `border-gray-200 ${style.hover}`;
}

function priorityDot(name) {
    return priorityStyles[name?.toLowerCase()]?.dot ?? 'bg-slate-500';
}

function priorityText(name) {
    return priorityStyles[name?.toLowerCase()]?.text ?? 'text-slate-700';
}

function priorityBadge(name) {
    return priorityStyles[name?.toLowerCase()]?.badge ?? 'bg-slate-100 text-slate-800';
}

// ── Form submission ───────────────────────────────────────────────────────
const submit = () => {
    isSubmitting.value = true;
    form.transform((data) => ({
        ...data,
        category_id:   data.category_id   || null,
        department_id: data.department_id || null,
    })).post(route('user.tickets.store'), {
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

// Auto-save draft to localStorage
const draftKey = 'ticket_draft';
watch(
    () => ({ ...form.data() }),
    (newDraft) => {
        if (newDraft.subject || newDraft.description) {
            localStorage.setItem(draftKey, JSON.stringify(newDraft));
        }
    },
    { deep: true }
);

// Load draft on mount
onMounted(() => {
    const saved = localStorage.getItem(draftKey);
    if (saved) {
        try {
            const draft = JSON.parse(saved);
            // Only restore if form is empty
            if (!form.subject && !form.description) {
                Object.assign(form, draft);
            }
        } catch (e) {
            console.error('Failed to load draft');
        }
    }
});

// Clear draft on successful submit
const clearDraft = () => {
    localStorage.removeItem(draftKey);
};
</script>

<template>
    <Head title="Create Ticket" />

    <UserNavigation>
        <template #header-title>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-gray-900">Create New Ticket</h1>
                <span v-if="form.processing" class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                    <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Saving...
                </span>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumb with progress indicator -->
                <nav class="flex items-center gap-2 text-sm mb-6">
                    <Link :href="route('user.tickets.index')" class="inline-flex items-center gap-1 text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        My Tickets
                    </Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-900 font-medium flex items-center gap-2">
                        New Ticket
                        <span class="flex items-center gap-1 text-xs text-gray-400">
                            <span class="h-1 w-1 rounded-full bg-gray-300"></span>
                            Step 1 of 1
                        </span>
                    </span>
                </nav>

                <!-- Main Form Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all hover:shadow-md">
                    <!-- Header with gradient -->
                    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                    Submit a Support Request
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">Please provide as much detail as possible to help us resolve your issue quickly.</p>
                            </div>
                            
                            <!-- Draft indicator -->
                            <div v-if="form.subject || form.description" class="flex items-center gap-2 text-xs">
                                <span class="flex items-center gap-1 text-gray-400">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Draft saved
                                </span>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="px-6 py-6 space-y-8">

                        <!-- Subject with character counter -->
                        <div class="space-y-1.5">
                            <label for="subject" class="block text-sm font-medium text-gray-700">
                                Subject <span class="text-red-500">*</span>
                                <span class="ml-2 text-xs text-gray-400 font-normal">(max 200 characters)</span>
                            </label>
                            <div class="relative">
                                <input
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    required
                                    maxlength="200"
                                    placeholder="e.g., Unable to access my account, Login issues, etc."
                                    class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm shadow-sm placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-all"
                                    :class="{ 
                                        'border-red-400 focus:border-red-500 focus:ring-red-500': form.errors.subject,
                                        'border-green-400': form.subject && !form.errors.subject
                                    }"
                                />
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                    <svg v-if="form.subject && !form.errors.subject" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <p v-if="form.errors.subject" class="text-sm text-red-600 flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.subject }}
                                </p>
                                <p class="text-xs text-gray-400 ml-auto">{{ form.subject.length }}/200</p>
                            </div>
                        </div>

                        <!-- Description with rich text preview -->
                        <div class="space-y-1.5">
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description <span class="text-red-500">*</span>
                                <span class="ml-2 text-xs text-gray-400 font-normal">(min 10, max 10,000 characters)</span>
                            </label>
                            
                            <!-- Tabs for Write/Preview -->
                            <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
                                <button
                                    type="button"
                                    @click="showPreview = false"
                                    class="text-sm font-medium px-3 py-1 rounded-md transition-colors"
                                    :class="!showPreview ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:text-gray-700'"
                                >
                                    Write
                                </button>
                                <button
                                    type="button"
                                    @click="showPreview = true"
                                    class="text-sm font-medium px-3 py-1 rounded-md transition-colors"
                                    :class="showPreview ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:text-gray-700'"
                                >
                                    Preview
                                </button>
                                <span class="text-xs text-gray-300">|</span>
                                <span class="text-xs text-gray-400">Markdown supported</span>
                            </div>

                            <!-- Textarea (Write mode) -->
                            <textarea
                                v-if="!showPreview"
                                id="description"
                                v-model="form.description"
                                rows="8"
                                required
                                minlength="10"
                                maxlength="10000"
                                placeholder="Describe your problem in detail. Include:&#10;• Steps to reproduce&#10;• Expected vs actual behavior&#10;• Error messages (if any)&#10;• Browser/device you're using"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm shadow-sm placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-all resize-y font-mono"
                                :class="{ 
                                    'border-red-400 focus:border-red-500 focus:ring-red-500': form.errors.description,
                                    'border-yellow-400': isDescriptionTooLong,
                                    'border-green-400': form.description && !form.errors.description && !isDescriptionTooLong
                                }"
                            />

                            <!-- Preview mode -->
                            <div
                                v-else
                                class="min-h-[200px] rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 whitespace-pre-wrap"
                            >
                                {{ form.description || 'Nothing to preview yet...' }}
                            </div>

                            <!-- Character count and validation -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <p v-if="form.errors.description" class="text-sm text-red-600 flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ form.errors.description }}
                                    </p>
                                    <p v-else-if="form.description && form.description.length < 10" class="text-sm text-yellow-600 flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Please provide more detail (minimum 10 characters)
                                    </p>
                                </div>
                                
                                <!-- Character counter with progress bar -->
                                <div class="flex items-center gap-3">
                                    <div class="w-24 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full transition-all duration-300"
                                            :class="{
                                                'bg-green-500': charCount <= 5000,
                                                'bg-yellow-500': charCount > 5000 && charCount <= 9500,
                                                'bg-red-500': charCount > 9500
                                            }"
                                            :style="{ width: (charCount / 10000 * 100) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="text-xs font-mono" :class="{ 'text-red-600': charCount > 9500 }">
                                        {{ charCount }}/10000
                                    </span>
                                </div>
                            </div>

                            <!-- Quick templates (optional) -->
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs text-gray-400">Quick templates:</span>
                                <button
                                    type="button"
                                    @click="form.description = 'I am unable to log in to my account. I have tried resetting my password but still cannot access.'"
                                    class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors"
                                >
                                    Login Issue
                                </button>
                                <button
                                    type="button"
                                    @click="form.description = 'The system is running very slowly. Pages take 10+ seconds to load.'"
                                    class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors"
                                >
                                    Performance
                                </button>
                                <button
                                    type="button"
                                    @click="form.description = 'I found a bug: when I click the submit button, nothing happens.'"
                                    class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors"
                                >
                                    Bug Report
                                </button>
                            </div>
                        </div>

                        <!-- Two-column layout for selections -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Department -->
                            <div v-if="departments.length" class="space-y-1.5">
                                <label for="department_id" class="block text-sm font-medium text-gray-700 flex items-center gap-1">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Department
                                </label>
                                <select
                                    id="department_id"
                                    v-model="form.department_id"
                                    class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-all"
                                >
                                    <option value="">— Select department (optional) —</option>
                                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                                <p class="text-xs text-gray-400 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Helps route your ticket faster
                                </p>
                            </div>

                            <!-- Category -->
                            <div v-if="categories.length" class="space-y-1.5">
                                <label for="category_id" class="block text-sm font-medium text-gray-700 flex items-center gap-1">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                    </svg>
                                    Category
                                </label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-all"
                                >
                                    <option value="">— Select category (optional) —</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Priority Selection with SLA Cards -->
                        <div v-if="priorities.length" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-700 flex items-center gap-1">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Priority <span class="text-red-500 ml-1">*</span>
                                </label>
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded-full text-gray-600">
                                    SLA times shown per priority
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <label
                                    v-for="p in priorities"
                                    :key="p.id"
                                    :for="'priority-' + p.id"
                                    class="relative flex cursor-pointer flex-col rounded-xl border-2 p-4 transition-all duration-200 hover:scale-[1.02] hover:shadow-md"
                                    :class="getPriorityStyle(p.name, form.priority === p.name.toLowerCase())"
                                >
                                    <input
                                        :id="'priority-' + p.id"
                                        v-model="form.priority"
                                        type="radio"
                                        :value="p.name.toLowerCase()"
                                        class="sr-only"
                                    />
                                    
                                    <!-- Priority header -->
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="h-3 w-3 rounded-full" :class="priorityDot(p.name)"></span>
                                            <span class="text-sm font-semibold" :class="priorityText(p.name)">
                                                {{ p.name }}
                                            </span>
                                        </div>
                                        
                                        <!-- Selected indicator -->
                                        <div v-if="form.priority === p.name.toLowerCase()" 
                                             class="flex h-5 w-5 items-center justify-center rounded-full bg-white shadow-sm"
                                             :class="priorityText(p.name)"
                                        >
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- SLA times with icons -->
                                    <div class="space-y-1.5">
                                        <template v-if="getSlaForPriority(p)">
                                            <div v-if="getSlaForPriority(p).response_time" 
                                                 class="flex items-center gap-1.5 text-xs"
                                                 :class="form.priority === p.name.toLowerCase() ? priorityText(p.name) : 'text-gray-500'"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Response: <strong>{{ formatMinutes(getSlaForPriority(p).response_time) }}</strong>
                                            </div>
                                            <div v-if="getSlaForPriority(p).resolution_time" 
                                                 class="flex items-center gap-1.5 text-xs"
                                                 :class="form.priority === p.name.toLowerCase() ? priorityText(p.name) : 'text-gray-500'"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Resolution: <strong>{{ formatMinutes(getSlaForPriority(p).resolution_time) }}</strong>
                                            </div>
                                        </template>
                                        <p v-else class="text-xs text-gray-400 italic">No SLA configured</p>
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.priority" class="text-sm text-red-600">{{ form.errors.priority }}</p>
                        </div>

                        <!-- Fallback for no priorities -->
                        <div v-else class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3">
                            <p class="text-sm text-yellow-700 flex items-center gap-2">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                ⚠ No ticket priorities are configured. Please contact an administrator.
                            </p>
                        </div>

                        <!-- Form Actions with loading states -->
                        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                            <button
                                type="submit"
                                :disabled="form.processing || !priorities.length"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span>{{ form.processing ? 'Creating Ticket...' : 'Create Ticket' }}</span>
                                
                                <!-- Keyboard shortcut hint -->
                                <span class="hidden sm:inline text-xs text-blue-200 ml-1">(Ctrl+Enter)</span>
                            </button>
                            
                            <Link
                                :href="route('user.tickets.index')"
                                class="rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-400 transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                Cancel
                            </Link>
                            
                            <!-- Clear draft button -->
                            <button
                                v-if="form.subject || form.description"
                                type="button"
                                @click="clearDraft"
                                class="ml-auto text-sm text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                Clear draft
                            </button>
                        </div>

                        <!-- Form validation summary -->
                        <div v-if="Object.keys(form.errors).length > 0" class="rounded-lg bg-red-50 border border-red-200 p-3">
                            <p class="text-xs text-red-600 flex items-center gap-1">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Please fix the errors above before submitting.
                            </p>
                        </div>

                    </form>
                </div>

                <!-- Help Card -->
                <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1 text-sm text-blue-700">
                            <p class="font-medium mb-1">Tips for a faster resolution:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Be specific about your issue - include error messages if any</li>
                                <li>Mention what you've already tried</li>
                                <li>Select the appropriate department for faster routing</li>
                                <li>Higher priority tickets get faster response times</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </UserNavigation>
</template>

<style scoped>
/* Smooth transitions */
textarea, input, select {
    transition: all 0.2s ease;
}

/* Custom focus styles */
textarea:focus, input:focus, select:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Loading animation */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>