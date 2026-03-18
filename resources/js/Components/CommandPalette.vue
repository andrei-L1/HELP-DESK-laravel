<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const isOpen = ref(false);
const searchQuery = ref('');

const actions = [
    { id: 1, title: 'Create New Ticket', label: 'Tickets', route: 'admin.tickets.create', icon: 'M12 4v16m8-8H4' },
    { id: 2, title: 'Add New Staff', label: 'Users', route: 'admin.users.create', icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' },
    { id: 3, title: 'Network Security Audit', label: 'System', route: 'admin.dashboard', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { id: 4, title: 'Billing & Subscriptions', label: 'Management', route: 'admin.dashboard', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    { id: 5, title: 'System API Settings', label: 'Configuration', route: 'admin.settings.index', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
];

const filteredActions = computed(() => {
    if (!searchQuery.value) return actions;
    return actions.filter(a => 
        a.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
        a.label.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const handleKeyDown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        isOpen.value = !isOpen.value;
    }
    if (e.key === 'Escape') {
        isOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

const executeAction = (notif) => {
    isOpen.value = false;
    router.visit(route(notif.route));
};
</script>

<template>
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 backdrop-blur-0"
        enter-to-class="opacity-100 backdrop-blur-xl"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 backdrop-blur-xl"
        leave-to-class="opacity-0 backdrop-blur-0"
    >
        <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-start justify-center pt-[15vh] px-4 pointer-events-auto">
            <!-- Background Backdrop -->
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity" @click="isOpen = false"></div>

            <!-- Command Modal -->
            <div class="relative w-full max-w-2xl bg-white rounded-[2.5rem] shadow-[0_50px_100px_-20px_rgba(15,23,42,0.15)] ring-1 ring-slate-900/5 transition-all overflow-hidden flex flex-col">
                <!-- Search Bar -->
                <div class="px-8 pt-8 pb-6 border-b border-slate-50 flex items-center gap-6">
                    <div class="h-12 w-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-slate-200 shrink-0">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search for actions, people, or system settings..." 
                        class="block w-full border-none bg-transparent p-0 text-xl font-bold placeholder:text-slate-300 focus:ring-0"
                        autofocus
                    />
                    <div class="px-3 py-1.5 rounded-xl bg-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest border border-slate-200">ESC</div>
                </div>

                <!-- Results -->
                <div class="flex-1 overflow-y-auto max-h-[400px] p-6 custom-scrollbar">
                    <div class="space-y-4">
                        <template v-if="filteredActions.length > 0">
                            <div v-for="action in filteredActions" :key="action.id" 
                                @click="executeAction(action)"
                                class="group flex items-center justify-between p-5 rounded-3xl hover:bg-slate-50 transition-all cursor-pointer border border-transparent hover:border-slate-100">
                                <div class="flex items-center gap-6">
                                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white group-hover:scale-105 transition-all duration-300">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon" /></svg>
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-black text-slate-900 group-hover:text-slate-700 transition-colors uppercase tracking-tight">{{ action.title }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest group-hover:text-slate-400">{{ action.label }}</span>
                                            <span class="h-1 w-1 rounded-full bg-slate-100"></span>
                                            <span class="text-[10px] font-bold text-slate-300 italic group-hover:text-slate-400 transition-colors shrink-0">Go to section</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="h-8 w-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-300 opacity-0 group-hover:opacity-100 group-hover:scale-110 transition-all">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                                </div>
                            </div>
                        </template>

                        <!-- No Results -->
                        <div v-else class="py-20 flex flex-col items-center">
                            <div class="h-20 w-20 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200 mb-6">
                                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <h4 class="text-xl font-black text-slate-900 tracking-tight">No results matched your search</h4>
                            <span class="text-[10px] font-black text-slate-400 mt-2 uppercase tracking-widest px-4 text-center">Try looking for "Ticket", "Staff", or "Audits"</span>
                        </div>
                    </div>
                </div>

                <!-- Command Help Footer -->
                <div class="px-8 py-5 border-t border-slate-50 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-2">
                             <div class="px-2 py-1 rounded bg-white text-[9px] font-black text-slate-400 uppercase border border-slate-200">ENTER</div>
                             <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">to select</span>
                        </div>
                        <div class="flex items-center gap-2">
                             <div class="px-2 py-1 rounded bg-white text-[9px] font-black text-slate-400 uppercase border border-slate-200">↑↓</div>
                             <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">to navigate</span>
                        </div>
                    </div>
                    <div class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic flex items-center gap-2">
                         <div class="h-1.5 w-1.5 rounded-full bg-slate-300 animate-pulse"></div>
                         Ready for input
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #CBD5E1; }
</style>
