<script setup>
import { computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import ManagerNavigation from '@/Components/ManagerNavigation.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    articles: Object,
    categories: Array,
    filters: Object
});

const page = usePage();
const layoutComponent = computed(() => {
    const role = page.props.auth.user.role;
    if (role === 'admin') return AdminNavigation;
    if (role === 'manager') return ManagerNavigation;
    return AgentNavigation;
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};

const deleteArticle = (id) => {
    if (confirm('Are you sure you want to delete this article?')) {
        router.delete(route('staff.kb.articles.destroy', id));
    }
};
</script>

<template>
    <component :is="layoutComponent">
        <template #header-title>
            <div class="flex flex-col">
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Knowledge Base Articles</h1>
            </div>
        </template>

        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="route('staff.kb.articles.index')" class="text-sm font-bold text-slate-600 hover:text-slate-900">All</Link>
                <Link :href="route('staff.kb.articles.index', { status: 'published' })" class="text-sm font-bold text-emerald-600 hover:text-emerald-700">Published</Link>
                <Link :href="route('staff.kb.articles.index', { status: 'draft' })" class="text-sm font-bold text-amber-600 hover:text-amber-700">Drafts</Link>
                <Link :href="route('staff.kb.articles.index', { status: 'internal' })" class="text-sm font-bold text-blue-600 hover:text-blue-700">Internal</Link>
            </div>
            <div class="flex gap-4">
                <Link :href="route('staff.kb.categories.index')" class="bg-white border-2 border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-black hover:bg-slate-50 transition-colors shadow-sm">
                    Manage Categories
                </Link>
                <Link :href="route('staff.kb.articles.create')" class="bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-black shadow-lg shadow-slate-200 hover:bg-slate-800 transition-colors">
                    New Article
                </Link>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Article</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Category</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Views</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="article in articles.data" :key="article.id" class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-900">{{ article.title }}</span>
                                <span class="text-xs text-slate-500">Updated {{ formatDate(article.updated_at) }} by {{ article.author?.name || 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-600">
                            {{ article.category?.name || 'Uncategorized' }}
                        </td>
                        <td class="px-6 py-4">
                            <span v-if="article.is_internal" class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-blue-100/50 text-blue-600 border border-blue-200">
                                Internal
                            </span>
                            <span v-else-if="article.is_published" class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-emerald-100/50 text-emerald-600 border border-emerald-200">
                                Published
                            </span>
                            <span v-else class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-amber-100/50 text-amber-600 border border-amber-200">
                                Draft
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-500 text-right">
                            {{ article.views }}
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
                                    <DropdownLink :href="route('public.kb.show', article.slug)" v-if="article.is_published && !article.is_internal">
                                        View Public
                                    </DropdownLink>
                                    <DropdownLink :href="route('staff.kb.articles.edit', article.id)">
                                        Edit
                                    </DropdownLink>
                                    <button @click="deleteArticle(article.id)" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-100">
                                        Delete
                                    </button>
                                </template>
                            </Dropdown>
                        </td>
                    </tr>
                    <tr v-if="articles.data.length === 0">
                        <td colspan="5" class="px-6 py-10 text-center text-slate-500 text-sm font-medium">
                            No articles found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-between items-center text-sm" v-if="articles.links && articles.data.length > 0">
            <div class="text-slate-500 font-medium tracking-tight">
                Showing <span class="font-bold text-slate-900">{{ articles.from }}</span> to <span class="font-bold text-slate-900">{{ articles.to }}</span> of <span class="font-bold text-slate-900">{{ articles.total }}</span> articles
            </div>
            <div class="flex gap-1.5 flex-wrap">
                <template v-for="(link, i) in articles.links" :key="i">
                    <div
                        v-if="link.url === null"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-300 font-bold text-xs"
                        v-html="link.label"
                    />
                    <Link
                        v-else
                        :href="link.url"
                        class="px-3 py-1.5 rounded-lg border text-xs font-bold transition-all duration-200"
                        :class="link.active 
                            ? 'bg-slate-900 border-slate-900 text-white shadow-md' 
                            : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </component>
</template>
