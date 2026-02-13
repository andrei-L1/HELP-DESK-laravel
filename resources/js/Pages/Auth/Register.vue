<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const form = useForm({
    username: '',
    first_name: '',
    last_name: '',
    middle_name: '',
    display_name: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const currentStep = ref(1);
const totalSteps = 2;

const stepTitles = {
    1: 'Account',
    2: 'Profile',
};

// If server returns errors, jump to the step that contains the first error
const stepFields = {
    1: ['username', 'email', 'password', 'password_confirmation'],
    2: ['first_name', 'last_name', 'middle_name', 'display_name', 'phone'],
};

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
    currentStep.value = Math.min(currentStep.value + 1, totalSteps);
};

const back = () => {
    currentStep.value = Math.max(currentStep.value - 1, 1);
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="w-full">
            <!-- Step indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <template v-for="step in totalSteps" :key="step">
                        <div class="flex items-center">
                            <button
                                type="button"
                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold transition-colors"
                                :class="
                                    currentStep === step
                                        ? 'bg-indigo-600 text-white'
                                        : currentStep > step
                                          ? 'bg-indigo-100 text-indigo-600'
                                          : 'bg-gray-200 text-gray-500'
                                "
                                @click="currentStep = step"
                            >
                                {{ step }}
                            </button>
                            <span
                                class="ml-2 hidden text-sm font-medium text-gray-600 sm:inline"
                            >
                                {{ stepTitles[step] }}
                            </span>
                            <span
                                v-if="step < totalSteps"
                                class="mx-2 h-0.5 w-8 flex-1 bg-gray-200 sm:mx-4 sm:w-12"
                            />
                        </div>
                    </template>
                </div>
                <p class="mt-2 text-center text-sm text-gray-500">
                    Step {{ currentStep }} of {{ totalSteps }}
                </p>
            </div>

            <form @submit.prevent="currentStep < totalSteps ? next() : submit()">
                <!-- Step 1: Account -->
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
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>
                </div>

                <!-- Step 2: Profile -->
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

                <!-- Buttons -->
                <div class="mt-8 flex items-center justify-between">
                    <div>
                        <Link
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
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            @click="back"
                        >
                            Back
                        </button>
                        <PrimaryButton
                            type="submit"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{ currentStep < totalSteps ? 'Next' : 'Create account' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
