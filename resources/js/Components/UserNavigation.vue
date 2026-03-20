<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import CommandPalette from '@/Components/CommandPalette.vue';

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
        localStorage.setItem('user_expanded_menus', JSON.stringify(newVal));
    } catch (e) {
        console.error('Failed to save menu state', e);
    }
}, { deep: true });

watch(() => page.url, () => {
    nextTick(autoExpandActiveMenus);
    // Close mobile menu on navigation
    showingMobileMenu.value = false;
    isNavigating.value = false;
}, { immediate: true });

// ────────────────────────────────────────────────
// Navigation data
// ────────────────────────────────────────────────

const navigation = [
    {
        name: 'Dashboard',
        route: 'user.dashboard',
        href: route('user.dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        description: 'Personal account overview'
    },
    {
        name: 'My Tickets',
        route: 'user.tickets.index',
        href: route('user.tickets.index'),
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        description: 'View and track your support requests'
    },
    {
        name: 'Create Ticket',
        route: 'user.tickets.create',
        href: route('user.tickets.create'),
        icon: 'M12 4v16m8-8H4',
        description: 'Submit a new support assistance request'
    },
    {
        name: 'Settings',
        route: 'user.settings',
        href: route('user.settings'),
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        description: 'Personal account and notification preferences'
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
    return (first + last).toUpperCase() || name.toUpperCase() || 'U';
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

const impersonation = computed(() => page.props.impersonation);
const isImpersonating = computed(() => impersonation.value !== null);

// Tooltip helper
const showTooltip = ref(null);
</script>

<template>
    <div class="flex h-screen bg-[#F8FAFC]">
        <ToastNotification />
        <CommandPalette />
        
        <!-- Desktop Sidebar -->
        <aside 
            class="hidden w-72 flex-col bg-white lg:flex relative z-20 transition-all duration-300 border-r border-slate-200/60"
        >
            <!-- Logo area -->
            <div class="flex h-20 items-center px-8 mb-4">
                <Link :href="route('user.dashboard')" class="flex items-center gap-3 group">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 shadow-lg shadow-indigo-100 transition-all duration-300 group-hover:scale-105 group-hover:bg-indigo-700">
                        <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black tracking-tight text-slate-900 uppercase">Help Desk</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest -mt-1">User</span>
                    </div>
                </Link>
            </div>

            <!-- Enhanced Search Bar -->
            <div class="px-6 mb-6">
                <div class="relative group">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search... (Ctrl+K)"
                        class="w-full rounded-xl border-none bg-slate-100/80 px-4 py-2.5 pl-10 text-sm placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-900/5 transition-all duration-300"
                        @focus="isSearchFocused = true"
                        @blur="isSearchFocused = false"
                    />
                    <div class="absolute right-3.5 top-2.5 hidden lg:flex items-center gap-1 opacity-40 group-focus-within:opacity-0 transition-opacity">
                        <span class="px-1.5 py-0.5 rounded-lg border border-slate-300 text-[9px] font-black text-slate-500 uppercase">CMD</span>
                        <span class="px-1.5 py-0.5 rounded-lg border border-slate-300 text-[9px] font-black text-slate-500 uppercase">K</span>
                    </div>
                    <svg
                        class="absolute left-3.5 top-3 h-4 w-4 text-slate-400 transition-colors duration-300"
                        :class="{ 'text-indigo-600': isSearchFocused }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 pb-6 custom-scrollbar">
                <div class="space-y-6">
                    <div>
                        <h3 class="px-4 text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em] mb-3">Main Menu</h3>
                        <ul class="space-y-1">
                            <li v-for="item in filteredNavigation" :key="item.name">
                                <div class="relative flex flex-col px-2">
                                    <!-- High-Density Item Container -->
                                    <div 
                                        class="group relative flex items-center rounded-xl transition-all duration-300 overflow-hidden"
                                        :class="[
                                            isParentActive(item)
                                                ? 'bg-slate-900 text-white shadow-xl shadow-slate-200 border-l-[4px] border-indigo-500'
                                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                                        ]"
                                    >
                                        <!-- Shared Background Effect -->
                                        <div v-if="isParentActive(item)" class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-transparent pointer-events-none"></div>

                                        <!-- Main Navigation Link -->
                                        <Link
                                            :href="item.href"
                                            class="flex-1 flex items-center gap-3.5 py-3 transition-all duration-300 relative z-10"
                                            :class="isParentActive(item) ? 'pl-[13px] pr-2' : 'px-4'"
                                            @click="isNavigating = true"
                                        >
                                            <svg
                                                class="h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110"
                                                :class="{ 
                                                    'text-slate-200': isParentActive(item), 
                                                    'text-slate-400 group-hover:text-slate-600': !isParentActive(item)
                                                }"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                            </svg>
                                            <span class="flex-1 truncate font-bold uppercase tracking-tight text-[13px]">{{ item.name }}</span>
                                        </Link>

                                        <!-- Independent Submenu Trigger (if any) -->
                                        <button
                                            v-if="item.children?.length"
                                            @click.stop.prevent="toggleMenu(item.name, $event)"
                                            class="relative z-20 px-4 py-3 text-slate-400 hover:text-white transition-all duration-300 border-l border-white/5"
                                            :class="[
                                                isParentActive(item) ? 'text-slate-300' : 'text-slate-400 group-hover:text-slate-600',
                                                { 'rotate-180': isMenuExpanded(item.name) }
                                            ]"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Submenu -->
                                    <transition
                                        enter-active-class="transition-all duration-300 ease-out"
                                        enter-from-class="opacity-0 -translate-y-2 max-h-0"
                                        enter-to-class="opacity-100 translate-y-0 max-h-96"
                                        leave-active-class="transition-all duration-200 ease-in"
                                        leave-from-class="opacity-100 translate-y-0 max-h-96"
                                        leave-to-class="opacity-0 -translate-y-2 max-h-0"
                                    >
                                        <ul v-if="item.children?.length && isMenuExpanded(item.name)" class="mt-1 space-y-1 ml-4 border-l border-slate-100 py-1 relative z-0">
                                            <li v-for="child in item.children" :key="child.name">
                                                <Link
                                                    :href="child.href"
                                                    class="group/child relative flex items-center gap-2.5 rounded-lg px-6 py-2 text-[13px] transition-all duration-200"
                                                    :class="[
                                                        isActive(child.route)
                                                            ? 'text-slate-900 font-black bg-indigo-50/40'
                                                            : 'text-slate-500 font-bold hover:text-slate-900 hover:bg-slate-50 hover:translate-x-1'
                                                    ]"
                                                    @click="isNavigating = true"
                                                >
                                                    <div v-if="isActive(child.route)" class="h-1.5 w-1.5 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(79,70,229,0.5)]"></div>
                                                    {{ child.name }}
                                                </Link>
                                            </li>
                                        </ul>
                                    </transition>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- No results -->
                <div v-if="filteredNavigation.length === 0" class="px-3 py-10 text-center">
                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 mb-3">
                         <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">No matches found</p>
                </div>
            </nav>

            <!-- User Footer -->
            <div class="p-4 mx-4 mb-4 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200/50 overflow-hidden">
                        <img 
                            v-if="userAvatar && !avatarError"
                            :src="userAvatar" 
                            class="h-full w-full object-cover"
                            @error="handleImageError"
                        />
                        <span v-else class="text-xs font-black text-slate-900">{{ userInitials }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ user?.first_name }} {{ user?.last_name || '' }}</p>
                        <p class="text-[10px] font-medium text-slate-500 truncate uppercase tracking-tighter">Verified member</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="flex flex-1 flex-col overflow-hidden relative">
            <!-- Glass Header -->
            <header class="h-20 bg-white/70 backdrop-blur-md border-b border-slate-200/50 sticky top-0 z-10 transition-all duration-300">
                <div class="flex h-full items-center justify-between px-8">
                    <div class="flex items-center gap-4 lg:gap-0">
                        <button
                            @click="showingMobileMenu = !showingMobileMenu"
                            class="rounded-xl p-2.5 text-slate-600 hover:bg-slate-100 lg:hidden"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showingMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <slot name="header-title">
                            <div class="flex flex-col">
                                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                                    User Dashboard
                                    <span v-if="isNavigating" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white animate-pulse">Loading</span>
                                </h1>
                                <div class="flex items-center gap-2 mt-1">
                                    <slot name="breadcrumbs"></slot>
                                </div>
                            </div>
                        </slot>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="hidden sm:flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                            <NotificationDropdown />
                            <button class="p-2 text-slate-500 hover:text-slate-900 hover:bg-white rounded-lg transition-all duration-300 shadow-none hover:shadow-sm">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>

                        <div class="h-8 w-px bg-slate-200 mx-2 hidden sm:block font-bold"></div>

                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button class="flex items-center gap-2.5 rounded-xl p-1.5 transition-all duration-300 hover:bg-slate-50 ring-1 ring-transparent hover:ring-slate-200/50">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 shadow-md text-[13px] font-black text-white overflow-hidden ring-1 ring-indigo-100">
                                        <img v-if="userAvatar && !avatarError" :src="userAvatar" class="h-full w-full object-cover" />
                                        <span v-else>{{ userInitials }}</span>
                                    </div>
                                    <svg class="h-4 w-4 text-slate-500 group-hover:text-slate-700 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Signed in as</p>
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ user?.email }}</p>
                                </div>
                                <div class="p-1.5">
                                    <DropdownLink :href="route('profile.edit')" class="rounded-lg flex items-center gap-3 py-2.5 font-bold text-slate-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Profile
                                    </DropdownLink>
                                    <div class="my-1.5 h-px bg-slate-100/80"></div>
                                    <DropdownLink :href="route('logout')" method="post" as="button" class="rounded-lg flex items-center gap-3 py-2.5 font-bold text-rose-600 hover:bg-rose-50">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Sign Out
                                    </DropdownLink>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <!-- Impersonation banner -->
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
                        class="bg-rose-600 text-white text-xs font-black uppercase tracking-widest px-8 py-2.5 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>You are impersonating <span class="font-black underline decoration-white/30 decoration-2 underline-offset-4">{{ user?.first_name }} {{ user?.last_name }}</span> <span v-if="impersonation?.admin" class="ml-2 opacity-60">({{ impersonation.admin.name }})</span></span>
                        </div>
                        <Link
                            :href="route('admin.users.stop-impersonate')"
                            method="post"
                            as="button"
                            class="bg-white text-rose-600 px-4 py-1.5 rounded-lg text-xs font-black hover:bg-slate-50 transition-all active:scale-95 shadow-sm"
                        >
                            Stop Impersonating
                        </Link>
                    </div>
                </transition>
            </header>

            <!-- Optional Breadcrumb -->
            <nav class="bg-white/40 border-b border-slate-200/40 px-8 py-3 text-[13px]" v-if="$slots.breadcrumb">
                <slot name="breadcrumb" />
            </nav>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto custom-scrollbar p-6 lg:p-10 bg-slate-100/30">
                <div class="mx-auto max-w-[1600px] animate-in fade-in slide-in-from-bottom-4 duration-700 ease-out">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Mobile Menu Overlay -->
        <transition
            enter-active-class="transition-opacity duration-400 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="showingMobileMenu" class="fixed inset-0 z-[60] lg:hidden">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-md" @click="showingMobileMenu = false" />
                <aside
                    class="fixed inset-y-0 left-0 w-[280px] bg-white shadow-2xl transform transition-transform duration-500 ease-out"
                    :class="showingMobileMenu ? 'translate-x-0' : '-translate-x-full'"
                >
                    <div class="flex h-20 items-center px-6 border-b border-slate-100">
                         <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white font-black italic shadow-lg shadow-indigo-100">U</div>
                         <span class="ml-3 text-lg font-black tracking-tight text-slate-900 uppercase">User Portal</span>
                    </div>
                    <nav class="p-4 overflow-y-auto h-[calc(100vh-80px)] custom-scrollbar">
                        <div v-for="item in navigation" :key="item.name" class="mb-4">
                            <Link
                                :href="item.href"
                                class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-sm font-bold transition-all transition-colors"
                                :class="isActive(item.route) ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-600'"
                                @click="showingMobileMenu = false"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                {{ item.name }}
                            </Link>
                        </div>
                    </nav>
                </aside>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #E2E8F0; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #CBD5E1; }

@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes slide-in-from-bottom { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.animate-in { animation-fill-mode: both; }
.fade-in { animation-name: fade-in; }
.slide-in-from-bottom-4 { animation-name: slide-in-from-bottom; }
.fade-in.slide-in-from-bottom-4 { animation: slide-in-from-bottom 0.6s cubic-bezier(0.16, 1, 0.3, 1); }

*:focus-visible { outline: 2px solid #4f46e5; outline-offset: 4px; }
</style>