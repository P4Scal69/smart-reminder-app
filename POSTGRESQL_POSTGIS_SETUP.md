# PostgreSQL + PostGIS Setup Guide untuk Windows

Panduan lengkap untuk install dan setup PostgreSQL dengan ekstensi PostGIS di Windows untuk Smart Reminder App.

## 📥 Step 1: Download PostgreSQL

1. Buka website resmi PostgreSQL: https://www.postgresql.org/download/windows/
2. Klik **"Download the installer"** (dari EnterpriseDB)
3. Pilih versi PostgreSQL **15.x** atau **16.x** (recommended)
4. Download installer sesuai sistem (64-bit biasanya)

**Direct Link:** https://www.enterprisedb.com/downloads/postgres-postgresql-downloads

## 🔧 Step 2: Install PostgreSQL

1. **Jalankan installer** yang sudah didownload (sebagai Administrator)
2. Klik **Next** pada welcome screen
3. **Installation Directory:** Biarkan default (C:\Program Files\PostgreSQL\15)
4. **Select Components:** Pastikan semua tercentang:
   - ✅ PostgreSQL Server
   - ✅ pgAdmin 4 (GUI tool)
   - ✅ Stack Builder (penting untuk PostGIS!)
   - ✅ Command Line Tools
5. **Data Directory:** Biarkan default (C:\Program Files\PostgreSQL\15\data)
6. **Password:** Set password untuk superuser `postgres`
   - ⚠️ **PENTING:** Catat password ini! Nanti dipake di `.env`
   - Contoh: `postgres123` (jangan pake password ini di production!)
7. **Port:** Biarkan default `5432`
8. **Locale:** Biarkan default atau pilih `English, United States`
9. Klik **Next** sampai proses instalasi selesai
10. **JANGAN uncheck** "Launch Stack Builder at exit" - kita butuh ini untuk PostGIS!

## 🌍 Step 3: Install PostGIS via Stack Builder

Setelah PostgreSQL terinstall, Stack Builder akan muncul otomatis:

1. Pilih **PostgreSQL 15 (atau versi yang lo install)** dari dropdown
2. Klik **Next**
3. Expand kategori **"Spatial Extensions"**
4. Centang **PostGIS 3.x Bundle for PostgreSQL**
5. Klik **Next**
6. Pilih lokasi download (biarkan default)
7. Klik **Next** untuk download
8. Setelah download selesai, installer PostGIS akan jalan:
   - Klik **I Agree**
   - **Install Path:** Biarkan default
   - **Database Connection:**
     - User: `postgres`
     - Password: (password yang lo set tadi)
     - Server: `localhost`
     - Port: `5432`
   - Centang **"Create spatial database"** jika mau (optional, bisa manual nanti)
   - Klik **Next** dan **Install**
9. Tunggu sampai instalasi selesai

### ⚠️ Jika Stack Builder Tidak Muncul

Download manual dari: https://postgis.net/windows_downloads/
- Pilih versi PostGIS yang sesuai dengan PostgreSQL lo
- Install dengan cara yang sama

## 🗄️ Step 4: Buat Database

### Opsi A: Via pgAdmin 4 (GUI)

1. Buka **pgAdmin 4** dari Start Menu
2. Masukkan master password (buat password baru untuk pgAdmin)
3. Di sidebar kiri, expand **Servers → PostgreSQL 15**
4. Masukkan password `postgres` yang lo set tadi
5. Klik kanan **Databases** → **Create** → **Database**
6. **Database name:** `smart_reminder_db`
7. **Owner:** `postgres`
8. Klik **Save**
9. Klik kanan database baru → **Query Tool**
10. Jalankan command: 
    ```sql
    CREATE EXTENSION IF NOT EXISTS postgis;
    ```
11. Klik **Execute** (tombol play ▶️)
12. Verifikasi dengan: 
    ```sql
    SELECT PostGIS_version();
    ```

### Opsi B: Via Command Line (psql)

1. Buka **Command Prompt** atau **PowerShell**
2. Navigate ke folder PostgreSQL bin:
   ```powershell
   cd "C:\Program Files\PostgreSQL\15\bin"
   ```
3. Login ke PostgreSQL:
   ```powershell
   .\psql -U postgres
   ```
4. Masukkan password `postgres`
5. Buat database:
   ```sql
   CREATE DATABASE smart_reminder_db;
   ```
6. Connect ke database:
   ```sql
   \c smart_reminder_db
   ```
