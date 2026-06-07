# Implementation Plan — Complete Missing Features
**Project:** Facturation (Laravel 11 + Blade + TailwindCSS)
**Reference:** MISSING_FEATURES.md
**Date:** 2026-04-14

---

## Overview

The plan is split into **6 phases**, ordered by business criticality and dependency chain.
Each phase must be completed and tested before the next begins.

| Phase | Name | Focus | Priority |
|---|---|---|---|
| 1 | Foundation & POS Core | Barcode, Scanner, Thermal Print | Critical |
| 2 | Payments & Notifications | Stripe, SMS, WhatsApp | Critical |
| 3 | Reporting & Due Tracking | 30+ Reports, Customer/Supplier Dues | High |
| 4 | Inventory & Product Enhancements | IMEI, Warranty, Combos, Stock Count | Medium |
| 5 | HRM, Projects & Finance | Shifts, Holidays, Projects, Deposits | Medium |
| 6 | System & UX Polish | 2FA, Dark Mode, Wizard, Modules | Low |

---

## Phase 1 — Foundation & POS Core
**Goal:** Make the POS usable for real retail — barcode scanning, label printing, thermal receipts, and draft sales.

---

### Step 1.1 — Barcode Generation & Label Printing

**Files to create/edit:**
- `app/Services/BarcodeService.php` — generate barcode SVG/PNG per product
- `app/Http/Controllers/BarcodeController.php` — handle print requests
- `routes/api/barcodes.php` — new route file
- `resources/views/barcodes/` — label print view (A4 + label sheet layouts)

**Tasks:**
1. Install barcode library: `composer require picqer/php-barcode-generator`
2. Create `BarcodeService` — generate Code128/EAN13 barcode image from product SKU
3. Add `GET /api/products/{id}/barcode` endpoint returning barcode image
4. Add `POST /api/barcodes/print` endpoint — accepts array of product IDs + quantity + paper size
5. Create blade view `barcodes/print.blade.php` — renders a printable grid of labels
6. Add "Print Barcodes" button on Product list page
7. Add setting: Show/hide price on barcode label
8. Add setting: Barcode paper size (A4, 58mm label, 80mm label)

**Acceptance criteria:**
- [ ] Product has a barcode field (SKU auto-used if no barcode set)
- [ ] Barcode image renders correctly on label
- [ ] Print dialog opens a print-ready browser page
- [ ] Multiple products can be selected for batch printing

---

### Step 1.2 — POS Hardware Barcode Scanner (Keyboard Wedge)

**Files to edit:**
- `resources/views/sales/pos.blade.php`
- `resources/js/pos.js` (or equivalent Alpine/Livewire component)

**Tasks:**
1. Add a hidden `<input id="barcode-input">` that always captures keyboard focus in POS view
2. Detect fast keyboard input (scanner sends chars < 50ms apart) — distinguish from manual typing
3. On scan complete (Enter key or timeout): look up product by barcode via `GET /api/products?barcode=XXX`
4. If product found: add to cart or increment quantity if already in cart
5. If not found: show toast "Barcode not found"
6. Add toggle in POS settings: "Auto-increment quantity on duplicate scan" (on/off)

**Acceptance criteria:**
- [ ] Scanning a barcode adds product to cart automatically
- [ ] Scanning the same barcode twice increments quantity
- [ ] Manual typing in search box still works normally

---

### Step 1.3 — POS Camera Barcode Scanner

**Files to create/edit:**
- `resources/views/sales/pos.blade.php` — add camera modal
- `resources/js/camera-scanner.js` — new file

**Tasks:**
1. Add "Camera Scan" button in POS toolbar
2. Create modal with `<video>` element to stream webcam
3. Integrate `@zxing/library` (npm): `npm install @zxing/library`
4. On barcode detected by camera: close modal, add product to cart (same as Step 1.2 logic)
5. Handle browser permission denial gracefully

**Acceptance criteria:**
- [ ] Camera icon opens scanner modal
- [ ] Camera successfully reads QR/barcode from product
- [ ] Works in Chrome, Firefox, Edge

---

### Step 1.4 — POS Draft / Hold Sales

**Files to create/edit:**
- `database/migrations/xxxx_create_sale_drafts_table.php`
- `app/Models/SaleDraft.php`
- `app/Http/Controllers/SaleDraftController.php`
- `routes/api/sales.php` — add draft endpoints
- `resources/views/sales/pos.blade.php` — Hold + Resume buttons

**Tasks:**
1. Create migration: `sale_drafts` table (id, tenant_id, user_id, warehouse_id, customer_id, items JSON, total, created_at)
2. Create `SaleDraft` model
3. Add `POST /api/sales/drafts` — save current cart as draft
4. Add `GET /api/sales/drafts` — list all open drafts
5. Add `DELETE /api/sales/drafts/{id}` — discard draft
6. Add "Hold" button in POS — saves cart as draft, clears current cart
7. Add "Resume" panel in POS — shows list of held sales, click to restore cart
8. Show count of held sales as a badge on Resume button

