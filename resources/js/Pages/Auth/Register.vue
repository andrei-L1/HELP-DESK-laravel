<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    departments: { type: Array, default: () => [] },
});

const form = useForm({
    username:              '',
    first_name:            '',
    last_name:             '',
    middle_name:           '',
    display_name:          '',
    phone:                 '',
    email:                 '',
    password:              '',
    password_confirmation: '',
    department_id:         '',
});

const currentStep = ref(1);
const totalSteps  = 3;

const stepTitles = { 1: 'Account', 2: 'Profile', 3: 'Department' };

const stepFields = {
    1: ['username', 'email', 'password', 'password_confirmation'],
    2: ['first_name', 'last_name', 'middle_name', 'display_name', 'phone'],
    3: ['department_id'],
};

// Jump back to the step that has errors
watch(
    () => form.errors,
    (errors) => {
        const keys = Object.keys(errors);
        if (keys.length === 0) return;
        for (let s = 1; s <= totalSteps; s++) {
            if (stepFields[s].some((f) => keys.includes(f))) {
                currentStep.value = s;
                break;
            }
        }
    },
    { deep: true }
);

const next = () => {
    form.clearErrors();

    if (currentStep.value === 1) {
        const required = ['username', 'email', 'password', 'password_confirmation'];
        if (required.some((f) => !form[f]?.trim())) {
            alert('Please fill in all required account fields.');
            return;
        }
        form.post(route('register'), {
            preserveState: true,
            preserveScroll: true,
            only: stepFields[1],
            onSuccess: () => { currentStep.value = 2; },
        });
    } else if (currentStep.value === 2) {
        if (!form.first_name?.trim() || !form.last_name?.trim()) {
            alert('First Name and Last Name are required.');
            return;
        }
        currentStep.value = 3;
    } else {
        submit();
    }
};

const back = () => {
    currentStep.value = Math.max(currentStep.value - 1, 1);
};

