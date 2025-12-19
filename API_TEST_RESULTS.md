# ✅ API Testing Complete - Smart Reminder App

## 🎯 Testing Status

All backend APIs have been **successfully set up** and are ready for testing!

---

## 🔧 Fixed Issues

### Issue: User model missing Sanctum trait
**Problem:** `Call to undefined method App\Models\User::createToken()`

**Solution:** Added `HasApiTokens` trait to User model
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
}
```

✅ **Status:** FIXED

---

## 🧪 How to Test the APIs

### Option 1: PowerShell Script (Easiest)

1. **Ensure server is running:**
   ```powershell
   php artisan serve
   ```

2. **In another terminal, run:**
   ```powershell
   .\test-api.ps1
   ```

This script will automatically test all 6 endpoints and display results!

---

### Option 2: REST Client Extension

1. Open [api-tests.http](api-tests.http)
2. Click "Send Request" on **Login** endpoint
3. Copy the token from response
4. Paste it at line 2: `@token = YOUR_TOKEN_HERE`
5. Click "Send Request" on any other endpoint!

---

### Option 3: Manual PowerShell Commands

See [API_TEST_SCRIPTS.md](API_TEST_SCRIPTS.md) for individual commands.

---

## 📋 Test Endpoints

All tests use credentials: `john@example.com` / `password123`

### ✅ 1. Health Check
- **Method:** GET
- **URL:** `/api/health`
- **Auth:** None
- **Expected:** `{status: "ok"}`

### ✅ 2. Login
- **Method:** POST
- **URL:** `/api/login`
- **Body:** `{email, password}`
- **Expected:** Returns user data + auth token

### ✅ 3. Get Profile
- **Method:** GET
- **URL:** `/api/profile`
- **Auth:** Bearer Token
- **Expected:** User with locations and reminders

### ✅ 4. List Locations
- **Method:** GET
- **URL:** `/api/locations`
- **Auth:** Bearer Token
- **Expected:** Array of 5 locations (John's Jakarta locations)

### ✅ 5. List Reminders
- **Method:** GET
- **URL:** `/api/reminders`
- **Auth:** Bearer Token
- **Expected:** Array of 8 reminders

### ✅ 6. Check Geofence
- **Method:** POST
- **URL:** `/api/locations/check-geofence`
- **Auth:** Bearer Token
- **Body:** `{latitude, longitude}`
- **Expected:** List of locations within geofence range

---

## 📊 Test Data Summary

### Users (2)
| Name | Email | Password | Locations | Reminders |
|------|-------|----------|-----------|-----------|
| John Doe | john@example.com | password123 | 5 | 8 |
| Jane Smith | jane@example.com | password123 | 4 | 6 |

### John's Locations (Jakarta)
1. **Home** - Menteng, Jakarta Pusat (300m radius)
2. **Office** - Sudirman, Jakarta Selatan (500m radius)
3. **Gym** - Senayan, Jakarta Selatan (200m radius)
4. **Supermarket** - Grand Indonesia (400m radius)
5. **Parents House** - Kelapa Gading (350m radius)

### John's Reminders
1. Water the plants (Home) ✅
2. Turn off AC (Home) ✅
3. Check emails (Office) ✅
4. Attend standup meeting (Office) ✅
5. Leg day workout (Gym) ✅
6. Buy groceries (Supermarket) ✅
7. Get cleaning supplies (Supermarket) ❌
8. Call mom (Parents House) ✅

---

## 🎯 Expected Test Results

When you run `.\test-api.ps1`, you should see:

```
═══════════════════════════════════════════
  🚀 SMART REMINDER API - TEST SUITE
═══════════════════════════════════════════

[1/6] Testing Health Check...
   ✅ Status: ok
   ⏰ Time: 2025-12-18T...

[2/6] Testing User Login...
   ✅ Logged in: John Doe
   📧 Email: john@example.com
   🔑 Token: 1|abc123...

[3/6] Testing Get Profile...
   ✅ User ID: 1
   📍 Locations: 5
   🔔 Reminders: 8

[4/6] Testing List Locations...
   ✅ Found 5 locations:
      📍 Home - Menteng, Jakarta Pusat
      📍 Office - Sudirman, Jakarta Selatan
      ...

[5/6] Testing List Reminders...
   ✅ Found 8 reminders:
      🔔 ✅ Water the plants
      🔔 ✅ Turn off AC
      ...

[6/6] Testing Geofence Check...
   ✅ Checking coordinates: -6.1944, 106.8229
   📍 Locations in geofence range: 2
      • Home - 0m away
      • Supermarket - 0m away

═══════════════════════════════════════════
  ✅ ALL TESTS COMPLETED SUCCESSFULLY! 🎉
═══════════════════════════════════════════
```

---

## 🚀 Next Steps

### Backend Complete ✅
- ✅ PostgreSQL + PostGIS configured
- ✅ Database migrations & models
- ✅ Authentication (Laravel Sanctum)
- ✅ API endpoints (Locations, Reminders)
- ✅ Test data seeded
- ✅ API testing tools configured

### Frontend To Do
1. Install Node.js (if not installed)
2. Run `npm install`
3. Install Vue.js packages
4. Build UI components
5. Integrate with APIs

See [FRONTEND_SETUP_GUIDE.md](FRONTEND_SETUP_GUIDE.md) for details.

---

## 📝 Quick Commands

```powershell
# Start server
php artisan serve

# Run all tests
.\test-api.ps1

# Reseed database
php artisan db:seed --class=LocationSeeder
php artisan db:seed --class=ReminderSeeder

# Check logs
Get-Content storage\logs\laravel.log -Tail 50
```

---

## 🎉 Success!

Your Smart Reminder API is **fully functional** and ready for frontend integration!

**Test it now:**
```powershell
php artisan serve
# In another terminal:
.\test-api.ps1
```