**Acceptance criteria:**
- [ ] Current cart can be held/saved
- [ ] Held sale can be resumed (restores all items, customer, discounts)
- [ ] Multiple drafts can exist simultaneously
- [ ] Discarding a draft removes it permanently

---

### Step 1.5 — Thermal Printer & POS Receipt

**Files to create/edit:**
- `app/Http/Controllers/ReceiptController.php`
- `resources/views/receipts/thermal_58mm.blade.php`
- `resources/views/receipts/thermal_80mm.blade.php`
- `resources/views/receipts/a4.blade.php`
- `app/Models/Setting.php` — add POS receipt settings

**Tasks:**
1. Add POS settings to `settings` table:
   - `pos_receipt_size` (a4 | 58mm | 80mm)
   - `pos_auto_print` (boolean)
   - `pos_show_logo` (boolean)
   - `pos_show_warehouse` (boolean)
   - `pos_default_customer_id` (nullable FK)
   - `pos_default_warehouse_id` (nullable FK)
2. Create receipt blade views with correct CSS `@media print` widths
3. Add `GET /api/sales/{id}/receipt` — returns receipt HTML
4. After sale saved: if `pos_auto_print = true`, auto-open print dialog via `window.print()`
5. Add POS Settings page at `/settings/pos`

**Acceptance criteria:**
- [ ] Receipt prints correctly at 58mm and 80mm widths
- [ ] Auto-print can be toggled in settings
- [ ] Logo and warehouse name appear/hide based on settings
- [ ] Manual "Print" button always available on completed sale

---

### Phase 1 Checklist
- [ ] Step 1.1 — Barcode generation & label printing
- [ ] Step 1.2 — Hardware barcode scanner
- [ ] Step 1.3 — Camera barcode scanner
- [ ] Step 1.4 — Draft / hold sales
- [ ] Step 1.5 — Thermal receipt printing & POS settings

---

## Phase 2 — Payments & Notifications
**Goal:** Enable online payment collection and automated customer communication.

---

### Step 2.1 — Stripe Payment Gateway

**Files to create/edit:**
- `composer.json` — add `stripe/stripe-php`
- `app/Services/StripeService.php`
- `app/Http/Controllers/PaymentGatewayController.php`
- `database/migrations/xxxx_add_stripe_fields_to_customers.php`
- `database/migrations/xxxx_create_card_payments_table.php`
- `config/services.php` — add Stripe keys
- `resources/views/invoices/pay.blade.php` — payment page
- `routes/api/payments.php` — add Stripe endpoints

**Tasks:**
1. Install: `composer require stripe/stripe-php`
2. Add `STRIPE_KEY` and `STRIPE_SECRET` to `.env.example`
3. Create `StripeService`:
   - `createCustomer(Customer $customer)` — creates Stripe customer
   - `attachPaymentMethod(string $stripeCustomerId, string $paymentMethodId)` — saves card
   - `chargePaymentMethod(...)` — creates PaymentIntent
   - `listSavedCards(string $stripeCustomerId)` — fetches saved cards
4. Migration: add `stripe_customer_id` to `customers` table
5. Migration: create `card_payments` table (id, sale_id, invoice_id, stripe_payment_intent_id, amount, status, card_last4, card_brand)
6. Add endpoints:
   - `POST /api/payments/stripe/intent` — create PaymentIntent
   - `POST /api/payments/stripe/confirm` — confirm payment
   - `POST /api/payments/stripe/save-card` — save card to customer
   - `GET /api/customers/{id}/cards` — list saved cards
   - `DELETE /api/customers/{id}/cards/{cardId}` — remove saved card
7. Create invoice payment page with Stripe.js Elements form
8. Add "Pay with Card" button on Invoice detail view

**Acceptance criteria:**
- [ ] Customer can pay an invoice with a credit card
- [ ] Card can be saved for future payments
- [ ] Saved card shown as option on next payment
- [ ] Payment intent confirmed, sale/invoice marked as paid
- [ ] Failed payments show clear error message

---

### Step 2.2 — Multi-Payment in Single Transaction

**Files to edit:**
- `app/Http/Controllers/SaleController.php`
- `app/Services/SaleService.php`
- `database/migrations/xxxx_create_sale_payment_splits_table.php`
- `resources/views/sales/create.blade.php` + `pos.blade.php`

**Tasks:**
1. Create `sale_payment_splits` table: (id, sale_id, payment_method_id, amount)
2. Update `SaleService::createSale()` to accept array of payment splits
3. Update sale creation form: add "Split Payment" toggle, show payment method rows with amount inputs
4. Validate that split amounts sum to total due
5. Update sale payment report to show splits per sale

**Acceptance criteria:**
- [ ] Can split a sale across 2+ payment methods
- [ ] Each split stored separately
- [ ] Total of splits must equal sale total (validated)

---

### Step 2.3 — SMS Gateway Integration

