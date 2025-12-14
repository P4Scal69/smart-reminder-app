# Smart Reminder by Location - Setup Guide

## 📋 Ringkasan Proyek

Aplikasi Smart Reminder by Location menggunakan:
- **Backend**: Laravel 12 (PHP)
- **Database**: PostgreSQL + PostGIS
- **Frontend**: Inertia.js + Vue 3
- **Package**: MatanYadaev Laravel Eloquent Spatial

## ✅ Yang Sudah Dibuat

### 1. Package PostGIS
- ✅ Installed `matanyadaev/laravel-eloquent-spatial` v4.5.0
- Package ini mendukung PostGIS geometry types di Laravel

### 2. Database Migrations

#### Migration: `create_locations_table`
**File**: `database/migrations/2025_12_14_105032_create_locations_table.php`

**Struktur Tabel**:
- `id` - Primary key
- `user_id` - Foreign key ke users table (cascade delete)
- `name` - Nama lokasi
- `address` - Alamat lengkap (nullable)
- `latitude` - Latitude (decimal 10,8)
- `longitude` - Longitude (decimal 11,8)
- `geofence_radius` - Radius geofence dalam meter (default: 100)
- `point` - Geometry POINT (4326) - koordinat lokasi
- `geofence_area` - Geometry POLYGON (4326) - area geofence circular
- `notes` - Catatan tambahan (nullable)
- `timestamps` - created_at & updated_at

**Fitur Khusus**:
- Enable PostGIS extension otomatis
- Spatial indexes untuk performa query
- SRID 4326 (WGS84 - standar GPS)

#### Migration: `create_reminders_table`
**File**: `database/migrations/2025_12_14_105156_create_reminders_table.php`

**Struktur Tabel**:
- `id` - Primary key
- `user_id` - Foreign key ke users table (cascade delete)
- `location_id` - Foreign key ke locations table (cascade delete)
- `title` - Judul reminder
- `description` - Deskripsi detail (nullable)
- `is_active` - Status aktif/nonaktif (default: true)
- `triggered_at` - Waktu terakhir reminder dipicu (nullable)
- `trigger_count` - Jumlah kali reminder dipicu (default: 0)
- `timestamps` - created_at & updated_at

**Indexes**:
- Composite index pada (user_id, is_active)
- Index pada location_id

### 3. Eloquent Models

#### Model: Location
**File**: `app/Models/Location.php`

**Fitur**:
- ✅ PostGIS support dengan trait `HasSpatial`
- ✅ Auto-casting Point dan Polygon geometry
- ✅ Relationship ke User (belongsTo)
- ✅ Relationship ke Reminder (hasMany)
- ✅ Auto-generate Point dan Polygon saat save
- ✅ Method `isWithinGeofence($lat, $lng)` - cek apakah koordinat dalam geofence
- ✅ Scope `withinDistance($lat, $lng, $distance)` - query lokasi dalam jarak tertentu

**Methods Penting**:
```php
// Cek apakah koordinat user dalam geofence
$location->isWithinGeofence($userLat, $userLng);

// Query lokasi dalam radius tertentu
Location::withinDistance($userLat, $userLng, 1000)->get();

// Akses geometry
$location->point; // Point object
$location->geofence_area; // Polygon object
```

#### Model: Reminder
**File**: `app/Models/Reminder.php`

**Fitur**:
- ✅ Relationship ke User (belongsTo)
- ✅ Relationship ke Location (belongsTo)
- ✅ Scopes: active(), inactive(), forUser(), untriggered(), recentlyTriggered()
- ✅ Helper methods untuk activate/deactivate/toggle
- ✅ Method `shouldTrigger($lat, $lng)` - cek apakah reminder harus dipicu

**Methods Penting**:
```php
// Cek apakah reminder harus dipicu
$reminder->shouldTrigger($userLat, $userLng);

// Mark reminder sebagai triggered
$reminder->markAsTriggered();

// Activate/deactivate
$reminder->activate();
$reminder->deactivate();
$reminder->toggleActive();

// Query dengan scopes
Reminder::active()->get();
Reminder::forUser($userId)->untriggered()->get();
```

#### Model: User (Updated)
**File**: `app/Models/User.php`

**Relationships Ditambahkan**:
- ✅ `locations()` - HasMany ke Location
- ✅ `reminders()` - HasMany ke Reminder
- ✅ `activeReminders()` - HasMany ke Reminder (hanya yang aktif)

## 🚀 Langkah Selanjutnya

### 1. Setup Database PostgreSQL dengan PostGIS

Pastikan PostgreSQL dan PostGIS sudah terinstall, lalu buat database:

```sql
CREATE DATABASE smart_reminder_app;
\c smart_reminder_app
CREATE EXTENSION postgis;
```

### 2. Configure .env

Update file `.env` dengan konfigurasi PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smart_reminder_app
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Test Models di Tinker

```bash
php artisan tinker
```

Contoh testing:

