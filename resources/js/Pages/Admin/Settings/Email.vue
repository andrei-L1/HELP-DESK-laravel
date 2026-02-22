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

const submit = () => {
    form.post(route('admin.settings.email.update'), {
        preserveScroll: true,
    });
};

const submitNotifications = () => {
    notificationForm.post(route('admin.settings.email.notifications.update'), {
        preserveScroll: true,
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

const saveSuccess = ref(false);

const showSuccessMessage = () => {
    saveSuccess.value = true;
    setTimeout(() => {
        saveSuccess.value = false;
    }, 3000);
};
</script>

<template>
    <Head title="Email Settings" />

    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.settings.index')"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Settings
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Email Settings</h1>
            </div>
        </template>

        <div class="p-6">
            <!-- Test Email Modal -->
            <Teleport to="body">
                <div
                    v-if="showTestModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="Test email"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div
                            class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            aria-hidden="true"
                            @click="closeTestModal"
                        />
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        >
                            <form @submit.prevent="sendTestEmail" class="p-6 space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Send Test Email</h3>
                                
                                <div>
                                    <label for="to_email" class="block text-sm font-medium text-gray-700">
                                        Send To <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="to_email"
                                        v-model="testEmailForm.to_email"
                                        type="email"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="test@example.com"
                                    />
                                    <p v-if="testEmailForm.errors.to_email" class="mt-1 text-sm text-red-600">
                                        {{ testEmailForm.errors.to_email }}
                                    </p>
                                </div>

                                <div>
                                    <label for="test_subject" class="block text-sm font-medium text-gray-700">
                                        Subject
                                    </label>
                                    <input
                                        id="test_subject"
                                        v-model="testEmailForm.subject"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label for="test_message" class="block text-sm font-medium text-gray-700">
                                        Message
                                    </label>
                                    <textarea
                                        id="test_message"
                                        v-model="testEmailForm.message"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    ></textarea>
                                </div>

                                <div class="bg-blue-50 p-3 rounded-md">
                                    <p class="text-xs text-blue-700">
                                        <strong>Note:</strong> This will send a test email using your current SMTP settings. 
                                        Make sure you've saved your settings first.
                                    </p>
                                </div>

                                <div class="flex gap-3 justify-end pt-2">
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                        @click="closeTestModal"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                        :disabled="testEmailForm.processing"
                                    >
                                        {{ testEmailForm.processing ? 'Sending...' : 'Send Test Email' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'smtp'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'smtp' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        SMTP Configuration
                    </button>
                    <button
                        @click="activeTab = 'notifications'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'notifications' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Notifications
                    </button>
                    <button
                        @click="activeTab = 'templates'"
                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium"
                        :class="activeTab === 'templates' 
                            ? 'border-slate-500 text-slate-600' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    >
                        Email Templates
                    </button>
                </nav>
            </div>

            <!-- Success Message -->
            <div
                v-if="saveSuccess"
                class="mb-4 rounded-md bg-green-50 p-4"
            >
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            Settings saved successfully!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Test Email Status -->
            <div
                v-if="testEmailStatus"
                class="mb-4 rounded-md p-4"
                :class="testEmailStatus.includes('success') ? 'bg-green-50' : 'bg-red-50'"
            >
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg 
                            class="h-5 w-5" 
                            :class="testEmailStatus.includes('success') ? 'text-green-400' : 'text-red-400'"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path 
                                v-if="testEmailStatus.includes('success')"
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" 
                            />
                            <path 
                                v-else
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" 
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium" :class="testEmailStatus.includes('success') ? 'text-green-800' : 'text-red-800'">
                            {{ testEmailStatus }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- SMTP Configuration Tab -->
            <div v-if="activeTab === 'smtp'" class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50 flex justify-between items-center">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-700">SMTP Configuration</h2>
                            <p class="text-xs text-gray-500 mt-1">Configure your email server settings</p>
                        </div>
                        <button
                            @click="openTestModal"
                            class="inline-flex items-center rounded-md bg-white border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Send Test Email
                        </button>
                    </div>
                    
                    <form @submit.prevent="submit" class="px-6 py-4 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="mail_mailer" class="block text-sm font-medium text-gray-700">
                                    Mail Driver <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="mail_mailer"
                                    v-model="form.mail_mailer"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option v-for="mailer in mailers" :key="mailer.value" :value="mailer.value">
                                        {{ mailer.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="mail_host" class="block text-sm font-medium text-gray-700">
                                    SMTP Host <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="mail_host"
                                    v-model="form.mail_host"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="smtp.gmail.com"
                                />
                                <p v-if="form.errors.mail_host" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.mail_host }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label for="mail_port" class="block text-sm font-medium text-gray-700">
                                    Port <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="mail_port"
                                    v-model="form.mail_port"
                                    type="number"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="587"
                                />
                            </div>

                            <div>
                                <label for="mail_encryption" class="block text-sm font-medium text-gray-700">
                                    Encryption
                                </label>
                                <select
                                    id="mail_encryption"
                                    v-model="form.mail_encryption"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                >
                                    <option v-for="enc in encryptionTypes" :key="enc.value" :value="enc.value">
                                        {{ enc.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="mail_username" class="block text-sm font-medium text-gray-700">
                                    Username
                                </label>
                                <input
                                    id="mail_username"
                                    v-model="form.mail_username"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="your-email@gmail.com"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="mail_password" class="block text-sm font-medium text-gray-700">
                                    Password / App Password
                                </label>
                                <input
                                    id="mail_password"
                                    v-model="form.mail_password"
                                    type="password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                    placeholder="••••••••"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    For Gmail, use an <a href="https://support.google.com/accounts/answer/185833" target="_blank" class="text-slate-600 hover:underline">App Password</a>
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">From Address</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="mail_from_address" class="block text-sm font-medium text-gray-700">
                                        From Email <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="mail_from_address"
                                        v-model="form.mail_from_address"
                                        type="email"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="noreply@helpdesk.com"
                                    />
                                </div>

                                <div>
                                    <label for="mail_from_name" class="block text-sm font-medium text-gray-700">
                                        From Name
                                    </label>
                                    <input
                                        id="mail_from_name"
                                        v-model="form.mail_from_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="HelpDesk System"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Reply-To Address (Optional)</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="mail_reply_to_address" class="block text-sm font-medium text-gray-700">
                                        Reply-To Email
                                    </label>
                                    <input
                                        id="mail_reply_to_address"
                                        v-model="form.mail_reply_to_address"
                                        type="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="support@helpdesk.com"
                                    />
                                </div>

                                <div>
                                    <label for="mail_reply_to_name" class="block text-sm font-medium text-gray-700">
                                        Reply-To Name
                                    </label>
                                    <input
                                        id="mail_reply_to_name"
                                        v-model="form.mail_reply_to_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm"
                                        placeholder="Support Team"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs text-blue-700">
                                        <strong>Common SMTP Settings:</strong><br>
                                        • Gmail: smtp.gmail.com, Port 587, TLS, Use App Password<br>
                                        • Outlook/Hotmail: smtp-mail.outlook.com, Port 587, TLS<br>
                                        • Yahoo: smtp.mail.yahoo.com, Port 587, TLS<br>
                                        • Office 365: smtp.office365.com, Port 587, TLS
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                class="inline-flex justify-center rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Saving...' : 'Save SMTP Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div v-if="activeTab === 'notifications'" class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700">Email Notifications</h2>
                        <p class="text-xs text-gray-500 mt-1">Configure who receives email notifications</p>
                    </div>
                    
                    <form @submit.prevent="submitNotifications" class="px-6 py-4 space-y-4">
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-700">Ticket Notifications</h3>
                            
                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="notificationForm.new_ticket_notification"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Notify when new ticket is created</span>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="notificationForm.ticket_assigned_notification"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Notify when ticket is assigned</span>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="notificationForm.ticket_updated_notification"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Notify when ticket is updated</span>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="notificationForm.new_message_notification"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Notify when new message is added</span>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-gray-200">
                            <h3 class="text-sm font-medium text-gray-700">Summary Reports</h3>
                            
                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="notificationForm.daily_summary"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Send daily summary (end of day)</span>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="notificationForm.weekly_summary"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Send weekly summary (every Monday)</span>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-gray-200">
                            <h3 class="text-sm font-medium text-gray-700">Admin Notifications</h3>
                            
                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="notificationForm.notify_admin_on_new_ticket"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Notify all admins on new ticket</span>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <label class="flex items-center text-sm text-gray-700">
                                    <input
                                        v-model="notificationForm.notify_manager_on_new_ticket"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-slate-600 focus:ring-slate-500"
                                    />
                                    <span class="ml-2">Notify department managers on new ticket</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                class="inline-flex justify-center rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-50"
                                :disabled="notificationForm.processing"
                            >
                                {{ notificationForm.processing ? 'Saving...' : 'Save Notification Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Email Templates Tab -->
            <div v-if="activeTab === 'templates'" class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700">Email Templates</h2>
                        <p class="text-xs text-gray-500 mt-1">Customize email templates for different events</p>
                    </div>
                    
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <!-- New Ticket Template -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900">New Ticket</h3>
                                    </div>
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                        Active
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">Sent when a new ticket is created</p>
                                <div class="flex justify-end">
                                    <button class="text-xs text-slate-600 hover:text-slate-900 font-medium">
                                        Edit Template
                                    </button>
                                </div>
                            </div>

                            <!-- Ticket Assigned Template -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900">Ticket Assigned</h3>
                                    </div>
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                        Active
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">Sent when a ticket is assigned to an agent</p>
                                <div class="flex justify-end">
                                    <button class="text-xs text-slate-600 hover:text-slate-900 font-medium">
                                        Edit Template
                                    </button>
                                </div>
                            </div>

                            <!-- New Message Template -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900">New Message</h3>
                                    </div>
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                        Active
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">Sent when a new message is added to a ticket</p>
                                <div class="flex justify-end">
                                    <button class="text-xs text-slate-600 hover:text-slate-900 font-medium">
                                        Edit Template
                                    </button>
                                </div>
                            </div>

                            <!-- Ticket Resolved Template -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900">Ticket Resolved</h3>
                                    </div>
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                        Draft
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">Sent when a ticket is marked as resolved</p>
                                <div class="flex justify-end">
                                    <button class="text-xs text-slate-600 hover:text-slate-900 font-medium">
                                        Create Template
                                    </button>
                                </div>
                            </div>

                            <!-- Daily Summary Template -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900">Daily Summary</h3>
                                    </div>
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                        Draft
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">Daily report of ticket activity</p>
                                <div class="flex justify-end">
                                    <button class="text-xs text-slate-600 hover:text-slate-900 font-medium">
                                        Create Template
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                                <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                New Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>