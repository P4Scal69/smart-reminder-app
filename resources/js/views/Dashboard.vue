<template>
  <div class="app-shell">
    <!-- Main Content -->
    <main class="px-4 py-8 sm:px-6 lg:px-8">
    

      <!-- Real GPS Tracking Control -->
      <div class="mb-6 rounded-2xl bg-gradient-to-r from-brand-600 via-brand-500 to-cyan-500 p-6 text-white shadow-[0_20px_45px_-26px_rgba(49,84,255,0.9)]">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="flex items-center gap-2 text-xl font-bold">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7-7-7 7"/>
                <circle cx="12" cy="12" r="9" stroke-width="2" fill="none"/>
              </svg>
              Real-Time GPS Tracking
              <span v-if="isTrackingLocation" class="animate-pulse rounded-full bg-emerald-500 px-2 py-1 text-xs">ACTIVE</span>
            </h2>
            <p class="mt-1 text-sm text-blue-100">
              {{ isTrackingLocation 
                ? 'Monitoring your location and checking geofences...' 
                : 'Enable GPS to automatically trigger reminders when you enter locations'
              }}
            </p>
<p v-if="currentPosition" class="mt-2 flex items-center gap-1 text-xs text-blue-100">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Current: {{ currentPosition.lat.toFixed(6) }}, {{ currentPosition.lng.toFixed(6) }} 
              (±{{ Math.round(currentPosition.accuracy) }}m)
            </p>
            <p v-if="locationError" class="mt-2 text-xs text-red-200">
              ⚠️ {{ locationError }}
            </p>
          </div>
<button
            @click="isTrackingLocation ? stopLocationTracking() : startLocationTracking()"
            class="rounded-xl px-6 py-3 font-bold transition hover:scale-[1.02]"
            :class="isTrackingLocation 
              ? 'bg-red-500 text-white hover:bg-red-600' 
              : 'bg-white text-brand-700 hover:bg-blue-50'"
          >
            {{ isTrackingLocation ? 'Stop Tracking' : 'Start Tracking' }}
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="surface-card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500">Total Locations</p>
              <p class="metric-value">{{ locations.length }}</p>
            </div>
            <div class="rounded-xl bg-brand-50 p-3">
              <svg class="h-6 w-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="surface-card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500">Active Reminders</p>
              <p class="metric-value">{{ reminders.length }}</p>
            </div>
            <div class="rounded-xl bg-emerald-100 p-3">
              <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="surface-card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500">In Range Now</p>
              <p class="metric-value">{{ locationsInRange }}</p>
            </div>
            <div class="rounded-xl bg-cyan-100 p-3">
              <svg class="h-6 w-6 text-cyan-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Map and Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Map -->
        <div class="surface-card lg:col-span-2 p-6">
          <div class="flex justify-between items-center mb-4">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Active Reminder Locations</h2>
              <p class="mt-1 text-sm text-slate-500 flex items-center gap-1">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Showing locations with active reminders
              </p>
            </div>
          </div>
          <div id="map" class="h-96 w-full rounded-xl border border-slate-200"></div>
          <div class="mt-3 text-xs text-slate-500">
            <p><strong>Legend:</strong> Blue markers show locations with active reminders. Click a marker to see reminder details. Blue circles show geofence areas.</p>
          </div>
        </div>

        <!-- Recent Reminders -->
        <div class="surface-card p-6">
          <h2 class="mb-4 text-lg font-bold text-slate-900">Recent Reminders</h2>
          <p class="mb-3 text-xs text-slate-500 flex items-center gap-1">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Click on a reminder to view its location on the map
          </p>
          <div class="space-y-3">
            <div
              v-for="reminder in reminders.slice(0, 8)"
              :key="reminder.id"
              class="rounded-xl border p-3 transition-all hover:scale-[1.01]"
              :class="reminder.is_active 
                ? 'border-brand-100 bg-brand-50/50 hover:border-brand-500 hover:shadow-sm' 
                : 'border-slate-200 bg-slate-50/80 opacity-70 hover:border-slate-400'"
            >
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1 cursor-pointer" @click="focusOnLocation(reminder.location_id)">
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="font-medium text-slate-800">{{ reminder.title }}</p>
                    <span v-if="reminder.is_active" class="rounded-full bg-emerald-500 px-2 py-0.5 text-xs text-white">Active</span>
                    <span 
                      v-if="reminder.trigger_type === 'entry' || reminder.trigger_on_enter"
                      class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2 py-0.5 text-xs text-brand-700"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7-7-7 7"/>
                      </svg>
                      Entry
                    </span>
                    <span 
                      v-if="reminder.trigger_type === 'exit' || reminder.trigger_on_exit"
                      class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 text-xs text-orange-700"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7 7 7-7"/>
                      </svg>
                      Exit
                    </span>
                  </div>
                  <p class="mt-1 flex items-center gap-1 text-sm text-slate-600">
                    <svg class="w-4 h-4 text-brand-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ reminder.location?.name }}
                  </p>
                  <p v-if="reminder.description" class="mt-1 text-xs text-slate-500">{{ reminder.description }}</p>
                </div>
                <div class="flex flex-col gap-1">
