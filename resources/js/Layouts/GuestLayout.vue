<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Left side - Branding (hidden on mobile) -->
        <div
            class="hidden lg:flex lg:w-1/2 lg:flex-col lg:items-center lg:justify-center lg:bg-gradient-to-br lg:from-indigo-600 lg:via-purple-600 lg:to-pink-500 lg:p-12"
        >
            <div class="max-w-md text-center text-white">
                <Link href="/" class="mb-8 inline-block">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm"
                    >
                        <ApplicationLogo class="h-12 w-12 fill-current text-white" />
                    </div>
                </Link>
                <h2 class="mb-4 text-4xl font-bold">Help Desk System</h2>
                <p class="text-lg text-indigo-100">
                    Streamline your support operations and deliver exceptional customer
                    service with our comprehensive help desk solution.
                </p>
                <div class="mt-8 flex justify-center gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold">24/7</div>
                        <div class="text-sm text-indigo-200">Support</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold">99%</div>
                        <div class="text-sm text-indigo-200">Uptime</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold">1000+</div>
                        <div class="text-sm text-indigo-200">Tickets</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div
            class="flex w-full flex-col items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:w-1/2 lg:px-8"
        >
            <!-- Mobile logo -->
            <div class="mb-8 lg:hidden">
                <Link href="/">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600"
                    >
                        <ApplicationLogo class="h-10 w-10 fill-current text-white" />
                    </div>
                </Link>
            </div>

            <div
                class="w-full max-w-md rounded-2xl bg-white px-8 py-10 shadow-xl sm:px-10"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