```php
// Create location
$user = User::first();
$location = $user->locations()->create([
    'name' => 'Kampus UGM',
    'address' => 'Bulaksumur, Yogyakarta',
    'latitude' => -7.771176,
    'longitude' => 110.377956,
    'geofence_radius' => 500, // 500 meters
    'notes' => 'Kampus utama'
]);

// Create reminder
$reminder = $location->reminders()->create([
    'user_id' => $user->id,
    'title' => 'Kuliah Pagi',
    'description' => 'Jangan lupa bawa buku catatan',
    'is_active' => true
]);

// Test geofence
$isInside = $location->isWithinGeofence(-7.771176, 110.377956);
// true - karena koordinat sama persis

// Test should trigger
$shouldTrigger = $reminder->shouldTrigger(-7.771176, 110.377956);
// true - reminder akan dipicu
```

## 📝 Fitur yang Perlu Dibuat Selanjutnya

### Backend (Laravel):
1. **Controllers**:
   - LocationController (CRUD locations)
   - ReminderController (CRUD reminders)
   - GeofenceController (check user position, trigger reminders)

2. **API Routes**:
   - POST /api/locations - Create location
   - GET /api/locations - Get user's locations
   - POST /api/reminders - Create reminder
   - GET /api/reminders - Get user's reminders
   - POST /api/check-location - Check if user in geofence

3. **Services**:
   - GeofenceService - Handle geofencing logic
   - NotificationService - Send notifications when triggered

4. **Jobs/Queues**:
   - CheckUserLocationJob - Background job untuk cek lokasi user
   - SendReminderNotificationJob - Send push notification

### Frontend (Inertia + Vue 3):
1. **Pages**:
   - Dashboard - Overview reminders & locations
   - Locations/Index - List locations
   - Locations/Create - Form tambah location dengan map
   - Locations/Edit - Edit location
   - Reminders/Index - List reminders
   - Reminders/Create - Form tambah reminder
   - Reminders/Edit - Edit reminder

2. **Components**:
   - MapPicker - Component untuk pilih lokasi di map
   - GeofenceVisualizer - Visualisasi area geofence
   - ReminderCard - Card untuk display reminder
   - LocationCard - Card untuk display location

3. **Services**:
   - GeolocationService - Get user's current position
   - MapService - Integration dengan Leaflet/Google Maps

## 🗺️ Contoh Penggunaan Map Library

### Option 1: Leaflet (Open Source - Recommended)
```bash
npm install leaflet vue-leaflet
```

### Option 2: Google Maps
```bash
npm install @googlemaps/js-api-loader
```

## 📊 Database Schema Visual

```
users
  ├── id
  ├── name
  ├── email
  └── password

locations
  ├── id
  ├── user_id (FK -> users)
  ├── name
  ├── address
  ├── latitude
  ├── longitude
  ├── geofence_radius
  ├── point (GEOMETRY)
  ├── geofence_area (GEOMETRY)
  └── notes

reminders
  ├── id
  ├── user_id (FK -> users)
  ├── location_id (FK -> locations)
  ├── title
  ├── description
  ├── is_active
  ├── triggered_at
  └── trigger_count
```

## 🔍 Query Examples

### Find locations near user
```php
$nearbyLocations = Location::withinDistance(
    $userLat, 
    $userLng, 
    5000 // 5km radius
)->get();
```

### Get active reminders that should trigger
```php
$user = auth()->user();
$activeReminders = $user->reminders()
    ->with('location')
    ->active()
    ->get()
    ->filter(fn($reminder) => $reminder->shouldTrigger($userLat, $userLng));
```

### Get recently triggered reminders
```php
$recentReminders = Reminder::forUser($userId)
    ->recentlyTriggered(24) // last 24 hours
    ->with('location')
    ->get();
```

## 🎯 Tips Development

1. **Testing Geofence**: Gunakan koordinat dummy dulu sebelum integrate GPS real
2. **Performance**: Spatial indexes sudah dibuat, tapi monitor query performance saat data banyak
3. **SRID 4326**: Ini adalah standar GPS (WGS84), jangan ubah kecuali ada kebutuhan khusus
4. **Accuracy**: Geofence radius minimal 50-100 meter untuk accuracy GPS mobile
5. **Battery**: Jangan polling GPS terlalu sering, use geolocation events atau interval 30-60 detik

## 📚 Resources

- [Laravel Eloquent Spatial Docs](https://github.com/MatanYadaev/laravel-eloquent-spatial)
- [PostGIS Documentation](https://postgis.net/documentation/)
- [Inertia.js Guide](https://inertiajs.com/)
- [Leaflet Documentation](https://leafletjs.com/)

## ⚠️ Important Notes

1. Pastikan PostGIS extension enabled di PostgreSQL
2. Model Location auto-generate Point dan Polygon, tidak perlu manual set
3. Coordinate system: SRID 4326 (latitude/longitude WGS84)
4. Geofence radius dalam meter (integer)
5. Point geometry: Point(latitude, longitude)

---

**Created**: December 14, 2025
**Laravel Version**: 12.x
**PHP Version**: 8.1+
**PostgreSQL**: 14+ with PostGIS 3.x
