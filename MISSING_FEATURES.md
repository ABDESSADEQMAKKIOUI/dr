# Missing Features & Gaps — Facturation vs Stocky Ultimate

**Reference System:** Stocky Ultimate Inventory Management System with POS (v5.0)
**Target System:** Facturation (Laravel 11 + Blade/TailwindCSS)
**Date Generated:** 2026-04-14

---

## Summary

| Category | Facturation | Stocky Ultimate | Gap |
|---|---|---|---|
| POS System | Basic | Full-featured | Major |
| Payment Gateways | None | Stripe + Credit Cards | Critical |
| SMS/WhatsApp | None | 4 gateways + WhatsApp | Critical |
| Barcode System | None | Full (print + scan + camera) | Major |
| Project Management | None | Full PM + Kanban | Major |
| Deposits & Money Transfer | None | Full | Moderate |
| Reporting | Basic (4 reports) | 30+ reports | Major |
| E-commerce | None | Client store integration | Moderate |
| Warranty/IMEI Tracking | None | Full | Moderate |
| Combo Products | None | Yes | Moderate |
| HRM Enhancements | Partial | Full (shifts, holidays, companies) | Moderate |
| Bulk Operations | None | Yes | Moderate |
| Import (CSV/Excel) | Products only | Products, Customers, Suppliers, Purchases | Moderate |
| Recurring Invoices | None | Yes | Moderate |
| Two-Factor Auth | None | Yes | Low |
| Delivery/Shipment | Model only (no UI) | Full workflow | Moderate |
| Due Tracking | None | Full (customers + suppliers) | Moderate |
| Dark Mode | None | Yes | Low |
| Setup Wizard | None | Yes | Low |

---

## 1. Point of Sale (POS) — Major Gaps

Facturation has a `/sales/pos` view but it is missing the following POS-specific capabilities present in Stocky:

### 1.1 Barcode Scanner Integration
- **Missing:** Hardware barcode scanner support (keyboard-wedge input)
- **Missing:** Camera-based barcode scanner (web camera scanning via browser API)
- **Missing:** Manual barcode entry with auto lookup
- **Missing:** Automatic quantity increment when the same barcode is scanned multiple times

### 1.2 POS Receipt Printing
- **Missing:** Thermal printer support (ESC/POS protocol)
- **Missing:** Receipt size configuration (58mm, 80mm)
- **Missing:** Company logo on receipt
- **Missing:** Warehouse name display on receipt
- **Missing:** Auto-print invoice toggle after sale completion

### 1.3 POS Workflow
- **Missing:** Draft/Hold sales — save a transaction and continue it later
- **Missing:** POS-specific settings panel (separate from global settings)
- **Missing:** Default customer per POS terminal
- **Missing:** Default warehouse per POS terminal
- **Missing:** Items-per-page configuration in the POS product grid
- **Missing:** Today's total sales counter displayed in POS

### 1.4 Barcode Label Printing
- **Missing:** Barcode label generation per product
- **Missing:** Barcode paper size selection (A4, label sheets)
- **Missing:** Option to show/hide price on barcode labels
- **Missing:** Bulk barcode printing for multiple products

---

## 2. Payment Gateways — Critical Gap

Facturation currently has only internal payment method records. There is no online payment processing.

### 2.1 Stripe Integration
- **Missing:** Stripe payment gateway (PHP SDK + Stripe.js)
- **Missing:** Credit card payment form in sales/invoices
- **Missing:** Save customer credit cards (Stripe Customer object)
- **Missing:** Default saved card selection during checkout
- **Missing:** Stripe customer management (create/update/delete)
- **Missing:** Payment confirmation and webhook handling

### 2.2 Multi-Payment in Single Transaction
- **Missing:** Ability to split a single sale across multiple payment methods (e.g., 50% cash + 50% card)

---

## 3. SMS & WhatsApp Notifications — Critical Gap

Facturation has an email template system but zero SMS or WhatsApp capability.

### 3.1 SMS Gateways
- **Missing:** Twilio SMS gateway
- **Missing:** Nexmo / Vonage SMS gateway
- **Missing:** InfoBip SMS gateway
- **Missing:** Termii SMS gateway (for African/regional deployments)
- **Missing:** Default SMS gateway selection in settings
- **Missing:** Per-gateway API key configuration

