<script setup>
import { computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    categories: Array
});

const page = usePage();
const layoutComponent = computed(() => {
    const role = page.props.auth.user.role;
    if (role === 'admin') return AdminNavigation;
    if (role === 'manager') return ManagerNavigation;
    return AgentNavigation;
});

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category? Ensure it has no articles.')) {
        router.delete(route('staff.kb.categories.destroy', id));
    }
};
</script>

<template>
    <component :is="layoutComponent">
        <template #header-title>
            <div class="flex items-center gap-4">
                <Link :href="route('staff.kb.articles.index')" class="p-2 rounded-xl bg-slate-100/50 hover:bg-slate-200/50 text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <div class="flex flex-col">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Manage Categories</h1>
                </div>
            </div>
        </template>

        <div class="mb-6 flex items-center justify-end">
            <Link :href="route('staff.kb.categories.create')" class="bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-black shadow-lg shadow-slate-200 hover:bg-slate-800 transition-colors">
                New Category
            </Link>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-5xl">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest w-16">Icon</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Name</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Articles</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Order</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="category in categories" :key="category.id" class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 text-lg">
                                <span v-if="category.icon" v-html="category.icon"></span>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-900">{{ category.name }}</span>
                                <span class="text-xs text-slate-500 truncate max-w-xs">{{ category.description || 'No description' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-600">
                            {{ category.articles_count || 0 }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-500 text-right">
                            {{ category.sort_order }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span v-if="category.is_active" class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-emerald-100/50 text-emerald-600 border border-emerald-200">
                                Active
                            </span>
                            <span v-else class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-slate-100 text-slate-500 border border-slate-200">
                                Inactive
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('public.kb.category', category.slug)" v-if="category.is_active">
                                        View Public
                                    </DropdownLink>
                                    <DropdownLink :href="route('staff.kb.categories.edit', category.id)">
                                        Edit
                                    </DropdownLink>
                                    <button @click="deleteCategory(category.id)" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-100">
                                        Delete
                                    </button>
                                </template>
                            </Dropdown>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500 text-sm font-medium">
                            No categories found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </component>
</template>
