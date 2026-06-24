# Laravel Screening Test App

Laravel 11 web application for a screening test with:

- login authentication
- product master data CRUD
- sales order transaction CRUD
- relational database link from transactions to products
- role-based access control for admin and staff users
- dashboard analytics and CSV export for sales orders

## Development Notes

- Architecture and refactor audit details: [`AUDIT.md`](./AUDIT.md)
- The current implementation applies thin controllers, Form Requests for validation, a dedicated service layer for sales order business logic, and local Eloquent scopes for reusable query logic.

## Prerequisites

- Docker Desktop / Docker Engine with Docker Compose
- PHP 8.2+
- Composer
- Node.js and npm
- Git

## Recommended Workflow

- Use Laravel Sail as the standard runtime for this project.
- In practice, that means Docker provides the containers, while `./vendor/bin/sail` is the consistent command entry point for Laravel, database, and test commands.
- Use host `npm` for frontend asset builds and Vite development.

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
npm run build
```

Open `http://localhost:8000/login` and sign in with:

- Email: `admin@example.com`
- Password: `password`

## Installation

1. Install PHP dependencies.

```bash
composer install
```

2. Install frontend dependencies.

```bash
npm install
```

3. Create the environment file.

```bash
cp .env.example .env
```

4. Generate the application key.

```bash
php artisan key:generate
```

5. Start the Docker services (Laravel Sail).

```bash
./vendor/bin/sail up -d
```
*Note: Laravel Sail will automatically start the web server inside the container, exposing the application on port 8000 of your host machine.*

6. Run database migrations and seed the demo user inside the container.

```bash
./vendor/bin/sail artisan migrate --seed
```

7. Build frontend assets (on your host machine).

```bash
npm run build
```

The application is now ready and running! You do not need to run a separate `serve` command because the Sail container is already serving the application.

Application URL:

- `http://localhost:8000`

For active frontend development, run Vite on your host machine in a separate terminal:

```bash
npm run dev
```

## Login Credentials

- Local demo accounts only. Do not use these seeded credentials outside local development/reviewer environments.
- **Admin Email:** `admin@example.com`
- **Staff Email:** `staff@example.com`
- **Fallback Demo Email:** `test@example.com`
- **Password:** `password`

If login fails with `These credentials do not match our records.`, seed the active database again:

```bash
./bin/demo-users
```

If you want to restore the full local demo dataset again:

```bash
./bin/demo-data
```

If you want an exact clean demo reset back to the seeded baseline:

```bash
./bin/demo-data --fresh
```

Equivalent explicit Sail command:

```bash
./vendor/bin/sail artisan db:seed --class=DemoUserSeeder
```

If you need a clean reset:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

## Roles

- `admin` can manage products and sales orders end-to-end
- `staff` can view products, view sales orders, create sales orders, and export sales order data

## Usage Flow

1. Open `http://localhost:8000/login`.
2. Log in with the demo credentials.
3. Open `Products` from the top navigation or dashboard.
4. Create one or more products.
5. Open `Sales Orders` from the top navigation or dashboard.
6. Create a sales order by selecting a product, entering quantity, and setting the order date.
7. Edit and delete records to verify full CRUD behavior.

## Daily Commands

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
./bin/demo-users
./bin/demo-data
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan test
npm run dev
```

## Features

### Authentication

- login with email and password
- protected dashboard
- logout
- seeded admin and staff demo accounts

## Additional Features

The application also includes a few safeguards and convenience features beyond the basic CRUD requirement:

1. **Financial & Data Integrity (Price Snapshotting):** Product prices are snapshotted into the transactions table upon order creation, preventing historical financial data corruption when product master prices change.
2. **Automated Inventory Control:** Sales orders automatically deduct or restore product stock. Strict validation prevents ordering beyond available stock.
3. **Database Atomicity:** Multi-table operations such as order creation, update, deletion, and stock adjustment are wrapped inside `DB::transaction()`.
4. **Role-Based Access Control (RBAC):** Built-in local authorization. `Admin` has full access, while `Staff` is limited to view and transactional creation flows.
5. **Business Intelligence Dashboard:** The dashboard shows Total Revenue, Total Orders, and Total Products for quick reviewer inspection.
6. **Data Portability:** Sales order history can be exported to CSV, including active search filters.

## Architecture Overview

- **Controllers:** keep HTTP concerns only. Request parsing, authorization entry points, service calls, and response handling stay in the controller.
- **Form Requests:** own validation rules, including reusable stock validation for sales orders.
- **Service Layer:** `App\Services\SalesOrderService` handles transactional sales order use cases, stock mutation, price snapshotting, and total calculation.
- **Models & Scopes:** `Product` and `SalesOrder` expose local scopes for search, sorting, and eager-loading to keep controllers and services DRY.

## Data Integrity Safeguards

- Sales order create, update, and delete flows are wrapped in `DB::transaction()`.
- Stock-sensitive writes use pessimistic locking via `lockForUpdate()`.
- Stock is validated twice:
  - at request validation time for fast user feedback,
  - inside the transactional service layer to protect against race conditions.

### Product Master Data

- product list
- product search by name or SKU
- create product
- edit product
- delete product

Fields:

- name
- SKU
- price
- stock

### Sales Order Transactions

- sales order list
- sales order search by product, SKU, or order date
- create sales order
- edit sales order
- delete sales order
- relation to product master data
- automatic unit price and total price calculation
- stock deduction and restoration on create, update, and delete
- CSV export with filter support

Fields:

- product
- quantity
- unit price
- total price
- order date

### Dashboard Analytics

- total revenue widget
- total products widget
- total transactions widget

## Running Tests

Recommended: run the test suite through Laravel Sail so the environment matches the documented setup:

```bash
./vendor/bin/sail artisan test
```

If you are running the application locally on your host (non-Docker):

```bash
php artisan test
```

Run focused feature suites (prepend with `./vendor/bin/sail` if using Sail):

```bash
./vendor/bin/sail artisan test --filter=DashboardTest
./vendor/bin/sail artisan test --filter=ProductCrudTest
./vendor/bin/sail artisan test --filter=SalesOrderCrudTest
```

## Tech Stack

- **Framework:** Laravel 11
- **Language:** PHP 8.2+
- **Database:** MySQL 8 via Laravel Sail
- **Authentication:** Laravel Breeze (Blade)
- **Frontend:** Blade, Tailwind CSS, Vite
- **Testing:** PHPUnit
