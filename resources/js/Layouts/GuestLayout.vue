<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const currentTestimonial = ref(0);
const testimonials = [
    {
        text: "This help desk system transformed how we handle customer support. Response times dropped by 60%.",
        author: "Sarah Johnson",
        role: "Support Manager at TechCorp"
    },
    {
        text: "The most intuitive help desk platform we've ever used. Our team adapted in days, not weeks.",
        author: "Michael Chen",
        role: "CTO at StartupFlow"
    },
    {
        text: "24/7 reliability and exceptional features. It's become essential to our operations.",
        author: "Emma Williams",
        role: "Customer Success Lead"
    }
];

onMounted(() => {
    // Rotate testimonials every 5 seconds
    setInterval(() => {
        currentTestimonial.value = (currentTestimonial.value + 1) % testimonials.length;
    }, 5000);
});
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <ToastNotification />
        <!-- Left side - Enhanced Branding with animated background -->
        <div
            class="relative hidden lg:flex lg:w-1/2 lg:flex-col lg:items-center lg:justify-center lg:overflow-hidden"
        >
            <!-- Animated gradient background -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white blur-3xl animate-pulse"></div>
                    <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-slate-400 blur-3xl animate-pulse delay-1000"></div>
                </div>
            </div>

            <!-- Floating elements animation -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-20 left-10 h-2 w-2 rounded-full bg-white/30 animate-float"></div>
                <div class="absolute bottom-40 right-20 h-3 w-3 rounded-full bg-white/20 animate-float-delayed"></div>
                <div class="absolute top-60 right-40 h-4 w-4 rounded-full bg-white/10 animate-float-slow"></div>
            </div>

            <!-- Main content -->
            <div class="relative z-10 max-w-md text-center text-white px-8">
                <!-- Logo with glow effect -->
                <Link href="/" class="mb-8 inline-block transform transition-transform hover:scale-105">
                    <div
                        class="flex h-24 w-24 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-lg shadow-2xl ring-1 ring-white/20"
                    >
                        <ApplicationLogo class="h-14 w-14 fill-current text-white drop-shadow-lg" />
                    </div>
                </Link>

                <!-- Headline -->
                <h2 class="mb-4 text-4xl font-bold leading-tight">
                    Welcome to 
                    <span class="bg-gradient-to-r from-slate-200 to-white bg-clip-text text-transparent">
                        HelpDesk
                    </span>
                </h2>
                
                <p class="text-lg text-slate-300/90 leading-relaxed">
                    Streamline your support operations and deliver exceptional customer
                    service with our comprehensive help desk solution.
                </p>

                <!-- Stats with animated counters -->
                <div class="mt-10 grid grid-cols-3 gap-4">
                    <div class="rounded-xl bg-white/5 backdrop-blur-sm p-4 ring-1 ring-white/10">
                        <div class="text-3xl font-bold text-white">24/7</div>
                        <div class="text-sm text-slate-400 mt-1">Support</div>
                    </div>
                    <div class="rounded-xl bg-white/5 backdrop-blur-sm p-4 ring-1 ring-white/10">
                        <div class="text-3xl font-bold text-white">99.9%</div>
                        <div class="text-sm text-slate-400 mt-1">Uptime</div>
                    </div>
                    <div class="rounded-xl bg-white/5 backdrop-blur-sm p-4 ring-1 ring-white/10">
                        <div class="text-3xl font-bold text-white">10k+</div>
                        <div class="text-sm text-slate-400 mt-1">Tickets</div>
                    </div>
                </div>

                <!-- Rotating testimonials -->
                <div class="mt-12 relative">
                    <transition 
                        enter-active-class="transition duration-500 ease-out"
                        enter-from-class="opacity-0 translate-y-4"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-300 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-4"
                        mode="out-in"
                    >
                        <div :key="currentTestimonial" class="bg-white/5 backdrop-blur-sm rounded-xl p-6 ring-1 ring-white/10">
                            <p class="text-slate-200 italic">"{{ testimonials[currentTestimonial].text }}"</p>
                            <div class="mt-4">
                                <p class="font-semibold text-white">{{ testimonials[currentTestimonial].author }}</p>
                                <p class="text-sm text-slate-400">{{ testimonials[currentTestimonial].role }}</p>
                            </div>
                        </div>
                    </transition>
                    
                    <!-- Dots indicator -->
                    <div class="flex justify-center gap-2 mt-4">
                        <button 
                            v-for="(_, index) in testimonials" 
                            :key="index"
                            @click="currentTestimonial = index"
                            class="h-1.5 rounded-full transition-all"
                            :class="[
                                currentTestimonial === index 
                                    ? 'w-6 bg-white' 
                                    : 'w-1.5 bg-white/30 hover:bg-white/50'
                            ]"
                        ></button>
                    </div>
                </div>

                <!-- Trust badges -->
                <div class="mt-12 flex justify-center gap-6">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>GDPR Compliant</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        <span>SSL Secure</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Enhanced Form Container -->
        <div
            class="flex w-full flex-col items-center justify-center bg-gradient-to-b from-gray-50 to-white px-4 py-12 sm:px-6 lg:w-1/2 lg:px-8"
        >
            <!-- Mobile header with improved styling -->
            <div class="mb-8 text-center lg:hidden">
                <Link href="/" class="inline-block">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 shadow-lg mb-4"
                    >
                        <ApplicationLogo class="h-12 w-12 fill-current text-white" />
                    </div>
                </Link>
                <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
                <p class="text-sm text-gray-600 mt-1">Sign in to continue to HelpDesk</p>
            </div>

            <!-- Form card with enhanced design -->
            <div
                class="w-full max-w-md transform transition-all duration-300 hover:shadow-2xl"
            >
                <div class="rounded-2xl bg-white px-8 py-10 shadow-xl sm:px-10 ring-1 ring-gray-200">
                    <!-- Header for desktop (hidden on mobile) -->
                    <div class="mb-8 hidden lg:block">
                        <h2 class="text-3xl font-bold text-gray-900">Welcome Back</h2>
                        <p class="text-sm text-gray-600 mt-2">
                            Sign in to your account to continue
                        </p>
                    </div>

                    <!-- Form content -->
                    <slot />

                    <!-- Additional helpful links -->
                    <div class="mt-6 text-center text-sm">
                        <span class="text-gray-600">Having trouble?</span>
                        <Link 
                            href="#" 
                            class="ml-1 font-medium text-slate-700 hover:text-slate-900 transition-colors underline-offset-2 hover:underline"
                        >
                            Contact Support
                        </Link>
                    </div>
                </div>

                <!-- Trust badges for mobile -->
                <div class="mt-6 flex justify-center gap-4 lg:hidden">
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>GDPR Compliant</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        <span>SSL Secure</span>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <p class="mt-8 text-xs text-gray-500 text-center max-w-xs">
                By signing in, you agree to our 
                <Link href="#" class="text-slate-700 hover:underline">Terms</Link> and 
                <Link href="#" class="text-slate-700 hover:underline">Privacy Policy</Link>
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes float {
    0%, 100% {
        transform: translateY(0) translateX(0);
        opacity: 0.3;
    }
    50% {
        transform: translateY(-20px) translateX(10px);
        opacity: 0.6;
    }
}

@keyframes float-slow {
    0%, 100% {
        transform: translateY(0) translateX(0);
        opacity: 0.2;
    }
    50% {
        transform: translateY(-30px) translateX(-15px);
        opacity: 0.4;
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float 7s ease-in-out infinite;
    animation-delay: 1s;
}

.animate-float-slow {
    animation: float-slow 10s ease-in-out infinite;
}

.delay-1000 {
    animation-delay: 1s;
}
</style>