<button
                     @click="focusOnLocation(reminder.location_id)"
                     class="rounded-lg bg-brand-100 p-1.5 text-brand-700 transition hover:bg-brand-200"
                     title="View on map"
                   >
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                     </svg>
                   </button>
                  <button
                    @click="deleteReminder(reminder.id)"
                    class="rounded-lg bg-red-100 p-1.5 text-red-600 transition hover:bg-red-200"
                    title="Delete reminder"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <div v-if="reminders.length === 0" class="py-8 text-center text-slate-500">
              <svg class="mx-auto mb-2 h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
import { ref, onMounted, computed, inject } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import L from 'leaflet';

const router = useRouter();
const authStore = useAuthStore();
const toast = inject('toast');

const locations = ref([]);
const reminders = ref([]);
const locationsInRange = ref(0);
let map = null;
const locationMarkers = ref({});

// Real GPS Tracking
const isTrackingLocation = ref(false);
const currentPosition = ref(null);
const locationError = ref(null);
let userLocationMarker = null;
let watchId = null;
const previouslyTriggeredReminders = new Set();
let cachedUserLocation = null; // Cache user location

// Request location permission and start tracking
const startLocationTracking = () => {
  if (!navigator.geolocation) {
    locationError.value = 'Geolocation is not supported by your browser';
    toast?.value?.addToast({
      title: 'Error',
      message: 'Geolocation is not supported by your browser',
      type: 'error',
      duration: 5000
    });
    return;
  }

  isTrackingLocation.value = true;
  locationError.value = null;
  // Manual restart should be treated as a fresh session for entry-trigger testing.
  previouslyTriggeredReminders.clear();

  const startWatch = (options, isRetry = false) => {
    watchId = navigator.geolocation.watchPosition(
    (position) => {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const accuracy = position.coords.accuracy;

      currentPosition.value = {
        lat,
        lng,
        accuracy
      };

      // Update user marker on map
      updateUserLocationMarker(lat, lng, accuracy);

      // Check for triggered reminders
      checkGeofencesTriggers(lat, lng);

      console.log('Location updated:', { lat, lng, accuracy });
    },
    (error) => {
      // If strict high-accuracy request times out, retry once with relaxed options.
      if (error.code === error.TIMEOUT && !isRetry) {
        toast?.value?.addToast({
          title: 'GPS Timeout',
          message: 'Retrying with relaxed GPS settings...',
          type: 'warning',
          duration: 3000
        });

        startWatch(
          {
            enableHighAccuracy: false,
            timeout: 20000,
            maximumAge: 30000,
          },
          true
        );
        return;
      }

      locationError.value = error.message;
      isTrackingLocation.value = false;

      toast?.value?.addToast({
        title: 'Location Error',
        message: error.message,
        type: 'error',
        duration: 5000
      });

      console.error('Geolocation error:', error);
    },
    options
  );
  };

  // Request permission and start watching position
  startWatch({
    enableHighAccuracy: true,
    timeout: 12000,
    maximumAge: 10000
  });

  toast?.value?.addToast({
    title: 'Location Tracking Started',
    message: 'Now monitoring your location for reminders',
    type: 'success',
    duration: 3000
  });
};

