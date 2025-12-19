<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-8">
            <h1 class="text-xl font-bold text-gray-800">Smart Reminder</h1>
            <div class="hidden md:flex space-x-4">
              <router-link to="/" class="px-3 py-2 rounded-lg text-blue-600 bg-blue-50 font-medium">
                Dashboard
              </router-link>
              <router-link to="/locations" class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
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

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm font-medium">Total Locations</p>
              <p class="text-3xl font-bold text-gray-800 mt-1">{{ locations.length }}</p>
            </div>
            <div class="bg-blue-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm font-medium">Active Reminders</p>
              <p class="text-3xl font-bold text-gray-800 mt-1">{{ reminders.length }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm font-medium">In Range Now</p>
              <p class="text-3xl font-bold text-gray-800 mt-1">{{ locationsInRange }}</p>
            </div>
            <div class="bg-purple-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Map and Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Map -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Your Locations</h2>
          <div id="map" class="h-96 rounded-lg"></div>
        </div>

        <!-- Recent Reminders -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Recent Reminders</h2>
          <div class="space-y-3">
            <div
              v-for="reminder in reminders.slice(0, 5)"
              :key="reminder.id"
              class="p-3 bg-gray-50 rounded-lg border border-gray-200"
            >
              <p class="font-medium text-gray-800">{{ reminder.title }}</p>
              <p class="text-sm text-gray-600 mt-1">{{ reminder.location?.name }}</p>
            </div>
            <div v-if="reminders.length === 0" class="text-center py-8 text-gray-500">
              No reminders yet
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
import L from 'leaflet';

const router = useRouter();
const authStore = useAuthStore();

const locations = ref([]);
const reminders = ref([]);
const locationsInRange = ref(0);
let map = null;

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const fetchData = async () => {
  try {
    const [locationsRes, remindersRes] = await Promise.all([
      axios.get('/api/locations'),
      axios.get('/api/reminders')
    ]);
    
    locations.value = locationsRes.data.data;
    reminders.value = remindersRes.data.data;

    // Initialize map after data is loaded
    initMap();
  } catch (error) {
    console.error('Error fetching data:', error);
  }
};

const initMap = () => {
  if (!locations.value.length) return;

  // Get first location's coordinates or default to center
  const firstLoc = locations.value[0];
  const lat = firstLoc.coordinates?.latitude || -6.2088;
  const lng = firstLoc.coordinates?.longitude || 106.8456;

  // Initialize map
  map = L.map('map').setView([lat, lng], 12);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Add markers for each location
  locations.value.forEach(location => {
    if (location.coordinates) {
      const marker = L.marker([
        location.coordinates.latitude,
        location.coordinates.longitude
      ]).addTo(map);

      marker.bindPopup(`
        <strong>${location.name}</strong><br>
        ${location.address || ''}
      `);
    }
  });
};

onMounted(() => {
  fetchData();
});
</script>
