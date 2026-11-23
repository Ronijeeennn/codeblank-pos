# Code Blank POS

A Point-of-Sale system built with **CodeIgniter 4** for IT0049 (Web System Technologies).
Staff sign in, manage products, customers and staff accounts, record sales that reduce stock, and review the sales history.

- **Live site:** _add your hosted URL here_
- **Demo login:** `admin` / `admin12345` (also `cashier` / `cashier123`). Change these after the first login.

## Features

| Area | What it does |
|---|---|
| Authentication | Login with `password_verify()`, session regenerated on login, throttled to 5 attempts/minute/IP, POST-only logout that destroys the session. An `auth` filter protects every page except `/login`; deleting an account also ends that person's session. |
| Products | Card grid with search, stock filters (all / running low / out of stock) and pagination; add, edit, delete. Optional image upload with live preview, cropped to a 400x400 square. |
| Customers | Full CRUD with search. Deleting a customer keeps their past sales (they become "Walk-in"). |
| Staff | Full CRUD, hashed passwords, avatar upload cropped to 200x200. Blank password on edit keeps the current one. |
| Record Sale | Tap a product tile, optional customer, quantity stepper. Live receipt preview. Stock drops by the quantity sold; a sale for more than is in stock is **rejected** with a message such as *"you asked for 50 but only 11 of Chicken Sandwich are in stock"*. |
| Sales History | Paginated list: sale #, date, product, customer (or Walk-in), staff member, quantity, total. |
| Dashboard | Today's sales, 7-day sales chart, top sellers, low-stock list, latest sales. |

## Design

A corner-store look: a striped shop awning as the signature element, deep navy with a marigold accent, Bricolage Grotesque
for headings and prices, Instrument Sans for text. Fully responsive (the sidebar becomes a top bar on phones).
Fonts load from Google Fonts, so the first load needs internet; without it the app falls back to system fonts and still works.

## Requirements

PHP 8.1+ with `intl`, `mbstring`, `gd` (image resizing) and `mysqli`; MySQL/MariaDB; Composer.
With XAMPP, enable `intl` and `gd` in `php.ini` (remove the `;` before `extension=intl` and `extension=gd`).

## Run it locally

```bash
composer install
cp .env.example .env                 # then edit the database settings
```

Create an empty database (for example `tindahan_pos`) in phpMyAdmin, then choose **one** way to build the tables:

```bash
# Option A: migrations + seeder (recommended)
php spark migrate
php spark db:seed PosSeeder   # includes a week of sample sales so the dashboard chart has data

# Option B: import database/schema.sql in phpMyAdmin (same tables + demo data)
```

```bash
php spark serve                      # http://localhost:8080
```

## Project structure (MVC)

```
app/
  Config/Routes.php        every URL, grouped by filter (auto-routing is off)
  Config/Filters.php       aliases: auth, guest; global: csrf, secureheaders
  Controllers/             Auth, Dashboard, Products, Customers, Staff, Sales (thin: validate, call model, redirect)
  Filters/                 AuthFilter, GuestFilter
  Libraries/ImageUploader  one place for upload rules, safe storage and resizing (products + avatars)
  Models/                  ProductModel, CustomerModel, UserModel (hashes passwords), SaleModel (record sale, history)
  Helpers/pos_helper.php   money(), fmt_date(), old_value(), upload_url(), initials()
  Views/                   layouts/main + one folder per area; partials/ for avatar and thumbnail
  Database/Migrations      the four tables in dependency order
  Database/Seeds/PosSeeder demo users, products, customers and a few sales
database/schema.sql        MySQL export of the schema + demo data
public/uploads/            products/ and avatars/ (execution disabled by .htaccess)
```

## Design decisions

- **Recording a sale is one transaction in `SaleModel::record()`.** Stock is reduced with a single conditional statement
  (`UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?`) and the sale row is inserted
  only if exactly one row changed. Two cashiers selling the last unit at once cannot make stock negative, and a failed insert rolls the stock change back.
- **`total_price` is calculated on the server** from the product's current price. The browser only sends ids and quantity.
- **Deleting is protected by the foreign keys in the schema.** A product with sales, or a staff member who recorded sales, cannot be deleted
  (the user gets a clear message; set a product's stock to 0 to stop selling it). Customers can be deleted because `sales.customer_id` is nullable.
  You can't delete the account you're signed in with.
- **Post/Redirect/Get everywhere.** Failed validation redirects back with errors and the old input; password fields are never flashed into the session.

## Security notes

- Every POST form has `csrf_field()` and the `csrf` filter runs globally. Deleting and logging out are POST-only.
- Output is escaped with `esc()`; queries go through the Query Builder (parameterised).
- Passwords: `password_hash(PASSWORD_DEFAULT)` in `UserModel`, 8-character minimum, 72-character maximum (bcrypt limit).
- Uploads: validated by size, real MIME type, extension, image check and dimensions (JPG/PNG/WebP, 2 MB, max 4000x4000);
  saved under a random name; re-encoded by GD (this crops to a square and strips metadata and any hidden payload);
  only the file name is stored in the database; `public/uploads/.htaccess` blocks script execution.
- `.env` (credentials) is git-ignored; use `.env.example` as the template.

## Deploying (InfinityFree)

InfinityFree has no SSH or Composer, always serves from `htdocs/`, and disables PHP's `putenv()`, which CodeIgniter's `.env` loader needs.
So on that host the app uses `app/Config/local.php` instead of `.env`:

1. Locally: `composer install --no-dev --optimize-autoloader` (so `vendor/` is small).
2. Copy `app/Config/local.example.php` to `app/Config/local.php` and fill in the site address and the MySQL details from the InfinityFree panel.
3. In the InfinityFree panel, create a MySQL database, open phpMyAdmin, select the database, and import `database/schema.sql`.
4. With FileZilla, upload the *contents* of the project (including `vendor/`, `.htaccess` and `local.php`, but **not** `.git`, `.env` or `tests/`) into `htdocs/`.
5. Visit your site and sign in with the demo account, then change the demo passwords.

The root `.htaccess` sends every request into `public/` and makes everything outside it unreachable from the web.
Do **not** upload a `.env` file to InfinityFree. `local.php` is git-ignored, so credentials never reach GitHub.

Other hosts that let you set the web root to `public/` can use `.env` as usual.

## How it was tested

The app was exercised end-to-end over HTTP (73 checks): auth guard on every route, CSRF, login errors, validation messages,
product/customer/staff CRUD, image rejection (fake image with PHP payload, wrong type), image resize, stock decrement,
oversell and zero-stock rejection, delete rules, password hashing and logout.