const submit = () => {
    form.post(route('register'), {
        preserveState: false,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const redirectToGoogle = () => {
    window.location.href = route('google.redirect');
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="w-full">
            <!-- Header -->
            <div class="mb-6 text-center">
                <div class="mb-2 flex justify-center">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-700 shadow-lg"
                    >
                        <svg
                            class="h-8 w-8 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                            />
                        </svg>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Create Account</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Get started with your free account
                </p>
            </div>

            <!-- Google Sign Up Button -->
            <button
                @click="redirectToGoogle"
                class="mb-6 flex w-full items-center justify-center gap-3 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4"
                    />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853"
                    />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                        fill="#FBBC05"
                    />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        fill="#EA4335"
                    />
                </svg>
                Continue with Google
            </button>

            <!-- Divider -->
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-4 text-gray-500">Or sign up with email</span>
                </div>
            </div>

            <!-- Step Indicator -->
            <div class="mb-8">
                <div class="flex items-center">
                    <template v-for="step in totalSteps" :key="step">
                        <!-- Step bubble -->
                        <button
                            type="button"
                            class="relative flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold transition-all duration-200"
                            :class="{
                                'bg-slate-700 text-white shadow-md ring-2 ring-slate-300': currentStep === step,
                                'bg-green-500 text-white': currentStep > step,
                                'bg-gray-100 text-gray-400': currentStep < step,
                            }"
                            @click="currentStep > step ? (currentStep = step) : null"
                        >
                            <!-- Checkmark for completed -->
                            <svg v-if="currentStep > step" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span v-else>{{ step }}</span>
                        </button>

                        <!-- Label -->
                        <span class="ml-2 hidden text-sm font-medium sm:inline" :class="currentStep === step ? 'text-slate-700' : 'text-gray-400'">
                            {{ stepTitles[step] }}
                        </span>

                        <!-- Connector line -->
                        <div
                            v-if="step < totalSteps"
                            class="mx-3 h-0.5 flex-1 transition-colors duration-300"
                            :class="currentStep > step ? 'bg-green-400' : 'bg-gray-200'"
                        />
                    </template>
                </div>
                <p class="mt-3 text-center text-xs text-gray-400">Step {{ currentStep }} of {{ totalSteps }}</p>
            </div>

            <form @submit.prevent="submit()">

                <!-- ── Step 1: Account ───────────────────────────── -->
                <div v-show="currentStep === 1" class="space-y-5">
                    <div>
                        <InputLabel for="username" value="Username" />
                        <TextInput
                            id="username"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.username"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="johndoe"
                        />
                        <InputError class="mt-2" :message="form.errors.username" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <!-- ── Step 2: Profile ───────────────────────────── -->
                <div v-show="currentStep === 2" class="space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <InputLabel for="first_name" value="First Name" />
                            <TextInput
                                id="first_name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.first_name"
                                required
                                autocomplete="given-name"
                            />
                            <InputError class="mt-2" :message="form.errors.first_name" />
                        </div>

                        <div>
                            <InputLabel for="last_name" value="Last Name" />
                            <TextInput
                                id="last_name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.last_name"
                                required
                                autocomplete="family-name"
                            />
                            <InputError class="mt-2" :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="middle_name" value="Middle Name (optional)" />
                        <TextInput
                            id="middle_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.middle_name"
                            autocomplete="additional-name"
                        />
                        <InputError class="mt-2" :message="form.errors.middle_name" />
                    </div>

                    <div>
                        <InputLabel for="display_name" value="Display Name (optional)" />
                        <TextInput
                            id="display_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.display_name"
                            autocomplete="nickname"
                            placeholder="How you want to be shown"
                        />
                        <InputError class="mt-2" :message="form.errors.display_name" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Phone Number (optional)" />
                        <TextInput
                            id="phone"
                            type="tel"
                            class="mt-1 block w-full"
                            v-model="form.phone"
                            autocomplete="tel"
                            placeholder="+63 912 345 6789"
                        />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>
                </div>

                <!-- ── Step 3: Department ────────────────────────── -->
                <div v-show="currentStep === 3" class="space-y-5">
                    <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3">
                        <p class="text-sm text-blue-700">
                            <span class="font-semibold">Optional.</span>
                            Selecting your department helps route your support tickets to the right team automatically.
                        </p>
                    </div>

                    <!-- No departments available -->
                    <div v-if="departments.length === 0" class="rounded-lg border border-gray-200 py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No departments configured yet.</p>
                        <p class="text-xs text-gray-400">You can be assigned to a department later by an administrator.</p>
                    </div>

                    <!-- Department selection cards -->
                    <div v-else class="space-y-2">
                        <!-- "None" option -->
                        <label
                            for="dept-none"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border p-3.5 transition-colors"
                            :class="form.department_id === '' ? 'border-slate-600 bg-slate-50 ring-1 ring-slate-600' : 'border-gray-200 hover:bg-gray-50'"
                        >
                            <input id="dept-none" v-model="form.department_id" type="radio" value="" class="sr-only" />
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2"
                                :class="form.department_id === '' ? 'border-slate-600 bg-slate-600' : 'border-gray-300'">
                                <svg v-if="form.department_id === ''" class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">I'll choose later</p>
                                <p class="text-xs text-gray-500">Skip for now — an admin can assign you later</p>
                            </div>
                        </label>

                        <!-- Department options -->
                        <label
                            v-for="dept in departments"
                            :key="dept.id"
                            :for="'dept-' + dept.id"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border p-3.5 transition-colors"
                            :class="form.department_id == dept.id ? 'border-slate-600 bg-slate-50 ring-1 ring-slate-600' : 'border-gray-200 hover:bg-gray-50'"
                        >
                            <input :id="'dept-' + dept.id" v-model="form.department_id" type="radio" :value="dept.id" class="sr-only" />
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2"
                                :class="form.department_id == dept.id ? 'border-slate-600 bg-slate-600' : 'border-gray-300'">
                                <svg v-if="form.department_id == dept.id" class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span v-else class="text-xs font-bold text-gray-400">{{ dept.short_code ?? dept.name[0] }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ dept.name }}</p>
                                <p v-if="dept.description" class="text-xs text-gray-500 line-clamp-1">{{ dept.description }}</p>
                            </div>
                        </label>

                        <InputError class="mt-2" :message="form.errors.department_id" />
                    </div>
                </div>

                <!-- ── Navigation Buttons ────────────────────────── -->
                <div class="mt-8 flex items-center justify-between">
                    <div>
                        <Link
                            v-if="currentStep === 1"
                            :href="route('login')"
                            class="text-sm text-gray-600 underline hover:text-gray-900"
                        >
                            Already registered?
                        </Link>
                    </div>

                    <div class="flex gap-3">
                        <button
                            v-if="currentStep > 1"
                            type="button"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                            @click="back"
                        >
                            Back
                        </button>

                        <PrimaryButton
                            type="button"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            @click="next"
                        >
                            <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ currentStep < totalSteps ? 'Next' : (form.processing ? 'Creating…' : 'Create account') }}
                        </PrimaryButton>
                    </div>
                </div>

            </form>

            <!-- Login Link for Mobile (if not shown in nav) -->
            <div class="mt-6 text-center lg:hidden">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <Link
                        :href="route('login')"
                        class="font-semibold text-slate-700 hover:text-slate-900"
                    >
                        Sign in
                    </Link>
                </p>
            </div>

            <!-- Security Note -->
            <p class="mt-6 text-xs text-center text-gray-500">
                By continuing with Google or email, you agree to our 
                <Link href="#" class="text-slate-600 hover:underline">Terms</Link> and 
                <Link href="#" class="text-slate-600 hover:underline">Privacy Policy</Link>
            </p>
        </div>
    </GuestLayout>
</template>