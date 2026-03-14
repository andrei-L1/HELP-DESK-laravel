<!-- resources/js/Layouts/ManagerLayout.vue -->
<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const showingMobileMenu = ref(false);
const expandedMenus = ref({});
const searchQuery = ref('');
const isSearchFocused = ref(false);
const avatarError = ref(false);
const isNavigating = ref(false);

// ────────────────────────────────────────────────
// Load / Save expanded state
// ────────────────────────────────────────────────

onMounted(async () => {
    const saved = localStorage.getItem('manager_expanded_menus');
    if (saved) {
        try {
            expandedMenus.value = JSON.parse(saved);
        } catch (e) {
            console.error('Failed to parse saved menu state', e);
        }
    }

    await nextTick();
    autoExpandActiveMenus();
    
    // Add keyboard shortcut for mobile menu (ESC key)
    document.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscapeKey);
});

const handleEscapeKey = (e) => {
    if (e.key === 'Escape' && showingMobileMenu.value) {
        showingMobileMenu.value = false;
    }
};

const autoExpandActiveMenus = () => {
    navigation.forEach(item => {
        if (item.children) {
            const isAnyChildActive = item.children.some(child =>
                isChildActive(child)
            );
            if (isAnyChildActive) {
                expandedMenus.value[item.name] = true;
            }
        }
    });
};

watch(expandedMenus, (newVal) => {
    try {
        localStorage.setItem('manager_expanded_menus', JSON.stringify(newVal));
    } catch (e) {
        console.error('Failed to save menu state', e);
    }
}, { deep: true });

watch(() => page.url, () => {
    nextTick(autoExpandActiveMenus);
    // Close mobile menu on navigation
    showingMobileMenu.value = false;
}, { immediate: true });

// ────────────────────────────────────────────────
// Navigation structure
// ────────────────────────────────────────────────

const navigation = [
    {
        name: 'Dashboard',
        href: route('manager.dashboard'),
        routeName: 'manager.dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        description: 'Overview of your manager dashboard'
    },
    {
        name: 'Tickets',
        href: route('manager.tickets.index'),
        routeName: 'manager.tickets.index',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        description: 'Manage and track tickets',
        children: [
            { name: 'All Tickets',       href: route('manager.tickets.all'),       routeName: 'manager.tickets.all'      },
            { name: 'Open Tickets',      href: route('manager.tickets.open'),      routeName: 'manager.tickets.open'     },
            { name: 'Assigned Tickets',  href: route('manager.tickets.assigned'),  routeName: 'manager.tickets.assigned' },
        ],
    },
    {
        name: 'Team',
        href: route('manager.team.index'),
        routeName: 'manager.team.index',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        description: 'Manage your team members',
        children: [
            // { name: 'Team Members', href: route('manager.team.members'), routeName: 'manager.team.members' },
        ],
    },
    {
        name: 'Reports',
        href: route('manager.reports.index'),
        routeName: 'manager.reports.index',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        description: 'Analytics and performance metrics',
        children: [
            { name: 'Overview',          href: route('manager.reports.index'),           routeName: 'manager.reports.index',     query: null          },
            { name: 'Performance',       href: route('manager.reports.index', { type: 'performance' }), routeName: 'manager.reports.index',     query: { type: 'performance' } },
            { name: 'Agent Performance', href: route('manager.reports.index', { type: 'agent' }),      routeName: 'manager.reports.index',     query: { type: 'agent' }      },
            { name: 'Resolution Time',   href: route('manager.reports.index', { type: 'resolution' }), routeName: 'manager.reports.index',     query: { type: 'resolution' } },
        ],
    },
    {
        name: 'Settings',
        href: '#',
        routeName: null,
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        description: 'Configure your preferences',
        children: [
            // { name: 'Team Settings', href: route('manager.settings.team'), routeName: 'manager.settings.team' },
            // { name: 'Notification Preferences', href: route('manager.settings.notifications'), routeName: 'manager.settings.notifications' },
            // { name: 'Ticket Settings', href: route('manager.settings.ticket'), routeName: 'manager.settings.ticket' },
        ],
    },
];

// Filter navigation based on search
const filteredNavigation = computed(() => {
    if (!searchQuery.value) return navigation;
    const query = searchQuery.value.toLowerCase();
    return navigation.filter(item => 
        item.name.toLowerCase().includes(query) ||
        item.description?.toLowerCase().includes(query)
    );
});

// ────────────────────────────────────────────────
// Active helpers
// ────────────────────────────────────────────────

const isActive = (routeName) => {
    if (!routeName) return false;
    return route().current(routeName) || route().current(`${routeName}.*`);
};

const isChildActive = (child) => {
    if (!child.routeName) return false;

    const currentParams = route().params || {};
    const currentQuery  = route().query  || {};

    // Exact route match or wildcard
    const routeMatches = isActive(child.routeName);

    // For reports – check query param 'type'
    if (child.query && child.query.type) {
        return routeMatches && currentQuery.type === child.query.type;
    }

    return routeMatches;
};

