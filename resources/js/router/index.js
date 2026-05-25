import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Import views
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Dashboard from '../views/Dashboard.vue';
import Locations from '../views/LocationsCRUD.vue';
import Reminders from '../views/RemindersCRUD.vue';
import Profile from '../views/Profile.vue';
import AlarmNotification from '../views/AlarmNotification.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { guest: true }
  },
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/locations',
    name: 'Locations',
    component: Locations,
    meta: { requiresAuth: true }
  },
  {
    path: '/reminders',
    name: 'Reminders',
    component: Reminders,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/alarm',
    name: 'AlarmNotification',
    component: AlarmNotification,
    meta: { requiresAuth: false } // Allow access without auth check for alarm page
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
  } else if (to.meta.requiresAuth && authStore.isAuthenticated && !authStore.emailVerified) {
    next({ path: '/login', query: { verify: '1' } });
  } else if (to.meta.guest && authStore.isFullyAuthenticated) {
    next('/dashboard');
  } else {
    next();
  }
});

export default router;