### 3.2 SMS Notifications
- **Missing:** SMS on sale creation
- **Missing:** SMS on purchase creation
- **Missing:** SMS on quotation creation
- **Missing:** SMS on payment received
- **Missing:** SMS on sale return
- **Missing:** SMS on purchase return
- **Missing:** Custom SMS templates per event

### 3.3 SMS Reporting
- **Missing:** SMS delivery status tracking
- **Missing:** SMS delivery report page
- **Missing:** SMS error logging and error log report page

### 3.4 WhatsApp Integration
- **Missing:** WhatsApp notification on sales
- **Missing:** WhatsApp notification on purchases
- **Missing:** WhatsApp notification on quotations

### 3.5 Email Notification Gaps
- **Missing:** Instant email notification toggle during transaction creation (send immediately on save vs. send manually)
- **Missing:** Email delivery status tracking
- **Missing:** Email error logging

---

## 4. Barcode & Product Identification — Major Gap

### 4.1 IMEI / Serial Number Tracking
- **Missing:** IMEI number field per sale item
- **Missing:** Serial number field per sale item
- **Missing:** Warranty linked to IMEI/serial at sale time
- **Missing:** IMEI lookup / search

### 4.2 Warranty Management
- **Missing:** Warranty information per product (duration, type)
- **Missing:** Warranty recording per sale transaction
- **Missing:** Warranty tracking and reporting

### 4.3 Combo / Bundle Products
- **Missing:** Combined/bundle product creation (multiple products sold as one SKU)
- **Missing:** Auto-deduction of component stock when a combo is sold

### 4.4 Weight Scale Integration
- **Missing:** USB weight scale device integration for selling items by weight

---

## 5. Project & Task Management — Major Gap

This entire module is absent from Facturation.

### 5.1 Projects
- **Missing:** Project creation and management
- **Missing:** Project status tracking
- **Missing:** Project discussions / comment threads
- **Missing:** Project document attachments
- **Missing:** Project issues tracking
- **Missing:** Employee-to-project assignments

### 5.2 Tasks
- **Missing:** Task creation and management
- **Missing:** Kanban board (drag-and-drop task status)
- **Missing:** Task status management (To Do, In Progress, Done, etc.)
- **Missing:** Task assignments to employees
- **Missing:** Task discussions / comment threads
- **Missing:** Task document attachments
- **Missing:** Employee time tracking on tasks
- **Missing:** Employee-to-task assignments

---

## 6. Financial / Accounting Gaps — Moderate

Facturation has a full double-entry accounting module but is missing the following operational finance features:

### 6.1 Deposits Management
- **Missing:** Cash/fund deposit recording (money received outside of sales)
- **Missing:** Deposit categories
- **Missing:** Deposit reports

### 6.2 Money Transfer Between Accounts
- **Missing:** Internal fund transfer between company accounts (e.g., cash to bank)
- **Missing:** Transfer money tracking and history
- **Missing:** Transfer money reports

### 6.3 Profit Calculation Methods
- **Missing:** FIFO (First In First Out) costing method for profit calculation
- **Missing:** Average Cost method for profit calculation
- **Missing:** COGS formula-based profit calculation
- Facturation currently has only a basic Profit & Loss report with no costing method selection.

### 6.4 Inventory Valuation
- **Missing:** Inventory Valuation Summary report (total stock value at cost vs. sale price)
- **Missing:** Per-warehouse inventory valuation

---

## 7. Reporting — Major Gap

Facturation has 4 basic report endpoints. Stocky has 30+. Missing reports:

### 7.1 Sales Reports
- **Missing:** Sales by category
- **Missing:** Sales by brand
- **Missing:** Sales by warehouse
- **Missing:** Sales by payment method
- **Missing:** Sales by user
- **Missing:** Top selling products
- **Missing:** Top customers by revenue

### 7.2 Purchase Reports
- **Missing:** Purchases by warehouse
- **Missing:** Product purchases detail report
- **Missing:** Purchases by user

