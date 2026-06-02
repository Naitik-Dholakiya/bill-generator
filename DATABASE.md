# Fully Normalized Database Design for Billing System

## 1. Overview

This database design is created for a modern Billing / Invoice Management System.

The schema follows normalization principles:

* 1NF (First Normal Form)
* 2NF (Second Normal Form)
* 3NF (Third Normal Form)
* BCNF (Boyce-Codd Normal Form)

The design supports:

* Customer Management
* Product Management
* Categories
* Billing / Invoice Generation
* Invoice Items
* Payments
* Taxes
* Discounts
* Users & Roles
* Inventory Management
* Suppliers
* Purchase Records

---

# 2. Main Modules

1. Users & Roles
2. Customers
3. Suppliers
4. Products
5. Categories
6. Inventory
7. Invoices
8. Invoice Items
9. Payments
10. Taxes
11. Discounts
12. Purchases
13. Purchase Items

---

# 3. Database Tables

---

# USERS & AUTHENTICATION

# Usermaster Table

Stores login users.

| Column Name | Data Type         | Nullable | Default        | Description                           |
| :---------- | :---------------- | :------: | :------------: | :-----------------------------------: |
| user_id     | BIGINT UNSIGNED   |    No    | Auto Increment | Primary Key                           |
| full_name   | VARCHAR(100)      |    No    |       -        | User Full Name                        |
| email       | VARCHAR(100)      |    No    |       -        | User Email Address                    |
| password    | VARCHAR(255)      |    No    |       -        | Hashed User Password                  |
| phone       | VARCHAR(15)       |   Yes    |      NULL      | User Phone Number                     |
| is_admin    | ENUM('N','Y')     |    No    |      'N'       | N = User, Y = Admin                   |
| status      | ENUM('0','1','2') |    No    |      '1'       | 0 = Inactive, 1 = Active, 2 = Blocked |
| created_at  | TIMESTAMP         |   Yes    |      NULL      | Record Creation Timestamp             |
| updated_at  | TIMESTAMP         |   Yes    |      NULL      | Last Update Timestamp                 |

## Notes

- **Primary Key:** `user_id`
- **Admin Status:**
  - `N` → Normal User
  - `Y` → Administrator
- **Account Status:**
  - `0` → Inactive
  - `1` → Active
  - `2` → Blocked
- **Password Storage:**
  - Store passwords using a secure hashing algorithm such as `bcrypt` or `Argon2`.
  - Never store plain text passwords.

## SQL Definition

```sql
CREATE TABLE `usermaster` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `is_admin` enum('N','Y') NOT NULL DEFAULT 'N' COMMENT 'N: No, Y: Yes',
  `status` enum('0','1','2') NOT NULL DEFAULT '1' COMMENT '0: Inactive, 1: Active, 2: Blocked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);
```
# CUSTOMER MANAGEMENT

## customers

Stores customer information.

| Column           | Type         | Constraints               |
| ---------------- | ------------ | ------------------------- |
| customer_id      | INT          | PK, AUTO_INCREMENT        |
| customer_name    | VARCHAR(100) | NOT NULL                  |
| email            | VARCHAR(100) | UNIQUE                    |
| phone            | VARCHAR(15)  | NULL                      |
| gst_number       | VARCHAR(30)  | NULL                      |
| billing_address  | TEXT         | NULL                      |
| shipping_address | TEXT         | NULL                      |
| created_at       | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP |

---

# SUPPLIER MANAGEMENT

## suppliers

Stores supplier information.

| Column         | Type         | Constraints               |
| -------------- | ------------ | ------------------------- |
| supplier_id    | INT          | PK, AUTO_INCREMENT        |
| supplier_name  | VARCHAR(100) | NOT NULL                  |
| contact_person | VARCHAR(100) | NULL                      |
| email          | VARCHAR(100) | NULL                      |
| phone          | VARCHAR(15)  | NULL                      |
| address        | TEXT         | NULL                      |
| gst_number     | VARCHAR(30)  | NULL                      |
| created_at     | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP |

---

# PRODUCT MANAGEMENT

## categories

Stores product categories.

| Column        | Type         | Constraints        |
| ------------- | ------------ | ------------------ |
| category_id   | INT          | PK, AUTO_INCREMENT |
| category_name | VARCHAR(100) | UNIQUE, NOT NULL   |
| description   | TEXT         | NULL               |

---

## products

Stores product details.

