# Billora Technical Documentation

**Updated 18 October 2026**

## Overview

Billora is a Laravel 12 application for multi-workspace customer, supplier, product, category, and invoice management. Server-rendered Blade views provide the authenticated user workspace and administrator console. The application uses PHP 8.2+, Laravel authentication middleware/custom auth controllers, a relational database, Tailwind CSS 4, and Vite. DomPDF generates invoice PDFs.

## Implemented areas

- **Authentication:** Login/logout and protected user/admin route groups. Workspace-scoped controller queries use the authenticated user identifier.
- **Workspace dashboard:** KPI and sales summaries, invoice status and trend, recent invoices, product/customer/supplier snapshots, and onboarding progress.
- **Customers and suppliers:** Create, list, detail, edit, and delete flows with workspace ownership.
- **Products and categories:** Product maintenance, supplier/category associations, pricing, reorder-level display, and category creation from product entry.
- **Invoices:** Invoice and item create/edit/detail/delete flows; quantity, taxes, discounts, totals, payment status, and PDF generation.
- **Jewellery:** Feature-gated voucher workflows with piece/weight/Touch/wastage and fine-weight calculations. Invoice template keys are configured in `config/invoice.php`.
- **Settings:** User profile, business identity, and password updates.
- **Administration:** Workspace metrics and user account management, including status, jewellery feature access, and invoice-template assignment.

## Planned, not implemented

Stock movements/quantity ledger and supplier bills/vouchers are future modules. Current reorder levels do not constitute inventory-on-hand accounting. Sidebar labels are informational only; there are no routes or data models for these modules.

## Important routes

| Route name | Purpose |
| --- | --- |
| `dashboard` | Authenticated workspace overview |
| `customers.*`, `createCustomer*`, `viewCustomer`, `editCustomer*` | Customer workflows |
| `suppliers.*`, `createSupplier*`, `supplier.*` | Supplier workflows |
| `products.*`, `createProductPost`, `editProductPost` | Product workflows |
| `invoice.*` | Invoice and jewellery voucher workflows |
| `settings`, `settings.update`, `changePassword` | Workspace profile/security settings |
| `admin.*` | Administrator workspace and account management |
| `privacy-policy`, `user-manual`, `documentation` | Public information pages rendered from `docs/` Markdown |

Route definitions are in `routes/web.php`. User-facing operations are protected by the `user.auth` middleware; administration uses `admin.auth`. ID-based customer, supplier, product, and invoice routes also use `resource.owner`, which verifies the record's `created_by` matches the authenticated workspace before allowing access.

## Data model

Core tables include `usermaster`, `customermaster`, `suppliermaster`, `categorymaster`, `productmaster`, `invoicemaster`, and `invoice_items`. Laravel cache and queue tables are also used by the framework. See `database/migrations/` for the authoritative schema and migration history.

Business rows are associated with a workspace/user identifier. Preserve these ownership filters in new queries and apply `resource.owner` to new ID-based routes so changing a URL ID cannot expose or modify another workspace's records. Unauthorized or missing IDs return 404.

## Local development

1. Install PHP dependencies with `composer install`.
2. Configure `.env`, database connection, app key, and mail settings for the local environment.
3. Run migrations with `php artisan migrate`.
4. Install JavaScript dependencies with `npm install`.
5. Start Vite with `npm run dev`; serve Laravel using `php artisan serve` or the project's combined `composer run dev`.
6. Run the test suite with `php artisan test`.
7. Create production assets with `npm run build`.

Use deployment-specific values for secrets and never commit `.env` credentials.

## Frontend and views

Blade templates are under `resources/views/`. Tailwind CSS is imported from `resources/css/app.css`; Vite bundles CSS and JavaScript through `vite.config.js`. Shared workspace navigation/footer are under `resources/views/layout/`; administrator shell partials are under `resources/views/admin/layouts/`.

Information pages render the Markdown documents in `docs/` using Laravel's Markdown renderer. Keep Markdown files reviewed and trusted because their rendered output is included in the page.

## PDF generation

`invoiceGeneratorController` resolves the account's configured invoice template from `config/invoice.php` and renders a Blade PDF template using DomPDF. Register any new template in configuration, implement its view, and test both standard and jewellery paths where applicable.

## Operational and privacy notes

The deployment operator is responsible for production HTTPS, secure cookie configuration, secret management, database and backup security, log retention, dependency updates, mail-provider review, and legally appropriate privacy disclosures. The privacy notice intentionally does not assert a universal data region, retention schedule, or list of third-party processors.

## Tests

Feature tests are under `tests/Feature/`; unit tests are under `tests/Unit/`. Run focused tests while developing, then `php artisan test` before release. Also compile Blade views and build Vite assets when changing views or frontend styles.
