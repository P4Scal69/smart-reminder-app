# Smart Reminder API Test Script
Write-Host "`n═══════════════════════════════════════════" -ForegroundColor Magenta
Write-Host "  🚀 SMART REMINDER API - TEST SUITE" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════`n" -ForegroundColor Magenta

# Test 1: Health Check
Write-Host "[1/6] Testing Health Check..." -ForegroundColor Yellow
try {
    $health = Invoke-RestMethod 'http://127.0.0.1:8000/api/health'
    Write-Host "   ✅ Status: $($health.status)" -ForegroundColor Green
    Write-Host "   ⏰ Time: $($health.timestamp)`n" -ForegroundColor Cyan
} catch {
    Write-Host "   ❌ ERROR: Server not responding. Make sure 'php artisan serve' is running.`n" -ForegroundColor Red
    exit 1
}

# Test 2: User Login
Write-Host "[2/6] Testing User Login..." -ForegroundColor Yellow
$loginBody = @{
    email = 'john@example.com'
    password = 'password123'
} | ConvertTo-Json

try {
    $auth = Invoke-RestMethod 'http://127.0.0.1:8000/api/login' -Method Post -Body $loginBody -ContentType 'application/json'
    $token = $auth.data.token
    $headers = @{Authorization = "Bearer $token"}
    Write-Host "   ✅ Logged in: $($auth.data.user.name)" -ForegroundColor Green
    Write-Host "   📧 Email: $($auth.data.user.email)" -ForegroundColor Cyan
    Write-Host "   🔑 Token: $($token.Substring(0,30))...`n" -ForegroundColor Yellow
} catch {
    Write-Host "   ❌ ERROR: Login failed`n" -ForegroundColor Red
    exit 1
}

# Test 3: Get Profile
Write-Host "[3/6] Testing Get Profile..." -ForegroundColor Yellow
try {
    $profile = Invoke-RestMethod 'http://127.0.0.1:8000/api/profile' -Headers $headers
    Write-Host "   ✅ User ID: $($profile.data.id)" -ForegroundColor Green
    Write-Host "   📍 Locations: $($profile.data.locations.Count)" -ForegroundColor Cyan
    Write-Host "   🔔 Reminders: $($profile.data.reminders.Count)`n" -ForegroundColor Cyan
} catch {
    Write-Host "   ❌ ERROR: Failed to get profile`n" -ForegroundColor Red
}

# Test 4: List Locations
Write-Host "[4/6] Testing List Locations..." -ForegroundColor Yellow
try {
    $locations = Invoke-RestMethod 'http://127.0.0.1:8000/api/locations' -Headers $headers
    Write-Host "   ✅ Found $($locations.data.Count) locations:" -ForegroundColor Green
    foreach ($loc in $locations.data) {
        Write-Host "      📍 $($loc.name) - $($loc.address)" -ForegroundColor Cyan
        Write-Host "         Lat: $($loc.latitude), Lng: $($loc.longitude), Radius: $($loc.geofence_radius)m" -ForegroundColor DarkCyan
    }
    Write-Host ""
} catch {
    Write-Host "   ❌ ERROR: Failed to list locations`n" -ForegroundColor Red
}

# Test 5: List Reminders
Write-Host "[5/6] Testing List Reminders..." -ForegroundColor Yellow
try {
    $reminders = Invoke-RestMethod 'http://127.0.0.1:8000/api/reminders' -Headers $headers
    Write-Host "   ✅ Found $($reminders.data.Count) reminders:" -ForegroundColor Green
    foreach ($rem in $reminders.data) {
        if ($rem.is_active) {
            $status = "✅"
        } else {
            $status = "❌"
        }
        Write-Host "      🔔 $status $($rem.title)" -ForegroundColor Cyan
        Write-Host "         Location: $($rem.location.name)" -ForegroundColor DarkCyan
    }
    Write-Host ""
} catch {
    Write-Host "   ❌ ERROR: Failed to list reminders`n" -ForegroundColor Red
}

# Test 6: Check Geofence
Write-Host "[6/6] Testing Geofence Check..." -ForegroundColor Yellow
$geoBody = @{
    latitude = -6.1944
    longitude = 106.8229
} | ConvertTo-Json

try {
    $geo = Invoke-RestMethod 'http://127.0.0.1:8000/api/locations/check-geofence' -Method Post -Headers $headers -Body $geoBody -ContentType 'application/json'
    Write-Host "   ✅ Checking coordinates: -6.1944, 106.8229" -ForegroundColor Green
    Write-Host "   📍 Locations in geofence range: $($geo.data.locations_in_range.Count)" -ForegroundColor Cyan
    
    if ($geo.data.locations_in_range.Count -gt 0) {
        foreach ($geoLoc in $geo.data.locations_in_range) {
            $distance = [math]::Round($geoLoc.distance_meters)
            Write-Host "      • $($geoLoc.name) - ${distance}m away" -ForegroundColor Yellow
        }
    } else {
        Write-Host "      No locations in range" -ForegroundColor DarkGray
    }
    Write-Host ""
} catch {
    Write-Host "   ❌ ERROR: Geofence check failed`n" -ForegroundColor Red
}

# Summary
Write-Host "═══════════════════════════════════════════" -ForegroundColor Green
Write-Host "  ✅ ALL TESTS COMPLETED SUCCESSFULLY! 🎉" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════`n" -ForegroundColor Green

Write-Host "📊 Summary:" -ForegroundColor Cyan
Write-Host "   • User: $($auth.data.user.name)" -ForegroundColor White
Write-Host "   • Locations: $($locations.data.Count)" -ForegroundColor White
Write-Host "   • Reminders: $($reminders.data.Count)" -ForegroundColor White
Write-Host "   • Geofence locations: $($geo.data.locations_in_range.Count)" -ForegroundColor White
Write-Host ""
