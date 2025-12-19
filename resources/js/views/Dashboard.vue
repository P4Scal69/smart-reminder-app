<template>
  <div class="min-h-screen bg-gray-50">
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
          <div class="flex justify-between items-center mb-4">
            <div>
              <h2 class="text-lg font-bold text-gray-800">Active Reminder Locations</h2>
              <p class="text-sm text-gray-500 mt-1">📍 Showing locations with active reminders</p>
            </div>
          </div>
          <div id="map" class="h-96 rounded-lg border-2 border-gray-200"></div>
          <div class="mt-3 text-xs text-gray-500">
            <p><strong>Legend:</strong> Blue markers show locations with active reminders. Click a marker to see reminder details. Blue circles show geofence areas.</p>
          </div>
        </div>

        <!-- Recent Reminders -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Recent Reminders</h2>
          <p class="text-xs text-gray-500 mb-3">💡 Click on a reminder to view its location on the map</p>
          <div class="space-y-3">
            <div
              v-for="reminder in reminders.slice(0, 8)"
              :key="reminder.id"
              @click="focusOnLocation(reminder.location_id)"
              class="p-3 rounded-lg border-2 transition-all cursor-pointer transform hover:scale-[1.02]"
              :class="reminder.is_active 
                ? 'bg-blue-50 border-blue-300 hover:border-blue-500 hover:shadow-md' 
                : 'bg-gray-50 border-gray-200 hover:border-gray-400 opacity-60'"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <p class="font-medium text-gray-800 flex items-center gap-2">
                    {{ reminder.title }}
                    <span v-if="reminder.is_active" class="text-xs bg-green-500 text-white px-2 py-0.5 rounded-full">Active</span>
                  </p>
                  <p class="text-sm text-gray-600 mt-1 flex items-center gap-1">
                    📍 {{ reminder.location?.name }}
                  </p>
                  <p v-if="reminder.description" class="text-xs text-gray-500 mt-1">{{ reminder.description }}</p>
                </div>
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div v-if="reminders.length === 0" class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
              <p>No reminders yet</p>
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
const locationMarkers = ref({});

