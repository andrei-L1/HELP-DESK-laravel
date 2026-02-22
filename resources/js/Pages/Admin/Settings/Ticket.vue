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
        statusForm.put(route('admin.settings.ticket.statuses.update', statusForm.id), {
            preserveScroll: true,
            onSuccess: () => closeStatusModal(),
        });
    } else {
        statusForm.post(route('admin.settings.ticket.statuses.store'), {
            preserveScroll: true,
            onSuccess: () => closeStatusModal(),
        });
    }
};

const deleteStatus = (id) => {
    if (confirm('Are you sure you want to delete this status?')) {
        router.delete(route('admin.settings.ticket.statuses.destroy', id), {
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
        priorityForm.put(route('admin.settings.ticket.priorities.update', priorityForm.id), {
            preserveScroll: true,
            onSuccess: () => closePriorityModal(),
        });
    } else {
        priorityForm.post(route('admin.settings.ticket.priorities.store'), {
            preserveScroll: true,
            onSuccess: () => closePriorityModal(),
        });
    }
};

const deletePriority = (id) => {
    if (confirm('Are you sure you want to delete this priority?')) {
        router.delete(route('admin.settings.ticket.priorities.destroy', id), {
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
        categoryForm.put(route('admin.settings.ticket.categories.update', categoryForm.id), {
            preserveScroll: true,
            onSuccess: () => closeCategoryModal(),
        });
    } else {
        categoryForm.post(route('admin.settings.ticket.categories.store'), {
            preserveScroll: true,
            onSuccess: () => closeCategoryModal(),
        });
    }
};

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('admin.settings.ticket.categories.destroy', id), {
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
        typeForm.put(route('admin.settings.ticket.types.update', typeForm.id), {
            preserveScroll: true,
            onSuccess: () => closeTypeModal(),
        });
    } else {
        typeForm.post(route('admin.settings.ticket.types.store'), {
            preserveScroll: true,
            onSuccess: () => closeTypeModal(),
        });
    }
};

const deleteType = (id) => {
    if (confirm('Are you sure you want to delete this type?')) {
        router.delete(route('admin.settings.ticket.types.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Settings form
const submitSettings = () => {
    settingsForm.post(route('admin.settings.ticket.update'), {
        preserveScroll: true,
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
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.settings.index')"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Settings
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Ticket Settings</h1>
            </div>
        </template>

        <div class="p-6">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'statuses'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'statuses' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Statuses
                    </button>
                    <button
                        @click="activeTab = 'priorities'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'priorities' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Priorities
                    </button>
                    <button
                        @click="activeTab = 'categories'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'categories' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Categories
                    </button>
                    <button
                        @click="activeTab = 'types'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'types' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Types
                    </button>
                    <button
                        @click="activeTab = 'general'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'general' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        General Settings
                    </button>
                </nav>
            </div>

            <!-- Statuses Tab -->
            <div v-if="activeTab === 'statuses'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Ticket Statuses</h2>
                    <button
                        @click="openStatusModal()"
                        class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Status
                    </button>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Color</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sort Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="status in statuses" :key="status.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="h-6 w-6 rounded" :style="getStatusColorClass(status.color_hex)"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ status.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ status.title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span v-if="status.is_closed" class="text-red-600">Closed</span>
                                    <span v-else-if="status.is_resolved" class="text-green-600">Resolved</span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ status.sort_order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="status.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ status.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="openStatusModal(status)"
                                            class="text-slate-600 hover:text-slate-900"
                                            title="Edit status"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteStatus(status.id)"
                                            class="text-red-600 hover:text-red-800"
                                            title="Delete status"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="statuses.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No statuses found. Create your first status.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Priorities Tab -->
            <div v-if="activeTab === 'priorities'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Ticket Priorities</h2>
                    <button
                        @click="openPriorityModal()"
                        class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Priority
                    </button>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Color</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sort Order</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="priority in priorities" :key="priority.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="h-6 w-6 rounded" :style="getStatusColorClass(priority.color_hex)"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ priority.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ priority.level }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ priority.sort_order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="openPriorityModal(priority)"
                                            class="text-slate-600 hover:text-slate-900"
                                            title="Edit priority"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deletePriority(priority.id)"
                                            class="text-red-600 hover:text-red-800"
                                            title="Delete priority"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="priorities.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No priorities found. Create your first priority.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categories Tab -->
            <div v-if="activeTab === 'categories'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Ticket Categories</h2>
                    <button
                        @click="openCategoryModal()"
                        class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Category
                    </button>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sort Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="category in categories" :key="category.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ category.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ category.title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ category.sort_order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="openCategoryModal(category)"
                                            class="text-slate-600 hover:text-slate-900"
                                            title="Edit category"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteCategory(category.id)"
                                            class="text-red-600 hover:text-red-800"
                                            title="Delete category"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="categories.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No categories found. Create your first category.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Types Tab -->
            <div v-if="activeTab === 'types'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Ticket Types</h2>
                    <button
                        @click="openTypeModal()"
                        class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Type
                    </button>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sort Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="type in types" :key="type.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ type.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ type.title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ type.sort_order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="type.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ type.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="openTypeModal(type)"
                                            class="text-slate-600 hover:text-slate-900"
                                            title="Edit type"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteType(type.id)"
                                            class="text-red-600 hover:text-red-800"
                                            title="Delete type"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="types.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No types found. Create your first type.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- General Settings Tab -->
            <div v-if="activeTab === 'general'" class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700">Ticket Configuration</h2>
                        <p class="text-xs text-gray-500 mt-1">Configure default ticket settings</p>
                    </div>
                    
                    <form @submit.prevent="submitSettings" class="px-6 py-4 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="default_status_id" class="block text-sm font-medium text-gray-700">
                                    Default Status
                                </label>
                                <select
                                    id="default_status_id"
                                    v-model="settingsForm.default_status_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option value="">Select default status</option>
                                    <option v-for="status in statuses" :key="status.id" :value="status.id">
                                        {{ status.title }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="default_priority_id" class="block text-sm font-medium text-gray-700">
                                    Default Priority
                                </label>
                                <select
                                    id="default_priority_id"
                                    v-model="settingsForm.default_priority_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option value="">Select default priority</option>
                                    <option v-for="priority in priorities" :key="priority.id" :value="priority.id">
                                        {{ priority.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center text-sm text-gray-700">
                                <input
                                    v-model="settingsForm.allow_attachments"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                />
                                <span class="ml-2">Allow file attachments on tickets</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="max_attachments" class="block text-sm font-medium text-gray-700">
                                    Max Attachments per Ticket
                                </label>
                                <input
                                    id="max_attachments"
                                    v-model="settingsForm.max_attachments"
                                    type="number"
                                    min="1"
                                    max="20"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                            </div>

                            <div>
                                <label for="max_attachment_size" class="block text-sm font-medium text-gray-700">
                                    Max Attachment Size (KB)
                                </label>
                                <input
                                    id="max_attachment_size"
                                    v-model="settingsForm.max_attachment_size"
                                    type="number"
                                    min="1"
                                    max="10240"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                />
                                <p class="mt-1 text-xs text-gray-500">Max 10MB (10240 KB)</p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center text-sm text-gray-700">
                                <input
                                    v-model="settingsForm.auto_assign"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                />
                                <span class="ml-2">Auto-assign tickets to available agents</span>
                            </label>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                class="inline-flex justify-center rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                :disabled="settingsForm.processing"
                            >
                                {{ settingsForm.processing ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status Modal -->
            <Teleport to="body">
                <div
                    v-if="showStatusModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="Status modal"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div
                            class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            aria-hidden="true"
                            @click="closeStatusModal"
                        />
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        >
                            <form @submit.prevent="saveStatus" class="p-6 space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ editingStatus ? 'Edit Status' : 'Create Status' }}
                                </h3>
                                
                                <div>
                                    <label for="status-name" class="block text-sm font-medium text-gray-700">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="status-name"
                                        v-model="statusForm.name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="e.g., open, pending, resolved"
                                    />
                                    <p v-if="statusForm.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ statusForm.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label for="status-title" class="block text-sm font-medium text-gray-700">
                                        Title <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="status-title"
                                        v-model="statusForm.title"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="e.g., Open, Pending, Resolved"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="status-color" class="block text-sm font-medium text-gray-700">
                                            Color
                                        </label>
                                        <input
                                            id="status-color"
                                            v-model="statusForm.color_hex"
                                            type="color"
                                            class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="status-sort" class="block text-sm font-medium text-gray-700">
                                            Sort Order
                                        </label>
                                        <input
                                            id="status-sort"
                                            v-model="statusForm.sort_order"
                                            type="number"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center text-sm text-gray-700">
                                        <input
                                            v-model="statusForm.is_active"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                        />
                                        <span class="ml-2">Active</span>
                                    </label>

                                    <label class="flex items-center text-sm text-gray-700">
                                        <input
                                            v-model="statusForm.is_closed"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                        />
                                        <span class="ml-2">Closed Status</span>
                                    </label>

                                    <label class="flex items-center text-sm text-gray-700">
                                        <input
                                            v-model="statusForm.is_resolved"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                        />
                                        <span class="ml-2">Resolved Status</span>
                                    </label>
                                </div>

                                <div class="flex gap-3 justify-end pt-2">
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                        @click="closeStatusModal"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                        :disabled="statusForm.processing"
                                    >
                                        {{ statusForm.processing ? 'Saving...' : (editingStatus ? 'Update' : 'Create') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Priority Modal -->
                <div
                    v-if="showPriorityModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="Priority modal"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div
                            class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            aria-hidden="true"
                            @click="closePriorityModal"
                        />
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        >
                            <form @submit.prevent="savePriority" class="p-6 space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ editingPriority ? 'Edit Priority' : 'Create Priority' }}
                                </h3>
                                
                                <div>
                                    <label for="priority-name" class="block text-sm font-medium text-gray-700">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="priority-name"
                                        v-model="priorityForm.name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="e.g., Low, Medium, High"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="priority-level" class="block text-sm font-medium text-gray-700">
                                            Level
                                        </label>
                                        <input
                                            id="priority-level"
                                            v-model="priorityForm.level"
                                            type="number"
                                            min="1"
                                            max="10"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        />
                                    </div>

                                    <div>
                                        <label for="priority-color" class="block text-sm font-medium text-gray-700">
                                            Color
                                        </label>
                                        <input
                                            id="priority-color"
                                            v-model="priorityForm.color_hex"
                                            type="color"
                                            class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label for="priority-sort" class="block text-sm font-medium text-gray-700">
                                        Sort Order
                                    </label>
                                    <input
                                        id="priority-sort"
                                        v-model="priorityForm.sort_order"
                                        type="number"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    />
                                </div>

                                <div class="flex gap-3 justify-end pt-2">
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                        @click="closePriorityModal"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                        :disabled="priorityForm.processing"
                                    >
                                        {{ priorityForm.processing ? 'Saving...' : (editingPriority ? 'Update' : 'Create') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Category Modal -->
                <div
                    v-if="showCategoryModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="Category modal"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div
                            class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            aria-hidden="true"
                            @click="closeCategoryModal"
                        />
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        >
                            <form @submit.prevent="saveCategory" class="p-6 space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ editingCategory ? 'Edit Category' : 'Create Category' }}
                                </h3>
                                
                                <div>
                                    <label for="category-name" class="block text-sm font-medium text-gray-700">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="category-name"
                                        v-model="categoryForm.name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="e.g., technical, billing"
                                    />
                                </div>

                                <div>
                                    <label for="category-title" class="block text-sm font-medium text-gray-700">
                                        Title <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="category-title"
                                        v-model="categoryForm.title"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="e.g., Technical Support"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="category-sort" class="block text-sm font-medium text-gray-700">
                                            Sort Order
                                        </label>
                                        <input
                                            id="category-sort"
                                            v-model="categoryForm.sort_order"
                                            type="number"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        />
                                    </div>

                                    <div class="flex items-center">
                                        <label class="flex items-center text-sm text-gray-700">
                                            <input
                                                v-model="categoryForm.is_active"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                            />
                                            <span class="ml-2">Active</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex gap-3 justify-end pt-2">
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                        @click="closeCategoryModal"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                        :disabled="categoryForm.processing"
                                    >
                                        {{ categoryForm.processing ? 'Saving...' : (editingCategory ? 'Update' : 'Create') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Type Modal -->
                <div
                    v-if="showTypeModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="Type modal"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div
                            class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            aria-hidden="true"
                            @click="closeTypeModal"
                        />
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        >
                            <form @submit.prevent="saveType" class="p-6 space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ editingType ? 'Edit Type' : 'Create Type' }}
                                </h3>
                                
                                <div>
                                    <label for="type-name" class="block text-sm font-medium text-gray-700">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="type-name"
                                        v-model="typeForm.name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="e.g., incident, request"
                                    />
                                </div>

                                <div>
                                    <label for="type-title" class="block text-sm font-medium text-gray-700">
                                        Title <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="type-title"
                                        v-model="typeForm.title"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="e.g., Incident, Service Request"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="type-sort" class="block text-sm font-medium text-gray-700">
                                            Sort Order
                                        </label>
                                        <input
                                            id="type-sort"
                                            v-model="typeForm.sort_order"
                                            type="number"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        />
                                    </div>

                                    <div class="flex items-center">
                                        <label class="flex items-center text-sm text-gray-700">
                                            <input
                                                v-model="typeForm.is_active"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                            />
                                            <span class="ml-2">Active</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex gap-3 justify-end pt-2">
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                        @click="closeTypeModal"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                        :disabled="typeForm.processing"
                                    >
                                        {{ typeForm.processing ? 'Saving...' : (editingType ? 'Update' : 'Create') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </AdminNavigation>
</template>