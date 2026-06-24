# Laravel Screening Test App

Laravel 11 web application for a screening test with:

- login authentication
- product master data CRUD
- sales order transaction CRUD
- relational database link from transactions to products
- role-based access control for admin and staff users
- dashboard analytics and CSV export for sales orders

## Engineering Notes

- Architecture and refactor audit details: [`AUDIT.md`](./AUDIT.md)
- The current implementation applies thin controllers, Form Requests for validation, a dedicated service layer for sales order business logic, and local Eloquent scopes for reusable query logic.

## Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm
- Docker Desktop with Docker Compose
- Git

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

## Login Credentials

- **Admin Email:** `admin@example.com`
- **Staff Email:** `staff@example.com`
- **Fallback Demo Email:** `test@example.com`
- **Password:** `password`

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

## Features

### Authentication

- login with email and password
- protected dashboard
- logout
- seeded admin and staff demo accounts

## Advanced Features Implemented (Candidate Edge)

To demonstrate production-grade software development practices, this application goes beyond standard CRUD with:

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

If you are using Laravel Sail (Docker), run the test suite inside the Sail container:

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
