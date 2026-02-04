# Reschedule Bug - Debug Guide

## Status
✅ Enhanced debugging code deployed to production
⏳ Waiting for customer to trigger the error so we can see detailed logs

## What Was Changed

### 1. **ApiSessionAuth Middleware** (`app/Http/Middleware/ApiSessionAuth.php`)
Enhanced with comprehensive logging that tracks:
- Session cookie presence and value
- Auth guard setup
- User loading from session
- Session data contents

**Log Pattern:** "======== ApiSessionAuth START ========" ... "======== ApiSessionAuth END ========"

### 2. **BookingController::reschedule()** (`app/Http/Controllers/BookingController.php`)
Enhanced with 6 levels of detailed logging:
1. Request & Session Details (path, method, session ID, driver, etc.)
2. Booking Details (ID, user_id, type, status)
3. Authentication State (user object, current guard, auth check)
4. Cookie & Session Details (session cookie presence, all session data)
5. Direct Guard Check (web guard user attempt)
6. User ID Comparison (int casting, match status)

**Log Pattern:** "=============== RESCHEDULE ATTEMPT ===============" ... "✓ AUTH PASSED" or "RESCHEDULE FAILED"

## Testing Steps

### Step 1: Execute Cache Clear
Navigate to: `https://cizynails-booking.web.id/clear_cache.php`

Expected: Page shows timestamp "Cleared at YYYY-MM-DD HH:mm:ss"

This activates the new logging code.

### Step 2: Test Auth Debug Endpoint (Authenticated)

1. Login as a customer on https://cizynails-booking.web.id
2. Open developer console (F12)
3. Open dashboard
4. Paste and run this in console:
```javascript
fetch('/api/debug/auth', {
    credentials: 'include',
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
})
.then(r => r.json())
.then(d => console.log(JSON.stringify(d, null, 2)))
```

**Expected Output:**
```json
{
  "authenticated": true,
  "user": { "id": 2, "name": "Customer Name", "email": "customer@email.com" },
  "session_id": "xxxxxxxxxxxxx"
}
```

If `authenticated` is `false`, then the session isn't loading on API calls - that's the bug!

### Step 3: Attempt Reschedule

1. On customer dashboard, click reschedule on any booking
2. Select new date/time
3. Click confirm
4. Watch for error message

### Step 4: Check Production Logs

After getting the error:

1. Download logs via SFTP from: `/home/cizynail/Cizy-Nails-Project/storage/logs/laravel.log`
2. Search for "=============== RESCHEDULE ATTEMPT ==============="
3. Look for one of these outcomes:

#### Outcome A: User Not Authenticated
```
[RESCHEDULE FAILED: request->user() is null (Unauthenticated)]
This means the session cookie is not being loaded
→ Issue: Session middleware or cookie transmission problem
```

#### Outcome B: User Authenticated but Wrong User
```
[RESCHEDULE FAILED: User ID mismatch (Unauthorized)]
booking_user_id: 5
request_user_id: 2
→ Issue: Wrong user is in the session, or user object corrupted
```

#### Outcome C: User Authenticated Correctly
```
[✓ AUTH PASSED - User is authenticated and authorized]
→ Issue must be elsewhere (probably later in reschedule function)
```

## Log Locations

All logs go to: `/home/cizynail/Cizy-Nails-Project/storage/logs/laravel.log`

### Key Log Sections

**Middleware logs start with:**
```
======== ApiSessionAuth START ========
======== ApiSessionAuth END ========
```

**Controller logs start with:**
```
=============== RESCHEDULE ATTEMPT ===============
✓ AUTH PASSED - User is authenticated and authorized
```

## Common Issues & Solutions

### Issue: "Unauthenticated" (401)
- Cause: Session not loading from cookie
- Check: Is SESSION_SAME_SITE=none properly set?
- Check: Is SESSION_SECURE_COOKIE=true on HTTPS?
- Check: Session storage permissions on server

### Issue: "Unauthorized" (403) with ID mismatch
- Cause: Wrong user loaded from session, or multiple users logged in
- Check: Browser cookies - should only have one session
- Check: Session file not corrupted
- Check: Cache not outdated

### Issue: Logs not appearing
- Cause: Cache not cleared properly
- Solution: Manually run on server:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Environment Info

**Production Settings (in .env):**
```
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
SESSION_COOKIE=cizy_nails_session
SESSION_DOMAIN=  (empty - uses current domain)
```

**Route Protection:**
Endpoint: `/api/bookings/{booking}/reschedule`
Middleware: `['api.session.auth', 'auth']`
- `api.session.auth`: Forces web guard and session auth
- `auth`: Ensures user is authenticated

## Next Steps After Getting Logs

Once we see the logs and identify the issue:

1. **If Session Not Loading:**
   - Check `storage/logs/` permissions (755)
   - Check Laravel cache path writable
   - May need to force session migration to database

2. **If User ID Mismatch:**
   - Verify customer account ID
   - Check session corruption
   - May need to rebuild session store

3. **If Still Auth Passed but Error:**
   - Issue is in reschedule business logic, not auth
   - Check booking status, reschedule count, etc.

---

## Quick Reference Commands

**Clear cache manually on server:**
```bash
cd /home/cizynail/Cizy-Nails-Project
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Watch logs in real-time:**
```bash
tail -f storage/logs/laravel.log | grep -E "(RESCHEDULE|ApiSessionAuth|AUTH PASSED|FAILED)"
```

**Check session file (if using file driver):**
```bash
ls -la storage/framework/sessions/
```

**Test session manually:**
Visit `/api/debug/auth` while logged in on the same browser.
