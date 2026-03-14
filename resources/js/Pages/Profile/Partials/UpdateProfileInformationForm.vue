<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { CameraIcon, CheckCircleIcon, ExclamationCircleIcon, PhotoIcon } from '@heroicons/vue/24/outline';

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

// Initialize form with current user data
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
    // This ensures it gets updated even if other fields aren't changed
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
            // Reset form defaults with updated user data
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
    activeTab.value = 'profile';
};


const currentAvatarPreview = computed(() => {
    if (avatarPreview.value) return avatarPreview.value;
    if (form.hide_google_avatar && !user.avatar_url) {
        // Show initials avatar when Google avatar is hidden
        return `https://ui-avatars.com/api/?name=${user.first_name}+${user.last_name}&size=128&background=475569&color=fff`;
    }
    return user.avatar;
});
</script>

<template>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Profile Settings</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Manage your account information and preferences
                </p>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'profile'"
                        :class="[
                            activeTab === 'profile'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        Profile Information
                    </button>
                    <button
                        @click="activeTab = 'avatar'"
                        :class="[
                            activeTab === 'avatar'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        Profile Picture
                    </button>
                </nav>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form @submit.prevent="submitForm" class="p-6" enctype="multipart/form-data">
                    <!-- Avatar Tab -->
                    <div v-if="activeTab === 'avatar'" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Update Profile Picture</h3>
                            <p class="text-sm text-gray-500 mb-6">
                                Upload a new profile picture. This will be visible to other users.
                            </p>
                        </div>
                        
                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center space-y-6">
                            <!-- Avatar Preview -->
                            <div class="relative">
                                <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-100 ring-4 ring-white shadow-lg">
                                    <img 
                                        :src="currentAvatarPreview" 
                                        :alt="form.display_name || 'Profile avatar'"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                
                                <!-- Camera button for upload -->
                                <button
                                    type="button"
                                    @click="fileInput?.click()"
                                    class="absolute bottom-0 right-0 bg-indigo-600 rounded-full p-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                >
                                    <CameraIcon class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Avatar Actions -->
                            <div class="flex-1 text-center">
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/gif,image/jpg"
                                    @change="handleAvatarChange"
                                    class="hidden"
                                />
                                
                                <div class="flex justify-center space-x-3">
                                    <SecondaryButton 
                                        type="button" 
                                        @click="fileInput?.click()"
                                        class="inline-flex items-center"
                                    >
                                        <PhotoIcon class="w-4 h-4 mr-2" />
                                        Choose Photo
                                    </SecondaryButton>
                                    
                                    <SecondaryButton 
                                        type="button"
                                        @click="removeAvatar"
                                        v-if="form.avatar || user.avatar_url || user.google_avatar"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        Remove
                                    </SecondaryButton>
                                </div>
                                <div class="flex items-center space-x-2 mt-4" v-if="user.google_avatar">
                                    <input
                                        id="hide_google_avatar"
                                        type="checkbox"
                                        v-model="form.hide_google_avatar"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <label for="hide_google_avatar" class="text-sm text-gray-700">
                                        Hide Google avatar
                                    </label>
                                    <span class="text-xs text-gray-500 ml-2">
                                        ({{ form.hide_google_avatar ? 'Using initials' : 'Using Google profile picture' }})
                                    </span>
                                </div>

                                <!-- Show message if no Google avatar -->
                                <div v-else class="mt-4 text-sm text-gray-500">
                                    <p>No Google avatar available to hide.</p>
                                </div>
                                
                                <p class="mt-2 text-xs text-gray-500">
                                    JPG, PNG or GIF. Max size 2MB.
                                </p>
                                <InputError :message="form.errors.avatar" class="mt-2 text-center" />
                            </div>
                        </div>
                    </div>

                    <!-- Profile Information Tab -->
                    <div v-if="activeTab === 'profile'" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                            <p class="text-sm text-gray-500 mb-6">
                                Update your personal details. Only fill in the fields you want to change.
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- First Name -->
                            <div>
                                <InputLabel for="first_name" value="First Name" class="text-sm font-medium text-gray-700" />
                                <TextInput
                                    id="first_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.first_name"
                                    autocomplete="given-name"
                                    placeholder="Enter your first name"
                                />
                                <p class="mt-1 text-xs text-gray-500">Current: {{ user.first_name }}</p>
                                <InputError class="mt-2" :message="form.errors.first_name" />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <InputLabel for="last_name" value="Last Name" class="text-sm font-medium text-gray-700" />
                                <TextInput
                                    id="last_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.last_name"
                                    autocomplete="family-name"
                                    placeholder="Enter your last name"
                                />
                                <p class="mt-1 text-xs text-gray-500">Current: {{ user.last_name }}</p>
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>

                            <!-- Middle Name -->
                            <div>
                                <InputLabel for="middle_name" value="Middle Name" class="text-sm font-medium text-gray-700" />
                                <TextInput
                                    id="middle_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.middle_name"
                                    autocomplete="additional-name"
                                    placeholder="Enter your middle name"
                                />
                                <p class="mt-1 text-xs text-gray-500">Current: {{ user.middle_name || 'Not set' }}</p>
                                <InputError class="mt-2" :message="form.errors.middle_name" />
                            </div>

                            <!-- Display Name -->
                            <div>
                                <InputLabel for="display_name" value="Display Name" class="text-sm font-medium text-gray-700" />
                                <TextInput
                                    id="display_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.display_name"
                                    autocomplete="nickname"
                                    placeholder="How others see you"
                                    @blur="generateDisplayName"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Current: {{ user.display_name || 'Not set' }}
                                </p>
                                <InputError class="mt-2" :message="form.errors.display_name" />
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <InputLabel for="email" value="Email Address" class="text-sm font-medium text-gray-700" />
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10"
                                        v-model="form.email"
                                        autocomplete="username"
                                        placeholder="you@example.com"
                                    />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <CheckCircleIcon v-if="user.email_verified" class="h-5 w-5 text-green-500" />
                                        <ExclamationCircleIcon v-else class="h-5 w-5 text-yellow-500" />
                                    </div>
                                </div>
                                
                                <!-- Email Verification Status -->
                                <div v-if="mustVerifyEmail && !user.email_verified" class="mt-2">
                                    <p class="text-sm text-yellow-600">
                                        Your email address is unverified.
                                        <Link
                                            :href="route('verification.send')"
                                            method="post"
                                            as="button"
                                            class="ml-1 text-indigo-600 hover:text-indigo-500 font-medium underline"
                                        >
                                            Resend verification email
                                        </Link>
                                    </p>
                                </div>

                                <div
                                    v-show="status === 'verification-link-sent'"
                                    class="mt-2 text-sm font-medium text-green-600 bg-green-50 p-2 rounded-md"
                                >
                                    A new verification link has been sent to your email address.
                                </div>

                                <p class="mt-1 text-xs text-gray-500">Current: {{ user.email }}</p>
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <!-- Phone -->
                            <div>
                                <InputLabel for="phone" value="Phone Number" class="text-sm font-medium text-gray-700" />
                                <TextInput
                                    id="phone"
                                    type="tel"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.phone"
                                    autocomplete="tel"
                                    placeholder="+63 XXX XXX XXXX"
                                />
                                <p class="mt-1 text-xs text-gray-500">Current: {{ user.phone || 'Not set' }}</p>
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                            <!-- Timezone -->
                            <div>
                                <InputLabel for="timezone" value="Timezone" class="text-sm font-medium text-gray-700" />
                                <select
                                    id="timezone"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.timezone"
                                >
                                    <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                                        {{ tz.label }}
                                    </option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Current: {{ user.timezone }}</p>
                                <InputError class="mt-2" :message="form.errors.timezone" />
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center space-x-3">
                            <PrimaryButton 
                                :disabled="form.processing || !hasChanges"
                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </PrimaryButton>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-if="form.recentlySuccessful"
                                    class="text-sm text-green-600 bg-green-50 px-3 py-2 rounded-md"
                                >
                                    ✓ Changes saved successfully!
                                </p>
                            </Transition>
                        </div>

                        <!-- Reset button -->
                        <SecondaryButton 
                            type="button"
                            @click="resetForm"
                            v-if="hasChanges"
                            class="text-gray-600"
                        >
                            Cancel
                        </SecondaryButton>
                    </div>

                    <!-- Changes Summary -->
                    <div v-if="hasChanges" class="mt-4 p-3 bg-blue-50 rounded-md">
                        <p class="text-sm text-blue-700">
                            <span class="font-medium">Changes to be saved:</span>
                            <span v-if="hasAvatarChanges"> Profile picture,</span>
                            <span v-for="(value, field) in modifiedFields" :key="field">
                                {{ field.replace('_', ' ') }},
                            </span>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Help Card -->
            <div class="mt-6 bg-blue-50 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1 md:flex md:justify-between">
                        <p class="text-sm text-blue-700">
                            <span class="font-medium">Tip:</span> You can update individual fields without filling out the entire form. Only changed fields will be saved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>