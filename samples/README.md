# Teacher Load Assignment System

## Setup Guide

### 1. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Import `database/schema.sql` to create all tables
3. Import `database/seed.sql` to add sample data
4. Import `database/setup_admin.sql` to create the default admin user

### 2. Default Login
- **Username:** admin
- **Password:** admin123

### 3. Directory Permissions
Ensure the following directories are writable:
- `uploads/teachers/`
- `uploads/subjects/`
- `uploads/schedules/`
- `exports/`

### 4. Optional: Composer Dependencies
If you need PhpSpreadsheet for Excel imports:
```bash
composer install
```

### 5. Features Overview
- **Dashboard** — KPIs, stats, quick actions
- **Teachers** — CRUD, expertise, availability calendar
- **Subjects** — Catalog, prerequisites, bulk import
- **Schedules** — Time slots, rooms, sections
- **Assignments** — Auto-match engine, manual override, conflict detection
- **Reports** — Load reports, CSV/PDF export, audit trail
