<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicKbNavigation from '@/Components/PublicKbNavigation.vue';

const props = defineProps({
    all_categories: Array,
    category: Object,
    articles: Object
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};
</script>

<template>
    <Head :title="category.name + ' - Knowledge Base'" />
    
    <PublicKbNavigation :all_categories="all_categories">
        <template #header-title>
            <div class="flex flex-col">
                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    Knowledge Base
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <Link :href="route('public.kb.index')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Home</Link>
                    <span class="text-[10px] text-slate-300">/</span>
                    <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ category.name }}</span>
                </div>
            </div>
        </template>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 mb-8">
            <h1 class="text-3xl font-black text-slate-900 mb-4">{{ category.name }}</h1>
            <p class="text-sm font-bold text-slate-500 max-w-2xl">{{ category.description }}</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10">
            <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-8 border-b border-slate-100 pb-4">Articles ({{ articles.total }})</h2>

            <div v-if="articles.data.length > 0" class="space-y-6">
                <Link 
                    v-for="article in articles.data" 
                    :key="article.id" 
                    :href="route('public.kb.show', article.slug)"
                    class="block group p-6 border border-slate-100 rounded-2xl hover:border-slate-300 hover:shadow-md transition-all"
                >
                    <h3 class="text-lg font-black text-slate-900 group-hover:text-slate-800 group-hover:underline mb-2">{{ article.title }}</h3>
                    <p class="text-slate-500 text-sm line-clamp-2 leading-relaxed mb-4">{{ article.excerpt || article.content.replace(/<[^>]*>?/gm, '').substring(0, 150) + '...' }}</p>
                    
                    <div class="flex flex-wrap items-center gap-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg> {{ article.views }} views</span>
                        <span>&bull;</span>
                        <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ formatDate(article.updated_at) }}</span>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-20">
                <div class="bg-slate-50 h-16 w-16 mx-auto rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 class="text-sm font-black text-slate-900 uppercase">No articles yet</h3>
                <p class="mt-1 text-xs text-slate-500 font-bold">Check back later for new content in this category.</p>
            </div>

            <!-- Pagination -->
            <div class="mt-10 pt-6 border-t border-slate-100 flex justify-center" v-if="articles.links && articles.data.length > 0">
                <div class="flex gap-2 flex-wrap">
                    <template v-for="(link, i) in articles.links" :key="i">
                        <div
                            v-if="link.url === null"
                            class="px-3 py-1.5 rounded-lg text-slate-300 font-black text-xs uppercase"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            class="px-3 py-1.5 rounded-lg text-xs font-black uppercase transition-all duration-200"
                            :class="link.active 
                                ? 'bg-slate-900 text-white shadow-md shadow-slate-200' 
                                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'"
                            v-html="link.label"
                        />
                    </template>
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
