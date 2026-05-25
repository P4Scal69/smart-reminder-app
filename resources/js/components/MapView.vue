<template>
  <div class="map-container">
    <div ref="mapContainer" class="map" style="height: 500px; width: 100%; border-radius: 8px;"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  locations: {
    type: Array,
    default: () => []
  },
  center: {
    type: Object,
    default: () => ({ lat: -6.2088, lng: 106.8456 }) // Jakarta default
  },
  zoom: {
    type: Number,
    default: 12
  },
  showUserLocation: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['location-click', 'map-click'])

const mapContainer = ref(null)
let map = null
let markers = []
let circles = []
let userMarker = null

// Fix Leaflet default marker icons
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
})

// Custom icons
const locationIcon = L.icon({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
})

const userIcon = L.divIcon({
  className: 'user-location-marker',
  html: '<div style="background: #4285F4; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.3);"></div>',
  iconSize: [16, 16],
  iconAnchor: [8, 8]
})

onMounted(async () => {
  await nextTick()
  initMap()
  if (props.showUserLocation) {
    showUserLocation()
  }
})

function initMap() {
  if (!mapContainer.value) return

  map = L.map(mapContainer.value).setView([props.center.lat, props.center.lng], props.zoom)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map)

  // Handle map clicks
  map.on('click', (e) => {
    emit('map-click', { lat: e.latlng.lat, lng: e.latlng.lng })
  })

  updateMarkers()
}

function updateMarkers() {
  if (!map) return

  // Clear existing markers and circles
  markers.forEach(marker => map.removeLayer(marker))
  circles.forEach(circle => map.removeLayer(circle))
  markers = []
  circles = []

  // Add location markers
  props.locations.forEach(location => {
    // Add marker
    const marker = L.marker([location.latitude, location.longitude], { icon: locationIcon })
      .addTo(map)
      .bindPopup(`
        <div style="min-width: 150px;">
          <h3 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">${location.name}</h3>
          ${location.address ? `<p style="margin: 0 0 8px 0; font-size: 12px; color: #666;">${location.address}</p>` : ''}
          <p style="margin: 0; font-size: 11px; color: #999;">Radius: ${location.geofence_radius}m</p>
          ${location.reminders?.length ? `<p style="margin: 4px 0 0 0; font-size: 11px; color: #4CAF50;">📝 ${location.reminders.length} reminder(s)</p>` : ''}
        </div>
      `)

    marker.on('click', () => {
      emit('location-click', location)
    })

    markers.push(marker)

    // Add geofence circle
    const circle = L.circle([location.latitude, location.longitude], {
      radius: location.geofence_radius || 100,
      color: '#2196F3',
      fillColor: '#2196F3',
      fillOpacity: 0.1,
      weight: 2
    }).addTo(map)

    circles.push(circle)
  })

  // Fit bounds if there are locations
  if (props.locations.length > 0) {
    const bounds = L.latLngBounds(props.locations.map(loc => [loc.latitude, loc.longitude]))
    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 })
  }
}

function showUserLocation() {
  if (!navigator.geolocation) {
    console.warn('Geolocation not supported')
    return
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const lat = position.coords.latitude
      const lng = position.coords.longitude

      if (userMarker) {
        map.removeLayer(userMarker)
      }

      userMarker = L.marker([lat, lng], { icon: userIcon })
        .addTo(map)
        .bindPopup('📍 Your Location')

      // Center map on user location if no other locations
      if (props.locations.length === 0) {
        map.setView([lat, lng], 15)
      }
    },
    (error) => {
      console.warn('Error getting user location:', error.message)
    },
    {
      enableHighAccuracy: true,
      timeout: 5000,
      maximumAge: 0
    }
  )
}

watch(() => props.locations, () => {
  updateMarkers()
}, { deep: true })

defineExpose({
  map,
  showUserLocation,
  setView: (lat, lng, zoom = 15) => {
    if (map) map.setView([lat, lng], zoom)
  }
})
</script>

<style scoped>
.map-container {
  width: 100%;
  position: relative;
}

.map {
  z-index: 0;
}

:deep(.user-location-marker) {
  background: transparent;
  border: none;
}
</style>
