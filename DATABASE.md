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

# Customer Master Table

Stores customer information used for billing, invoicing, quotations, and sales transactions.

| Column Name      | Data Type         | Nullable | Default        | Description                          |
| :--------------- | :---------------- | :------: | :------------: | :----------------------------------: |
| customer_id      | BIGINT UNSIGNED   |    No    | Auto Increment | Primary Key                          |
| customer_code    | VARCHAR(20)       |    No    |       -        | Unique Customer Code (CUS0001)       |
| customer_name    | VARCHAR(100)      |    No    |       -        | Customer Name                        |
| email            | VARCHAR(100)      |   Yes    |      NULL      | Customer Email Address               |
| phone            | VARCHAR(20)       |   Yes    |      NULL      | Customer Phone Number                |
| gst_number       | VARCHAR(15)       |   Yes    |      NULL      | GST Identification Number            |
| billing_address  | TEXT              |   Yes    |      NULL      | Customer Billing Address             |
| shipping_address | TEXT              |   Yes    |      NULL      | Customer Shipping Address            |
| status           | ENUM('0','1')     |    No    |      '1'       | 0 = Inactive, 1 = Active             |
| created_by       | BIGINT UNSIGNED   |   Yes    |      NULL      | User Who Created Record              |
| updated_by       | BIGINT UNSIGNED   |   Yes    |      NULL      | User Who Last Updated Record         |
| created_at       | TIMESTAMP         |   Yes    |      NULL      | Record Creation Timestamp            |
| updated_at       | TIMESTAMP         |   Yes    |      NULL      | Last Update Timestamp                |
| deleted_at       | TIMESTAMP         |   Yes    |      NULL      | Soft Delete Timestamp                |

## Notes

### Primary Key

- `customer_id`

### Customer Code

- Auto-generated unique code.
- Examples:
  - `CUS0001`
  - `CUS0002`
  - `CUS0003`

### Status

| Value | Meaning |
| ------- | ------- |
| 0 | Inactive |
| 1 | Active |

### GST Number

- Should be unique when provided.
- Can be NULL for customers without GST registration.

### Soft Delete

- Records are not permanently deleted.
- Deleted records are marked using the `deleted_at` column.

## Relationships

| Column | References |
| ------- | ------- |
| created_by | users.id |
| updated_by | users.id |

## SQL Definition

```sql
CREATE TABLE `customers` (
  `customer_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gst_number` varchar(15) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0: Inactive, 1: Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`customer_id`),

  UNIQUE KEY `customers_customer_code_unique` (`customer_code`),
  UNIQUE KEY `customers_email_unique` (`email`),
  UNIQUE KEY `customers_gst_number_unique` (`gst_number`)
);
```

# SUPPLIER MANAGEMENT

# Supplier Master Table

Stores supplier information used for purchases, procurement, inventory management, expense tracking, and vendor transactions.

| Column Name      | Data Type       | Nullable |     Default    | Description                                  |
| :--------------- | :-------------- | :------: | :------------: | :------------------------------------------- |
| supplier_id      | BIGINT UNSIGNED |    No    | Auto Increment | Primary Key                                  |
| supplier_code    | VARCHAR(20)     |    No    |        -       | Unique Supplier Code (SUP010001)             |
| supplier_name    | VARCHAR(100)    |    No    |        -       | Supplier Name                                |
| contact_person   | VARCHAR(100)    |    Yes   |      NULL      | Supplier Contact Person                      |
| email            | VARCHAR(100)    |    Yes   |      NULL      | Supplier Email Address                       |
| phone            | VARCHAR(20)     |    Yes   |      NULL      | Supplier Phone Number                        |
| gst_number       | VARCHAR(15)     |    Yes   |      NULL      | GST Identification Number                    |
| billing_address  | TEXT            |    Yes   |      NULL      | Supplier Billing Address                     |
| shipping_address | TEXT            |    Yes   |      NULL      | Supplier Shipping Address                    |
| status           | ENUM('0','1')   |    No    |       '1'      | 0 = Inactive, 1 = Active                     |
| created_by       | BIGINT UNSIGNED |    Yes   |      NULL      | User Who Created Record (usermaster.user_id) |
| updated_by       | BIGINT UNSIGNED |    Yes   |      NULL      | User Who Last Updated Record                 |
| created_at       | TIMESTAMP       |    Yes   |      NULL      | Record Creation Timestamp                    |
| updated_at       | TIMESTAMP       |    Yes   |      NULL      | Last Update Timestamp                        |
| deleted_at       | TIMESTAMP       |    Yes   |      NULL      | Soft Delete Timestamp                        |

