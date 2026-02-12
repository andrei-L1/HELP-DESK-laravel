<!-- resources/js/Pages/Welcome.vue -->
<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 dark:text-white tracking-tight">
            HelpDesk System
          </h1>
          <p class="mt-6 text-xl md:text-2xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
            Simple, fast, and modern way to manage support tickets for your team or organization
          </p>

          <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
            <a
              href="/login"
              class="inline-block px-8 py-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-lg"
            >
              Login to Dashboard
            </a>
            <a
              href="/register"
              class="inline-block px-8 py-4 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-lg"
            >
              Create Account
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white dark:bg-gray-800 py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-center text-gray-900 dark:text-white mb-16">
          Why Choose Our HelpDesk?
        </h2>

        <div class="grid md:grid-cols-3 gap-10">
          <div class="text-center p-8 rounded-xl bg-gray-50 dark:bg-gray-700">
            <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Fast Ticketing</h3>
            <p class="text-gray-600 dark:text-gray-300">Create and track tickets in seconds with a clean interface.</p>
          </div>

          <div class="text-center p-8 rounded-xl bg-gray-50 dark:bg-gray-700">
            <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Team Collaboration</h3>
            <p class="text-gray-600 dark:text-gray-300">Assign tickets, add comments, and work together easily.</p>
          </div>

          <div class="text-center p-8 rounded-xl bg-gray-50 dark:bg-gray-700">
            <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Mobile Friendly</h3>
            <p class="text-gray-600 dark:text-gray-300">Works perfectly on phones, tablets, and desktops.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-blue-600 py-16 text-center">
      <h2 class="text-4xl font-bold text-white mb-6">
        Ready to get started?
      </h2>
      <a
        href="/register"
        class="inline-block px-10 py-5 bg-white text-blue-600 font-bold rounded-lg text-xl hover:bg-gray-100 transition"
      >
        Sign Up Now — It's Free
      </a>
    </div>
  </div>
</template>

<script setup>
// You can add JavaScript logic here if needed
</script>