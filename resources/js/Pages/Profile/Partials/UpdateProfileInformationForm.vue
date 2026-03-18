<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { 
    CameraIcon, 
    CheckCircleIcon, 
    ExclamationCircleIcon, 
    PhotoIcon, 
    KeyIcon, 
    UserIcon, 
    IdentificationIcon, 
    LightBulbIcon,
    ShieldCheckIcon
} from '@heroicons/vue/24/outline';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const avatarPreview = ref(null);
const fileInput = ref(null);
const activeTab = ref('profile');

// Check if user has a password (for OAuth users)
const hasPassword = computed(() => {
    return user.has_password || false;
});

// Initialize profile form with current user data
const form = useForm({
    first_name: user.first_name,
    last_name: user.last_name,
    middle_name: user.middle_name || '',
    display_name: user.display_name || '',
    email: user.email,
    phone: user.phone || '',
    timezone: user.timezone || 'Asia/Manila',
    avatar: null,
    _method: 'PATCH',
    hide_google_avatar: user.hide_google_avatar ? Boolean(user.hide_google_avatar) : false,
});

// Initialize password form
const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
    _method: 'POST',
});

const hasGoogleAvatar = computed(() => {
    return user.google_avatar && !user.avatar_url;
});

// Track which fields have been modified (including hide_google_avatar)
const modifiedFields = computed(() => {
    const modified = {};
    Object.keys(form.data()).forEach(key => {
        if (key !== 'avatar' && key !== '_method') {
            // Handle boolean comparison for hide_google_avatar
            if (key === 'hide_google_avatar') {
                const userValue = user.hide_google_avatar ? Boolean(user.hide_google_avatar) : false;
                if (form[key] !== userValue) {
                    modified[key] = form[key] ? 1 : 0;
                }
            } else if (form[key] !== user[key] && form[key] !== '') {
                modified[key] = form[key];
            }
        }
    });
    return modified;
});

// Check if any profile fields (non-avatar) have been modified
const hasProfileChanges = computed(() => Object.keys(modifiedFields.value).length > 0);

// Check if avatar has been changed
const hasAvatarChanges = computed(() => form.avatar !== null);

// Check if form has any changes
const hasChanges = computed(() => hasProfileChanges.value || hasAvatarChanges.value);

// Common timezones with display names
const timezones = [
    { value: 'Asia/Manila', label: 'Manila (GMT+8)' },
    { value: 'Asia/Singapore', label: 'Singapore (GMT+8)' },
    { value: 'Asia/Tokyo', label: 'Tokyo (GMT+9)' },
    { value: 'Asia/Seoul', label: 'Seoul (GMT+9)' },
    { value: 'Asia/Shanghai', label: 'Shanghai (GMT+8)' },
    { value: 'Australia/Sydney', label: 'Sydney (GMT+11)' },
    { value: 'Pacific/Auckland', label: 'Auckland (GMT+13)' },
    { value: 'America/New_York', label: 'New York (GMT-5)' },
    { value: 'America/Chicago', label: 'Chicago (GMT-6)' },
    { value: 'America/Denver', label: 'Denver (GMT-7)' },
    { value: 'America/Los_Angeles', label: 'Los Angeles (GMT-8)' },
    { value: 'Europe/London', label: 'London (GMT+0)' },
    { value: 'Europe/Paris', label: 'Paris (GMT+1)' },
    { value: 'Europe/Berlin', label: 'Berlin (GMT+1)' },
    { value: 'UTC', label: 'UTC (GMT+0)' },
];

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            form.setError('avatar', 'Image size must not exceed 2MB');
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            form.setError('avatar', 'File must be an image');
            return;
        }
        
        form.avatar = file;
        form.clearErrors('avatar');
        
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeAvatar = () => {
    form.avatar = null;
    avatarPreview.value = null;
    if (fileInput.value) fileInput.value.value = '';

    // Send flag to backend
    form.remove_avatar = true;
};

