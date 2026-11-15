# Vyapar User Manual

**For Vyapar billing workspaces · Updated 18 October 2026**

## 1. Sign in

Use the credentials provided by your workspace administrator. Keep your password private and sign out when using a shared device. If you cannot access the account, contact the person who manages your workspace.

## 2. Complete the getting-started steps

The dashboard includes a setup guide that tracks the first record in each area:

1. **Add a customer** from Customers. Enter the customer name and any available contact, GST, and address details.
2. **Add a supplier** from Suppliers. Add the supplier and contact details before assigning suppliers to products.
3. **Add products** from Products. Select the supplier and category, then set purchase and selling prices and any applicable product details.
4. **Create an invoice** from Invoices. Choose a customer, add one or more products, review quantities, rates, tax, discounts, and totals, then save.

The setup guide marks each step complete when it detects at least one corresponding record. It is a starting checklist, not a validation of every business setting.

## 3. Customers

The Customers area lists customer records and provides create, edit, and detail screens. Customer records can include a code, name, email, phone, GST number, billing address, and shipping address. Use the detail page to review a record. Update details when customer information changes.

## 4. Suppliers

The Suppliers area stores supplier and contact information, including supplier code, contact person, phone, email, GST number, and address. Products can be associated with a supplier.

## 5. Products and categories

Products include a product code and name, category, supplier, purchase price, selling price, reorder level, and active status. Standard workspaces use integer quantities; jewellery-enabled workspaces use net grams for stock and reorder thresholds. Jewellery workspaces can also store default Touch and wastage percentages for eligible products. Categories can be selected or created during product setup.

The Stock area shows the recorded quantity or net weight for each active product, highlights products at or below their matching reorder level, and lets you update the available balance. Jewellery quantities are recorded to three decimal places in grams. Existing piece-count balances are not converted automatically; verify and enter an opening net-weight balance after jewellery stock tracking is enabled. Use **View stock history** to review tracked supplier-bill receipts/reversals, invoice sales, and manual adjustments.

Open Settings and choose the workspace overselling policy: **Warn, but allow the sale**, **Block the sale**, or **Allow without warning**. The policy controls what happens when an invoice would take stock below zero. Jewellery invoice stock deductions use item net weights.

## 6. Invoices

Create an invoice by selecting a customer and date, adding product rows, and reviewing the calculated line totals and invoice summary. Check the customer, products, rates, tax, discounts, and final total before saving. Invoices can be reviewed, edited, and deleted from the invoice screens. Invoice details retain the customer, business, and product descriptions used when saved; the previous version is available in **Saved invoice history** after an edit.

Use the invoice detail/PDF actions to view or download the generated invoice. The invoice layout is determined by the account's configured template. Record customer receipts from the **Payments** section with the date, amount, method, and optional reference. The remaining balance and payment status update from recorded amounts. A payment cannot exceed the remaining balance. To correct an entry, void it with a reason; the void remains visible in the payment history.

## 7. Supplier bills

Create a supplier bill by selecting a supplier and date, adding purchased products, and entering quantities, unit costs, tax, and discounts. The bill total is calculated from the line items. Jewellery workspaces instead record pieces, gross/net weights, touch, wastage, fine weight, gold adjustments, and supplier gold balances, matching the jewellery invoice workflow; piece counts still update stock. Supplier bills can be searched, reviewed, edited, deleted, and downloaded as PDFs. For standard bills, record supplier payments from the **Payments** section with the date, amount, method, and optional reference. The remaining balance and payment status update from recorded amounts. Payments can be voided with a reason and remain in the history.

Saving a bill increases stock. Editing a bill adjusts stock by the difference in item quantities or net weights. Deleting a bill reverses its stock movement, but is blocked if that would make stock negative. Bills do not automatically change the purchase price on the product record.

## 8. Jewellery vouchers

Jewellery voucher entry is available only to workspaces whose administrator enabled jewellery mode. Select a party and date, add products, and enter the piece count and applicable weights, Touch, and wastage. Stock is tracked using net grams, while piece count remains a voucher detail. For a customer with an earlier jewellery invoice, the opening gold balance carries forward from that customer's latest saved closing balance. Current invoice fine weight is added to opening gold to suggest closing gold; review and edit the closing value when needed before saving. Confirm the generated voucher carefully; weight calculations depend on the values entered.

## 9. Dashboard

The dashboard summarizes available business and invoice records, including revenue, outstanding invoices, customer/product/supplier counts, invoice status, recent invoices, and sales summaries. A value of zero is different from missing data: some summaries depend on having invoices or invoice items. Legacy partial invoices may show that the previous amount paid needs reconciliation; no amount is assumed for those older records.

## 10. Settings and account security

Open Settings to update personal details, business name, address, GSTIN, overselling policy, and password. Invoice template and jewellery access may be managed by an administrator. Keep business profile and invoice details accurate before generating customer-facing documents.

## 11. Payment and stock history notes

The stock history starts with the available balance when movement tracking was enabled; older invoice sales are not replayed. Existing pending and paid payment statuses are migrated as zero and fully paid opening amounts. For an older partial invoice or bill, enter the amount previously paid when recording its next payment so the remaining balance can be calculated accurately. Refunds and payment allocation across multiple documents are not currently available.

## 12. Help and data questions

Use Documentation for technical and feature information and Privacy Policy for data-handling responsibilities. For access, correction, or deletion requests, contact the organization operating your Vyapar workspace.
