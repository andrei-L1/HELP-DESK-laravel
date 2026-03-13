<script setup>
import { Head } from '@inertiajs/vue3';
import UserNavigation from '@/Components/UserNavigation.vue';

const props = defineProps({
    ticket_id: {
        type: String,
        required: true,
    }
});
</script>

<template>
    <Head title="View Ticket" />

    <UserNavigation>
        <template #header-title>
            <h1 class="text-xl font-semibold text-gray-900">
                Ticket #{{ ticket_id }}
            </h1>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Ticket details go here.
                    </div>
                </div>
            </div>
        </div>
    </UserNavigation>
</template>
