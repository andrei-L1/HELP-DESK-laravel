<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    subject: '',
    description: '',
    priority: 'medium',
    category_id: '',
    department_id: '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        category_id: data.category_id || null,
        department_id: data.department_id || null,
    })).post(route('admin.tickets.store'));
};
</script>

<template>
    <Head title="Create Ticket" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">Tickets</span>
            </div>
        </template>

        <template #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <Link :href="route('admin.tickets.index')" class="hover:text-slate-700 cursor-pointer">Tickets</Link>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900 uppercase tracking-widest">Create Ticket</span>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-10 pb-20 pt-8 px-4">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-2 border-b border-slate-200 stagger-1">
                <div class="space-y-4">
                    <Link :href="route('admin.tickets.all')" class="flex items-center gap-2.5 text-[11px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest transition-colors leading-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg>
                        Back to All Tickets
                    </Link>
                    <div>
                        <h1 class="text-4xl font-bold text-slate-900 tracking-tight">Create Ticket</h1>
                        <p class="text-slate-500 text-sm mt-1 font-medium">Fill in the details below to initialize a new support ticket.</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-10">
                <!-- Ticket Content Section -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden stagger-2">
                    <div class="px-8 py-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Ticket Information</h2>
                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </div>
                    <div class="p-8 space-y-8">
                        <div class="space-y-2">
                            <label for="subject" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Subject</label>
                            <input id="subject" v-model="form.subject" type="text" required maxlength="200" placeholder="Brief subject or title"
                                class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-slate-900 transition-all outline-none shadow-inner" />
                            <p v-if="form.errors.subject" class="text-[10px] text-rose-500 font-bold mt-1.5 ml-1 italic">{{ form.errors.subject }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="description" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Description</label>
                            <textarea id="description" v-model="form.description" rows="6" required placeholder="Describe the issue or request in detail..."
                                class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-slate-900 transition-all outline-none shadow-inner resize-none lg:text-base" />
                            <p v-if="form.errors.description" class="text-[10px] text-rose-500 font-bold mt-1.5 ml-1 italic">{{ form.errors.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Classification Details Section -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden stagger-3 text-slate-900">
                    <div class="px-8 py-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Classification Details</h2>
                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 11h.01M7 15h.01M11 7h.01M11 11h.01M11 15h.01M15 7h.01M15 11h.01M15 15h.01" /></svg>
                    </div>
                    <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8 text-slate-900">
                        <div class="space-y-2">
                            <label for="priority" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Priority</label>
                            <select id="priority" v-model="form.priority" required 
                                class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-slate-900 transition-all outline-none cursor-pointer shadow-inner appearance-none custom-select">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div v-if="categories.length" class="space-y-2 text-slate-900">
                            <label for="category_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Category</label>
                            <select id="category_id" v-model="form.category_id" 
                                class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-slate-900 transition-all outline-none cursor-pointer shadow-inner appearance-none custom-select">
                                <option value="">None</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </select>
                        </div>
                        <div v-if="departments.length" class="space-y-2 text-slate-900">
                            <label for="department_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Department</label>
                            <select id="department_id" v-model="form.department_id" 
                                class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-slate-900 transition-all outline-none cursor-pointer shadow-inner appearance-none custom-select">
                                <option value="">None</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-6 pt-4 stagger-4">
                    <Link :href="route('admin.tickets.all')" class="text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors">Discard</Link>
                    <button type="submit" class="px-12 py-4 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-[0.1em] shadow-xl hover:bg-slate-800 transition-all active:scale-95 flex items-center gap-4 disabled:opacity-50" :disabled="form.processing">
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ form.processing ? 'Syncing...' : 'Create Ticket' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminNavigation>
</template>

<style scoped>
.custom-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>