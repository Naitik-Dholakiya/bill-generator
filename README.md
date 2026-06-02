# Smart Billing Platform — Laravel Custom Bill Generator

A professional billing and invoice management application built with Laravel and [Tailwind CSS](https://tailwindcss.com?utm_source=chatgpt.com). This project is designed to help businesses create elegant invoices, manage customers and products, generate PDFs, and track payments through a modern web interface.

---

## ✨ Features

### 🔐 Authentication

* Custom Registration and Login (built from scratch, without Laravel Breeze)
* Secure password hashing
* Session-based authentication
* Protected routes and logout

### 📄 Invoice Management

* Create professional invoices
* Automatic invoice number generation
* Add multiple products/services to a single bill
* Quantity, price, GST, discount, and total calculations
* Print-ready invoice templates
* PDF download support

### 👥 Customer Management

* Add, edit, delete, and search customers
* Store GST number, contact information, and addresses

### 📦 Product Management

* Manage products and services
* Price, stock, HSN code, and GST percentage

### 💳 Payment Tracking

* Paid, Pending, and Partially Paid statuses
* Due date management

### 📊 Dashboard & Reports

* Sales analytics
* Monthly revenue reports
* Top customers and products
* Pending payment overview

### 🇮🇳 India-Specific Features

* GST Number support
* CGST / SGST calculations
* HSN Code support
* UPI QR code on invoices
* INR formatting

---

## 🛠️ Tech Stack

| Technology                                                     | Purpose            |
| -------------------------------------------------------------- | ------------------ |
| Laravel                                                        | Backend Framework  |
| [Tailwind CSS](https://tailwindcss.com?utm_source=chatgpt.com) | Frontend Styling   |
| MySQL                                                          | Database           |
| DomPDF                                                         | PDF Generation     |
| Chart.js                                                       | Reports and Charts |
| Simple QrCode                                                  | QR Code Generation |

---

## 📂 Project Structure

```plaintext
app/
├── Http/Controllers/
│   ├── AuthController.php
│   ├── CustomerController.php
│   ├── ProductController.php
│   ├── InvoiceController.php
│   └── ReportController.php

app/Models/
├── User.php
├── Customer.php
├── Product.php
├── Invoice.php
└── InvoiceItem.php

resources/views/
├── auth/
├── customers/
├── products/
├── invoices/
└── layouts/
```

---

## 🗄️ Database Tables

* users
* customers
* products
* invoices
* invoice_items
* payments
* company_settings

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd smart-billing-platform
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file:

```env
DB_DATABASE=smart_billing
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Development Server

```bash
php artisan serve
npm run dev
```

Visit: `http://127.0.0.1:8000`

---

## 🔐 Authentication Flow

1. User registers with name, email, and password.
2. Password is hashed using Laravel's hashing system.
3. User logs in using email and password.
4. Session stores authenticated user information.
5. Protected pages are accessible only to logged-in users.
6. Logout clears the session.

---

## 📄 Invoice Workflow

1. Create or select a customer
2. Add products/services
3. Enter quantities and prices
4. Apply GST and discounts
5. Save invoice
6. Print or download as PDF
7. Track payment status

---

## 📦 Recommended Packages

### PDF Generation

```bash
composer require barryvdh/laravel-dompdf
```

### QR Code Generation

```bash
composer require simplesoftwareio/simple-qrcode
```

### Excel Export (Optional)

```bash
composer require maatwebsite/excel
```

---

## 🎨 UI Highlights

* Premium split-screen authentication pages
* Glassmorphism effects
* Responsive design
* Dark aesthetic with gradient backgrounds
* Professional dashboard cards
* Elegant forms and tables

---

## 📈 Future Enhancements

* Email invoices to customers
* Multi-company support
* Role and permission management
* Inventory tracking
* REST API
* Mobile application integration

---

## 🎯 Learning Objectives

This project demonstrates:

* Custom authentication in Laravel
* MVC architecture
* Database relationships
* Dynamic forms
* PDF generation
* Session management
* Modern UI design

---

## 👨‍💻 Author

Developed by **Naitik Dholakiya** as a full-featured Laravel billing and invoice management system.

---

## 📜 License

This project is open-source and available under the MIT License.
