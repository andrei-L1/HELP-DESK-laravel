<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const showingMobileMenu = ref(false);
const expandedMenus = ref({});
const avatarError = ref(false);

// ────────────────────────────────────────────────
// Load / Save expanded state
// ────────────────────────────────────────────────

onMounted(async () => {
    const saved = localStorage.getItem('admin_expanded_menus');
    if (saved) {
        try {
            expandedMenus.value = JSON.parse(saved);
        } catch (e) {
            console.error('Failed to parse saved menu state', e);
        }
    }

    await nextTick();
    autoExpandActiveMenus();
});

const autoExpandActiveMenus = () => {
    navigation.forEach(item => {
        if (item.children) {
            const isAnyChildActive = item.children.some(child =>
                route().current(child.route) || route().current(`${child.route}.*`)
            );
            if (isAnyChildActive) {
                expandedMenus.value[item.name] = true;
            }
        }
    });
};

watch(expandedMenus, (newVal) => {
    try {
        localStorage.setItem('admin_expanded_menus', JSON.stringify(newVal));
    } catch (e) {
        console.error('Failed to save menu state', e);
    }
}, { deep: true });

watch(() => page.url, () => {
    nextTick(autoExpandActiveMenus);
}, { immediate: true });

// ────────────────────────────────────────────────
// Navigation data
// ────────────────────────────────────────────────

