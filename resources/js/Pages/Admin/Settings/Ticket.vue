<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    statuses: {
        type: Array,
        default: () => [],
    },
    priorities: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    types: {
        type: Array,
        default: () => [],
    },
    settings: {
        type: Object,
        default: () => ({
            default_status_id: null,
            default_priority_id: null,
            allow_attachments: true,
            max_attachments: 5,
            max_attachment_size: 2048, // in KB
            auto_assign: false,
        }),
    },
});

const activeTab = ref('statuses');

// Status management
const showStatusModal = ref(false);
const editingStatus = ref(null);
const statusForm = useForm({
    id: null,
    name: '',
    title: '',
    color_hex: '#64748b',
    sort_order: 100,
    is_active: true,
    is_closed: false,
    is_resolved: false,
});

// Priority management
const showPriorityModal = ref(false);
const editingPriority = ref(null);
const priorityForm = useForm({
    id: null,
    name: '',
    level: 1,
    color_hex: '#64748b',
    sort_order: 100,
});

// Category management
const showCategoryModal = ref(false);
const editingCategory = ref(null);
const categoryForm = useForm({
    id: null,
    name: '',
    title: '',
    sort_order: 100,
    is_active: true,
});

// Type management
const showTypeModal = ref(false);
const editingType = ref(null);
const typeForm = useForm({
    id: null,
    name: '',
    title: '',
    sort_order: 100,
    is_active: true,
});

// Settings form
const settingsForm = useForm({
    default_status_id: props.settings.default_status_id,
    default_priority_id: props.settings.default_priority_id,
    allow_attachments: props.settings.allow_attachments,
    max_attachments: props.settings.max_attachments,
    max_attachment_size: props.settings.max_attachment_size,
    auto_assign: props.settings.auto_assign,
});

// Modal functions
const openStatusModal = (status = null) => {
    if (status) {
        editingStatus.value = status;
        statusForm.id = status.id;
        statusForm.name = status.name;
        statusForm.title = status.title;
        statusForm.color_hex = status.color_hex || '#64748b';
        statusForm.sort_order = status.sort_order || 100;
        statusForm.is_active = status.is_active ?? true;
        statusForm.is_closed = status.is_closed ?? false;
        statusForm.is_resolved = status.is_resolved ?? false;
    } else {
        editingStatus.value = null;
        statusForm.reset();
        statusForm.sort_order = 100;
        statusForm.is_active = true;
    }
    showStatusModal.value = true;
};

const closeStatusModal = () => {
    showStatusModal.value = false;
    statusForm.reset();
    editingStatus.value = null;
};

const saveStatus = () => {
    if (editingStatus.value) {
        statusForm.put(route('admin.settings.statuses.update', statusForm.id), {
            preserveScroll: true,
            onSuccess: () => closeStatusModal(),
        });
    } else {
        statusForm.post(route('admin.settings.statuses.store'), {
            preserveScroll: true,
            onSuccess: () => closeStatusModal(),
        });
    }
};

