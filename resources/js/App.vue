<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation Bar (only show when authenticated) -->
    <nav v-if="authStore.isAuthenticated" class="bg-white shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo/Brand -->
          <div class="flex items-center">
            <h1 class="text-xl font-bold text-blue-600">Smart Reminder</h1>
          </div>

          <!-- Navigation Links -->
          <div class="flex space-x-4">
            <router-link
              to="/"
              class="px-4 py-2 rounded-lg font-medium transition"
              :class="$route.path === '/' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
            >
              📊 Dashboard
            </router-link>
            <router-link
              to="/locations"
              class="px-4 py-2 rounded-lg font-medium transition"
              :class="$route.path === '/locations' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
            >
              📍 Locations
            </router-link>
            <router-link
              to="/reminders"
              class="px-4 py-2 rounded-lg font-medium transition"
              :class="$route.path === '/reminders' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
            >
              🔔 Reminders
            </router-link>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">{{ authStore.user?.name }}</span>
            <button
              @click="logout"
              class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-medium transition"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <router-view />
  </div>
</template>

<script setup>
import { useAuthStore } from './stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style>
/* Import Leaflet CSS */
@import 'leaflet/dist/leaflet.css';
</style>
