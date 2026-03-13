<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const showingMobileMenu = ref(false);

const navigation = [
    {
        name: 'Dashboard',
        href: route('user.dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        name: 'My Tickets',
        href: route('user.tickets.index'),
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        name: 'Create Ticket',
        href: route('user.tickets.create'),
        icon: 'M12 4v16m8-8H4',
    },
];

const isActive = (href) => {
    if (href === '#') return false;
    const currentRoute = route().current();
    if (href === route('user.dashboard')) {
        return currentRoute === 'user.dashboard';
    }
    if (href === route('user.tickets.index')) {
        return currentRoute === 'user.tickets.index';
    }
    if (href === route('user.tickets.create')) {
        return currentRoute === 'user.tickets.create';
    }
    return false;
};

const user = computed(() => page.props.auth?.user || {});

// Get user initials for avatar
const userInitials = computed(() => {
    const firstName = user.value?.first_name?.[0] || '';
    const lastName = user.value?.last_name?.[0] || '';
    return (firstName + lastName).toUpperCase() || user.value?.name?.[0]?.toUpperCase() || 'U';
});
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="hidden w-64 flex-col border-r border-gray-200 bg-white lg:flex">
            <!-- Logo -->
            <div class="flex h-16 items-center border-b border-gray-200 px-6">
                <Link :href="route('user.dashboard')" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                        <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900">User Portal</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 py-6">
                <ul class="space-y-1">
                    <li v-for="item in navigation" :key="item.name">
                        <Link
                            :href="item.href"
                            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                            :class="
                                isActive(item.href)
                                    ? 'bg-blue-50 text-blue-700'
                                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                            "
                        >
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            <span>{{ item.name }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- User Section -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                        {{ userInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ user.value?.first_name }} {{ user.value?.last_name }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ user.value?.email }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top Navigation Bar -->
            <header class="border-b border-gray-200 bg-white">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button @click="showingMobileMenu = !showingMobileMenu" class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!showingMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div class="flex-1">
                        <slot name="header-title">
                            <h1 class="text-xl font-semibold text-gray-900">
                                User Dashboard
                            </h1>
                        </slot>
                    </div>

                    <!-- Right side actions -->
                    <div class="flex items-center gap-4">
                        <!-- User Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 rounded-lg p-2 text-sm hover:bg-gray-100">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-semibold text-white">
                                        {{ userInitials }}
                                    </div>
                                    <span class="hidden font-medium text-gray-700 sm:inline">
                                        {{ user.value?.first_name }} {{ user.value?.last_name }}
                                    </span>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile Settings
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Mobile Sidebar -->
            <div v-show="showingMobileMenu" class="fixed inset-0 z-50 lg:hidden" @click="showingMobileMenu = false">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75" />
                <aside class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl" @click.stop>
                    <div class="flex h-16 items-center border-b border-gray-200 px-6">
                        <Link :href="route('user.dashboard')" class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                                <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                            </div>
                            <span class="text-lg font-bold text-gray-900">User Portal</span>
                        </Link>
                    </div>
                    <nav class="overflow-y-auto px-4 py-6">
                        <ul class="space-y-1">
                            <li v-for="item in navigation" :key="item.name">
                                <Link
                                    :href="item.href"
                                    class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                                    :class="
                                        isActive(item.href)
                                            ? 'bg-blue-50 text-blue-700'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                                    "
                                >
                                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                    </svg>
                                    <span>{{ item.name }}</span>
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </aside>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <slot />
            </main>
        </div>
    </div>
</template>