const focusOnLocation = (locationId) => {
  if (!map || !locationId) {
    console.log('No map or locationId:', { map, locationId });
    return;
  }

  const location = locations.value.find(loc => loc.id === locationId);
  if (!location) {
    console.log('Location not found');
    return;
  }

  // Get coordinates - handle both direct properties and nested coordinates object
  const lat = location.coordinates?.latitude || location.latitude;
  const lng = location.coordinates?.longitude || location.longitude;

  if (!lat || !lng) {
    console.log('No valid coordinates found');
    return;
  }

  console.log('Focusing on location:', location.name, { lat, lng });

  const marker = locationMarkers.value[locationId];
  if (!marker) {
    console.log('Marker not found for location ID:', locationId);
    // Even if marker not found, still pan to location
    map.flyTo([lat, lng], 16, { duration: 1.5, easeLinearity: 0.25 });
    return;
  }

  // Pan to location with animation
  map.flyTo([lat, lng], 16, {
    duration: 1.5,
    easeLinearity: 0.25
  });

  // Open popup after animation
  setTimeout(() => {
    marker.openPopup();
    
    // Add a temporary highlight effect
    const originalIcon = marker.getIcon();
    
    // Pulse animation by temporarily changing the marker
    marker.setIcon(L.divIcon({
      className: 'custom-marker-pulse',
      html: originalIcon.options.html,
      iconSize: originalIcon.options.iconSize,
      iconAnchor: originalIcon.options.iconAnchor,
      popupAnchor: originalIcon.options.popupAnchor
    }));
    
    setTimeout(() => {
      marker.setIcon(originalIcon);
    }, 2000);
  }, 1500);
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

  // Filter locations that have active reminders
  const locationsWithActiveReminders = locations.value.filter(location => {
    const activeReminders = reminders.value.filter(
      reminder => reminder.location_id === location.id && reminder.is_active
    );
    return activeReminders.length > 0;
  });

  // Use first location with active reminder, or first location, or default
  const centerLoc = locationsWithActiveReminders[0] || locations.value[0];
  const lat = centerLoc?.coordinates?.latitude || -6.2088;
  const lng = centerLoc?.coordinates?.longitude || 106.8456;

  // Initialize map
  map = L.map('map').setView([lat, lng], 12);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  // Add markers for locations with active reminders
  locations.value.forEach(location => {
    // Get coordinates - handle both direct properties and nested coordinates object
    const lat = location.coordinates?.latitude || location.latitude;
    const lng = location.coordinates?.longitude || location.longitude;

    if (lat && lng) {
      // Get active reminders for this location
      const activeReminders = reminders.value.filter(
        reminder => reminder.location_id === location.id && reminder.is_active
      );

      if (activeReminders.length === 0) return; // Skip if no active reminders

      // Create custom icon for locations with active reminders (larger and more visible)
      const customIcon = L.divIcon({
        className: 'custom-marker',
        html: `
          <div style="position: relative; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
            <svg width="48" height="64" viewBox="0 0 48 64" xmlns="http://www.w3.org/2000/svg">
              <path d="M24 0C10.8 0 0 10.8 0 24c0 16.8 24 40 24 40s24-22.2 24-40c0-13.2-10.8-24-24-24z" fill="#ef4444" stroke="#991b1b" stroke-width="2"/>
              <circle cx="24" cy="24" r="14" fill="white" stroke="#ef4444" stroke-width="2"/>
              <text x="24" y="31" text-anchor="middle" font-size="20" font-weight="bold" fill="#ef4444">${activeReminders.length}</text>
            </svg>
            <div style="position: absolute; top: -8px; right: -8px; background: #10b981; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; border: 2px solid white;">
              🔔
            </div>
          </div>
        `,
        iconSize: [48, 64],
        iconAnchor: [24, 64],
        popupAnchor: [0, -64]
      });

      const marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);

      // Create popup content with reminder details
      const remindersList = activeReminders.map(r => 
        `<li class="text-sm">
          <strong>${r.title}</strong>
          ${r.description ? '<br><span class="text-gray-600">' + r.description + '</span>' : ''}
        </li>`
      ).join('');

      marker.bindPopup(`
        <div style="min-width: 200px;">
          <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 8px; color: #1f2937;">
            📍 ${location.name}
          </h3>
          <p style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">
            ${location.address || 'No address'}
          </p>
          <div style="border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 8px;">
            <p style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px;">
              🔔 Active Reminders (${activeReminders.length}):
            </p>
            <ul style="list-style: none; padding: 0; margin: 0;">
              ${remindersList}
            </ul>
          </div>
        </div>
      `);

      // Add geofence circle if radius exists (more visible)
      if (location.geofence_radius) {
        L.circle([location.coordinates.latitude, location.coordinates.longitude], {
          radius: location.geofence_radius,
          color: '#ef4444',
          fillColor: '#fecaca',
          fillOpacity: 0.25,
          weight: 3,
          dashArray: '10, 10'
        }).addTo(map);
      }

      // Store marker reference using location ID
      locationMarkers.value[location.id] = marker;
    }
  });

  // Auto-fit map to show all markers
  if (locationsWithActiveReminders.length > 0) {
    const bounds = L.latLngBounds(
      locationsWithActiveReminders
        .filter(loc => loc.coordinates)
        .map(loc => [loc.coordinates.latitude, loc.coordinates.longitude])
    );
    map.fitBounds(bounds, { padding: [50, 50] });
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style>
.custom-marker-pulse {
  animation: pulse 1s ease-in-out 2;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
  }
}

/* Custom scrollbar for reminders list */
.space-y-3 {
  max-height: 500px;
  overflow-y: auto;
  padding-right: 8px;
}

.space-y-3::-webkit-scrollbar {
  width: 6px;
}

.space-y-3::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.space-y-3::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.space-y-3::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
