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
        href: route('admin.admindashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        name: 'Tickets',
        href: route('admin.tickets.index'),
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        children: [
            { name: 'All Tickets', href: route('admin.tickets.index') },
            { name: 'Open Tickets', href: '#' },
            { name: 'Assigned to Me', href: '#' },
            { name: 'Ticket Categories', href: '#' },
        ],
    },
    {
        name: 'Users',
        href: '#', // Placeholder - update when route exists
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        children: [
            { name: 'All Users', href: '#' },
            { name: 'Add User', href: '#' },
            { name: 'User Roles', href: '#' },
        ],
    },
    {
        name: 'Departments',
        href: '#', // Placeholder - update when route exists
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    },
    {
        name: 'Settings',
        href: '#', // Placeholder - update when route exists
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        children: [
            { name: 'General', href: '#' },
            { name: 'Ticket Settings', href: '#' },
            { name: 'Email Settings', href: '#' },
            { name: 'SLA Policies', href: '#' },
        ],
    },
];

const isActive = (href) => {
    if (href === '#') return false;
    const currentRoute = route().current();
    if (href === route('admin.admindashboard')) {
        return currentRoute === 'admin.admindashboard';
    }
    if (href === route('admin.tickets.index')) {
        return currentRoute === 'admin.tickets.index';
    }
    return false;
};

const user = computed(() => page.props.auth?.user || {});
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside
            class="hidden w-64 flex-col border-r border-gray-200 bg-white lg:flex"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center border-b border-gray-200 px-6">
                <Link :href="route('admin.admindashboard')" class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-700"
                    >
                        <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900">Admin Panel</span>
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
                                    ? 'bg-slate-100 text-slate-900'
                                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                            "
                        >
                            <svg
                                class="h-5 w-5 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="item.icon"
                                />
                            </svg>
                            <span>{{ item.name }}</span>
                        </Link>
                        <!-- Submenu (if children exist) -->
                        <ul
                            v-if="item.children && item.children.length > 0"
                            class="mt-1 space-y-1 pl-11"
                        >
                            <li v-for="child in item.children" :key="child.name">
                                <Link
                                    :href="child.href"
                                    class="block rounded-lg px-3 py-2 text-sm text-gray-600 transition-colors hover:bg-gray-50 hover:text-gray-900"
                                >
                                    {{ child.name }}
                                </Link>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- User Section -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white"
                    >
                        {{ user.value?.first_name?.[0] || user.value?.name?.[0] || 'A' }}
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
                    <button
                        @click="showingMobileMenu = !showingMobileMenu"
                        class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 lg:hidden"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                v-if="!showingMobileMenu"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <!-- Page Title (can be overridden by slot) -->
                    <div class="flex-1">
                        <slot name="header-title">
                            <h1 class="text-xl font-semibold text-gray-900">
                                Admin Dashboard
                            </h1>
                        </slot>
                    </div>

                    <!-- Right side actions -->
                    <div class="flex items-center gap-4">
                        <!-- Notifications (placeholder) -->
                        <button
                            class="relative rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
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
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                />
                            </svg>
                            <span
                                class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"
                            />
                        </button>

                        <!-- User Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    class="flex items-center gap-3 rounded-lg p-2 text-sm hover:bg-gray-100"
                                >
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-700 text-xs font-semibold text-white"
                                    >
                                        {{ user.value?.first_name?.[0] || user.value?.name?.[0] || 'A' }}
                                    </div>
                                    <span class="hidden font-medium text-gray-700 sm:inline">
                                        {{ user.value?.first_name }} {{ user.value?.last_name }}
                                    </span>
                                    <svg
                                        class="hidden h-4 w-4 text-gray-400 sm:inline"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile Settings
                                </DropdownLink>
                                <DropdownLink :href="route('dashboard')">
                                    User Dashboard
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Mobile Sidebar -->
            <div
                v-show="showingMobileMenu"
                class="fixed inset-0 z-50 lg:hidden"
                @click="showingMobileMenu = false"
            >
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75" />
                <aside
                    class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl"
                    @click.stop
                >
                    <div class="flex h-16 items-center border-b border-gray-200 px-6">
                        <Link
                            :href="route('admin.admindashboard')"
                            class="flex items-center gap-3"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-700"
                            >
                                <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                            </div>
                            <span class="text-lg font-bold text-gray-900">Admin Panel</span>
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
                                            ? 'bg-slate-100 text-slate-900'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                                    "
                                >
                                    <svg
                                        class="h-5 w-5 flex-shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            :d="item.icon"
                                        />
                                    </svg>
                                    <span>{{ item.name }}</span>
                                </Link>
                                <ul
                                    v-if="item.children && item.children.length > 0"
                                    class="mt-1 space-y-1 pl-11"
                                >
                                    <li v-for="child in item.children" :key="child.name">
                                        <Link
                                            :href="child.href"
                                            class="block rounded-lg px-3 py-2 text-sm text-gray-600 transition-colors hover:bg-gray-50 hover:text-gray-900"
                                        >
                                            {{ child.name }}
                                        </Link>
                                    </li>
                                </ul>
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