---

## Notes

### Primary Key

* `supplier_id`

### Supplier Code

Uses the same logic as Customer Code.

Format:

```text
SUP(UserID)(Sequence)
```

Examples:

```text
SUP010001
SUP010002
SUP010003

SUP020001
SUP020002
```

Where:

| Part | Meaning                  |
| ---- | ------------------------ |
| SUP  | Supplier Prefix          |
| 01   | User ID from usermaster  |
| 0001 | Supplier Sequence Number |

Example:

If user_id = 1 creates suppliers:

```text
SUP010001
SUP010002
SUP010003
```

If user_id = 2 creates suppliers:

```text
SUP020001
SUP020002
SUP020003
```

This ensures supplier codes remain unique even when multiple users create suppliers.

## Relationships

| Column     | References         |
| ---------- | ------------------ |
| created_by | usermaster.user_id |
| updated_by | usermaster.user_id |

---

## SQL Definition

```sql
CREATE TABLE `suppliermaster` (
  `supplier_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(20) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gst_number` varchar(15) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0: Inactive, 1: Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`supplier_id`),

  UNIQUE KEY `suppliermaster_supplier_code_unique` (`supplier_code`),
  UNIQUE KEY `suppliermaster_email_unique` (`email`),
  UNIQUE KEY `suppliermaster_gst_number_unique` (`gst_number`)
);
```

# PRODUCT MANAGEMENT

# Category Master Table

Stores product categories used to classify products.

| Column Name  | Data Type         | Nullable | Default        | Description                                  |
| :----------- | :---------------- | :------: | :------------: | :------------------------------------------: |
| category_id  | BIGINT UNSIGNED   |    No    | Auto Increment | Primary Key                                  |
| category_name| VARCHAR(100)      |    No    |       -        | Category Name                                |
| description  | TEXT              |   Yes    |      NULL      | Category Description                         |
| status       | ENUM('0','1')     |    No    |      '1'       | 0 = Inactive, 1 = Active                     |
| created_by   | BIGINT UNSIGNED   |   Yes    |      NULL      | User Who Created Record                      |
| updated_by   | BIGINT UNSIGNED   |   Yes    |      NULL      | User Who Last Updated Record                 |
| created_at   | TIMESTAMP         |   Yes    |      NULL      | Record Creation Timestamp                    |
| updated_at   | TIMESTAMP         |   Yes    |      NULL      | Last Update Timestamp                        |
| deleted_at   | TIMESTAMP         |   Yes    |      NULL      | Soft Delete Timestamp                        |

## Notes

### Primary Key

- `category_id`

### Status

| Value | Meaning |
| ------- | ------- |
| 0 | Inactive |
| 1 | Active |

### Relationships

| Column | References |
| ------- | ---------- |
| created_by | usermaster.user_id |
| updated_by | usermaster.user_id |

## SQL Definition

```sql
CREATE TABLE `categorymaster` (
  `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_code` varchar(20) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0: Inactive, 1: Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`category_id`),

  UNIQUE KEY `categorymaster_category_code_unique` (`category_code`),
  UNIQUE KEY `categorymaster_createdby_categoryname_unique` (`created_by`,`category_name`)
);
```

---

# Tax Master Table

Stores product tax information such as GST or VAT.

| Column Name    | Data Type         | Nullable | Default        | Description                          |
| :------------- | :---------------- | :------: | :------------: | :----------------------------------: |
| tax_id         | BIGINT UNSIGNED   |    No    | Auto Increment | Primary Key                          |
| tax_name       | VARCHAR(50)       |    No    |       -        | Tax Name                             |
| tax_percentage | DECIMAL(5,2)      |    No    |      0.00      | Tax Percentage                       |
| status         | ENUM('0','1')     |    No    |      '1'       | 0 = Inactive, 1 = Active             |
| created_by     | BIGINT UNSIGNED   |   Yes    |      NULL      | User Who Created Record              |
| updated_by     | BIGINT UNSIGNED   |   Yes    |      NULL      | User Who Last Updated Record         |
| created_at     | TIMESTAMP         |   Yes    |      NULL      | Record Creation Timestamp            |
| updated_at     | TIMESTAMP         |   Yes    |      NULL      | Last Update Timestamp                |
| deleted_at     | TIMESTAMP         |   Yes    |      NULL      | Soft Delete Timestamp                |

## Notes

### Primary Key

- `tax_id`

### Relationships

| Column | References |
| ------- | ---------- |
| created_by | usermaster.user_id |
| updated_by | usermaster.user_id |

