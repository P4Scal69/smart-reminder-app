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
              <router-link to="/locations" class="px-3 py-2 rounded-lg text-blue-600 bg-blue-50 font-medium">
                Locations
              </router-link>
              <router-link to="/reminders" class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
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
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Locations</h1>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="location in locations"
          :key="location.id"
          class="bg-white rounded-xl shadow-sm p-6 border border-gray-200"
        >
          <h3 class="text-lg font-bold text-gray-800">{{ location.name }}</h3>
          <p class="text-gray-600 text-sm mt-2">{{ location.address }}</p>
          <div class="mt-4 text-sm text-gray-500">
            <p>Lat: {{ location.coordinates?.latitude }}</p>
            <p>Lng: {{ location.coordinates?.longitude }}</p>
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
const locations = ref([]);

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const fetchLocations = async () => {
  try {
    const response = await axios.get('/api/locations');
    locations.value = response.data.data;
  } catch (error) {
    console.error('Error fetching locations:', error);
  }
};

onMounted(() => {
  fetchLocations();
});
</script>
