<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Reminders</h1>
            <p class="mt-2 text-sm text-gray-600">Manage your location-based reminders</p>
          </div>
          <button
            @click="openCreateModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-150 ease-in-out flex items-center gap-2"
          >
            <span class="text-xl">+</span>
            Add Reminder
          </button>
        </div>
      </div>

      <!-- Filter -->
      <div class="mb-6 flex gap-4">
        <select
          v-model="filterActive"
          @change="fetchReminders"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="all">All Reminders</option>
          <option value="true">Active Only</option>
          <option value="false">Inactive Only</option>
        </select>
      </div>

      <!-- Reminders List -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading reminders...</p>
      </div>

      <div v-else-if="reminders.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No reminders yet</h3>
        <p class="mt-2 text-sm text-gray-500">Create your first reminder to get started.</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="reminder in reminders"
          :key="reminder.id"
          class="bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200 p-6"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-semibold text-gray-900">{{ reminder.title }}</h3>
                <span
                  :class="reminder.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                  class="px-3 py-1 rounded-full text-xs font-medium"
                >
                  {{ reminder.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <p v-if="reminder.description" class="text-gray-600 mb-3">{{ reminder.description }}</p>

              <div class="flex items-center gap-2 text-sm text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                <span class="font-medium">{{ reminder.location?.name || 'Unknown Location' }}</span>
                <span v-if="reminder.location?.address" class="text-gray-400">• {{ reminder.location.address }}</span>
              </div>

              <div class="flex gap-4 mt-3 text-xs text-gray-500">
                <span v-if="reminder.trigger_on_enter">✓ Trigger on Enter</span>
                <span v-if="reminder.trigger_on_exit">✓ Trigger on Exit</span>
              </div>
            </div>

            <div class="flex flex-col gap-2 ml-4">
              <button
                @click="toggleActive(reminder)"
                :class="reminder.is_active ? 'bg-yellow-100 hover:bg-yellow-200 text-yellow-800' : 'bg-green-100 hover:bg-green-200 text-green-800'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap"
              >
                {{ reminder.is_active ? 'Deactivate' : 'Activate' }}
              </button>
              <button
                @click="openEditModal(reminder)"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition"
              >
                Edit
              </button>
              <button
                @click="deleteReminder(reminder.id)"
                class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
              {{ isEditing ? 'Edit Reminder' : 'Create Reminder' }}
            </h2>

            <form @submit.prevent="submitForm" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Buy groceries, Call mom, etc."
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Additional details..."
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                <select
                  v-model="form.location_id"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Select a location</option>
                  <option v-for="location in locations" :key="location.id" :value="location.id">
                    {{ location.name }} - {{ location.address }}
                  </option>
                </select>
              </div>

              <div class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  id="is_active"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
              </div>

              <div class="flex items-center">
                <input
                  v-model="form.trigger_on_enter"
                  type="checkbox"
                  id="trigger_on_enter"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="trigger_on_enter" class="ml-2 block text-sm text-gray-700">Trigger when entering location</label>
              </div>

              <div class="flex items-center">
                <input
                  v-model="form.trigger_on_exit"
                  type="checkbox"
                  id="trigger_on_exit"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="trigger_on_exit" class="ml-2 block text-sm text-gray-700">Trigger when leaving location</label>
              </div>

              <div v-if="formError" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                {{ formError }}
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  type="button"
                  @click="closeModal"
                  class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition disabled:opacity-50"
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
import { ref, onMounted } from 'vue';
import axios from 'axios';

const reminders = ref([]);
const locations = ref([]);
const loading = ref(true);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const submitting = ref(false);
const formError = ref('');
const filterActive = ref('all');

const form = ref({
  title: '',
  description: '',
  location_id: '',
  is_active: true,
  trigger_on_enter: true,
  trigger_on_exit: false
});

const fetchLocations = async () => {
  try {
    const response = await axios.get('/api/locations');
    locations.value = response.data.data;
  } catch (error) {
    console.error('Error fetching locations:', error);
  }
};

const fetchReminders = async () => {
  try {
    loading.value = true;
    let url = '/api/reminders';
    if (filterActive.value !== 'all') {
      url += `?active=${filterActive.value}`;
    }
    const response = await axios.get(url);
    reminders.value = response.data.data;
  } catch (error) {
    console.error('Error fetching reminders:', error);
    alert('Failed to load reminders');
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = {
    title: '',
    description: '',
    location_id: '',
    is_active: true,
    trigger_on_enter: true,
    trigger_on_exit: false
  };
  formError.value = '';
  showModal.value = true;
};

const openEditModal = (reminder) => {
  isEditing.value = true;
  editingId.value = reminder.id;
  form.value = {
    title: reminder.title,
    description: reminder.description || '',
    location_id: reminder.location_id,
    is_active: reminder.is_active,
    trigger_on_enter: reminder.trigger_on_enter,
    trigger_on_exit: reminder.trigger_on_exit
  };
  formError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  formError.value = '';
};

const submitForm = async () => {
  try {
    submitting.value = true;
    formError.value = '';

    if (isEditing.value) {
      await axios.put(`/api/reminders/${editingId.value}`, form.value);
    } else {
      await axios.post('/api/reminders', form.value);
    }

    closeModal();
    fetchReminders();
  } catch (error) {
    console.error('Error saving reminder:', error);
    formError.value = error.response?.data?.message || 'Failed to save reminder';
  } finally {
    submitting.value = false;
  }
};

const toggleActive = async (reminder) => {
  try {
    await axios.put(`/api/reminders/${reminder.id}`, {
      is_active: !reminder.is_active
    });
    fetchReminders();
  } catch (error) {
    console.error('Error toggling reminder:', error);
    alert('Failed to update reminder');
  }
};

const deleteReminder = async (id) => {
  if (!confirm('Are you sure you want to delete this reminder?')) {
    return;
  }

  try {
    await axios.delete(`/api/reminders/${id}`);
    fetchReminders();
  } catch (error) {
    console.error('Error deleting reminder:', error);
    alert('Failed to delete reminder');
  }
};

onMounted(async () => {
  await fetchLocations();
  await fetchReminders();
});
</script>