## SQL Definition

```sql
CREATE TABLE `taxmaster` (
  `tax_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tax_name` varchar(50) NOT NULL,
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`tax_id`)
);
```

---

# Product Master Table

Stores product information used in purchasing, inventory, quotations, invoices, and sales.

| Column Name     | Data Type         | Nullable | Default        | Description                                  |
| :-------------- | :---------------- | :------: | :------------: | :------------------------------------------: |
| product_id      | BIGINT UNSIGNED   |    No    | Auto Increment | Primary Key                                  |
| product_code    | VARCHAR(20)       |    No    |       -        | Unique Product Code (PRD010001)              |
| product_name    | VARCHAR(150)      |    No    |       -        | Product Name                                 |
| category_id     | BIGINT UNSIGNED   |    No    |       -        | Category Reference                           |
| supplier_id     | BIGINT UNSIGNED   |   Yes    |      NULL      | Supplier Reference                           |
| tax_id          | BIGINT UNSIGNED   |   Yes    |      NULL      | Tax Reference                                |
| sku             | VARCHAR(50)       |   Yes    |      NULL      | Stock Keeping Unit                           |
| barcode         | VARCHAR(100)      |   Yes    |      NULL      | Barcode                                      |
| purchase_price  | DECIMAL(10,2)     |    No    |      0.00      | Purchase Price                               |
| selling_price   | DECIMAL(10,2)     |    No    |      0.00      | Selling Price                                |
| reorder_level   | INT               |    No    |       0        | Minimum Stock Level                          |
| status          | ENUM('0','1')     |    No    |      '1'       | 0 = Inactive, 1 = Active                     |
| created_by      | BIGINT UNSIGNED   |   Yes    |      NULL      | User Who Created Record                      |
| updated_by      | BIGINT UNSIGNED   |   Yes    |      NULL      | User Who Last Updated Record                 |
| created_at      | TIMESTAMP         |   Yes    |      NULL      | Record Creation Timestamp                    |
| updated_at      | TIMESTAMP         |   Yes    |      NULL      | Last Update Timestamp                        |
| deleted_at      | TIMESTAMP         |   Yes    |      NULL      | Soft Delete Timestamp                        |

## Notes

### Primary Key

- `product_id`

### Product Code

Auto-generated unique code.

Format:

```text
PRD(UserID)(Sequence)
```

Examples:

```text
PRD010001
PRD010002
PRD020001
```

### Status

| Value | Meaning |
| ------- | ------- |
| 0 | Inactive |
| 1 | Active |

### Relationships

| Column | References |
| ------- | ---------- |
| category_id | categorymaster.category_id |
| supplier_id | suppliermaster.supplier_id |
| tax_id | taxmaster.tax_id |
| created_by | usermaster.user_id |
| updated_by | usermaster.user_id |

### Unique Constraints

- `product_code`
- (`created_by`, `sku`)
- (`created_by`, `barcode`)
- (`created_by`, `product_name`)

## SQL Definition

```sql
CREATE TABLE `productmaster` (
  `product_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_code` varchar(20) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tax_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `purchase_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `selling_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reorder_level` int NOT NULL DEFAULT '0',
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`product_id`),

  UNIQUE KEY `productmaster_product_code_unique` (`product_code`),
  UNIQUE KEY `productmaster_createdby_sku_unique` (`created_by`,`sku`),
  UNIQUE KEY `productmaster_createdby_barcode_unique` (`created_by`,`barcode`),
  UNIQUE KEY `productmaster_createdby_productname_unique` (`created_by`,`product_name`)
);
```

---

# Inventory Master Table

Stores the current stock available for each product.

| Column Name       | Data Type       | Nullable | Default        | Description                 |
| :---------------- | :-------------- | :------: | :------------: | :-------------------------: |
| inventory_id      | BIGINT UNSIGNED |    No    | Auto Increment | Primary Key                 |
| product_id        | BIGINT UNSIGNED |    No    |       -        | Product Reference           |
| quantity_in_stock | INT             |    No    |       0        | Available Quantity          |
| last_updated      | TIMESTAMP       |   Yes    | CURRENT_TIMESTAMP | Last Stock Update       |

## Notes

### Primary Key

- `inventory_id`

### Relationships

| Column | References |
| ------- | ---------- |
| product_id | productmaster.product_id |

## SQL Definition

```sql
CREATE TABLE `inventorymaster` (
  `inventory_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_in_stock` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`inventory_id`),

  UNIQUE KEY `inventorymaster_product_unique` (`product_id`)
);
```

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
