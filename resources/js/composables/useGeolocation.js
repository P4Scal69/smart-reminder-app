import { ref, computed, onMounted, onUnmounted } from 'vue'

export function useGeolocation() {
  const coords = ref({ latitude: null, longitude: null, accuracy: null })
  const error = ref(null)
  const isSupported = ref(false)
  const watchId = ref(null)

  onMounted(() => {
    isSupported.value = 'geolocation' in navigator
  })

  function getCurrentPosition() {
    return new Promise((resolve, reject) => {
      if (!isSupported.value) {
        reject(new Error('Geolocation is not supported'))
        return
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          coords.value = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy
          }
          error.value = null
          resolve(coords.value)
        },
        (err) => {
          error.value = err.message
          reject(err)
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0
        }
      )
    })
  }

  function watchPosition(callback) {
    if (!isSupported.value) return

    watchId.value = navigator.geolocation.watchPosition(
      (position) => {
        coords.value = {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          accuracy: position.coords.accuracy
        }
        error.value = null
        if (callback) callback(coords.value)
      },
      (err) => {
        error.value = err.message
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 5000
      }
    )
  }

  function clearWatch() {
    if (watchId.value !== null) {
      navigator.geolocation.clearWatch(watchId.value)
      watchId.value = null
    }
  }

  onUnmounted(() => {
    clearWatch()
  })

  return {
    coords,
    error,
    isSupported,
    getCurrentPosition,
    watchPosition,
    clearWatch
  }
}

export function useLocationTracking(locations, reminders) {
  const { coords, getCurrentPosition, watchPosition } = useGeolocation()
  const nearbyLocations = ref([])
  const triggeredReminders = ref([])
  const isTracking = ref(false)

  // Calculate distance between two points (Haversine formula)
  function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000 // Earth's radius in meters
    const φ1 = lat1 * Math.PI / 180
    const φ2 = lat2 * Math.PI / 180
    const Δφ = (lat2 - lat1) * Math.PI / 180
    const Δλ = (lon2 - lon1) * Math.PI / 180

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ / 2) * Math.sin(Δλ / 2)
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))

    return R * c // Distance in meters
  }

  function checkNearbyLocations(userLat, userLng) {
    if (!locations.value || !Array.isArray(locations.value)) return []

    const nearby = []

    locations.value.forEach(location => {
      const distance = calculateDistance(
        userLat,
        userLng,
        location.latitude,
        location.longitude
      )

      const radius = location.geofence_radius || 100

      if (distance <= radius) {
        nearby.push({
          ...location,
          distance,
          isInside: true
        })
      }
    })

    return nearby
  }

  function checkTriggeredReminders(userLat, userLng) {
    if (!reminders.value || !Array.isArray(reminders.value)) return []

    const triggered = []

    reminders.value.forEach(reminder => {
      if (!reminder.is_active || !reminder.location) return

      const distance = calculateDistance(
        userLat,
        userLng,
        reminder.location.latitude,
        reminder.location.longitude
      )

      const radius = reminder.location.geofence_radius || 100

      if (distance <= radius) {
        triggered.push({
          ...reminder,
          distance
        })
      }
    })

    return triggered
  }

  async function startTracking() {
    try {
      await getCurrentPosition()
      isTracking.value = true

      watchPosition((position) => {
        nearbyLocations.value = checkNearbyLocations(
          position.latitude,
          position.longitude
        )
        triggeredReminders.value = checkTriggeredReminders(
          position.latitude,
          position.longitude
        )
      })
    } catch (err) {
      console.error('Failed to start tracking:', err)
      isTracking.value = false
    }
  }

  function stopTracking() {
    isTracking.value = false
    nearbyLocations.value = []
    triggeredReminders.value = []
  }

  return {
    coords,
    nearbyLocations,
    triggeredReminders,
    isTracking,
    startTracking,
    stopTracking,
    checkNearbyLocations,
    checkTriggeredReminders,
    calculateDistance
  }
}
