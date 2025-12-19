# Nail Artist Access

## Login Credentials

- **Email**: cizynails@cizynails.com
- **Password**: cizynails123

## Nail Artist Dashboard Features

### Access
Navigate to: `/nail-artist/dashboard` after login

### Capabilities

1. **View Bookings**
   - See all upcoming bookings
   - View today's bookings count
   - Monitor pending bookings
   - Track completed bookings

2. **Update Booking Status**
   - **Pending**: Default status when customer creates appointment
   - **Complete**: Mark service as completed
   - **Cancelled**: Cancel appointment (can be done by customer or nail artist)

### Status Flow

```
Customer Creates Appointment
         ↓
    [PENDING]
         ↓
Nail Artist Provides Service
         ↓
   [COMPLETED]
```

### Alternative Actions
- Customer or Nail Artist can cancel appointment at any time
- Completed bookings can be undone back to confirmed status

## Dashboard Pages

1. **Dashboard** (`/nail-artist/dashboard`)
   - Statistics overview
   - Today's bookings
   - Pending count
   - Completed today count
   - Quick access to upcoming bookings

2. **All Bookings** (`/nail-artist/bookings`)
   - Complete list of all bookings
   - Filter by date and status
   - Update status directly from table
   - View customer details and contact info

## Booking Status Options

- **Pending**: Awaiting service
- **Confirmed**: Payment verified (automatic via Midtrans)
- **Completed**: Service finished by nail artist
- **Cancelled**: Appointment cancelled

## Notes

- Nail artists CANNOT delete bookings (only admin can)
- Nail artists CAN update booking status
- Status changes are instant and logged
- Payment status is separate from booking status
