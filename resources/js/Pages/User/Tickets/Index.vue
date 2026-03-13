<script setup>
import { Head } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';
</script>

<template>
    <Head title="My Tickets" />

    <UserNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">
                My Tickets
            </h1>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Ticket list goes here.
                    </div>
                </div>
            </div>
        </div>
    </UserNavigation>
</template>
