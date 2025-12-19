# 🧪 Quick API Testing Script

## Using PowerShell - Copy and run these commands

### 1. Health Check
```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/health' | ConvertTo-Json
```

### 2. Login and Save Token
```powershell
$body = @{
    email = 'john@example.com'
    password = 'password123'
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/login' `
    -Method Post `
    -Body $body `
    -ContentType 'application/json'

$token = $response.data.token
Write-Host "Token saved! Use it for protected endpoints." -ForegroundColor Green
$response | ConvertTo-Json -Depth 10
```

### 3. Get Profile
```powershell
$headers = @{
    Authorization = "Bearer $token"
}

Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/profile' `
    -Headers $headers | ConvertTo-Json -Depth 10
```

### 4. List Locations
```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/locations' `
    -Headers $headers | ConvertTo-Json -Depth 10
```

### 5. Create Location
```powershell
$locationBody = @{
    name = 'PowerShell Test Location'
    address = 'Jakarta'
    latitude = -6.2088
    longitude = 106.8456
    geofence_radius = 500
} | ConvertTo-Json

Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/locations' `
    -Method Post `
    -Headers $headers `
    -Body $locationBody `
    -ContentType 'application/json' | ConvertTo-Json -Depth 10
```

### 6. Check Geofence
```powershell
$geofenceBody = @{
    latitude = -6.1944
    longitude = 106.8229
} | ConvertTo-Json

Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/locations/check-geofence' `
    -Method Post `
    -Headers $headers `
    -Body $geofenceBody `
    -ContentType 'application/json' | ConvertTo-Json -Depth 10
```

### 7. List Reminders
```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/reminders' `
    -Headers $headers | ConvertTo-Json -Depth 10
```

### 8. Create Reminder
```powershell
$reminderBody = @{
    location_id = 1
    title = 'PowerShell Test Reminder'
    description = 'Created from PowerShell'
    is_active = $true
} | ConvertTo-Json

Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/reminders' `
    -Method Post `
    -Headers $headers `
    -Body $reminderBody `
    -ContentType 'application/json' | ConvertTo-Json -Depth 10
```

### 9. Logout
```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/logout' `
    -Method Post `
    -Headers $headers | ConvertTo-Json
```

---

## 🚀 Quick Test All APIs (Run in one go)

```powershell
# Login
$body = @{email='john@example.com'; password='password123'} | ConvertTo-Json
$response = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/login' -Method Post -Body $body -ContentType 'application/json'
$token = $response.data.token
$headers = @{Authorization = "Bearer $token"}

Write-Host "`n=== Logged in as: $($response.data.user.name) ===" -ForegroundColor Cyan

# Get Profile
Write-Host "`n=== Profile ===" -ForegroundColor Yellow
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/profile' -Headers $headers | Select-Object -ExpandProperty data | Format-List

# List Locations
Write-Host "`n=== Locations ===" -ForegroundColor Yellow
$locations = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/locations' -Headers $headers
Write-Host "Total locations: $($locations.data.Count)"
$locations.data | Format-Table name, address, geofence_radius

# List Reminders
Write-Host "`n=== Reminders ===" -ForegroundColor Yellow
$reminders = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/reminders' -Headers $headers
Write-Host "Total reminders: $($reminders.data.Count)"
$reminders.data | Format-Table title, @{Label="Location"; Expression={$_.location.name}}, is_active

# Check Geofence at Home location
Write-Host "`n=== Geofence Check ===" -ForegroundColor Yellow
$geofenceBody = @{latitude=-6.1944; longitude=106.8229} | ConvertTo-Json
$geofence = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/locations/check-geofence' -Method Post -Headers $headers -Body $geofenceBody -ContentType 'application/json'
Write-Host "Locations in range: $($geofence.data.locations_in_range.Count)"
$geofence.data.locations_in_range | Format-Table name, distance_meters

Write-Host "`n=== All tests completed! ===" -ForegroundColor Green
```