### 7.3 Customer Reports
- **Missing:** Customer summary report (all customers, totals)
- **Missing:** Customer detail report (individual: sales, payments, quotations, returns)
- **Missing:** Customer due report (outstanding balances)

### 7.4 Supplier Reports
- **Missing:** Supplier summary report
- **Missing:** Supplier detail report
- **Missing:** Supplier due report (outstanding balances)

### 7.5 Product Reports
- **Missing:** Product sales details
- **Missing:** Inventory valuation summary
- **Missing:** Stock count / physical inventory report

### 7.6 User Reports
- **Missing:** Sales report per user
- **Missing:** Purchases report per user
- **Missing:** Quotations report per user
- **Missing:** Returns (sale & purchase) per user
- **Missing:** Transfers & adjustments per user

### 7.7 Warehouse Reports
- **Missing:** Full warehouse-level report (sales, purchases, returns, expenses, stock counts per warehouse)
- **Missing:** Quotations by warehouse

### 7.8 Financial Reports
- **Missing:** All payment transactions report (across all methods)
- **Missing:** Sales by payment method report
- **Missing:** Deposits report
- **Missing:** Money transfers report

### 7.9 Report Features
- **Missing:** PDF export button on all report pages (Facturation only has some)
- **Missing:** Excel/CSV export from report pages
- **Missing:** Row totals / summary row at bottom of report tables

---

## 8. Due Tracking & Bulk Payments — Moderate Gap

### 8.1 Customer Due Tracking
- **Missing:** Outstanding balance ("due") per customer visible on customer list
- **Missing:** Customer due report page
- **Missing:** Bulk payment — pay all outstanding dues for a customer in one action

### 8.2 Supplier Due Tracking
- **Missing:** Outstanding balance ("due") per supplier visible on supplier list
- **Missing:** Supplier due report page
- **Missing:** Bulk payment — pay all outstanding supplier dues in one action

---

## 9. Import / Export Gaps — Moderate

### 9.1 CSV/Excel Imports Missing
- **Missing:** Import customers from CSV/Excel
- **Missing:** Import suppliers from CSV/Excel
- **Missing:** Import purchases from Excel
- **Missing:** Opening stock import (set initial inventory quantities)
- Facturation currently only supports product import

### 9.2 Export Gaps
- **Missing:** Export customers to CSV/Excel
- **Missing:** Export suppliers to CSV/Excel
- **Missing:** Export purchases to CSV/Excel

---

## 10. HRM Module Gaps — Moderate

Facturation has a solid HRM base (employees, departments, designations, attendance, leaves, payroll) but is missing:

### 10.1 Company Management
- **Missing:** Multiple company / branch management (for multi-entity businesses)
- **Missing:** Company assignment per employee

### 10.2 Office Shifts
- **Missing:** Office shift creation (define working hours per shift)
- **Missing:** Shift assignment to employees
- **Missing:** Shift-based attendance validation

### 10.3 Holiday Management
- **Missing:** Holiday calendar (define public holidays)
- **Missing:** Holiday management (add/edit/delete holidays)
- **Missing:** Automatic leave deduction exclusion on holidays

### 10.4 Employee Profile Enhancements
- **Missing:** Employee work experience history
- **Missing:** Employee bank account details
- **Missing:** Employee social media profiles
- **Missing:** Employee profile photo upload

---

## 11. Delivery / Shipment — Moderate Gap

Facturation has a `Shipment` model in the database but no implemented UI or workflow.

- **Missing:** Shipment creation UI when creating a sale
- **Missing:** Shipment status management (Pending → Shipped → Delivered)
- **Missing:** Shipment list page with filtering
- **Missing:** Shipment tracking number field
- **Missing:** Shipment detail page

---

## 12. Recurring Invoices — Moderate Gap

- **Missing:** Recurring invoice configuration (daily, weekly, monthly, yearly)
- **Missing:** Automatic invoice generation on schedule (requires queue/scheduler)
- **Missing:** Recurring invoice management page (view, pause, cancel)
- **Missing:** Auto-send recurring invoice to customer via email

---

## 13. E-commerce Integration — Moderate Gap