// Stop location tracking
const stopLocationTracking = () => {
  if (watchId !== null) {
    navigator.geolocation.clearWatch(watchId);
    watchId = null;
  }
  
  isTrackingLocation.value = false;
  currentPosition.value = null;
  // Reset entry/exit transition memory so next manual start can retrigger immediately.
  previouslyTriggeredReminders.clear();
  
  if (userLocationMarker && map) {
    map.removeLayer(userLocationMarker);
    userLocationMarker = null;
  }

  toast?.value?.addToast({
    title: 'Location Tracking Stopped',
    message: 'No longer monitoring your location',
    type: 'info',
    duration: 3000
  });
};

// Update user location marker on map
const updateUserLocationMarker = (lat, lng, accuracy) => {
  if (!map) return;

  // Remove old marker
  if (userLocationMarker) {
    map.removeLayer(userLocationMarker);
  }

  // Create custom user location icon (pin style like reminder markers)
  const userIcon = L.divIcon({
    className: 'custom-user-marker',
    html: `
      <div style="position: relative;">
        <div style="
          width: 40px;
          height: 40px;
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          border: 4px solid white;
          border-radius: 50% 50% 50% 0;
          transform: rotate(-45deg);
          box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
          display: flex;
          align-items: center;
          justify-content: center;
        ">
          <span style="
            transform: rotate(45deg);
            font-size: 18px;
            color: white;
          ">👤</span>
        </div>
        <div style="
          position: absolute;
          top: -10px;
          left: -10px;
          width: 60px;
          height: 60px;
          background: rgba(102, 126, 234, 0.2);
          border: 2px solid rgba(102, 126, 234, 0.4);
          border-radius: 50%;
          animation: pulse-user 2s infinite;
        "></div>
      </div>
    `,
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40]
  });

userLocationMarker = L.marker([lat, lng], { icon: userIcon })
     .addTo(map)
     .bindPopup(`
       <div style="text-align: center;">
         <strong style="color: #667eea; font-size: 16px;">Your Location</strong><br>
         <span style="color: #666; font-size: 12px;">±${Math.round(accuracy)}m accuracy</span>
       </div>
     `);

  // Pan to user location
  map.setView([lat, lng], 15);
};

// Check if user is within any geofences and trigger reminders
const checkGeofencesTriggers = (userLat, userLng) => {
  const triggered = [];

  reminders.value.forEach(reminder => {
    if (!reminder.is_active || !reminder.location) return;

    const locLat = reminder.location.latitude;
    const locLng = reminder.location.longitude;
    const radius = reminder.location.geofence_radius || 100;

    const distance = calculateDistance(userLat, userLng, locLat, locLng);
    const reminderId = reminder.id;
    const isInside = distance <= radius;
    const wasInside = previouslyTriggeredReminders.has(reminderId);

    // Determine trigger type (support both old and new format)
    const triggerType = reminder.trigger_type || (reminder.trigger_on_exit ? 'exit' : 'entry');

    // Check if we should trigger based on entry/exit
    if (isInside && !wasInside && triggerType === 'entry') {
      // User just entered and reminder is set to trigger on entry
      triggered.push({
        ...reminder,
        distance
      });
      previouslyTriggeredReminders.add(reminderId);
    } else if (!isInside && wasInside && triggerType === 'exit') {
      // User just left and reminder is set to trigger on exit
      triggered.push({
        ...reminder,
        distance: radius + 1 // Outside the geofence
      });
      previouslyTriggeredReminders.delete(reminderId);
    } else if (isInside) {
      // User is inside, keep tracking for potential exit trigger
      previouslyTriggeredReminders.add(reminderId);
    } else if (!isInside && triggerType === 'entry') {
      // User is outside and this is an entry trigger, remove from tracking
      previouslyTriggeredReminders.delete(reminderId);
    }
    // Note: For exit triggers, we keep them in the set even when outside
    // so we can detect when they enter and then exit again
  });

  // Show alarm page for newly triggered reminders
  if (triggered.length > 0) {
    triggered.forEach(reminder => {
      openAlarmPage(reminder);
    });

    // Update locations in range count
    locationsInRange.value = previouslyTriggeredReminders.size;
  }
};

// Open alarm page (replace current tab)
const openAlarmPage = (reminder) => {
  const reminderData = encodeURIComponent(JSON.stringify({
    id: reminder.id,
    title: reminder.title,
    description: reminder.description,
    trigger_type: reminder.trigger_type
  }));
  
  const locationData = encodeURIComponent(JSON.stringify({
    name: reminder.location.name,
    address: reminder.location.address
  }));
  
  const triggeredAt = new Date().toISOString();
  
  // Navigate to alarm page in same tab
  router.push({
    path: '/alarm',
    query: {
      reminder: reminderData,
      location: locationData,
      triggeredAt: triggeredAt
    }
  });
};

