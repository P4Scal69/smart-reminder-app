<template>
  <div class="app-shell py-8">
    <div class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="surface-card-soft mb-8 p-6">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Location Management</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Locations</h1>
            <p class="mt-2 text-sm text-slate-600">Manage your saved places and geofence radius settings.</p>
          </div>
          <button
            @click="openCreateModal"
            class="btn-brand flex items-center gap-2 px-6 py-3"
          >
            <span class="text-xl">+</span>
            Add Location
          </button>
        </div>
      </div>

      <!-- Locations Grid -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block h-12 w-12 animate-spin rounded-full border-b-2 border-brand-600"></div>
        <p class="mt-4 text-slate-600">Loading locations...</p>
      </div>

      <div v-else-if="locations.length === 0" class="surface-card py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-semibold text-slate-900">No locations yet</h3>
        <p class="mt-2 text-sm text-slate-500">Get started by creating your first location.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="location in locations"
          :key="location.id"
          class="surface-card overflow-hidden transition-all duration-200 hover:-translate-y-0.5"
        >
          <div class="p-6">
            <div class="flex justify-between items-start mb-4">
              <div class="flex-1">
                <h3 class="mb-1 text-xl font-semibold text-slate-900">{{ location.name }}</h3>
                <p class="text-sm text-slate-600">{{ location.address || 'No address provided' }}</p>
              </div>
            </div>

            <div class="space-y-2 mb-4">
              <div class="flex items-center text-sm text-slate-600">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                <span>Radius: {{ location.geofence_radius }}m</span>
              </div>
              <div class="flex items-center text-sm text-slate-600">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>{{ location.reminders?.length || 0 }} reminders</span>
              </div>
            </div>

            <div class="flex gap-2 border-t border-slate-200 pt-4">
              <button
                @click="openEditModal(location)"
                class="flex-1 rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200"
              >
                Edit
              </button>
              <button
                @click="deleteLocation(location.id)"
                class="flex-1 rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-200"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl">
          <div class="p-6">
            <h2 class="mb-6 text-2xl font-bold text-slate-900">
              {{ isEditing ? 'Edit Location' : 'Create Location' }}
            </h2>

            <form @submit.prevent="submitForm" class="space-y-4">
              <!-- Map Picker -->
              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                  📍 Click on the map to select location
                </label>
                <div class="relative">
                  <div id="mapPicker" class="mb-2 h-80 w-full cursor-crosshair rounded-xl border border-brand-200 shadow-sm"></div>
                  <div v-if="!form.latitude || !form.longitude" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                    <div class="rounded-lg border border-brand-500 bg-white/90 px-6 py-4 shadow-xl">
                      <p class="text-center text-lg font-semibold text-brand-700">Click on the map to select location</p>
                      <p class="mt-1 text-center text-sm text-slate-600">A marker will appear at the clicked point</p>
                    </div>
                  </div>
                </div>
                <div class="mb-2 rounded-lg border border-brand-200 bg-brand-50 p-3">
                  <p class="text-xs text-brand-700">
                    <strong>How to use:</strong>
                  </p>
                  <ul class="mt-1 space-y-1 text-xs text-brand-600">
                    <li><strong>Click</strong> anywhere on the map to place a marker</li>
                    <li><strong>Drag</strong> the marker to adjust position</li>
                    <li>Address will be <strong>auto-filled</strong> from coordinates</li>
                    <li>Adjust the <strong>radius slider</strong> below to see geofence area</li>
                  </ul>
                </div>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                  placeholder="Home, Office, etc."
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                  Address 
                  <span v-if="addressLoading" class="text-xs text-brand-600">(loading...)</span>
                </label>
                <input
                  v-model="form.address"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                  placeholder="Auto-filled from map or enter manually"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="mb-2 block text-sm font-medium text-slate-700">Latitude</label>
                  <input
                    v-model.number="form.latitude"
                    @input="updateMapMarker"
                    type="number"
                    step="any"
                    required
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                    placeholder="6.471774"
                  />
                </div>
                <div>
                  <label class="mb-2 block text-sm font-medium text-slate-700">Longitude</label>
                  <input
                    v-model.number="form.longitude"
                    @input="updateMapMarker"
                    type="number"
                    step="any"
                    required
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                    placeholder="100.50018"
                  />
                </div>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                  Geofence Radius: {{ form.geofence_radius }}m
                </label>
                <input
                  v-model.number="form.geofence_radius"
                  @input="updateGeofenceCircle"
                  type="range"
                  min="10"
                  max="5000"
                  step="10"
                  class="w-full"
                />
                <div class="mt-1 flex justify-between text-xs text-slate-500">
                  <span>10m</span>
                  <span>5000m</span>
                </div>
              </div>

              <div v-if="formError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ formError }}
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  type="button"
                  @click="closeModal"
                  class="flex-1 rounded-lg bg-slate-100 px-4 py-2 font-medium text-slate-700 transition hover:bg-slate-200"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="flex-1 rounded-lg bg-brand-600 px-4 py-2 font-medium text-white transition hover:bg-brand-700 disabled:opacity-50"
                >
                  {{ submitting ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix Leaflet marker icon issue
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

const locations = ref([]);
const loading = ref(true);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const submitting = ref(false);
const formError = ref('');
const addressLoading = ref(false);

let map = null;
let marker = null;
let circle = null;

const form = ref({
  name: '',
  address: '',
  latitude: null,
  longitude: null,
  geofence_radius: 100
});

const fetchLocations = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/locations');
    locations.value = response.data.data;
  } catch (error) {
    console.error('Error fetching locations:', error);
    alert('Failed to load locations');
  } finally {
    loading.value = false;
  }
};

