<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicKbNavigation from '@/Components/PublicKbNavigation.vue';

const props = defineProps({
    all_categories: Array,
    categories: Array,
    articles: Object, 
    filters: Object
});
</script>

<template>
    <Head title="Knowledge Base" />
    
    <PublicKbNavigation :all_categories="categories">
        <template #header-title>
            <div class="flex flex-col">
                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    Knowledge Base
                </h1>
            </div>
        </template>

        <!-- Search Results Display -->
        <div v-if="filters?.search">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-900">Search Results for "{{ filters.search }}"</h2>
                <Link :href="route('public.kb.index')" class="text-sm font-bold text-slate-500 hover:text-slate-900">Clear Search</Link>
            </div>

            <div v-if="articles.data.length > 0" class="space-y-4">
                <Link 
                    v-for="article in articles.data" 
                    :key="article.id" 
                    :href="route('public.kb.show', article.slug)"
                    class="block bg-white p-6 rounded-2xl border border-slate-200 hover:border-slate-300 shadow-sm hover:shadow-md transition-all group"
                >
                    <h3 class="text-lg font-black text-slate-900 mb-2 group-hover:underline">{{ article.title }}</h3>
                    <p class="text-slate-600 line-clamp-2 text-sm">{{ article.excerpt || article.content.replace(/<[^>]*>?/gm, '').substring(0, 150) }}...</p>
                    <div class="mt-4 flex items-center gap-4 text-xs font-bold text-slate-400 uppercase tracking-widest">
                        <span>{{ article.category?.name || 'Uncategorized' }}</span>
                        <span>&bull;</span>
                        <span>{{ article.views || 0 }} views</span>
                    </div>
                </Link>
            </div>
            <div v-else class="text-center py-20 bg-white rounded-2xl border border-slate-200 shadow-sm">
                <svg class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <h3 class="text-lg font-black text-slate-900">No results found</h3>
                <p class="mt-1 text-slate-500">We couldn't find any articles matching your search query.</p>
            </div>
        </div>

        <!-- Default View -->
        <div v-else>
            <!-- Welcome Header resembling Admin Dashboards -->
            <div class="mb-10 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex flex-col items-start justify-center overflow-hidden relative">
                <div class="absolute inset-y-0 right-0 w-1/3 bg-gradient-to-l from-slate-50 to-transparent pointer-events-none"></div>
                <h2 class="text-3xl font-black text-slate-900 relative z-10">How can we help?</h2>
                <p class="text-slate-500 mt-2 relative z-10 max-w-xl">Browse our documentation or use the search bar in the sidebar to find the answers you need.</p>
            </div>

            <!-- Categories Grid -->
            <div class="mb-16">
                <h2 class="text-xl font-black text-slate-900 mb-6">Browse by Category</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link 
                        v-for="category in categories" 
                        :key="category.id" 
                        :href="route('public.kb.category', category.slug)"
                        class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all group"
                    >
                        <div class="flex flex-col gap-4">
                            <div class="bg-slate-100 text-slate-600 p-3 h-12 w-12 flex items-center justify-center rounded-xl group-hover:bg-slate-900 group-hover:text-white transition-colors">
                                <span v-if="category.icon" v-html="category.icon" class="text-xl"></span>
                                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 group-hover:text-slate-700 transition-colors">{{ category.name }}</h3>
                                <p class="mt-1 text-sm text-slate-500 line-clamp-2">{{ category.description }}</p>
                                <p class="mt-4 text-xs font-bold text-slate-400 uppercase tracking-widest">{{ category.articles_count || 0 }} articles</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Popular Articles -->
            <div>
                <h2 class="text-xl font-black text-slate-900 mb-6">Popular Articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link 
                        v-for="article in articles" 
                        :key="article.id"
                        :href="route('public.kb.show', article.slug)"
                        class="flex flex-col justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group h-full"
                    >
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">{{ article.category?.name || 'Article' }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 group-hover:underline">{{ article.title }}</h3>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
        
        <div class="mt-16 text-center">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest py-8">
                &copy; {{ new Date().getFullYear() }} Help Desk. All rights reserved.
            </p>
        </div>
    </PublicKbNavigation>
</template>
