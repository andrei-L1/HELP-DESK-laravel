<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({
            mail_mailer: 'smtp',
            mail_host: 'smtp.gmail.com',
            mail_port: 587,
            mail_username: '',
            mail_password: '',
            mail_encryption: 'tls',
            mail_from_address: '',
            mail_from_name: '',
            mail_reply_to_address: '',
            mail_reply_to_name: '',
        }),
    },
    testEmailStatus: {
        type: String,
        default: '',
    },
});

const activeTab = ref('smtp');
const showTestModal = ref(false);
const testEmailForm = useForm({
    to_email: '',
    subject: 'Test Email from HelpDesk System',
    message: 'This is a test email to verify your SMTP configuration.',
});

const form = useForm({
    mail_mailer: props.settings.mail_mailer,
    mail_host: props.settings.mail_host,
    mail_port: props.settings.mail_port,
    mail_username: props.settings.mail_username,
    mail_password: props.settings.mail_password,
    mail_encryption: props.settings.mail_encryption,
    mail_from_address: props.settings.mail_from_address,
    mail_from_name: props.settings.mail_from_name,
    mail_reply_to_address: props.settings.mail_reply_to_address,
    mail_reply_to_name: props.settings.mail_reply_to_name,
});

const notificationForm = useForm({
    new_ticket_notification: true,
    ticket_assigned_notification: true,
    ticket_updated_notification: true,
    new_message_notification: true,
    daily_summary: false,
    weekly_summary: true,
    notify_admin_on_new_ticket: true,
    notify_manager_on_new_ticket: false,
});