const deleteStatus = (id) => {
    if (confirm('Are you sure you want to delete this status?')) {
        router.delete(route('admin.settings.statuses.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Priority functions
const openPriorityModal = (priority = null) => {
    if (priority) {
        editingPriority.value = priority;
        priorityForm.id = priority.id;
        priorityForm.name = priority.name;
        priorityForm.level = priority.level || 1;
        priorityForm.color_hex = priority.color_hex || '#64748b';
        priorityForm.sort_order = priority.sort_order || 100;
    } else {
        editingPriority.value = null;
        priorityForm.reset();
        priorityForm.level = 1;
        priorityForm.sort_order = 100;
    }
    showPriorityModal.value = true;
};

const closePriorityModal = () => {
    showPriorityModal.value = false;
    priorityForm.reset();
    editingPriority.value = null;
};

const savePriority = () => {
    if (editingPriority.value) {
        priorityForm.put(route('admin.settings.priorities.update', priorityForm.id), {
            preserveScroll: true,
            onSuccess: () => closePriorityModal(),
        });
    } else {
        priorityForm.post(route('admin.settings.priorities.store'), {
            preserveScroll: true,
            onSuccess: () => closePriorityModal(),
        });
    }
};

const deletePriority = (id) => {
    if (confirm('Are you sure you want to delete this priority?')) {
        router.delete(route('admin.settings.priorities.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Category functions
const openCategoryModal = (category = null) => {
    if (category) {
        editingCategory.value = category;
        categoryForm.id = category.id;
        categoryForm.name = category.name;
        categoryForm.title = category.title;
        categoryForm.sort_order = category.sort_order || 100;
        categoryForm.is_active = category.is_active ?? true;
    } else {
        editingCategory.value = null;
        categoryForm.reset();
        categoryForm.sort_order = 100;
        categoryForm.is_active = true;
    }
    showCategoryModal.value = true;
};

const closeCategoryModal = () => {
    showCategoryModal.value = false;
    categoryForm.reset();
    editingCategory.value = null;
};

const saveCategory = () => {
    if (editingCategory.value) {
        categoryForm.put(route('admin.settings.categories.update', categoryForm.id), {
            preserveScroll: true,
            onSuccess: () => closeCategoryModal(),
        });
    } else {
        categoryForm.post(route('admin.settings.categories.store'), {
            preserveScroll: true,
            onSuccess: () => closeCategoryModal(),
        });
    }
};

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('admin.settings.categories.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Type functions
const openTypeModal = (type = null) => {
    if (type) {
        editingType.value = type;
        typeForm.id = type.id;
        typeForm.name = type.name;
        typeForm.title = type.title;
        typeForm.sort_order = type.sort_order || 100;
        typeForm.is_active = type.is_active ?? true;
    } else {
        editingType.value = null;
        typeForm.reset();
        typeForm.sort_order = 100;
        typeForm.is_active = true;
    }
    showTypeModal.value = true;
};

const closeTypeModal = () => {
    showTypeModal.value = false;
    typeForm.reset();
    editingType.value = null;
};

const saveType = () => {
    if (editingType.value) {
        typeForm.put(route('admin.settings.types.update', typeForm.id), {
            preserveScroll: true,
            onSuccess: () => closeTypeModal(),
        });
    } else {
        typeForm.post(route('admin.settings.types.store'), {
            preserveScroll: true,
            onSuccess: () => closeTypeModal(),
        });
    }
};

const deleteType = (id) => {
    if (confirm('Are you sure you want to delete this type?')) {
        router.delete(route('admin.settings.types.destroy', id), {
            preserveScroll: true,
        });
    }
};

const saveSuccess = ref(false);

// Settings form
const submitSettings = () => {
    settingsForm.post(route('admin.settings.ticket.update'), {
        preserveScroll: true,
        onSuccess: () => {
            saveSuccess.value = true;
            setTimeout(() => {
                saveSuccess.value = false;
            }, 5000);
        },
    });
};

const getStatusColorClass = (color) => {
    return { backgroundColor: color || '#64748b' };
};
</script>

<template>
    <Head title="Ticket Settings" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex flex-col">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                    <Link :href="route('admin.settings.index')" class="hover:text-slate-900 transition-colors">Settings</Link>
                    <span>/</span>
                    <span class="text-slate-900">Tickets</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Ticket Infrastructure</h1>
            </div>
        </template>

        <div class="p-6">
            <!-- Navigation Tabs -->
            <div class="mb-10 stagger-1">
                <nav class="flex flex-wrap gap-2 bg-slate-100/50 p-1.5 rounded-2xl w-fit border border-slate-200/60 backdrop-blur-sm">
                    <button
                        v-for="tab in [
                            { id: 'statuses', label: 'Statuses', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
                            { id: 'priorities', label: 'Priorities', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
                            { id: 'categories', label: 'Categories', icon: 'M7 7h.01M7 11h.01M7 15h.01M11 7h8M11 11h8M11 15h8' },
                            { id: 'types', label: 'Types', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
                            { id: 'general', label: 'Configuration', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }
                        ]"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-2.5 px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300"
                        :class="activeTab === tab.id 
                            ? 'bg-white text-slate-900 shadow-md shadow-slate-200/50 ring-1 ring-slate-200' 
                            : 'text-slate-400 hover:text-slate-600 hover:bg-white/50'"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="tab.icon" />
                        </svg>
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Statuses Tab -->
            <div v-if="activeTab === 'statuses'" class="stagger-2 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-2">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">System Statuses</h2>
                        <p class="text-sm text-slate-500 font-medium">Define the lifecycle stages of your support tickets.</p>
                    </div>
                    <button
                        @click="openStatusModal()"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-slate-900 text-white text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        Create New Status
                    </button>
                </div>

                <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Visual</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Identity</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Type</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Order</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Lifecycle</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Manage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="status in statuses" :key="status.id" class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="h-8 w-8 rounded-xl shadow-inner border border-white" :style="getStatusColorClass(status.color_hex)"></div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-slate-900 text-sm">{{ status.title }}</div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ status.name }}</div>
                                    </td>
                                    <td class="px-8 py-5 text-sm">
                                        <div v-if="status.is_closed" class="inline-flex items-center gap-1.5 text-rose-600 font-bold bg-rose-50 px-2.5 py-1 rounded-lg">
                                            <span class="h-1 w-1 rounded-full bg-rose-600"></span> Terminal
                                        </div>
                                        <div v-else-if="status.is_resolved" class="inline-flex items-center gap-1.5 text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1 rounded-lg">
                                            <span class="h-1 w-1 rounded-full bg-emerald-600"></span> Success
                                        </div>
                                        <span v-else class="text-slate-400 font-medium italic">Active</span>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-slate-600">
                                        {{ status.sort_order }}
                                    </td>
                                    <td class="px-8 py-5">
                                        <span v-if="status.deleted_at" class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-400 uppercase tracking-widest line-through">Archived</span>
                                        <span v-else :class="status.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'" 
                                              class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                                            {{ status.is_active ? 'Online' : 'Disabled' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openStatusModal(status)" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-900 hover:border-slate-900 transition-all">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button @click="deleteStatus(status.id)" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-600 transition-all">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="statuses.length === 0">
                                    <td colspan="6" class="px-8 py-16 text-center">
                                        <div class="text-slate-400 font-bold italic">No statuses found. Begin by creating your first.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Priorities Tab -->
            <div v-if="activeTab === 'priorities'" class="stagger-2 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-2">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Severity Levels</h2>
                        <p class="text-sm text-slate-500 font-medium">Control the urgency and response prioritization of incoming requests.</p>
                    </div>
                    <button
                        @click="openPriorityModal()"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-slate-900 text-white text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Severity Level
                    </button>
                </div>

                <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Visual Marker</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Label</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Internal Level</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Sequence</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Manage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="priority in priorities" :key="priority.id" class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="h-8 w-12 rounded-xl shadow-sm border border-white flex items-center justify-center" :style="getStatusColorClass(priority.color_hex)">
                                            <span class="text-[10px] font-black text-white/50">{{ priority.level }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-slate-900 text-sm">{{ priority.name }}</div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-lg">Level {{ priority.level }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-slate-600">
                                        <span v-if="priority.deleted_at" class="text-slate-300 line-through italic">Archived</span>
                                        <span v-else>{{ priority.sort_order }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openPriorityModal(priority)" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-900 hover:border-slate-900 transition-all">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button @click="deletePriority(priority.id)" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-600 transition-all">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="priorities.length === 0">
                                    <td colspan="5" class="px-8 py-16 text-center">
                                        <div class="text-slate-400 font-bold italic">No severity levels found.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Categories Tab -->
            <div v-if="activeTab === 'categories'" class="stagger-2 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-2">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Functional Categories</h2>
                        <p class="text-sm text-slate-500 font-medium">Classify tickets by department or functional area.</p>
                    </div>
                    <button
                        @click="openCategoryModal()"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-slate-900 text-white text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        New Category
                    </button>
                </div>

                <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Identity</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Sequence</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Status</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Manage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="category in categories" :key="category.id" class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-slate-900 text-sm">{{ category.title }}</div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ category.name }}</div>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-slate-600">
                                        {{ category.sort_order }}
                                    </td>
                                    <td class="px-8 py-5">
                                        <span v-if="category.deleted_at" class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-400 uppercase tracking-widest line-through">Archived</span>
                                        <span v-else :class="category.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'" 
                                              class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text">
                                            {{ category.is_active ? 'Active' : 'Disabled' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openCategoryModal(category)" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-900 hover:border-slate-900 transition-all">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button @click="deleteCategory(category.id)" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-600 transition-all">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="categories.length === 0">
                                    <td colspan="4" class="px-8 py-16 text-center">
                                        <div class="text-slate-400 font-bold italic">No categories found.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Types Tab -->
            <div v-if="activeTab === 'types'" class="stagger-2 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-2">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Request Types</h2>
                        <p class="text-sm text-slate-500 font-medium">Define the nature of incoming support requests.</p>
                    </div>
                    <button
                        @click="openTypeModal()"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-slate-900 text-white text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        New Request Type
                    </button>
                </div>

                <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Identity</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Sequence</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Status</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Manage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="type in types" :key="type.id" class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-slate-900 text-sm">{{ type.title }}</div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ type.name }}</div>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-slate-600">
                                        {{ type.sort_order }}
                                    </td>
                                    <td class="px-8 py-5">
                                        <span v-if="type.deleted_at" class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-400 uppercase tracking-widest line-through">Archived</span>
                                        <span v-else :class="type.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'" 
                                              class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text">
                                            {{ type.is_active ? 'Active' : 'Disabled' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openTypeModal(type)" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-900 hover:border-slate-900 transition-all">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button @click="deleteType(type.id)" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-600 transition-all">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="types.length === 0">
                                    <td colspan="4" class="px-8 py-16 text-center">
                                        <div class="text-slate-400 font-bold italic">No request types found.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- General Settings Tab -->
            <div v-if="activeTab === 'general'" class="stagger-2 space-y-10 max-w-5xl">
                <!-- Success Messaging -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform -translate-y-4 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="saveSuccess" class="bg-emerald-900 text-white px-6 py-4 rounded-3xl shadow-xl shadow-emerald-200/50 flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-emerald-800 flex items-center justify-center">
                            <svg class="h-5 w-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-black uppercase tracking-widest">Protocol Updated</p>
                            <p class="text-xs text-emerald-200/80 font-medium italic">General ticket infrastructure has been successfully reconfigured.</p>
                        </div>
                    </div>
                </Transition>

                <form @submit.prevent="submitSettings" class="space-y-8 pb-20">
                    <!-- Infrastructure Constants -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-1 uppercase">Standard Defaults</h3>
                            <p class="text-sm text-slate-500 font-serif italic">Establish the initial state of every incoming service request.</p>
                        </div>
                        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-300/40 p-10 shadow-sm shadow-slate-200/60 space-y-8">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-left">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Default Status</label>
                                    <select v-model="settingsForm.default_status_id" 
                                            class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all appearance-none">
                                        <option value="">Select entry status</option>
                                        <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.title }}</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Default Severity</label>
                                    <select v-model="settingsForm.default_priority_id" 
                                            class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all appearance-none">
                                        <option value="">Select urgency level</option>
                                        <option v-for="priority in priorities" :key="priority.id" :value="priority.id">{{ priority.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-8 border-t border-slate-100">
                                <label class="group flex items-center justify-between p-6 bg-slate-50 rounded-2xl border border-slate-100 cursor-pointer hover:bg-slate-100/50 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center shadow-sm text-slate-400">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-900 uppercase">Intelligent Routing</p>
                                            <p class="text-[10px] text-slate-500 font-serif italic">Automatically assign new tickets to available support engineers.</p>
                                        </div>
                                    </div>
                                    <input v-model="settingsForm.auto_assign" type="checkbox" 
                                           class="h-6 w-11 rounded-full bg-slate-300 border-none text-slate-900 focus:ring-0 focus:ring-offset-0 checked:bg-slate-900 transition-all appearance-none relative before:content-[''] before:absolute before:h-4 before:w-4 before:bg-white before:rounded-full before:top-1 before:left-1 checked:before:translate-x-5 before:transition-all cursor-pointer shadow-inner" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Media Handling -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1 text-left">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-1 uppercase">Asset Transmission</h3>
                            <p class="text-sm text-slate-500 font-serif italic">Manage file attachments and technical documentation uploads.</p>
                        </div>
                        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-300/40 p-10 shadow-sm shadow-slate-200/60 space-y-8">
                            <label class="group flex items-center justify-between p-6 bg-slate-50 rounded-2xl border border-slate-100 cursor-pointer hover:bg-slate-100/50 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center shadow-sm text-slate-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.414a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 uppercase">File Attachments</p>
                                        <p class="text-[10px] text-slate-500 font-serif italic">Enable users to upload screenshots and logs to their requests.</p>
                                    </div>
                                </div>
                                <input v-model="settingsForm.allow_attachments" type="checkbox" 
                                       class="h-6 w-11 rounded-full bg-slate-300 border-none text-slate-900 focus:ring-0 focus:ring-offset-0 checked:bg-slate-900 transition-all appearance-none relative before:content-[''] before:absolute before:h-4 before:w-4 before:bg-white before:rounded-full before:top-1 before:left-1 checked:before:translate-x-5 before:transition-all cursor-pointer shadow-inner" />
                            </label>

                            <div v-if="settingsForm.allow_attachments" class="grid grid-cols-1 sm:grid-cols-2 gap-8 stagger-3">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Max Upload Count</label>
                                    <div class="relative group">
                                        <input v-model="settingsForm.max_attachments" type="number" min="1" max="20"
                                               class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" />
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-300 uppercase italic">Files</div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Payload Ceiling</label>
                                    <div class="relative group">
                                        <input v-model="settingsForm.max_attachment_size" type="number" min="1" max="10240"
                                               class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" />
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-300 uppercase italic">KB</div>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-serif italic ml-1">System maximum: 10,240 KB (10 MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-10 border-t border-slate-100 stagger-4">
                        <button
                            type="submit"
                            class="group relative inline-flex items-center justify-center gap-3 px-12 py-5 rounded-3xl bg-slate-900 text-white text-sm font-black shadow-2xl shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95 disabled:opacity-50 overflow-hidden"
                            :disabled="settingsForm.processing"
                        >
                            <span v-if="settingsForm.processing" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                SYNCING...
                            </span>
                            <span v-else class="flex items-center gap-3">
                                COMMIT CHANGES
                                <svg class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Status Modal -->
            <Teleport to="body">
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeStatusModal" />
                        
                        <div class="relative bg-white rounded-[2rem] shadow-2xl shadow-slate-900/20 w-full max-w-lg overflow-hidden transform transition-all border border-slate-100">
                            <form @submit.prevent="saveStatus" class="p-8 space-y-6">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-6 mb-2">
                                    <div>
                                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ editingStatus ? 'Refine Status' : 'New Status Entity' }}</h3>
                                        <p class="text-xs text-slate-400 font-serif italic">Define a new stage in the ticket lifecycle.</p>
                                    </div>
                                    <button type="button" @click="closeStatusModal" class="p-2 rounded-xl hover:bg-slate-50 text-slate-300 hover:text-slate-900 transition-colors">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1 text-left">Internal Name <span class="text-rose-500">*</span></label>
                                        <input v-model="statusForm.name" type="text" required class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" placeholder="e.g. open_internal" />
                                        <p v-if="statusForm.errors.name" class="mt-1 text-xs text-rose-600 font-bold uppercase tracking-widest">{{ statusForm.errors.name }}</p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1 text-left">Public Display Title <span class="text-rose-500">*</span></label>
                                        <input v-model="statusForm.title" type="text" required class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" placeholder="e.g. Assigned to Engineer" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1.5 text-left">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Chromatic Marker</label>
                                            <div class="relative group">
                                                <input v-model="statusForm.color_hex" type="color" class="w-full h-14 bg-slate-50 border-0 rounded-2xl p-1.5 cursor-pointer ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all" />
                                            </div>
                                        </div>
                                        <div class="space-y-1.5 text-left">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Sequence Order</label>
                                            <input v-model="statusForm.sort_order" type="number" class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" />
                                        </div>
                                    </div>

                                    <div class="pt-4 space-y-3">
                                        <div class="flex flex-wrap gap-4">
                                            <label class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 cursor-pointer hover:bg-slate-100 transition-all text-left">
                                                <input v-model="statusForm.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-0" />
                                                <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none mt-0.5">Active</span>
                                            </label>
                                            <label class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 cursor-pointer hover:bg-slate-100 transition-all text-left">
                                                <input v-model="statusForm.is_closed" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-0" />
                                                <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none mt-0.5">Terminal Stage</span>
                                            </label>
                                            <label class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 cursor-pointer hover:bg-slate-100 transition-all text-left">
                                                <input v-model="statusForm.is_resolved" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-0" />
                                                <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none mt-0.5">Resolution Point</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <button type="button" @click="closeStatusModal" class="flex-1 px-8 py-4 rounded-2xl bg-slate-50 text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-100 hover:text-slate-900 transition-all">Cancel</button>
                                    <button type="submit" :disabled="statusForm.processing" class="flex-2 px-10 py-4 rounded-2xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50">
                                        {{ statusForm.processing ? 'Syncing...' : (editingStatus ? 'Update Stage' : 'Commit Stage') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Transition>

                <!-- Priority Modal -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="showPriorityModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closePriorityModal" />
                        
                        <div class="relative bg-white rounded-[2rem] shadow-2xl shadow-slate-900/20 w-full max-w-lg overflow-hidden transform transition-all border border-slate-100">
                            <form @submit.prevent="savePriority" class="p-8 space-y-6">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-6 mb-2">
                                    <div>
                                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ editingPriority ? 'Refine Severity' : 'New Severity Level' }}</h3>
                                        <p class="text-xs text-slate-400 font-serif italic">Establish a new level of urgency for incoming requests.</p>
                                    </div>
                                    <button type="button" @click="closePriorityModal" class="p-2 rounded-xl hover:bg-slate-50 text-slate-300 hover:text-slate-900 transition-colors">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="space-y-1.5 text-left">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Label Name <span class="text-rose-500">*</span></label>
                                        <input v-model="priorityForm.name" type="text" required class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" placeholder="e.g. Critical Support" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1.5 text-left">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Severity Level (1-10)</label>
                                            <input v-model="priorityForm.level" type="number" min="1" max="10" class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" />
                                        </div>
                                        <div class="space-y-1.5 text-left text-left">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Marker Color</label>
                                            <input v-model="priorityForm.color_hex" type="color" class="w-full h-14 bg-slate-50 border-0 rounded-2xl p-1.5 cursor-pointer ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all" />
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 text-left">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1 text-left">Sequence Order</label>
                                        <input v-model="priorityForm.sort_order" type="number" class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" />
                                    </div>
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <button type="button" @click="closePriorityModal" class="flex-1 px-8 py-4 rounded-2xl bg-slate-50 text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-100 hover:text-slate-900 transition-all">Cancel</button>
                                    <button type="submit" :disabled="priorityForm.processing" class="flex-2 px-10 py-4 rounded-2xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50">
                                        {{ priorityForm.processing ? 'Syncing...' : (editingPriority ? 'Update Level' : 'Commit Level') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Transition>

                <!-- Category Modal -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="showCategoryModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center text-left">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeCategoryModal" />
                        
                        <div class="relative bg-white rounded-[2rem] shadow-2xl shadow-slate-900/20 w-full max-w-lg overflow-hidden transform transition-all border border-slate-100">
                            <form @submit.prevent="saveCategory" class="p-8 space-y-6">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-6 mb-2">
                                    <div>
                                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ editingCategory ? 'Modify Portfolio' : 'New Domain' }}</h3>
                                        <p class="text-xs text-slate-400 font-serif italic">Categorize tickets by departmental focus.</p>
                                    </div>
                                    <button type="button" @click="closeCategoryModal" class="p-2 rounded-xl hover:bg-slate-50 text-slate-300 hover:text-slate-900 transition-colors">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="space-y-1.5 text-left">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Unique Key <span class="text-rose-500">*</span></label>
                                        <input v-model="categoryForm.name" type="text" required class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" placeholder="e.g. infrastructure_core" />
                                    </div>

                                    <div class="space-y-1.5 text-left">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Display Title <span class="text-rose-500">*</span></label>
                                        <input v-model="categoryForm.title" type="text" required class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" placeholder="e.g. Core Systems" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-6 items-end">
                                        <div class="space-y-1.5 text-left">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Priority Order</label>
                                            <input v-model="categoryForm.sort_order" type="number" class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" />
                                        </div>
                                        <label class="flex items-center gap-2 px-6 py-4 bg-slate-50 rounded-2xl border border-slate-100 cursor-pointer hover:bg-slate-100 transition-all h-fit">
                                            <input v-model="categoryForm.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-0" />
                                            <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none mt-0.5">Deployment Ready</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <button type="button" @click="closeCategoryModal" class="flex-1 px-8 py-4 rounded-2xl bg-slate-50 text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-100 hover:text-slate-900 transition-all">Cancel</button>
                                    <button type="submit" :disabled="categoryForm.processing" class="flex-2 px-10 py-4 rounded-2xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50">
                                        {{ categoryForm.processing ? 'Syncing...' : (editingCategory ? 'Update Domain' : 'Commit Domain') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Transition>

                <!-- Type Modal -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="showTypeModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center text-left">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeTypeModal" />
                        
                        <div class="relative bg-white rounded-[2rem] shadow-2xl shadow-slate-900/20 w-full max-w-lg overflow-hidden transform transition-all border border-slate-100">
                            <form @submit.prevent="saveType" class="p-8 space-y-6 text-left">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-6 mb-2">
                                    <div>
                                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ editingType ? 'Refine Logic' : 'New Interaction' }}</h3>
                                        <p class="text-xs text-slate-400 font-serif italic">Establish the logic for ticket classification.</p>
                                    </div>
                                    <button type="button" @click="closeTypeModal" class="p-2 rounded-xl hover:bg-slate-50 text-slate-300 hover:text-slate-900 transition-colors">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="space-y-1.5 text-left">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Logic Key <span class="text-rose-500">*</span></label>
                                        <input v-model="typeForm.name" type="text" required class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" placeholder="e.g. system_incident" />
                                    </div>

                                    <div class="space-y-1.5 text-left">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Display Title <span class="text-rose-500">*</span></label>
                                        <input v-model="typeForm.title" type="text" required class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" placeholder="e.g. Critical Incident" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-6 items-end">
                                        <div class="space-y-1.5 text-left">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Priority Order</label>
                                            <input v-model="typeForm.sort_order" type="number" class="w-full bg-slate-50 border-0 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 ring-1 ring-slate-900/5 focus:ring-2 focus:ring-slate-900 transition-all shadow-inner" />
                                        </div>
                                        <label class="flex items-center gap-2 px-6 py-4 bg-slate-50 rounded-2xl border border-slate-100 cursor-pointer hover:bg-slate-100 transition-all h-fit">
                                            <input v-model="typeForm.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-0" />
                                            <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none mt-0.5">Active Logic</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <button type="button" @click="closeTypeModal" class="flex-1 px-8 py-4 rounded-2xl bg-slate-50 text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-100 hover:text-slate-900 transition-all">Cancel</button>
                                    <button type="submit" :disabled="typeForm.processing" class="flex-2 px-10 py-4 rounded-2xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50">
                                        {{ typeForm.processing ? 'Syncing...' : (editingType ? 'Update Logic' : 'Commit Logic') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </AdminNavigation>
</template>