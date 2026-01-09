# Removal Feature (+30 Minutes) - Implementation Summary

## Overview
Added logic to handle the removal option checkbox "There are nails/eyelash lama yang perlu di-remove +30 min" in the booking system. When selected, this option adds 30 minutes to the service duration, affecting both the display and availability calculations.

## Changes Made

### 1. Frontend - Booking Form (`resources/views/customer/booking.blade.php`)

#### Updated JavaScript Functions:

**a) `selectService()` function - Enhanced:**
- Now calls `updateDurationDisplay()` when a service is selected
- Shows/hides removal option container based on service type (nails_art or eyelash)
- Updates available times when service is selected

**b) `updateDurationDisplay()` function - NEW:**
- Calculates total duration: base duration + 30 minutes if removal is selected
- Displays "Total duration: X minutes" to the customer
- Called whenever removal checkbox state changes

**c) Removal checkbox event listeners - UPDATED:**
- Now calls `updateDurationDisplay()` when removal checkbox state changes
- Reloads available times when removal option changes (critical for availability calculation)

**d) `loadAvailableTimes()` function - UPDATED:**
- Now passes `needs_removal` parameter to the API
- The value is 1 if removal is selected, 0 otherwise
- This allows server-side calculation of availability based on total duration

### 2. Backend - BookingController (`app/Http/Controllers/BookingController.php`)

#### Updated API Endpoint: `getAvailableTimes()`
- **NEW Parameter**: Accepts optional `needs_removal` query parameter (0 or 1)
- **Duration Calculation**: Calculates total duration as `service_duration + 30` if `needs_removal = 1`
- **Availability Logic**: 
  - For services with `type_id` (new system): Uses `total_duration_minutes` from existing bookings
  - For legacy services: Falls back to service duration
  - Availability check now considers the calculated total duration when checking for overlaps

#### Updated Method: `store()` - Capacity Check
- **Pre-existing**: Already calculates `total_duration_minutes` and stores in database
- **UPDATED**: Enhanced capacity check to use `total_duration_minutes` from existing bookings
- **Logic**: 
  - For TYPE-based services: Counts overlapping bookings using their `total_duration_minutes`
  - For legacy services: Counts overlapping bookings using their `total_duration_minutes` if available
  - Calculates new booking's end time considering removal duration for overlap detection

#### Updated Method: `reschedule()` - Capacity Check
- **Uses**: The existing booking's `total_duration_minutes` during rescheduling
- **Logic**: 
  - Checks against other bookings on the new date using their `total_duration_minutes`
  - Ensures the rescheduled booking doesn't violate duration-aware availability

### 3. Data Model - Booking Model (`app/Models/Booking.php`)

**Already Contains:**
- `total_duration_minutes` field (fillable)
- `needs_removal` field (fillable, cast to boolean)

## How It Works - Example Scenario

### Scenario:
Customer books:
- **Service**: Nail Art (45 minutes)
- **Date**: 2024-01-15
- **Time**: 10:00 AM
- **Removal**: YES (adds 30 minutes)

### Result:
1. **Display to Customer**: "Total duration: 75 minutes"
2. **Database Storage**: 
   - `total_duration_minutes = 75`
   - `needs_removal = true`
3. **Availability Impact**:
   - Booking occupies: 10:00 - 11:15 (75 minutes)
   - Time slots 10:00, 10:30, 11:00 are marked as UNAVAILABLE
   - 11:15 onwards are available for other bookings (if capacity allows)

## Key Features

### ✅ Real-time Duration Display
- Shows updated total duration when removal checkbox is toggled
- Updates immediately in the booking form

### ✅ Smart Availability Calculation
- Considers total duration (service + removal) when checking time slot availability
- Prevents double-booking by accounting for extended booking time
- Reloads available times when removal option changes

### ✅ Backward Compatibility
- Works with both new TYPE-based services and legacy services
- Falls back to service duration for bookings without `total_duration_minutes`
- No database schema changes needed (field already exists)

### ✅ Proper Overlap Detection
- Checks if new booking start time overlaps with any existing booking's duration (including removal)
- Used consistently in:
  - Initial booking creation
  - Availability time slot checking
  - Booking rescheduling

## Testing Checklist

- [ ] Select service with removal option, verify duration displays correctly
- [ ] Toggle removal checkbox, verify available times update
- [ ] Book with removal, verify booking duration is base + 30 minutes
- [ ] Verify another customer cannot book overlapping time (considering extended duration)
- [ ] Reschedule booking with removal, verify availability considers total duration
- [ ] Check dashboard displays correct duration for bookings with removal
- [ ] Verify nail artist dashboard shows correct duration
- [ ] Verify email notifications show correct duration

## Files Modified

1. `/resources/views/customer/booking.blade.php` - Frontend form and JavaScript
2. `/app/Http/Controllers/BookingController.php` - Backend API and logic
3. `/app/Models/Booking.php` - No changes (already had required fields)

## Notes

- The removal feature only appears for 'nails_art' and 'eyelash' service types
- Total duration is stored in the `total_duration_minutes` column
- The `needs_removal` boolean flag indicates whether removal was applied
- This feature is frontend and backend agnostic - no breaking changes to existing functionality
