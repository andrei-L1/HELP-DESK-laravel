<!-- resources/js/Layouts/AgentLayout.vue -->
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
    const saved = localStorage.getItem('agent_expanded_menus');
    if (saved) {
        try {
            expandedMenus.value = JSON.parse(saved);
        } catch (e) {
            console.error('Failed to parse saved menu state', e);
        }
    }
    await nextTick();
    autoExpandActiveMenus();

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
            const isAnyChildActive = item.children.some(child => isChildActive(child));
            if (isAnyChildActive) {
                expandedMenus.value[item.name] = true;
            }
        }
    });
};

watch(expandedMenus, (newVal) => {
    try {
        localStorage.setItem('agent_expanded_menus', JSON.stringify(newVal));
    } catch (e) {
        console.error('Failed to save menu state', e);
    }
}, { deep: true });

watch(() => page.url, () => {
    nextTick(autoExpandActiveMenus);
    showingMobileMenu.value = false;
}, { immediate: true });

// ────────────────────────────────────────────────
// Agent Navigation
// ────────────────────────────────────────────────
const navigation = [
    {
        name: 'Dashboard',
        href: route('agent.dashboard'),
        routeName: 'agent.dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        description: 'Your agent overview'
    },
    {
        name: 'Tickets',
        href: route('agent.tickets.index'),
        routeName: 'agent.tickets.index',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        description: 'Manage your assigned tickets',
        children: [
            { name: 'All My Tickets', href: route('agent.tickets.index'), routeName: 'agent.tickets.index' },
            { name: 'Open',           href: route('agent.tickets.open'),   routeName: 'agent.tickets.open' },
            { name: 'Pending Reply',  href: route('agent.tickets.pending'), routeName: 'agent.tickets.pending' },
            { name: 'Resolved',       href: route('agent.tickets.resolved'), routeName: 'agent.tickets.resolved' },
            { name: 'Closed',         href: route('agent.tickets.closed'), routeName: 'agent.tickets.closed' },
        ],
    },
    {
        name: 'New Ticket',
        href: route('agent.tickets.create'),
        routeName: 'agent.tickets.create',
        icon: 'M12 4v16m8-8H4',
        description: 'Create a new ticket'
    },
    {
        name: 'Knowledge Base',
        href: route('agent.kb.index'),
        routeName: 'agent.kb.index',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        description: 'Internal help articles & solutions'
    },
    {
        name: 'Settings',
        href: '#',
        routeName: null,
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        description: 'Your profile & preferences',
        children: [
            { name: 'Profile',               href: route('profile.edit'), routeName: 'profile.edit' },
            { name: 'Notifications',         href: route('agent.settings.notifications'), routeName: 'agent.settings.notifications' },
            { name: 'Signature',             href: route('agent.settings.signature'), routeName: 'agent.settings.signature' },
            // Add canned responses, availability, etc. if implemented
        ],
    }, 
];

// Search filtering (also search inside children names)
const filteredNavigation = computed(() => {
    if (!searchQuery.value) return navigation;
    const query = searchQuery.value.toLowerCase();
    return navigation.filter(item => {
        const matchesParent = item.name.toLowerCase().includes(query) || item.description?.toLowerCase().includes(query);
        const matchesChild = item.children?.some(child => child.name.toLowerCase().includes(query));
        return matchesParent || matchesChild;
    });
});

// ────────────────────────────────────────────────
// Active state logic
// ────────────────────────────────────────────────
const isActive = (routeName) => {
    if (!routeName) return false;
    return route().current(routeName) || route().current(`${routeName}.*`);
};

const isChildActive = (child) => {
    if (!child.routeName) return false;
    const currentQuery = route().query || {};
    const routeMatches = isActive(child.routeName);
    if (child.query?.type) {
        return routeMatches && currentQuery.type === child.query.type;
    }
    return routeMatches;
};

const isParentActive = (item) => {
    if (item.routeName && isActive(item.routeName)) return true;
    if (item.children) return item.children.some(isChildActive);
    return false;
};

const isMenuExpanded = (menuName) => !!expandedMenus.value[menuName];

const toggleMenu = (menuName, event) => {
    event.preventDefault();
    event.stopPropagation();
    expandedMenus.value[menuName] = !expandedMenus.value[menuName];
};

// ────────────────────────────────────────────────
// User & impersonation
// ────────────────────────────────────────────────
const user = computed(() => page.props.auth?.user || {});
const userAvatar = computed(() => user.value?.avatar_url || null);
const userInitials = computed(() => {
    const first = user.value?.first_name?.[0] || '';
    const last = user.value?.last_name?.[0] || '';
    return (first + last).toUpperCase() || user.value?.name?.[0]?.toUpperCase() || 'A';
});

const handleImageError = (e) => {
    avatarError.value = true;
    e.target.style.display = 'none';
    e.target.parentElement.querySelector('span')?.classList.remove('hidden');
};