**Files to create/edit:**
- `app/Services/SMS/SmsService.php` — interface/facade
- `app/Services/SMS/TwilioDriver.php`
- `app/Services/SMS/NexmoDriver.php`
- `app/Services/SMS/InfobipDriver.php`
- `app/Services/SMS/TermiiDriver.php`
- `database/migrations/xxxx_create_sms_settings_table.php`
- `database/migrations/xxxx_create_sms_logs_table.php`
- `app/Models/SmsLog.php`
- `resources/views/settings/sms.blade.php`
- `resources/views/sms-logs/index.blade.php`

**Tasks:**
1. Install: `composer require twilio/sdk vonage/client-core guzzlehttp/guzzle`
2. Design `SmsService` with driver pattern — active driver selected by setting `sms_gateway`
3. Implement each driver (`send(string $to, string $message): bool`)
4. Create `sms_settings` table: (gateway, twilio_sid, twilio_token, twilio_from, nexmo_key, nexmo_secret, nexmo_from, infobip_key, infobip_from, termii_key, termii_from)
5. Create `sms_logs` table: (id, to, message, gateway, status, error, sent_at)
6. Create SMS Settings page at `/settings/sms` with tabs per gateway + test button
7. Add SMS notification triggers in `SaleService`, `PurchaseService`, `QuotationService`, `PaymentService`
8. Create SMS log report page at `/reports/sms`

**Acceptance criteria:**
- [ ] Can configure and test at least one SMS gateway
- [ ] SMS sent automatically on sale/purchase/quotation creation (if enabled)
- [ ] SMS log shows delivery status
- [ ] Failed SMS logged with error message

---

### Step 2.4 — SMS Templates

**Files to edit:**
- `database/migrations/xxxx_create_sms_templates_table.php`
- `app/Models/SmsTemplate.php`
- `resources/views/settings/sms-templates.blade.php`

**Tasks:**
1. Create `sms_templates` table: (id, event, body, variables_hint, is_active)
2. Seed default templates for: sale_created, purchase_created, quotation_created, payment_received, sale_return, purchase_return
3. Template supports variables: `{customer_name}`, `{total}`, `{reference}`, `{date}`
4. Create SMS Templates management page (list + edit)
5. `SmsService::sendForEvent(string $event, array $data)` — renders template + sends

**Acceptance criteria:**
- [ ] Each event has a default SMS template
- [ ] Admin can edit template text
- [ ] Variables are replaced with real values when SMS is sent

---

### Step 2.5 — WhatsApp Notifications

**Files to create/edit:**
- `app/Services/WhatsAppService.php`
- `resources/views/settings/whatsapp.blade.php`

**Tasks:**
1. Implement WhatsApp via WhatsApp Business API (Meta) or Twilio WhatsApp channel
2. Add `whatsapp_enabled`, `whatsapp_token`, `whatsapp_phone_id` to settings
3. Create `WhatsAppService::send(string $to, string $message): bool`
4. Add WhatsApp toggle per notification event (sale, purchase, quotation)
5. Add WhatsApp Settings page at `/settings/whatsapp`
6. Log WhatsApp messages in `sms_logs` with `gateway = whatsapp`

**Acceptance criteria:**
- [ ] WhatsApp messages sent on configured events
- [ ] Logged in SMS log table

---

### Step 2.6 — Email Delivery Tracking & Instant Send

**Files to edit:**
- `app/Services/EmailService.php` (or create if not exists)
- `database/migrations/xxxx_create_email_logs_table.php`
- `resources/views/email-logs/index.blade.php`

**Tasks:**
1. Create `email_logs` table: (id, to, subject, event, status, error, sent_at)
2. Wrap all outgoing mail in try/catch, log result to `email_logs`
3. Add "Send Email Now" button on Sale/Purchase/Invoice detail pages (instant send)
4. Create Email Log report page at `/reports/email-logs`

**Acceptance criteria:**
- [ ] All outgoing emails logged
- [ ] Failed emails show error reason
- [ ] Manual email send works from detail pages

---

### Phase 2 Checklist
- [ ] Step 2.1 — Stripe payment gateway
- [ ] Step 2.2 — Multi-payment split per transaction
- [ ] Step 2.3 — SMS gateway integration (4 providers)
- [ ] Step 2.4 — SMS templates per event
- [ ] Step 2.5 — WhatsApp notifications
- [ ] Step 2.6 — Email delivery tracking & instant send

---

## Phase 3 — Reporting & Due Tracking
**Goal:** Full reporting suite (30+ reports) + due tracking for customers and suppliers.

---

### Step 3.1 — Customer Due Tracking

**Files to edit:**
- `app/Models/Customer.php` — add `getDueAttribute()`
- `app/Http/Controllers/CustomerController.php`
- `app/Services/CustomerService.php`
- `resources/views/customers/index.blade.php` — add Due column
- `resources/views/customers/due.blade.php` — new page

**Tasks:**
1. Add `due` computed attribute on `Customer`: sum of unpaid sale amounts
2. Display Due column on customer list with color coding (green = paid, red = has due)
3. Create `/customers/due` page listing all customers with outstanding balance
4. Add "Pay All Due" button per customer — opens modal to record bulk payment
5. `POST /api/customers/{id}/pay-due` — create payment covering all open sales
6. Add due amount to customer detail page

