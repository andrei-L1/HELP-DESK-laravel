<script setup>
import { ref } from 'vue';
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    department: {
        type: Object,
        required: true,
    },
    assignedUsers: {
        type: Array,
        default: () => [],
    },
    availableUsers: {
        type: Array,
        default: () => [],
    },
});

// Assign users modal
const showAssignModal = ref(false);
const assignForm = useForm({
    user_ids: [],
    is_primary: false,
});

const openAssignModal = () => {
    assignForm.reset();
    assignForm.user_ids = [];
    showAssignModal.value = true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    assignForm.reset();
};

const submitAssign = () => {
    assignForm.post(route('admin.departments.assign-users', props.department.id), {
        preserveScroll: true,
        onSuccess: () => closeAssignModal(),
    });
};

const removeUser = (userId) => {
    if (confirm('Are you sure you want to remove this user from the department?')) {
        router.delete(route('admin.departments.remove-user', [props.department.id, userId]), {
            preserveScroll: true,
        });
    }
};

const setPrimary = (userId) => {
    router.post(route('admin.departments.set-primary', [props.department.id, userId]), {
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusBadgeStyle = (isActive) => {
    return isActive 
        ? { backgroundColor: 'rgba(16, 185, 129, 0.1)', color: '#059669', borderColor: 'rgba(16, 185, 129, 0.2)' }
        : { backgroundColor: 'rgba(244, 63, 94, 0.1)', color: '#e11d48', borderColor: 'rgba(244, 63, 94, 0.2)' };
};

const getDeptIconColor = (name) => {
    const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    const colors = ['bg-slate-900', 'bg-indigo-600', 'bg-blue-600', 'bg-emerald-600', 'bg-amber-600', 'bg-rose-600', 'bg-violet-600'];
    return colors[hash % colors.length];
};
</script>

<template>
    <Head :title="`Department: ${department.name}`" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-4">
                <span class="text-xl font-bold text-slate-900 tracking-tight">{{ department.name }}</span>
                <span 
                    class="text-[9px] font-black uppercase tracking-tight px-2 py-0.5 rounded-md border"
                    :style="getStatusBadgeStyle(department.is_active)"
                >
                    {{ department.is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </template>

        <template #breadcrumbs>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <Link :href="route('admin.departments.index')" class="hover:text-slate-700 cursor-pointer uppercase">Departments</Link>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900 uppercase">Details</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto pb-20 pt-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                <!-- Left Column: User Table -->
                <div class="lg:col-span-3 space-y-8 px-4">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 stagger-1">
                        <div class="space-y-1">
                            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Department Users</h2>
                            <p class="text-slate-500 font-medium italic">List of users currently assigned to this department.</p>
                        </div>
                        <button
                            @click="openAssignModal"
                            :disabled="availableUsers.length === 0"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95 disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            Assign Users
                        </button>
                    </div>

                    <div class="bg-white rounded-[2rem] border border-slate-300/40 shadow-xl shadow-slate-200/40 overflow-hidden stagger-2">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">User</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Contact</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Primary Dept</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="user in assignedUsers" :key="user.id" class="group hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs font-black shadow-sm group-hover:scale-110 transition-transform">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-900 tracking-tight">{{ user.name }}</p>
                                                <p class="text-[10px] font-bold text-slate-400">{{ user.username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-[11px] font-bold text-slate-600">{{ user.email }}</p>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Joined {{ formatDate(user.joined_at) }}</p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div v-if="user.is_primary" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            Primary
                                        </div>
                                        <button
                                            v-else
                                            @click="setPrimary(user.id)"
                                            class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 border-b border-transparent hover:border-slate-900 transition-all pb-0.5"
                                        >
                                            Set as Primary
                                        </button>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <button
                                            @click="removeUser(user.id)"
                                            class="p-2.5 rounded-xl text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all"
                                            title="Remove from department"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="assignedUsers.length === 0">
                                    <td colspan="4" class="px-8 py-20 text-center">
                                        <div class="h-16 w-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-100 italic font-black text-slate-200">!</div>
                                        <p class="text-sm font-black text-slate-900">No users found</p>
                                        <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-tighter">No team members are assigned to this department yet.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column: Sidebar (Department Details) -->
                <div class="space-y-8 px-4 lg:px-0">
                    <div class="bg-white rounded-[2rem] border border-slate-300/40 shadow-xl shadow-slate-200/40 overflow-hidden stagger-3">
                        <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                            <h3 class="text-[11px] font-black text-slate-500 uppercase tracking-[0.2em]">Department Info</h3>
                            <div class="h-6 w-6 rounded-lg shadow-inner bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400 italic">i</div>
                        </div>
                        <div class="p-8 space-y-8">
                            <div class="space-y-2">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Name</span>
                                <h4 class="text-xl font-black text-slate-900 tracking-tight leading-tight">{{ department.name }}</h4>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none block mb-1">Code</span>
                                    <span class="text-xs font-black text-slate-900">{{ department.short_code || '—' }}</span>
                                </div>
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none block mb-1">Status</span>
                                    <span 
                                        class="text-[10px] font-bold uppercase tracking-tight"
                                        :style="{ color: department.is_active ? '#059669' : '#e11d48' }"
                                    >
                                        {{ department.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <section>
                                <h5 class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-2">Description</h5>
                                <p class="text-xs font-semibold text-slate-600 leading-relaxed italic">
                                    {{ department.description || 'No description provided for this department.' }}
                                </p>
                            </section>

                            <div class="pt-6 border-t border-slate-50 space-y-4">
                                <div v-for="item in [
                                    { label: 'Manager', value: department.manager_name || 'Not assigned', icon: 'user' },
                                    { label: 'Last Updated', value: formatDate(department.updated_at), icon: 'clock' },
                                    { label: 'Created On', value: formatDate(department.created_at), icon: 'calendar' }
                                ]" :key="item.label" class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ item.label }}</span>
                                    <span class="text-[11px] font-bold text-slate-900 truncate">{{ item.value }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Module -->
                    <div class="bg-slate-900 rounded-[2rem] p-8 shadow-2xl shadow-slate-900/40 stagger-4">
                        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Quick Stats</h3>
                        <div class="space-y-8">
                            <div v-for="stat in [
                                { label: 'Total Users', value: assignedUsers.length, total: 100 },
                                { label: 'Open Tickets', value: department.tickets_count || 0, total: 50 },
                            ]" :key="stat.label">
                                <div class="flex items-center justify-between mb-2 px-1">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ stat.label }}</span>
                                    <span class="text-xs font-black text-white">{{ stat.value }}</span>
                                </div>
                                <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-white rounded-full transition-all duration-1000 opacity-20" :style="{ width: '50%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
                    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100">
                        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Assign Users</h3>
                                <p class="text-xs font-bold text-slate-400 mt-0.5">Select users to add to the {{ department.name }} department.</p>
                            </div>
                            <button @click="closeAssignModal" class="p-2.5 rounded-2xl hover:bg-slate-50 text-slate-400 transition-colors border border-slate-100">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitAssign" class="p-10 space-y-8">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between px-2">
                                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none">Available Users</label>
                                    <span class="text-[10px] font-bold text-slate-400 capitalize">{{ availableUsers.length }} Users Found</span>
                                </div>
                                <div class="grid grid-cols-1 gap-3 p-4 bg-slate-50/50 rounded-2xl border border-slate-100 max-h-60 overflow-y-auto custom-scrollbar">
                                    <label v-for="user in availableUsers" :key="user.id" class="relative group flex items-center p-4 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:shadow-md hover:border-slate-300/50 cursor-pointer">
                                        <div class="relative flex items-center justify-center">
                                            <input 
                                                v-model="assignForm.user_ids" 
                                                :value="user.id"
                                                type="checkbox" 
                                                class="h-6 w-6 rounded-lg border-slate-200 text-slate-900 focus:ring-slate-900/20 transition-all" 
                                            />
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <p class="text-sm font-black text-slate-800 tracking-tight group-hover:text-slate-900 transition-colors">{{ user.name }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 truncate max-w-[250px]">{{ user.email }}</p>
                                        </div>
                                    </label>
                                    <div v-if="availableUsers.length === 0" class="py-8 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                                        <p class="text-xs font-black text-slate-300 uppercase tracking-widest">No users available</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center px-2 group cursor-pointer" @click="assignForm.is_primary = !assignForm.is_primary">
                                <div 
                                    class="h-6 w-11 rounded-full p-1 transition-colors duration-200"
                                    :class="assignForm.is_primary ? 'bg-indigo-600' : 'bg-slate-200'"
                                >
                                    <div 
                                        class="h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"
                                        :class="assignForm.is_primary ? 'translate-x-5' : 'translate-x-0'"
                                    ></div>
                                </div>
                                <div class="ml-4">
                                    <span class="text-xs font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors block leading-none">Set as Primary Department</span>
                                    <p class="text-[9px] font-bold text-slate-400 mt-1">This will be the main department for these users.</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-slate-50">
                                <button type="button" @click="closeAssignModal" class="flex-1 px-8 py-4 rounded-2xl bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                                <button 
                                    type="submit" 
                                    :disabled="assignForm.processing || assignForm.user_ids.length === 0" 
                                    class="flex-[2] px-8 py-4 rounded-2xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50"
                                >
                                    {{ assignForm.processing ? 'Assigning...' : 'Assign Selected' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminNavigation>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

.stagger-1 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.stagger-2 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; opacity: 0; }
.stagger-3 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }
.stagger-4 { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards; opacity: 0; }

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>