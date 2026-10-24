# Privacy Policy

**Effective date: 18 October 2026**

This document describes the information handled by the Vyapar billing application. It is an operational privacy notice, not legal advice. The business or organization that deploys and operates a Vyapar instance is responsible for reviewing this notice, identifying its legal obligations, and publishing an appropriate policy for its customers and users.

## Information handled

Depending on the features in use, the application can store:

- Account information such as a user's name, email address, phone number, account status, and password hash.
- Business profile information such as business name, address, GSTIN, and invoice template preference.
- Business records entered by users, including customer and supplier contact details, product and category information, invoices, invoice items, tax, payment status, and invoice notes.
- Jewellery voucher details for accounts enabled for that workflow, including piece counts, weights, touch, wastage, and gold balance calculations.
- Technical/session information needed to authenticate users and operate the application, such as authentication cookies and standard application logs.

Users should enter only information needed for their business operations. Avoid putting passwords, payment-card data, or unrelated sensitive personal information in notes or free-text fields.

## How information is used

The application uses stored information to authenticate accounts, provide account and business settings, manage customer/supplier/product records, calculate and display invoices and vouchers, generate invoice PDFs, and show workspace summaries. Administrators can manage account status, assigned invoice template, and jewellery feature access.

## Hosting, access, and sharing

Vyapar is self-hostable. The deploying organization controls the hosting environment, database, backups, mail delivery, logs, access policies, and retention. This codebase does not define one universal hosting provider, data-residency region, backup schedule, or retention period. The operator should document those choices and restrict access to authorized users.

The application may send account-related email through the mail transport configured by the operator. The operator should review the configured mail provider's privacy terms. The application does not need to sell business records; do not infer from this notice that a specific deployment has no third-party infrastructure or processors.

## Cookies and sessions

Authentication and workspace behavior use browser cookies. Session cookies should be treated as credentials: use a trusted device, sign out on shared devices, and avoid sharing session cookies. Operators should configure HTTPS and secure cookie settings for production.

## Retention and deletion

Business records are managed through the application's available account workflows and the deployment's database/backup lifecycle. Deleting a record in the application may not immediately remove copies in database backups or infrastructure logs. The operator must define retention, backup expiry, and data-subject request procedures appropriate to its deployment.

## Security responsibilities

Operators should keep dependencies and hosting systems updated, use unique credentials, enforce least-privilege access, protect environment secrets and backups, configure HTTPS, and monitor logs. No software can guarantee absolute security.

## Choices and requests

For questions about data in a Vyapar deployment, contact the organization that operates that deployment. The operator is responsible for verifying the requester and handling access, correction, export, or deletion requests under applicable law.

## Changes and contact

Operators should replace or supplement this notice with their organization name, contact details, jurisdiction, retention periods, subprocessors, and legally required disclosures before production use. Update the effective date when the deployed policy changes.
