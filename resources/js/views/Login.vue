<template>
  <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-900 px-4 pb-10 pt-16">

        <!-- Custom map background layer -->
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
          <p class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-100/80">Smart Reminder</p>
          <h1 class="mt-4 text-4xl font-bold leading-tight">Never Miss a Place-Based Task Again.</h1>
          <p class="mt-4 max-w-sm text-sm text-blue-100/90">
            Geofence-powered reminders that trigger exactly when you arrive or leave your important locations.
          </p>
        </div>
      </section>

      <section class="bg-white/70 p-8 backdrop-blur-sm sm:p-10">
        <div class="mb-8">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Welcome back</p>
          <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Sign in to your account</h2>
          <p class="mt-2 text-sm text-slate-500">Access your locations, reminders, and live geofence dashboard.</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <div v-if="isRecoveryMode" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">
            <p class="text-sm font-medium">Password Recovery</p>
            <p class="mt-1 text-sm">Set a new password for your account.</p>
          </div>

          <div v-if="verificationMessage" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
            <p class="text-sm">{{ verificationMessage }}</p>
            <button
              type="button"
              class="mt-2 rounded-lg bg-amber-100 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-200"
              @click="resendVerificationEmail"
              :disabled="resendingVerification"
            >
              {{ resendingVerification ? 'Sending...' : 'Resend Verification Email' }}
            </button>
          </div>

          <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            {{ error }}
          </div>

          <div v-if="forgotSuccessMessage" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            {{ forgotSuccessMessage }}
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

          <div v-if="!isRecoveryMode">
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

          <template v-if="!isRecoveryMode">
            <button
              type="submit"
              :disabled="loading"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ loading ? 'Logging in...' : 'Login' }}
            </button>

            <button
              type="button"
              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
              @click="showForgotPassword = !showForgotPassword"
            >
              {{ showForgotPassword ? 'Hide Forgot Password' : 'Forgot Password?' }}
            </button>
          </template>

          <template v-else>
            <div>
              <label for="newPassword" class="mb-2 block text-sm font-medium text-slate-700">New Password</label>
              <input
                id="newPassword"
                v-model="recoveryForm.password"
                type="password"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
                placeholder="••••••••"
              />
            </div>

            <div v-if="recoveryForm.password">
              <label for="confirmNewPassword" class="mb-2 block text-sm font-medium text-slate-700">Confirm New Password</label>
              <input
                id="confirmNewPassword"
                v-model="recoveryForm.confirmPassword"
                type="password"
                required
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
                placeholder="••••••••"
              />
            </div>

            <button
              type="button"
              :disabled="resettingPassword"
              class="w-full rounded-xl bg-brand-600 px-4 py-3 font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
              @click="handlePasswordRecovery"
            >
              {{ resettingPassword ? 'Updating Password...' : 'Update Password' }}
            </button>
          </template>

          <div v-if="showForgotPassword && !isRecoveryMode" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-4">
            <p class="text-sm font-medium text-slate-800">Reset your password</p>
            <p class="mt-1 text-xs text-slate-500">We will send you a recovery email.</p>

            <div class="mt-3">
              <label for="forgotEmail" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
              <input
                id="forgotEmail"
                v-model="forgotEmail"
                type="email"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
                placeholder="john@example.com"
              />
            </div>

            <button
              type="button"
              :disabled="sendingResetEmail"
              class="mt-3 w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
              @click="sendPasswordReset"
            >
              {{ sendingResetEmail ? 'Sending...' : 'Send Reset Email' }}
            </button>
          </div>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
          Don't have an account?
          <router-link to="/register" class="font-semibold text-brand-600 hover:text-brand-700">
            Register
          </router-link>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { supabase } from '../supabaseClient';

const mapPatternUrl = '/images/map-pattern.png';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: ''
});

const loading = ref(false);
const error = ref('');
const verificationMessage = ref('');
const resendingVerification = ref(false);
const showForgotPassword = ref(false);
const forgotEmail = ref('');
const sendingResetEmail = ref(false);
const forgotSuccessMessage = ref('');
const isRecoveryMode = ref(false);
const resettingPassword = ref(false);

const recoveryForm = ref({
  password: '',
  confirmPassword: '',
});

onMounted(() => {
  if (route.query.verify === '1') {
    verificationMessage.value = 'Please verify your email before accessing the dashboard.';
  }

  if (typeof route.query.verifyEmail === 'string') {
    form.value.email = route.query.verifyEmail;
    verificationMessage.value = 'Account created. Check your email and verify before login.';
  }

  const hashParams = new URLSearchParams(window.location.hash.replace('#', ''));
  if (hashParams.get('type') === 'recovery') {
    isRecoveryMode.value = true;
    showForgotPassword.value = false;
    verificationMessage.value = '';
    forgotSuccessMessage.value = 'Recovery link verified. Set your new password below.';
  }
});

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  verificationMessage.value = '';

  try {
    await authStore.login(form.value.email, form.value.password);
    router.push('/dashboard');
  } catch (err) {
    if (err.verificationRequired) {
      verificationMessage.value = err.message;
    } else {
      error.value = err.response?.data?.message || err.message || 'Login failed. Please check your credentials.';
    }
  } finally {
    loading.value = false;
  }
};

const resendVerificationEmail = async () => {
  if (!form.value.email) {
    error.value = 'Enter your email first to resend verification.';
    return;
  }

  resendingVerification.value = true;
  error.value = '';

  try {
    const { error: resendError } = await supabase.auth.resend({
      type: 'signup',
      email: form.value.email,
    });

    if (resendError) {
      throw resendError;
    }

    verificationMessage.value = 'Verification email sent. Please check your inbox.';
  } catch (err) {
    error.value = err.message || 'Failed to resend verification email.';
  } finally {
    resendingVerification.value = false;
  }
};

const sendPasswordReset = async () => {
  const email = forgotEmail.value || form.value.email;
  if (!email) {
    error.value = 'Enter your email to send a reset link.';
    return;
  }

  sendingResetEmail.value = true;
  error.value = '';
  forgotSuccessMessage.value = '';

  try {
    const { error: resetError } = await supabase.auth.resetPasswordForEmail(email, {
      redirectTo: `${window.location.origin}/login`,
    });

    if (resetError) {
      throw resetError;
    }

    forgotSuccessMessage.value = 'Password reset email sent. Check your inbox and open the recovery link.';
  } catch (err) {
    error.value = err.message || 'Failed to send password reset email.';
  } finally {
    sendingResetEmail.value = false;
  }
};

const handlePasswordRecovery = async () => {
  if (!recoveryForm.value.password) {
    error.value = 'Please enter a new password.';
    return;
  }

  if (!recoveryForm.value.confirmPassword) {
    error.value = 'Please confirm your new password.';
    return;
  }

  if (recoveryForm.value.password !== recoveryForm.value.confirmPassword) {
    error.value = 'Passwords do not match.';
    return;
  }

  resettingPassword.value = true;
  error.value = '';

  try {
    const { error: updateError } = await supabase.auth.updateUser({
      password: recoveryForm.value.password,
    });

    if (updateError) {
      throw updateError;
    }

    forgotSuccessMessage.value = 'Password updated successfully. You can now sign in with your new password.';
    isRecoveryMode.value = false;
    recoveryForm.value.password = '';
    recoveryForm.value.confirmPassword = '';
    window.history.replaceState({}, document.title, '/login');
  } catch (err) {
    error.value = err.message || 'Failed to update password.';
  } finally {
    resettingPassword.value = false;
  }
};
</script>