const isParentActive = (item) => {
    if (item.routeName && isActive(item.routeName)) return true;
    if (item.children) {
        return item.children.some(isChildActive);
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
    const last  = user.value?.last_name?.[0]  || '';
    return (first + last).toUpperCase() || user.value?.name?.[0]?.toUpperCase() || 'M';
});

const handleImageError = (e) => {
    avatarError.value = true;
    e.target.style.display = 'none';
    e.target.parentElement.querySelector('span')?.classList.remove('hidden');
};

const handleImageLoad = () => {
    avatarError.value = false;
};

// Reset error when avatar changes
watch(userAvatar, () => {
    avatarError.value = false;
});

// Tooltip helper
const showTooltip = ref(null);
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Desktop Sidebar -->
        <aside 
            class="hidden w-64 flex-col border-r border-gray-200 bg-white lg:flex transition-all duration-300"
            :class="{ 'shadow-lg': showingMobileMenu }"
        >
            <!-- Logo with hover effect -->
            <div class="flex h-16 items-center border-b border-gray-200 px-6 group">
                <Link :href="route('manager.dashboard')" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 transition-transform group-hover:scale-105 group-hover:shadow-md">
                        <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900 group-hover:text-emerald-700 transition-colors">Manager Portal</span>
                </Link>
            </div>

            <!-- Search Bar -->
            <div class="px-4 pt-4">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search menu..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 pl-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-all"
                        @focus="isSearchFocused = true"
                        @blur="isSearchFocused = false"
                    />
                    <svg
                        class="absolute left-3 top-2.5 h-4 w-4 text-gray-400"
                        :class="{ 'text-emerald-500': isSearchFocused }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation with smooth scrolling -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent hover:scrollbar-thumb-gray-400">
                <ul class="space-y-1">
                    <li v-for="item in filteredNavigation" :key="item.name">
                        <div class="relative flex items-center">
                            <Link
                                :href="item.href"
                                class="group flex flex-1 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
                                :class="{
                                    'bg-emerald-50 text-emerald-900 font-semibold shadow-sm': isParentActive(item),
                                    'text-gray-700 hover:bg-gray-50 hover:text-gray-900 hover:shadow-sm': !isParentActive(item)
                                }"
                                @click="isNavigating = true"
                            >
                                <svg
                                    class="h-5 w-5 flex-shrink-0 transition-transform group-hover:scale-110"
                                    :class="{ 
                                        'text-emerald-900': isParentActive(item), 
                                        'text-gray-500': !isParentActive(item)
                                    }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span class="flex-1">{{ item.name }}</span>
                            </Link>

                            <!-- Tooltip on hover (for truncated items) -->
                            <div
                                v-if="item.description"
                                class="absolute left-full ml-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-50"
                            >
                                {{ item.description }}
                            </div>

                            <!-- Chevron for children -->
                            <button
                                v-if="item.children?.length"
                                @click.stop="toggleMenu(item.name, $event)"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all hover:scale-110"
                                :class="{ 'text-emerald-700 rotate-180': isMenuExpanded(item.name) }"
                                :aria-label="isMenuExpanded(item.name) ? 'Collapse' : 'Expand'"
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

                        <!-- Submenu with improved animation -->
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
                                        class="block rounded-lg px-3 py-2 text-sm transition-all hover:translate-x-1"
                                        :class="{
                                            'bg-emerald-50 text-emerald-900 font-medium': isChildActive(child),
                                            'text-gray-600 hover:bg-gray-50 hover:text-gray-900': !isChildActive(child)
                                        }"
                                    >
                                        {{ child.name }}
                                    </Link>
                                </li>
                            </ul>
                        </transition>
                    </li>
                    
                    <!-- No results message -->
                    <li v-if="filteredNavigation.length === 0" class="px-3 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No menu items found</p>
                        <p class="text-xs text-gray-400">Try a different search term</p>
                    </li>
                </ul>
            </nav>

            <!-- User Section with improved styling -->
            <div class="border-t border-gray-200 p-4 bg-gradient-to-b from-white to-gray-50">
                <div class="flex items-center gap-3">
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-emerald-700 text-sm font-semibold text-white overflow-hidden ring-2 ring-emerald-200">
                        <img 
                            v-if="userAvatar && !avatarError"
                            :src="userAvatar" 
                            :alt="user?.first_name"
                            class="h-full w-full object-cover"
                            @error="handleImageError"
                            @load="handleImageLoad"
                            ref="avatarImg"
                        />
                        <span v-else>{{ userInitials }}</span>
                        
                        <!-- Online status indicator -->
                        <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-400 ring-2 ring-white"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate flex items-center gap-1">
                            {{ user?.first_name }} {{ user?.last_name || '' }}
                            <svg v-if="user?.verified" class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ user?.email }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile menu overlay with improved transitions -->
        <transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="showingMobileMenu"
                class="fixed inset-0 z-50 lg:hidden"
                @click="showingMobileMenu = false"
            >
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75 backdrop-blur-sm" />
                <aside
                    class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl overflow-y-auto transform transition-transform duration-300 ease-out"
                    :class="showingMobileMenu ? 'translate-x-0' : '-translate-x-full'"
                    @click.stop
                >
                    <!-- Mobile menu content -->
                    <div class="flex h-16 items-center border-b border-gray-200 px-6">
                        <Link :href="route('manager.dashboard')" class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700">
                                <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                            </div>
                            <span class="text-lg font-bold text-gray-900">Manager Portal</span>
                        </Link>
                    </div>

                    <!-- Mobile menu items -->
                    <nav class="px-4 py-6">
                        <ul class="space-y-2">
                            <li v-for="item in navigation" :key="item.name">
                                <div class="relative flex items-center">
                                    <Link
                                        :href="item.href"
                                        class="group flex flex-1 items-center gap-4 rounded-lg px-4 py-3 text-base font-medium transition-colors"
                                        :class="{
                                            'bg-emerald-50 text-emerald-900 font-semibold': isParentActive(item),
                                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900': !isParentActive(item)
                                        }"
                                        @click="showingMobileMenu = false"
                                    >
                                        <svg
                                            class="h-6 w-6 flex-shrink-0"
                                            :class="{ 'text-emerald-900': isParentActive(item), 'text-gray-500': !isParentActive(item) }"
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
                                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                        :class="{ 'text-emerald-700': isMenuExpanded(item.name) }"
                                    >
                                        <svg
                                            class="h-5 w-5 transition-transform duration-200"
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
                                        class="mt-2 space-y-2 pl-12"
                                    >
                                        <li v-for="child in item.children" :key="child.name">
                                            <Link
                                                :href="child.href"
                                                class="block rounded-lg px-4 py-2.5 text-base transition-colors"
                                                :class="{
                                                    'bg-emerald-50 text-emerald-900 font-medium': isChildActive(child),
                                                    'text-gray-600 hover:bg-gray-50 hover:text-gray-900': !isChildActive(child)
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
        </transition>

        <!-- Main content area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <header class="border-b border-gray-200 bg-white sticky top-0 z-10">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button
                        @click="showingMobileMenu = !showingMobileMenu"
                        class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 lg:hidden transition-all hover:scale-105 active:scale-95"
                        :aria-label="showingMobileMenu ? 'Close menu' : 'Open menu'"
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
                            <h1 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                                <span>Manager Dashboard</span>
                                <span v-if="isNavigating" class="inline-block h-2 w-2 animate-ping rounded-full bg-emerald-600"></span>
                            </h1>
                        </slot>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Quick actions -->
                        <button class="hidden sm:block rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all hover:scale-110">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </button>
                        
                        <button class="hidden sm:block rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all hover:scale-110">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>

                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 rounded-lg p-2 text-sm hover:bg-gray-100 transition-all hover:scale-105 active:scale-95">
                                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-emerald-700 text-sm font-semibold text-white overflow-hidden ring-2 ring-emerald-200">
                                        <img 
                                            v-if="userAvatar && !avatarError"
                                            :src="userAvatar" 
                                            :alt="user?.first_name"
                                            class="h-full w-full object-cover"
                                            @error="handleImageError"
                                            @load="handleImageLoad"
                                            ref="avatarImg"
                                        />
                                        <span v-else>{{ userInitials }}</span>
                                        <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-400 ring-2 ring-white"></span>
                                    </div>
                                    <span class="hidden font-medium text-gray-700 sm:inline">
                                        {{ user?.first_name }} {{ user?.last_name || '' }}
                                    </span>
                                    <svg
                                        class="ml-1 h-4 w-4 text-gray-400 transition-transform group-hover:rotate-180"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-3 border-b border-gray-100 sm:hidden">
                                    <p class="text-sm font-medium text-gray-900">{{ user?.first_name }} {{ user?.last_name || '' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Profile Settings
                                    </div>
                                </DropdownLink>
                                <DropdownLink :href="route('user.dashboard')">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        User Dashboard
                                    </div>
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    <div class="flex items-center gap-2 text-red-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Log Out
                                    </div>
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Breadcrumb navigation (optional) -->
            <nav class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-2 text-sm" v-if="$slots.breadcrumb">
                <slot name="breadcrumb" />
            </nav>

            <!-- Main content with smooth scrolling -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                <div class="mx-auto">
                    <!-- Loading indicator -->
                    <div v-if="isNavigating" class="fixed top-4 right-4 z-50 animate-pulse bg-emerald-600 text-white px-4 py-2 rounded-lg shadow-lg">
                        Loading...
                    </div>
                    
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar styles */
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: #d1d5db;
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background-color: #9ca3af;
}

/* Smooth transitions for all interactive elements */
button, a {
    transition: all 0.2s ease-in-out;
}

/* Focus styles for accessibility */
button:focus-visible, a:focus-visible {
    outline: 2px solid #10b981;
    outline-offset: 2px;
}

/* Mobile menu backdrop blur */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
}

/* Loading animation */
@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Tooltip animation */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.group-hover\:block:hover + .group-hover\:block {
    animation: slideIn 0.2s ease-out;
}
</style>