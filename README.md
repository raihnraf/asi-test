# Laravel Screening Test App

A Laravel 11 web application demonstrating authentication, product management, and sales order transactions with proper RDBMS relationships.

## Prerequisites

- Docker Desktop (with Docker Compose)
- Git

## Installation

### Step 1: Start Docker containers

```bash
./vendor/bin/sail up -d
```

This starts the MySQL database container.

### Step 2: Run database migrations

```bash
./vendor/bin/sail artisan migrate
```

This creates the required database tables (users, sessions, etc.).

### Step 3: Seed demo user

```bash
./vendor/bin/sail artisan db:seed --class=DemoUserSeeder
```

This creates a demo user for testing the login flow.

### Step 4: Start the development server

```bash
./vendor/bin/sail artisan serve
```

The application will be available at: http://localhost:8000

## Login Credentials

Use these credentials to test the authentication flow:

- **Email:** `test@example.com`
- **Password:** `password`

## Testing the App

1. Open http://localhost:8000/login in your browser
2. Log in with the demo credentials above
3. You will be redirected to the dashboard at http://localhost:8000/dashboard
4. Click "Log Out" in the navigation to end your session

## Running Tests

```bash
./vendor/bin/sail artisan test
```

## Tech Stack

- **Framework:** Laravel 11 (PHP 8.4)
- **Database:** MySQL 8.0
- **Authentication:** Laravel Breeze (Blade stack)
- **Frontend:** Blade templates + Tailwind CSS + Vite
- **Testing:** PHPUnit
