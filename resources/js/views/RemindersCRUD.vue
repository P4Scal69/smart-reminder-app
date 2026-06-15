<template>
  <div class="app-shell py-8">
    <div class="px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="surface-card-soft mb-8 p-6">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Reminder Workflow</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Reminders</h1>
            <p class="mt-2 text-sm text-slate-600">Manage location-based reminders and trigger behavior.</p>
          </div>
          <button
            @click="openCreateModal"
            class="btn-brand flex items-center gap-2 px-6 py-3"
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
          class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
        >
          <option value="all">All Reminders</option>
          <option value="true">Active Only</option>
          <option value="false">Inactive Only</option>
        </select>
      </div>

      <!-- Reminders List -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block h-12 w-12 animate-spin rounded-full border-b-2 border-brand-600"></div>
        <p class="mt-4 text-slate-600">Loading reminders...</p>
      </div>

      <div v-else-if="reminders.length === 0" class="surface-card py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <h3 class="mt-4 text-lg font-semibold text-slate-900">No reminders yet</h3>
        <p class="mt-2 text-sm text-slate-500">Create your first reminder to get started.</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="reminder in reminders"
          :key="reminder.id"
          class="surface-card p-6 transition-all duration-200 hover:-translate-y-0.5"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-semibold text-slate-900">{{ reminder.title }}</h3>
                <span
                  :class="reminder.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'"
                  class="px-3 py-1 rounded-full text-xs font-medium"
                >
                  {{ reminder.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <p v-if="reminder.description" class="mb-3 text-slate-600">{{ reminder.description }}</p>

              <div class="flex items-center gap-2 text-sm text-slate-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                <span class="font-medium">{{ reminder.location?.name || 'Unknown Location' }}</span>
                <span v-if="reminder.location?.address" class="text-slate-400">• {{ reminder.location.address }}</span>
              </div>

              <div class="flex gap-2 mt-3">
                <span 
                  v-if="reminder.trigger_type === 'entry' || reminder.trigger_on_enter"
                  class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2 py-1 text-xs font-medium text-brand-700"
                >
                  📥 On Entry
                </span>
                <span 
                  v-if="reminder.trigger_type === 'exit' || reminder.trigger_on_exit"
                  class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-700"
                >
                  📤 On Exit
                </span>
              </div>
            </div>

            <div class="flex flex-col gap-2 ml-4">
              <button
                @click="toggleActive(reminder)"
                :class="reminder.is_active ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'"
                class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition"
              >
                {{ reminder.is_active ? 'Deactivate' : 'Activate' }}
              </button>
              <button
                @click="openEditModal(reminder)"
                class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200"
              >
                Edit
              </button>
              <button
                @click="deleteReminder(reminder.id)"
                class="rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-200"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl">
          <div class="p-6">
            <h2 class="mb-6 text-2xl font-bold text-slate-900">
              {{ isEditing ? 'Edit Reminder' : 'Create Reminder' }}
            </h2>

            <form @submit.prevent="submitForm" class="space-y-4">
              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Title</label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                  placeholder="Buy groceries, Call mom, etc."
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                  placeholder="Additional details..."
                ></textarea>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Location</label>
                <select
                  v-model="form.location_id"
                  required
                  class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
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
                  class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                />
                <label for="is_active" class="ml-2 block text-sm text-slate-700">Active</label>
              </div>

              <div>
                <label class="mb-3 block text-sm font-medium text-slate-700">Trigger Type</label>
                <div class="space-y-2">
                  <div class="flex items-center">
                    <input
                      v-model="form.trigger_type"
                      type="radio"
                      id="trigger_entry"
                      value="entry"
                      class="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500"
                    />
                    <label for="trigger_entry" class="ml-3 block text-sm text-slate-700">
                      <span class="font-medium">📥 Entering Location</span>
                      <span class="mt-0.5 block text-xs text-slate-500">Reminder triggers when you enter the geofence area</span>
                    </label>
                  </div>
                  <div class="flex items-center">
                    <input
                      v-model="form.trigger_type"
                      type="radio"
                      id="trigger_exit"
                      value="exit"
                      class="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500"
                    />
                    <label for="trigger_exit" class="ml-3 block text-sm text-slate-700">
                      <span class="font-medium">📤 Leaving Location</span>
                      <span class="mt-0.5 block text-xs text-slate-500">Reminder triggers when you leave the geofence area</span>
                    </label>
                  </div>
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
  trigger_type: 'entry' // 'entry' or 'exit'
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
    trigger_type: 'entry'
  };
  formError.value = '';
  showModal.value = true;
};

const openEditModal = (reminder) => {
  isEditing.value = true;
  editingId.value = reminder.id;
  
  // Convert old format to new format
  let triggerType = 'entry'; // default
  if (reminder.trigger_type) {
    triggerType = reminder.trigger_type;
  } else if (reminder.trigger_on_exit) {
    triggerType = 'exit';
  }
  
  form.value = {
    title: reminder.title,
    description: reminder.description || '',
    location_id: reminder.location_id,
    is_active: reminder.is_active,
    trigger_type: triggerType
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
