# Frontend Setup Guide - Smart Reminder App

## 🎨 Frontend Technology Stack

- **Framework**: Vue.js 3 (Composition API)
- **Routing**: Vue Router 4
- **State Management**: Pinia
- **UI Framework**: Tailwind CSS 4.0 (already configured)
- **HTTP Client**: Axios (already installed)
- **Maps**: Leaflet.js
- **Build Tool**: Vite (already configured)

---

## 📦 Prerequisites

### Install Node.js (if not already installed)

1. Download Node.js from: https://nodejs.org/
2. Choose the **LTS version** (recommended)
3. Run the installer
4. Verify installation:
```powershell
node --version
npm --version
```

---

## 🚀 Setup Instructions

### Step 1: Install Frontend Dependencies

```powershell
npm install
```

### Step 2: Install Vue.js and Related Packages

```powershell
npm install vue@next vue-router@4 pinia
```

### Step 3: Install Leaflet for Maps

```powershell
npm install leaflet vue3-leaflet
```

### Step 4: Install Additional UI Libraries

```powershell
npm install @headlessui/vue @heroicons/vue
```

---

## 📁 Project Structure

After setup, the structure will be:

```
resources/
├── js/
│   ├── app.js              # Main entry point
│   ├── router/
│   │   └── index.js        # Vue Router configuration
│   ├── stores/
│   │   ├── auth.js         # Authentication store
│   │   ├── location.js     # Location store
│   │   └── reminder.js     # Reminder store
│   ├── components/
│   │   ├── Layout/
│   │   │   ├── AppLayout.vue
│   │   │   ├── Navbar.vue
│   │   │   └── Sidebar.vue
│   │   ├── Map/
│   │   │   ├── LeafletMap.vue
│   │   │   └── LocationMarker.vue
│   │   ├── Location/
│   │   │   ├── LocationList.vue
│   │   │   ├── LocationForm.vue
│   │   │   └── LocationCard.vue
│   │   └── Reminder/
│   │       ├── ReminderList.vue
│   │       ├── ReminderForm.vue
│   │       └── ReminderCard.vue
│   ├── views/
│   │   ├── Auth/
│   │   │   ├── Login.vue
│   │   │   └── Register.vue
│   │   ├── Dashboard.vue
│   │   ├── Locations/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   └── Edit.vue
│   │   └── Reminders/
│   │       ├── Index.vue
│   │       ├── Create.vue
│   │       └── Edit.vue
│   └── utils/
│       ├── api.js          # Axios configuration
│       └── helpers.js      # Helper functions
├── css/
│   └── app.css             # Tailwind CSS
└── views/
    └── app.blade.php       # Laravel Blade template (SPA entry)
```

---

## 🔧 Configuration Files to Update

### 1. package.json (Updated)

```json
{
    "$schema": "https://www.schemastore.org/package.json",
    "private": true,
    "type": "module",
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "preview": "vite preview"
    },
    "dependencies": {
        "vue": "^3.5.13",
        "vue-router": "^4.4.5",
        "pinia": "^2.2.6",
        "axios": "^1.11.0",
        "leaflet": "^1.9.4",
        "vue3-leaflet": "^1.0.39"
    },
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "@vitejs/plugin-vue": "^5.2.1",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "^2.0.0",
        "tailwindcss": "^4.0.0",
        "vite": "^7.0.7"
    }
}
```

### 2. vite.config.js (Updated)

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
```

---

## 🎯 Quick Start Commands

### Development Mode (Hot Reload)

```powershell
# Terminal 1: Run Laravel backend
php artisan serve

# Terminal 2: Run Vite dev server
npm run dev
```

### Production Build

```powershell
npm run build
```

---

## 🔐 Environment Variables

Create `.env.local` or add to existing `.env`:

```env
VITE_APP_NAME="Smart Reminder"
VITE_API_URL=http://127.0.0.1:8000
```

Access in Vue components:
```javascript
const apiUrl = import.meta.env.VITE_API_URL;
```

---

## 🗺️ Leaflet CSS

Add to `resources/css/app.css`:

```css
@import 'leaflet/dist/leaflet.css';

@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom map styles */
.leaflet-container {
    width: 100%;
    height: 100%;
}
```

---

## 📱 Features to Implement

### Phase 1: Authentication
- [ ] Login page
- [ ] Register page
- [ ] Protected routes
- [ ] Token management

### Phase 2: Locations
- [ ] Location list with map
- [ ] Add/Edit location form
- [ ] Location detail view
- [ ] Delete location
- [ ] Geofence visualization

### Phase 3: Reminders
- [ ] Reminder list
- [ ] Create/Edit reminder
- [ ] Toggle active status
- [ ] Link to locations

### Phase 4: Dashboard
- [ ] Overview statistics
- [ ] Recent activity
- [ ] Active reminders
- [ ] Map with all locations

### Phase 5: Advanced Features
- [ ] Real-time geofence checking
- [ ] Push notifications
- [ ] Location sharing
- [ ] Export/Import data

---

## 🎨 UI Components

### Recommended Component Libraries

1. **Headless UI** (already recommended) - Unstyled, accessible components
2. **Heroicons** (already recommended) - SVG icons
3. **Vue Toastification** - Toast notifications
4. **VueUse** - Composition utilities

Install additional:
```powershell
npm install @vueuse/core vue-toastification@next
```

---

## 🧪 Testing

Install testing dependencies:
```powershell
npm install --save-dev vitest @vue/test-utils jsdom
```

Add to `package.json`:
```json
"scripts": {
    "test": "vitest",
    "test:ui": "vitest --ui"
}
```

---

## 📝 Next Steps

1. Install Node.js (if not installed)
2. Run `npm install`
3. Install Vue and related packages
4. Create basic Vue components
5. Set up routing
6. Implement authentication flow
7. Build location management UI
8. Add map integration
9. Implement reminder management
10. Add real-time features

---

## ⚡ Pro Tips

- Use Vue DevTools browser extension for debugging
- Enable HMR (Hot Module Replacement) for faster development
- Use Pinia DevTools for state management debugging
- Leverage Tailwind CSS utilities for rapid UI development
- Use Vue Router's navigation guards for auth protection

---

## 🆘 Common Issues

### Issue: npm command not found
**Solution**: Install Node.js from nodejs.org

### Issue: Vite not starting
**Solution**: Delete `node_modules` and run `npm install` again

### Issue: Vue components not hot reloading
**Solution**: Check vite.config.js and ensure vue plugin is installed

### Issue: Tailwind styles not applied
**Solution**: Ensure `@tailwind` directives are in app.css

---

## 📚 Resources

- Vue.js Docs: https://vuejs.org/
- Vue Router: https://router.vuejs.org/
- Pinia: https://pinia.vuejs.org/
- Leaflet: https://leafletjs.com/
- Tailwind CSS: https://tailwindcss.com/
- Vite: https://vitejs.dev/

---

**Ready to build the frontend!** 🚀