| Column         | Type                      | Constraints                  |
| -------------- | ------------------------- | ---------------------------- |
| product_id     | INT                       | PK, AUTO_INCREMENT           |
| category_id    | INT                       | FK -> categories.category_id |
| supplier_id    | INT                       | FK -> suppliers.supplier_id  |
| product_name   | VARCHAR(150)              | NOT NULL                     |
| sku            | VARCHAR(50)               | UNIQUE                       |
| barcode        | VARCHAR(100)              | UNIQUE                       |
| unit_price     | DECIMAL(10,2)             | NOT NULL                     |
| purchase_price | DECIMAL(10,2)             | NOT NULL                     |
| tax_id         | INT                       | FK -> taxes.tax_id           |
| reorder_level  | INT                       | DEFAULT 0                    |
| status         | ENUM('active','inactive') | DEFAULT 'active'             |
| created_at     | TIMESTAMP                 | DEFAULT CURRENT_TIMESTAMP    |

---

## inventory

Tracks product stock.

| Column            | Type      | Constraints                                           |
| ----------------- | --------- | ----------------------------------------------------- |
| inventory_id      | INT       | PK, AUTO_INCREMENT                                    |
| product_id        | INT       | FK -> products.product_id                             |
| quantity_in_stock | INT       | NOT NULL                                              |
| last_updated      | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

---

# TAX MANAGEMENT

## taxes

Stores tax information.

| Column         | Type         | Constraints        |
| -------------- | ------------ | ------------------ |
| tax_id         | INT          | PK, AUTO_INCREMENT |
| tax_name       | VARCHAR(50)  | NOT NULL           |
| tax_percentage | DECIMAL(5,2) | NOT NULL           |
| description    | TEXT         | NULL               |

---

# DISCOUNT MANAGEMENT

## discounts

Stores discount information.

| Column         | Type                       | Constraints        |
| -------------- | -------------------------- | ------------------ |
| discount_id    | INT                        | PK, AUTO_INCREMENT |
| discount_name  | VARCHAR(100)               | NOT NULL           |
| discount_type  | ENUM('percentage','fixed') | NOT NULL           |
| discount_value | DECIMAL(10,2)              | NOT NULL           |
| start_date     | DATE                       | NULL               |
| end_date       | DATE                       | NULL               |

---

# BILLING / INVOICE MANAGEMENT

## invoices

Stores invoice master records.

| Column          | Type                             | Constraints                 |
| --------------- | -------------------------------- | --------------------------- |
| invoice_id      | INT                              | PK, AUTO_INCREMENT          |
| invoice_number  | VARCHAR(50)                      | UNIQUE, NOT NULL            |
| customer_id     | INT                              | FK -> customers.customer_id |
| user_id         | INT                              | FK -> users.user_id         |
| invoice_date    | DATE                             | NOT NULL                    |
| subtotal        | DECIMAL(12,2)                    | NOT NULL                    |
| total_tax       | DECIMAL(12,2)                    | DEFAULT 0                   |
| discount_amount | DECIMAL(12,2)                    | DEFAULT 0                   |
| grand_total     | DECIMAL(12,2)                    | NOT NULL                    |
| payment_status  | ENUM('pending','partial','paid') | DEFAULT 'pending'           |
| notes           | TEXT                             | NULL                        |
| created_at      | TIMESTAMP                        | DEFAULT CURRENT_TIMESTAMP   |

---

## invoice_items

Stores products inside invoices.

| Column          | Type          | Constraints               |
| --------------- | ------------- | ------------------------- |
| invoice_item_id | INT           | PK, AUTO_INCREMENT        |
| invoice_id      | INT           | FK -> invoices.invoice_id |
| product_id      | INT           | FK -> products.product_id |
| quantity        | INT           | NOT NULL                  |
| unit_price      | DECIMAL(10,2) | NOT NULL                  |
| tax_amount      | DECIMAL(10,2) | DEFAULT 0                 |
| discount_amount | DECIMAL(10,2) | DEFAULT 0                 |
| total_amount    | DECIMAL(12,2) | NOT NULL                  |

---

# PAYMENT MANAGEMENT

## payment_methods

Stores payment method types.

| Column            | Type        | Constraints        |
| ----------------- | ----------- | ------------------ |
| payment_method_id | INT         | PK, AUTO_INCREMENT |
| method_name       | VARCHAR(50) | UNIQUE             |

---

## payments

Stores payment transactions.

| Column            | Type                               | Constraints                             |
| ----------------- | ---------------------------------- | --------------------------------------- |
| payment_id        | INT                                | PK, AUTO_INCREMENT                      |
| invoice_id        | INT                                | FK -> invoices.invoice_id               |
| payment_method_id | INT                                | FK -> payment_methods.payment_method_id |
| paid_amount       | DECIMAL(12,2)                      | NOT NULL                                |
| payment_date      | DATETIME                           | NOT NULL                                |
| reference_number  | VARCHAR(100)                       | NULL                                    |
| payment_status    | ENUM('success','failed','pending') | DEFAULT 'success'                       |
| created_at        | TIMESTAMP                          | DEFAULT CURRENT_TIMESTAMP               |