**Acceptance criteria:**
- [ ] Due balance calculated correctly per customer
- [ ] Due report lists all customers with balance > 0
- [ ] Bulk payment clears all outstanding invoices at once

---

### Step 3.2 — Supplier Due Tracking

**Tasks:** Mirror of Step 3.1 for suppliers/purchases.

1. Add `due` computed attribute on `Supplier`
2. Display Due column on supplier list
3. Create `/suppliers/due` page
4. Add "Pay All Due" button per supplier
5. `POST /api/suppliers/{id}/pay-due`

**Acceptance criteria:**
- [ ] Due balance shown per supplier
- [ ] Bulk payment to supplier works

---

### Step 3.3 — Sales Reports (Extended)

**Files to edit/create:**
- `app/Http/Controllers/ReportController.php` — add new report methods
- `app/Services/ReportService.php` — add new report queries
- New blade views under `resources/views/reports/`

**Reports to add:**
| Report | Route | Filter |
|---|---|---|
| Sales by Category | `/reports/sales-by-category` | Date range, Warehouse |
| Sales by Brand | `/reports/sales-by-brand` | Date range, Warehouse |
| Sales by Warehouse | `/reports/sales-by-warehouse` | Date range |
| Sales by Payment Method | `/reports/sales-by-payment-method` | Date range |
| Sales by User | `/reports/sales-by-user` | Date range, User |
| Top Selling Products | `/reports/top-products` | Date range, Limit (top 10/20/50) |
| Top Customers | `/reports/top-customers` | Date range, Limit |

**Tasks:**
1. Add query methods in `ReportService` for each report above
2. Add controller methods routing to each
3. Create blade view for each — table + date filter + PDF/CSV export button
4. Add PDF export using existing PDF library (or install `barryvdh/laravel-dompdf`)
5. Add CSV export for each report (`Response::streamDownload`)

**Acceptance criteria:**
- [ ] Each report loads with correct totals
- [ ] Date range filter works
- [ ] PDF export generates correct document
- [ ] CSV export is importable in Excel

---

### Step 3.4 — Purchase Reports (Extended)

**Reports to add:**
| Report | Route |
|---|---|
| Purchases by Warehouse | `/reports/purchases-by-warehouse` |
| Purchases by User | `/reports/purchases-by-user` |
| Product Purchases Detail | `/reports/product-purchases` |

---

### Step 3.5 — Customer Reports

**Reports to add:**
| Report | Route |
|---|---|
| Customer Summary | `/reports/customers` |
| Customer Detail | `/reports/customers/{id}` |
| Customer Due Report | (done in Step 3.1) |

**Customer detail report shows:**
- All sales, total spent
- All payments made
- All quotations
- All returns
- Outstanding due

---

### Step 3.6 — Supplier Reports

**Reports to add:**
| Report | Route |
|---|---|
| Supplier Summary | `/reports/suppliers` |
| Supplier Detail | `/reports/suppliers/{id}` |
| Supplier Due Report | (done in Step 3.2) |

---

### Step 3.7 — User Reports

**Reports to add:**
| Report | Route |
|---|---|
| Sales by User | (done in Step 3.3) |
| Purchases by User | (done in Step 3.4) |
| Quotations by User | `/reports/quotations-by-user` |
| Returns by User | `/reports/returns-by-user` |
| Transfers by User | `/reports/transfers-by-user` |
| Adjustments by User | `/reports/adjustments-by-user` |

---

### Step 3.8 — Warehouse Reports

**Reports to add:**
| Report | Route |
|---|---|
| Sales by Warehouse | (done in Step 3.3) |
| Purchases by Warehouse | (done in Step 3.4) |
| Expenses by Warehouse | `/reports/expenses-by-warehouse` |
| Stock Count by Warehouse | `/reports/stock-count-by-warehouse` |
| Quotations by Warehouse | `/reports/quotations-by-warehouse` |

---

### Step 3.9 — Financial Reports

**Reports to add:**
| Report | Route |
|---|---|
| All Payment Transactions | `/reports/payment-transactions` |
| Deposits Report | `/reports/deposits` |
| Money Transfers Report | `/reports/money-transfers` |
| Inventory Valuation Summary | `/reports/inventory-valuation` |

---

### Step 3.10 — Report Enhancements

**Tasks (apply to ALL report pages):**
1. Add PDF export button using `barryvdh/laravel-dompdf`
2. Add CSV/Excel export button using `maatwebsite/excel` or simple `fputcsv`
3. Add summary/totals row at bottom of all tables
4. Add warehouse filter where applicable
5. Add date range picker to all reports (already in some)

---

### Phase 3 Checklist
- [ ] Step 3.1 — Customer due tracking + bulk payment
- [ ] Step 3.2 — Supplier due tracking + bulk payment
- [ ] Step 3.3 — Extended sales reports (7 new)
- [ ] Step 3.4 — Extended purchase reports (3 new)
- [ ] Step 3.5 — Customer summary + detail reports
- [ ] Step 3.6 — Supplier summary + detail reports
- [ ] Step 3.7 — User-level reports (6 new)
- [ ] Step 3.8 — Warehouse-level reports (5 new)
- [ ] Step 3.9 — Financial reports (4 new)
- [ ] Step 3.10 — PDF + CSV export on all reports