const submitForm = () => {
    const formData = new FormData();
    formData.append('_method', 'PATCH');

    // Include modified profile fields
    if (hasProfileChanges.value) {
        Object.keys(modifiedFields.value).forEach(key => {
            formData.append(key, modifiedFields.value[key]);
        });
    }

    // Include avatar if changed
    if (hasAvatarChanges.value && form.avatar) {
        formData.append('avatar', form.avatar);
    }
    
    // Handle remove_avatar
    if (form.remove_avatar) {
        formData.append('remove_avatar', 1);
    }

    // IMPORTANT: Always include hide_google_avatar in the request
    formData.append('hide_google_avatar', form.hide_google_avatar ? '1' : '0');

    form.post(route('profile.update'), {
        data: formData,
        preserveScroll: true,
        headers: {
            'Content-Type': 'multipart/form-data',
        },
        onSuccess: () => {
            if (!form.avatar) {
                avatarPreview.value = null;
            }
            form.defaults({
                first_name: user.first_name,
                last_name: user.last_name,
                middle_name: user.middle_name || '',
                display_name: user.display_name || '',
                email: user.email,
                phone: user.phone || '',
                timezone: user.timezone || 'Asia/Manila',
                avatar: null,
                hide_google_avatar: user.hide_google_avatar ? Boolean(user.hide_google_avatar) : false,
                _method: 'PATCH',
            });
            form.reset();
            form.clearErrors();
        },
        onError: (errors) => {
            console.log('Form errors:', errors);
        },
    });
};

const submitPassword = () => {
    const routeName = hasPassword.value ? 'password.update' : 'password.set';
    
    if (hasPassword.value) {
        passwordForm._method = 'PUT';
    } else {
        passwordForm._method = 'POST';
    }
    
    passwordForm.post(route(routeName), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            passwordForm._method = 'POST'; // Reset to default
            if (!hasPassword.value) {
                window.location.reload();
            }
        },
    });
};

// Generate display name from first and last name
const generateDisplayName = () => {
    if (form.first_name && form.last_name && !form.display_name) {
        form.display_name = `${form.first_name} ${form.last_name}`.trim();
    }
};

// Watch for first/last name changes to suggest display name
watch([() => form.first_name, () => form.last_name], () => {
    if (!form.display_name || form.display_name === `${form.first_name} ${form.last_name}`.trim()) {
        generateDisplayName();
    }
});

// Reset form to original values
const resetForm = () => {
    form.reset();
    removeAvatar();
};

const currentAvatarPreview = computed(() => {
    if (avatarPreview.value) return avatarPreview.value;
    if (form.hide_google_avatar && !user.avatar_url) {
        return `https://ui-avatars.com/api/?name=${user.first_name}+${user.last_name}&size=256&background=4f46e5&color=fff`;
    }
    return user.avatar_url || `https://ui-avatars.com/api/?name=${user.first_name}+${user.last_name}&size=256&background=818cf8&color=fff`;
});
</script>

