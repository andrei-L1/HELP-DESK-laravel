<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const isScrolled = ref(false);

const dashboardRoute = computed(() => {
    const user = page.props.auth.user;
    if (!user || !user.role) return route('user.dashboard');
    
    switch (user.role) {
        case 'admin': return route('admin.dashboard');
        case 'manager': return route('manager.dashboard');
        case 'agent': return route('agent.dashboard');
        default: return route('user.dashboard');
    }
});

onMounted(() => {
    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 10;
    });
});
</script>

<template>
    <Head title="Welcome - Modern Help Desk" />
    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50">
        <!-- Navigation - Enhanced with scroll effect -->
        <nav 
            class="fixed top-0 z-50 w-full transition-all duration-300"
            :class="[
                isScrolled 
                    ? 'bg-white/95 backdrop-blur-md shadow-lg py-2' 
                    : 'bg-transparent py-4'
            ]"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <!-- Logo with animation -->
                    <div class="flex items-center gap-3 group">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 shadow-lg transition-transform group-hover:scale-110 group-hover:rotate-3"
                        >
                            <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-slate-700 to-slate-900 bg-clip-text text-transparent">
                            HelpDesk
                        </span>
                    </div>

                    <!-- Navigation Links -->
                    <div class="flex items-center gap-4">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="dashboardRoute"
                                class="rounded-xl bg-gradient-to-r from-slate-700 to-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all hover:shadow-xl hover:scale-105 hover:from-slate-800 hover:to-slate-950"
                            >
                                Go to Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="relative text-sm font-medium text-gray-700 after:absolute after:-bottom-1 after:left-0 after:h-0.5 after:w-0 after:bg-slate-700 after:transition-all hover:text-slate-700 hover:after:w-full"
                            >
                                Sign In
                            </Link>
                            <Link
                                :href="route('register')"
                                class="rounded-xl bg-gradient-to-r from-slate-700 to-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all hover:shadow-xl hover:scale-105 hover:from-slate-800 hover:to-slate-950"
                            >
                                Get Started Free
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section - Modern gradient design -->
        <section class="relative overflow-hidden pt-32 pb-24 lg:pt-40 lg:pb-32">
            <!-- Animated background elements -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-20 left-1/4 h-72 w-72 rounded-full bg-slate-200 opacity-20 blur-3xl animate-pulse"></div>
                <div class="absolute bottom-20 right-1/4 h-72 w-72 rounded-full bg-slate-300 opacity-20 blur-3xl animate-pulse delay-1000"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <!-- Animated badge -->
                    <div class="mb-8 inline-flex animate-bounce">
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
                            ✨ New: AI-Powered Features Available
                        </span>
                    </div>

                    <!-- Main heading with gradient -->
                    <h1 class="text-5xl font-extrabold tracking-tight sm:text-6xl md:text-7xl">
                        <span class="block bg-gradient-to-r from-gray-900 via-slate-800 to-slate-700 bg-clip-text text-transparent">
                            Streamline Your
                        </span>
                        <span class="block mt-2 bg-gradient-to-r from-slate-700 to-slate-500 bg-clip-text text-transparent">
                            Support Operations
                        </span>
                    </h1>

                    <!-- Subheading with better readability -->
                    <p class="mx-auto mt-6 max-w-3xl text-lg text-gray-600 sm:text-xl md:text-2xl">
                        Transform customer support with our intelligent ticketing system. 
                        <span class="block mt-2 text-slate-600">70% faster resolution times guaranteed.</span>
                    </p>

                    <!-- CTA Buttons with micro-interactions -->
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="dashboardRoute"
                                class="group relative w-full overflow-hidden rounded-xl bg-gradient-to-r from-slate-700 to-slate-900 px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all hover:shadow-xl hover:scale-105 sm:w-auto"
                            >
                                <span class="relative z-10">Access Dashboard</span>
                                <div class="absolute inset-0 -translate-x-full group-hover:translate-x-0 bg-gradient-to-r from-slate-600 to-slate-800 transition-transform duration-300"></div>
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('register')"
                                class="group relative w-full overflow-hidden rounded-xl bg-gradient-to-r from-slate-700 to-slate-900 px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all hover:shadow-xl hover:scale-105 sm:w-auto"
                            >
                                <span class="relative z-10">Start Free Trial</span>
                                <div class="absolute inset-0 -translate-x-full group-hover:translate-x-0 bg-gradient-to-r from-slate-600 to-slate-800 transition-transform duration-300"></div>
                            </Link>
                            <Link
                                :href="route('login')"
                                class="group relative w-full overflow-hidden rounded-xl border-2 border-gray-300 bg-white/80 backdrop-blur-sm px-8 py-4 text-lg font-semibold text-gray-700 shadow-sm transition-all hover:border-slate-500 hover:shadow-lg sm:w-auto"
                            >
                                <span class="relative z-10">Sign In &rarr;</span>
                            </Link>
                        </template>
                    </div>

                    <!-- Trust indicators -->
                    <div class="mt-8 flex items-center justify-center gap-4 text-sm text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            No credit card required
                        </span>
                        <span class="h-4 w-px bg-gray-300"></span>
                        <span class="flex items-center gap-1">
                            <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            14-day free trial
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section - Card redesign -->
        <section class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Section header with animation -->
                <div class="text-center animate-fade-in-up">
                    <span class="text-sm font-semibold text-slate-600 uppercase tracking-wider">
                        Features
                    </span>
                    <h2 class="mt-2 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Everything You Need to 
                        <span class="text-slate-700">Excel</span>
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
                        Powerful features designed to make support teams more productive and customers happier.
                    </p>
                </div>

                <!-- Feature cards with hover effects -->
                <div class="mt-16 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-slate-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Lightning Fast</h3>
                            <p class="mt-2 text-gray-600 leading-relaxed">
                                Create and resolve tickets in seconds with our optimized workflow and keyboard shortcuts.
                            </p>
                            <div class="mt-4 flex items-center text-sm font-medium text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                Learn more
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-slate-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Team Collaboration</h3>
                            <p class="mt-2 text-gray-600 leading-relaxed">
                                Real-time comments, mentions, and notifications keep everyone in sync.
                            </p>
                            <div class="mt-4 flex items-center text-sm font-medium text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                Learn more
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-slate-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Mobile First</h3>
                            <p class="mt-2 text-gray-600 leading-relaxed">
                                Fully responsive design that works seamlessly on any device, anywhere.
                            </p>
                            <div class="mt-4 flex items-center text-sm font-medium text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                Learn more
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-slate-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Enterprise Security</h3>
                            <p class="mt-2 text-gray-600 leading-relaxed">
                                Bank-level encryption, SSO, and 99.9% uptime SLA included.
                            </p>
                            <div class="mt-4 flex items-center text-sm font-medium text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                Learn more
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-slate-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Advanced Analytics</h3>
                            <p class="mt-2 text-gray-600 leading-relaxed">
                                Real-time dashboards and custom reports to track team performance.
                            </p>
                            <div class="mt-4 flex items-center text-sm font-medium text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                Learn more
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-slate-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.5A1.5 1.5 0 0112.5 2h3A1.5 1.5 0 0117 3.5V4a1 1 0 00-1 1v3a1 1 0 01-1 1h-2a1 1 0 01-1-1V5a1 1 0 00-1-1v.5zM5 7a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Custom Workflows</h3>
                            <p class="mt-2 text-gray-600 leading-relaxed">
                                Automate repetitive tasks and create custom ticket routing rules.
                            </p>
                            <div class="mt-4 flex items-center text-sm font-medium text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                Learn more
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section - Modern design -->
        <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 to-slate-800 py-20">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-white blur-3xl"></div>
            </div>
            
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 md:grid-cols-3">
                    <div class="text-center transform transition-all hover:scale-105">
                        <div class="text-6xl font-bold text-white">24/7</div>
                        <div class="mt-3 text-lg text-slate-300">Support Available</div>
                        <div class="mt-2 text-sm text-slate-400">Round-the-clock assistance</div>
                    </div>
                    <div class="text-center transform transition-all hover:scale-105">
                        <div class="text-6xl font-bold text-white">99.9%</div>
                        <div class="mt-3 text-lg text-slate-300">Uptime</div>
                        <div class="mt-2 text-sm text-slate-400">Enterprise-grade reliability</div>
                    </div>
                    <div class="text-center transform transition-all hover:scale-105">
                        <div class="text-6xl font-bold text-white">10k+</div>
                        <div class="mt-3 text-lg text-slate-300">Happy Customers</div>
                        <div class="mt-2 text-sm text-slate-400">Trusted worldwide</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section - Enhanced -->
        <section class="relative py-24 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-50 to-white"></div>
            <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                    Ready to Transform Your 
                    <span class="text-slate-700">Support Experience?</span>
                </h2>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600">
                    Join thousands of teams who've reduced response times by 70% and increased customer satisfaction scores.
                </p>
                
                <!-- Email capture form -->
                <div class="mt-10 mx-auto max-w-md">
                    <form class="flex flex-col sm:flex-row gap-3">
                        <input 
                            type="email" 
                            placeholder="Enter your work email" 
                            class="flex-1 rounded-xl border border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-500 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                        />
                        <button 
                            type="submit"
                            class="rounded-xl bg-gradient-to-r from-slate-700 to-slate-900 px-6 py-3 font-semibold text-white shadow-lg transition-all hover:shadow-xl hover:scale-105"
                        >
                            Get Started
                        </button>
                    </form>
                </div>

                <p class="mt-4 text-sm text-gray-500 flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                    No credit card required • Free forever plan available
                </p>
            </div>
        </section>

        <!-- Footer - Modern design -->
        <footer class="bg-gray-900 text-gray-300">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <!-- Brand -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-600 to-slate-800">
                                <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                            </div>
                            <span class="text-lg font-bold text-white">HelpDesk</span>
                        </div>
                        <p class="mt-4 text-sm text-gray-400 max-w-xs">
                            Modern help desk software that helps teams deliver exceptional customer support.
                        </p>
                    </div>

                    <!-- Links -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Product</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="#" class="text-sm hover:text-white transition-colors">Features</Link></li>
                            <li><Link href="#" class="text-sm hover:text-white transition-colors">Pricing</Link></li>
                            <li><Link href="#" class="text-sm hover:text-white transition-colors">Security</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Company</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="#" class="text-sm hover:text-white transition-colors">About</Link></li>
                            <li><Link href="#" class="text-sm hover:text-white transition-colors">Blog</Link></li>
                            <li><Link href="#" class="text-sm hover:text-white transition-colors">Contact</Link></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 border-t border-gray-800 pt-8">
                    <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                        <p class="text-sm text-gray-400">
                            &copy; {{ new Date().getFullYear() }} HelpDesk. All rights reserved.
                        </p>
                        <div class="flex gap-6">
                            <Link :href="route('login')" class="text-sm text-gray-400 hover:text-white transition-colors">
                                Sign In
                            </Link>
                            <Link :href="route('register')" class="text-sm text-gray-400 hover:text-white transition-colors">
                                Sign Up
                            </Link>
                            <Link href="#" class="text-sm text-gray-400 hover:text-white transition-colors">
                                Privacy
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out;
}

.delay-1000 {
    animation-delay: 1s;
}
</style>