const handleImageLoad = () => {
    avatarError.value = false;
};

watch(userAvatar, () => { avatarError.value = false; });

const impersonation = computed(() => page.props.impersonation);
const isImpersonating = computed(() => impersonation.value !== null);
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Desktop Sidebar -->
        <aside class="hidden w-64 flex-col border-r border-gray-200 bg-white lg:flex transition-all duration-300">
            <!-- Logo -->
            <div class="flex h-16 items-center border-b border-gray-200 px-6 group">
                <Link :href="route('agent.dashboard')" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 transition-transform group-hover:scale-105 group-hover:shadow-md">
                        <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900 group-hover:text-emerald-700 transition-colors">Agent Portal</span>
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
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" :class="{ 'text-emerald-500': isSearchFocused }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
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
                                <svg class="h-5 w-5 flex-shrink-0 transition-transform group-hover:scale-110" :class="{ 'text-emerald-900': isParentActive(item), 'text-gray-500': !isParentActive(item) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span class="flex-1">{{ item.name }}</span>
                            </Link>

                            <button
                                v-if="item.children?.length"
                                @click.stop="toggleMenu(item.name, $event)"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all hover:scale-110"
                                :class="{ 'text-emerald-700 rotate-180': isMenuExpanded(item.name) }"
                                :aria-label="isMenuExpanded(item.name) ? 'Collapse' : 'Expand'"
                            >
                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': isMenuExpanded(item.name) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <ul v-if="item.children?.length && isMenuExpanded(item.name)" class="mt-1 space-y-1 pl-11">
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

                    <li v-if="filteredNavigation.length === 0" class="px-3 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No menu items found</p>
                        <p class="text-xs text-gray-400">Try a different search term</p>
                    </li>
                </ul>
            </nav>

            <!-- User Section -->
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
                        />
                        <span v-else>{{ userInitials }}</span>
                        <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-400 ring-2 ring-white"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ user?.first_name }} {{ user?.last_name || '' }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Menu Overlay -->
        <transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="showingMobileMenu" class="fixed inset-0 z-50 lg:hidden" @click="showingMobileMenu = false">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75 backdrop-blur-sm" />
                <aside
                    class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl overflow-y-auto transform transition-transform duration-300 ease-out"
                    :class="showingMobileMenu ? 'translate-x-0' : '-translate-x-full'"
                    @click.stop
                >
                    <div class="flex h-16 items-center border-b border-gray-200 px-6">
                        <Link :href="route('agent.dashboard')" class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700">
                                <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                            </div>
                            <span class="text-lg font-bold text-gray-900">Agent Portal</span>
                        </Link>
                    </div>

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
                                        <svg class="h-6 w-6 flex-shrink-0" :class="{ 'text-emerald-900': isParentActive(item), 'text-gray-500': !isParentActive(item) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': isMenuExpanded(item.name) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <ul v-if="item.children?.length && isMenuExpanded(item.name)" class="mt-2 space-y-2 pl-12">
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

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <header class="border-b border-gray-200 bg-white sticky top-0 z-10">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button
                        @click="showingMobileMenu = !showingMobileMenu"
                        class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 lg:hidden transition-all hover:scale-105 active:scale-95"
                        :aria-label="showingMobileMenu ? 'Close menu' : 'Open menu'"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!showingMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="flex-1">
                        <slot name="header-title">
                            <h1 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                                <span>Agent Dashboard</span>
                                <span v-if="isNavigating" class="inline-block h-2 w-2 animate-ping rounded-full bg-emerald-600"></span>
                            </h1>
                        </slot>
                    </div>

                    <div class="flex items-center gap-4">
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
                                        />
                                        <span v-else>{{ userInitials }}</span>
                                        <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-400 ring-2 ring-white"></span>
                                    </div>
                                    <span class="hidden font-medium text-gray-700 sm:inline">
                                        {{ user?.first_name }} {{ user?.last_name || '' }}
                                    </span>
                                    <svg class="ml-1 h-4 w-4 text-gray-400 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-3 border-b border-gray-100 sm:hidden">
                                    <p class="text-sm font-medium text-gray-900">{{ user?.first_name }} {{ user?.last_name || '' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')" as="a">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Profile
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

                <!-- Impersonation Banner – kept intentionally -->
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
                                You are impersonating this agent
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

            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                <div class="mx-auto">
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
/* Same scrollbar, transitions, focus styles as ManagerLayout */
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 3px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background-color: #9ca3af; }

button, a { transition: all 0.2s ease-in-out; }

button:focus-visible, a:focus-visible {
    outline: 2px solid #10b981;
    outline-offset: 2px;
}

@keyframes ping {
    75%, 100% { transform: scale(2); opacity: 0; }
}
.animate-ping { animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite; }
</style>