// Delete reminder from dashboard
const deleteReminder = async (reminderId) => {
  if (!confirm('Are you sure you want to delete this reminder?')) {
    return;
  }

  try {
    await axios.delete(`/api/reminders/${reminderId}`);
    
    toast?.value?.addToast({
      title: 'Reminder Deleted',
      message: 'Reminder has been deleted successfully',
      type: 'success',
      duration: 3000
    });
    
    // Refresh data
    fetchData();
  } catch (error) {
    console.error('Error deleting reminder:', error);
    toast?.value?.addToast({
      title: 'Error',
      message: 'Failed to delete reminder',
      type: 'error',
      duration: 5000
    });
  }
};

// Calculate distance between two points (Haversine formula)
const calculateDistance = (lat1, lon1, lat2, lon2) => {
  const R = 6371000; // Earth's radius in meters
  const φ1 = lat1 * Math.PI / 180;
  const φ2 = lat2 * Math.PI / 180;
  const Δφ = (lat2 - lat1) * Math.PI / 180;
  const Δλ = (lon2 - lon1) * Math.PI / 180;

  const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  return R * c; // Distance in meters
};

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

  const lat = location.latitude;
  const lng = location.longitude;

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
  // If map already exists and we have locations, just update markers
  if (map && locations.value.length) {
    updateMapMarkers();
    return;
  }

  // Use cached location if available
  if (cachedUserLocation) {
    createMap(cachedUserLocation.lat, cachedUserLocation.lng);
    return;
  }

  // Try to get user's current location first (with reduced timeout)
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;
        cachedUserLocation = { lat: userLat, lng: userLng };
        createMap(userLat, userLng);
      },
      (error) => {
        // If geolocation fails, use fallback immediately
        console.log('Geolocation error, using fallback:', error);
        useFallbackLocation();
      },
      { timeout: 2000, maximumAge: 300000 } // 2 second timeout, cache for 5 minutes
    );
  } else {
    useFallbackLocation();
  }
};

const useFallbackLocation = () => {
  // Use first location with active reminder, or first location, or default
  const locationsWithActiveReminders = locations.value.filter(location => {
    const activeReminders = reminders.value.filter(
      reminder => reminder.location_id === location.id && reminder.is_active
    );
    return activeReminders.length > 0;
  });

  const centerLoc = locationsWithActiveReminders[0] || locations.value[0];
  const lat = centerLoc?.latitude || 6.471774051901331;
  const lng = centerLoc?.longitude || 100.50018143333673;
  
  createMap(lat, lng);
};

const updateMapMarkers = () => {
  if (!map) return;

  // Clear existing markers
  Object.values(locationMarkers.value).forEach(marker => {
    if (marker && map) {
      map.removeLayer(marker);
    }
  });
  locationMarkers.value = {};

  // Re-add markers
  addMarkersToMap();
};

const createMap = (lat, lng) => {
  // Remove existing map if it exists
  if (map) {
    map.remove();
    map = null;
  }

  if (!locations.value.length) {
    // Create empty map at user location
    map = L.map('map').setView([lat, lng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
      maxZoom: 19
    }).addTo(map);
    return;
  }

  // Initialize map
  map = L.map('map').setView([lat, lng], 12);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  // Add markers for locations with active reminders
  addMarkersToMap();

  // Fit map bounds to show all markers
  if (locations.value.length > 0) {
    const bounds = L.latLngBounds(
        locations.value
        .map(loc => [loc.latitude, loc.longitude])
    );
    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
  }
};

const addMarkersToMap = () => {
  locations.value.forEach(location => {
    const lat = location.latitude;
    const lng = location.longitude;

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
            ${location.name}
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
        L.circle([location.latitude, location.longitude], {
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

/* User location marker animation */
@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.3);
    opacity: 0.7;
  }
}

@keyframes pulse-user {
  0%, 100% {
    transform: scale(1);
    opacity: 0.5;
  }
  50% {
    transform: scale(1.3);
    opacity: 0;
  }
}

.user-location-marker,
.custom-user-marker {
  background: transparent;
  border: none;
}
</style>
