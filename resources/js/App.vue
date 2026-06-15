<template>
  <div id="app" class="app-shell">
    <div v-if="authStore.isFullyAuthenticated" class="min-h-screen lg:flex lg:ml-0">
      <!-- Sidebar (full on desktop, overlay on mobile) -->
      <aside
        class="fixed inset-y-0 left-0 z-40 w-64 border-r border-white/10 bg-black shadow-xl shadow-black/30 transition-all lg:static"
        :class="[
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
          sidebarCollapsed ? 'lg:w-20 lg:px-3 lg:py-4' : 'p-5 lg:w-72'
        ]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <button
              type="button"
              class="flex items-center gap-3"
              :class="sidebarCollapsed && 'lg:mx-auto'"
              @click="toggleSidebarCollapsed"
              aria-label="Toggle sidebar collapse"
            >
              <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 text-lg font-bold text-white">SR</span>
              <div class="text-left" :class="sidebarCollapsed && 'lg:hidden'">
                <p class="text-lg font-bold text-white">Smart Reminder</p>
              </div>
            </button>
          </div>
          <button
            class="rounded-xl border border-white/15 bg-white/10 p-2 text-white transition hover:bg-white/15 lg:hidden"
            @click="sidebarOpen = false"
            aria-label="Close sidebar"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <nav class="mt-8 space-y-2">
          <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="top-nav-link flex items-center text-white/85 hover:bg-white/10 hover:text-white"
            :class="isRouteActive(item.to)
              ? '!bg-emerald-200 !py-3 !text-black hover:!bg-emerald-200 hover:!text-black'
              : ''"
            @click="handleNavClick"
          >
            <span class="flex items-center gap-3 lg:justify-center" :class="sidebarCollapsed && 'lg:mx-auto'">
              <span
                class="grid h-9 w-9 place-items-center rounded-xl bg-white/10"
                :class="isRouteActive(item.to) ? '!bg-black/10 !text-black' : ''"
                v-html="iconSvg(item.icon)"
              ></span>
              <span v-show="!sidebarCollapsed">{{ item.label }}</span>
            </span>
          </router-link>
        </nav>

        <router-link
          to="/profile"
          class="mt-8 block rounded-2xl border border-white/15 bg-white/5 p-4 text-white transition hover:-translate-y-0.5 hover:bg-white/10 lg:hidden"
          @click="handleNavClick"
        >
          <p class="text-xs uppercase tracking-wider text-white/60">Signed in as</p>
          <p class="mt-1 font-semibold text-white">{{ authStore.user?.name || 'User' }}</p>
          <p class="mt-3 text-sm font-medium text-emerald-200">Open Profile</p>
        </router-link>
        <router-link
          to="/profile"
          class="mt-auto block rounded-2xl border border-white/15 bg-white/5 p-4 text-white transition hover:-translate-y-0.5 hover:bg-white/10 hidden lg:block"
          @click="handleNavClick"
        >
          <p class="text-xs uppercase tracking-wider text-white/60">Signed in as</p>
          <p class="mt-1 font-semibold text-white">{{ authStore.user?.name || 'User' }}</p>
          <p class="mt-3 text-sm font-medium text-emerald-200">Open Profile</p>
        </router-link>
      </aside>

      <div class="flex-1">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/80 backdrop-blur-md">
          <div class="mx-auto flex max-w-[1200px] items-center justify-between px-4 py-4 sm:px-6 lg:px-8 lg:ml-0" :class="{ 'lg:ml-20': sidebarCollapsed }">
            <div class="flex items-center gap-3">
              <button
                v-if="!sidebarOpen"
                @click="sidebarOpen = true"
                class="flex items-center justify-center rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
                aria-label="Open menu"
              >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
              </button>
              <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Smart Reminder</p>
                <h1 class="text-lg font-bold text-slate-900">Location Intelligence Dashboard</h1>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div class="hidden rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 sm:block">
                {{ authStore.user?.name || 'User' }}
              </div>
              <button @click="logout" class="btn-brand">Logout</button>
            </div>
          </div>
        </header>
        <main class="relative z-10 mx-auto max-w-[1200px] px-4 py-8 sm:px-6 lg:px-8" :class="{ 'lg:ml-20': sidebarCollapsed }">
          <router-view />
        </main>
      </div>

      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
        @click="sidebarOpen = false"
      ></div>
    </div>

    <router-view v-else />

    <ToastNotification ref="toast" />
  </div>
</template>

<script setup>
import { ref, provide, onMounted } from 'vue'
import { useAuthStore } from './stores/auth';
import { useRouter } from 'vue-router';
import ToastNotification from './components/ToastNotification.vue'

const authStore = useAuthStore();
const router = useRouter();
const toast = ref(null)
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)

onMounted(() => {
  // On desktop, sidebar is always visible
  sidebarOpen.value = true
  sidebarCollapsed.value = false
})

const navItems = [
  { to: '/dashboard', label: 'Dashboard', icon: 'home' },
  { to: '/locations', label: 'Locations', icon: 'map-pin' },
  { to: '/reminders', label: 'Reminders', icon: 'bell' },
  { to: '/profile', label: 'Profile', icon: 'user' },
]

const isRouteActive = (path) => router.currentRoute.value.path.startsWith(path)

const iconSvg = (iconName) => {
  const icons = {
    home: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
    'map-pin': '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
    bell: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>',
    user: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>'
  }
  return icons[iconName] || null
}

const handleNavClick = () => {
  // On mobile, close sidebar when a link is clicked
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false
  }
}

// Mobile: toggle the overlay sidebar
// Desktop: toggle between full and mini sidebar
const toggleSidebarCollapsed = () => {
  const isDesktop = window.matchMedia?.('(min-width: 1024px)')?.matches ?? false
  
  if (isDesktop) {
    sidebarCollapsed.value = !sidebarCollapsed.value
  } else {
    // On mobile, close sidebar when clicking SR logo (already open)
    sidebarOpen.value = false
  }
}

// Provide toast to all components
provide('toast', toast)

const logout = async () => {
  sidebarOpen.value = false
  await authStore.logout();
  router.push('/login');
};
</script>

<style>
/* Import Leaflet CSS */
@import 'leaflet/dist/leaflet.css';
</style>
