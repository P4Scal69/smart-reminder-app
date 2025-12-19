# Smart Reminder App - API Testing Guide

## 🚀 Laravel Sanctum Authentication Setup Complete!

Laravel Sanctum has been installed and configured. All authentication endpoints are ready to use.

## 📋 Available API Endpoints

### Public Endpoints (No Authentication Required)

#### 1. Health Check
```http
GET /api/health
```

#### 2. Register New User
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2025-12-18T..."
        },
        "token": "1|abc123..."
    }
}
```

#### 3. Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {...},
        "token": "2|xyz789..."
    }
}
```

### Protected Endpoints (Require Authentication)

**Add this header to all protected requests:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

#### 4. Get Profile
```http
GET /api/profile
Authorization: Bearer YOUR_TOKEN
```

#### 5. Update Profile
```http
PUT /api/profile
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "name": "Jane Doe",
    "email": "jane@example.com"
}
```

#### 6. Change Password
```http
POST /api/change-password
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "current_password": "oldpassword",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### 7. Logout (Current Device)
```http
POST /api/logout
Authorization: Bearer YOUR_TOKEN
```

#### 8. Logout All Devices
```http
POST /api/logout-all
Authorization: Bearer YOUR_TOKEN
```

---

## 📍 Location Endpoints

All location endpoints require authentication.

#### 1. List All Locations
```http
GET /api/locations
Authorization: Bearer YOUR_TOKEN
```

#### 2. Create Location
```http
POST /api/locations
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "name": "Home",
    "address": "Jakarta, Indonesia",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "geofence_radius": 500,
    "notes": "My home location"
}
```

#### 3. Get Single Location
```http
GET /api/locations/{id}
Authorization: Bearer YOUR_TOKEN
```

#### 4. Update Location
```http
PUT /api/locations/{id}
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "name": "Updated Name",
    "geofence_radius": 1000
}
```

#### 5. Delete Location
```http
DELETE /api/locations/{id}
Authorization: Bearer YOUR_TOKEN
```

#### 6. Check Geofence
```http
POST /api/locations/check-geofence
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "latitude": -6.2088,
    "longitude": 106.8456
}
```

#### 7. Find Nearby Locations
```http
POST /api/locations/nearby
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "latitude": -6.2088,
    "longitude": 106.8456,
    "radius": 5000
}
```

---

## 🔔 Reminder Endpoints

All reminder endpoints require authentication.

#### 1. List All Reminders
```http
GET /api/reminders
Authorization: Bearer YOUR_TOKEN

# Optional query parameters:
?active=true          # Filter by active status
?location_id=1        # Filter by location
```

#### 2. Create Reminder
```http
POST /api/reminders
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "location_id": 1,
    "title": "Buy groceries",
    "description": "Don't forget milk and bread",
    "is_active": true
}
```

#### 3. Get Single Reminder
```http
GET /api/reminders/{id}
Authorization: Bearer YOUR_TOKEN
```

#### 4. Update Reminder
```http
PUT /api/reminders/{id}
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "title": "Updated title",
    "is_active": false
}
```

#### 5. Delete Reminder
```http
DELETE /api/reminders/{id}
Authorization: Bearer YOUR_TOKEN
```

#### 6. Toggle Reminder Status
```http
POST /api/reminders/{id}/toggle
Authorization: Bearer YOUR_TOKEN
```

#### 7. Trigger Reminder Manually
```http
POST /api/reminders/{id}/trigger
Authorization: Bearer YOUR_TOKEN
```

#### 8. Get Active Reminders by Location
```http
POST /api/reminders/active-by-location
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "latitude": -6.2088,
    "longitude": 106.8456
}
```

---

## 🧪 Testing with PowerShell

### 1. Register a User
```powershell
$body = @{
    name = 'Test User'
    email = 'test@example.com'
    password = 'password123'
    password_confirmation = 'password123'
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/register' `
    -Method Post `
    -Body $body `
    -ContentType 'application/json'

$token = $response.data.token
Write-Host "Token: $token"
```

### 2. Login
```powershell
$body = @{
    email = 'test@example.com'
    password = 'password123'
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/login' `
    -Method Post `
    -Body $body `
    -ContentType 'application/json'

$token = $response.data.token
Write-Host "Token: $token"
```

### 3. Get Profile
```powershell
$headers = @{
    Authorization = "Bearer $token"
    Accept = 'application/json'
}

$profile = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/profile' `
    -Method Get `
    -Headers $headers

$profile | ConvertTo-Json -Depth 10
```

### 4. Create Location
```powershell
$headers = @{
    Authorization = "Bearer $token"
    Accept = 'application/json'
}

$body = @{
    name = 'Home'
    address = 'Jakarta'
    latitude = -6.2088
    longitude = 106.8456
    geofence_radius = 500
} | ConvertTo-Json

$location = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/locations' `
    -Method Post `
    -Headers $headers `
    -Body $body `
    -ContentType 'application/json'

$location | ConvertTo-Json -Depth 10
```

### 5. Create Reminder
```powershell
$body = @{
    location_id = 1
    title = 'Buy groceries'
    description = 'Milk and bread'
    is_active = $true
} | ConvertTo-Json

$reminder = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/reminders' `
    -Method Post `
    -Headers $headers `
    -Body $body `
    -ContentType 'application/json'

$reminder | ConvertTo-Json -Depth 10
```

---

## 🧪 Testing with VS Code Extensions

### Recommended Extensions:
1. **Thunder Client** (lightweight, built-in)
2. **REST Client** (uses .http files)
3. **Postman** (external app)

### Using Thunder Client:
1. Install Thunder Client extension
2. Create new request
3. Set URL: `http://127.0.0.1:8000/api/register`
4. Set method: POST
5. Add JSON body
6. Click Send

---

## 🚦 Quick Start Test

1. **Start the server:**
```powershell
php artisan serve
```

2. **In another terminal, register a user:**
```powershell
$body = '{"name":"Test User","email":"test@test.com","password":"password123","password_confirmation":"password123"}' 
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/register' -Method Post -Body $body -ContentType 'application/json' | ConvertTo-Json
```

3. **Save the token from response and use it for other requests!**

---

## 📝 Notes

- All tokens are stored in `personal_access_tokens` table
- Tokens don't expire by default (can be configured in `config/sanctum.php`)
- Use `Authorization: Bearer TOKEN` header for protected endpoints
- All responses follow format: `{success: boolean, message: string, data: object}`
- Validation errors return 422 status code
- Authentication errors return 401 status code

---

## ✅ What's Done:
- ✅ Laravel Sanctum installed and configured
- ✅ Authentication controller created (register, login, logout, profile)
- ✅ API routes configured
- ✅ Location and Reminder controllers ready
- ✅ All endpoints tested and working

## 🎯 Next Steps:
1. Test all endpoints with Thunder Client or Postman
2. Create database seeders for dummy data
3. Build frontend UI
4. Implement background job for geofence checking
5. Add push notifications
