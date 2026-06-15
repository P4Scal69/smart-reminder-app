<template>
  <div id="app" class="app-shell">
    <div v-if="authStore.isFullyAuthenticated" class="min-h-screen lg:flex">
      <aside
        class="fixed inset-y-0 left-0 z-40 w-72 border-r border-white/10 bg-black shadow-xl shadow-black/30 transition-all lg:static"
        :class="[
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
          sidebarCollapsed ? 'lg:w-20 lg:px-3 lg:py-4' : 'p-5 lg:w-72'
        ]"
      >
        <div class="flex items-center justify-between">
          <button
            type="button"
            class="flex items-center gap-3"
            :class="sidebarCollapsed ? 'lg:mx-auto' : ''"
            @click="toggleSidebarCollapsed"
            aria-label="Toggle sidebar collapse"
          >
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 text-lg font-bold text-white">SR</span>
            <div v-if="!sidebarCollapsed" class="text-left">
              <p class="text-lg font-bold text-white">Smart Reminder</p>
            </div>
          </button>
          <button
            class="rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15 lg:hidden"
            @click="sidebarOpen = false"
          >
            Close
          </button>
        </div>

        <nav class="mt-8 space-y-2">
          <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="top-nav-link flex items-center justify-between text-white/85 hover:bg-white/10 hover:text-white"
            :class="isRouteActive(item.to)
              ? '!bg-emerald-200 !py-3 !text-black hover:!bg-emerald-200 hover:!text-black'
              : ''"
            @click="handleNavClick"
          >
<span class="flex items-center gap-3" :class="sidebarCollapsed ? 'lg:mx-auto lg:justify-center' : ''">
               <span
                 class="grid h-9 w-9 place-items-center rounded-xl bg-white/10"
                 :class="isRouteActive(item.to) ? '!bg-black/10 !text-black' : ''"
               >
                 <component :is="iconComponent(item.icon)" class="h-5 w-5 text-white" />
               </span>
               <span v-if="!sidebarCollapsed">{{ item.label }}</span>
             </span>
          </router-link>
        </nav>

        <router-link
          v-if="!sidebarCollapsed"
          to="/profile"
          class="mt-8 block rounded-2xl border border-white/15 bg-white/5 p-4 text-white transition hover:-translate-y-0.5 hover:bg-white/10"
          @click="handleNavClick"
        >
          <p class="text-xs uppercase tracking-wider text-white/60">Signed in as</p>
          <p class="mt-1 font-semibold text-white">{{ authStore.user?.name || 'User' }}</p>
          <p class="mt-3 text-sm font-medium text-emerald-200">Open Profile</p>
        </router-link>
      </aside>

<div class="flex-1 lg:ml-0">
         <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/80 backdrop-blur-md">
           <div class="mx-auto flex max-w-[1200px] items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
             <div class="flex items-center gap-3">
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
         <main class="relative z-10 mx-auto max-w-[1200px] px-4 py-8 sm:px-6 lg:px-8">
           <router-view />
         </main>
       </div>
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
  sidebarOpen.value = window.matchMedia?.('(min-width: 1024px)')?.matches ?? true
})

const navItems = [
  { to: '/dashboard', label: 'Dashboard', icon: 'home' },
  { to: '/locations', label: 'Locations', icon: 'map-pin' },
  { to: '/reminders', label: 'Reminders', icon: 'bell' },
  { to: '/profile', label: 'Profile', icon: 'user' },
]

const isRouteActive = (path) => router.currentRoute.value.path.startsWith(path)

const iconComponent = (iconName) => {
  const icons = {
    home: {
      template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>'
    },
    'map-pin': {
      template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'
    },
    bell: {
      template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>'
    },
    user: {
      template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>'
    }
  }
  return icons[iconName] || null
}

const handleNavClick = () => {
  const isDesktop = window.matchMedia?.('(min-width: 1024px)')?.matches ?? false
  if (!isDesktop) sidebarOpen.value = false
}

const toggleSidebarCollapsed = () => {
  const isDesktop = window.matchMedia?.('(min-width: 1024px)')?.matches ?? false
  if (isDesktop) {
    sidebarCollapsed.value = !sidebarCollapsed.value
    return
  }

  router.push('/dashboard')
  sidebarOpen.value = false
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
