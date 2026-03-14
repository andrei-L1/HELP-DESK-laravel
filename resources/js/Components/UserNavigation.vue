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

// ────────────────────────────────────────────────
// Load / Save expanded state (future-proof for submenus)
// ────────────────────────────────────────────────

onMounted(async () => {
    const saved = localStorage.getItem('user_expanded_menus');
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
            const isAnyChildActive = item.children.some(child => isActive(child.route));
            if (isAnyChildActive) {
                expandedMenus.value[item.name] = true;
            }
        }
    });
};

watch(expandedMenus, (newVal) => {
    try {
        localStorage.setItem('user_expanded_menus', JSON.stringify(newVal));
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
// Navigation (prepared for children in future)
// ────────────────────────────────────────────────

const navigation = [
    {
        name: 'Dashboard',
        href: route('user.dashboard'),
        route: 'user.dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        description: 'Overview of your account'
    },
    {
        name: 'My Tickets',
        href: route('user.tickets.index'),
        route: 'user.tickets.index',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        badge: 3, // Example: Could be computed from actual data
        badgeColor: 'bg-red-500'
    },
    {
        name: 'Create Ticket',
        href: route('user.tickets.create'),
        route: 'user.tickets.create',
        icon: 'M12 4v16m8-8H4',
        highlight: true
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

const user = computed(() => page.props.auth?.user || {});

const userInitials = computed(() => {
    const first = user.value?.first_name?.[0] || '';
    const last  = user.value?.last_name?.[0]  || '';
    return (first + last).toUpperCase() || user.value?.name?.[0]?.toUpperCase() || 'U';
});

const userAvatar = computed(() => {
    return user.value?.avatar_url || null;
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    e.target.parentElement.querySelector('span')?.classList.remove('hidden');
};

const impersonation = computed(() => page.props.impersonation);
const isImpersonating = computed(() => impersonation.value !== null);

// Tooltip helper
const showTooltip = ref(null);
const setTooltip = (item, event) => {
    // Implementation for tooltips on hover
};

// Loading state for navigation
const isNavigating = ref(false);
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
                <Link :href="route('user.dashboard')" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 transition-transform group-hover:scale-105 group-hover:shadow-md">
                        <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">User Portal</span>
                </Link>
            </div>

            <!-- Search Bar -->
            <div class="px-4 pt-4">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search menu..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 pl-10 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all"
                        @focus="isSearchFocused = true"
                        @blur="isSearchFocused = false"
                    />
                    <svg
                        class="absolute left-3 top-2.5 h-4 w-4 text-gray-400"
                        :class="{ 'text-blue-500': isSearchFocused }"
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
                                    'bg-blue-50 text-blue-700 font-semibold shadow-sm': isParentActive(item),
                                    'text-gray-700 hover:bg-gray-50 hover:text-gray-900 hover:shadow-sm': !isParentActive(item),
                                    'border-2 border-blue-200 bg-blue-50/50': item.highlight
                                }"
                                @click="isNavigating = true"
                            >
                                <svg
                                    class="h-5 w-5 flex-shrink-0 transition-transform group-hover:scale-110"
                                    :class="{ 
                                        'text-blue-700': isParentActive(item), 
                                        'text-gray-500': !isParentActive(item),
                                        'text-blue-500': item.highlight
                                    }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span class="flex-1">{{ item.name }}</span>
                                
                                <!-- Badge for notifications -->
                                <span
                                    v-if="item.badge"
                                    class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white rounded-full"
                                    :class="item.badgeColor || 'bg-gray-500'"
                                >
                                    {{ item.badge }}
                                </span>
                            </Link>

                            <!-- Tooltip on hover (for truncated items) -->
                            <div
                                v-if="item.description"
                                class="absolute left-full ml-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-50"
                            >
                                {{ item.description }}
                            </div>

                            <!-- Chevron – only appears if children added in future -->
                            <button
                                v-if="item.children?.length"
                                @click.stop="toggleMenu(item.name, $event)"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all hover:scale-110"
                                :class="{ 'text-blue-700 rotate-180': isMenuExpanded(item.name) }"
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
                                            'bg-blue-50 text-blue-700 font-medium': isActive(child.route),
                                            'text-gray-600 hover:bg-gray-50 hover:text-gray-900': !isActive(child.route)
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
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white overflow-hidden ring-2 ring-blue-200">
                        <img 
                            v-if="userAvatar"
                            :src="userAvatar" 
                            :alt="user?.first_name"
                            class="h-full w-full object-cover"
                            @error="handleImageError"
                            ref="avatarImg"
                        />
                        <span v-else>{{ userInitials }}</span>
                        
                        <!-- Online status indicator -->
                        <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-400 ring-2 ring-white"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate flex items-center gap-1">
                            {{ user?.first_name }} {{ user?.last_name || '' }}
                            <svg v-if="user?.verified" class="h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
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
                    <!-- Mobile menu content (similar to desktop but with mobile optimizations) -->
                    <div class="flex h-16 items-center border-b border-gray-200 px-6">
                        <Link :href="route('user.dashboard')" class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                                <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                            </div>
                            <span class="text-lg font-bold text-gray-900">User Portal</span>
                        </Link>
                    </div>

                    <!-- Mobile menu items (same as desktop but with touch-friendly sizing) -->
                    <nav class="px-4 py-6">
                        <ul class="space-y-2">
                            <li v-for="item in navigation" :key="item.name">
                                <div class="relative flex items-center">
                                    <Link
                                        :href="item.href"
                                        class="group flex flex-1 items-center gap-4 rounded-lg px-4 py-3 text-base font-medium transition-colors"
                                        :class="{
                                            'bg-blue-50 text-blue-700 font-semibold': isParentActive(item),
                                            'text-gray-700 hover:bg-gray-50 hover:text-gray-900': !isParentActive(item)
                                        }"
                                        @click="showingMobileMenu = false"
                                    >
                                        <svg
                                            class="h-6 w-6 flex-shrink-0"
                                            :class="{ 'text-blue-700': isParentActive(item), 'text-gray-500': !isParentActive(item) }"
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
                                        :class="{ 'text-blue-700': isMenuExpanded(item.name) }"
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
                                                    'bg-blue-50 text-blue-700 font-medium': isActive(child.route),
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
                                <span>User Dashboard</span>
                                <span v-if="isNavigating" class="inline-block h-2 w-2 animate-ping rounded-full bg-blue-600"></span>
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
                                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white overflow-hidden ring-2 ring-blue-200">
                                        <img 
                                            v-if="userAvatar"
                                            :src="userAvatar" 
                                            :alt="user?.first_name"
                                            class="h-full w-full object-cover"
                                            @error="handleImageError"
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

                <!-- Impersonation banner with animation -->
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-full"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-full"
                >
                    <div
                        v-if="isImpersonating"
                        class="bg-gradient-to-r from-red-600 to-red-700 text-white text-sm px-4 py-2 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>
                                You are impersonating this user
                                <span v-if="impersonation?.admin" class="font-semibold">
                                    (Admin: {{ impersonation.admin.name }})
                                </span>
                            </span>
                        </div>

                        <Link
                            :href="route('admin.users.stop-impersonate')"
                            method="post"
                            as="button"
                            class="bg-white text-red-600 px-3 py-1 rounded text-xs font-semibold hover:bg-gray-100 transition-all hover:scale-105 active:scale-95 shadow-sm"
                        >
                            Stop Impersonating
                        </Link>
                    </div>
                </transition>
            </header>

            <!-- Breadcrumb navigation (optional) -->
            <nav class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-2 text-sm" v-if="$slots.breadcrumb">
                <slot name="breadcrumb" />
            </nav>

            <!-- Main content with smooth scrolling -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                <div class="mx-auto">
                    <!-- Loading indicator -->
                    <div v-if="isNavigating" class="fixed top-4 right-4 z-50 animate-pulse bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg">
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
    outline: 2px solid #3b82f6;
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