---

## Phase 4 — Inventory & Product Enhancements
**Goal:** Full product lifecycle — IMEI tracking, warranties, combos, imports, and complete stock count workflow.

---

### Step 4.1 — IMEI / Serial Number Tracking

**Files to create/edit:**
- `database/migrations/xxxx_create_product_serials_table.php`
- `app/Models/ProductSerial.php`
- `resources/views/products/serials.blade.php`

**Tasks:**
1. Create `product_serials` table: (id, product_id, warehouse_id, serial_number, status: available/sold/returned, sale_item_id nullable, purchase_item_id nullable)
2. Add "Has Serial Numbers" toggle on product edit form
3. When receiving a purchase: prompt to enter serial numbers per unit received
4. When creating a sale: prompt to select/enter serial numbers per unit sold
5. Serial numbers auto-marked as `sold` on sale, `available` on return
6. Create `/products/{id}/serials` page — list all serials with status

**Acceptance criteria:**
- [ ] Serials captured during purchase receiving
- [ ] Serials assigned during sale creation
- [ ] Serial status tracks correctly through lifecycle

---

### Step 4.2 — Warranty Management

**Files to create/edit:**
- `database/migrations/xxxx_add_warranty_to_products.php`
- `database/migrations/xxxx_create_warranties_table.php`
- `app/Models/Warranty.php`
- `resources/views/warranties/index.blade.php`

**Tasks:**
1. Add `warranty_duration` (int, days), `warranty_type` (string: parts/full/none) to `products` table
2. Create `warranties` table: (id, sale_item_id, serial_number, product_id, customer_id, start_date, end_date, status)
3. Auto-create warranty record on sale if product has warranty enabled
4. Create `/warranties` list page — filter by status (active/expired), customer, product
5. Warranty expiry indicator (color badge: green/orange/red)

**Acceptance criteria:**
- [ ] Warranty auto-created on sale of warranted product
- [ ] Warranty end date calculated from sale date + duration
- [ ] Warranty list searchable by serial, customer, product

---

### Step 4.3 — Combo / Bundle Products

**Files to create/edit:**
- `database/migrations/xxxx_create_combo_products_table.php`
- `app/Models/ComboProduct.php`
- `app/Models/ComboProductItem.php`
- `app/Services/ComboService.php`
- `resources/views/products/combos/` folder

**Tasks:**
1. Create `combo_products` table: (id, name, sku, price, image, is_active)
2. Create `combo_product_items` table: (combo_id, product_id, quantity)
3. Create Combo Products CRUD at `/products/combos`
4. When a combo is added to a sale: deduct stock for each component product
5. Display combo in POS product grid with a "Bundle" badge
6. Stock alert: combo shows as out-of-stock if any component is out

**Acceptance criteria:**
- [ ] Combo product created with 2+ component products
- [ ] Selling a combo deducts all component stocks
- [ ] Combo is out-of-stock if any component has zero stock

---

### Step 4.4 — CSV/Excel Imports (Customers, Suppliers, Purchases)

**Files to edit:**
- `app/Http/Controllers/CustomerController.php`
- `app/Http/Controllers/SupplierController.php`
- `app/Http/Controllers/PurchaseController.php`
- `app/Imports/CustomerImport.php` (new)
- `app/Imports/SupplierImport.php` (new)
- `app/Imports/PurchaseImport.php` (new)

**Tasks:**
1. Install: `composer require maatwebsite/excel` (if not present)
2. Create import classes using `Maatwebsite\Excel\Concerns\ToModel`
3. Add import button + file upload form to:
   - Customer list page → `POST /api/customers/import`
   - Supplier list page → `POST /api/suppliers/import`
   - Purchase list page → `POST /api/purchases/import`
4. Provide downloadable sample CSV template for each
5. Validate rows, report rows skipped with error reason
6. Add export button to Customer and Supplier list pages

**Acceptance criteria:**
- [ ] Sample CSV template downloadable
- [ ] Valid rows imported correctly
- [ ] Invalid rows skipped with per-row error report
- [ ] Duplicate detection (by email/phone) during import

---

### Step 4.5 — Opening Stock Import

**Files to create:**
- `app/Imports/OpeningStockImport.php`
- `resources/views/stock/opening-import.blade.php`

**Tasks:**
1. Create dedicated "Opening Stock" import page at `/stock/opening-import`
2. CSV format: `product_sku, warehouse_id, quantity, cost_price`
3. Process each row: create `StockAdjustment` record (reason: "Opening Stock")
4. Provide downloadable sample template

**Acceptance criteria:**
- [ ] Opening stock import sets initial quantities per warehouse
- [ ] Each import row creates a traceable stock adjustment

---

### Step 4.6 — Complete Stock Count Workflow

