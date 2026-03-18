<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
});

const form = useForm({
    first_name: props.user.first_name ?? '',
    last_name: props.user.last_name ?? '',
    middle_name: props.user.middle_name ?? '',
    display_name: props.user.display_name ?? '',
    username: props.user.username ?? '',
    email: props.user.email ?? '',
    role_id: props.user.role_id ?? '',
    is_active: props.user.is_active ?? true,
});

const submit = () => form.put(route('admin.users.update', props.user.id));
</script>

<template>
    <Head :title="`Edit User: ${user.display_name || user.username}`" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">User Management</span>
            </div>
        </template>

        <template #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <Link :href="route('admin.users.index')" class="hover:text-slate-700 cursor-pointer">User Management</Link>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900 uppercase tracking-tighter">Modify Personnel Identity</span>
            </div>
        </template>

        <div class="min-h-screen bg-slate-50/50 px-6 py-12 pb-24 text-slate-900 font-sans">
            <div class="max-w-4xl mx-auto space-y-10">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-2 border-b border-slate-200">
                    <div class="space-y-4">
                        <Link :href="route('admin.users.index')" class="flex items-center gap-2.5 text-[11px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest transition-colors leading-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg>
                            Back to User Management
                        </Link>
                        <div>
                            <h1 class="text-4xl font-bold text-slate-900 tracking-tight">Edit User</h1>
                            <p class="text-slate-500 text-sm mt-1 font-medium">Update the profile and access settings for {{ user.display_name || user.username }}.</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-10">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Personnel Profile Details</h2>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-slate-900">
                                <div class="space-y-1.5">
                                    <label for="first_name" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">First Name</label>
                                    <input id="first_name" v-model="form.first_name" type="text" required class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-500 outline-none shadow-inner" />
                                    <p v-if="form.errors.first_name" class="text-[10px] text-rose-500 font-bold mt-1.5 ml-1">{{ form.errors.first_name }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label for="last_name" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Last Name</label>
                                    <input id="last_name" v-model="form.last_name" type="text" required class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-500 outline-none shadow-inner" />
                                    <p v-if="form.errors.last_name" class="text-[10px] text-rose-500 font-bold mt-1.5 ml-1">{{ form.errors.last_name }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-slate-900 pt-8 border-t border-slate-50">
                                <div class="space-y-1.5">
                                    <label for="middle_name" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Middle Name</label>
                                    <input id="middle_name" v-model="form.middle_name" type="text" class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white outline-none shadow-inner" />
                                </div>
                                <div class="space-y-1.5">
                                    <label for="display_name" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Display Name</label>
                                    <input id="display_name" v-model="form.display_name" type="text" required class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white outline-none shadow-inner" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Configuration -->
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
                        <div class="px-8 py-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Access Configuration</h2>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <div class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-slate-900">
                                <div class="space-y-1.5 opacity-60">
                                    <label for="username" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Username (Locked)</label>
                                    <input id="username" v-model="form.username" type="text" disabled class="w-full bg-slate-100 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-black text-slate-400 cursor-not-allowed outline-none shadow-inner" />
                                </div>
                                <div class="space-y-1.5">
                                    <label for="email" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                                    <input id="email" v-model="form.email" type="email" required class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white outline-none shadow-inner" />
                                    <p v-if="form.errors.email" class="text-[10px] text-rose-500 font-bold mt-1.5 ml-1">{{ form.errors.email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Status -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                            <label for="role_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 block">Access Privileges</label>
                            <select id="role_id" v-model="form.role_id" required class="w-full bg-slate-50 border-slate-200 border rounded-xl px-4 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-500 transition-all outline-none cursor-pointer shadow-inner">
                                <option value="">Select a role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                            </select>
                            <p v-if="form.errors.role_id" class="text-[10px] text-rose-500 font-bold mt-1.5 ml-1">{{ form.errors.role_id }}</p>
                        </div>

                        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div class="space-y-1.5">
                                <p class="text-[15px] font-bold text-slate-800">System Authorization</p>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Enable identity for immediate system synchronization</p>
                            </div>
                            <label class="group flex items-center gap-4 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="sr-only peer">
                                <div class="w-12 h-6.5 bg-slate-200 rounded-full peer-checked:bg-emerald-600 after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:rounded-full after:h-5.5 after:w-5.5 after:transition-all peer-checked:after:translate-x-5.5 relative shadow-inner transition-colors"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-6 pt-4">
                        <Link :href="route('admin.users.index')" class="text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors">Cancel</Link>
                        <button type="submit" class="px-12 py-4 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-[0.1em] shadow-xl hover:bg-indigo-600 transition-all active:scale-95 flex items-center gap-4 disabled:opacity-50" :disabled="form.processing">
                            <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminNavigation>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
.min-h-screen { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; }
input:disabled { background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0,0,0,0.02) 10px, rgba(0,0,0,0.02) 20px); }
</style>
