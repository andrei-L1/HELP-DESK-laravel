<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicKbNavigation from '@/Components/PublicKbNavigation.vue';

const props = defineProps({
    all_categories: Array,
    article: Object,
    relatedArticles: Array
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};
</script>

<template>
    <Head :title="article.title + ' - Knowledge Base'" />
    
    <PublicKbNavigation :all_categories="all_categories">
        <template #header-title>
            <div class="flex flex-col">
                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    Knowledge Base
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <Link :href="route('public.kb.index')" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Home</Link>
                    <span class="opacity-50 text-slate-300 text-[10px]">/</span>
                    <Link v-if="article.category" :href="route('public.kb.category', article.category.slug)" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">{{ article.category.name }}</Link>
                    <span class="opacity-50 text-slate-300 text-[10px]" v-if="article.category">/</span>
                    <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest truncate max-w-[200px]">{{ article.title }}</span>
                </div>
            </div>
        </template>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 md:p-12 mb-8 relative overflow-hidden">
            <div class="absolute inset-y-0 right-0 w-1/3 bg-gradient-to-l from-slate-50 to-transparent pointer-events-none"></div>
            
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 leading-tight tracking-tight relative z-10">{{ article.title }}</h1>
            
            <div class="mt-8 flex items-center gap-6 text-[10px] uppercase tracking-widest font-black text-slate-400 relative z-10">
                <span class="flex items-center gap-2">
                    <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 border border-slate-200">
                        {{ article.author?.name?.charAt(0) || 'A' }}
                    </div>
                    By {{ article.author?.name || 'Unknown' }}
                </span>
                <span class="opacity-30">&bull;</span>
                <span class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Updated {{ formatDate(article.updated_at) }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 md:p-12 mb-12">
            <!-- Article Content Box -->
            <article class="prose prose-slate max-w-none prose-headings:font-black prose-headings:tracking-tight prose-a:text-indigo-600 hover:prose-a:text-indigo-500 text-sm leading-relaxed">
                <!-- Render HTML content -->
                <div v-html="article.content.replace(/\n/g, '<br>')"></div>
            </article>

            <div class="mt-16 pt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <span>{{ article.views }} Views</span>
                </div>

                <!-- Helpful Buttons -->
                <div class="flex items-center gap-4">
                    <span class="text-xs font-black uppercase text-slate-400 tracking-widest">Was this helpful?</span>
                    <div class="flex gap-2">
                        <button class="bg-slate-50 hover:bg-slate-900 hover:text-white text-slate-600 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-sm">
                            👍 Yes
                        </button>
                        <button class="bg-slate-50 hover:bg-slate-900 hover:text-white text-slate-600 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-sm">
                            👎 No
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Articles -->
        <div v-if="relatedArticles && relatedArticles.length > 0">
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.15em] mb-6 px-4">Related Articles</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link 
                    v-for="related in relatedArticles" 
                    :key="related.id" 
                    :href="route('public.kb.show', related.slug)"
                    class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group lg:min-h-[140px]"
                >
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="h-4 w-4 flex-shrink-0 text-slate-300 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight group-hover:underline line-clamp-1">{{ related.title }}</h4>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-500 font-bold line-clamp-3 leading-relaxed">{{ related.excerpt || related.content.substring(0, 100) }}</p>
                    </div>
                </Link>
            </div>
        </div>

        <div class="mt-16 text-center">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest py-8">
                &copy; {{ new Date().getFullYear() }} Help Desk. All rights reserved.
            </p>
        </div>
    </PublicKbNavigation>
</template>
