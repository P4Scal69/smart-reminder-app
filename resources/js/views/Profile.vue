<template>
  <div class="app-shell py-8">
    <div class="mx-auto max-w-[1000px] px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="surface-card-soft mb-6 p-6">
        <div class="flex items-center gap-4">
          <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-600 to-cyan-500 text-2xl font-bold text-white">
            {{ userInitials }}
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Account Center</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ user?.name }}</h1>
            <p class="text-slate-500">{{ user?.email }}</p>
            <p class="mt-1 text-sm text-slate-400">Member since {{ memberSince }}</p>
          </div>
        </div>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="surface-card p-6">
          <div class="text-3xl font-bold text-brand-600">{{ stats.locations }}</div>
          <div class="mt-1 text-sm text-slate-500">Total Locations</div>
        </div>
        <div class="surface-card p-6">
          <div class="text-3xl font-bold text-emerald-600">{{ stats.reminders }}</div>
          <div class="mt-1 text-sm text-slate-500">Total Reminders</div>
        </div>
        <div class="surface-card p-6">
          <div class="text-3xl font-bold text-cyan-700">{{ stats.activeReminders }}</div>
          <div class="mt-1 text-sm text-slate-500">Active Reminders</div>
        </div>
      </div>

      <!-- Profile Settings -->
      <div class="surface-card p-6 mb-6">
        <h2 class="mb-4 text-lg font-bold text-slate-900">Profile Information</h2>
        <form @submit.prevent="updateProfile" class="space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
              required
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
              required
            />
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="resetForm"
              class="rounded-lg bg-slate-100 px-4 py-2 text-slate-700 transition hover:bg-slate-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded-lg bg-brand-600 px-4 py-2 text-white transition hover:bg-brand-700"
              :disabled="updating"
            >
              {{ updating ? 'Updating...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Change Password -->
      <div class="surface-card p-6 mb-6">
        <h2 class="mb-4 text-lg font-bold text-slate-900">Change Password</h2>
        <form @submit.prevent="changePassword" class="space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Current Password</label>
            <input
              v-model="passwordForm.currentPassword"
              type="password"
              :class="[
                'w-full rounded-lg px-4 py-2 focus:ring-2',
                passwordErrors.current
                  ? 'border border-red-300 focus:border-red-500 focus:ring-red-100'
                  : 'border border-slate-300 focus:border-brand-500 focus:ring-brand-200'
              ]"
              required
            />
            <p v-if="passwordErrors.current" class="mt-1 text-xs text-red-600">{{ passwordErrors.current }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">New Password</label>
            <input
              v-model="passwordForm.newPassword"
              type="password"
              :class="[
                'w-full rounded-lg px-4 py-2 focus:ring-2',
                passwordErrors.new
                  ? 'border border-red-300 focus:border-red-500 focus:ring-red-100'
                  : 'border border-slate-300 focus:border-brand-500 focus:ring-brand-200'
              ]"
              required
              minlength="8"
            />
            <p v-if="passwordErrors.new" class="mt-1 text-xs text-red-600">{{ passwordErrors.new }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Confirm New Password</label>
            <input
              v-model="passwordForm.confirmPassword"
              type="password"
              :class="[
                'w-full rounded-lg px-4 py-2 focus:ring-2',
                passwordErrors.confirm
                  ? 'border border-red-300 focus:border-red-500 focus:ring-red-100'
                  : 'border border-slate-300 focus:border-brand-500 focus:ring-brand-200'
              ]"
              required
            />
            <p v-if="passwordErrors.confirm" class="mt-1 text-xs text-red-600">{{ passwordErrors.confirm }}</p>
          </div>

          <div v-if="passwordErrors.general" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {{ passwordErrors.general }}
          </div>

          <div v-if="passwordSuccessMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            {{ passwordSuccessMessage }}
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              class="rounded-lg bg-emerald-600 px-4 py-2 text-white transition hover:bg-emerald-700"
              :disabled="changingPassword"
            >
              {{ changingPassword ? 'Changing...' : 'Change Password' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Settings -->
      <div class="surface-card p-6 mb-6">
        <h2 class="mb-4 text-lg font-bold text-slate-900">Notification Settings</h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium text-slate-900">Location Reminders</div>
              <div class="text-sm text-slate-500">Get notified when you enter a geofence</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="settings.locationReminders" type="checkbox" class="sr-only peer">
              <div class="h-6 w-11 rounded-full bg-slate-200 peer peer-checked:bg-brand-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium text-slate-900">Sound Alerts</div>
              <div class="text-sm text-slate-500">Play sound when reminder is triggered</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="settings.soundAlerts" type="checkbox" class="sr-only peer">
              <div class="h-6 w-11 rounded-full bg-slate-200 peer peer-checked:bg-brand-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium text-slate-900">Email Notifications</div>
              <div class="text-sm text-slate-500">Receive email summaries</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="settings.emailNotifications" type="checkbox" class="sr-only peer">
              <div class="h-6 w-11 rounded-full bg-slate-200 peer peer-checked:bg-brand-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="surface-card border-red-200 p-6">
        <h2 class="mb-4 text-lg font-bold text-red-600">Danger Zone</h2>
        <div class="space-y-3">
          <button
            @click="handleLogout"
            class="w-full rounded-lg bg-slate-100 px-4 py-3 font-medium text-slate-700 transition hover:bg-slate-200"
          >
            🚪 Logout
          </button>
          <button
            @click="confirmDeleteAccount"
            :disabled="deletingAccount"
            class="w-full rounded-lg bg-red-50 px-4 py-3 font-medium text-red-600 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {{ deletingAccount ? 'Deleting Account...' : 'Delete Account' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const userInitials = computed(() => {
  const name = user.value?.name || 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const memberSince = computed(() => {
  if (!user.value?.created_at) return 'Unknown'
  return new Date(user.value.created_at).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long'
  })
})

const stats = ref({
  locations: 0,
  reminders: 0,
  activeReminders: 0
})

const form = ref({
  name: '',
  email: ''
})

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const settings = ref({
  locationReminders: true,
  soundAlerts: true,
  emailNotifications: false
})

const updating = ref(false)
const changingPassword = ref(false)
const deletingAccount = ref(false)
const passwordSuccessMessage = ref('')
const passwordErrors = ref({
  current: '',
  new: '',
  confirm: '',
  general: ''
})

onMounted(async () => {
  try {
    await authStore.fetchProfile()
  } catch (error) {
    console.error('Error fetching profile:', error)
  }
  resetForm()
  await fetchStats()
  loadSettings()
})

function resetForm() {
  form.value = {
    name: user.value?.name || '',
    email: user.value?.email || ''
  }
}

async function fetchStats() {
  try {
    const [locationsRes, remindersRes] = await Promise.all([
      axios.get('/api/locations'),
      axios.get('/api/reminders')
    ])
    
    stats.value.locations = locationsRes.data.data.length
    stats.value.reminders = remindersRes.data.data.length
    stats.value.activeReminders = remindersRes.data.data.filter(r => r.is_active).length
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

function loadSettings() {
  try {
    const saved = localStorage.getItem('appSettings')
    if (saved) {
      settings.value = {
        ...settings.value,
        ...JSON.parse(saved)
      }
    }
  } catch (error) {
    console.error('Failed to load settings:', error)
  }
}

function saveSettings() {
  localStorage.setItem('appSettings', JSON.stringify(settings.value))
}

async function updateProfile() {
  updating.value = true
  try {
    const response = await axios.put('/api/profile', {
      name: form.value.name,
      email: form.value.email
    })

    authStore.user = response.data.data
    alert('Profile updated successfully!')
  } catch (error) {
    console.error('Failed to update profile:', error)
    alert('Failed to update profile')
  } finally {
    updating.value = false
  }
}

async function changePassword() {
  passwordSuccessMessage.value = ''
  passwordErrors.value = {
    current: '',
    new: '',
    confirm: '',
    general: ''
  }

  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    passwordErrors.value.confirm = 'New password and confirm password do not match.'
    return
  }

  if (passwordForm.value.currentPassword === passwordForm.value.newPassword) {
    passwordErrors.value.new = 'New password must be different from current password.'
    return
  }

  changingPassword.value = true
  try {
    await axios.post('/api/change-password', {
      current_password: passwordForm.value.currentPassword,
      password: passwordForm.value.newPassword,
      password_confirmation: passwordForm.value.confirmPassword
    })

    passwordSuccessMessage.value = 'Password changed successfully.'
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    }
  } catch (error) {
    console.error('Failed to change password:', error)
    const validationErrors = error?.response?.data?.errors

    if (validationErrors?.current_password?.length) {
      passwordErrors.value.current = validationErrors.current_password[0]
    }

    if (validationErrors?.password?.length) {
      passwordErrors.value.new = validationErrors.password[0]
    }

    if (!passwordErrors.value.current && !passwordErrors.value.new) {
      passwordErrors.value.general = error?.response?.data?.message || 'Failed to change password.'
    }
  } finally {
    changingPassword.value = false
  }
}

async function handleLogout() {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

function confirmDeleteAccount() {
  if (confirm('Are you sure you want to delete your account? This action cannot be undone!')) {
    if (confirm('This will permanently delete all your locations and reminders. Are you absolutely sure?')) {
      deleteAccount()
    }
  }
}

async function deleteAccount() {
  deletingAccount.value = true
  try {
    await axios.delete('/api/profile')
    localStorage.removeItem('pendingBackendProvisionEmail')
    alert('Account deleted successfully')
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Failed to delete account:', error)
    const message = error?.response?.data?.message || 'Failed to delete account'
    alert(message)
  } finally {
    deletingAccount.value = false
  }
}

// Watch settings changes
watch(settings, () => {
  saveSettings()
}, { deep: true })
</script>

<style scoped>
/* Custom styles if needed */
</style>