7. Enable PostGIS:
   ```sql
   CREATE EXTENSION IF NOT EXISTS postgis;
   ```
8. Verifikasi:
   ```sql
   SELECT PostGIS_version();
   ```
   Harusnya muncul versi PostGIS (contoh: `3.4 USE_GEOS=1 USE_PROJ=1...`)
9. Keluar:
   ```sql
   \q
   ```

## 🔑 Step 5: Update Laravel .env

Buka file `.env` di root project dan update konfigurasi database:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smart_reminder_db
DB_USERNAME=postgres
DB_PASSWORD=postgres123
```

**Ganti nilai sesuai setting lo!**

## 📦 Step 6: Install Dependencies Laravel

1. Pastikan composer dependencies sudah terinstall:
   ```powershell
   composer install
   ```

2. Verifikasi package spatial ada:
   ```powershell
   composer show matanyadaev/laravel-eloquent-spatial
   ```

## 🚀 Step 7: Run Migrations

1. Generate app key (jika belum):
   ```powershell
   php artisan key:generate
   ```

2. Clear cache:
   ```powershell
   php artisan config:clear
   php artisan cache:clear
   ```

3. Run migrations:
   ```powershell
   php artisan migrate
   ```

   Harusnya muncul output:
   ```
   INFO  Preparing database.
   
   Creating migration table ................................ 32ms DONE
   
   INFO  Running migrations.
   
   2014_10_12_000000_create_users_table ................... 45ms DONE
   2014_10_12_100000_create_password_resets_table ......... 28ms DONE
   2019_08_19_000000_create_failed_jobs_table ............. 35ms DONE
   2025_12_14_105032_create_locations_table ............... 52ms DONE
   2025_12_14_105156_create_reminders_table ............... 41ms DONE
   ```

## ✅ Step 8: Verifikasi Installation

### Test Database Connection

```powershell
php artisan tinker
```

Di Tinker console:
```php
DB::connection()->getPdo();
// Harusnya return PDO object

DB::select('SELECT PostGIS_version()');
// Harusnya return PostGIS version

\App\Models\Location::count();
// Harusnya return 0 (karena belum ada data)

exit
```

### Test Create Location

```powershell
php artisan tinker
```

In Tinker, run:
```php
use App\Models\User;
use App\Models\Location;
use MatanYadaev\EloquentSpatial\Objects\Point;

$user = User::factory()->create();

$location = $user->locations()->create([
    'name' => 'Test Location',
    'address' => 'Jakarta',
    'latitude' => -6.2088,
    'longitude' => 106.8456,
    'point' => new Point(-6.2088, 106.8456),
    'geofence_radius' => 500,
]);

echo "Location created with ID: " . $location->id;

exit
```

## 🔧 Troubleshooting

### Error: "could not connect to server"

**Solusi:**
1. Cek PostgreSQL service running:
   ```powershell
   Get-Service -Name postgresql*
   ```
2. Jika status Stopped, start service:
   ```powershell
   Start-Service -Name postgresql-x64-15
   ```

### Error: "extension postgis does not exist"

**Solusi:**
1. Install ulang PostGIS via Stack Builder
2. Atau manual enable via pgAdmin/psql:
   ```sql
   CREATE EXTENSION IF NOT EXISTS postgis;
   ```

### Error: "SQLSTATE[42P01]: Undefined table"

**Solusi:**
- Pastikan migrations sudah dijalankan:
  ```powershell
  php artisan migrate:status
  php artisan migrate
  ```

### Error: "Class 'MatanYadaev\EloquentSpatial\Objects\Point' not found"

**Solusi:**
```powershell
composer require matanyadaev/laravel-eloquent-spatial
composer dump-autoload
```

## 📚 Resources

- PostgreSQL Docs: https://www.postgresql.org/docs/
- PostGIS Documentation: https://postgis.net/documentation/
- Laravel Eloquent Spatial: https://github.com/MatanYadaev/laravel-eloquent-spatial
- pgAdmin Documentation: https://www.pgadmin.org/docs/

## 🎯 Next Steps

Setelah database setup selesai:

1. ✅ Setup authentication (Laravel Sanctum/Breeze)
2. ✅ Test API endpoints dengan Postman/Thunder Client
3. ✅ Buat seeders untuk dummy data
4. ✅ Implement frontend (Vue.js + Leaflet)
5. ✅ Setup background job untuk geofence checking
6. ✅ Implement push notifications

---

**Need Help?** Jika ada error atau masalah, paste error message-nya dan kita troubleshoot bareng! 🚀