---

# PURCHASE MANAGEMENT

## purchases

Stores supplier purchase records.

| Column        | Type          | Constraints                 |
| ------------- | ------------- | --------------------------- |
| purchase_id   | INT           | PK, AUTO_INCREMENT          |
| supplier_id   | INT           | FK -> suppliers.supplier_id |
| user_id       | INT           | FK -> users.user_id         |
| purchase_date | DATE          | NOT NULL                    |
| total_amount  | DECIMAL(12,2) | NOT NULL                    |
| created_at    | TIMESTAMP     | DEFAULT CURRENT_TIMESTAMP   |

---

## purchase_items

Stores purchased products.

| Column           | Type          | Constraints                 |
| ---------------- | ------------- | --------------------------- |
| purchase_item_id | INT           | PK, AUTO_INCREMENT          |
| purchase_id      | INT           | FK -> purchases.purchase_id |
| product_id       | INT           | FK -> products.product_id   |
| quantity         | INT           | NOT NULL                    |
| purchase_price   | DECIMAL(10,2) | NOT NULL                    |
| total_price      | DECIMAL(12,2) | NOT NULL                    |

---

# 4. Relationships

## One-to-Many Relationships

| Parent Table | Child Table    | Relationship                      |
| ------------ | -------------- | --------------------------------- |
| roles        | users          | One Role -> Many Users            |
| categories   | products       | One Category -> Many Products     |
| suppliers    | products       | One Supplier -> Many Products     |
| customers    | invoices       | One Customer -> Many Invoices     |
| invoices     | invoice_items  | One Invoice -> Many Items         |
| products     | invoice_items  | One Product -> Many Invoice Items |
| invoices     | payments       | One Invoice -> Many Payments      |
| suppliers    | purchases      | One Supplier -> Many Purchases    |
| purchases    | purchase_items | One Purchase -> Many Items        |

---

# 5. Normalization Explanation

## First Normal Form (1NF)

* No repeating groups.
* Atomic values only.
* Each row uniquely identified.

Example:

* Invoice items stored separately in `invoice_items`.

---

## Second Normal Form (2NF)

* All non-key attributes fully depend on the primary key.
* Partial dependency removed.

Example:

* Product details are stored only in `products` table.

---

## Third Normal Form (3NF)

* No transitive dependencies.
* Non-key columns depend only on primary key.

Example:

* Category names stored in `categories` instead of `products`.

---

## BCNF

* Every determinant is a candidate key.
* Avoids anomalies.

Example:

* Unique SKU for products.
* Unique invoice number.

---

# 6. Suggested Indexes

## Performance Optimization Indexes

```sql
CREATE INDEX idx_customer_name ON customers(customer_name);
CREATE INDEX idx_product_name ON products(product_name);
CREATE INDEX idx_invoice_date ON invoices(invoice_date);
CREATE INDEX idx_payment_date ON payments(payment_date);
CREATE INDEX idx_sku ON products(sku);
```

---

# 7. Recommended Features

## Optional Advanced Tables

### audit_logs

Tracks system activity.

### returns

Stores returned products.

### expenses

Stores company expenses.

### warehouses

Supports multiple warehouse inventory.

### shipment_tracking

Tracks deliveries.

### loyalty_points

Customer reward system.

---

# 8. Example Invoice Flow

1. User logs into system.
2. Customer selected.
3. Products added to invoice.
4. Invoice items inserted.
5. Inventory reduced.
6. Tax calculated.
7. Payment recorded.
8. Invoice marked paid.

---

# 9. Best Practices

* Use foreign keys.
* Use transactions for billing.
* Store password hashes only.
* Never store calculated totals redundantly unless required.
* Use soft delete if needed.
* Add audit logs for security.
* Use UUIDs in large-scale systems.
* Use indexing for search-heavy systems.

---

# 10. Recommended Tech Stack

## Backend

* Laravel
* Node.js + Express
* Django
* Spring Boot

## Database

* MySQL
* PostgreSQL

## Frontend

* React.js
* Vue.js
* Angular

---

# 11. Sample ER Diagram Structure

```text
customers ----< invoices ----< invoice_items >---- products >---- categories
                           |
                           |
                        payments

suppliers ----< purchases ----< purchase_items >---- products

roles ----< users
```

---

# 12. Conclusion

This billing system database is:

* Fully normalized
* Scalable
* Secure
* Production-ready
* Suitable for ERP systems
* Suitable for POS systems
* Suitable for GST billing software
* Suitable for inventory-based billing applications

The schema minimizes:

* Data redundancy
* Update anomalies
* Insert anomalies
* Delete anomalies

while improving:

* Data consistency
* Maintainability
* Scalability
* Query performance
