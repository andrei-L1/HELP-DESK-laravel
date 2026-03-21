<script setup>
import { computed } from 'vue';
import { usePage, useForm, Link } from '@inertiajs/vue3';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import AgentNavigation from '@/Components/AgentNavigation.vue';
import ManagerNavigation from '@/Components/ManagerNavigation.vue';

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

const form = useForm({
    title: '',
    category_id: '',
    content: '',
    excerpt: '',
    is_published: false,
    is_internal: false
});

const submit = () => {
    form.post(route('staff.kb.articles.store'));
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
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Create Article</h1>
                </div>
            </div>
        </template>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl">
            <div class="p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Title</label>
                            <input v-model="form.title" type="text" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-colors" placeholder="e.g. How to reset your password" required />
                            <div v-if="form.errors.title" class="text-rose-500 text-xs font-bold mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Category</label>
                            <select v-model="form.category_id" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-colors" required>
                                <option value="" disabled>Select a category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.category_id" class="text-rose-500 text-xs font-bold mt-1">{{ form.errors.category_id }}</div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Excerpt</label>
                        <textarea v-model="form.excerpt" rows="2" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-colors" placeholder="Brief summary of the article..."></textarea>
                        <div v-if="form.errors.excerpt" class="text-rose-500 text-xs font-bold mt-1">{{ form.errors.excerpt }}</div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Content</label>
                        <textarea v-model="form.content" rows="12" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-colors font-mono text-sm" placeholder="Write article content here (Markdown or HTML supported)..." required></textarea>
                        <div v-if="form.errors.content" class="text-rose-500 text-xs font-bold mt-1">{{ form.errors.content }}</div>
                    </div>

                    <div class="flex items-center gap-6 p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" v-model="form.is_published" class="peer sr-only" />
                                <div class="h-6 w-10 bg-slate-300 rounded-full peer-checked:bg-emerald-500 transition-colors duration-300"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-4 shadow-sm"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition-colors">Published</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" v-model="form.is_internal" class="peer sr-only" />
                                <div class="h-6 w-10 bg-slate-300 rounded-full peer-checked:bg-blue-500 transition-colors duration-300"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-4 shadow-sm"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-700 group-hover:text-blue-900 transition-colors">Internal Only (Staff)</span>
                        </label>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="form.processing" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 w-full sm:w-auto rounded-xl font-black text-sm transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                            Submit Article
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </component>
</template>
