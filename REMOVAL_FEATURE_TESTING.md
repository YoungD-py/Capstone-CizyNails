# Removal Feature Testing & Validation Guide

## Feature Overview
The removal feature allows customers to add 30 minutes to their booking if they have old nails/eyelashes that need to be removed. This adjusts:
1. The displayed total duration to the customer
2. The availability time slots (prevents scheduling conflicts within the extended time)
3. The stored booking duration

## Test Cases

### Test Case 1: Display Duration When Removal Selected
**Steps:**
1. Navigate to booking page
2. Select a "Nails Art" or "Eyelash" service (e.g., 45 min service)
3. Verify removal checkbox option appears
4. Select removal checkbox
5. Check duration info text below checkbox

**Expected Result:**
- Duration text displays: "Total duration: 75 minutes"
- Updates immediately when checkbox is toggled

**Validation:**
- ✓ Without removal: Shows service duration (e.g., 45 minutes)
- ✓ With removal: Shows service duration + 30 (e.g., 75 minutes)

---

### Test Case 2: Availability Updates with Removal Option
**Steps:**
1. Select a service (45 min)
2. Select a date
3. Note available time slots WITHOUT removal checked
4. Toggle removal checkbox ON
5. Observe time slots change

**Expected Result:**
- More time slots may become unavailable when removal is added
- This is because each slot needs to accommodate the extended duration

**Validation:**
- ✓ Available slots update when removal checkbox changes
- ✓ Fewer slots available with removal than without

---

### Test Case 3: Booking with Removal Option
**Steps:**
1. Select service (45 min)
2. Select date and time
3. Check removal checkbox
4. Proceed to payment
5. Complete booking
6. Check dashboard

**Expected Result:**
- Booking is created with `total_duration_minutes = 75`
- Dashboard shows "⏱️ 75 minutes"
- Payment is processed normally

**Validation:**
- ✓ Booking created successfully
- ✓ Database stores correct duration
- ✓ Dashboard displays correct duration

---

### Test Case 4: Double-Booking Prevention with Removal Duration
**Steps:**
1. Create first booking: 10:00 AM, 45 min, WITH removal (ends at 11:15)
2. Try to create second booking at 11:00 AM for same service type
3. Note availability message

**Expected Result:**
- 11:00 AM slot shows as UNAVAILABLE
- Error message: "This time slot is fully booked"
- Cannot proceed with booking at 11:00

**Validation:**
- ✓ System correctly identifies conflict within extended booking time
- ✓ 11:00 is blocked (within 10:00-11:15 window)
- ✓ 11:15 onwards is available

---

### Test Case 5: Removal Doesn't Apply to Non-Applicable Services
**Steps:**
1. Select a "Hair" or other non-applicable service
2. Look for removal checkbox

**Expected Result:**
- Removal checkbox option is NOT visible
- Service duration only (no +30 option)

**Validation:**
- ✓ Removal only shows for 'nails_art' and 'eyelash' types
- ✓ Other services unaffected

---

### Test Case 6: Booking Without Removal Option
**Steps:**
1. Select Nails Art service (45 min)
2. DON'T check removal checkbox
3. Select time slot
4. Complete booking
5. Check dashboard

**Expected Result:**
- `total_duration_minutes = 45` (base duration only)
- `needs_removal = false`
- Duration displays: 45 minutes

**Validation:**
- ✓ Booking without removal calculates correctly
- ✓ Doesn't affect availability calculations
- ✓ Stored as `needs_removal = 0`

---

### Test Case 7: Reschedule with Removal Duration Considered
**Steps:**
1. Create booking: 10:00 AM with removal (10:00-11:15)
2. Try to reschedule to 11:00 AM (same service type, same date)
3. Check error message

**Expected Result:**
- Rescheduling to 11:00 is rejected
- Error: "Slot waktu penuh" (Time slot full)
- 11:15 onwards would be available

**Validation:**
- ✓ Reschedule respects removal duration
- ✓ Availability check prevents conflicts

---