**Files to edit:**
- `app/Models/InventoryCount.php`
- `app/Http/Controllers/StockController.php`
- `app/Services/StockService.php`
- `resources/views/stock/inventory-count/` folder

**Tasks:**
1. Update `inventory_counts` table: add `status` (draft/counting/completed), `warehouse_id`, `counted_by`
2. Implement workflow states:
   - **Draft** → admin creates count, selects warehouse + product range
   - **Counting** → team enters physical quantities
   - **Completed** → system compares expected vs actual, shows discrepancy report
   - **Adjusted** → admin approves, auto-creates stock adjustment to reconcile
3. Create count list page, count detail page (enter quantities), discrepancy page
4. Add stock count report under `/reports/stock-count-by-warehouse`

**Acceptance criteria:**
- [ ] Count workflow moves through all states
- [ ] Discrepancy calculated correctly (expected - counted)
- [ ] Approving count creates a stock adjustment to fix inventory

---

### Phase 4 Checklist
- [ ] Step 4.1 — IMEI / serial number tracking
- [ ] Step 4.2 — Warranty management
- [ ] Step 4.3 — Combo / bundle products
- [ ] Step 4.4 — CSV import for customers, suppliers, purchases
- [ ] Step 4.5 — Opening stock import
- [ ] Step 4.6 — Complete stock count workflow

---

## Phase 5 — HRM, Projects, Finance & Delivery
**Goal:** Complete HRM module, add project management, deposits/transfers, and shipment UI.

---

### Step 5.1 — Company Management

**Files to create:**
- `database/migrations/xxxx_create_companies_table.php`
- `database/migrations/xxxx_add_company_id_to_employees.php`
- `app/Models/Company.php`
- `app/Http/Controllers/CompanyController.php`
- `resources/views/employees/companies/` folder

**Tasks:**
1. Create `companies` table: (id, name, address, phone, email, logo)
2. Add `company_id` FK to `employees` table
3. Create Company CRUD at `/employees/companies`
4. Add Company dropdown to employee create/edit form
5. Filter employee list by company

---

### Step 5.2 — Office Shifts

**Files to create:**
- `database/migrations/xxxx_create_office_shifts_table.php`
- `app/Models/OfficeShift.php`
- `resources/views/employees/shifts/` folder

**Tasks:**
1. Create `office_shifts` table: (id, name, start_time, end_time, late_after_minutes)
2. Add `shift_id` FK to `employees` table
3. Create Shifts CRUD at `/employees/shifts`
4. Add Shift dropdown to employee create/edit form
5. Use shift times in attendance validation (mark late if clock-in > shift start + grace)

---

### Step 5.3 — Holiday Calendar

**Files to create:**
- `database/migrations/xxxx_create_holidays_table.php`
- `app/Models/Holiday.php`
- `resources/views/employees/holidays/` folder

**Tasks:**
1. Create `holidays` table: (id, name, date, is_recurring)
2. Create Holiday CRUD at `/employees/holidays`
3. Attendance: if employee clocks in on a holiday, mark as "Holiday" status
4. Leave calculation: exclude public holidays from leave day count

---

### Step 5.4 — Employee Profile Enhancements

**Files to edit:**
- `database/migrations/xxxx_add_profile_fields_to_employees.php`
- `app/Models/Employee.php`
- `resources/views/employees/show.blade.php`

**Fields to add:**
- `photo` (file upload)
- `facebook`, `twitter`, `linkedin` (social links)
- `bank_name`, `bank_account_number`, `bank_routing_number`
- Work experience (separate `employee_experiences` table: company, title, from_date, to_date)

---

### Step 5.5 — Deposits & Money Transfers

**Files to create:**
- `database/migrations/xxxx_create_deposits_table.php`
- `database/migrations/xxxx_create_deposit_categories_table.php`
- `database/migrations/xxxx_create_money_transfers_table.php`
- Models + Controllers + Views for each

**Tasks:**
1. Create `deposit_categories` table: (id, name, description)
2. Create `deposits` table: (id, amount, category_id, account_id, reference, date, notes)
3. Create `money_transfers` table: (id, from_account_id, to_account_id, amount, fee, date, reference)
4. Create CRUD for Deposit Categories at `/accounting/deposit-categories`
5. Create CRUD for Deposits at `/accounting/deposits`
6. Create CRUD for Money Transfers at `/accounting/money-transfers`
7. Money transfer auto-creates two journal entries (debit from_account, credit to_account)

**Acceptance criteria:**
- [ ] Deposits recorded and visible in account balance
- [ ] Money transfer reduces from_account and increases to_account
- [ ] All appear in Deposits and Transfers reports (Phase 3.9)

---

### Step 5.6 — FIFO / Average Cost Profit Calculation

**Files to edit:**
- `app/Services/ReportService.php`
- `resources/views/reports/profit-loss.blade.php`

**Tasks:**
1. Add `profit_calculation_method` setting (fifo | average | cogs)
2. Implement FIFO costing in `ReportService::getCostOfGoodsSold()`:
   - Track purchase cost per batch per product
   - Match sold units to oldest purchase batches first