const navigation = [
    {
        name: 'Dashboard',
        route: 'admin.dashboard',
        href: route('admin.dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        name: 'Tickets',
        route: 'admin.tickets.index',
        href: route('admin.tickets.index'),
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        children: [
            { name: 'All Tickets',     route: 'admin.tickets.all',      href: route('admin.tickets.all')     },
            { name: 'Open Tickets',    route: 'admin.tickets.open',     href: route('admin.tickets.open')    },
            { name: 'Assigned to Me',  route: 'admin.tickets.assigned', href: route('admin.tickets.assigned') },
        ],
    },
    {
        name: 'Users',
        route: 'admin.users.index',
        href: route('admin.users.index'),
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        children: [
            { name: 'All Users',   route: 'admin.users.all',   href: route('admin.users.all')   },
            { name: 'User Roles',  route: 'admin.users.roles', href: route('admin.users.roles') },
        ],
    },
    {
        name: 'Departments',
        route: 'admin.departments.index',
        href: route('admin.departments.index'),
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    },
    {
        name: 'Settings',
        route: 'admin.settings.index',
        href: route('admin.settings.index'),
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        children: [
            { name: 'General',        route: 'admin.settings.general', href: route('admin.settings.general') },
            { name: 'Ticket Settings', route: 'admin.settings.ticket',  href: route('admin.settings.ticket')  },
            { name: 'Email Settings',  route: 'admin.settings.email',   href: route('admin.settings.email')   },
            { name: 'SLA Policies',    route: 'admin.settings.sla',     href: route('admin.settings.sla')     },
        ],
    },
];

// ────────────────────────────────────────────────
// Active state helpers
// ────────────────────────────────────────────────

const isActive = (routeName) => {
    if (!routeName) return false;
    return route().current(routeName) || route().current(`${routeName}.*`);
};

const isParentActive = (item) => {
    if (isActive(item.route)) return true;
    if (item.children) {
        return item.children.some(child => isActive(child.route));
    }
    return false;
};

const isMenuExpanded = (menuName) => !!expandedMenus.value[menuName];

const toggleMenu = (menuName, event) => {
    event.preventDefault();
    event.stopPropagation();
    expandedMenus.value[menuName] = !expandedMenus.value[menuName];
};

// ────────────────────────────────────────────────
// User and Avatar handling
// ────────────────────────────────────────────────

const user = computed(() => page.props.auth?.user || {});

const userAvatar = computed(() => {
    return user.value?.avatar_url || null;
});

const userInitials = computed(() => {
    const first = user.value?.first_name?.[0] || '';
    const last = user.value?.last_name?.[0] || '';
    const name = user.value?.name?.[0] || '';
    return (first + last).toUpperCase() || name.toUpperCase() || 'A';
});

const handleImageError = (e) => {
    avatarError.value = true;
    e.target.style.display = 'none';
};

const handleImageLoad = () => {
    avatarError.value = false;
};

// Reset error when avatar changes
watch(userAvatar, () => {
    avatarError.value = false;
});
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Desktop Sidebar -->
        <aside class="hidden w-64 flex-col border-r border-gray-200 bg-white lg:flex">
            <!-- Logo -->
            <div class="flex h-16 items-center border-b border-gray-200 px-6">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-700">
                        <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900">Admin Panel</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 py-6">
                <ul class="space-y-1">
                    <li v-for="item in navigation" :key="item.name">
                        <div class="relative flex items-center">
                            <Link
                                :href="item.href"
                                class="group flex flex-1 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                                :class="{
                                    'bg-slate-100 text-slate-900 font-semibold': isParentActive(item),
                                    'text-gray-700 hover:bg-gray-50 hover:text-gray-900': !isParentActive(item)
                                }"
                            >
                                <svg
                                    class="h-5 w-5 flex-shrink-0"
                                    :class="{ 'text-slate-900': isParentActive(item), 'text-gray-500': !isParentActive(item) }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span>{{ item.name }}</span>
                            </Link>

                            <button
                                v-if="item.children?.length"
                                @click.stop="toggleMenu(item.name, $event)"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                                :class="{ 'text-slate-600': isMenuExpanded(item.name) }"
                                :aria-label="isMenuExpanded(item.name) ? 'Collapse' : 'Expand'"
                                aria-expanded="isMenuExpanded(item.name)"
                            >
                                <svg
                                    class="h-4 w-4 transition-transform duration-200"
                                    :class="{ 'rotate-180': isMenuExpanded(item.name) }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

                        <transition
                            enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <ul
                                v-if="item.children?.length && isMenuExpanded(item.name)"
                                class="mt-1 space-y-1 pl-11"
                            >
                                <li v-for="child in item.children" :key="child.name">
                                    <Link
                                        :href="child.href"
                                        class="block rounded-lg px-3 py-2 text-sm transition-colors"
                                        :class="{
                                            'bg-slate-100 text-slate-900 font-medium': isActive(child.route),
                                            'text-gray-600 hover:bg-gray-50 hover:text-gray-900': !isActive(child.route)
                                        }"
                                    >
                                        {{ child.name }}
                                    </Link>
                                </li>
                            </ul>
                        </transition>
                    </li>
                </ul>
            </nav>

            <!-- User info with avatar -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="relative flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white overflow-hidden">
                        <!-- Avatar Image -->
                        <img 
                            v-if="userAvatar && !avatarError"
                            :src="userAvatar" 
                            :alt="user?.first_name"
                            class="absolute inset-0 h-full w-full object-cover"
                            @error="handleImageError"
                            @load="handleImageLoad"
                        />
                        <!-- Initials Fallback -->
                        <span v-else class="relative z-10">
                            {{ userInitials }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ user?.first_name }} {{ user?.last_name || '' }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ user?.email }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile menu overlay + sidebar -->
        <div
            v-show="showingMobileMenu"
            class="fixed inset-0 z-50 lg:hidden"
            @click="showingMobileMenu = false"
        >
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" />
            <aside
                class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl overflow-y-auto"
                @click.stop
            >
                <div class="flex h-16 items-center border-b border-gray-200 px-6">
                    <Link :href="route('admin.dashboard')" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-700">
                            <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                        </div>
                        <span class="text-lg font-bold text-gray-900">Admin Panel</span>
                    </Link>
                </div>

                <nav class="px-4 py-6">
                    <ul class="space-y-1">
                        <li v-for="item in navigation" :key="item.name">
                            <div class="relative flex items-center">
                                <Link
                                    :href="item.href"
                                    class="group flex flex-1 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                                    :class="{
                                        'bg-slate-100 text-slate-900 font-semibold': isParentActive(item),
                                        'text-gray-700 hover:bg-gray-50 hover:text-gray-900': !isParentActive(item)
                                    }"
                                    @click="showingMobileMenu = false"
                                >
                                    <svg
                                        class="h-5 w-5 flex-shrink-0"
                                        :class="{ 'text-slate-900': isParentActive(item), 'text-gray-500': !isParentActive(item) }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                    </svg>
                                    <span>{{ item.name }}</span>
                                </Link>

                                <button
                                    v-if="item.children?.length"
                                    @click.stop="toggleMenu(item.name, $event)"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                    :class="{ 'text-slate-600': isMenuExpanded(item.name) }"
                                >
                                    <svg
                                        class="h-4 w-4 transition-transform duration-200"
                                        :class="{ 'rotate-180': isMenuExpanded(item.name) }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <transition
                                enter-active-class="transition-all duration-200 ease-out"
                                enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition-all duration-150 ease-in"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-2"
                            >
                                <ul
                                    v-if="item.children?.length && isMenuExpanded(item.name)"
                                    class="mt-1 space-y-1 pl-11"
                                >
                                    <li v-for="child in item.children" :key="child.name">
                                        <Link
                                            :href="child.href"
                                            class="block rounded-lg px-3 py-2 text-sm transition-colors"
                                            :class="{
                                                'bg-slate-100 text-slate-900 font-medium': isActive(child.route),
                                                'text-gray-600 hover:bg-gray-50 hover:text-gray-900': !isActive(child.route)
                                            }"
                                            @click="showingMobileMenu = false"
                                        >
                                            {{ child.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </transition>
                        </li>
                    </ul>
                </nav>
            </aside>
        </div>

        <!-- Main content area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="border-b border-gray-200 bg-white">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button
                        @click="showingMobileMenu = !showingMobileMenu"
                        class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 lg:hidden"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                    <div class="flex-1">
                        <slot name="header-title">
                            <h1 class="text-xl font-semibold text-gray-900">Admin Dashboard</h1>
                        </slot>
                    </div>

                    <div class="flex items-center gap-4">
                        <button class="relative rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                />
                            </svg>
                            <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"></span>
                        </button>

                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 rounded-lg p-2 text-sm hover:bg-gray-100">
                                    <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-slate-700 text-xs font-semibold text-white overflow-hidden">
                                        <!-- Avatar Image -->
                                        <img 
                                            v-if="userAvatar && !avatarError"
                                            :src="userAvatar" 
                                            :alt="user?.first_name"
                                            class="absolute inset-0 h-full w-full object-cover"
                                            @error="handleImageError"
                                            @load="handleImageLoad"
                                        />
                                        <!-- Initials Fallback -->
                                        <span v-else class="relative z-10">
                                            {{ userInitials }}
                                        </span>
                                    </div>
                                    <span class="hidden font-medium text-gray-700 sm:inline">
                                        {{ user?.first_name }} {{ user?.last_name || '' }}
                                    </span>
                                    <svg class="hidden h-4 w-4 text-gray-400 sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile Settings</DropdownLink>
                                <DropdownLink :href="route('user.dashboard')">User Dashboard</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50">
                <slot />
            </main>
        </div>
    </div>
</template>