const tabs = [
    { id: 'smtp', label: 'SMTP Configuration', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { id: 'notifications', label: 'Email Notifications', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    { id: 'templates', label: 'Email Templates', icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
];

const saveSuccess = ref(false);

const submit = () => {
    form.post(route('admin.settings.email.update'), {
        preserveScroll: true,
        onSuccess: () => {
            saveSuccess.value = true;
            setTimeout(() => {
                saveSuccess.value = false;
            }, 5000);
        },
    });
};

const submitNotifications = () => {
    notificationForm.post(route('admin.settings.email.notifications.update'), {
        preserveScroll: true,
        onSuccess: () => {
            saveSuccess.value = true;
            setTimeout(() => {
                saveSuccess.value = false;
            }, 5000);
        },
    });
};

const sendTestEmail = () => {
    testEmailForm.post(route('admin.settings.email.test'), {
        preserveScroll: true,
        onSuccess: () => {
            showTestModal.value = false;
            testEmailForm.reset();
        },
    });
};

const openTestModal = () => {
    testEmailForm.reset();
    testEmailForm.to_email = '';
    showTestModal.value = true;
};

const closeTestModal = () => {
    showTestModal.value = false;
    testEmailForm.reset();
};

const mailers = [
    { value: 'smtp', label: 'SMTP' },
    { value: 'sendmail', label: 'Sendmail' },
    { value: 'mailgun', label: 'Mailgun' },
    { value: 'ses', label: 'Amazon SES' },
    { value: 'postmark', label: 'Postmark' },
    { value: 'log', label: 'Log (for testing)' },
];

const encryptionTypes = [
    { value: 'tls', label: 'TLS' },
    { value: 'ssl', label: 'SSL' },
    { value: '', label: 'None' },
];
</script>

<template>
    <Head title="Email Settings" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex flex-col">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 stagger-1">
                    <Link :href="route('admin.settings.index')" class="hover:text-slate-900 transition-colors">Settings</Link>
                    <span>/</span>
                    <span class="text-slate-900">Email</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight stagger-1">Email Configuration</h1>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-8 pb-20 pt-4">
            <!-- Navigation Protocol & Success Messages -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-2 stagger-1">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'group flex items-center gap-2.5 px-5 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all duration-300',
                            activeTab === tab.id 
                                ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' 
                                : 'bg-white text-slate-400 hover:text-slate-900 border border-slate-200 hover:border-slate-300 shadow-sm'
                        ]"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="tab.icon" />
                        </svg>
                        {{ tab.label }}
                    </button>
                    <button
                        v-if="activeTab === 'smtp'"
                        @click="openTestModal"
                        class="px-5 py-3 rounded-xl bg-white border border-slate-900 text-slate-900 font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all shadow-sm"
                    >
                        Send Test
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <Transition
                        enter-active-class="transition duration-500 ease-out"
                        enter-from-class="opacity-0 translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition duration-300 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-if="saveSuccess" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            Changes saved successfully
                        </div>
                    </Transition>
                    <Transition
                        enter-active-class="transition duration-500 ease-out"
                        enter-from-class="opacity-0 translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition duration-300 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-if="testEmailStatus" 
                             :class="testEmailStatus.includes('success') ? 'bg-indigo-50 border-indigo-100 text-indigo-700' : 'bg-rose-50 border-rose-100 text-rose-700'"
                             class="flex items-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ testEmailStatus }}
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- SMTP Configuration -->
            <div v-if="activeTab === 'smtp'" class="stagger-2 space-y-8">
                <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden">
                    <form @submit.prevent="submit" class="divide-y divide-slate-100">
                        <div class="grid grid-cols-1 lg:grid-cols-12">
                            <!-- Sidebar Info -->
                            <div class="lg:col-span-4 p-8 lg:p-10 bg-slate-50/30 space-y-6">
                                <div class="h-12 w-12 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg shadow-slate-200">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="space-y-2 text-wrap">
                                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">Postmaster Configuration</h3>
                                    <p class="text-sm text-slate-500 font-medium leading-relaxed font-serif italic">Establish the connection parameters for outbound system communications.</p>
                                </div>
                                
                                <div class="p-5 rounded-2xl bg-white border border-slate-200/60 shadow-sm space-y-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Verified Connection</p>
                                    <p class="text-[11px] text-slate-600 font-medium leading-relaxed italic font-serif">You can validate your SMTP settings immediately after saving.</p>
                                    <button
                                        type="button"
                                        @click="openTestModal"
                                        class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-slate-50 text-slate-900 border border-slate-200 font-black text-[10px] uppercase tracking-widest hover:bg-white hover:border-slate-900 hover:shadow-md transition-all shadow-sm"
                                    >
                                        Connection Probe
                                    </button>
                                </div>
                            </div>

                            <!-- Form Fields -->
                            <div class="lg:col-span-8 p-8 lg:p-10 space-y-10">
                                <!-- Connection Parameters -->
                                <div class="space-y-8">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-1.5 rounded-full bg-slate-900"></div>
                                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-900">Host Infrastructure</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Protocol / Driver</label>
                                            <div class="relative group">
                                                <select v-model="form.mail_mailer" class="w-full appearance-none rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                                    <option v-for="item in mailers" :key="item.value" :value="item.value">{{ item.label }}</option>
                                                </select>
                                                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">SMTP Hostname</label>
                                            <input v-model="form.mail_host" type="text" class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all placeholder:text-slate-300" placeholder="smtp.relay.com">
                                        </div>

                                        <div class="space-y-2 text-wrap">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Port</label>
                                            <input v-model="form.mail_port" type="number" class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" placeholder="587">
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Security</label>
                                            <div class="relative group">
                                                <select v-model="form.mail_encryption" class="w-full appearance-none rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                                    <option v-for="enc in encryptionTypes" :key="enc.value" :value="enc.value">{{ enc.label }}</option>
                                                </select>
                                                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Security Credentials -->
                                <div class="space-y-8">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-1.5 rounded-full bg-slate-400"></div>
                                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-900">Credentials</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Username</label>
                                            <input v-model="form.mail_username" type="text" class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all placeholder:text-slate-300" placeholder="e.g. support@domain.com">
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Password</label>
                                            <input v-model="form.mail_password" type="password" class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all placeholder:text-slate-300" placeholder="••••••••••••">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sender Info -->
                                <div class="space-y-8">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-1.5 rounded-full bg-slate-400"></div>
                                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-900 text-wrap">Identification</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="space-y-2 text-wrap">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Outbound Address</label>
                                            <input v-model="form.mail_from_address" type="email" class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all placeholder:text-slate-300" placeholder="noreply@helpdesk.com">
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Display Name</label>
                                            <input v-model="form.mail_from_name" type="text" class="w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 py-4 text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all placeholder:text-slate-300" placeholder="System Automations">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="p-8 lg:p-10 flex items-center justify-between gap-6 bg-slate-50/50">
                            <div class="hidden sm:flex items-center gap-3 text-slate-400">
                                 <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                 <p class="text-[10px] font-bold uppercase tracking-widest italic leading-tight max-w-[200px]">Server restart may be required for some drivers.</p>
                            </div>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-10 py-4 rounded-2xl bg-slate-900 text-white text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50 active:scale-95">
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>{{ form.processing ? 'Persisting...' : 'Save Configuration' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="activeTab === 'notifications'" class="stagger-2 space-y-8">
                <div class="bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/60 overflow-hidden">
                    <form @submit.prevent="submitNotifications">
                        <div class="grid grid-cols-1 lg:grid-cols-12">
                            <!-- Sidebar Info -->
                            <div class="lg:col-span-4 p-8 lg:p-10 bg-slate-50/30 space-y-6">
                                <div class="h-12 w-12 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg shadow-slate-200">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                </div>
                                <div class="space-y-2 text-wrap">
                                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">Notification Logic</h3>
                                    <p class="text-sm text-slate-500 font-medium leading-relaxed font-serif italic">Define which system events should trigger email dispatches to users and staff.</p>
                                </div>
                            </div>

                            <!-- List of Switches -->
                            <div class="lg:col-span-8 divide-y divide-slate-100">
                                <div 
                                    v-for="(label, key) in {
                                        new_ticket_notification: 'New Ticket Customer Receipt',
                                        ticket_assigned_notification: 'Agent Assignment Notification',
                                        ticket_updated_notification: 'Status Change Alert',
                                        new_message_notification: 'Message Thread Updates',
                                        notify_admin_on_new_ticket: 'Global Administrator Dispatch',
                                        notify_manager_on_new_ticket: 'Managerial Escalation'
                                    }" 
                                    :key="key"
                                    class="p-6 lg:px-10 flex items-center justify-between group hover:bg-slate-50/50 transition-all"
                                >
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ key.includes('admin') ? 'Staff Alert' : 'Standard Response' }}</p>
                                        <h4 class="text-sm font-bold text-slate-900">{{ label }}</h4>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="notificationForm[key]" class="sr-only peer">
                                        <div class="w-12 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900 shadow-inner"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="p-8 lg:p-10 flex items-center justify-between gap-6 bg-slate-50/50 border-t border-slate-100">
                            <div class="hidden sm:flex items-center gap-3 text-slate-400 text-wrap">
                                 <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                 <p class="text-[10px] font-bold uppercase tracking-widest italic leading-tight max-w-[200px]">Notification delays may occur during high system load.</p>
                            </div>
                            <button type="submit" :disabled="notificationForm.processing"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-10 py-4 rounded-2xl bg-slate-900 text-white text-sm font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all disabled:opacity-50">
                                <span>{{ notificationForm.processing ? 'Persisting...' : 'Save Dispatch Rules' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="activeTab === 'templates'" class="stagger-3 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="template in [
                            { name: 'New Ticket Confirmation', category: 'TICKET', status: 'ACTIVE', desc: 'Sent to customers when they create a new ticket.', icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6', color: 'indigo' },
                            { name: 'Agent Assignment', category: 'ASSIGNMENT', status: 'ACTIVE', desc: 'Sent to agents when a ticket is assigned to them.', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', color: 'slate' },
                            { name: 'New Reply Notification', category: 'MESSAGING', status: 'ACTIVE', desc: 'Sent when a new reply is added to a ticket.', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', color: 'emerald' },
                            { name: 'Ticket Resolved', category: 'CLOSURE', status: 'DRAFT', desc: 'Sent when a ticket is marked as resolved.', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'rose' }
                        ]"
                        :key="template.name"
                        class="group bg-white rounded-2xl border border-slate-300/40 shadow-sm shadow-slate-200/60 p-8 hover:shadow-xl hover:shadow-slate-200/50 hover:border-slate-400/40 transition-all duration-300 flex flex-col gap-6"
                    >
                        <div class="flex items-center justify-between">
                            <div :class="{
                                'bg-indigo-100 text-indigo-600': template.color === 'indigo',
                                'bg-slate-100 text-slate-600': template.color === 'slate',
                                'bg-emerald-100 text-emerald-600': template.color === 'emerald',
                                'bg-rose-100 text-rose-600': template.color === 'rose',
                            }" class="h-10 w-10 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="template.icon" /></svg>
                            </div>
                            <span :class="template.status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100'" class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border">
                                {{ template.status }}
                            </span>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 opacity-80">{{ template.category }}</h4>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight leading-tight">{{ template.name }}</h3>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed italic font-serif">{{ template.desc }}</p>
                        </div>
                        <div class="pt-6 border-t border-slate-50 mt-auto flex items-center justify-between">
                            <button class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Clone</button>
                            <button class="text-[10px] font-black uppercase tracking-widest text-slate-900 flex items-center gap-1.5 group/btn">
                                Edit Blueprint
                                <svg class="h-3 w-3 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Test Email Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showTestModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 min-h-screen">
                    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeTestModal"></div>
                    
                    <div class="relative w-full max-w-xl bg-white rounded-[2.5rem] shadow-2xl shadow-slate-900/20 overflow-hidden">
                        <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <div class="flex items-center gap-4 text-wrap">
                                <div class="h-10 w-10 rounded-2xl bg-slate-900 flex items-center justify-center shadow-lg shadow-slate-200">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="text-wrap">
                                    <h3 class="text-lg font-black text-slate-900 tracking-tight text-wrap">Send Test Email</h3>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 text-wrap line-clamp-1">SMTP Connection Test</p>
                                </div>
                            </div>
                            <button @click="closeTestModal" class="h-10 w-10 rounded-2xl hover:bg-slate-100 flex items-center justify-center transition-colors text-slate-400 hover:text-slate-900">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form @submit.prevent="sendTestEmail" class="p-10 space-y-8">
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Recipient Email</label>
                                    <input v-model="testEmailForm.to_email" type="email" required class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 placeholder:text-slate-300 focus:bg-white focus:border-slate-900 focus:ring-0 transition-all" placeholder="your-email@example.com">
                                    <p v-if="testEmailForm.errors.to_email" class="text-[10px] font-bold text-rose-500 mt-1 uppercase tracking-wider">{{ testEmailForm.errors.to_email }}</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Subject</label>
                                    <input v-model="testEmailForm.subject" type="text" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 placeholder:text-slate-300 focus:bg-white focus:border-slate-900 focus:ring-0 transition-all">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Message Body</label>
                                    <textarea v-model="testEmailForm.message" rows="3" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold text-slate-900 placeholder:text-slate-300 focus:bg-white focus:border-slate-900 focus:ring-0 transition-all resize-none"></textarea>
                                </div>
                            </div>

                            <div class="p-6 rounded-3xl bg-emerald-50 border-2 border-emerald-100/50">
                                <p class="text-[11px] text-emerald-900 font-bold italic font-serif leading-relaxed">
                                    <span class="font-black not-italic block mb-1 uppercase tracking-widest text-[9px] opacity-70">Important:</span>
                                    This test uses your current SMTP settings. Make sure you have saved any recent changes before sending the test.
                                </p>
                            </div>

                            <div class="pt-6 flex items-center justify-end gap-4">
                                <button type="button" @click="closeTestModal" class="px-8 py-4 rounded-xl font-black text-[10px] uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel</button>
                                <button
                                    type="submit"
                                    :disabled="testEmailForm.processing"
                                    class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 disabled:opacity-50 flex items-center gap-3"
                                >
                                    <svg v-if="testEmailForm.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    {{ testEmailForm.processing ? 'Sending...' : 'Send Test Email' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminNavigation>
</template>