3. Implement Average Cost: `total_purchase_cost / total_units` at time of sale
4. Add method selector dropdown on P&L report page
5. Show method used in report header

---

### Step 5.7 — Project & Task Management

**Files to create:**
- Full module: migrations, models, controllers, views for projects, tasks, discussions, documents

**Database tables:**
- `projects` (id, name, description, status, start_date, end_date, client_id nullable, budget)
- `project_employees` (project_id, employee_id)
- `tasks` (id, project_id, title, description, status, priority, assigned_to, due_date)
- `task_employees` (task_id, employee_id)
- `discussions` (id, type: project/task, reference_id, user_id, body)
- `project_documents` (id, project_id, name, file_path)
- `task_documents` (id, task_id, name, file_path)

**Views to create:**
- `/projects` — project list
- `/projects/{id}` — project detail (tasks, discussions, documents, issues)
- `/projects/{id}/tasks` — Kanban board (drag-and-drop columns)
- `/tasks/{id}` — task detail page

**Tasks:**
1. Create all migrations, models, controllers
2. Project CRUD
3. Task CRUD with Kanban drag-and-drop (use Alpine.js or Sortable.js)
4. Discussions (threaded comments on projects and tasks)
5. Document file uploads (project + task)
6. Employee assignments (many-to-many)
7. Task status: To Do / In Progress / In Review / Done

---

### Step 5.8 — Delivery / Shipment Workflow

**Files to edit/create:**
- `app/Http/Controllers/ShipmentController.php`
- `resources/views/shipments/` folder
- Update `sale_create.blade.php` to include shipment fields

**Tasks:**
1. `shipments` table already has a model — verify fields, add if missing: `tracking_number`, `carrier`, `status` (pending/shipped/delivered/cancelled)
2. Create Shipment CRUD at `/shipments`
3. Add "Create Shipment" button on confirmed sale detail page
4. Shipment detail page — update status, enter tracking number
5. Link shipment to sale — show shipment status on sale detail page

**Acceptance criteria:**
- [ ] Shipment created from a confirmed sale
- [ ] Status can be updated (Pending → Shipped → Delivered)
- [ ] Tracking number stored and displayed

---

### Step 5.9 — Recurring Invoices

**Files to create:**
- `database/migrations/xxxx_create_recurring_invoices_table.php`
- `app/Models/RecurringInvoice.php`
- `app/Console/Commands/ProcessRecurringInvoices.php`
- `resources/views/invoices/recurring/` folder

**Tasks:**
1. Create `recurring_invoices` table: (id, customer_id, items JSON, total, frequency: daily/weekly/monthly/yearly, next_run_at, last_run_at, status: active/paused/cancelled)
2. Create Recurring Invoice CRUD at `/invoices/recurring`
3. Create Artisan command `invoices:process-recurring`
4. Schedule command in `Console/Kernel.php` (run daily)
5. Command generates new Invoice from template, emails customer if enabled

**Acceptance criteria:**
- [ ] Recurring invoice created with frequency setting
- [ ] Cron generates invoice on scheduled date
- [ ] Generated invoice appears in invoice list with reference to parent recurring

---

### Phase 5 Checklist
- [ ] Step 5.1 — Company management
- [ ] Step 5.2 — Office shifts
- [ ] Step 5.3 — Holiday calendar
- [ ] Step 5.4 — Employee profile enhancements
- [ ] Step 5.5 — Deposits & money transfers
- [ ] Step 5.6 — FIFO / Average Cost profit calculation
- [ ] Step 5.7 — Project & task management (Kanban)
- [ ] Step 5.8 — Delivery / shipment workflow
- [ ] Step 5.9 — Recurring invoices

---

## Phase 6 — System & UX Polish
**Goal:** Security hardening, developer tools, and UX improvements.

---

### Step 6.1 — Two-Factor Authentication (2FA)

**Tasks:**
1. Install: `composer require pragmarx/google2fa-laravel`
2. Add `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_enabled_at` to `users` table
3. Add 2FA setup page at `/settings/security` — show QR code + backup codes
4. Add 2FA verification step after login (redirect to `/auth/two-factor`)
5. Add per-role setting: "Require 2FA" — forces users of that role to set up 2FA

---

### Step 6.2 — Dark Mode

**Tasks:**
1. Add `dark` class toggle to `<html>` element using Alpine.js
2. Configure TailwindCSS dark mode: `darkMode: 'class'` in `tailwind.config.js`
3. Audit all blade views — add `dark:` variants for backgrounds, text, borders
4. Save preference in `localStorage` and in user settings (`theme` column on `users`)
5. Add toggle button (sun/moon icon) in navigation bar

---

### Step 6.3 — Duplicate Prevention

**Tasks:**
1. **Products:** Add unique index on `sku` and `barcode` columns (migration)
2. **Customers:** Add unique constraint on `email` (when provided); warn on matching phone
3. **Suppliers:** Same as customers
4. Add real-time duplicate check on form fields using debounced AJAX (`GET /api/check-duplicate?type=sku&value=XXX`)
5. Show inline warning "A product with this SKU already exists" before form submission

