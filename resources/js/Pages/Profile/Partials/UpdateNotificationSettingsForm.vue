<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { BellIcon, EnvelopeIcon, BoltIcon } from '@heroicons/vue/24/outline';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const user = usePage().props.auth.user;

const defaultSettings = {
    new_message: { mail: true, database: true, broadcast: true },
    ticket_created: { mail: true, database: true, broadcast: true },
    ticket_assigned: { mail: true, database: true, broadcast: true },
    status_changed: { mail: true, database: true, broadcast: true },
    sla_warning: { mail: true, database: true, broadcast: true },
    sla_breach: { mail: true, database: true, broadcast: true },
};

const form = useForm({
    notification_settings: {
        ...defaultSettings,
        ...(user.notification_settings || {}),
    },
});

const updateSettings = () => {
    form.patch(route('profile.notifications.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: show local success state
        },
    });
};

const settingTypes = [
    { key: 'new_message', label: 'New Messages', desc: 'Alerts for any new replies on your tickets.' },
    { key: 'ticket_created', label: 'Ticket Creation', desc: 'When a new ticket is logged in the system.' },
    { key: 'ticket_assigned', label: 'Assignments', desc: 'When you are assigned to a new support case.' },
    { key: 'status_changed', label: 'Status Updates', desc: 'When a ticket status moves to Pending, Solved, etc.' },
    { key: 'sla_warning', label: 'SLA Warning', desc: 'Get notified when a ticket reaches its resolution limit.' },
    { key: 'sla_breach', label: 'SLA Breach', desc: 'Urgent alerts when a ticket exceeds its resolution deadline.' },
];

const channels = [
    { key: 'mail', label: 'Email', icon: EnvelopeIcon, color: 'text-rose-500 bg-rose-50' },
    { key: 'database', label: 'In-App (Bell)', icon: BellIcon, color: 'text-indigo-500 bg-indigo-50' },
    { key: 'broadcast', label: 'Real-time (Toasts)', icon: BoltIcon, color: 'text-amber-500 bg-amber-50' },
];
</script>

<template>
    <section class="p-8">
        <header class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="h-10 w-10 rounded-xl bg-slate-900 shadow-lg flex items-center justify-center text-white">
                    <BoltIcon class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none">Notification Center</h2>
                    <p class="mt-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Delivery Preferences</p>
                </div>
            </div>
        </header>

        <form @submit.prevent="updateSettings" class="space-y-4">
            <div class="bg-slate-50/50 rounded-2xl border border-slate-100 overflow-hidden">
                <!-- Header Labels -->
                <div class="grid grid-cols-1 md:grid-cols-[1fr,auto] items-center p-4 border-b border-slate-100 bg-white">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Notification Event</span>
                    <div class="hidden md:flex items-center gap-8 pr-2">
                        <span v-for="channel in channels" :key="channel.key" class="w-7 text-center text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ channel.label }}</span>
                    </div>
                </div>

                <!-- Settings Rows -->
                <div v-for="(type, index) in settingTypes" :key="type.key" 
                    :class="['grid grid-cols-1 md:grid-cols-[1fr,auto] items-center p-4 transition-all hover:bg-white', index !== settingTypes.length - 1 ? 'border-b border-slate-50' : '']"
                >
                    <div class="flex flex-col gap-0.5 ml-2">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ type.label }}</h4>
                        <p class="text-[10px] font-medium text-slate-400 italic leading-tight">{{ type.desc }}</p>
                    </div>

                    <div class="flex items-center justify-between md:justify-end gap-8 mt-4 md:mt-0 pr-2">
                        <div v-for="channel in channels" :key="channel.key" class="flex flex-col items-center">
                            <label class="relative inline-flex items-center cursor-pointer group/toggle">
                                <input 
                                    type="checkbox" 
                                    :checked="form.notification_settings[type.key]?.[channel.key] ?? true"
                                    @change="(e) => {
                                        if (!form.notification_settings[type.key]) {
                                            form.notification_settings[type.key] = { mail: true, database: true, broadcast: true };
                                        }
                                        form.notification_settings[type.key][channel.key] = e.target.checked;
                                    }"
                                    class="sr-only peer"
                                >
                                <div :class="[
                                    'h-7 w-7 rounded-lg flex items-center justify-center border transition-all duration-200',
                                    form.notification_settings[type.key]?.[channel.key] 
                                        ? 'bg-slate-900 border-slate-900 text-white shadow-md' 
                                        : 'bg-white border-slate-200 text-slate-300 group-hover/toggle:border-slate-400'
                                ]">
                                    <component :is="channel.icon" class="h-3.5 w-3.5" />
                                </div>
                            </label>
                            <span class="md:hidden text-[8px] font-bold text-slate-400 mt-1 uppercase">{{ channel.label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <transition
                    enter-active-class="transition duration-300"
                    enter-from-class="opacity-0 translate-x-1"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <span v-if="form.recentlySuccessful" class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg flex items-center gap-2 uppercase tracking-widest">
                        <CheckCircleIcon class="h-3 w-3" />
                        Settings Saved
                    </span>
                </transition>

                <button 
                    :disabled="form.processing"
                    class="ml-auto px-6 py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.15em] rounded-xl shadow-lg shadow-slate-200 hover:bg-slate-800 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 transition-all"
                >
                    Save Preferences
                </button>
            </div>
        </form>
    </section>
</template>
