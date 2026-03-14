<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const showingMobileMenu = ref(false);
const expandedMenus = ref({});

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
});

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
    },
    {
        name: 'My Tickets',
        href: route('user.tickets.index'),
        route: 'user.tickets.index',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        name: 'Create Ticket',
        href: route('user.tickets.create'),
        route: 'user.tickets.create',
        icon: 'M12 4v16m8-8H4',
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
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Desktop Sidebar -->
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
                        <div class="relative flex items-center">
                            <Link
                                :href="item.href"
                                class="group flex flex-1 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                                :class="{
                                    'bg-blue-50 text-blue-700 font-semibold': isParentActive(item),
                                    'text-gray-700 hover:bg-gray-50 hover:text-gray-900': !isParentActive(item)
                                }"
                            >
                                <svg
                                    class="h-5 w-5 flex-shrink-0"
                                    :class="{ 'text-blue-700': isParentActive(item), 'text-gray-500': !isParentActive(item) }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span>{{ item.name }}</span>
                            </Link>

                            <!-- Chevron – only appears if children added in future -->
                            <button
                                v-if="item.children?.length"
                                @click.stop="toggleMenu(item.name, $event)"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                                :class="{ 'text-blue-700': isMenuExpanded(item.name) }"
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

                        <!-- Submenu – hidden until children exist -->
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
                </ul>
            </nav>

            <!-- User Section -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white overflow-hidden">
                        <img 
                            v-if="userAvatar"
                            :src="userAvatar" 
                            :alt="user?.first_name"
                            class="h-full w-full object-cover"
                            @error="handleImageError"
                            ref="avatarImg"
                        />
                        <span v-else>{{ userInitials }}</span>
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
                    <Link :href="route('user.dashboard')" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                            <ApplicationLogo class="h-6 w-6 fill-current text-white" />
                        </div>
                        <span class="text-lg font-bold text-gray-900">User Portal</span>
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
                                        'bg-blue-50 text-blue-700 font-semibold': isParentActive(item),
                                        'text-gray-700 hover:bg-gray-50 hover:text-gray-900': !isParentActive(item)
                                    }"
                                    @click="showingMobileMenu = false"
                                >
                                    <svg
                                        class="h-5 w-5 flex-shrink-0"
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
                                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                    :class="{ 'text-blue-700': isMenuExpanded(item.name) }"
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

        <!-- Main content area -->
        <div class="flex flex-1 flex-col overflow-hidden">
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
                            <h1 class="text-xl font-semibold text-gray-900">User Dashboard</h1>
                        </slot>
                    </div>

                    <div class="flex items-center gap-4">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 rounded-lg p-2 text-sm hover:bg-gray-100">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white overflow-hidden">
                                        <img 
                                            v-if="userAvatar"
                                            :src="userAvatar" 
                                            :alt="user?.first_name"
                                            class="h-full w-full object-cover"
                                            @error="handleImageError"
                                            ref="avatarImg"
                                        />
                                        <span v-else>{{ userInitials }}</span>
                                    </div>
                                    <span class="hidden font-medium text-gray-700 sm:inline">
                                        {{ user?.first_name }} {{ user?.last_name || '' }}
                                    </span>
                                    <svg
                                        class="ml-1 h-4 w-4 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile Settings</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <!-- Impersonation banner -->
                <div
                    v-if="isImpersonating"
                    class="bg-red-600 text-white text-sm px-4 py-2 flex items-center justify-between"
                >
                    <span>
                        You are impersonating this user
                        <span v-if="impersonation?.admin">
                            (Admin: {{ impersonation.admin.name }})
                        </span>
                    </span>

                    <Link
                        :href="route('admin.users.stop-impersonate')"
                        method="post"
                        as="button"
                        class="bg-white text-red-600 px-3 py-1 rounded text-xs font-semibold hover:bg-gray-100"
                    >
                        Stop Impersonating
                    </Link>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50">
                <slot />
            </main>
        </div>
    </div>
</template>