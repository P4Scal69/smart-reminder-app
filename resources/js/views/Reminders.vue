<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-8">
            <h1 class="text-xl font-bold text-gray-800">Smart Reminder</h1>
            <div class="hidden md:flex space-x-4">
              <router-link to="/" class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                Dashboard
              </router-link>
              <router-link to="/locations" class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                Locations
              </router-link>
              <router-link to="/reminders" class="px-3 py-2 rounded-lg text-blue-600 bg-blue-50 font-medium">
                Reminders
              </router-link>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-gray-700 font-medium">{{ authStore.user?.name }}</span>
            <button
              @click="handleLogout"
              class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Reminders</h1>
      
      <div class="space-y-4">
        <div
          v-for="reminder in reminders"
          :key="reminder.id"
          class="bg-white rounded-xl shadow-sm p-6 border border-gray-200"
        >
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-lg font-bold text-gray-800">{{ reminder.title }}</h3>
              <p class="text-gray-600 mt-2">{{ reminder.description }}</p>
              <div class="mt-3 flex items-center space-x-4 text-sm text-gray-500">
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  </svg>
                  {{ reminder.location?.name }}
                </span>
                <span :class="reminder.is_active ? 'text-green-600' : 'text-gray-400'">
                  {{ reminder.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();
const reminders = ref([]);

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const fetchReminders = async () => {
  try {
    const response = await axios.get('/api/reminders');
    reminders.value = response.data.data;
  } catch (error) {
    console.error('Error fetching reminders:', error);
  }
};

onMounted(() => {
  fetchReminders();
});
</script>
