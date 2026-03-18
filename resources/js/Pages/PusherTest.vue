<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const messages = ref([]);
const form = useForm({
    message: ''
});

onMounted(() => {
    console.log('Echo initialization check...');
    if (window.Echo) {
        console.log('Subscribing to my-channel...');
        window.Echo.channel('my-channel')
            .listen('.my-event', (e) => {
                console.log('Received event:', e);
                messages.value.push(JSON.stringify(e));
            });
    } else {
        console.error('Echo is not defined. Check your bootstrap.js');
    }
});

const sendEvent = () => {
    axios.post(route('pusher.send'), {
        message: form.message
    }).then(() => {
        form.message = '';
    });
};
</script>

<template>
    <Head title="Pusher Test" />

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-bold mb-4">Pusher Test</h1>
                <p class="mb-4 text-gray-600">
                    Publish an event to channel <code class="bg-gray-100 px-1 rounded">my-channel</code> 
                    with event name <code class="bg-gray-100 px-1 rounded">my-event</code>; it will appear below:
                </p>

                <div class="mb-6">
                    <form @submit.prevent="sendEvent" class="flex gap-2">
                        <input 
                            v-model="form.message" 
                            type="text" 
                            placeholder="Type a message..." 
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                        <button 
                            type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                        >
                            Send
                        </button>
                    </form>
                </div>

                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Messages</h2>
                    <ul class="space-y-2">
                        <li v-if="messages.length === 0" class="text-gray-400 italic">No messages yet...</li>
                        <li v-for="(msg, index) in messages" :key="index" class="text-sm font-mono bg-white p-2 border border-gray-100 rounded shadow-sm">
                            {{ msg }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
