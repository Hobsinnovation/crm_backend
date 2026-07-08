# HOBS CRM — Backend API

A modern, scalable Customer Relationship Management (CRM) system backend built with **Laravel 12**. This RESTful API powers the HOBS CRM platform, providing robust client management, lead tracking, domain monitoring, invoicing, and role-based access control.

<p>
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8.0">
  <img src="https://img.shields.io/badge/Status-In%20Development-yellow?style=flat-square" alt="Status">
</p>

---

## 📋 Overview

HOBS CRM Backend is the server-side application of a full-stack CRM solution. It exposes a comprehensive REST API consumed by the [Next.js frontend](https://github.com/Hobsinnovation/crm_frontend) and handles all business logic, data persistence, authentication, and authorization.

## ✨ Features

- **User Management** — Complete user lifecycle with account activation controls
- **Role-Based Access Control (RBAC)** — Granular roles and permissions system with module-level actions
- **Client Management** — Store and manage client profiles, companies, and contact details
- **Lead Tracking** — Capture leads with source attribution, status pipeline, and team assignment
- **Domain Monitoring** — Track domain registrations, expiry dates, and auto-renewal status
- **Invoicing** — Generate and manage invoices with status tracking and due dates
- **Notifications** — In-app notification system with read/unread states
- **Activity Logging** — Full audit trail of user actions and model changes
- **API Authentication** — Secure token-based auth via Laravel Sanctum *(Phase 2)*

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Language | PHP 8.2+ |
| Database | MySQL 8.0 |
| Authentication | Laravel Sanctum |
| Architecture | Service–Repository Pattern |
| API Style | RESTful JSON |

## 🗄️ Database Schema

The system uses 10 core tables:

| Table | Purpose |
|-------|---------|
| `users` | User accounts with roles and activation status |
| `roles` | System roles with priority levels |
| `permissions` | Module-based permission definitions |
| `role_permission` | Role–permission pivot mapping |
| `clients` | Client profiles and company information |
| `leads` | Sales leads with pipeline status |
| `domains` | Domain registrations and expiry tracking |
| `invoices` | Billing and invoice management |
| `notifications` | User notification queue |
| `activity_logs` | System-wide audit trail |

All tables include timestamps, and soft deletes are enabled where appropriate.

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.2
- Composer >= 2.x
- MySQL >= 8.0
- Node.js (for asset compilation, if needed)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Hobsinnovation/crm_backend.git
   cd crm_backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set up the database**

   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hafiz_crm
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

   The API will be available at `http://localhost:8000`

### Frontend Connection

Set the frontend URL in `.env` for CORS configuration:

```env
FRONTEND_URL=http://localhost:3000
```

## 📁 Project Structure

```
crm_backend/
├── app/
│   ├── Models/            # Eloquent models
│   ├── Http/Controllers/  # API controllers
│   ├── Services/          # Business logic layer
│   ├── Repositories/      # Data access layer
│   └── Policies/          # Authorization policies
├── database/
│   ├── migrations/        # Database schema definitions
│   ├── seeders/           # Sample/default data
│   └── factories/         # Model factories for testing
├── routes/
│   ├── api.php            # API route definitions
│   └── web.php            # Web routes
└── tests/                 # Feature & unit tests
```

## 🧭 Roadmap

- [x] **Phase 1** — Architecture, database design, project scaffolding
- [ ] **Phase 2** — Authentication (Sanctum), roles & permissions, admin dashboard APIs
- [ ] **Phase 3** — Client, lead, and domain management modules
- [ ] **Phase 4** — Invoicing, notifications, and activity logs
- [ ] **Phase 5** — Testing, optimization, and deployment

## 🔧 Useful Commands

```bash
php artisan migrate:status    # Check migration status
php artisan migrate:fresh     # Rebuild database from scratch
php artisan route:list        # List all registered routes
php artisan test              # Run the test suite
```

## 🤝 Related Repositories

- **Frontend:** [crm_frontend](https://github.com/Hobsinnovation/crm_frontend) — Next.js 15 client application

## 📄 License

This project is proprietary software developed by **Hobs Innovation**. All rights reserved.

---

<p align="center">Built with ❤️ by <a href="https://github.com/Hobsinnovation">Hobs Innovation</a></p>