# Sales & Customers Management — Laravel Build

This package contains the **application-specific files only** (models, migrations,
controllers, routes, views, seeders). You drop them into a fresh Laravel install
rather than unzip-and-run, because a full Laravel skeleton (bootstrap files,
`.env`, `vendor/`, etc.) is environment-specific and shouldn't be copy-pasted.

## What's included
```
app/Http/Controllers/DashboardController.php
app/Http/Controllers/SalesOrderController.php
app/Http/Controllers/PageController.php
app/Models/Customer.php
app/Models/SalesOrder.php
app/Models/SalesOrderItem.php
app/Models/Ticket.php
database/migrations/*.php
database/factories/*.php
database/seeders/DatabaseSeeder.php
routes/web.php
resources/views/**  (layout, sidebar, topbar, dashboard, sales orders, placeholder)
public/css/app.css  (page/card transition styles)
```

## Step 1 — Create a fresh Laravel project
You need PHP 8.2+, Composer, and a database (MySQL/MariaDB/Postgres, or SQLite for
the quickest path) installed locally.

```bash
composer create-project laravel/laravel sales-dashboard
cd sales-dashboard
```

## Step 2 — Copy these files into the new project
Unzip this package and copy its contents **over** the matching folders in
`sales-dashboard/`, merging (don't delete anything Laravel generated):

```bash
cp -r app/Http/Controllers/*.php   sales-dashboard/app/Http/Controllers/
cp -r app/Models/*.php             sales-dashboard/app/Models/
cp -r database/migrations/*.php    sales-dashboard/database/migrations/
cp -r database/factories/*.php     sales-dashboard/database/factories/
cp -r database/seeders/*.php       sales-dashboard/database/seeders/
cp    routes/web.php               sales-dashboard/routes/web.php
cp -r resources/views/*            sales-dashboard/resources/views/
cp -r public/css                   sales-dashboard/public/
```
(Adjust paths if you extracted this zip somewhere else.)

## Step 3 — Configure the database
Open `sales-dashboard/.env`. Easiest option — SQLite, zero server setup:

```env
DB_CONNECTION=sqlite
# comment out or remove DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD
```
```bash
touch database/database.sqlite
```

Or, if you prefer MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_dashboard
DB_USERNAME=root
DB_PASSWORD=
```
(Create the `sales_dashboard` database first: `mysql -u root -e "CREATE DATABASE sales_dashboard"`.)

## Step 4 — Install dependencies
```bash
composer install
php artisan key:generate
```

## Step 5 — Run migrations and seed the demo data
This creates the `customers`, `sales_orders`, `sales_order_items`, and `tickets`
tables, then seeds them with the same rows shown in the mockups (SO-1001…SO-1007,
Juan Dela Cruz, Maria Santos, etc.) plus 20 extra random customers/orders so the
charts and pagination have real volume.

```bash
php artisan migrate:fresh --seed
```

## Step 6 — Serve the app
```bash
php artisan serve
```
Visit **http://127.0.0.1:8000** — this loads the Dashboard. Click **Sales Orders**
in the sidebar to see the second screen.

## How navigation & transitions work
- **Sidebar links** (`resources/views/partials/sidebar.blade.php`) use Laravel
  named routes (`route('dashboard')`, `route('sales-orders.index')`, etc.), so
  every button goes to a real, working route — including placeholder pages for
  Customers / Support System / Reports / MCM (`PageController`) and an `Exit`
  route.
- **Active-state highlighting**: `request()->routeIs(...)` adds the teal
  background to whichever nav item matches the current route.
- **Page transitions**: `public/css/app.css` fades/slides the `<main>` content
  in on every page load (`.page-transition`), cards lift on hover
  (`.card-hover`), and status badges pop in (`.badge-in`).
- **Sales Order detail panel**: clicking a row in the "Sales Order Listing"
  table calls `GET /sales-orders/{id}` (`SalesOrderController@show`) via
  `fetch()` and Alpine.js updates the right-hand panel without a full page
  reload — the same interaction pattern as the mockup's live order detail view.
- **Charts**: `Chart.js` renders the Sales Overview line chart and Orders by
  Status donut chart from data computed server-side in `DashboardController`.

## Extending it
- Add authentication (`php artisan make:auth` equivalent — e.g. Laravel
  Breeze) and swap the `/exit` route for `Auth::logout()`.
- Flesh out `PageController`/`placeholder.blade.php` into real Customers,
  Support System, Reports, and MCM screens using the same
  controller → view → route pattern used for Dashboard and Sales Orders.
- Add a `SalesOrderRequest` form request + a create/edit form if you need to
  manage orders through the UI instead of just the seeder.
