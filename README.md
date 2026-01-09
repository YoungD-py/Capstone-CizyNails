<h1 align="center">Cizy Nails — Booking & Management System</h1>

Modern booking platform for nail/eyelash services with role-based dashboards, payment integration, and real-time availability. Built with Laravel, Blade, and Vite.

---

## Overview

Cizy Nails streamlines service discovery and booking for customers, while providing powerful tools for admins and nail artists to manage schedules, services, and payments.

### Core Capabilities

- Guest availability preview on the landing page
- Booking with optional removal (+30 minutes duration)
- Persistent booking intent for guests (session-based)
- Midtrans payment integration (Snap)
- Role-based dashboards: Customer, Admin, Nail Artist
- Admin service management (enable/disable removal per service)
- Robust access control with custom unauthorized pages
- Edit Profile for all roles (Full Name, Email, WhatsApp, Password)

### Custom Unauthorized Pages

- Not logged in: redirected to "WHAT YOU TRNA DO BRUH?" (unauthorized)
- Wrong role: redirected to "SORRY THIS IS NOT YOUR ACCESS" (unauthorized-access)

---

## Tech Stack

- Backend: Laravel (PHP)
- Frontend: Blade + Vite + Vanilla JS
- Database: MySQL
- Payments: Midtrans Snap

---

## Getting Started

### Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+ and npm
- MySQL (or compatible)
- Midtrans account (client key & server key)

### Setup

1. Clone and install dependencies:

	```powershell
	git clone <repo-url>
	cd Cizy-Nails-Project
	composer install
	npm install
	```

2. Configure environment:

	- Copy `.env.example` to `.env`
	- Set database credentials
	- Configure Midtrans keys (see [MIDTRANS_SETUP.md](MIDTRANS_SETUP.md))
	- Configure email/SMTP if needed (see [GMAIL_SETUP.md](GMAIL_SETUP.md))

3. Generate keys and run migrations:

	```powershell
	php artisan key:generate
	php artisan migrate
	```

4. Build assets and start servers:

	```powershell
	npm run dev
	php artisan serve
	```

If you change routes or config, clear caches:

```powershell
php artisan optimize:clear
php artisan route:list
```

---

## Features & Flows

### Booking Flow

- Customers select service, date, time, and optional removal (+30 min)
- Guest users can preview availability on the landing page and save intent
- On login/register, booking intent is preserved and applied to the booking form

### Availability Logic

- Duration-aware checks prevent overlapping bookings
- Removal option adds 30 minutes to service duration when enabled by admin

### Admin Controls

- Manage services and toggle `enable_removal` per service
- View bookings, bulk delete, cancel, and manage customers/services/schedules

### Nail Artist Dashboard

- View assigned bookings and update booking statuses
- Reference: [NAIL_ARTIST_README.md](NAIL_ARTIST_README.md)

### Edit Profile

- Available to all authenticated roles
- Fields: Full Name, Email, WhatsApp, Password
- Post-update redirects to role-appropriate dashboard

---

## Access Control

Route protection is enforced by role-based middleware with clear outcomes:

- Customer pages: require `role:customer`
- Admin pages: require `role:admin`
- Nail Artist pages: require `role:nail_artist`
- Not logged in: redirected to unauthorized page
- Logged in but wrong role: redirected to unauthorized-access page

Key middleware:

- `app/Http/Middleware/CheckRole.php` — central role check + redirects
- `app/Http/Middleware/Authenticate.php` — redirects unauthenticated users to unauthorized

---

## Key Routes

### Web

- Landing: `/`
- Auth: `/login`, `/register`, `/logout`
- Errors: `/unauthorized`, `/access-denied`
- Customer: `/dashboard`, `/booking`
- Admin: `/admin/*` (dashboard, services, types, bookings, customers, schedules)
- Nail Artist: `/nail-artist/*` (dashboard, bookings)

### API (selected)

- `POST /api/save-booking-intent` — save guest booking intent
- `GET /api/bookings/available-times` — availability with `needs_removal`
- `POST /api/bookings` — create booking
- `POST /api/midtrans/webhook` — payment notifications

---

## Configuration References

- Payments: [MIDTRANS_SETUP.md](MIDTRANS_SETUP.md)
- Email/SMTP: [GMAIL_SETUP.md](GMAIL_SETUP.md)
- Nail Artist Ops: [NAIL_ARTIST_README.md](NAIL_ARTIST_README.md)

---

## Development

### Useful Commands

```powershell
php artisan optimize:clear
php artisan route:list
php artisan migrate
php artisan serve
npm run dev
```

### Testing

```powershell
php artisan test
```

---

## Project Structure (high level)

- `app/` — Controllers, Models, Services, Middleware
- `routes/` — Web and API route definitions
- `resources/views/` — Blade templates (landing, dashboards, errors, booking, profile)
- `database/` — Migrations and seeders
- `public/` — Public assets and entry point
- `config/` — Laravel configuration files

---

## Troubleshooting

- Seeing login instead of unauthorized? Ensure `Authenticate` middleware redirects to `unauthorized`.
- Role cannot access a page? Confirm route group uses `role:<role>` and caches are cleared.
- Availability off by time zone? Use the landing page’s timezone-safe date handling.

---

## Notes

This repository is tailored to the Cizy Nails capstone project with production-ready role-based access control, booking flows, and payment integration. For environment-specific credentials and operational guides, refer to the setup documents linked above.
