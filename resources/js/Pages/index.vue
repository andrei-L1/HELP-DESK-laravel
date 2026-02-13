<script setup>
import { Link, Head } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="Welcome" />
    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-700"
                        >
                            <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                        </div>
                        <span class="text-xl font-bold text-gray-900">Help Desk</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-gray-700 hover:text-slate-700 transition-colors"
                        >
                            Sign In
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-md transition-all hover:bg-slate-800 hover:shadow-lg"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-slate-50">
            <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
                <div class="text-center">
                    <div class="mb-6 flex justify-center">
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-700 shadow-xl"
                        >
                            <svg
                                class="h-10 w-10 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>
                    </div>
                    <h1
                        class="text-5xl font-extrabold tracking-tight text-gray-900 sm:text-6xl md:text-7xl"
                    >
                        Streamline Your
                        <span class="text-slate-700">
                            Support Operations
                        </span>
                    </h1>
                    <p
                        class="mx-auto mt-6 max-w-3xl text-xl text-gray-600 sm:text-2xl"
                    >
                        A modern, intuitive help desk system designed to help your team
                        deliver exceptional customer service and manage tickets efficiently.
                    </p>
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Link
                            :href="route('register')"
                            class="w-full rounded-xl bg-slate-700 px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all hover:bg-slate-800 hover:shadow-xl sm:w-auto"
                        >
                            Get Started Free
                        </Link>
                        <Link
                            :href="route('login')"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-8 py-4 text-lg font-semibold text-gray-700 shadow-sm transition-all hover:border-slate-500 hover:bg-gray-50 sm:w-auto"
                        >
                            Sign In
                        </Link>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">
                        No credit card required • Free forever
                    </p>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2
                        class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl"
                    >
                        Everything You Need
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
                        Powerful features to help you manage support tickets, collaborate
                        with your team, and delight your customers.
                    </p>
                </div>

                <div class="mt-16 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div
                        class="group rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:border-slate-300 hover:shadow-lg"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition-transform group-hover:scale-110"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            Lightning Fast
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Create and track tickets in seconds with our streamlined
                            interface designed for speed.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="group rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:border-indigo-300 hover:shadow-lg"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition-transform group-hover:scale-110"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            Team Collaboration
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Assign tickets, add comments, and work together seamlessly with
                            your team members.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="group rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:border-indigo-300 hover:shadow-lg"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition-transform group-hover:scale-110"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            Mobile Friendly
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Access your help desk from anywhere. Works perfectly on phones,
                            tablets, and desktops.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div
                        class="group rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:border-slate-300 hover:shadow-lg"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition-transform group-hover:scale-110"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            Secure & Reliable
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Your data is safe with us. Enterprise-grade security and 99.9%
                            uptime guarantee.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div
                        class="group rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:border-indigo-300 hover:shadow-lg"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition-transform group-hover:scale-110"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            Real-time Analytics
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Track performance with detailed reports and insights into your
                            support operations.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div
                        class="group rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:border-indigo-300 hover:shadow-lg"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition-transform group-hover:scale-110"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
                                />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            Customizable
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Tailor the system to your needs with customizable workflows,
                            categories, and priorities.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="bg-slate-800 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-3">
                    <div class="text-center text-white">
                        <div class="text-5xl font-bold">24/7</div>
                        <div class="mt-2 text-lg text-slate-300">Support Available</div>
                    </div>
                    <div class="text-center text-white">
                        <div class="text-5xl font-bold">99.9%</div>
                        <div class="mt-2 text-lg text-slate-300">Uptime</div>
                    </div>
                    <div class="text-center text-white">
                        <div class="text-5xl font-bold">1000+</div>
                        <div class="mt-2 text-lg text-slate-300">Tickets Managed</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-white py-24">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                    Ready to Transform Your Support?
                </h2>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600">
                    Join thousands of teams using our help desk system to deliver
                    exceptional customer service. Get started in minutes.
                </p>
                <div class="mt-10">
                    <Link
                        :href="route('register')"
                        class="inline-block rounded-xl bg-slate-700 px-10 py-4 text-lg font-semibold text-white shadow-lg transition-all hover:bg-slate-800 hover:shadow-xl"
                    >
                        Start Free Trial
                    </Link>
                </div>
                <p class="mt-4 text-sm text-gray-500">
                    No credit card required • Setup in 5 minutes • Cancel anytime
                </p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-700"
                        >
                            <ApplicationLogo class="h-5 w-5 fill-current text-white" />
                        </div>
                        <span class="text-sm font-medium text-gray-600">
                            Help Desk System
                        </span>
                    </div>
                    <div class="flex gap-6 text-sm text-gray-600">
                        <Link :href="route('login')" class="hover:text-slate-700">
                            Sign In
                        </Link>
                        <Link :href="route('register')" class="hover:text-slate-700">
                            Sign Up
                        </Link>
                    </div>
                </div>
                <div class="mt-8 border-t border-gray-200 pt-8 text-center text-sm text-gray-500">
                    <p>&copy; {{ new Date().getFullYear() }} Help Desk System. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>