### Test Case 8: Available Times API Validation
**Steps:**
1. Open browser developer tools (F12)
2. Go to booking page
3. Select service and date
4. Watch Network tab for API calls
5. Check WITH removal checked, then WITHOUT

**Expected Result:**
- API endpoint: `/api/bookings/available-times?service_id=X&date=Y&needs_removal=Z`
- `needs_removal=1` when checked
- `needs_removal=0` when unchecked
- Different availability results based on needs_removal value

**Validation:**
- ✓ API receives correct needs_removal parameter
- ✓ Availability changes based on parameter
- ✓ Duration calculation is server-side (secure)

---

### Test Case 9: Staff Capacity with Removal Duration
**Steps:**
1. Assuming 2 staff for service type A
2. Create booking 1: 10:00-11:15 (with removal)
3. Create booking 2: 10:30-11:00 (without removal)
4. Try to create booking 3: 10:15 same service type

**Expected Result:**
- Booking 3 at 10:15 is rejected
- Reason: Slot overlaps with both booking 1 and 2
- Exceeds staff capacity (2 staff)

**Validation:**
- ✓ Multiple bookings correctly overlap detection
- ✓ Staff capacity respected with removal durations

---

### Test Case 10: Email Notification Shows Correct Duration
**Steps:**
1. Create booking with removal
2. Wait for confirmation email
3. Check email content

**Expected Result:**
- Email displays: "Total Duration: 75 minutes"
- Booking confirmation shows extended time

**Validation:**
- ✓ Email template uses `total_duration_minutes`
- ✓ Customer sees correct duration in confirmation

---

## Integration Points to Verify

### Database Level
```sql
-- Check booking was created with correct duration
SELECT id, service_id, booking_date, booking_time, total_duration_minutes, needs_removal 
FROM bookings 
WHERE id = [booking_id];
```

### Customer Dashboard
- ✓ Duration shown: "⏱️ X minutes"
- ✓ Correct value stored and retrieved

### Nail Artist Dashboard
- ✓ Duration column shows: "X min"
- ✓ Correctly reflects removal duration

### Admin Dashboard
- ✓ Bookings list shows correct duration
- ✓ Filters/searches work with duration field

---

## Edge Cases to Test

### Edge Case 1: Service at End of Business Hours
**Scenario:** 45-min service + 30-min removal = 75 min, but shop closes at 20:00

**Expected:**
- Cannot book at 19:00 (would end at 20:15, past closing)
- Cannot book at 18:45 (would end at 20:00, valid but no buffer)
- Can book at 18:45 if it ends exactly at 20:00

### Edge Case 2: Rapid-Fire Availability Check
**Scenario:** User checks availability, checks removal checkbox multiple times quickly

**Expected:**
- No errors or race conditions
- Last state is correct
- API calls properly queued

### Edge Case 3: Multiple Services in Booking (Future)
**Scenario:** If system supports multi-service bookings

**Expected:**
- Each service's removal is tracked separately
- Total duration is sum of all service durations with their respective removals

### Edge Case 4: Booking Cancellation with Removal
**Scenario:** Cancel a booking that had removal option

**Expected:**
- Slot becomes available for other bookings
- Other customers can now book in that time range
- Availability updates properly

---

## Success Criteria

✅ All test cases pass
✅ No SQL errors in logs
✅ No JavaScript console errors
✅ Duration calculations are correct (base + 30 if removal)
✅ Availability respects extended duration
✅ Staff capacity checks work with durations
✅ Backward compatible with existing bookings
✅ Email notifications correct
✅ Dashboard displays correct duration
✅ No changes to non-removal-related functionality

---

## Rollback Plan (if needed)

If issues occur:
1. Remove `needs_removal` parameter from API calls
2. Revert JavaScript changes in booking.blade.php
3. Clear browser cache
4. Revert BookingController.php to use only service duration
5. All data is preserved (fields already exist in database)

---

## Performance Considerations

- ✅ Availability calculation uses indexed columns (booking_date, booking_time, status)
- ✅ API calls are same frequency as before (just additional parameter)
- ✅ No additional database queries added
- ✅ Logic is client-validated before server submission