<template>
    <div class="h-full flex flex-col">
        <!-- Tab Navigation (Modern Pills) -->
        <div class="px-10 pt-10 pb-6 flex gap-4 overflow-x-auto no-scrollbar border-b border-slate-50">
            <button
                v-for="tab in [
                    { id: 'profile', name: 'Profile Information', icon: UserIcon },
                    { id: 'avatar', name: 'Profile Picture', icon: PhotoIcon },
                    { id: 'password', name: hasPassword ? 'Update Password' : 'Set Password', icon: KeyIcon }
                ]"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                    activeTab === tab.id
                        ? 'bg-slate-900 text-white shadow-xl shadow-slate-200'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                    'flex items-center px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all duration-300'
                ]"
            >
                <component :is="tab.icon" class="w-4 h-4 mr-3" />
                {{ tab.name }}
            </button>
        </div>

        <!-- Form Content Area -->
        <div class="flex-1 p-10">
            <form @submit.prevent="submitForm" enctype="multipart/form-data" v-if="activeTab !== 'password'">
                
                <!-- Avatar Tab -->
                <div v-if="activeTab === 'avatar'" class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mr-1">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Profile Picture</h3>
                            <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Update your profile picture and visual presence.</p>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50/50 rounded-[2.5rem] border border-slate-100 p-12 flex flex-col items-center justify-center relative group">
                        <!-- Abstract background mask -->
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(99,102,241,0.05),transparent)] pointer-events-none"></div>

                        <div class="relative">
                            <div class="w-48 h-48 rounded-[3rem] overflow-hidden bg-white ring-8 ring-white shadow-2xl transition-all duration-500 group-hover:scale-[1.02] group-hover:rotate-1 relative z-10">
                                <img 
                                    :src="currentAvatarPreview" 
                                    :alt="form.display_name || 'Profile avatar'"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            
                            <!-- Large Floating Camera Button -->
                            <button
                                type="button"
                                @click="fileInput?.click()"
                                class="absolute -bottom-4 -right-4 bg-slate-900 rounded-[1.5rem] p-4 text-white shadow-2xl hover:bg-slate-800 hover:scale-110 active:scale-95 transition-all duration-300 z-20 border-4 border-white"
                            >
                                <CameraIcon class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="mt-12 text-center w-full max-w-sm relative z-10">
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/jpg"
                                @change="handleAvatarChange"
                                class="hidden"
                            />
                            
                            <div class="flex justify-center gap-4">
                                <button 
                                    type="button" 
                                    @click="fileInput?.click()"
                                    class="inline-flex items-center px-8 py-4 bg-white border border-slate-200 rounded-2xl font-black text-xs uppercase tracking-widest text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95"
                                >
                                    <PhotoIcon class="w-4 h-4 mr-2.5 text-slate-400" />
                                    Upload Photo
                                </button>
                                
                                <button 
                                    type="button"
                                    @click="removeAvatar"
                                    v-if="form.avatar || user.avatar_url || user.google_avatar"
                                    class="inline-flex items-center px-8 py-4 bg-rose-50 border border-rose-100 rounded-2xl font-black text-xs uppercase tracking-widest text-rose-600 shadow-sm hover:bg-rose-100 transition-all active:scale-95"
                                >
                                    Remove
                                </button>
                            </div>
                            
                            <div class="mt-8 flex items-center justify-center gap-3 bg-white/60 backdrop-blur-sm py-4 px-6 rounded-2xl border border-slate-100 shadow-sm" v-if="user.google_avatar">
                                <input
                                    id="hide_google_avatar"
                                    type="checkbox"
                                    v-model="form.hide_google_avatar"
                                    class="h-5 w-5 rounded-lg border-slate-200 text-slate-900 focus:ring-slate-900 transition-all cursor-pointer"
                                />
                                <label for="hide_google_avatar" class="text-[10px] font-black uppercase tracking-widest text-slate-500 cursor-pointer hover:text-slate-900 transition-colors">
                                    Hide Google Avatar
                                </label>
                            </div>

                            <p class="mt-6 text-[10px] font-black text-slate-300 uppercase tracking-widest">
                                Recommended: 256x256px • JPG, PNG, GIF • Max 2MB
                            </p>
                            <InputError :message="form.errors.avatar" class="mt-2 text-center" />
                        </div>
                    </div>
                </div>

                <!-- Profile Information Tab -->
                <div v-if="activeTab === 'profile'" class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Profile Information</h3>
                        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Update your account's profile information and email address.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                        <!-- First Name -->
                        <div class="space-y-2">
                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">First Name *</label>
                            <input
                                id="first_name"
                                type="text"
                                class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner"
                                v-model="form.first_name"
                                autocomplete="given-name"
                                placeholder="e.g. Jane"
                            />
                            <InputError class="mt-2" :message="form.errors.first_name" />
                        </div>

                        <!-- Last Name -->
                        <div class="space-y-2">
                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Last Name *</label>
                            <input
                                id="last_name"
                                type="text"
                                class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner"
                                v-model="form.last_name"
                                autocomplete="family-name"
                                placeholder="e.g. Doe"
                            />
                            <InputError class="mt-2" :message="form.errors.last_name" />
                        </div>

                        <!-- Middle Name -->
                        <div class="space-y-2">
                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Middle Name</label>
                            <input
                                id="middle_name"
                                type="text"
                                class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner"
                                v-model="form.middle_name"
                                autocomplete="additional-name"
                                placeholder="Optional"
                            />
                            <InputError class="mt-2" :message="form.errors.middle_name" />
                        </div>

                        <!-- Display Name -->
                        <div class="space-y-2">
                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Display Name</label>
                            <input
                                id="display_name"
                                type="text"
                                class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner"
                                v-model="form.display_name"
                                autocomplete="nickname"
                                placeholder="How your name will appear to others"
                                @blur="generateDisplayName"
                            />
                            <p class="px-2 mt-1 text-[10px] font-bold text-slate-400 italic">Automatically generated if left empty.</p>
                            <InputError class="mt-2" :message="form.errors.display_name" />
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Email</label>
                            <div class="relative group">
                                <input
                                    id="email"
                                    type="email"
                                    class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 pr-14 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner"
                                    v-model="form.email"
                                    autocomplete="username"
                                    placeholder="you@example.com"
                                />
                                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                    <CheckCircleIcon v-if="user.email_verified" class="h-6 w-6 text-emerald-500" />
                                    <ExclamationCircleIcon v-else class="h-6 w-6 text-amber-500" />
                                </div>
                            </div>
                            
                            <div v-if="mustVerifyEmail && !user.email_verified" class="mt-4 bg-amber-50/50 rounded-2xl p-5 border border-amber-100 flex items-start">
                                <div class="bg-amber-500 rounded-xl p-1.5 mr-4 mt-0.5">
                                    <ExclamationCircleIcon class="h-5 w-5 text-white" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-black text-amber-800 uppercase tracking-widest">Email Verification Required</p>
                                    <p class="text-xs font-bold text-amber-700 mt-1 italic">Your email address is unverified.</p>
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="mt-4 text-[10px] font-black uppercase tracking-widest text-white bg-amber-600 px-6 py-2.5 rounded-xl hover:bg-amber-700 transition-all active:scale-95 shadow-lg shadow-amber-200"
                                    >
                                        Resend Verification Email
                                    </Link>
                                </div>
                            </div>

                            <div v-show="status === 'verification-link-sent'" class="mt-4 bg-emerald-50/50 rounded-2xl p-5 border border-emerald-100 flex items-start animate-in fade-in slide-in-from-top-2">
                                <div class="bg-emerald-500 rounded-xl p-1.5 mr-4 mt-0.5">
                                    <CheckCircleIcon class="h-5 w-5 text-white" />
                                </div>
                                <p class="text-xs font-black text-emerald-800 uppercase tracking-widest mt-1.5">
                                    A new verification link has been sent to your email address.
                                </p>
                            </div>
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Phone -->
                        <div class="space-y-2">
                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Phone Number</label>
                            <input
                                id="phone"
                                type="tel"
                                class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner"
                                v-model="form.phone"
                                autocomplete="tel"
                                placeholder="+63 XXX XXX XXXX"
                            />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <!-- Timezone -->
                        <div class="space-y-2">
                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Timezone</label>
                            <div class="relative">
                                <select
                                    id="timezone"
                                    class="block w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all shadow-inner appearance-none cursor-pointer"
                                    v-model="form.timezone"
                                >
                                    <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                                        {{ tz.label }}
                                    </option>
                                </select>
                                <svg class="absolute right-5 top-4.5 h-4 w-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <InputError class="mt-2" :message="form.errors.timezone" />
                        </div>
                    </div>
                </div>

                <!-- Shared Form Actions for Avatar & Profile -->
                <div class="mt-12 pt-8 border-t border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <button 
                            type="submit"
                            :disabled="form.processing || !hasChanges"
                            class="inline-flex items-center px-10 py-5 bg-slate-900 border border-transparent rounded-2xl font-black text-[11px] uppercase tracking-[0.15em] text-white shadow-2xl shadow-slate-200 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 disabled:opacity-30 disabled:cursor-not-allowed transition-all active:scale-95"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </button>

                        <Transition
                            enter-active-class="transition duration-500 ease-out"
                            enter-from-class="opacity-0 translate-x-4"
                            enter-to-class="opacity-100 translate-x-0"
                            leave-active-class="transition duration-300 ease-in"
                            leave-from-class="opacity-100 translate-x-0"
                            leave-to-class="opacity-0 translate-x-4"
                        >
                            <span v-if="form.recentlySuccessful" class="flex items-center text-[10px] font-black uppercase tracking-[0.15em] text-emerald-600 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100">
                                <CheckCircleIcon class="w-4 h-4 mr-2" />
                                Saved
                            </span>
                        </Transition>
                    </div>

                    <button 
                        type="button"
                        @click="resetForm"
                        v-if="hasChanges"
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-600 transition-colors border-b-2 border-transparent hover:border-rose-100 pb-1"
                    >
                        Reset Form
                    </button>
                </div>

                <!-- Changes Summary -->
                <div v-if="hasChanges" class="mt-8 flex items-center gap-4 bg-slate-900 text-white p-6 rounded-[2rem] border border-slate-800 shadow-2xl animate-in fade-in slide-in-from-bottom-2">
                    <div class="bg-white/10 rounded-xl p-2">
                        <LightBulbIcon class="w-5 h-5 text-indigo-300" />
                    </div>
                    <div class="flex-1">
                        <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em] mb-1">Unsaved Changes</p>
                        <div class="flex flex-wrap gap-2">
                            <span v-if="hasAvatarChanges" class="text-[10px] font-bold px-2 py-0.5 bg-indigo-500/20 rounded-md border border-indigo-500/30">Profile Picture</span>
                            <span v-for="(value, field) in modifiedFields" :key="field" class="text-[10px] font-bold px-2 py-0.5 bg-white/5 rounded-md border border-white/10 uppercase tracking-widest">
                                {{ field.replace('_', ' ') }}
                            </span>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Password Tab -->
            <div v-else-if="activeTab === 'password'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <form @submit.prevent="submitPassword" class="space-y-12">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mr-1">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">
                                {{ hasPassword ? 'Update Password' : 'Set Password' }}
                            </h3>
                            <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">
                                {{ hasPassword ? 'Ensure your account is using a long, random password to stay secure.' : 'Ensure your account is using a long, random password to stay secure.' }}
                            </p>
                        </div>
                    </div>

                    <div class="max-w-2xl bg-slate-50/50 p-12 rounded-[2.5rem] border border-slate-100 space-y-8">
                        <!-- Current Password -->
                        <div v-if="hasPassword" class="space-y-2">
                            <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Current Password</label>
                            <input
                                id="current_password"
                                type="password"
                                class="block w-full rounded-2xl border-none bg-white shadow-inner px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/5 transition-all"
                                v-model="passwordForm.current_password"
                                autocomplete="current-password"
                                placeholder="Verify current password"
                            />
                            <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- New Password -->
                            <div class="space-y-2">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">New Password</label>
                                <input
                                    id="new_password"
                                    type="password"
                                    class="block w-full rounded-2xl border-none bg-white shadow-inner px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/5 transition-all"
                                    v-model="passwordForm.new_password"
                                    autocomplete="new-password"
                                    placeholder="Create strong password"
                                />
                                <InputError :message="passwordForm.errors.new_password" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="space-y-2">
                                <label class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Confirm Password</label>
                                <input
                                    id="new_password_confirmation"
                                    type="password"
                                    class="block w-full rounded-2xl border-none bg-white shadow-inner px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-slate-900/5 transition-all"
                                    v-model="passwordForm.new_password_confirmation"
                                    autocomplete="new-password"
                                    placeholder="Re-type new password"
                                />
                                <InputError :message="passwordForm.errors.new_password_confirmation" class="mt-2" />
                            </div>
                        </div>

                        <!-- Security Requirements -->
                        <div class="bg-slate-900 rounded-3xl p-8 border border-slate-800 shadow-2xl relative overflow-hidden group">
                             <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all duration-700"></div>
                             <div class="flex items-start gap-5 relative z-10">
                                <div class="bg-white/10 rounded-2xl p-3 shadow-lg">
                                    <ShieldCheckIcon class="w-6 h-6 text-indigo-300" />
                                </div>
                                 <div class="flex-1 pt-1">
                                    <h4 class="text-xs font-black text-white uppercase tracking-widest">Password Requirements</h4>
                                    <p class="text-[10px] font-bold text-slate-400 mt-2 leading-relaxed">Ensure your password is at least 8 characters long and includes a mix of letters, numbers, and symbols for better security.</p>
                                </div>
                             </div>
                        </div>
                    </div>

                    <div class="mt-12 pt-8 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <button 
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="inline-flex items-center px-10 py-5 bg-slate-900 border border-transparent rounded-2xl font-black text-[11px] uppercase tracking-[0.15em] text-white shadow-2xl shadow-slate-200 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 disabled:opacity-30 disabled:cursor-not-allowed transition-all active:scale-95"
                            >
                                <svg v-if="passwordForm.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ passwordForm.processing ? 'Saving...' : 'Save' }}
                            </button>

                            <Transition
                                enter-active-class="transition duration-500 ease-out"
                                enter-from-class="opacity-0 translate-x-4"
                                enter-to-class="opacity-100 translate-x-0"
                                leave-active-class="transition duration-300 ease-in"
                                leave-from-class="opacity-100 translate-x-0"
                                leave-to-class="opacity-0 translate-x-4"
                            >
                                <span v-if="passwordForm.recentlySuccessful" class="flex items-center text-[10px] font-black uppercase tracking-[0.15em] text-emerald-600 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100">
                                    <CheckCircleIcon class="w-4 h-4 mr-2" />
                                    Saved
                                </span>
                            </Transition>
                        </div>

                        <button 
                            type="button"
                            @click="passwordForm.reset(); passwordForm.clearErrors()"
                            v-if="passwordForm.isDirty"
                            class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-600 transition-colors border-b-2 border-transparent hover:border-rose-100 pb-1"
                        >
                            Reset Form
                        </button>
                    </div>  
                </form>
            </div>
        </div>
    </div>
</template>