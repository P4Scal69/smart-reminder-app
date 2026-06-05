<template>
  <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-900 px-4 pb-10 pt-16">

    <!-- Custom map background layer (same as login) -->
    <div class="fixed inset-0 z-0 opacity-80">
      <img
        :src="mapPatternUrl"
        alt=""
        aria-hidden="true"
        class="h-full w-full object-cover brightness-110 contrast-125"
      />
    </div>

    <div class="pointer-events-none fixed inset-0 z-0">
      <div class="absolute -left-24 -top-32 h-80 w-80 rounded-full bg-brand-600/35 blur-3xl"></div>
      <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-cyan-500/25 blur-3xl"></div>
    </div>

    <div class="relative z-10 mx-auto grid w-full max-w-6xl max-h-[calc(100vh-6.5rem)] overflow-x-hidden overflow-y-auto rounded-3xl border border-white/10 bg-transparent shadow-[0_35px_90px_-40px_rgba(0,0,0,0.7)] md:grid-cols-2">
      <section class="hidden flex-col justify-between bg-gradient-to-br from-brand-600/65 via-brand-700/70 to-slate-900/75 p-10 text-white md:flex">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-100/80">Join Smart Reminder</p>
          <h1 class="mt-4 text-4xl font-bold leading-tight">Build Habits Around Places, Not Timers.</h1>
          <p class="mt-4 max-w-sm text-sm text-blue-100/90">
            Create account, define your geofences, and let context-aware reminders do the rest.
          </p>
        </div>
        <div class="rounded-2xl border border-white/15 bg-white/5 p-5">
          <p class="text-xs uppercase tracking-[0.2em] text-blue-100/80">Feature Highlight</p>
          <p class="mt-2 text-lg font-semibold">Entry and Exit Triggers</p>
          <p class="mt-1 text-sm text-blue-100/90">Notify users on arrival, departure, or both based on geofence type.</p>
        </div>
      </section>

      <section class="bg-white/70 p-8 backdrop-blur-sm sm:p-10">
        <div class="mb-8">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Create account</p>
          <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Start your reminder workspace</h2>
          <p class="mt-2 text-sm text-slate-500">Set up your account in seconds and continue to the dashboard.</p>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-5">
          <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            {{ error }}
          </div>

          <div>
            <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Full Name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
              placeholder="John Doe"
            />
          </div>

          <div>
            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
              placeholder="john@example.com"
            />
          </div>

          <div>
            <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
              placeholder="••••••••"
            />
          </div>

          <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
              placeholder="••••••••"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-xl bg-brand-600 px-4 py-3 font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ loading ? 'Creating Account...' : 'Register' }}
          </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
          Already have an account?
          <router-link to="/login" class="font-semibold text-brand-600 hover:text-brand-700">
            Login
          </router-link>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { getSupabaseClient } from '../supabaseClient';
import axios from 'axios';

const mapPatternUrl = '/images/map-pattern.png';

const router = useRouter();

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const loading = ref(false);
const error = ref('');

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const supabase = getSupabaseClient();
    // Register user with Supabase Auth first (email verification source of truth)
    const { data, error: supabaseError } = await supabase.auth.signUp({
      email: form.value.email,
      password: form.value.password,
      options: {
        emailRedirectTo: `${window.location.origin}/login`,
        data: {
          name: form.value.name
        }
      }
    });

    if (supabaseError) {
      const alreadyRegistered = /already registered|already exists|user already/i.test(supabaseError.message || '');

      if (alreadyRegistered) {
        localStorage.setItem('pendingBackendProvisionEmail', form.value.email);

        try {
          await supabase.auth.resend({
            type: 'signup',
            email: form.value.email,
          });
        } catch (resendError) {
          console.warn('Resend verification warning:', resendError);
        }

        router.push({
          path: '/login',
          query: {
            verifyEmail: form.value.email,
            verify: '1',
          },
        });
        return;
      }

      error.value = supabaseError.message;
      return;
    }

    console.log('User registered in Supabase:', data.user);
    
    // Register in Laravel backend so profile/locations/reminders are available after verification.
    try {
      await axios.post('/api/register', {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        supabase_user_id: data?.user?.id || null,
      });

      localStorage.removeItem('pendingBackendProvisionEmail');
    } catch (backendError) {
      // If backend account already exists, allow flow to continue with verification.
      if (backendError?.response?.status !== 422) {
        localStorage.setItem('pendingBackendProvisionEmail', form.value.email);
        console.warn('Laravel backend registration issue:', backendError);
      }
    }

    router.push({ path: '/login', query: { verifyEmail: form.value.email } });
  } catch (err) {
    error.value = err.message || 'Registration failed. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>