- **Missing:** E-commerce customer management (separate from regular customers)
- **Missing:** Customer store data (online store account details)
- **Missing:** Order sync from online store to inventory
- **Missing:** E-commerce-specific pricing or catalog

---

## 14. Bulk Operations — Moderate Gap

- **Missing:** Select multiple records and bulk delete
- **Missing:** Bulk status change (e.g., confirm multiple purchases at once)
- **Missing:** Bulk export selected records

---

## 15. System / Admin Gaps — Low–Moderate

### 15.1 Two-Factor Authentication (2FA)
- **Missing:** TOTP-based 2FA (Google Authenticator, Authy)
- **Missing:** 2FA enforcement per role
- **Missing:** 2FA setup flow (QR code + backup codes)

### 15.2 Dark Mode
- **Missing:** UI dark mode toggle
- **Missing:** Per-user theme preference persistence

### 15.3 Setup / Installation Wizard
- **Missing:** First-run installation wizard (database setup, admin account creation, company info)
- Facturation requires manual `.env` configuration

### 15.4 Module Management
- **Missing:** Enable/disable individual modules from the admin panel
- **Missing:** Upload and install additional modules
- Facturation's modules are always-on, with no toggle capability

### 15.5 Update System
- **Missing:** In-app update checker (check for new versions)
- **Missing:** In-app update installer (one-click upgrade)

### 15.6 Duplicate Prevention
- **Missing:** Automatic duplicate detection for customers (by email/phone)
- **Missing:** Automatic duplicate detection for suppliers
- **Missing:** Automatic duplicate detection for products (by SKU/barcode)

### 15.7 Error Logging
- **Missing:** Application error log table in the database
- **Missing:** Error log report page in admin panel
- Laravel's log channel writes to file only; no in-app error log viewer

---

## 16. Stock Count / Physical Inventory — Moderate Gap

Facturation has an `inventory_counts` table but the feature appears incomplete:

- **Missing:** Stock count workflow (create count → input actual quantities → compare vs. system → approve adjustment)
- **Missing:** Stock count report by warehouse
- **Missing:** Discrepancy report (expected vs. counted quantities)
- **Missing:** Partial stock count (by category or product range)

---

## 17. Language & Localization Gaps — Low

- **Missing:** 30+ pre-built language packs (Facturation has i18n infrastructure but limited language files)
- **Missing:** RTL (right-to-left) layout support for Arabic/Hebrew
- **Missing:** Per-area default language (e.g., POS uses a different language than admin)
- **Missing:** Community-contributed translations

---

## 18. Dashboard Enhancements — Low

Facturation has a basic dashboard. Missing widgets:

- **Missing:** Top selling products widget
- **Missing:** Top customers widget
- **Missing:** Stock alerts summary widget (low stock count)
- **Missing:** Pending purchases / payments widget
- **Missing:** Today's sales counter
- **Missing:** Interactive charts for purchase trends
- **Missing:** Revenue vs. expenses chart (note: route exists but chart features may be limited)

---

## Priority Summary

### Priority 1 — Critical (implement first)
1. **Payment gateway (Stripe)** — needed for online payments
2. **SMS notifications** — expected by end users in most markets
3. **Barcode print + POS scanner** — essential for retail use

### Priority 2 — High (core feature gaps)
4. **Barcode label printing**
5. **Due tracking (customers & suppliers)**
6. **Import customers/suppliers from CSV**
7. **30+ missing reports** (especially customer, supplier, user, and warehouse-level)
8. **Recurring invoices**
9. **Shipment / delivery workflow** (model exists, UI missing)

### Priority 3 — Medium
10. **IMEI/Serial/Warranty tracking**
11. **Combo products**
12. **Deposits & money transfers**
13. **FIFO / Average Cost profit methods**
14. **Office shifts + holidays in HRM**
15. **Project & task management**
16. **Bulk operations**

### Priority 4 — Low / Nice to Have
17. **2FA**
18. **Dark mode**
19. **Setup wizard**
20. **Module management**
21. **In-app update system**
22. **Error log viewer**
23. **E-commerce integration**
24. **Weight scale integration**

---

*Generated by comparing Stocky Ultimate v5.0 feature set against Facturation codebase as of 2026-04-14.*
