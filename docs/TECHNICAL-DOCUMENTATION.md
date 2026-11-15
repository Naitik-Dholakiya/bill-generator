# Vyapar Technical Documentation

**Updated 25 October 2026**

## Overview

Vyapar is a Laravel 12 application for multi-workspace customer, supplier, product, category, invoice, and supplier-bill management. Server-rendered Blade views provide the authenticated user workspace and administrator console. The application uses PHP 8.2+, Laravel authentication middleware/custom auth controllers, a relational database, Tailwind CSS 4, and Vite. DomPDF generates invoice and supplier-bill PDFs.

## Implemented areas

- **Authentication:** Login/logout and protected user/admin route groups. Workspace-scoped controller queries use the authenticated user identifier.
- **Workspace dashboard:** KPI and sales summaries, invoice status and trend, recent invoices, product/customer/supplier snapshots, and onboarding progress.
- **Customers and suppliers:** Create, list, detail, edit, and delete flows with workspace ownership.
- **Products and categories:** Product maintenance, supplier/category associations, pricing, reorder-level display, and category creation from product entry.
- **Invoices:** Invoice and item create/edit/detail/delete flows; quantity, taxes, discounts, totals, payment history, and PDF generation. Invoice numbers are allocated transactionally. Customer, seller, and item descriptions are stored with each invoice; previous invoice versions are retained when an invoice is edited or deleted.
- **Supplier bills:** Supplier purchase bills with itemized costs/tax/discount, payment history, CRUD, PDF output, and inventory movements when bills are created, edited, or deleted.
- **Stock:** Standard workspaces track integer quantities; jewellery workspaces track net grams to three decimal places, with separate reorder thresholds. Manual adjustments and paginated movement history support each unit system. Invoice sales deduct quantity or net weight; edits and deletions post compensating movements. A workspace setting controls whether overselling warns, blocks the sale, or is allowed silently.
- **Payments:** Customer receipts and supplier payments are stored as dated, method/reference-bearing records. Payment status and known balances are recalculated from opening paid amounts and active payment records. Entries are not deleted; a void requires a reason and remains visible in the history.
- **Dashboard:** Financial queries exclude soft-deleted invoices, and customer/product counts exclude soft-deleted rows. Outstanding balances use recorded payments; legacy partial documents whose paid amount is unknown are identified for reconciliation rather than assigned an invented amount.
- **Jewellery:** Feature-gated voucher workflows with piece/weight/Touch/wastage and fine-weight calculations. Jewellery invoices carry forward the customer's latest saved closing-gold balance as the next opening balance; closing gold defaults to opening plus current fine weight and can be overridden. Invoice template keys are configured in `config/invoice.php`.
- **Settings:** User profile, business identity, password, and workspace overselling-policy updates.
- **Administration:** Workspace metrics and user account management, including status, jewellery feature access, and invoice-template assignment.

## Planned, not implemented

Payment records and inventory movement history are implemented. The application does not currently support payment allocation across multiple invoices/bills, refunds, or tax filing.

## Important routes

| Route name | Purpose |
| --- | --- |
| `dashboard` | Authenticated workspace overview |
| `customers.*`, `createCustomer*`, `viewCustomer`, `editCustomer*` | Customer workflows |
| `suppliers.*`, `createSupplier*`, `supplier.*` | Supplier workflows |
| `products.*`, `createProductPost`, `editProductPost` | Product workflows |
| `invoice.*` | Invoice and jewellery voucher workflows |
| `supplier-bills.*` | Supplier purchase bill list, CRUD, and PDF |
| `invoice.payments.*`, `supplier-bills.payments.*` | Record or void a payment against one invoice or supplier bill |
| `stock.*` | Current stock listing and quantity updates |
| `stock.movements` | Product-specific stock movement history |
| `settings`, `settings.update`, `changePassword` | Workspace profile/security settings |
| `admin.*` | Administrator workspace and account management |
| `privacy-policy`, `user-manual`, `documentation` | Public information pages rendered from `docs/` Markdown |

Route definitions are in `routes/web.php`. User-facing operations are protected by the `user.auth` middleware; administration uses `admin.auth`. ID-based customer, supplier, product, and invoice routes also use `resource.owner`, which verifies the record's `created_by` matches the authenticated workspace before allowing access.

## Data model

Core tables include `usermaster`, `customermaster`, `suppliermaster`, `categorymaster`, `productmaster`, `invoicemaster`, `invoice_items`, `invoice_revisions`, `invoice_payments`, `inventorymaster`, `inventory_movements`, `purchasemaster`, `purchase_items`, `supplier_bill_payments`, and `document_sequences`. Laravel cache and queue tables are also used by the framework. See `database/migrations/` for the authoritative schema and migration history.

Business rows are associated with a workspace/user identifier. Preserve these ownership filters in new queries and apply `resource.owner` to new ID-based routes so changing a URL ID cannot expose or modify another workspace's records. Unauthorized or missing IDs return 404.

Jewellery stock is stored separately from standard integer stock in `inventorymaster.weight_in_stock`; product gram reorder thresholds use `productmaster.reorder_weight_level`. Legacy piece-count stock is not converted because no reliable gram equivalent exists. Operators should verify and set the opening net-weight balance when enabling the feature. Weighted movements use nullable `weight_delta` and `weight_after` fields so existing standard movements remain intact.

The inventory migration records each current on-hand quantity as an opening movement and only tracks document movements from deployment onward; it does not replay old invoices and risk deducting historical sales twice. Existing invoices remain untracked for stock reversals. Existing pending and paid payment statuses become zero and full opening paid amounts respectively. A legacy partial status has no inferable amount: it stays marked partial with an unknown balance until the operator enters the previous amount when recording the next payment.

Pre-feature invoices are backfilled with the customer, seller, and product details currently present in their master records. Earlier changes to those master records cannot be reconstructed, so only invoices created or edited after this migration have point-in-time snapshots.

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