const initMap = async () => {
  await nextTick();
  
  if (map) {
    map.remove();
  }

  // Default center (Jakarta)
  const defaultLat = form.value.latitude || 6.471774051901331;
  const defaultLng = form.value.longitude || 100.50018143333673;

  map = L.map('mapPicker', {
    center: [defaultLat, defaultLng],
    zoom: 13,
    zoomControl: true,
    attributionControl: true
  });

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  // Add custom CSS to show pointer cursor
  const mapContainer = document.getElementById('mapPicker');
  if (mapContainer) {
    mapContainer.style.cursor = 'crosshair';
  }

  // Add marker if coordinates exist
  if (form.value.latitude && form.value.longitude) {
    marker = L.marker([form.value.latitude, form.value.longitude], {
      draggable: true
    }).addTo(map);

    marker.on('dragend', async function(e) {
      const position = e.target.getLatLng();
      form.value.latitude = position.lat;
      form.value.longitude = position.lng;
      await reverseGeocode(position.lat, position.lng);
      updateGeofenceCircle();
    });

    // Add geofence circle
    updateGeofenceCircle();
  }

  // Click event to add/move marker
  map.on('click', async function(e) {
    form.value.latitude = e.latlng.lat;
    form.value.longitude = e.latlng.lng;

    if (marker) {
      marker.setLatLng(e.latlng);
    } else {
      marker = L.marker(e.latlng, { draggable: true }).addTo(map);
      marker.on('dragend', async function(e) {
        const position = e.target.getLatLng();
        form.value.latitude = position.lat;
        form.value.longitude = position.lng;
        await reverseGeocode(position.lat, position.lng);
        updateGeofenceCircle();
      });
    }

    await reverseGeocode(e.latlng.lat, e.latlng.lng);
    updateGeofenceCircle();
  });
};

const reverseGeocode = async (lat, lng) => {
  try {
    addressLoading.value = true;
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
    );
    const data = await response.json();
    
    if (data.display_name) {
      form.value.address = data.display_name;
    }
  } catch (error) {
    console.error('Reverse geocoding error:', error);
  } finally {
    addressLoading.value = false;
  }
};

const updateMapMarker = () => {
  if (map && form.value.latitude && form.value.longitude) {
    const newLatLng = [form.value.latitude, form.value.longitude];
    
    if (marker) {
      marker.setLatLng(newLatLng);
    } else {
      marker = L.marker(newLatLng, { draggable: true }).addTo(map);
      marker.on('dragend', async function(e) {
        const position = e.target.getLatLng();
        form.value.latitude = position.lat;
        form.value.longitude = position.lng;
        await reverseGeocode(position.lat, position.lng);
        updateGeofenceCircle();
      });
    }
    
    map.setView(newLatLng, 15);
    updateGeofenceCircle();
  }
};

const updateGeofenceCircle = () => {
  if (circle) {
    circle.remove();
  }

  if (marker && form.value.geofence_radius) {
    circle = L.circle(marker.getLatLng(), {
      radius: form.value.geofence_radius,
      color: '#3b82f6',
      fillColor: '#3b82f6',
      fillOpacity: 0.2
    }).addTo(map);
  }
};

const openCreateModal = async () => {
  isEditing.value = false;
  form.value = {
    name: '',
    address: '',
    latitude: 6.471774051901331,
    longitude: 100.50018143333673,
    geofence_radius: 100
  };
  formError.value = '';
  showModal.value = true;
  await initMap();
};

const openEditModal = async (location) => {
  isEditing.value = true;
  editingId.value = location.id;
  form.value = {
    name: location.name,
    address: location.address || '',
    latitude: location.coordinates?.coordinates[1] || 6.471774051901331,
    longitude: location.coordinates?.coordinates[0] || 100.50018143333673,
    geofence_radius: location.geofence_radius || 100
  };
  formError.value = '';
  showModal.value = true;
  await initMap();
};

const closeModal = () => {
  showModal.value = false;
  formError.value = '';
  if (map) {
    map.remove();
    map = null;
  }
  marker = null;
  circle = null;
};

const submitForm = async () => {
  try {
    submitting.value = true;
    formError.value = '';

    if (isEditing.value) {
      await axios.put(`/api/locations/${editingId.value}`, form.value);
    } else {
      await axios.post('/api/locations', form.value);
    }

    closeModal();
    fetchLocations();
  } catch (error) {
    console.error('Error saving location:', error);
    formError.value = error.response?.data?.message || 'Failed to save location';
  } finally {
    submitting.value = false;
  }
};

const deleteLocation = async (id) => {
  if (!confirm('Are you sure you want to delete this location? All reminders for this location will also be deleted.')) {
    return;
  }

  try {
    await axios.delete(`/api/locations/${id}`);
    fetchLocations();
  } catch (error) {
    console.error('Error deleting location:', error);
    alert('Failed to delete location');
  }
};

onMounted(() => {
  fetchLocations();
});
</script>
