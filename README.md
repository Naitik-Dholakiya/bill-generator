# Bill Generator

A Laravel-based billing and inventory management system built for businesses that need to manage customers, suppliers, products, categories, and invoices from a single dashboard.

This project includes custom authentication, admin controls, invoice generation, PDF export, stock monitoring, and sales insights for daily business operations.

---

## Overview

The application is designed for small and medium businesses to:

- create and manage customer records
- manage supplier and product data
- organize products under categories
- generate professional GST-compliant invoices
- view sales and revenue analytics
- monitor low-stock and reorder items
- export invoice PDFs
- manage users through an admin panel

---

## Implemented Features

### User Authentication and Access Control

- custom registration and login flow
- cookie-based session handling
- role-based access for regular users and admin users
- logout and protected routes
- admin dashboard for user management

### Admin Panel

- admin overview dashboard
- user listing with status, template, and jewelry mode visibility
- create and update user accounts
- manage user invoice template preferences
- toggle jewelry invoice mode for specialized billing workflows

### Customer Management

- add new customers
- update customer data
- view customer details
- delete customers when no longer active
- customer code, GST number, email, phone, and address tracking
- active/inactive customer status handling

### Supplier Management

- supplier dashboard and listing
- create and edit supplier profiles
- supplier code, GST, phone, and address records
- supplier relationship with product catalog

### Product and Category Management

- add, edit, and delete products
- assign suppliers and categories
- product code, HSN-style catalog structure, price, and stock tracking
- reorder-level monitoring for low-stock warnings
- category creation from the product workflow

### Invoice Management

- create invoices with customer and product selection
- add multiple invoice items in a single bill
- quantity, unit price, discount, tax, and total calculations
- automatic invoice number generation
- invoice view, edit, and delete operations
- payment status tracking: paid, partial, and pending
- invoice date and payment records

### PDF and Print Support

- PDF invoice generation using DomPDF
- invoice template support for different business types
- view-ready invoice layouts for printing or download
- Indian currency formatting support

### Dashboard and Analytics

- total customers, products, suppliers, and categories overview
- monthly revenue summary
- GST collected totals
- invoice status breakdown
- recent invoices list
- top customers and top-selling product summaries
- reorder watch panel for stock alerts
- supplier snapshot cards

### Indian Business Features

- GST number support
- tax calculations and totals
- INR currency formatting
- customer billing and invoice details tailored for Indian operations
- support for jewelry-specific invoice workflows

### Settings and Personalization

- user profile and company settings update
- password change flow
- company name, address, and GST configuration
- invoice template configuration per user

---

## Supported Modules

The application currently includes these main modules:

- AuthController
- AdminDashboardController
- AdminUserController
- dashboardController
- customerController
- supplierController
- productController
- invoiceController
- invoiceGeneratorController
- settingController

---

## Database Structure

This project uses Laravel database tables such as:

- usermaster
- customermaster
- suppliermaster
- categorymaster
- productmaster
- invoicemaster
- invoice_items

The app is configured to work with Laravel's SQLite default as well as MySQL-compatible setups.

---

## Tech Stack

| Technology | Purpose |
| --- | --- |
| Laravel 12 | Backend framework |
| PHP 8.2 | Application runtime |
| Blade Templates | UI rendering |
| SQLite / MySQL-ready | Database support |
| DomPDF | PDF invoice generation |
| Tailwind CSS | Styling |
| Vite | Frontend asset build |

---

## Project Structure

```text
app/
├── Console/
├── Helper/
├── Http/
│   ├── Controllers/
│   └── Middleware/
├── Models/
├── Mail/
├── Providers/
├── Support/
config/
database/
public/
resources/
routes/
storage/
tests/
```

---

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd bill-generator
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database configuration:

```env
DB_CONNECTION=sqlite
# or use mysql if needed
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

If using MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bill_generator
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run database migrations

```bash
php artisan migrate
```

### 6. Start the app

```bash
php artisan serve
npm run dev
```

Then open:

```text
http://127.0.0.1:8000
```

---

## Typical Workflow

1. Log in as a user created by the admin
2. Open the dashboard and review business metrics
3. Add customers, suppliers, categories, and products
4. Create an invoice with multiple line items
5. Save and preview the bill
6. Download or print the PDF invoice
7. Track payment status and follow up on outstanding balances

---

## Notes

- The application contains a custom admin flow separate from the normal business-user flow.
- Invoice templates can be customized per user profile.
- Jewelry-specific billing mode is supported for specialized invoice layouts.
- Low-stock and reorder monitoring is included in the dashboard.

---

## License

This project is under the MIT license.

---

## Author

Developed by Naitik Dholakiya.
