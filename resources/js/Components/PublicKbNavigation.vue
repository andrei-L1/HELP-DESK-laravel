<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    all_categories: Array
});

const page = usePage();
const showingMobileMenu = ref(false);

const handleEscapeKey = (e) => {
    if (e.key === 'Escape' && showingMobileMenu.value) {
        showingMobileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscapeKey);
});

watch(() => page.url, () => {
    showingMobileMenu.value = false;
});

const isCategoryActive = (slug) => route().current('public.kb.category', { slug });
</script>

<template>
    <div class="flex h-screen bg-[#F8FAFC]">
        <!-- Desktop Sidebar -->
        <aside class="hidden w-72 flex-col bg-white lg:flex relative z-20 transition-all duration-300 border-r border-slate-200/60">
            <!-- Logo area -->
            <div class="flex h-20 items-center px-8 mb-4">
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 shadow-lg shadow-slate-200 transition-all duration-300 group-hover:scale-105 group-hover:bg-slate-800">
                        <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black tracking-tight text-slate-900 uppercase">Knowledge Base</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest -mt-1">Help Center</span>
                    </div>
                </Link>
            </div>

            <!-- Enhanced Search Bar -->
            <div class="px-6 mb-6">
                <form @submit.prevent="$inertia.get(route('public.kb.index', { search: $event.target.search.value }))" class="relative group">
                    <input
                        name="search"
                        type="text"
                        :defaultValue="$page.props.filters?.search || ''"
                        placeholder="Search... (Enter)"
                        class="w-full rounded-xl border-none bg-slate-100/80 px-4 py-2.5 pl-10 text-sm placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all duration-300"
                    />
                    <svg
                        class="absolute left-3.5 top-3 h-4 w-4 text-slate-400 transition-colors duration-300 group-focus-within:text-slate-900"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </form>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 pb-6 custom-scrollbar">
                <div class="space-y-6">
                    <div>
                        <h3 class="px-4 text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] mb-3">Menu</h3>
                        <ul class="space-y-1">
                            <li>
                                <Link
                                    :href="route('public.kb.index')"
                                    class="group relative flex items-center rounded-xl transition-all duration-300 overflow-hidden"
                                    :class="[
                                        route().current('public.kb.index')
                                            ? 'bg-slate-900 text-white shadow-xl shadow-slate-200 px-4 py-3'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 px-4 py-3',
                                    ]"
                                >
                                    <svg class="h-5 w-5 mr-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span class="font-bold tracking-tight text-[13px] uppercase">Home</span>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="px-4 text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] mb-3">Categories</h3>
                        <ul class="space-y-1" v-if="all_categories?.length > 0">
                            <li v-for="cat in all_categories" :key="cat.id">
                                <Link
                                    :href="route('public.kb.category', cat.slug)"
                                    class="group relative flex items-center rounded-xl transition-all duration-300 overflow-hidden"
                                    :class="[
                                        isCategoryActive(cat.slug)
                                            ? 'bg-slate-900 text-white shadow-xl shadow-slate-200 px-4 py-3'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 px-4 py-3',
                                    ]"
                                >
                                    <span v-if="cat.icon" v-html="cat.icon" class="h-5 w-5 mr-3.5 flex-shrink-0 flex items-center justify-center text-lg"></span>
                                    <svg v-else class="h-5 w-5 mr-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    <span class="font-bold tracking-tight text-[13px] line-clamp-1">{{ cat.name }}</span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main content area -->
        <div class="flex flex-1 flex-col overflow-hidden relative">
            <header class="h-20 bg-white/70 backdrop-blur-md border-b border-slate-200/50 sticky top-0 z-10 transition-all duration-300">
                <div class="flex h-full items-center justify-between px-8">
                    <div class="flex items-center gap-4 lg:gap-0">
                        <button @click="showingMobileMenu = !showingMobileMenu" class="rounded-xl p-2.5 text-slate-600 hover:bg-slate-100 lg:hidden">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showingMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <slot name="header-title">
                            <div class="flex flex-col">
                                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                                    Knowledge Base
                                </h1>
                            </div>
                        </slot>
                    </div>

                    <div class="flex items-center gap-4 text-sm font-bold">
                        <Link href="/" class="text-slate-500 hover:text-slate-900 transition-colors hidden sm:inline-block">Back to Home</Link>
                        <template v-if="$page.props.auth.user">
                            <Link :href="route('user.dashboard')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition-colors shadow-none hover:shadow-sm">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="bg-slate-900 text-white px-4 py-2 rounded-xl hover:bg-slate-800 transition-colors shadow-none hover:shadow-sm">Sign In</Link>
                        </template>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto custom-scrollbar p-6 lg:p-10 bg-slate-100/30">
                <div class="mx-auto max-w-[1200px] animate-in fade-in slide-in-from-bottom-4 duration-700 ease-out">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Mobile Menu Overlay -->
        <transition enter-active-class="transition-opacity duration-400 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-show="showingMobileMenu" class="fixed inset-0 z-[60] lg:hidden">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-md" @click="showingMobileMenu = false" />
                <aside class="fixed inset-y-0 left-0 w-[280px] bg-white shadow-2xl transform transition-transform duration-500 ease-out" :class="showingMobileMenu ? 'translate-x-0' : '-translate-x-full'">
                    <div class="flex h-20 items-center px-6 border-b border-slate-100">
                         <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white font-black shadow-lg shadow-slate-200">KB</div>
                         <span class="ml-3 text-lg font-black tracking-tight text-slate-900 uppercase">Knowledge Base</span>
                    </div>
                    <nav class="p-4 overflow-y-auto custom-scrollbar">
                        <div class="mb-4">
                            <Link :href="route('public.kb.index')" class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-sm font-bold" :class="route().current('public.kb.index') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50'" @click="showingMobileMenu = false">
                                Home
                            </Link>
                        </div>
                        <div v-for="cat in all_categories" :key="cat.id" class="mb-2">
                            <Link :href="route('public.kb.category', cat.slug)" class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-sm font-bold" :class="isCategoryActive(cat.slug) ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50'" @click="showingMobileMenu = false">
                                {{ cat.name }}
                            </Link>
                        </div>
                    </nav>
                </aside>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #E2E8F0; border-radius: 20px; }
@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes slide-in-from-bottom { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.animate-in { animation-fill-mode: both; }
.fade-in { animation-name: fade-in; }
.slide-in-from-bottom-4 { animation-name: slide-in-from-bottom; }
</style>