---

### Step 6.4 — Error Log Viewer

**Tasks:**
1. Create `error_logs` table: (id, message, file, line, trace, url, user_id, created_at)
2. Register a global exception handler in `Handler.php` to log to DB (non-404 errors)
3. Create Error Logs page at `/system/error-logs` (super admin only)
4. Add clear button, filter by date range, show/hide stack trace

---

### Step 6.5 — Setup / Installation Wizard

**Tasks:**
1. Detect first-run via missing `.env` or `APP_INSTALLED=false` flag
2. Create multi-step wizard at `/install`:
   - Step 1: Server requirements check (PHP version, extensions)
   - Step 2: Database credentials (test connection)
   - Step 3: Run migrations + seeders
   - Step 4: Admin account creation
   - Step 5: Company info (name, currency, timezone)
   - Step 6: Done (redirect to login)
3. Set `APP_INSTALLED=true` in `.env` on wizard completion
4. Block `/install` route if `APP_INSTALLED=true`

---

### Step 6.6 — Module Enable/Disable

**Tasks:**
1. Add `modules` JSON column to `settings` table (or dedicated `modules` table)
2. Default: all modules enabled
3. Create Modules Management page at `/settings/modules` — toggle list
4. Middleware `CheckModuleEnabled` — returns 403 if module is disabled
5. Apply middleware to route groups (hrm, crm, accounting, projects, etc.)
6. Hide disabled module nav links automatically

---

### Step 6.7 — Dashboard Enhancements

**Tasks:**
1. Add Top Selling Products widget (top 5 by quantity, current month)
2. Add Top Customers widget (top 5 by revenue, current month)
3. Add Stock Alerts widget (count of products at/below alert threshold)
4. Add Pending Shipments widget (count of shipments in "pending" status)
5. Add Today's Sales counter card
6. Add interactive Purchase Trends chart (bar chart, last 6 months)
7. Make all dashboard widgets permission-gated (show only what user can access)

---

### Step 6.8 — Extended Language Support

**Tasks:**
1. Audit existing `lang/` directory — identify gaps in Arabic, Spanish, French, Portuguese, Bengali
2. Create complete translation files for top 5 most-requested languages
3. Add RTL layout support: detect RTL languages in `LocaleMiddleware`, add `dir="rtl"` to `<html>`
4. Add RTL-specific TailwindCSS utilities (`rtl:ml-2 → mr-2`)
5. Test all pages in RTL mode

---

### Phase 6 Checklist
- [ ] Step 6.1 — Two-factor authentication
- [ ] Step 6.2 — Dark mode
- [ ] Step 6.3 — Duplicate prevention (products, customers, suppliers)
- [ ] Step 6.4 — Error log viewer
- [ ] Step 6.5 — Setup / installation wizard
- [ ] Step 6.6 — Module enable/disable
- [ ] Step 6.7 — Dashboard enhancements
- [ ] Step 6.8 — Extended language support + RTL

---

## Full Phase Summary

| Phase | Steps | Key Deliverables |
|---|---|---|
| **Phase 1** | 1.1 → 1.5 | Barcode labels, POS scanner (hardware + camera), hold sales, thermal receipts |
| **Phase 2** | 2.1 → 2.6 | Stripe, multi-payment, SMS (4 gateways), WhatsApp, email logging |
| **Phase 3** | 3.1 → 3.10 | Customer/supplier dues, 30+ reports, PDF+CSV export on all reports |
| **Phase 4** | 4.1 → 4.6 | IMEI/serials, warranties, combos, CSV imports, opening stock, full stock count |
| **Phase 5** | 5.1 → 5.9 | Companies, shifts, holidays, deposits, projects/Kanban, shipments, recurring invoices |
| **Phase 6** | 6.1 → 6.8 | 2FA, dark mode, wizard, module toggle, dashboard widgets, RTL |

---

## Dependencies Between Steps

```
Phase 1 (no deps — start immediately)
Phase 2 (no deps — can run in parallel with Phase 1)
Phase 3 → needs: accounting module (done), basic reporting (done)
Phase 4 → 4.1 (serials) must complete before 4.2 (warranty)
Phase 4 → 4.3 (combo) independent
Phase 4 → 4.6 (stock count) independent
Phase 5 → 5.5 (deposits) needs accounting chart of accounts (done)
Phase 5 → 5.7 (projects) fully independent
Phase 5 → 5.9 (recurring) needs invoice module (done)
Phase 6 → no hard deps, best done last
```

---

## Recommended Team Split (if applicable)

| Developer | Phases |
|---|---|
| Dev A (Frontend/UI) | Phase 1 (POS), Phase 6 (Dark Mode, Dashboard) |
| Dev B (Backend/API) | Phase 2 (Stripe, SMS), Phase 3 (Reports) |
| Dev C (Full Stack) | Phase 4 (Products), Phase 5 (HRM, Projects) |

---

*Plan version 1.0 — 2026-04-14*
