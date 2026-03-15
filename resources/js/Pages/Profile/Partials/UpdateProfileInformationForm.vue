<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { CameraIcon, CheckCircleIcon, ExclamationCircleIcon, PhotoIcon, KeyIcon, UserIcon, IdentificationIcon, LightBulbIcon } from '@heroicons/vue/24/outline';

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
        return `https://ui-avatars.com/api/?name=${user.first_name}+${user.last_name}&size=128&background=475569&color=fff`;
    }
    return user.avatar;
});
</script>

<template>
    <div class="h-full flex flex-col">
        <!-- Tab Navigation (Modern Pills) -->
        <div class="px-8 pt-8 pb-4 border-b border-gray-100 flex gap-2 overflow-x-auto no-scrollbar">
            <button
                @click="activeTab = 'profile'"
                :class="[
                    activeTab === 'profile'
                        ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-500/20'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                    'flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200'
                ]"
            >
                <IdentificationIcon class="w-5 h-5 mr-2 -ml-1 text-inherit" />
                Profile Information
            </button>
            <button
                @click="activeTab = 'avatar'"
                :class="[
                    activeTab === 'avatar'
                        ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-500/20'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                    'flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200'
                ]"
            >
                <PhotoIcon class="w-5 h-5 mr-2 -ml-1 text-inherit" />
                Profile Picture
            </button>
            <button
                @click="activeTab = 'password'"
                :class="[
                    activeTab === 'password'
                        ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-500/20'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                    'flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200'
                ]"
            >
                <KeyIcon class="w-5 h-5 mr-2 -ml-1 text-inherit" />
                {{ hasPassword ? 'Change Password' : 'Set Password' }}
            </button>
        </div>

        <!-- Form Content Area -->
        <div class="flex-1 p-8">
            <form @submit.prevent="submitForm" enctype="multipart/form-data" v-if="activeTab !== 'password'">
                
                <!-- Avatar Tab -->
                <div v-if="activeTab === 'avatar'" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Update Profile Picture</h3>
                        <p class="text-sm text-gray-500 mt-1">Upload a new profile picture. This will be visible to other users across the system.</p>
                    </div>
                    
                    <div class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
                        <div class="relative group">
                            <div class="w-40 h-40 rounded-full overflow-hidden bg-white ring-4 ring-white shadow-xl transition duration-200 group-hover:scale-105">
                                <img 
                                    :src="currentAvatarPreview" 
                                    :alt="form.display_name || 'Profile avatar'"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            
                            <button
                                type="button"
                                @click="fileInput?.click()"
                                class="absolute bottom-1 right-1 bg-indigo-600 rounded-full p-3 text-white shadow-lg hover:bg-indigo-700 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                            >
                                <CameraIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="mt-8 text-center w-full max-w-sm">
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/jpg"
                                @change="handleAvatarChange"
                                class="hidden"
                            />
                            
                            <div class="flex justify-center flex-wrap gap-3">
                                <button 
                                    type="button" 
                                    @click="fileInput?.click()"
                                    class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 rounded-xl font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all"
                                >
                                    <PhotoIcon class="w-5 h-5 mr-2 text-gray-400" />
                                    Choose Photo
                                </button>
                                
                                <button 
                                    type="button"
                                    @click="removeAvatar"
                                    v-if="form.avatar || user.avatar_url || user.google_avatar"
                                    class="inline-flex items-center px-5 py-2.5 bg-white border border-rose-300 rounded-xl font-semibold text-rose-700 shadow-sm hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all"
                                >
                                    Remove
                                </button>
                            </div>
                            
                            <div class="flex items-center justify-center space-x-2 mt-6 bg-white py-3 px-4 rounded-xl border border-gray-200 shadow-sm" v-if="user.google_avatar">
                                <input
                                    id="hide_google_avatar"
                                    type="checkbox"
                                    v-model="form.hide_google_avatar"
                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 transition duration-150 ease-in-out"
                                />
                                <label for="hide_google_avatar" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Hide Google avatar
                                </label>
                            </div>

                            <p class="mt-4 text-xs font-medium text-gray-400">
                                Recommended: 256x256px. Formats: JPG, PNG, GIF. Max 2MB.
                            </p>
                            <InputError :message="form.errors.avatar" class="mt-2 text-center" />
                        </div>
                    </div>
                </div>

                <!-- Profile Information Tab -->
                <div v-if="activeTab === 'profile'" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Personal Data</h3>
                        <p class="text-sm text-gray-500 mt-1">Review and update your personal information.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <!-- First Name -->
                        <div>
                            <InputLabel for="first_name" value="First Name" />
                            <TextInput
                                id="first_name"
                                type="text"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="form.first_name"
                                autocomplete="given-name"
                                placeholder="E.g. Jane"
                            />
                            <InputError class="mt-2" :message="form.errors.first_name" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <InputLabel for="last_name" value="Last Name" />
                            <TextInput
                                id="last_name"
                                type="text"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="form.last_name"
                                autocomplete="family-name"
                                placeholder="E.g. Doe"
                            />
                            <InputError class="mt-2" :message="form.errors.last_name" />
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <InputLabel for="middle_name" value="Middle Name" />
                            <TextInput
                                id="middle_name"
                                type="text"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="form.middle_name"
                                autocomplete="additional-name"
                                placeholder="Optional"
                            />
                            <InputError class="mt-2" :message="form.errors.middle_name" />
                        </div>

                        <!-- Display Name -->
                        <div>
                            <InputLabel for="display_name" value="Display Name" />
                            <TextInput
                                id="display_name"
                                type="text"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="form.display_name"
                                autocomplete="nickname"
                                placeholder="How others see you"
                                @blur="generateDisplayName"
                            />
                            <p class="mt-1.5 text-xs text-gray-500">Auto-generated if left empty.</p>
                            <InputError class="mt-2" :message="form.errors.display_name" />
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <InputLabel for="email" value="Email Address" />
                            <div class="mt-2 relative rounded-xl shadow-sm">
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10 transition duration-150"
                                    v-model="form.email"
                                    autocomplete="username"
                                    placeholder="you@example.com"
                                />
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <CheckCircleIcon v-if="user.email_verified" class="h-5 w-5 text-emerald-500" />
                                    <ExclamationCircleIcon v-else class="h-5 w-5 text-amber-500" />
                                </div>
                            </div>
                            
                            <div v-if="mustVerifyEmail && !user.email_verified" class="mt-3 bg-amber-50 rounded-lg p-3 border border-amber-100 flex items-start">
                                <ExclamationCircleIcon class="h-5 w-5 text-amber-500 mt-0.5 mr-2 flex-shrink-0" />
                                <div>
                                    <p class="text-sm font-medium text-amber-800">Email unverified</p>
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="mt-1 text-sm text-indigo-600 hover:text-indigo-800 font-semibold transition-colors"
                                    >
                                        Click here to resend verification email
                                    </Link>
                                </div>
                            </div>

                            <div v-show="status === 'verification-link-sent'" class="mt-3 bg-emerald-50 rounded-lg p-3 border border-emerald-100 flex items-start">
                                <CheckCircleIcon class="h-5 w-5 text-emerald-500 mt-0.5 mr-2 flex-shrink-0" />
                                <p class="text-sm font-medium text-emerald-800">
                                    A new verification link has been sent to your email address.
                                </p>
                            </div>
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <InputLabel for="phone" value="Phone Number" />
                            <TextInput
                                id="phone"
                                type="tel"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="form.phone"
                                autocomplete="tel"
                                placeholder="+63 XXX XXX XXXX"
                            />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <!-- Timezone -->
                        <div>
                            <InputLabel for="timezone" value="Timezone" />
                            <select
                                id="timezone"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="form.timezone"
                            >
                                <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                                    {{ tz.label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.timezone" />
                        </div>
                    </div>
                </div>

                <!-- Shared Form Actions for Avatar & Profile -->
                <div class="mt-10 pt-6 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button 
                            type="submit"
                            :disabled="form.processing || !hasChanges"
                            class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                        >
                            {{ form.processing ? 'Saving Changes...' : 'Save Changes' }}
                        </button>

                        <Transition
                            enter-active-class="transition duration-300 ease-out"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <span v-if="form.recentlySuccessful" class="flex items-center text-sm font-medium text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg">
                                <CheckCircleIcon class="w-4 h-4 mr-1.5" />
                                Saved successfully
                            </span>
                        </Transition>
                    </div>

                    <button 
                        type="button"
                        @click="resetForm"
                        v-if="hasChanges"
                        class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        Discard changes
                    </button>
                </div>

                <!-- Changes Summary -->
                <div v-if="hasChanges" class="mt-4 flex items-center text-sm bg-indigo-50/50 text-indigo-700 p-3 rounded-xl border border-indigo-100/50">
                    <LightBulbIcon class="w-5 h-5 mr-2 text-indigo-500 flex-shrink-0" />
                    <span>
                        <span class="font-semibold">Unsaved changes:</span>
                        <span v-if="hasAvatarChanges"> Profile picture,</span>
                        <span v-for="(value, field) in modifiedFields" :key="field">
                            {{ field.replace('_', ' ') }},
                        </span>
                    </span>
                </div>
            </form>

            <!-- Password Tab -->
            <div v-else-if="activeTab === 'password'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                <form @submit.prevent="submitPassword" class="space-y-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ hasPassword ? 'Update Password' : 'Set Account Password' }}
                        </h3>
                        
                        <p v-if="!hasPassword" class="text-sm text-gray-500 mt-1">
                            You currently use Google to sign in. Setting a password allows you to sign in with email/password as well.
                        </p>
                        <p v-else class="text-sm text-gray-500 mt-1">
                            Ensure your password is long and unique to keep your account secure.
                        </p>
                    </div>

                    <div class="max-w-xl space-y-6">
                        <!-- Current Password -->
                        <div v-if="hasPassword">
                            <InputLabel for="current_password" value="Current Password" />
                            <TextInput
                                id="current_password"
                                type="password"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="passwordForm.current_password"
                                autocomplete="current-password"
                                placeholder="Enter current password"
                            />
                            <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                        </div>

                        <!-- New Password -->
                        <div>
                            <InputLabel for="new_password" :value="hasPassword ? 'New Password' : 'Password'" />
                            <TextInput
                                id="new_password"
                                type="password"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="passwordForm.new_password"
                                autocomplete="new-password"
                                placeholder="Create new password"
                            />
                            <InputError :message="passwordForm.errors.new_password" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <InputLabel for="new_password_confirmation" :value="hasPassword ? 'Confirm New Password' : 'Confirm Password'" />
                            <TextInput
                                id="new_password_confirmation"
                                type="password"
                                class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                                v-model="passwordForm.new_password_confirmation"
                                autocomplete="new-password"
                                placeholder="Confirm new password"
                            />
                            <InputError :message="passwordForm.errors.new_password_confirmation" class="mt-2" />
                        </div>

                        <!-- Security Notice -->
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 flex items-start">
                            <KeyIcon class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" />
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Password Requirements</h4>
                                <ul class="mt-2 text-sm text-gray-600 list-disc list-inside space-y-1">
                                    <li>At least 8 characters long</li>
                                    <li>Contains uppercase and lowercase letters</li>
                                    <li>Contains numbers and special symbols</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 pt-6 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button 
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition-all"
                            >
                                {{ passwordForm.processing ? 'Processing...' : (hasPassword ? 'Update Password' : 'Set Password') }}
                            </button>

                            <Transition
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <span v-if="passwordForm.recentlySuccessful" class="flex items-center text-sm font-medium text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg">
                                    <CheckCircleIcon class="w-4 h-4 mr-1.5" />
                                    Password saved
                                </span>
                            </Transition>
                        </div>

                        <button 
                            type="button"
                            @click="passwordForm.reset(); passwordForm.clearErrors()"
                            v-if="passwordForm.isDirty"
                            class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            Clear form
                        </button>
                    </div>  
                </form>
            </div>

        </div>
    </div>
</template>